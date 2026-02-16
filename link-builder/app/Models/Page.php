<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Page extends Model
{
    use HasFactory;
    
    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'site_id',
        'status',
        'blocks',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array<string, mixed>
     */
    protected $attributes = [
        'blocks' => '[]',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'blocks' => 'array',
        ];
    }

    /**
     * Get the site that owns the page.
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Publish this page (change status from draft to published)
     */
    public function publish(): bool
    {
        if ($this->status === 'draft') {
            $this->status = 'published';
            return $this->save();
        }
        
        return false;
    }

    /**
     * Check if page is published
     */
    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    /**
     * Check if page is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
