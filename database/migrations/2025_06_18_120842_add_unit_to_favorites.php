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
        Schema::table('favorites', function (Blueprint $table) {
            $table->string('unit', 10);
            $table->unique(['favorite_list_id', 'product_id', 'unit'], 'unique_favorite_list_product_unit');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('favorites', function (Blueprint $table) {
            $table->dropUnique('unique_favorite_list_product_unit');
            $table->dropColumn('unit');
        });
    }
};
