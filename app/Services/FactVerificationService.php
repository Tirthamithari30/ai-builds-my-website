<?php

namespace App\Services;

use App\Models\Business;
use App\Models\ExtractedFact;
use App\Models\FactConfirmation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

class FactVerificationService
{
    /**
     * Required core fact keys for generating an authentic business website.
     */
    public const REQUIRED_FACTS = [
        'phone' => 'Phone Number',
        'email' => 'Email Address',
        'address' => 'Physical Address',
        'description' => 'Business Summary',
        'services' => 'Services / Offerings',
        'operating_hours' => 'Operating Hours',
    ];

    /**
     * Optional / secondary business fact categories.
     */
    public const OPTIONAL_FACTS = [
        'website' => 'Website URL',
        'pricing' => 'Pricing Information',
        'products' => 'Products List',
        'features' => 'Facilities & Features',
        'social_media' => 'Social Media Links',
        'certifications' => 'Certifications & Accreditations',
        'awards' => 'Awards & Recognitions',
        'founded_year' => 'Established / Founded Year',
    ];

    public function __construct(
        protected AiService $aiService,
        protected SourceExtractionService $sourceExtractionService
    ) {}

    /**
     * Analyze all extracted facts for a business, identify conflicts, and categorize statuses.
     */
    public function analyzeFacts(Business $business): array
    {
        $allFacts = ExtractedFact::where('business_id', $business->id)->get();
        $grouped = $allFacts->groupBy('fact_key');

        // Batch load all fact confirmations for this business to eliminate N+1 queries
        $allConfirmations = FactConfirmation::where('business_id', $business->id)
            ->latest()
            ->get()
            ->groupBy('fact_key');

        $confirmed = [];
        $conflicts = [];
        $unverified = [];
        $rejected = [];
        $outdated = [];
        $missing = [];

        // Check required facts first
        foreach (self::REQUIRED_FACTS as $key => $label) {
            $factsForKey = $grouped->get($key, collect());

            // 1. Check if user already confirmed or manually supplied this fact
            $confirmation = $allConfirmations->get($key)?->first();

            $confirmedModel = $factsForKey->firstWhere('verification_status', 'user-confirmed');

            if ($confirmation || $confirmedModel) {
                $val = $confirmedModel ? $confirmedModel->user_confirmed_value ?: $confirmedModel->fact_value : $confirmation->confirmed_value;
                $confirmed[$key] = [
                    'fact_key' => $key,
                    'fact_name' => $label,
                    'fact_value' => $val,
                    'status' => 'user-confirmed',
                    'is_owner_provided' => $confirmedModel ? $confirmedModel->is_owner_provided : true,
                    'confirmed_at' => $confirmation ? $confirmation->confirmed_at : ($confirmedModel->updated_at ?? now()),
                    'model' => $confirmedModel,
                ];
                continue;
            }

            // Check if active facts exist
            $activeCandidates = $factsForKey->filter(fn($f) => !in_array($f->verification_status, ['rejected', 'outdated']));

            if ($activeCandidates->isEmpty()) {
                // Check if direct business attribute was entered during onboarding
                $directVal = $business->{$key} ?? null;
                if (!empty($directVal)) {
                    $valString = is_array($directVal) ? json_encode($directVal) : (string)$directVal;
                    $confirmed[$key] = [
                        'fact_key' => $key,
                        'fact_name' => $label,
                        'fact_value' => $valString,
                        'status' => 'user-confirmed',
                        'is_owner_provided' => true,
                        'source' => 'Direct Business Onboarding',
                        'model' => null,
                    ];
                } else {
                    $missing[$key] = [
                        'fact_key' => $key,
                        'fact_name' => $label,
                        'status' => 'missing',
                        'prompt' => "No {$label} found in your sources. Please enter it manually or upload a document containing it.",
                    ];
                }
                continue;
            }

            // 2. Conflict detection across active candidate facts
            $distinctValues = $activeCandidates->pluck('fact_value')->map(fn($v) => trim((string)$v))->unique();
            $hasExplicitConflict = $activeCandidates->contains('verification_status', 'conflicting');

            if ($distinctValues->count() > 1 || $hasExplicitConflict) {
                $conflicts[$key] = [
                    'fact_key' => $key,
                    'fact_name' => $label,
                    'status' => 'conflicting',
                    'candidates' => $activeCandidates->map(fn($f) => [
                        'id' => $f->id,
                        'value' => $f->fact_value,
                        'source_type' => $f->source_type,
                        'source_url' => $f->source_url,
                        'evidence' => $f->evidence ?: $f->notes,
                        'confidence' => $f->confidence_score,
                    ])->values()->all(),
                ];

                ExtractedFact::whereIn('id', $activeCandidates->pluck('id'))->update([
                    'verification_status' => 'conflicting',
                ]);
            } else {
                $single = $activeCandidates->first();
                $unverified[$key] = [
                    'fact_key' => $key,
                    'fact_name' => $label,
                    'fact_value' => $single->fact_value,
                    'status' => $single->verification_status === 'conflicting' ? 'source-supported' : $single->verification_status,
                    'source_type' => $single->source_type,
                    'source_url' => $single->source_url,
                    'evidence' => $single->evidence ?: $single->notes,
                    'confidence' => $single->confidence_score,
                    'model' => $single,
                ];
            }
        }

        // Process all other arbitrary/optional fact keys
        foreach ($grouped as $key => $factsForKey) {
            if (isset(self::REQUIRED_FACTS[$key]) || isset($confirmed[$key]) || isset($conflicts[$key])) {
                continue;
            }

            // Rejected facts
            $rejectedItems = $factsForKey->where('verification_status', 'rejected');
            foreach ($rejectedItems as $rej) {
                $rejected[] = $rej;
            }

            // Outdated facts
            $outdatedItems = $factsForKey->where('verification_status', 'outdated');
            foreach ($outdatedItems as $out) {
                $outdated[] = $out;
            }

            $active = $factsForKey->filter(fn($f) => !in_array($f->verification_status, ['rejected', 'outdated']));
            if ($active->isEmpty()) {
                continue;
            }

            $confirmedItem = $active->firstWhere('verification_status', 'user-confirmed');
            if ($confirmedItem) {
                $confirmed[$key] = [
                    'fact_key' => $key,
                    'fact_name' => $confirmedItem->fact_name,
                    'fact_value' => $confirmedItem->user_confirmed_value ?: $confirmedItem->fact_value,
                    'status' => 'user-confirmed',
                    'is_owner_provided' => $confirmedItem->is_owner_provided,
                    'model' => $confirmedItem,
                ];
                continue;
            }

            $distinct = $active->pluck('fact_value')->map(fn($v) => trim((string)$v))->unique();
            $hasExplicitConflict = $active->contains('verification_status', 'conflicting');
            if ($distinct->count() > 1 || $hasExplicitConflict) {
                $conflicts[$key] = [
                    'fact_key' => $key,
                    'fact_name' => $active->first()->fact_name ?? ucwords(str_replace('_', ' ', $key)),
                    'status' => 'conflicting',
                    'candidates' => $active->map(fn($f) => [
                        'id' => $f->id,
                        'value' => $f->fact_value,
                        'source_type' => $f->source_type,
                        'source_url' => $f->source_url,
                        'evidence' => $f->evidence ?: $f->notes,
                    ])->values()->all(),
                ];
            } else {
                $single = $active->first();
                $unverified[$key] = [
                    'fact_key' => $key,
                    'fact_name' => $single->fact_name,
                    'fact_value' => $single->fact_value,
                    'status' => $single->verification_status,
                    'source_type' => $single->source_type,
                    'source_url' => $single->source_url,
                    'evidence' => $single->evidence ?: $single->notes,
                    'confidence' => $single->confidence_score,
                    'model' => $single,
                ];
            }
        }

        return [
            'all_facts' => $allFacts,
            'confirmed' => $confirmed,
            'verified_facts' => $confirmed, // Backward compatibility alias for Phase 1
            'conflicts' => $conflicts,
            'unverified' => $unverified,
            'rejected' => $rejected,
            'outdated' => $outdated,
            'missing' => $missing,
            'has_blocking_conflicts' => count($conflicts) > 0,
            'can_generate_website' => count($conflicts) === 0 && count($missing) <= 2,
        ];
    }

