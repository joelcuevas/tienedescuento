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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('store_id');
            $table->string('brand')->index();
            $table->string('sku')->index();
            $table->text('title');
            $table->string('slug');
            $table->decimal('latest_price', 8, 2)->nullable();
            $table->decimal('minimum_price', 8, 2)->nullable();
            $table->decimal('maximum_price', 8, 2)->nullable();
            $table->decimal('regular_price', 8, 2)->nullable();
            $table->timestamp('priced_at')->nullable();
            $table->text('url');
            $table->text('image_url')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'sku']);
            $table->index(['store_id', 'brand']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
