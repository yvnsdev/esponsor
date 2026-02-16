<?php

namespace App\Console\Commands;

use App\Blocks\BlockRegistry;
use Illuminate\Console\Command;

class GenerateExampleBlocks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'blocks:generate-examples 
                            {--type= : Generate specific block type} 
                            {--all : Generate all block types}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate example blocks for testing and development';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if ($this->option('all')) {
            $this->generateAllBlocks();
        } elseif ($this->option('type')) {
            $this->generateBlock($this->option('type'));
        } else {
            $this->showMenu();
        }
    }

    private function showMenu()
    {
        $this->info('Block Generator Menu');
        $this->line('==================');
        
        $types = BlockRegistry::getAvailableTypes();
        $this->table(['Type', 'Label', 'Category'], 
            collect(BlockRegistry::getCatalog())->map(function($block) {
                return [$block['type'], $block['label'], $block['category']];
            })->toArray()
        );

        $choice = $this->choice('Select block type to generate', 
            array_merge($types, ['all']));

        if ($choice === 'all') {
            $this->generateAllBlocks();
        } else {
            $this->generateBlock($choice);
        }
    }

    private function generateAllBlocks()
    {
        $types = BlockRegistry::getAvailableTypes();
        $this->info("Generating examples for all {count($types)} block types...");
        
        foreach ($types as $type) {
            $this->generateBlock($type);
        }
    }

    private function generateBlock(string $type)
    {
        $block = BlockRegistry::createNewBlock($type);
        
        if (!$block) {
            $this->error("Block type '{$type}' not found!");
            return;
        }

        $this->info("Generated {$type} block:");
        $this->line(json_encode($block, JSON_PRETTY_PRINT));
        $this->newLine();
    }
}
