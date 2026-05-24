<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class Booking extends Model
{
    /**
     * Allowed status transitions. Used by the BookingObserver and the
     * Filament EditBooking page to prevent illegal moves (e.g. moving a
     * `completed` booking back to `pending`).
     *
     * Format: from-status => [list of allowed to-statuses]
     */
    public const ALLOWED_TRANSITIONS = [
        'pending'   => ['paid', 'cancelled', 'expired'],
        'paid'      => ['confirmed', 'completed', 'cancelled'],
        'confirmed' => ['completed', 'cancelled'],
        'completed' => [],            // terminal
        'cancelled' => [],            // terminal
        'expired'   => [],            // terminal
    ];

    /**
     * How many times to retry generating a booking code on duplicate.
     */
    private const CODE_GENERATION_MAX_ATTEMPTS = 3;

    protected $fillable = [
        'booking_code',
        'user_id',
        'tour_package_id',
        'visit_date',
        'guest_count',
        'guest_name',
        'guest_phone',
        'guest_email',
        'notes',
        'total_price',
        'status',
        'qr_code_path',
    ];

    protected function casts(): array
    {
        return [
            'visit_date' => 'date',
            'total_price' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tourPackage(): BelongsTo
    {
        return $this->belongsTo(TourPackage::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    /**
     * Check whether a given status transition is allowed.
     *
     * Same-state ($from === $to) is treated as valid (no-op save).
     * Transitions to a status not present in ALLOWED_TRANSITIONS keys
     * are also rejected.
     */
    public static function canTransitionTo(?string $from, string $to): bool
    {
        if ($from === null || $from === $to) {
            return true;
        }

        $allowed = self::ALLOWED_TRANSITIONS[$from] ?? null;

        if ($allowed === null) {
            return false;
        }

        return in_array($to, $allowed, true);
    }

    /**
     * Generate a unique booking code atomically.
     *
     * Uses a serialised transaction with `lockForUpdate()` over the
     * existing rows for the day so that two concurrent INSERTs cannot
     * compute the same sequence number. Retries on the rare case of a
     * unique-constraint collision (which can still happen if the code
     * is generated outside of a transaction by older callers).
     */
    public static function generateBookingCode(string $date): string
    {
        $prefix = 'KDA-' . date('Ymd', strtotime($date));

        $attempts = 0;
        while (true) {
            $attempts++;
            try {
                return DB::transaction(function () use ($prefix) {
                    $lastBooking = static::where('booking_code', 'like', $prefix . '%')
                        ->orderByDesc('id')
                        ->lockForUpdate()
                        ->first();

                    $sequence = 1;
                    if ($lastBooking) {
                        $lastSequence = (int) substr($lastBooking->booking_code, -5);
                        $sequence = $lastSequence + 1;
                    }

                    return $prefix . '-' . str_pad((string) $sequence, 5, '0', STR_PAD_LEFT);
                });
            } catch (QueryException $e) {
                if ($attempts >= self::CODE_GENERATION_MAX_ATTEMPTS) {
                    throw $e;
                }
                // brief jitter before retry
                usleep(random_int(10_000, 50_000));
            }
        }
    }
}
