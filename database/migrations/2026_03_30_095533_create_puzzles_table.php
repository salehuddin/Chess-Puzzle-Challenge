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
        Schema::create('puzzles', function (Blueprint $table) {
            $table->id();
            $table->string('lichess_id')->unique();
            $table->string('fen');
            $table->json('moves');
            $table->integer('rating')->index();
            $table->integer('rating_deviation')->default(0);
            $table->integer('popularity')->default(0);
            $table->integer('nb_plays')->default(0);
            $table->json('themes')->nullable();
            $table->string('game_url')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('puzzles');
    }
};
