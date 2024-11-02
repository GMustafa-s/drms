<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('wells', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained('companies')->cascadeOnDelete()->cascadeOnUpdate();
            $table->string('lease');
            $table->string('chemical')->nullable();
            $table->string('gas_lift')->nullable();
            $table->string('chemical_type')->nullable();
            $table->string('rate') ->nullable();
            $table->string('ppg') ->nullable();
            $table->string('based_on')->nullable();
            $table->string('injection_point')->nullable();
            $table->string('comments')->nullable();
            $table->foreignId('site_id')->constrained('sites')->cascadeOnUpdate()->cascadeOnDelete();
            $table->Boolean('is_published')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('wells');
    }
};
