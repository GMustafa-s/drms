<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('well_usages', function (Blueprint $table) {
            $table->id();
            $table->string('product_type');
            $table->string('product_name');
            $table->string('injection_location');
            $table->string('ppm');
            $table->string('quarts_per_day');
            $table->string('gallons_per_day');
            $table->string('gallons_per_month');
            $table->string('location');
            $table->string('program');
            $table->string('delivery_per_gallon');
            $table->string('ppg');
            $table->string('monthly_cost');
            $table->string('bwe');
            $table->string('bowg');
            $table->string('production_location');
            $table->string('bopd');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('well_usages');
    }
};
