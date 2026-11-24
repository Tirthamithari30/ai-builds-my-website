<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\ExtractedFact;
use App\Models\Website;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * Display the user dashboard with real metrics, projects, and search filters.
     */
    public function index(Request $request): View
    {
        $user = Auth::user();

        // 1. Real Database Statistics
        $totalBusinesses = $user->businesses()->count();
        $businessIds = $user->businesses()->pluck('id');

        $totalWebsites = Website::whereIn('business_id', $businessIds)->count();
        $publishedWebsites = Website::whereIn('business_id', $businessIds)->where('is_published', true)->count();
        $draftWebsites = Website::whereIn('business_id', $businessIds)->where('is_published', false)->count();
        $totalFactsExtracted = ExtractedFact::whereIn('business_id', $businessIds)->count();

        // 2. Query Businesses with search and filtering
        $query = $user->businesses()->with(['latestWebsite', 'sources', 'files', 'extractedFacts']);

        if ($search = trim((string)$request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%")
                  ->orWhere('category', 'like', "%{$search}%");
            });
        }

        if ($category = $request->query('category')) {
            $query->where('category', $category);
        }

        if ($status = $request->query('status')) {
            if ($status === 'published') {
                $query->whereHas('latestWebsite', fn($q) => $q->where('is_published', true));
            } elseif ($status === 'draft') {
                $query->where(function ($q) {
                    $q->whereDoesntHave('latestWebsite')
                      ->orWhereHas('latestWebsite', fn($sub) => $sub->where('is_published', false));
                });
            }
        }

        $businesses = $query->latest('updated_at')->paginate(10)->withQueryString();

        return view('dashboard.index', [
            'user' => $user,
            'businesses' => $businesses,
            'stats' => [
                'total_businesses' => $totalBusinesses,
                'total_websites' => $totalWebsites,
                'published_websites' => $publishedWebsites,
                'draft_websites' => $draftWebsites,
                'total_facts' => $totalFactsExtracted,
            ],
            'filters' => [
                'search' => $request->query('search', ''),
                'category' => $request->query('category', ''),
                'status' => $request->query('status', ''),
            ],
        ]);
    }
}
