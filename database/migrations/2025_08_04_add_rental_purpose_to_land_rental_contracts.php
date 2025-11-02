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
            if (!Schema::hasColumn('land_rental_contracts', 'rental_purpose')) {
                $table->string('rental_purpose')->nullable()->comment('Mục đích thuê đất')->after('rental_location');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('land_rental_contracts', function (Blueprint $table) {
            if (Schema::hasColumn('land_rental_contracts', 'rental_purpose')) {
                $table->dropColumn('rental_purpose');
            }
        });
    }
};
