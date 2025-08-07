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
        Schema::create('dividend_records', function (Blueprint $table) {
            $table->id();
            $table->foreignId('securities_management_id')->constrained('securities_management')->onDelete('cascade');
            $table->decimal('tax_rate', 5, 4)->default(0.05)->comment('Tỷ lệ thuế áp dụng');
            
            // Deposited shares
            $table->bigInteger('deposited_shares_quantity')->default(0)->comment('Số lượng cổ phiếu đã lưu ký');
            $table->decimal('deposited_amount_before_tax', 15, 2)->default(0)->comment('Giá trị cổ tức trước thuế (đã lưu ký)');
            
            // Not deposited shares
            $table->bigInteger('non_deposited_shares_quantity')->default(0)->comment('Số lượng cổ phiếu chưa lưu ký');
            $table->decimal('non_deposited_amount_before_tax', 15, 2)->default(0)->comment('Giá trị cổ tức trước thuế (chưa lưu ký)');
            
            // Payment information
            $table->date('payment_date')->nullable()->comment('Ngày thanh toán cổ tức');
            $table->string('account_number')->nullable()->comment('Số tài khoản ngân hàng');
            $table->string('bank_name')->nullable()->comment('Tên ngân hàng');

            $table->text('notes')->nullable()->comment('Ghi chú');
            $table->timestamps();
            
            // Indexes
            $table->index('securities_management_id');
            $table->index('dividend_date');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dividend_records');
    }
};
