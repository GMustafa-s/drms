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
        // Create companies table
        Schema::create('companies', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED
            $table->string('name');
            $table->string('slug');
            $table->timestamps();
        });

        // Create pivot table for companies and users
        Schema::create('company_user', function (Blueprint $table) {
            $table->id(); // BIGINT UNSIGNED
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('company_user');
        Schema::dropIfExists('companies');
    }
};