    /**
     * Confirm an extracted fact as accurate.
     */
    public function confirmFact(ExtractedFact $fact, int $userId): ExtractedFact
    {
        $fact->update([
            'verification_status' => 'user-confirmed',
            'is_confirmed' => true,
            'user_confirmed_value' => $fact->fact_value,
        ]);

        FactConfirmation::create([
            'business_id' => $fact->business_id,
            'fact_key' => $fact->fact_key,
            'confirmed_value' => $fact->fact_value,
            'resolution_type' => 'accepted',
            'confirmed_by' => $userId,
            'confirmed_at' => now(),
        ]);

        $this->syncBusinessColumnIfApplicable($fact->business, $fact->fact_key, $fact->fact_value);

        return $fact;
    }

    /**
     * Edit and confirm an extracted fact with a custom value.
     */
    public function editAndConfirmFact(ExtractedFact $fact, string $newValue, int $userId): ExtractedFact
    {
        $fact->update([
            'verification_status' => 'user-confirmed',
            'is_confirmed' => true,
            'user_confirmed_value' => $newValue,
        ]);

        FactConfirmation::create([
            'business_id' => $fact->business_id,
            'fact_key' => $fact->fact_key,
            'confirmed_value' => $newValue,
            'resolution_type' => 'custom_edited',
            'conflict_candidates' => [$fact->fact_value],
            'confirmed_by' => $userId,
            'confirmed_at' => now(),
        ]);

        $this->syncBusinessColumnIfApplicable($fact->business, $fact->fact_key, $newValue);

        return $fact;
    }

