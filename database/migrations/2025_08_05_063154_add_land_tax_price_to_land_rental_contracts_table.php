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
        Schema::table('land_rental_contracts', function (Blueprint $table) {
            $table->decimal('land_tax_price', 15, 2)->nullable()->after('export_tax')->comment('Đơn giá thuế đất');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('land_rental_contracts', function (Blueprint $table) {
            $table->dropColumn('land_tax_price');
        });
    }
};
