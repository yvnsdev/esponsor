<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            // Banner customization
            $table->string('banner_color')->nullable();
            $table->string('banner_image_url')->nullable();
            
            // Links customization
            $table->string('link_bg_color')->nullable();
            $table->string('link_text_color')->nullable();
            
            // Social icons customization
            $table->string('social_hover_color')->nullable();
            
            // CTA customization
            $table->string('cta_bg_color')->nullable();
            $table->string('cta_text_color')->nullable();
            $table->string('cta_button_color')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn([
                'banner_color',
                'banner_image_url',
                'link_bg_color',
                'link_text_color',
                'social_hover_color',
                'cta_bg_color',
                'cta_text_color',
                'cta_button_color'
            ]);
        });
    }
};
