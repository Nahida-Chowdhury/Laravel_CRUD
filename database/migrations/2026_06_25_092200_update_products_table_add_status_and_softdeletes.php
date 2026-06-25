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
        Schema::table('products', function (Blueprint $table) {
            // Drop the old enum column
            $table->dropColumn('status');

            // Add the new tinyInteger status (0, 1, 2)
            // 0 = Trash, 1 = Active, 2 = Permanent Delete
            $table->tinyInteger('status')->default(1);

            // Add the 'deleted_at' column
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->dropColumn('status');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
        });
    }
};
