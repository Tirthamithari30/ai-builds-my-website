<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Website;
use App\Models\WebsiteSection;
use Illuminate\Support\Str;

class WebsiteGenerationService
{
    public const PRESETS = [
        'modern_minimal' => [
            'name' => 'Modern Minimal',
            'font' => 'Inter, system-ui, sans-serif',
            'primary' => '#2563eb',
            'secondary' => '#0f172a',
            'bg' => '#f8fafc',
            'style' => 'Clean whitespace, crisp borders, high typography contrast.',
        ],
        'premium_elegant' => [
            'name' => 'Premium Elegant',
            'font' => 'Playfair Display, Georgia, serif',
            'primary' => '#b45309',
            'secondary' => '#1c1917',
            'bg' => '#fffbeb',
            'style' => 'Refined serif headers, warm metallic tones, luxury framing.',
        ],
        'bold_creative' => [
            'name' => 'Bold Creative',
            'font' => 'Plus Jakarta Sans, sans-serif',
            'primary' => '#7c3aed',
            'secondary' => '#18181b',
            'bg' => '#faf5ff',
            'style' => 'Vibrant violet accents, expressive badges, dynamic cards.',
        ],
    ];

    public function __construct(
        protected AiService $aiService,
        protected FactVerificationService $factService,
        protected WebsitePublishingService $publishingService
    ) {}

    /**
     * Generate complete structured website based on verified facts and category.
     */
    public function generateWebsite(Business $business, string $preset = 'modern_minimal'): Website
    {
        $verifiedFacts = $this->factService->getVerifiedDictionary($business);

        // Resolve slug
        $slug = $this->publishingService->generateUniqueSlug($business->name);

        $presetConfig = self::PRESETS[$preset] ?? self::PRESETS['modern_minimal'];

        $website = Website::create([
            'business_id' => $business->id,
            'user_id' => $business->user_id,
            'title' => $business->name,
            'slug' => $slug,
            'design_preset' => $preset,
            'primary_color' => $presetConfig['primary'],
            'secondary_color' => $presetConfig['secondary'],
            'font_family' => $presetConfig['font'],
            'meta_description' => "Welcome to {$business->name} in " . ($verifiedFacts['city'] ?? 'our city') . ". " . Str::limit($verifiedFacts['description'] ?? '', 140),
            'is_published' => false,
            'current_version' => 1,
            'settings' => [
                'tagline' => $verifiedFacts['tagline'] ?? "Excellence in {$business->category}",
                'phone' => $verifiedFacts['phone'] ?? $business->phone,
                'email' => $verifiedFacts['email'] ?? $business->email,
                'address' => $verifiedFacts['address'] ?? $business->address,
                'city' => $verifiedFacts['city'] ?? $business->city,
                'operating_hours' => $verifiedFacts['operating_hours'] ?? $business->operating_hours,
            ],
        ]);

        // Generate sections
        $sectionTypes = $this->determineSectionsForCategory($business->category);

        $order = 0;
        foreach ($sectionTypes as $type) {
            $content = $this->buildSectionContent($type, $business, $verifiedFacts, $preset);

            WebsiteSection::create([
                'website_id' => $website->id,
                'section_type' => $type,
                'order' => $order++,
                'is_visible' => true,
                'content' => $content,
            ]);
        }

        // Save initial snapshot version
        $this->publishingService->createSnapshot($website, 'Initial AI generation from verified business facts', $business->user_id);

        return $website;
    }

    /**
     * Regenerate an individual section without modifying other sections.
     */
    public function regenerateSection(WebsiteSection $section, string $userPrompt = ''): array
    {
        $website = $section->website;
        $business = $website->business;
        $verifiedFacts = $this->factService->getVerifiedDictionary($business);

        $newContent = $this->buildSectionContent(
            type: $section->section_type,
            business: $business,
            verifiedFacts: $verifiedFacts,
            preset: $website->design_preset,
            customPrompt: $userPrompt
        );

        return [
            'success' => true,
            'old_content' => $section->content,
            'new_content' => $newContent,
        ];
    }

    /**
     * Determine tailored sections based on business category.
     */
    public function determineSectionsForCategory(string $category): array
    {
        $base = ['hero', 'about', 'services'];

        switch (strtolower($category)) {
            case 'restaurant':
            case 'cafe':
                return ['hero', 'about', 'products', 'gallery', 'features', 'location', 'contact', 'footer'];
            case 'salon':
            case 'fitness':
                return ['hero', 'about', 'services', 'gallery', 'faq', 'cta', 'location', 'contact', 'footer'];
            case 'retail':
                return ['hero', 'features', 'products', 'gallery', 'about', 'location', 'contact', 'footer'];
            case 'consultant':
            case 'professional_services':
            default:
                return ['hero', 'about', 'services', 'features', 'faq', 'cta', 'contact', 'footer'];
        }
    }

