<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Shareholder extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'shareholders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        // Thông tin cơ bản
        'full_name',
        'sid',
        'investor_code',
        'registration_number',
        'issue_date',
        'address',
        'email',
        'phone',
        'nationality',
        'cntc',
        'txnum',
        'notes',
        
        // Thông tin chứng khoán
        'non_deposited_shares_quantity',
        'deposited_shares_quantity',
        'non_deposited_amount_before_tax',
        'deposited_amount_before_tax',
        'non_deposited_personal_income_tax',
        'deposited_personal_income_tax',
        'dividend_price_per_share',
        'dividend_percentage',
        
        // Thông tin thanh toán
        'payment_date',
        'account_number',
        'bank_name',
        'payment_status',
        'transfer_date',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'issue_date' => 'date',
        'payment_date' => 'date',
        'transfer_date' => 'datetime',
    ];
}
