<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandRentalContract extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'land_rental_contracts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'contract_number',
        'contract_file_path',
        'rental_decision',
        'rental_decision_file_name',
        'rental_decision_file_path',
        'rental_zone',
        'rental_location',
        'export_tax',
        'land_tax_price',
        'area',
        'rental_period',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'area' => 'array',
        'rental_period' => 'array',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'area' => 'array',
            'rental_period' => 'array',
        ];
    }

    /**
     * Get the land rental prices for the contract.
     */
    public function landRentalPrices()
    {
        return $this->hasMany(LandRentalPrice::class);
    }

    /**
     * Get the payment histories for the contract.
     */
    public function paymentHistories()
    {
        return $this->hasMany(LandRentalPaymentHistory::class);
    }
}
