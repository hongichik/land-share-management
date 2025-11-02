<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('securities_management', function (Blueprint $table) {
            $table->id();
            $table->string('full_name')->comment('Họ và tên');
            $table->string('sid')->nullable()->comment('Mã định dạng NĐT (SID)');
            $table->string('investor_code')->nullable()->comment('Mã nhà đầu tư (Investor code)');
            $table->string('registration_number')->comment('Số ĐKSH');
            $table->date('issue_date')->comment('Ngày cấp');
            $table->text('address')->comment('Địa chỉ');
            $table->string('email')->nullable()->comment('Email');
            $table->string('phone')->nullable()->comment('Điện thoại');
            $table->string('nationality')->default('Việt Nam')->comment('Quốc tịch');
            $table->bigInteger('not_deposited_quantity')->default(0)->comment('Chưa lưu ký (số lượng)');
            $table->bigInteger('deposited_quantity')->default(0)->comment('Lưu ký (số lượng)');
            $table->bigInteger('slqmpb_chualk')->default(0)->comment('Số lượng quyền mua chưa lưu ký (SLQMPB_CHUALK)');
            $table->bigInteger('slqmpb_dalk')->default(0)->comment('Số lượng quyền mua đã lưu ký (SLQMPB_DALK)');
            $table->string('cntc')->nullable()->comment('Phân loại Cá nhân hay Tổ chức (CNTC)');
            $table->string('txnum')->nullable()->comment('Mã giao dịch (TXNUM)');
            $table->text('notes')->nullable()->comment('Ghi chú');

            $table->string('bank_account')->nullable()->comment('Số tài khoản ngân hàng');
            $table->string('bank_name')->nullable()->comment('Tên ngân hàng');
            
            $table->timestamps();
            
            // Indexes
            $table->index('full_name');
            $table->index('not_deposited_quantity');
            $table->index('deposited_quantity');
            $table->index('slqmpb_chualk');
            $table->index('slqmpb_dalk');
            $table->index('cntc');
            $table->index('txnum');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('securities_management');
    }
};
