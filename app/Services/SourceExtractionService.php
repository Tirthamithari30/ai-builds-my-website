<?php

namespace App\Services;

use App\Models\Business;
use App\Models\BusinessSource;
use App\Models\ExtractedFact;
use App\Models\UploadedFile;
use Illuminate\Support\Facades\Log;

class SourceExtractionService
{
    public function __construct(
        protected AiService $aiService,
        protected DocumentProcessingService $documentService,
        protected WebResearchService $webService
    ) {}

    /**
     * Process an uploaded file (PDF, DOCX, TXT, Image) and extract structured business facts.
     */
    public function processFile(UploadedFile $file): array
    {
        $file->update(['processing_status' => 'processing']);

        $result = $this->documentService->process($file);

        if (!$result['success'] && $result['status'] === 'failed') {
            return $result;
        }

        $extractedText = $result['text'] ?? '';

        // If selectable text is present and document is not a scanned image requiring OCR
        if (!empty($extractedText) && ($result['status'] ?? '') === 'completed' && !in_array(strtolower($file->file_type), ['jpg', 'jpeg', 'png', 'webp', 'image'])) {
            $this->extractAndStoreFacts(
                business: $file->business,
                text: $extractedText,
                sourceType: 'document',
                sourceId: $file->id,
                sourceUrl: $file->original_name
            );
        }

        return [
            'success' => true,
            'status' => $result['status'],
            'message' => $result['error'] ?? 'File processed successfully.',
            'metadata' => $result['metadata'] ?? [],
        ];
    }

    /**
     * Crawl a business source (URL / Google listing) and extract facts.
     */
    public function processSource(BusinessSource $source): array
    {
        if (empty($source->url)) {
            $source->update(['status' => 'failed', 'error_message' => 'No URL provided.']);
            return ['success' => false, 'error' => 'No URL provided.'];
        }

        $source->update(['status' => 'pending']);

        $crawlResult = $this->webService->crawlUrl($source->url);

        if (!$crawlResult['success']) {
            $source->update([
                'title' => $crawlResult['title'] ?? null,
                'status' => $crawlResult['status'],
                'error_message' => $crawlResult['error'],
                'metadata' => $crawlResult['metadata'] ?? null,
            ]);
            return $crawlResult;
        }

        $source->update([
            'title' => $crawlResult['title'],
            'raw_content' => $crawlResult['content'],
            'status' => 'crawled',
            'crawled_at' => now(),
            'error_message' => null,
            'metadata' => $crawlResult['metadata'] ?? null,
        ]);

        $this->extractAndStoreFacts(
            business: $source->business,
            text: $crawlResult['content'],
            sourceType: 'webpage',
            sourceId: $source->id,
            sourceUrl: $source->url
        );

        return [
            'success' => true,
            'status' => 'crawled',
            'message' => 'Webpage crawled and facts extracted successfully.',
        ];
    }

    /**
     * Parse text for facts using Gemini AI with regex heuristic fallback.
     */
    public function extractAndStoreFacts(Business $business, string $text, string $sourceType, ?int $sourceId, ?string $sourceUrl): void
    {
        $facts = [];

        // If Gemini is configured, use AI extraction
        if ($this->aiService->isConfigured()) {
            $aiResponse = $this->aiService->extractFacts($text, $business->name, $sourceType);
            if ($aiResponse['success'] && isset($aiResponse['data']['facts']) && is_array($aiResponse['data']['facts'])) {
                $facts = $aiResponse['data']['facts'];
            }
        }

        // Always supplement with verified regex heuristics (phone, email, hours)
        $heuristicFacts = $this->extractHeuristicFacts($text);

        // Merge AI facts with heuristics without duplicates
        $existingKeys = array_column($facts, 'fact_key');
        foreach ($heuristicFacts as $hFact) {
            if (!in_array($hFact['fact_key'], $existingKeys, true)) {
                $facts[] = $hFact;
            }
        }

        // Persist ExtractedFact models
        foreach ($facts as $fact) {
            $factValue = is_array($fact['fact_value']) ? json_encode($fact['fact_value']) : (string)$fact['fact_value'];

            if (empty(trim($factValue))) {
                continue;
            }

            ExtractedFact::create([
                'business_id' => $business->id,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'source_url' => $sourceUrl,
                'fact_key' => $fact['fact_key'] ?? 'general',
                'fact_name' => $fact['fact_name'] ?? ucwords(str_replace('_', ' ', $fact['fact_key'] ?? 'fact')),
                'fact_value' => $factValue,
                'verification_status' => 'extracted',
                'confidence_score' => $fact['confidence_score'] ?? 0.85,
                'is_confirmed' => false,
                'extraction_time' => now(),
                'notes' => $fact['notes'] ?? ($fact['source_excerpt'] ?? null),
            ]);
        }
    }

    /**
     * Deterministic, zero-hallucination regex extraction for common business facts.
     */
    protected function extractHeuristicFacts(string $text): array
    {
        $facts = [];

        // Email extraction
        if (preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            $uniqueEmails = array_values(array_unique($matches[0]));
            if (!empty($uniqueEmails)) {
                $facts[] = [
                    'fact_key' => 'email',
                    'fact_name' => 'Email Address',
                    'fact_value' => $uniqueEmails[0],
                    'confidence_score' => 0.95,
                    'notes' => 'Direct regex pattern match',
                ];
            }
        }

        // Phone extraction (Indian & International patterns)
        if (preg_match_all('/(?:\+?\d{1,3}[-.\s]?)?\(?\d{2,5}\)?[-.\s]?\d{3,5}[-.\s]?\d{3,5}/', $text, $matches)) {
            $candidates = array_filter($matches[0], fn($p) => strlen(preg_replace('/\D/', '', $p)) >= 10);
            $uniquePhones = array_values(array_unique($candidates));
            if (!empty($uniquePhones)) {
                $facts[] = [
                    'fact_key' => 'phone',
                    'fact_name' => 'Phone Number',
                    'fact_value' => trim($uniquePhones[0]),
                    'confidence_score' => 0.90,
                    'notes' => 'Pattern match from document text',
                ];
            }
        }

        return $facts;
    }
}
