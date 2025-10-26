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
        'deposited_shares_quantity',
        'non_deposited_shares_quantity',
        'deposited_amount_before_tax',
        'non_deposited_amount_before_tax',
        'deposited_personal_income_tax',
        'non_deposited_personal_income_tax',
        'dividend_price_per_share',
        'dividend_percentage',
        'payment_date',
        'account_number',
        'bank_name',
        'notes',
        'payment_status',
        'transfer_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'payment_date' => 'date',
        'transfer_date' => 'datetime',
        'non_deposited_shares_quantity' => 'integer',
        'deposited_shares_quantity' => 'integer',
        'non_deposited_amount_before_tax' => 'decimal:2',
        'deposited_amount_before_tax' => 'decimal:2',
        'non_deposited_personal_income_tax' => 'decimal:2',
        'deposited_personal_income_tax' => 'decimal:2',
        'dividend_price_per_share' => 'decimal:2',
        'dividend_percentage' => 'decimal:4',
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
     * Get the total amount after tax.
     *
     * @return float
     */
    public function getTotalAmountAfterTaxAttribute(): float
    {
        $totalTax = $this->deposited_personal_income_tax + $this->non_deposited_personal_income_tax;
        return $this->getTotalAmountBeforeTaxAttribute() - $totalTax;
    }

    /**
     * Get the total personal income tax.
     *
     * @return float
     */
    public function getTotalPersonalIncomeTaxAttribute(): float
    {
        return $this->deposited_personal_income_tax + $this->non_deposited_personal_income_tax;
    }

    /**
     * Get the deposited amount after tax.
     *
     * @return float
     */
    public function getDepositedAmountAfterTaxAttribute(): float
    {
        return $this->deposited_amount_before_tax - $this->deposited_personal_income_tax;
    }

    /**
     * Get the non-deposited amount after tax.
     *
     * @return float
     */
    public function getNonDepositedAmountAfterTaxAttribute(): float
    {
        return $this->non_deposited_amount_before_tax - $this->non_deposited_personal_income_tax;
    }
}
