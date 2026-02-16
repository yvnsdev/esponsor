<?php

namespace App\Http\Controllers;

use App\Blocks\BlockRegistry;
use App\Http\Requests\UpdateBlocksRequest;
use App\Models\Site;
use App\Models\Page;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class BuilderController extends Controller
{
    /**
     * Display the page builder dashboard.
     */
    public function index(): Response
    {
        $user = auth()->user();
        
        // Get or create user's site
        $site = $this->getOrCreateUserSite($user);
        
        // Get or create draft page for the site
        $pageDraft = $this->getOrCreateDraftPage($site);
        
        return Inertia::render('Builder', [
            'site' => $site->toArray(),
            'pageDraft' => $pageDraft->toArray(),
            'blockCatalog' => BlockRegistry::getCatalog(),
            'blockSchemas' => BlockRegistry::getAllSchemas(),
        ]);
    }

    /**
     * Update blocks in the draft page
     */
    public function updateBlocks(UpdateBlocksRequest $request)
    {
        $user = auth()->user();
        
        // Get user's site
        $site = $user->site;
        if (!$site) {
            return redirect()->back()->with('error', 'Site not found. Please refresh the page.');
        }
        
        // Get draft page
        $draftPage = $site->pages()->where('status', 'draft')->first();
        if (!$draftPage) {
            return redirect()->back()->with('error', 'Draft page not found. Please refresh the page.');
        }
        
        // Get validated blocks
        $blocks = $request->getValidatedBlocks();
        
        // Update blocks in the draft page
        $draftPage->update(['blocks' => $blocks]);
        
        return redirect()->back();
    }

    /**
     * Update profile settings
     */
    public function updateProfile(Request $request)
    {
        $user = auth()->user();
        
        // Get user's site
        $site = $user->site;
        if (!$site) {
            return redirect()->back()->with('error', 'Site not found.');
        }
        
        // Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'bio' => 'nullable|string|max:500',
            'avatar_url' => 'nullable|url|max:500',
            'nav_logo_url' => 'nullable|url|max:500',
            'theme_mode' => 'required|in:light,dark',
            'banner_color' => 'nullable|string|max:20',
            'banner_image_url' => 'nullable|url|max:500',
        ]);
        
        // Update site
        $site->update($validated);
        
        // Clear cache
        Cache::forget("public:site:{$site->slug}:published");
        
        return redirect()->back();
    }

    /**
     * Publish the current draft page
     */
    public function publish()
    {
        $user = auth()->user();
        
        // Get user's site
        $site = $user->site;
        if (!$site) {
            return redirect()->back()->with('publishError', 'Site not found. Please refresh the page.');
        }
        
        // Get draft page
        $draftPage = $site->pages()->where('status', 'draft')->first();
        if (!$draftPage) {
            return redirect()->back()->with('publishError', 'No draft page found to publish.');
        }
        
        // Check if there are any enabled blocks to publish
        $enabledBlocks = collect($draftPage->blocks)
            ->filter(fn($block) => $block['enabled'] ?? false)
            ->values()
            ->all();
            
        if (empty($enabledBlocks)) {
            return redirect()->back()->with('publishError', 'Cannot publish a page with no enabled blocks. Please enable at least one block first.');
        }
        
        // Create or update published page (upsert pattern)
        $publishedPage = $site->pages()->updateOrCreate(
            ['status' => 'published'],
            [
                'blocks' => $draftPage->blocks,
                'updated_at' => now()
            ]
        );
        
        // Invalidate cache for the public page
        Cache::forget("public:site:{$site->slug}:published");
        
        \Log::info("Published page for site @{$site->slug} and cleared cache");
        
        $publicUrl = route('public.page', ['slug' => $site->slug]);
        
        return redirect()->back()->with([
            'publicUrl' => $publicUrl,
        ]);
    }

    /**
     * Get or create user's site
     */
    private function getOrCreateUserSite($user): Site
    {
        // Check if user already has a site
        $site = $user->site;
        
        if ($site) {
            return $site;
        }
        
        // Generate unique slug for the new site
        $tempSlug = $this->generateTempSlug($user);
        
        // Create new site for the user
        $site = Site::create([
            'user_id' => $user->id,
            'name' => $user->name . "'s Site",
            'slug' => $tempSlug,
            'bio' => 'Welcome to my page! Find all my important links here.',
            'avatar_url' => 'https://ui-avatars.com/api/?name=' . urlencode($user->name) . '&color=7c3aed&background=fbbf24',
        ]);
        
        return $site;
    }

    /**
     * Get or create draft page for the site
     */
    private function getOrCreateDraftPage(Site $site): Page
    {
        $draftPage = $site->pages()->where('status', 'draft')->first();
        
        if (!$draftPage) {
            // Create demo blocks for first-time users
            $demoBlocks = [
                [
                    'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                    'type' => 'text',
                    'enabled' => true,
                    'props' => [
                        'content' => '✨ Welcome to my page! Here you\'ll find all my important links and updates.',
                        'textAlign' => 'center',
                        'fontSize' => '18',
                        'textColor' => '#374151',
                    ]
                ],
                [
                    'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                    'type' => 'links',
                    'enabled' => true,
                    'props' => [
                        'title' => 'My Links',
                        'links' => [
                            [
                                'text' => '🌐 My Website',
                                'url' => 'https://example.com',
                                'backgroundColor' => '#3b82f6',
                                'textColor' => '#ffffff',
                            ],
                            [
                                'text' => '📧 Contact Me',
                                'url' => 'mailto:hello@example.com',
                                'backgroundColor' => '#10b981',
                                'textColor' => '#ffffff',
                            ]
                        ],
                        'buttonStyle' => 'rounded',
                    ]
                ],
                [
                    'id' => \Ramsey\Uuid\Uuid::uuid4()->toString(),
                    'type' => 'image',
                    'enabled' => true,
                    'props' => [
                        'imageUrl' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=400&h=300&fit=crop',
                        'altText' => 'Beautiful landscape',
                        'width' => '80',
                        'alignment' => 'center',
                        'borderRadius' => '12',
                        'linkUrl' => '',
                    ]
                ]
            ];
            
            $draftPage = Page::create([
                'site_id' => $site->id,
                'status' => 'draft',
                'blocks' => $demoBlocks,
            ]);
        }
        
        return $draftPage;
    }

    /**
     * Generate temporary slug for new sites
     */
    private function generateTempSlug($user): string
    {
        $baseSlug = 'user-' . $user->id . '-' . time();
        
        // Ensure uniqueness
        $counter = 1;
        $slug = $baseSlug;
        
        while (Site::where('slug', $slug)->exists()) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }
        
        return $slug;
    }

    /**
     * Clear page cache for published page
     */
    private function clearPageCache(string $slug): void
    {
        // Clear cache for the published page
        $cacheKey = "public:site:{$slug}:published";
        Cache::forget($cacheKey);
        
        \Log::info("Cache cleared for public page: @{$slug}");
    }
}