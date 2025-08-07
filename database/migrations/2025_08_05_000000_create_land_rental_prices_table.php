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
        Schema::create('land_rental_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('land_rental_contract_id')->constrained('land_rental_contracts')->onDelete('cascade')->comment('Liên kết với bảng land_rental_contracts');
            $table->string('price_decision')->nullable()->comment('Quyết định giá thuê đất');
            $table->string('price_decision_file_path')->nullable()->comment('Đường dẫn file scan quyết định giá thuê đất');
            $table->json('price_period')->nullable()->comment('Thời gian áp dụng giá thuê (JSON)');
            $table->decimal('rental_price', 15, 2)->comment('Giá thuê đất');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_rental_prices');
    }
};
