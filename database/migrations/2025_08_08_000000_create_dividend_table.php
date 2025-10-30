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
        Schema::create('dividends', function (Blueprint $table) {
            $table->id();
            $table->foreignId('securities_management_id')->constrained('securities_management')->onDelete('cascade');
            
            // Securities quantity
            $table->bigInteger('non_deposited_shares_quantity')->default(0)->comment('Số lượng chứng khoán chưa lưu ký');
            $table->bigInteger('deposited_shares_quantity')->default(0)->comment('Số lượng chứng khoán đã lưu ký');
            
            // Amount before tax
            $table->decimal('non_deposited_amount_before_tax', 15, 2)->default(0)->comment('Số tiền thanh toán trước thuế (chưa lưu ký)');
            $table->decimal('deposited_amount_before_tax', 15, 2)->default(0)->comment('Số tiền thanh toán trước thuế (đã lưu ký)');
            
            // Personal income tax
            $table->decimal('non_deposited_personal_income_tax', 15, 2)->default(0)->comment('Thuế thu nhập cá nhân (chưa lưu ký)');
            $table->decimal('deposited_personal_income_tax', 15, 2)->default(0)->comment('Thuế thu nhập cá nhân (đã lưu ký)');
            
            
            // Dividend price per share
            $table->decimal('dividend_price_per_share', 15, 2)->default(10000)->comment('Giá của 1 cổ phiếu khi chia cổ tức');
            $table->decimal('dividend_percentage', 5, 4)->default(0)->comment('Phần trăm cổ tức');
            
            // Payment information
            $table->date('payment_date')->nullable()->comment('Ngày thanh toán cổ tức');

            $table->string('account_number')->nullable()->comment('Số tài khoản ngân hàng');
            $table->string('bank_name')->nullable()->comment('Tên ngân hàng');

            $table->text('notes')->nullable()->comment('Ghi chú');
            $table->timestamps();
            $table->enum('payment_status', ['paid', 'unpaid'])->default('unpaid')->comment('Trạng thái trả tiền cổ tức');
            $table->datetime('transfer_date')->nullable()->comment('Thời gian chuyển tiền');

            // Indexes
            $table->index('securities_management_id');
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
