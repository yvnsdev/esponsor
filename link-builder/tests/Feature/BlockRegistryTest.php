<?php

namespace Tests\Feature;

use App\Blocks\BlockRegistry;
use Tests\TestCase;

class BlockRegistryTest extends TestCase
{
    public function test_can_get_all_schemas()
    {
        $schemas = BlockRegistry::getAllSchemas();
        
        $this->assertCount(6, $schemas);
        $this->assertArrayHasKey('text', $schemas);
        $this->assertArrayHasKey('links', $schemas);
        $this->assertArrayHasKey('image', $schemas);
        $this->assertArrayHasKey('video', $schemas);
        $this->assertArrayHasKey('social-icons', $schemas);
        $this->assertArrayHasKey('cta', $schemas);
    }

    public function test_can_get_schema_by_type()
    {
        $textSchema = BlockRegistry::getSchemaByType('text');
        
        $this->assertIsArray($textSchema);
        $this->assertEquals('text', $textSchema['type']);
        $this->assertEquals('Text Block', $textSchema['label']);
        $this->assertArrayHasKey('defaults', $textSchema);
        $this->assertArrayHasKey('fields', $textSchema);
    }

    public function test_can_create_new_block()
    {
        $block = BlockRegistry::createNewBlock('text');
        
        $this->assertIsArray($block);
        $this->assertEquals('text', $block['type']);
        $this->assertEquals(true, $block['enabled']);
        $this->assertIsString($block['id']);
        $this->assertArrayHasKey('props', $block);
    }

    public function test_can_get_catalog()
    {
        $catalog = BlockRegistry::getCatalog();
        
        $this->assertCount(6, $catalog);
        
        foreach ($catalog as $item) {
            $this->assertArrayHasKey('type', $item);
            $this->assertArrayHasKey('label', $item);
            $this->assertArrayHasKey('category', $item);
            $this->assertArrayHasKey('icon', $item);
            $this->assertArrayHasKey('description', $item);
        }
    }

    public function test_validates_block_structure()
    {
        $validBlock = [
            'id' => 'test-id',
            'type' => 'text',
            'enabled' => true,
            'props' => []
        ];
        
        $this->assertTrue(BlockRegistry::validateBlock($validBlock));
        
        $invalidBlock = [
            'type' => 'text',
            'enabled' => true
        ];
        
        $this->assertFalse(BlockRegistry::validateBlock($invalidBlock));
    }
}