<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Site;
use App\Models\Page;
use App\Blocks\BlockRegistry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BuilderUITest extends TestCase
{
    use RefreshDatabase;

    public function test_builder_ui_loads_with_demo_blocks()
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->get('/dashboard/builder');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Builder')
            ->has('site')
            ->has('pageDraft')
            ->has('pageDraft.blocks', 3) // Should have 3 demo blocks
            ->has('blockSchemas')
        );
    }

    public function test_builder_ui_loads_with_existing_blocks()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);
        
        $testBlocks = [
            BlockRegistry::createNewBlock('text'),
            BlockRegistry::createNewBlock('links'),
        ];
        
        $draftPage = Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'draft',
            'blocks' => $testBlocks
        ]);

        $this->actingAs($user);
        $response = $this->get('/dashboard/builder');
        
        $response->assertStatus(200);
        $response->assertInertia(fn ($page) => $page
            ->component('Builder')
            ->where('site.id', $site->id)
            ->where('pageDraft.id', $draftPage->id)
            ->where('pageDraft.blocks', $testBlocks)
            ->where('blockSchemas.text.label', 'Text Block')
            ->where('blockSchemas.links.label', 'Links Block')
        );
    }

    public function test_add_block_endpoint_works_with_valid_data()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);
        $draftPage = Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'draft',
            'blocks' => []
        ]);

        $newBlock = BlockRegistry::createNewBlock('text');

        $response = $this->actingAs($user)
            ->patchJson('/dashboard/builder/blocks', [
                'blocks' => [$newBlock]
            ]);

        $response->assertStatus(302);

        // Verify block was saved
        $draftPage->refresh();
        $this->assertCount(1, $draftPage->blocks);
        $this->assertEquals($newBlock['type'], $draftPage->blocks[0]['type']);
    }

    public function test_block_operations_maintain_order()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);
        
        $textBlock = BlockRegistry::createNewBlock('text');
        $linksBlock = BlockRegistry::createNewBlock('links');
        $imageBlock = BlockRegistry::createNewBlock('image');
        
        $initialBlocks = [$textBlock, $linksBlock, $imageBlock];
        
        $draftPage = Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'draft',
            'blocks' => $initialBlocks
        ]);

        // Test reordering by moving middle block to first position
        $reorderedBlocks = [$linksBlock, $textBlock, $imageBlock];

        $response = $this->actingAs($user)
            ->patchJson('/dashboard/builder/blocks', [
                'blocks' => $reorderedBlocks
            ]);

        $response->assertStatus(302);
        
        $draftPage->refresh();
        $this->assertEquals($linksBlock['id'], $draftPage->blocks[0]['id']);
        $this->assertEquals($textBlock['id'], $draftPage->blocks[1]['id']);
        $this->assertEquals($imageBlock['id'], $draftPage->blocks[2]['id']);
    }

    public function test_block_enabled_toggle_works()
    {
        $user = User::factory()->create();
        $site = Site::factory()->create(['user_id' => $user->id]);
        
        $textBlock = BlockRegistry::createNewBlock('text');
        $textBlock['enabled'] = false; // Disable block
        
        $draftPage = Page::factory()->create([
            'site_id' => $site->id,
            'status' => 'draft',
            'blocks' => [$textBlock]
        ]);

        $response = $this->actingAs($user)
            ->patchJson('/dashboard/builder/blocks', [
                'blocks' => [$textBlock]
            ]);

        $response->assertStatus(302);
        
        $draftPage->refresh();
        $this->assertFalse($draftPage->blocks[0]['enabled']);
    }
}