    /**
     * Reject an extracted fact.
     */
    public function rejectFact(ExtractedFact $fact, int $userId, ?string $reason = null): ExtractedFact
    {
        $fact->update([
            'verification_status' => 'rejected',
            'is_confirmed' => false,
            'notes' => $reason ? ($fact->notes . ' | Rejection reason: ' . $reason) : $fact->notes,
        ]);

        return $fact;
    }

    /**
     * Mark an extracted fact as outdated.
     */
    public function markOutdated(ExtractedFact $fact, int $userId): ExtractedFact
    {
        $fact->update([
            'verification_status' => 'outdated',
            'is_confirmed' => false,
        ]);

        return $fact;
    }

    /**
     * Resolve a conflict across conflicting facts for a specific key.
     */
    public function resolveConflict(Business $business, string $factKey, string $confirmedValue, int $userId, string $resolutionType = 'conflict_resolved'): FactConfirmation
    {
        $candidates = ExtractedFact::where('business_id', $business->id)
            ->where('fact_key', $factKey)
            ->pluck('fact_value')
            ->all();

        $confirmation = FactConfirmation::create([
            'business_id' => $business->id,
            'fact_key' => $factKey,
            'confirmed_value' => $confirmedValue,
            'resolution_type' => $resolutionType,
            'conflict_candidates' => $candidates,
            'confirmed_by' => $userId,
            'confirmed_at' => now(),
        ]);

        // Mark the selected one as confirmed, others as outdated
        ExtractedFact::where('business_id', $business->id)
            ->where('fact_key', $factKey)
            ->each(function ($fact) use ($confirmedValue) {
                if (trim($fact->fact_value) === trim($confirmedValue)) {
                    $fact->update([
                        'verification_status' => 'user-confirmed',
                        'is_confirmed' => true,
                        'user_confirmed_value' => $confirmedValue,
                    ]);
                } else {
                    $fact->update([
                        'verification_status' => 'outdated',
                        'is_confirmed' => false,
                    ]);
                }
            });

        $this->syncBusinessColumnIfApplicable($business, $factKey, $confirmedValue);

        return $confirmation;
    }

