<?php

namespace App\Services;

use App\Models\Business;
use App\Models\ExtractedFact;
use App\Models\FactConfirmation;
use Illuminate\Support\Collection;

class FactVerificationService
{
    /**
     * Required core fact keys for generating a complete business website.
     */
    public const REQUIRED_FACTS = [
        'phone' => 'Phone Number',
        'email' => 'Email Address',
        'address' => 'Physical Address',
        'city' => 'City',
        'operating_hours' => 'Operating Hours',
        'description' => 'Business Summary',
        'services' => 'Services / Offerings',
    ];

    /**
     * Analyze all extracted facts for a business, identify conflicts, and categorize statuses.
     */
    public function analyzeFacts(Business $business): array
    {
        $allFacts = ExtractedFact::where('business_id', $business->id)->get();
        $grouped = $allFacts->groupBy('fact_key');

        $verifiedFacts = [];
        $conflicts = [];
        $unverified = [];
        $missing = [];

        // Check required facts
        foreach (self::REQUIRED_FACTS as $key => $label) {
            $factsForKey = $grouped->get($key, collect());

            // Check if user confirmed one previously
            $confirmation = FactConfirmation::where('business_id', $business->id)
                ->where('fact_key', $key)
                ->latest()
                ->first();

            if ($confirmation) {
                $verifiedFacts[$key] = [
                    'fact_key' => $key,
                    'fact_name' => $label,
                    'fact_value' => $confirmation->confirmed_value,
                    'status' => 'user-confirmed',
                    'confirmed_at' => $confirmation->confirmed_at,
                    'resolution_type' => $confirmation->resolution_type,
                ];
                continue;
            }

            if ($factsForKey->isEmpty()) {
                // Check business table direct attribute
                $directVal = $business->{$key} ?? null;
                if (!empty($directVal)) {
                    $verifiedFacts[$key] = [
                        'fact_key' => $key,
                        'fact_name' => $label,
                        'fact_value' => is_array($directVal) ? json_encode($directVal) : (string)$directVal,
                        'status' => 'user-confirmed',
                        'source' => 'Direct Business Onboarding',
                    ];
                } else {
                    $missing[$key] = [
                        'fact_key' => $key,
                        'fact_name' => $label,
                        'status' => 'missing',
                        'prompt' => "Please provide the business {$label} to include in your website.",
                    ];
                }
                continue;
            }

            // Conflict detection: Multiple distinct values across sources
            $distinctValues = $factsForKey->pluck('fact_value')->map(fn($v) => trim((string)$v))->unique();

            if ($distinctValues->count() > 1) {
                $conflicts[$key] = [
                    'fact_key' => $key,
                    'fact_name' => $label,
                    'status' => 'conflicting',
                    'candidates' => $factsForKey->map(fn($f) => [
                        'id' => $f->id,
                        'value' => $f->fact_value,
                        'source_type' => $f->source_type,
                        'source_url' => $f->source_url,
                        'confidence' => $f->confidence_score,
                    ])->values()->all(),
                ];

                // Mark models as conflicting in DB
                ExtractedFact::whereIn('id', $factsForKey->pluck('id'))->update([
                    'verification_status' => 'conflicting',
                ]);
            } else {
                $single = $factsForKey->first();
                $verifiedFacts[$key] = [
                    'fact_key' => $key,
                    'fact_name' => $label,
                    'fact_value' => $single->fact_value,
                    'status' => 'source-supported',
                    'source_type' => $single->source_type,
                    'source_url' => $single->source_url,
                ];
            }
        }

        // Process other arbitrary facts (products, social links, founded year, etc.)
        foreach ($grouped as $key => $factsForKey) {
            if (isset(self::REQUIRED_FACTS[$key]) || isset($verifiedFacts[$key]) || isset($conflicts[$key])) {
                continue;
            }

            $distinct = $factsForKey->pluck('fact_value')->unique();
            if ($distinct->count() > 1) {
                $conflicts[$key] = [
                    'fact_key' => $key,
                    'fact_name' => $factsForKey->first()->fact_name ?? ucwords(str_replace('_', ' ', $key)),
                    'status' => 'conflicting',
                    'candidates' => $factsForKey->map(fn($f) => [
                        'id' => $f->id,
                        'value' => $f->fact_value,
                        'source_type' => $f->source_type,
                        'source_url' => $f->source_url,
                    ])->values()->all(),
                ];
            } else {
                $single = $factsForKey->first();
                $verifiedFacts[$key] = [
                    'fact_key' => $key,
                    'fact_name' => $single->fact_name,
                    'fact_value' => $single->fact_value,
                    'status' => 'source-supported',
                    'source_type' => $single->source_type,
                    'source_url' => $single->source_url,
                ];
            }
        }

        return [
            'verified_facts' => $verifiedFacts,
            'conflicts' => $conflicts,
            'missing' => $missing,
            'has_blocking_conflicts' => count($conflicts) > 0,
            'can_generate_website' => count($conflicts) === 0 && (count($missing) <= 2),
        ];
    }

    /**
     * Confirm a fact value and resolve any conflicts.
     */
    public function resolveConflict(Business $business, string $factKey, string $confirmedValue, int $userId, string $resolutionType = 'conflict_resolved'): FactConfirmation
    {
        // Record confirmation
        $confirmation = FactConfirmation::create([
            'business_id' => $business->id,
            'fact_key' => $factKey,
            'confirmed_value' => $confirmedValue,
            'resolution_type' => $resolutionType,
            'conflict_candidates' => ExtractedFact::where('business_id', $business->id)
                ->where('fact_key', $factKey)
                ->pluck('fact_value')
                ->all(),
            'confirmed_by' => $userId,
            'confirmed_at' => now(),
        ]);

        // Update ExtractedFact records
        ExtractedFact::where('business_id', $business->id)
            ->where('fact_key', $factKey)
            ->update([
                'verification_status' => 'user-confirmed',
                'is_confirmed' => true,
                'user_confirmed_value' => $confirmedValue,
            ]);

        // If it matches a direct business column, update business record too
        if (in_array($factKey, ['phone', 'email', 'address', 'city', 'description'])) {
            $business->update([$factKey => $confirmedValue]);
        }

        return $confirmation;
    }

    /**
     * Return all facts in an associative key-value map for website generation.
     */
    public function getVerifiedDictionary(Business $business): array
    {
        $analysis = $this->analyzeFacts($business);
        $dict = [];

        foreach ($analysis['verified_facts'] as $k => $item) {
            $dict[$k] = $item['fact_value'];
        }

        // Merge baseline business attributes if not yet in dict
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
}
