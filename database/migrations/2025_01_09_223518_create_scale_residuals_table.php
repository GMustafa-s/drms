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
        Schema::create('scale_residuals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('well_id')->constrained()->cascadeOnDelete();
            $table->string('sample_point')->nullable();
            $table->string('sample_date')->nullable();
            $table->string('chem_used')->nullable();
            $table->string('po4')->nullable();
            $table->string('ppm')->nullable();
            $table->string('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scale_residuals');
    }
};
