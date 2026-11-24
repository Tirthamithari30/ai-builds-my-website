<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Exception;

class WebResearchService
{
    protected ?string $searchApiKey;
    protected string $searchProvider;

    public function __construct()
    {
        $this->searchApiKey = config('services.search.api_key', env('SEARCH_API_KEY'));
        $this->searchProvider = config('services.search.provider', env('SEARCH_PROVIDER', 'serper'));
    }

    /**
     * Check if web search provider is configured.
     */
    public function isSearchConfigured(): bool
    {
        return !empty($this->searchApiKey) &&
               trim($this->searchApiKey) !== '' &&
               $this->searchApiKey !== 'your_search_api_key_here';
    }

    /**
     * Search the web for a business.
     * Does NOT fabricate results if API key is missing.
     */
    public function searchBusiness(string $businessName, ?string $city = null): array
    {
        if (!$this->isSearchConfigured()) {
            return [
                'success' => false,
                'available' => false,
                'message' => 'Web search integration is currently not configured (SEARCH_API_KEY is missing). You can add direct website URLs, Google Business links, or documents manually.',
                'results' => [],
            ];
        }

        $query = $businessName . ($city ? " {$city}" : '');

        try {
            if ($this->searchProvider === 'serper') {
                $response = Http::timeout(15)
                    ->withHeaders([
                        'X-API-KEY' => $this->searchApiKey,
                        'Content-Type' => 'application/json',
                    ])
                    ->post('https://google.serper.dev/search', [
                        'q' => $query,
                        'num' => 5,
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $organic = $json['organic'] ?? [];
                    $places = $json['places'] ?? [];

                    $results = [];

                    foreach ($places as $place) {
                        $results[] = [
                            'title' => $place['title'] ?? $businessName,
                            'url' => $place['website'] ?? null,
                            'snippet' => ($place['address'] ?? '') . ' | ' . ($place['category'] ?? '') . ' | ' . ($place['phoneNumber'] ?? ''),
                            'source_type' => 'google_business',
                            'details' => $place,
                        ];
                    }

                    foreach ($organic as $item) {
                        $results[] = [
                            'title' => $item['title'] ?? '',
                            'url' => $item['link'] ?? '',
                            'snippet' => $item['snippet'] ?? '',
                            'source_type' => 'search_result',
                        ];
                    }

                    return [
                        'success' => true,
                        'available' => true,
                        'message' => 'Search completed successfully.',
                        'results' => $results,
                    ];
                }

                return [
                    'success' => false,
                    'available' => true,
                    'message' => "Search API returned error HTTP {$response->status()}",
                    'results' => [],
                ];
            }

            return [
                'success' => false,
                'available' => false,
                'message' => "Unsupported search provider: {$this->searchProvider}",
                'results' => [],
            ];

        } catch (Exception $e) {
            Log::error("Web search error: " . $e->getMessage());
            return [
                'success' => false,
                'available' => true,
                'message' => 'Web search request failed: ' . $e->getMessage(),
                'results' => [],
            ];
        }
    }

    /**
     * Crawl a public webpage with strict SSRF protection.
     */
    public function crawlUrl(string $url): array
    {
        // 1. SSRF and Protocol Validation
        $ssrfCheck = $this->validateSafeUrl($url);
        if (!$ssrfCheck['safe']) {
            return [
                'success' => false,
                'status' => 'blocked',
                'error' => 'Security violation: ' . $ssrfCheck['reason'],
                'url' => $url,
                'title' => null,
                'content' => null,
            ];
        }

        // 2. Safe HTTP Request
        try {
            $response = Http::timeout(15)
                ->withUserAgent('Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36')
                ->withHeaders([
                    'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
                    'Accept-Language' => 'en-US,en;q=0.5',
                ])
                ->get($url);

            $status = $response->status();

            if ($status === 403 || $status === 401 || $status === 429) {
                return [
                    'success' => false,
                    'status' => 'blocked',
                    'error' => "This external website blocked automated reading (HTTP {$status}). Please provide business details manually or via PDF upload.",
                    'url' => $url,
                    'title' => null,
                    'content' => null,
                ];
            }

            if (!$response->successful()) {
                return [
                    'success' => false,
                    'status' => 'failed',
                    'error' => "Webpage request failed with HTTP status {$status}.",
                    'url' => $url,
                    'title' => null,
                    'content' => null,
                ];
            }

            $html = $response->body();
            $extracted = $this->extractCleanTextFromHtml($html);

            return [
                'success' => true,
                'status' => 'crawled',
                'error' => null,
                'url' => $url,
                'title' => $extracted['title'],
                'content' => $extracted['text'],
            ];

        } catch (Exception $e) {
            Log::warning("Webpage crawl error for {$url}: " . $e->getMessage());

            return [
                'success' => false,
                'status' => 'failed',
                'error' => 'Failed to reach webpage: ' . $e->getMessage(),
                'url' => $url,
                'title' => null,
                'content' => null,
            ];
        }
    }

    /**
     * Strict SSRF validator preventing access to internal, loopback, or private networks.
     */
    public function validateSafeUrl(string $url): array
    {
        $parsed = parse_url($url);

        if (!$parsed || empty($parsed['scheme']) || empty($parsed['host'])) {
            return ['safe' => false, 'reason' => 'Invalid URL structure.'];
        }

        $scheme = strtolower($parsed['scheme']);
        if (!in_array($scheme, ['http', 'https'], true)) {
            return ['safe' => false, 'reason' => 'Only HTTP and HTTPS protocols are permitted.'];
        }

        $host = strtolower($parsed['host']);

        // Block localhost and standard loopback hostnames
        if (in_array($host, ['localhost', '127.0.0.1', '::1', '0.0.0.0'], true) ||
            str_ends_with($host, '.localhost') ||
            str_ends_with($host, '.internal') ||
            str_ends_with($host, '.local')) {
            return ['safe' => false, 'reason' => 'Access to localhost and internal hostnames is strictly forbidden.'];
        }

        // Resolve DNS and check against private / reserved IP spaces
        $ip = gethostbyname($host);
        if ($ip === $host && !filter_var($host, FILTER_VALIDATE_IP)) {
            return ['safe' => false, 'reason' => 'Hostname could not be resolved.'];
        }

        // Validate IP is not private or reserved (RFC 1918, RFC 3927 cloud metadata)
        if (!filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
            return ['safe' => false, 'reason' => 'Access to private or cloud metadata IP ranges is prohibited.'];
        }

        // AWS / GCP / Azure metadata endpoint check (169.254.169.254)
        if ($ip === '169.254.169.254' || str_starts_with($ip, '169.254.')) {
            return ['safe' => false, 'reason' => 'Access to cloud instance metadata is prohibited.'];
        }

        return ['safe' => true, 'reason' => null];
    }

    /**
     * Clean and strip HTML down to plain textual content.
     */
    protected function extractCleanTextFromHtml(string $html): array
    {
        // Extract title
        $title = '';
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $matches)) {
            $title = html_entity_decode(trim($matches[1]), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }

        // Strip scripts, styles, svg, and nav clutter
        $clean = preg_replace('/<(script|style|svg|noscript|header|footer|nav)\b[^>]*>(.*?)<\/\1>/is', ' ', $html);
        $clean = strip_tags($clean);
        $clean = html_entity_decode($clean, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $clean = preg_replace('/\s+/', ' ', trim($clean));

        // Limit to reasonable token window (first 8000 characters)
        if (mb_strlen($clean) > 8000) {
            $clean = mb_substr($clean, 0, 8000) . '... [Content truncated]';
        }

        return [
            'title' => $title ?: 'Untitled Webpage',
            'text' => $clean,
        ];
    }
}
