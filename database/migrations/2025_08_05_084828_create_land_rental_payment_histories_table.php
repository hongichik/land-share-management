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
        Schema::create('land_rental_payment_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_rental_contract_id')->constrained('land_rental_contracts')->onDelete('cascade')->comment('ID hợp đồng thuê đất');
            $table->tinyInteger('period')->comment('Kỳ nộp (1 hoặc 2)');
            $table->tinyInteger('payment_type')->comment('Loại nộp: 1-nộp trước, 2-nộp đúng hạn, 3-miễn giảm');
            $table->decimal('amount', 15, 2)->comment('Số tiền nộp');
            $table->date('payment_date')->comment('Ngày nộp tiền');
            $table->text('notes')->nullable()->comment('Ghi chú');
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_rental_payment_histories');
    }
};
