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
        Schema::table('securities_management', function (Blueprint $table) {
            $table->string('account_number')->nullable()->comment('Số tài khoản ngân hàng');
            $table->string('bank_name')->nullable()->comment('Tên ngân hàng');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('securities_management', function (Blueprint $table) {
            $table->dropColumn(['account_number', 'bank_name']);
        });
    }
};
