<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Models\Site;
use App\Models\Page;
use Illuminate\Console\Command;

class PublishPage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'page:publish {user_id : The ID of the user whose draft page should be published}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish the draft page for a specific user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $userId = $this->argument('user_id');
        
        // Find the user
        $user = User::find($userId);
        if (!$user) {
            $this->error("User with ID {$userId} not found.");
            return 1;
        }
        
        // Get user's site
        $site = $user->site;
        if (!$site) {
            $this->error("User {$user->name} doesn't have a site.");
            return 1;
        }
        
        // Get draft page
        $draftPage = $site->pages()->where('status', 'draft')->first();
        if (!$draftPage) {
            $this->error("No draft page found for user {$user->name}.");
            return 1;
        }
        
        // Publish the page
        if ($draftPage->publish()) {
            $this->info("✅ Published draft page for {$user->name}!");
            $this->info("📱 Public URL: " . url("/@{$site->slug}"));
            $this->line("");
            $this->line("Page contains " . count($draftPage->blocks) . " blocks:");
            
            foreach ($draftPage->blocks as $index => $block) {
                $status = $block['enabled'] ? '🟢' : '🔴';
                $this->line("  {$status} " . ($index + 1) . ". " . ucfirst($block['type']) . " Block");
            }
            
            return 0;
        } else {
            $this->error("Failed to publish page for {$user->name}.");
            return 1;
        }
    }
}
