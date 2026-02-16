<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Site;
use App\Models\Page;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_page_shows_not_published_when_no_published_page()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create([
            'user_id' => $user->id,
            'slug' => 'test-user'
        ]);
        
        // Create only draft page, no published
        Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'draft',
            'blocks' => []
        ]);

        $response = $this->get('/@test-user');
        
        $response->assertStatus(200);
        $response->assertSee('Coming Soon');
        $response->assertSee('This page hasn', false); // Use false to avoid HTML escaping
    }

    public function test_public_page_shows_published_content()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create([
            'user_id' => $user->id,
            'slug' => 'test-user',
            'name' => 'Test User',
            'bio' => 'Test bio content'
        ]);
        
        // Create published page with blocks
        $blocks = [
            [
                'id' => 'block-1',
                'type' => 'text',
                'enabled' => true,
                'props' => [
                    'content' => 'Welcome to my page!',
                    'textAlign' => 'center',
                    'fontSize' => '18',
                    'textColor' => '#000000'
                ]
            ],
            [
                'id' => 'block-2',
                'type' => 'links',
                'enabled' => true,
                'props' => [
                    'title' => 'My Links',
                    'links' => [
                        [
                            'text' => 'My Website',
                            'url' => 'https://example.com',
                            'backgroundColor' => '#3b82f6',
                            'textColor' => '#ffffff'
                        ]
                    ],
                    'buttonStyle' => 'rounded'
                ]
            ]
        ];
        
        Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'published',
            'blocks' => $blocks
        ]);

        $response = $this->get('/@test-user');
        
        $response->assertStatus(200);
        $response->assertSee('Test User');
        $response->assertSee('Test bio content');
        $response->assertSee('Welcome to my page!');
        $response->assertSee('My Links');
        $response->assertSee('My Website');
    }

    public function test_public_page_filters_disabled_blocks()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create([
            'user_id' => $user->id,
            'slug' => 'test-user'
        ]);
        
        $blocks = [
            [
                'id' => 'block-1',
                'type' => 'text',
                'enabled' => true,
                'props' => [
                    'content' => 'Visible content'
                ]
            ],
            [
                'id' => 'block-2',
                'type' => 'text',
                'enabled' => false,
                'props' => [
                    'content' => 'Hidden content'
                ]
            ]
        ];
        
        Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'published',
            'blocks' => $blocks
        ]);

        $response = $this->get('/@test-user');
        
        $response->assertStatus(200);
        $response->assertSee('Visible content');
        $response->assertDontSee('Hidden content');
    }

    public function test_public_page_returns_404_for_nonexistent_slug()
    {
        $response = $this->get('/@nonexistent-user');
        
        $response->assertStatus(404);
    }

    public function test_public_page_shows_most_recent_published_page()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create([
            'user_id' => $user->id,
            'slug' => 'test-user'
        ]);
        
        // Create first published page
        $firstPage = Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'published',
            'blocks' => [
                [
                    'id' => 'block-1',
                    'type' => 'text',
                    'enabled' => true,
                    'props' => ['content' => 'Old content']
                ]
            ],
            'updated_at' => now()->subHour()
        ]);
        
        // Create more recent published page
        $secondPage = Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'published',
            'blocks' => [
                [
                    'id' => 'block-1',
                    'type' => 'text',
                    'enabled' => true,
                    'props' => ['content' => 'New content']
                ]
            ],
            'updated_at' => now()
        ]);

        $response = $this->get('/@test-user');
        
        $response->assertStatus(200);
        $response->assertSee('New content');
        $response->assertDontSee('Old content');
    }
}