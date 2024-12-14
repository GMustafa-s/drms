<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('well_usages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('well_id')->constrained('wells')->cascadeOnDelete()->cascadeOnUpdate();
            // Chemical Injection Points Section
            $table->string('product_type'); // Type of chemical product (e.g., Cor/Scale/FeS Inh)
            $table->string('product_name'); // Name of the product used for injection
            $table->string('injection_location'); // Injection location (e.g., Silverton 1H Flowline)

            // Continuous Applications Section
            $table->integer('ppm')->nullable()->default('0'); // Parts Per Million for the chemical
            $table->decimal('quarts_per_day', 10, 2); // Usage in quarts per day
            $table->decimal('gallons_per_day', 10, 2); // Usage in gallons per day
            $table->decimal('gallons_per_month', 10, 2); // Usage in gallons per month

            // Usage For Month Section
            // $table->string('usage_location'); // Location of chemical usage
            // $table->string('program'); // Program under which usage is categorized
            $table->integer('deliveries_gallons'); // Number of deliveries in gallons

            // Cost Based On Usage And Deliveries Section
            $table->decimal('ppg', 10, 2); // Price per gallon
            $table->decimal('monthly_cost', 10, 2); // Monthly cost based on usage
            $table->decimal('bwe', 5, 2); // BWE value in dollars
            $table->decimal('bowg', 5, 2); // BOWG value in dollars

            // Production (Daily Average) Section
            // $table->string('production_location'); // Production location (e.g., Silverton 1H)
            $table->integer('bopd'); // Barrels of Oil Per Day (BOPD)
            $table->decimal('mmcf', 10, 3); // Million Cubic Feet (MMCF)
            $table->integer('bwpd'); // Barrels of Water Per Day (BWPD)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('well_usages');
    }
};
