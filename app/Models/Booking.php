<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Booking extends Model
{
    protected $fillable = [
        'booking_code',
        'user_id',
        'tour_package_id',
        'visit_date',
        'guest_count',
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
     * Generate a unique booking code.
     */
    public static function generateBookingCode(string $date): string
    {
        $prefix = 'KDA-' . date('Ymd', strtotime($date));
        $lastBooking = static::where('booking_code', 'like', $prefix . '%')
            ->orderByDesc('id')
            ->first();

        $sequence = 1;
        if ($lastBooking) {
            $lastSequence = (int) substr($lastBooking->booking_code, -5);
            $sequence = $lastSequence + 1;
        }

        return $prefix . '-' . str_pad($sequence, 5, '0', STR_PAD_LEFT);
    }
}
