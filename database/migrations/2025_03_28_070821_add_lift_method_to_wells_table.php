<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLiftMethodToWellsTable extends Migration
{
    public function up(): void
    {
        Schema::table('wells', function (Blueprint $table) {
            $table->string('lift_method')->nullable()->after('lease');
        });
    }

    public function down(): void
    {
        Schema::table('wells', function (Blueprint $table) {
            $table->dropColumn('lift_method');
        });
    }
}
