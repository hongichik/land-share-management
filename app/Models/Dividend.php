<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dividend extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'dividends';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
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
        'bank_account',
        'bank_name',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'issue_date' => 'date',
    ];

    /**
     * Get the dividend records for this dividend.
     */
    public function dividendRecords(): HasMany
    {
        return $this->hasMany(DividendRecord::class, 'dividend_id');
    }
}
