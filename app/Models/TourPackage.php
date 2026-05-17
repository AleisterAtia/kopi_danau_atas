<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class TourPackage extends Model
{
    use HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_hours',
        'daily_capacity',
        'facilities',
        'is_active',
        'is_featured',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PackageImage::class)->orderBy('sort_order');
    }

    public function bookings(): HasMany
    {
        return $this->hasMany(Booking::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    /**
     * Get remaining quota for a specific date.
     *
     * Counts pending bookings (created within the last hour, since older
     * pending bookings are auto-expired by the bookings:expire-pending
     * scheduled command) plus paid/confirmed bookings as occupying quota.
     * This prevents double-booking while a user is still in the payment flow.
     */
    public function getAvailableQuota(string $date): int
    {
        $bookedCount = $this->bookings()
            ->where('visit_date', $date)
            ->where(function ($query) {
                $query->whereIn('status', ['paid', 'confirmed', 'completed'])
                    ->orWhere(function ($q) {
                        $q->where('status', 'pending')
                            ->where('created_at', '>=', now()->subHour());
                    });
            })
            ->sum('guest_count');

        return max(0, $this->daily_capacity - (int) $bookedCount);
    }

    /**
     * Get number of guests already booked on a specific date.
     */
    public function getBookedCount(string $date): int
    {
        return (int) $this->bookings()
            ->where('visit_date', $date)
            ->where(function ($query) {
                $query->whereIn('status', ['paid', 'confirmed', 'completed'])
                    ->orWhere(function ($q) {
                        $q->where('status', 'pending')
                            ->where('created_at', '>=', now()->subHour());
                    });
            })
            ->sum('guest_count');
    }

    /**
     * Get average rating from approved reviews.
     */
    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->where('status', 'approved')->avg('rating') ?? 0, 1);
    }
}
