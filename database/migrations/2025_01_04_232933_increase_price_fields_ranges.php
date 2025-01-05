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
            $table->decimal('latest_price', 12, 2)->change();
            $table->decimal('minimum_price', 12, 2)->change();
            $table->decimal('maximum_price', 12, 2)->change();
            $table->decimal('regular_price', 12, 2)->change();
            $table->decimal('regular_price_upper', 12, 2)->change();
            $table->decimal('regular_price_lower', 12, 2)->change();
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->decimal('price', 12, 2)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
