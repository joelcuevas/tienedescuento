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
            $table->renameColumn('url', 'external_url');
        });

        Schema::table('stores', function (Blueprint $table) {
            $table->renameColumn('url', 'external_url');
        });

        Schema::table('categories', function (Blueprint $table) {
            $table->renameColumn('url', 'external_url');
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
