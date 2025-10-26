<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SecuritiesManagement extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'securities_management';

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
    'not_deposited_quantity',
    'deposited_quantity',
    'slqmpb_chualk', // Số lượng quyền mua chưa lưu ký (SLQMPB_CHUALK)
    'slqmpb_dalk',   // Số lượng quyền mua đã lưu ký (SLQMPB_DALK)
    'cntc',          // Phân loại Cá nhân/Tổ chức (CNTC)
    'txnum',         // Mã giao dịch (TXNUM)
    'bank_account',
    'bank_name',
    'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
    'issue_date' => 'date',
    'not_deposited_quantity' => 'integer',
    'deposited_quantity' => 'integer',
    'slqmpb_chualk' => 'integer',
    'slqmpb_dalk' => 'integer',
    'status' => 'integer',
    ];


    /**
     * Scope lọc những nhà đầu tư đang hoạt động.
     */
    public function scopeActive($query)
    {
        return $query->where('status', 1);
    }

    /**
     * Scope lọc những nhà đầu tư chưa lưu ký (số lượng > 0).
     */
    public function scopeNotDeposited($query)
    {
        return $query->where('not_deposited_quantity', '>', 0);
    }

    /**
     * Scope lọc những nhà đầu tư đã lưu ký (số lượng > 0).
     */
    public function scopeDeposited($query)
    {
        return $query->where('deposited_quantity', '>', 0);
    }

    /**
     * Trả về trạng thái dạng text.
     */
    public function getStatusTextAttribute()
    {
        return $this->status == 1 ? 'Hoạt động' : 'Không hoạt động';
    }

    /**
     * Trả về trạng thái lưu ký dạng text.
     */
    public function getDepositStatusTextAttribute()
    {
        if ($this->not_deposited_quantity > 0) {
            return 'Chưa lưu ký';
        } elseif ($this->deposited_quantity > 0) {
            return 'Đã lưu ký';
        } else {
            return 'Chưa có dữ liệu';
        }
    }

    /**
     * Get the dividend records for this securities management.
     */
    public function dividendRecords(): HasMany
    {
        return $this->hasMany(DividendRecord::class, 'securities_management_id');
    }
}
