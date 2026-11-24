<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ExtractedFact;
use App\Services\AiService;
use App\Services\FactVerificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class FactVerificationController extends Controller
{
    public function __construct(
        protected FactVerificationService $factService,
        protected AiService $aiService
    ) {}

    /**
     * Display the Fact Review and Verification screen for a business.
     */
    public function index(Request $request, Business $business): View
    {
        Gate::authorize('view', $business);

        $analysis = $this->factService->analyzeFacts($business);
        $filter = $request->query('filter', 'all');

        return view('businesses.facts.index', [
            'business' => $business,
            'analysis' => $analysis,
            'activeFilter' => $filter,
            'aiConfigured' => $this->aiService->isConfigured(),
        ]);
    }

    /**
     * Confirm an extracted fact as accurate.
     */
    public function confirm(Request $request, ExtractedFact $fact): RedirectResponse
    {
        Gate::authorize('update', $fact->business);

        $this->factService->confirmFact($fact, auth()->id());

        return back()->with('success', "Fact '{$fact->fact_name}' has been verified and confirmed.");
    }

    /**
     * Edit and confirm an extracted fact with a custom value.
     */
    public function editAndConfirm(Request $request, ExtractedFact $fact): RedirectResponse
    {
        Gate::authorize('update', $fact->business);

        $validated = $request->validate([
            'fact_value' => ['required', 'string', 'max:5000'],
        ], [
            'fact_value.required' => 'Please provide a confirmed value.',
        ]);

        $this->factService->editAndConfirmFact($fact, $validated['fact_value'], auth()->id());

        return back()->with('success', "Fact '{$fact->fact_name}' updated and confirmed.");
    }

    /**
     * Reject an extracted fact.
     */
    public function reject(Request $request, ExtractedFact $fact): RedirectResponse
    {
        Gate::authorize('update', $fact->business);

        $reason = $request->input('reason');
        $this->factService->rejectFact($fact, auth()->id(), $reason);

        return back()->with('info', "Fact '{$fact->fact_name}' was marked as rejected.");
    }

    /**
     * Mark an extracted fact as outdated.
     */
    public function markOutdated(Request $request, ExtractedFact $fact): RedirectResponse
    {
        Gate::authorize('update', $fact->business);

        $this->factService->markOutdated($fact, auth()->id());

        return back()->with('info', "Fact '{$fact->fact_name}' marked as outdated.");
    }

    /**
     * Resolve a conflict across competing facts for the same attribute.
     */
    public function resolveConflict(Request $request, Business $business): RedirectResponse
    {
        Gate::authorize('update', $business);

        $validated = $request->validate([
            'fact_key' => ['required', 'string'],
            'confirmed_value' => ['required', 'string', 'max:5000'],
        ]);

        $this->factService->resolveConflict(
            business: $business,
            factKey: $validated['fact_key'],
            confirmedValue: $validated['confirmed_value'],
            userId: auth()->id()
        );

        return back()->with('success', "Conflict resolved for attribute '{$validated['fact_key']}'. Confirmed value saved.");
    }

    /**
     * Manually add missing business information.
     */
    public function addManual(Request $request, Business $business): RedirectResponse
    {
        Gate::authorize('update', $business);

        $validated = $request->validate([
            'fact_key' => ['required', 'string', 'max:100'],
            'fact_name' => ['required', 'string', 'max:255'],
            'fact_value' => ['required', 'string', 'max:5000'],
        ], [
            'fact_value.required' => 'Please enter the fact value.',
        ]);

        $this->factService->addManualFact(
            business: $business,
            factKey: $validated['fact_key'],
            factName: $validated['fact_name'],
            factValue: $validated['fact_value'],
            userId: auth()->id()
        );

        return back()->with('success', "Information '{$validated['fact_name']}' added as owner-verified.");
    }

    /**
     * Trigger extraction across all stored documents and crawled sources.
     */
    public function reextract(Request $request, Business $business): RedirectResponse
    {
        Gate::authorize('update', $business);

        $result = $this->factService->extractAllSources($business);

        return back()->with('success', "Extraction completed: {$result['new_facts_count']} new facts identified from sources.");
    }
}
