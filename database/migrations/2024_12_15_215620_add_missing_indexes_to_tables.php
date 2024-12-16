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
        Schema::table('categories', function (Blueprint $table) {
            $table->index('store_id');
            $table->index(['store_id', 'code']);
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->index('source');
        });

        Schema::table('products', function (Blueprint $table) {
            $table->index('discount');
            $table->index('store_id');
        });

        Schema::table('urls', function (Blueprint $table) {
            $table->index('reserved_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
    }
};
