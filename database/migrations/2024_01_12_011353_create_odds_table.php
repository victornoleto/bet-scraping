<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('odds', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('game_id');
            $table->foreign('game_id')->references('id')->on('games');
            $table->unsignedBigInteger('bookmaker_id');
            $table->foreign('bookmaker_id')->references('id')->on('bookmakers');
            $table->unsignedBigInteger('betting_market_id');
            $table->foreign('betting_market_id')->references('id')->on('betting_markets');
            $table->string('period');
            $table->string('alternative')->nullable();
            $table->float('o1');
            $table->float('o2');
            $table->float('o3')->nullable();
            $table->integer('status');
            $table->float('payout');
            $table->string('refresh_id');
            $table->timestamp('refreshed_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odds');
    }
};
