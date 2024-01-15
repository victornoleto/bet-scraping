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
        Schema::create('server_requests', function (Blueprint $table) {
            $table->id();
            $table->string('url');
            $table->json('query');
            $table->json('headers');
            $table->timestamp('started_at');
            $table->timestamp('finished_at');
            $table->float('elapsed_time');
            $table->integer('status_code');
            $table->text('error')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('server_requests');
    }
};
