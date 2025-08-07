<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DividendHistory extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'securities_management_id',
        'land_rental_contract_id',
        'dividend_date',
        'dividend_rate',
        'deposited_quantity',
        'deposited_amount_before_tax',
        'deposited_tax_amount',
        'not_deposited_quantity',
        'not_deposited_amount_before_tax',
        'not_deposited_tax_amount',
        'payment_date',
        'account_number',
        'bank_name',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'dividend_date' => 'date',
        'dividend_rate' => 'decimal:2',
        'deposited_quantity' => 'integer',
        'deposited_amount_before_tax' => 'decimal:2',
        'deposited_tax_amount' => 'decimal:2',
        'not_deposited_quantity' => 'integer',
        'not_deposited_amount_before_tax' => 'decimal:2',
        'not_deposited_tax_amount' => 'decimal:2',
        'payment_date' => 'date',
    ];

    /**
     * Get the securities management that owns the dividend history.
     */
    public function securitiesManagement(): BelongsTo
    {
        return $this->belongsTo(SecuritiesManagement::class);
    }

    /**
     * Get the land rental contract associated with the dividend history.
     */
    public function landRentalContract(): BelongsTo
    {
        return $this->belongsTo(LandRentalContract::class);
    }
}
