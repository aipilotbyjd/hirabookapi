<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class SponsoredAd extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'title',
        'description',
        'image_url',
        'target_url',
        'sponsor_name',
        'sponsor_logo',
        'cta_text',
        'background_color',
        'text_color',
        'is_active',
        'priority',
        'starts_at',
        'ends_at',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'is_active' => 'boolean',
        'priority' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'impressions_count' => 'integer',
        'clicks_count' => 'integer',
    ];

    /**
     * Get the events for the sponsored ad.
     */
    public function events(): HasMany
    {
        return $this->hasMany(SponsoredAdEvent::class);
    }

    /**
     * Get the impressions for the sponsored ad.
     */
    public function impressions(): HasMany
    {
        return $this->hasMany(SponsoredAdEvent::class)->where('event_type', 'impression');
    }

    /**
     * Get the clicks for the sponsored ad.
     */
    public function clicks(): HasMany
    {
        return $this->hasMany(SponsoredAdEvent::class)->where('event_type', 'click');
    }

    /**
     * Scope a query to only include active ads.
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($query) {
                $query->whereNull('starts_at')
                    ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>=', now());
            });
    }

    /**
     * Delete the ad image from storage.
     *
     * @return bool
     */
    public function deleteImage(): bool
    {
        if ($this->image_url && Storage::disk('public')->exists($this->getImagePath())) {
            return Storage::disk('public')->delete($this->getImagePath());
        }

        return false;
    }

    /**
     * Delete the sponsor logo from storage.
     *
     * @return bool
     */
    public function deleteSponsorLogo(): bool
    {
        if ($this->sponsor_logo && Storage::disk('public')->exists($this->getSponsorLogoPath())) {
            return Storage::disk('public')->delete($this->getSponsorLogoPath());
        }

        return false;
    }

    /**
     * Get the path of the image relative to the storage disk.
     *
     * @return string|null
     */
    public function getImagePath(): ?string
    {
        if (!$this->image_url) {
            return null;
        }

        // Extract the path from the URL
        $path = str_replace('/storage/', '', $this->image_url);
        return $path;
    }

    /**
     * Get the path of the sponsor logo relative to the storage disk.
     *
     * @return string|null
     */
    public function getSponsorLogoPath(): ?string
    {
        if (!$this->sponsor_logo) {
            return null;
        }

        // Extract the path from the URL
        $path = str_replace('/storage/', '', $this->sponsor_logo);
        return $path;
    }

    /**
     * Delete all associated files when the model is being deleted.
     */
    protected static function booted()
    {
        static::deleting(function ($sponsoredAd) {
            $sponsoredAd->deleteImage();
            $sponsoredAd->deleteSponsorLogo();
        });
    }

    /**
     * Increment the impressions count.
     */
    public function incrementImpressions(): void
    {
        $this->increment('impressions_count');
    }

    /**
     * Increment the clicks count.
     */
    public function incrementClicks(): void
    {
        $this->increment('clicks_count');
    }
}