    /**
     * Build structured content for each section.
     */
    public function buildSectionContent(string $type, Business $business, array $verifiedFacts, string $preset, string $customPrompt = ''): array
    {
        $name = $business->name;
        $city = $verifiedFacts['city'] ?? $business->city ?? 'our local area';
        $phone = $verifiedFacts['phone'] ?? $business->phone ?? 'Contact us for details';
        $email = $verifiedFacts['email'] ?? $business->email ?? 'info@' . Str::slug($name) . '.com';
        $address = $verifiedFacts['address'] ?? $business->address ?? "Main Street, {$city}";
        $desc = $verifiedFacts['description'] ?? $business->description ?? "Delivering exceptional {$business->category} solutions with verified standards and customer satisfaction.";

        switch ($type) {
            case 'hero':
                return [
                    'badge' => "Verified Local Business in {$city}",
                    'heading' => "Welcome to {$name}",
                    'subheading' => $desc,
                    'primary_button_text' => 'Get in Touch',
                    'primary_button_link' => '#contact',
                    'secondary_button_text' => 'View Offerings',
                    'secondary_button_link' => '#services',
                    'hero_image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&w=1200&q=80',
                ];

            case 'about':
                return [
                    'badge' => 'About Us',
                    'heading' => "Your Trusted {$business->category} Partner in {$city}",
                    'description_p1' => "At {$name}, we are committed to delivering authentic quality and dependable service. Rooted in {$city}, our mission is to provide an unmatched experience tailored to your exact needs.",
                    'description_p2' => $desc,
                    'highlights' => [
                        ['title' => 'Locally Owned', 'desc' => "Proudly serving {$city} with verified commitment."],
                        ['title' => 'Customer Focused', 'desc' => 'Every client receives direct, attentive care.'],
                        ['title' => 'Authentic Standards', 'desc' => 'Grounded in transparent and factual quality.'],
                    ],
                ];

            case 'services':
            case 'products':
                $items = [];
                $rawItems = $verifiedFacts[$type] ?? ($business->{$type} ?? null);

                if (is_array($rawItems) && !empty($rawItems)) {
                    foreach (array_slice($rawItems, 0, 6) as $item) {
                        $items[] = [
                            'title' => is_string($item) ? $item : ($item['name'] ?? 'Premium Offering'),
                            'description' => is_array($item) ? ($item['description'] ?? "Tailored {$type} designed for excellence.") : "Top-tier {$type} by {$name}.",
                            'price' => is_array($item) ? ($item['price'] ?? null) : null,
                        ];
                    }
                } else {
                    $items = [
                        ['title' => 'Core Consultation & Service', 'description' => "Comprehensive {$business->category} solutions customized for individual needs.", 'price' => null],
                        ['title' => 'Standard Package', 'description' => 'Reliable, proven methodology ensuring consistent results.', 'price' => null],
                        ['title' => 'Specialized Custom Care', 'description' => 'Dedicated priority assistance and focused attention.', 'price' => null],
                    ];
                }

                return [
                    'badge' => ucfirst($type),
                    'heading' => "Explore Our {$business->category} Offerings",
                    'subheading' => "Curated selections and services crafted specifically for our clients in {$city}.",
                    'items' => $items,
                ];

            case 'features':
                return [
                    'badge' => 'Why Choose Us',
                    'heading' => "The {$name} Difference",
                    'subheading' => 'What sets us apart in reliability, execution, and customer loyalty.',
                    'cards' => [
                        ['title' => 'Verified Quality', 'desc' => 'Every service is vetted to guarantee high standards.', 'icon' => 'check-circle'],
                        ['title' => 'Fast Response', 'desc' => 'Swift communication via phone and direct inquiry.', 'icon' => 'clock'],
                        ['title' => 'Community Trust', 'desc' => "Deep relationships built across {$city}.", 'icon' => 'shield'],
                    ],
                ];

            case 'gallery':
                return [
                    'badge' => 'Gallery',
                    'heading' => 'A Glimpse into Our Space & Work',
                    'images' => [
                        ['url' => 'https://images.unsplash.com/photo-1497366811353-6870744d04b2?auto=format&fit=crop&w=600&q=80', 'caption' => 'Our Facilities'],
                        ['url' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?auto=format&fit=crop&w=600&q=80', 'caption' => 'Professional Environment'],
                        ['url' => 'https://images.unsplash.com/photo-1557804506-669a67965ba0?auto=format&fit=crop&w=600&q=80', 'caption' => 'Customer Experience'],
                    ],
                ];

            case 'location':
                return [
                    'badge' => 'Location & Hours',
                    'heading' => "Visit {$name}",
                    'address' => $address,
                    'city' => $city,
                    'operating_hours' => $verifiedFacts['operating_hours'] ?? 'Monday – Saturday: 9:00 AM – 8:00 PM',
                    'map_note' => "Conveniently situated in {$city}.",
                ];

            case 'faq':
                return [
                    'badge' => 'Frequently Asked Questions',
                    'heading' => 'Clear Answers to Your Common Inquiries',
                    'questions' => [
                        ['q' => "How do I book or order with {$name}?", 'a' => "You can reach us directly via phone at {$phone} or email at {$email}."],
                        ['q' => "Where are you located?", 'a' => "We are located at {$address}, {$city}."],
                        ['q' => "What are your business operating hours?", 'a' => is_string($verifiedFacts['operating_hours'] ?? null) ? $verifiedFacts['operating_hours'] : 'Please contact us directly for specific holiday and daily hours.'],
                    ],
                ];

            case 'cta':
                return [
                    'heading' => "Ready to Experience {$name}?",
                    'subheading' => "Reach out today to discover how our {$business->category} services can benefit you.",
                    'button_text' => 'Contact Us Today',
                    'button_link' => '#contact',
                ];

            case 'contact':
                return [
                    'badge' => 'Contact Us',
                    'heading' => 'Get in Touch Directly',
                    'phone' => $phone,
                    'email' => $email,
                    'address' => $address,
                    'form_title' => 'Send Us a Message',
                ];

            case 'footer':
                return [
                    'copyright' => "© " . date('Y') . " {$name}. All rights reserved.",
                    'tagline' => "Professional {$business->category} in {$city}.",
                    'phone' => $phone,
                    'email' => $email,
                ];

            default:
                return [
                    'heading' => ucwords(str_replace('_', ' ', $type)),
                    'content' => "Custom section for {$name}.",
                ];
        }
    }
}
