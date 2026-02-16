<?php

namespace App\Blocks;

use App\Blocks\Schemas\TextBlockSchema;
use App\Blocks\Schemas\LinksBlockSchema;
use App\Blocks\Schemas\ImageBlockSchema;
use App\Blocks\Schemas\VideoBlockSchema;
use App\Blocks\Schemas\SocialIconsBlockSchema;
use App\Blocks\Schemas\CtaBlockSchema;
use Ramsey\Uuid\Uuid;

class BlockRegistry
{
    /**
     * Available block schemas
     */
    private static array $schemas = [];

    /**
     * Initialize schemas
     */
    private static function initializeSchemas(): void
    {
        if (empty(self::$schemas)) {
            self::$schemas = [
                'text' => TextBlockSchema::getSchema(),
                'links' => LinksBlockSchema::getSchema(),
                'image' => ImageBlockSchema::getSchema(),
                'video' => VideoBlockSchema::getSchema(),
                'social-icons' => SocialIconsBlockSchema::getSchema(),
                'cta' => CtaBlockSchema::getSchema(),
            ];
        }
    }

    /**
     * Get all available block schemas
     * 
     * @return array
     */
    public static function getAllSchemas(): array
    {
        self::initializeSchemas();
        return self::$schemas;
    }

    /**
     * Get a specific block schema by type
     * 
     * @param string $type
     * @return array|null
     */
    public static function getSchemaByType(string $type): ?array
    {
        self::initializeSchemas();
        return self::$schemas[$type] ?? null;
    }

    /**
     * Get available block types
     * 
     * @return array
     */
    public static function getAvailableTypes(): array
    {
        self::initializeSchemas();
        return array_keys(self::$schemas);
    }

    /**
     * Check if a block type exists
     * 
     * @param string $type
     * @return bool
     */
    public static function typeExists(string $type): bool
    {
        self::initializeSchemas();
        return isset(self::$schemas[$type]);
    }

    /**
     * Create a new block instance with UUID and defaults
     * 
     * @param string $type
     * @return array|null
     */
    public static function createNewBlock(string $type): ?array
    {
        $schema = self::getSchemaByType($type);
        
        if (!$schema) {
            return null;
        }

        return [
            'id' => Uuid::uuid4()->toString(),
            'type' => $type,
            'enabled' => true,
            'props' => $schema['defaults'] ?? [],
        ];
    }

    /**
     * Validate block data against schema
     * 
     * @param array $blockData
     * @return bool
     */
    public static function validateBlock(array $blockData): bool
    {
        // Check required structure
        if (!isset($blockData['id']) || !isset($blockData['type']) || 
            !isset($blockData['enabled']) || !isset($blockData['props'])) {
            return false;
        }

        // Check if type exists
        if (!self::typeExists($blockData['type'])) {
            return false;
        }

        // Sanitize URLs in props
        $blockData['props'] = self::sanitizeBlockProps($blockData['props']);

        return true;
    }

    /**
     * Sanitize block properties (especially URLs)
     * 
     * @param array $props
     * @return array
     */
    public static function sanitizeBlockProps(array $props): array
    {
        foreach ($props as $key => $value) {
            // Sanitize URLs and prevent javascript: protocol
            if (is_string($value) && (str_contains($key, 'url') || str_contains($key, 'Url') || str_contains($key, 'link'))) {
                $value = filter_var($value, FILTER_SANITIZE_URL);
                
                // Block javascript: protocol and other dangerous protocols
                if (preg_match('/^(javascript|data|vbscript|file):/i', $value)) {
                    $value = '';
                }
                
                $props[$key] = $value;
            }
            
            // Recursively sanitize nested arrays
            if (is_array($value)) {
                $props[$key] = self::sanitizeBlockProps($value);
            }
        }
        
        return $props;
    }

    /**
     * Get schema catalog for frontend (simplified format)
     * 
     * @return array
     */
    public static function getCatalog(): array
    {
        self::initializeSchemas();
        
        $catalog = [];
        foreach (self::$schemas as $type => $schema) {
            $catalog[] = [
                'type' => $type,
                'label' => $schema['label'] ?? ucfirst($type),
                'category' => self::getBlockCategory($type),
                'icon' => $schema['icon'] ?? self::getBlockIcon($type),
                'description' => $schema['description'] ?? self::getBlockDescription($type),
            ];
        }

        return $catalog;
    }

    /**
     * Get block category for organizational purposes
     * 
     * @param string $type
     * @return string
     */
    private static function getBlockCategory(string $type): string
    {
        return match($type) {
            'text' => 'Content',
            'links' => 'Navigation',
            'image' => 'Media',
            'video' => 'Media',
            default => 'Other',
        };
    }

    /**
     * Get block icon for UI
     * 
     * @param string $type
     * @return string
     */
    private static function getBlockIcon(string $type): string
    {
        return match($type) {
            'text' => 'text',
            'links' => 'link',
            'image' => 'image',
            'video' => 'video',
            default => 'square',
        };
    }

    /**
     * Get block description
     * 
     * @param string $type
     * @return string
     */
    private static function getBlockDescription(string $type): string
    {
        return match($type) {
            'text' => 'Add text content with customizable styling',
            'links' => 'Create a collection of clickable links',
            'image' => 'Display images with optional links',
            'video' => 'Embed YouTube videos',
            default => 'Custom block',
        };
    }
}