<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('areas', function (Blueprint $table) {
            $table->id(); // Creates 'id' as unsignedBigInteger by default
            $table->string('name');
            $table->text('description')->nullable();
            $table->unsignedBigInteger('company_id'); // Match data type with 'companies.id'

            // Create the foreign key constraint
            $table->foreign('company_id')
                ->references('id')->on('companies')
                ->onDelete('cascade') // Delete related areas if a company is deleted
                ->onUpdate('cascade'); // Update related areas if a company id is updated

            $table->softDeletes(); // Add 'deleted_at' for soft deleting records
            $table->boolean('is_published')->default(false);
            $table->timestamps(); // Adds 'created_at' and 'updated_at' columns

            $table->charset = 'utf8mb4'; // Ensure charset matches with 'companies' table
            $table->collation = 'utf8mb4_unicode_ci'; // Ensure collation matches with 'companies' table
        });
    }

    public function down(): void
    {
        Schema::table('areas', function (Blueprint $table) {
            // Drop foreign key first before dropping the column or table
            $table->dropForeign(['company_id']);
        });

        Schema::dropIfExists('areas');
    }
};
