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
        Schema::create('dividend_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('securities_management_id')->constrained('securities_management')->onDelete('cascade');
            $table->foreignId('land_rental_contract_id')->nullable()->constrained('land_rental_contracts')->onDelete('set null');
            $table->date('dividend_date')->comment('Ngày nhận cổ tức');
            $table->decimal('dividend_rate', 10, 2)->comment('Tỷ lệ cổ tức (%)');
            
            // Deposited shares
            $table->bigInteger('deposited_quantity')->default(0)->comment('Lưu ký (số)');
            $table->decimal('deposited_amount_before_tax', 15, 2)->default(0)->comment('Tiền thanh toán trước thuế lưu ký');
            $table->decimal('deposited_tax_amount', 15, 2)->default(0)->comment('Thuế lưu ký');
            
            // Not deposited shares
            $table->bigInteger('not_deposited_quantity')->default(0)->comment('Chưa lưu ký (số)');
            $table->decimal('not_deposited_amount_before_tax', 15, 2)->default(0)->comment('Tiền thanh toán trước thuế chưa lưu ký');
            $table->decimal('not_deposited_tax_amount', 15, 2)->default(0)->comment('Thuế chưa lưu ký');
            
            // Payment information
            $table->date('payment_date')->nullable()->comment('Thời gian rút');
            $table->string('account_number')->nullable()->comment('Số tài khoản ngân hàng');
            $table->string('bank_name')->nullable()->comment('Tên ngân hàng');

            $table->text('notes')->nullable()->comment('Ghi chú');
            $table->timestamps();
            
            // Indexes
            $table->index('securities_management_id');
            $table->index('land_rental_contract_id');
            $table->index('dividend_date');
            $table->index('payment_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dividend_histories');
    }
};
