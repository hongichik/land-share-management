<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LandRentalPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'land_rental_contract_id',
        'price_decision',
        'price_decision_file_path',
        'price_period',
        'rental_price',
        'note',
    ];

    protected $casts = [
        'price_period' => 'array',
    ];

    public function landRentalContract()
    {
        return $this->belongsTo(LandRentalContract::class);
    }
}
