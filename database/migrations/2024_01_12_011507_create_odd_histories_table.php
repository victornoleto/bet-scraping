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
        Schema::create('odd_histories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('odd_id');
            $table->foreign('odd_id')->references('id')->on('odds');
            $table->float('home_odd');
            $table->float('away_odd');
            $table->float('draw_odd')->nullable();
            $table->timestamp('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('odd_histories');
    }
};
