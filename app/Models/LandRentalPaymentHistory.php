<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LandRentalPaymentHistory extends Model
{
    use HasFactory;

    protected $fillable = [
        'land_rental_contract_id',
        'period',
        'payment_type',
        'amount',
        'payment_date',
        'notes',
    ];

    protected $casts = [
        'payment_date' => 'date',
        'amount' => 'decimal:2',
    ];

    // Constants for payment types
    public const PAYMENT_TYPE_ADVANCE = 1;        // Nộp trước
    public const PAYMENT_TYPE_ON_TIME = 2;        // Nộp đúng hạn
    public const PAYMENT_TYPE_EXEMPTION = 3;      // Miễn giảm

    public const PAYMENT_TYPES = [
        self::PAYMENT_TYPE_ADVANCE => 'Nộp trước',
        self::PAYMENT_TYPE_ON_TIME => 'Nộp đúng hạn',
        self::PAYMENT_TYPE_EXEMPTION => 'Miễn giảm',
    ];

    // Constants for periods
    public const PERIOD_1 = 1;
    public const PERIOD_2 = 2;

    public const PERIODS = [
        self::PERIOD_1 => 'Kỳ 1',
        self::PERIOD_2 => 'Kỳ 2',
    ];

    /**
     * Get the land rental contract that owns the payment history.
     */
    public function landRentalContract(): BelongsTo
    {
        return $this->belongsTo(LandRentalContract::class);
    }

    /**
     * Get payment type label
     */
    public function getPaymentTypeNameAttribute(): string
    {
        return self::PAYMENT_TYPES[$this->payment_type] ?? 'Unknown';
    }

    /**
     * Get period label
     */
    public function getPeriodNameAttribute(): string
    {
        return self::PERIODS[$this->period] ?? 'Unknown';
    }


    
}
