<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreBusinessRequest;
use App\Http\Requests\UpdateBusinessRequest;
use App\Models\Business;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\View\View;

class BusinessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): RedirectResponse
    {
        return redirect()->route('dashboard');
    }

    /**
     * Show the form for creating a new business project.
     */
    public function create(): View
    {
        return view('businesses.create');
    }

    /**
     * Store a newly created business in database.
     */
    public function store(StoreBusinessRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        // Convert operating hours / products / services if entered as text lines
        $operatingHours = !empty($validated['operating_hours']) ? trim($validated['operating_hours']) : null;
        $products = !empty($validated['products']) ? array_filter(array_map('trim', explode("\n", $validated['products']))) : null;
        $services = !empty($validated['services']) ? array_filter(array_map('trim', explode("\n", $validated['services']))) : null;

        $business = Business::create([
            'user_id' => Auth::id(),
            'name' => $validated['name'],
            'category' => $validated['category'] ?? 'other',
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? 'India',
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'description' => $validated['description'] ?? null,
            'operating_hours' => $operatingHours ? ['raw' => $operatingHours] : null,
            'products' => $products,
            'services' => $services,
            'additional_info' => $validated['additional_info'] ?? null,
            'status' => 'draft',
        ]);

        return redirect()->route('businesses.show', $business)
            ->with('success', "Business '{$business->name}' has been created! You can now upload documents or connect web sources.");
    }

    /**
     * Display the specified business details.
     */
    public function show(Business $business): View
    {
        Gate::authorize('view', $business);

        $business->load([
            'sources',
            'files',
            'extractedFacts',
            'factConfirmations',
            'latestWebsite.activePublication',
        ]);

        return view('businesses.show', [
            'business' => $business,
        ]);
    }

    /**
     * Show the form for editing the business.
     */
    public function edit(Business $business): View
    {
        Gate::authorize('update', $business);

        return view('businesses.edit', [
            'business' => $business,
        ]);
    }

    /**
     * Update the specified business in storage.
     */
    public function update(UpdateBusinessRequest $request, Business $business): RedirectResponse
    {
        Gate::authorize('update', $business);

        $validated = $request->validated();

        $operatingHours = !empty($validated['operating_hours']) ? trim($validated['operating_hours']) : null;
        $products = !empty($validated['products']) ? array_filter(array_map('trim', explode("\n", $validated['products']))) : null;
        $services = !empty($validated['services']) ? array_filter(array_map('trim', explode("\n", $validated['services']))) : null;

        $business->update([
            'name' => $validated['name'],
            'category' => $validated['category'] ?? 'other',
            'city' => $validated['city'] ?? null,
            'state' => $validated['state'] ?? null,
            'country' => $validated['country'] ?? 'India',
            'address' => $validated['address'] ?? null,
            'phone' => $validated['phone'] ?? null,
            'email' => $validated['email'] ?? null,
            'description' => $validated['description'] ?? null,
            'operating_hours' => $operatingHours ? ['raw' => $operatingHours] : null,
            'products' => $products,
            'services' => $services,
            'additional_info' => $validated['additional_info'] ?? null,
            'status' => $validated['status'] ?? $business->status,
        ]);

        return redirect()->route('businesses.show', $business)
            ->with('success', "Business profile for '{$business->name}' updated successfully.");
    }

    /**
     * Remove the specified business from storage.
     */
    public function destroy(Business $business): RedirectResponse
    {
        Gate::authorize('delete', $business);

        $name = $business->name;
        $business->delete();

        return redirect()->route('dashboard')
            ->with('success', "Business project '{$name}' was deleted successfully.");
    }
}
