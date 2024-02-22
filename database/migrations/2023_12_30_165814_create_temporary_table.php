<?php

use App\Enums\Resolution;
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
        Schema::create('temporary_main_pieces', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('position_x');
            $table->bigInteger('position_y');
            $table->enum('resolution', Resolution::list()->pluck('id')->toArray())
                ->default(Resolution::R4K->value);
            $table->float('dark_range');
            $table->float('medium_range');
            $table->float('light_range');
            $table->string('filename');
            $table->string('path');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('temporary');
    }
};
