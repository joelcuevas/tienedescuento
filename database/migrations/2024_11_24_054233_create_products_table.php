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
            $table->string('sku')->index();
            $table->text('title');
            $table->string('slug');
            $table->string('brand')->nullable()->index();
            $table->string('brand_slug')->nullable()->index();
            $table->bigInteger('views')->default(0);
            $table->decimal('latest_price', 8, 2)->nullable();
            $table->decimal('minimum_price', 8, 2)->nullable();
            $table->decimal('maximum_price', 8, 2)->nullable();
            $table->decimal('regular_price', 8, 2)->nullable();
            $table->decimal('regular_price_upper', 8, 2)->nullable();
            $table->decimal('regular_price_lower', 8, 2)->nullable();
            $table->integer('discount')->nullable();
            $table->decimal('savings', 12, 2)->nullable();
            $table->string('status')->nullable();
            $table->date('priced_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('url');
            $table->text('image_url')->nullable();
            $table->timestamps();

            $table->unique(['store_id', 'sku']);
            $table->index(['store_id', 'brand']);
            $table->fullText(['brand', 'sku', 'title']);
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
