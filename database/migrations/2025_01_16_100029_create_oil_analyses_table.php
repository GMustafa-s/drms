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
        Schema::create('oil_analyses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('well_id')->constrained()->cascadeOnDelete();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->string('sample_point')->nullable();
            $table->string('sample_date')->nullable();
            $table->string('api_gravity')->nullable();
            $table->string('pour_ptf')->nullable();
            $table->string('cloud_ptf')->nullable();
            $table->string('praffin')->nullable();
            $table->string('asphaltene')->nullable();
            $table->string('comments')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oil_analyses');
    }
};
