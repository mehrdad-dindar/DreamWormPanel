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
        Schema::create('batches', function (Blueprint $table) {
            $table->id();
            $table->integer('batch_number')->unique();
            $table->date('egg_date')->default(now());
            $table->json('watering_dates')->nullable();
            $table->json('feeding_dates')->nullable();
            $table->json('fertilization_dates')->nullable();
            $table->integer('actual_boxes')->default(0);
            $table->date('expected_harvest_date')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('batches');
    }
};
