<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('heartbeats', static function (Blueprint $table) {
            $table->id();
            $table->string('application_key');
            $table->string('heartbeat_key');
            $table->integer('unhealthy_after_minutes');
            $table->timestamp('last_check_in');
            $table->timestamps();

            $table->unique(['application_key', 'heartbeat_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('heartbeats');
    }
};
