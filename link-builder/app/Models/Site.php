<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'name',
        'slug',
        'bio',
        'avatar_url',
        'nav_logo_url',
        'primary_color',
        'secondary_color',
        'theme_mode',
        // Color customization fields
        'banner_color',
        'banner_image_url',
        'link_bg_color',
        'link_text_color',
        'social_hover_color',
        'cta_bg_color',
        'cta_text_color',
        'cta_button_color',
    ];

    /**
     * Get the user that owns the site.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the pages for the site.
     */
    public function pages(): HasMany
    {
        return $this->hasMany(Page::class);
    }

    /**
     * Get the published page for the site.
     */
    public function publishedPage(): HasMany
    {
        return $this->hasMany(Page::class)->where('status', 'published');
    }

    /**
     * Get the draft page for the site.
     */
    public function draftPage(): HasMany
    {
        return $this->hasMany(Page::class)->where('status', 'draft');
    }
}
