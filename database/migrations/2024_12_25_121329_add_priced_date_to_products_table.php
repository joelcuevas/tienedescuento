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
            $table->renameColumn('priced_at', 'priced_date');
            $table->renameIndex('products_priced_at_index', 'products_priced_date_index');
            $table->timestamp('priced_at')->nullable()->after('priced_date')->index();
        });

        Schema::table('prices', function (Blueprint $table) {
            $table->renameColumn('priced_at', 'priced_date');
            $table->renameIndex('prices_priced_at_index', 'prices_priced_date_index');
            $table->timestamp('priced_at')->nullable()->after('priced_date')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            //
        });
    }
};
