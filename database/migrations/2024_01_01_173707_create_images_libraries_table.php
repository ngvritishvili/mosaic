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
        Schema::create('images_libraries', function (Blueprint $table) {
            $table->id();
            $table->string('filename');
            $table->string('path');
            $table->foreignId('category_id')->references('id')->on('categories');
            $table->float('dark_range');
            $table->float('medium_range');
            $table->float('light_range');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('images_libraries');
    }
};
