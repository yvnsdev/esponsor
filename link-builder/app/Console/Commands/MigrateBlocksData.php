<?php

namespace App\Console\Commands;

use App\Models\Page;
use Illuminate\Console\Command;

class MigrateBlocksData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blocks:migrate {--dry-run : Run without saving changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate block data from old schema format to new schema format';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        if ($dryRun) {
            $this->info('🔍 Running in DRY-RUN mode - no changes will be saved');
        }

        $pages = Page::all();
        $totalPages = $pages->count();
        
        if ($totalPages === 0) {
            $this->info('No pages found to migrate.');
            return 0;
        }

        $this->info("Found {$totalPages} pages to process...");
        
        $migratedCount = 0;
        $skippedCount = 0;

        foreach ($pages as $page) {
            $blocks = $page->blocks ?? [];
            $hasChanges = false;
            $migratedBlocks = [];

            foreach ($blocks as $block) {
                $migratedBlock = $this->migrateBlock($block);
                
                if ($migratedBlock !== $block) {
                    $hasChanges = true;
                }
                
                $migratedBlocks[] = $migratedBlock;
            }

            if ($hasChanges) {
                $this->line("  Migrating page #{$page->id} (URL: {$page->url})");
                
                if (!$dryRun) {
                    $page->blocks = $migratedBlocks;
                    $page->save();
                }
                
                $migratedCount++;
            } else {
                $skippedCount++;
            }
        }

        $this->newLine();
        $this->info('✅ Migration complete!');
        $this->table(
            ['Status', 'Count'],
            [
                ['Migrated', $migratedCount],
                ['Skipped (already migrated)', $skippedCount],
                ['Total', $totalPages],
            ]
        );

        if ($dryRun) {
            $this->warn('⚠️  This was a DRY-RUN. Run without --dry-run to apply changes.');
        }

        return 0;
    }

    /**
     * Migrate a single block to the new schema format
     */
    private function migrateBlock(array $block): array
    {
        $type = $block['type'] ?? null;

        switch ($type) {
            case 'text':
                return $this->migrateTextBlock($block);
            case 'links':
                return $this->migrateLinksBlock($block);
            case 'image':
                return $this->migrateImageBlock($block);
            case 'video':
                return $this->migrateVideoBlock($block);
            default:
                return $block;
        }
    }

    /**
     * Migrate text block: content → title + body
     */
    private function migrateTextBlock(array $block): array
    {
        $data = $block['data'] ?? [];
        
        // Already migrated
        if (isset($data['title']) && isset($data['body'])) {
            return $block;
        }

        // Migrate from old format
        if (isset($data['content'])) {
            $content = $data['content'];
            
            // Try to split into title and body (first line = title, rest = body)
            $lines = explode("\n", $content, 2);
            
            $data['title'] = $lines[0] ?? '';
            $data['body'] = $lines[1] ?? '';
            $data['textAlign'] = $data['textAlign'] ?? 'left';
            
            // Remove old fields
            unset($data['content'], $data['fontSize'], $data['textColor']);
            
            $block['data'] = $data;
        }

        return $block;
    }

    /**
     * Migrate links block: text + backgroundColor → label + style + icon
     */
    private function migrateLinksBlock(array $block): array
    {
        $data = $block['data'] ?? [];
        
        // Already migrated
        if (isset($data['label']) && isset($data['style'])) {
            return $block;
        }

        // Migrate from old format
        if (isset($data['text'])) {
            $data['label'] = $data['text'];
            $data['url'] = $data['url'] ?? '#';
            $data['icon'] = 'link'; // Default icon
            
            // Map backgroundColor to style
            $bgColor = $data['backgroundColor'] ?? '#6366f1';
            $data['style'] = $this->mapColorToStyle($bgColor);
            
            // Remove old fields
            unset($data['text'], $data['backgroundColor'], $data['textColor'], $data['buttonStyle']);
            
            $block['data'] = $data;
        }

        return $block;
    }

    /**
     * Migrate image block: add caption if missing
     */
    private function migrateImageBlock(array $block): array
    {
        $data = $block['data'] ?? [];
        
        // Add caption field if missing
        if (!isset($data['caption'])) {
            $data['caption'] = '';
            $block['data'] = $data;
        }

        return $block;
    }

    /**
     * Migrate video block: no changes needed
     */
    private function migrateVideoBlock(array $block): array
    {
        return $block;
    }

    /**
     * Map old backgroundColor to new style value
     */
    private function mapColorToStyle(string $color): string
    {
        // Normalize color
        $color = strtolower(trim($color));
        
        // Map common colors to styles
        $colorMap = [
            '#6366f1' => 'primary',    // indigo-600
            '#6366f1ff' => 'primary',
            'indigo' => 'primary',
            '#64748b' => 'secondary',  // slate-600
            '#64748bff' => 'secondary',
            'slate' => 'secondary',
            'gray' => 'secondary',
            'grey' => 'secondary',
            'transparent' => 'ghost',
            '#00000000' => 'ghost',
        ];

        return $colorMap[$color] ?? 'primary';
    }
}
