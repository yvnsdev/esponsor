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
            $table->string('primary_color')->default('#6366f1')->after('avatar_url');
            $table->string('secondary_color')->default('#8b5cf6')->after('primary_color');
            $table->string('theme_mode')->default('light')->after('secondary_color'); // light/dark
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sites', function (Blueprint $table) {
            $table->dropColumn(['primary_color', 'secondary_color', 'theme_mode']);
        });
    }
};
