<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DividendRecord extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dividend_records';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'securities_management_id',
        'tax_rate',
        'deposited_shares_quantity',
        'deposited_amount_before_tax',
        'non_deposited_shares_quantity',
        'non_deposited_amount_before_tax',
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
        'payment_date' => 'date',
        'tax_rate' => 'decimal:4',
        'deposited_amount_before_tax' => 'decimal:2',
        'non_deposited_amount_before_tax' => 'decimal:2',
    ];

    /**
     * Get the securities management record that owns the dividend record.
     */
    public function securitiesManagement(): BelongsTo
    {
        return $this->belongsTo(SecuritiesManagement::class, 'securities_management_id');
    }

    /**
     * Get the total shares quantity.
     *
     * @return int
     */
    public function getTotalSharesQuantityAttribute(): int
    {
        return $this->deposited_shares_quantity + $this->non_deposited_shares_quantity;
    }

    /**
     * Get the total amount before tax.
     *
     * @return float
     */
    public function getTotalAmountBeforeTaxAttribute(): float
    {
        return $this->deposited_amount_before_tax + $this->non_deposited_amount_before_tax;
    }

    /**
     * Get the deposited amount after tax.
     *
     * @return float
     */
    public function getDepositedAmountAfterTaxAttribute(): float
    {
        return $this->deposited_amount_before_tax * (1 - $this->tax_rate);
    }

    /**
     * Get the non-deposited amount after tax.
     *
     * @return float
     */
    public function getNonDepositedAmountAfterTaxAttribute(): float
    {
        return $this->non_deposited_amount_before_tax * (1 - $this->tax_rate);
    }

    /**
     * Get the total amount after tax.
     *
     * @return float
     */
    public function getTotalAmountAfterTaxAttribute(): float
    {
        return $this->getTotalAmountBeforeTaxAttribute() * (1 - $this->tax_rate);
    }
}
