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
        Schema::create('storage_a_i_s', function (Blueprint $table) {
            $table->id();
            $table->string('name_ai');
            $table->string('slug_ai')->unique();
            $table->text('des')->nullable();
            $table->string('data')->nullable();
            $table->string('id_storage')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('storage_a_i_s');
    }
};