    /**
     * Manually add a missing fact (clearly flagged as owner-provided).
     */
    public function addManualFact(Business $business, string $factKey, string $factName, string $factValue, int $userId): ExtractedFact
    {
        $fact = ExtractedFact::create([
            'business_id' => $business->id,
            'source_type' => 'manual',
            'source_url' => 'Manual Entry by Business Owner',
            'fact_key' => $factKey,
            'fact_name' => $factName,
            'fact_value' => $factValue,
            'evidence' => 'Supplied directly by business owner via Fact Review interface.',
            'verification_status' => 'user-confirmed',
            'confidence_score' => 1.0,
            'is_confirmed' => true,
            'is_owner_provided' => true,
            'user_confirmed_value' => $factValue,
            'extraction_time' => now(),
        ]);

        FactConfirmation::create([
            'business_id' => $business->id,
            'fact_key' => $factKey,
            'confirmed_value' => $factValue,
            'resolution_type' => 'manual_owner_entry',
            'confirmed_by' => $userId,
            'confirmed_at' => now(),
        ]);

        $this->syncBusinessColumnIfApplicable($business, $factKey, $factValue);

        return $fact;
    }

    /**
     * Re-extract facts across all uploaded documents and sources for a business.
     * Idempotent: avoids creating duplicate facts if identical value and source already exist.
     */
    public function extractAllSources(Business $business): array
    {
        $newFactsCount = 0;

        // Process files
        foreach ($business->files as $file) {
            if ($file->extracted_text && $file->processing_status === 'completed') {
                $count = $this->extractFactsFromText(
                    business: $business,
                    text: $file->extracted_text,
                    sourceType: 'document',
                    sourceId: $file->id,
                    sourceUrl: $file->original_name
                );
                $newFactsCount += $count;
            }
        }

        // Process sources
        foreach ($business->sources as $source) {
            if ($source->raw_content && $source->status === 'crawled') {
                $count = $this->extractFactsFromText(
                    business: $business,
                    text: $source->raw_content,
                    sourceType: 'webpage',
                    sourceId: $source->id,
                    sourceUrl: $source->url
                );
                $newFactsCount += $count;
            }
        }

        return [
            'success' => true,
            'new_facts_count' => $newFactsCount,
        ];
    }

    /**
     * Helper to extract and store facts from arbitrary text, avoiding exact duplicates.
     */
    protected function extractFactsFromText(Business $business, string $text, string $sourceType, ?int $sourceId, ?string $sourceUrl): int
    {
        $facts = [];

        if ($this->aiService->isConfigured()) {
            $aiResult = $this->aiService->extractFacts($text, $business->name, $sourceType);
            if ($aiResult['success'] && isset($aiResult['data']['facts']) && is_array($aiResult['data']['facts'])) {
                $facts = $aiResult['data']['facts'];
            }
        }

        // Always merge with deterministic regex heuristics
        $heuristicFacts = $this->extractHeuristicFacts($text);
        $existingKeys = array_column($facts, 'fact_key');
        foreach ($heuristicFacts as $hFact) {
            if (!in_array($hFact['fact_key'], $existingKeys, true)) {
                $facts[] = $hFact;
            }
        }

        $createdCount = 0;

        foreach ($facts as $f) {
            $val = is_array($f['fact_value']) ? json_encode($f['fact_value']) : trim((string)$f['fact_value']);
            if (empty($val)) {
                continue;
            }

            // Check if identical fact from same source already exists to avoid duplication
            $exists = ExtractedFact::where('business_id', $business->id)
                ->where('fact_key', $f['fact_key'])
                ->where('fact_value', $val)
                ->where('source_type', $sourceType)
                ->where('source_id', $sourceId)
                ->exists();

            if (!$exists) {
                ExtractedFact::create([
                    'business_id' => $business->id,
                    'source_type' => $sourceType,
                    'source_id' => $sourceId,
                    'source_url' => $sourceUrl,
                    'fact_key' => $f['fact_key'],
                    'fact_name' => $f['fact_name'] ?? ucwords(str_replace('_', ' ', $f['fact_key'])),
                    'fact_value' => $val,
                    'evidence' => $f['source_excerpt'] ?? ($f['evidence'] ?? null),
                    'verification_status' => 'extracted',
                    'confidence_score' => $f['confidence_score'] ?? 0.85,
                    'is_confirmed' => false,
                    'is_owner_provided' => false,
                    'extraction_time' => now(),
                    'notes' => $f['notes'] ?? null,
                ]);
                $createdCount++;
            }
        }

        return $createdCount;
    }

