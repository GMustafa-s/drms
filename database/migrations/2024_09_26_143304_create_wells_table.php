<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wells', function (Blueprint $table) {
            $table->id();
            $table->string('lease');
            $table->string('chemical');
            $table->string('chemical_type');
            $table->string('rate');
            $table->string('based_on');
            $table->string('injection_point');
            $table->string('comments');
            $table->foreignId('site_id')->constrained('sites')->cascadeOnUpdate()->cascadeOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wells');
    }
};
