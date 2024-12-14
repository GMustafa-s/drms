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
        Schema::table('well_usages', function (Blueprint $table) {
            $table->dropColumn(['production_location','usage_location', 'program']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {

    }
};
