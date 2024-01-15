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
        Schema::create('games', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('sport_id');
            $table->foreign('sport_id')->references('id')->on('sports');
            $table->bigInteger('server_id');
            $table->string('ht'); // home team
            $table->unsignedBigInteger('ht_server_id');
            $table->string('at'); // away team
            $table->unsignedBigInteger('at_server_id');
            $table->string('url');
            $table->string('key');
            $table->timestamp('match_time');
            $table->string('category');
            $table->string('league');
            $table->string('last_odds_refresh_id')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('games');
    }
};
