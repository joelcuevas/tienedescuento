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
        Schema::create('urls', function (Blueprint $table) {
            $table->id();
            $table->text('href');
            $table->bigInteger('hits')->default(0);
            $table->integer('status')->nullable()->index();
            $table->integer('streak')->default(0);
            $table->timestamp('crawled_at')->nullable();
            $table->timestamp('scheduled_at')->nullable()->index();
            $table->timestamp('reserved_at')->nullable();
            $table->string('domain')->index();
            $table->string('hash')->unique();
            $table->string('crawler_class')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('urls');
    }
};
