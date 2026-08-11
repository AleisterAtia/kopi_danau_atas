<?php

namespace App\Models;

use App\Exceptions\PackageHasBookingsException;
use App\Models\Concerns\HasAutoTranslation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Spatie\Translatable\HasTranslations;

class TourPackage extends Model
{
    use HasAutoTranslation, HasFactory, HasSlug, HasTranslations;

    /** Columns stored as {"id": "...", "en": "..."} and resolved per app locale. */
    public array $translatable = ['name', 'description', 'facilities'];

    protected $fillable = [
        'name',
        'slug',
        'category_id',
        'description',
        'price',
        'duration_hours',
        'daily_capacity',
        'facilities',
        'is_active',
        'is_featured',
    ];

    /**
     * Block hard-deleting a package that still has bookings so its financial
     * history (bookings + payments) is never cascade-wiped. Admins should
     * deactivate (`is_active = false`) such packages instead. Enforced here at
     * the model layer so it holds regardless of the DB driver (the prod FK is
     * also RESTRICT — see the restrict_tour_package_booking_fk migration).
     */
    protected static function booted(): void
    {
        static::deleting(function (TourPackage $package) {
            if ($package->bookings()->exists()) {
                throw new PackageHasBookingsException(
                    "Paket \"{$package->name}\" tidak dapat dihapus karena masih memiliki booking. ".
                    "Nonaktifkan paket (matikan 'Active') untuk menyembunyikannya tanpa menghapus riwayat."
                );
            }
        });
    }

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
            ->saveSlugsTo('slug')
            // Keep the permalink stable: `name` is now translatable, so
            // regenerating on update would rebuild the slug from whichever
            // locale is active when an admin edits a translation.
            ->doNotGenerateSlugsOnUpdate();
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PackageCategory::class, 'category_id');
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
     * scheduled command) plus paid/confirmed/completed bookings as
     * occupying quota. This prevents double-booking while a user is
     * still in the payment flow.
     */
    public function getAvailableQuota(string $date): int
    {
        return max(0, $this->daily_capacity - $this->getBookedCount($date));
    }

    /**
     * Get number of guests already booked on a specific date.
     */
    public function getBookedCount(string $date): int
    {
        return (int) $this->bookings()
            ->whereDate('visit_date', $date)
            ->occupyingQuota()
            ->sum('guest_count');
    }

    /**
     * Get average rating from all reviews.
     *
     * If the controller pre-loaded the aggregate via `withAvg('reviews',
     * 'rating')`, Eloquent exposes it as `reviews_avg_rating` and we use
     * it directly — saving an aggregate query per package on listings.
     * Falls back to a fresh query otherwise so callers that don't eager
     * load still work.
     *
     * Reviews are auto-published, so every review contributes to the
     * average.
     */
    public function getAverageRatingAttribute(): float
    {
        // `withAvg('reviews','rating')` exposes the aggregate at the
        // virtual column `reviews_avg_rating` (null when no reviews).
        // Detect the eager-loaded form by inspecting the attribute bag
        // directly (so we don't accidentally recurse into the accessor).
        $attributes = $this->getAttributes();
        if (array_key_exists('reviews_avg_rating', $attributes)) {
            return round((float) ($attributes['reviews_avg_rating'] ?? 0), 1);
        }

        // Fallback for callers that didn't eager-load the aggregate.
        return round((float) ($this->reviews()->avg('rating') ?? 0), 1);
    }
}
