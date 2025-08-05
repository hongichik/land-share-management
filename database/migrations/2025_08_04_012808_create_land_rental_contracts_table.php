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
        Schema::create('land_rental_contracts', function (Blueprint $table) {
            $table->id();
            $table->string('contract_number')->unique()->comment('Số hợp đồng');
            $table->string('contract_file_path')->nullable()->comment('Đường dẫn file scan hợp đồng');
            $table->string('rental_decision')->nullable()->comment('Quyết định cho thuê đất');
            
            $table->string('rental_decision_file_name')->nullable()->comment('Tên file quyết định thuê đất');
            $table->string('rental_decision_file_path')->nullable()->comment('Đường dẫn file scan quyết định thuê');

            $table->string('rental_zone')->nullable()->comment('Vùng/khu vực thuê');
            $table->string('rental_location')->nullable()->comment('Vị trí thuê đất');

            $table->decimal('export_tax', 5, 4)->default(0.03)->comment('Thuế xuất (mặc định 0.03)');

            $table->json('area')->nullable()->comment('Thông tin diện tích (JSON)');

            $table->json('rental_period')->nullable()->comment('Thông tin thời gian thuê (JSON)');

            $table->text('notes')->nullable()->comment('Ghi chú/chú thích');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('land_rental_contracts');
    }
};
