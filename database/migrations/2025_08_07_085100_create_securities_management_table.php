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
            $table->string('sid')->unique()->comment('Mã định dạng NĐT (SID)');
            $table->string('investor_code')->unique()->comment('Mã nhà đầu tư (Investor code)');
            $table->string('registration_number')->comment('Số ĐKSH');
            $table->date('issue_date')->comment('Ngày cấp');
            $table->text('address')->comment('Địa chỉ');
            $table->string('email')->nullable()->comment('Email');
            $table->string('phone')->nullable()->comment('Điện thoại');
            $table->string('nationality')->default('Việt Nam')->comment('Quốc tịch');
            $table->bigInteger('not_deposited_quantity')->default(0)->comment('Chưa lưu ký (số lượng)');
            $table->bigInteger('deposited_quantity')->default(0)->comment('Lưu ký (số lượng)');
            $table->text('notes')->nullable()->comment('Ghi chú');
            $table->tinyInteger('status')->default(1)->comment('Trạng thái: 1-hoạt động, 0-không hoạt động');
            
            $table->timestamps();
            
            // Indexes
            $table->index(['sid', 'investor_code']);
            $table->index('full_name');
            $table->index('status');
            $table->index('not_deposited_quantity');
            $table->index('deposited_quantity');
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
