<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Site;
use App\Models\Page;
use App\Blocks\BlockRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_creates_site_and_draft_page_for_new_user()
    {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)->get('/dashboard/builder');
        
        $response->assertStatus(200);
        
        // Check that site was created
        $site = $user->fresh()->site;
        $this->assertNotNull($site);
        $this->assertEquals($user->name . "'s Site", $site->name);
        $this->assertStringStartsWith('user-' . $user->id, $site->slug);
        
        // Check that draft page was created with demo blocks
        $draftPage = $site->pages()->where('status', 'draft')->first();
        $this->assertNotNull($draftPage);
        $this->assertIsArray($draftPage->blocks);
        $this->assertCount(3, $draftPage->blocks); // Should have 3 demo blocks
        
        // Verify demo block types
        $blockTypes = array_column($draftPage->blocks, 'type');
        $this->assertContains('text', $blockTypes);
        $this->assertContains('links', $blockTypes);
        $this->assertContains('image', $blockTypes);
        
        // Check Inertia props
        $response->assertInertia(fn ($page) => $page
            ->component('Builder')
            ->has('site')
            ->has('pageDraft')
            ->has('blockCatalog')
            ->has('blockSchemas')
            ->where('site.id', $site->id)
            ->where('pageDraft.id', $draftPage->id)
        );
    }

    public function test_index_uses_existing_site_and_draft_page()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create([
            'user_id' => $user->id,
            'name' => 'Existing Site',
            'slug' => 'existing-site'
        ]);
        $draftPage = Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'draft',
            'blocks' => [['id' => 'test-1', 'type' => 'text', 'enabled' => true, 'props' => []]]
        ]);
        
        $response = $this->actingAs($user)->get('/dashboard/builder');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->where('site.id', $site->id)
            ->where('site.name', 'Existing Site')
            ->where('pageDraft.id', $draftPage->id)
            ->where('pageDraft.blocks', $draftPage->blocks)
        );
    }

    public function test_update_blocks_successfully()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);
        $draftPage = Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'draft',
            'blocks' => []
        ]);

        $blocks = [
            BlockRegistry::createNewBlock('text'),
            BlockRegistry::createNewBlock('links'),
        ];

        $response = $this->actingAs($user)
            ->patchJson('/dashboard/builder/blocks', [
                'blocks' => $blocks
            ]);

        $response->assertStatus(302);

        // Check that blocks were saved
        $draftPage->refresh();
        $this->assertCount(2, $draftPage->blocks);
        $this->assertEquals($blocks[0]['id'], $draftPage->blocks[0]['id']);
    }

    public function test_update_blocks_validation_fails_for_invalid_structure()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);
        $draftPage = Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'draft'
        ]);

        $invalidBlocks = [
            ['type' => 'text'], // Missing required fields
        ];

        $response = $this->actingAs($user)
            ->patchJson('/dashboard/builder/blocks', [
                'blocks' => $invalidBlocks
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
    }

    public function test_update_blocks_validation_fails_for_invalid_block_type()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);
        $draftPage = Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'draft'
        ]);

        $invalidBlocks = [
            [
                'id' => 'test-1',
                'type' => 'invalid_type',
                'enabled' => true,
                'props' => []
            ]
        ];

        $response = $this->actingAs($user)
            ->patchJson('/dashboard/builder/blocks', [
                'blocks' => $invalidBlocks
            ]);

        $response->assertStatus(422);
        $response->assertJsonStructure(['message', 'errors']);
        // Check that validation caught the invalid block type
        $this->assertStringContainsString('invalid_type', $response->getContent());
    }

    public function test_update_blocks_requires_authentication()
    {
        $response = $this->patchJson('/dashboard/builder/blocks', [
            'blocks' => []
        ]);

        $response->assertStatus(401);
    }

    public function test_update_blocks_accepts_empty_array()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);
        $draftPage = Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'draft',
            'blocks' => [BlockRegistry::createNewBlock('text')]
        ]);

        // Delete all blocks (send empty array)
        $response = $this->actingAs($user)
            ->patchJson('/dashboard/builder/blocks', [
                'blocks' => []
            ]);

        $response->assertStatus(302);
        
        // Check that blocks were cleared
        $draftPage->refresh();
        $this->assertCount(0, $draftPage->blocks);
        $this->assertEquals([], $draftPage->blocks);
    }
}
