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
        Schema::create('alerts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->foreign('game_id')->references('id')->on('games');
            $table->unsignedBigInteger('betting_market_id');
            $table->foreign('betting_market_id')->references('id')->on('betting_markets');
            $table->string('period');
            $table->string('alternative')->nullable();
            $table->unsignedBigInteger('o1_id')->index();
            $table->foreign('o1_id')->references('id')->on('odds');
            $table->unsignedBigInteger('o1_bookmaker_id');
            $table->foreign('o1_bookmaker_id')->references('id')->on('bookmakers');
            $table->float('o1');
            $table->unsignedBigInteger('o2_id')->index();
            $table->foreign('o2_id')->references('id')->on('odds');
            $table->unsignedBigInteger('o2_bookmaker_id');
            $table->foreign('o2_bookmaker_id')->references('id')->on('bookmakers');
            $table->float('o2');
            $table->float('profit_percentage');
            $table->timestamp('refreshed_at');
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerts');
    }
};
