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
        Schema::create('fe_mn_counts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('well_id')->constrained()->cascadeOnDelete();
            $table->string('sample_point')->nullable();
            $table->string('sample_date')->nullable();
            $table->string('chem_used')->nullable();
            $table->string('fe')->nullable();
            $table->string('mn')->nullable();
            $table->string('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fe_mn_counts');
    }
};
