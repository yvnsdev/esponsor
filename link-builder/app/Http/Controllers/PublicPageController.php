<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class PublicPageController extends Controller
{
    /**
     * Display a public page by site slug.
     */
    public function show(string $slug)
    {
        // Find the site by slug
        $site = Site::where('slug', $slug)->first();
        
        if (!$site) {
            abort(404, 'Site not found');
        }
        
        // Cache key for this public page
        $cacheKey = "public:site:{$slug}:published";
        
        // Cache the page data for 5 minutes (300 seconds)
        $cachedData = Cache::remember($cacheKey, 300, function () use ($site) {
            // Get the most recent published page for this site
            $publishedPage = $site->pages()
                ->where('status', 'published')
                ->latest('updated_at')
                ->first();
            
            // If no published page exists, return null data
            if (!$publishedPage) {
                return [
                    'site' => $site->toArray(),
                    'page' => null,
                    'blocks' => [],
                ];
            }
            
            // Filter only enabled blocks
            $enabledBlocks = collect($publishedPage->blocks)
                ->filter(function ($block) {
                    return $block['enabled'] ?? false;
                })
                ->values()
                ->all();
            
            return [
                'site' => $site->toArray(),
                'page' => $publishedPage->toArray(),
                'blocks' => $enabledBlocks,
            ];
        });
        
        // Convert arrays back to objects/models for view compatibility
        $site = $site->fresh(); // Get fresh model to avoid stale data
        $publishedPage = $cachedData['page'] ? (object) $cachedData['page'] : null;
        
        return view('public.page', [
            'site' => $site,
            'page' => $publishedPage,
            'blocks' => $cachedData['blocks'],
        ]);
    }
}