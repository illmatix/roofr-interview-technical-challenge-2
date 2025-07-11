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
        Schema::create('parking_sessions', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('vehicle_id')
                  ->nullable()
                  ->constrained('vehicles')
                  ->cascadeOnDelete();
            $table->timestamp('started_at')->default(now());
            $table->timestamp('ended_at')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_sessions');
    }
};