    /**
     * Regex heuristics for verified extraction without external AI.
     */
    protected function extractHeuristicFacts(string $text): array
    {
        $facts = [];

        // Email regex
        if (preg_match_all('/[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}/', $text, $matches)) {
            $uniqueEmails = array_values(array_unique($matches[0]));
            if (!empty($uniqueEmails)) {
                $facts[] = [
                    'fact_key' => 'email',
                    'fact_name' => 'Email Address',
                    'fact_value' => $uniqueEmails[0],
                    'confidence_score' => 0.95,
                    'source_excerpt' => "Direct match: {$uniqueEmails[0]}",
                    'notes' => 'Matched regex pattern',
                ];
            }
        }

        // Phone regex
        if (preg_match_all('/(?:\+?\d{1,3}[-.\s]?)?\(?\d{2,5}\)?[-.\s]?\d{3,5}[-.\s]?\d{3,5}/', $text, $matches)) {
            $candidates = array_filter($matches[0], fn($p) => strlen(preg_replace('/\D/', '', $p)) >= 10);
            $uniquePhones = array_values(array_unique($candidates));
            if (!empty($uniquePhones)) {
                $facts[] = [
                    'fact_key' => 'phone',
                    'fact_name' => 'Phone Number',
                    'fact_value' => trim($uniquePhones[0]),
                    'confidence_score' => 0.90,
                    'source_excerpt' => "Direct match: " . trim($uniquePhones[0]),
                    'notes' => 'Matched phone regex pattern',
                ];
            }
        }

        return $facts;
    }

    /**
     * Return all verified facts for website compilation.
     */
    public function getVerifiedDictionary(Business $business): array
    {
        $analysis = $this->analyzeFacts($business);
        $dict = [];

        foreach ($analysis['confirmed'] as $k => $item) {
            $dict[$k] = $item['fact_value'];
        }

        // Baseline business attributes
        $dict['business_name'] = $business->name;
        $dict['category'] = $business->category;
        $dict['city'] = $dict['city'] ?? $business->city;
        $dict['state'] = $dict['state'] ?? $business->state;
        $dict['country'] = $dict['country'] ?? $business->country;
        $dict['phone'] = $dict['phone'] ?? $business->phone;
        $dict['email'] = $dict['email'] ?? $business->email;
        $dict['address'] = $dict['address'] ?? $business->address;
        $dict['description'] = $dict['description'] ?? $business->description;

        return $dict;
    }

    /**
     * Update direct business column if matching attribute exists.
     */
    protected function syncBusinessColumnIfApplicable(Business $business, string $key, string $val): void
    {
        if (in_array($key, ['phone', 'email', 'address', 'city', 'description'])) {
            $business->update([$key => $val]);
        }
    }
}
