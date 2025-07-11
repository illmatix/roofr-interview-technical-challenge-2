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
        Schema::create('parking_spots', function (Blueprint $table) {
            $table->id();

            $table->unsignedBigInteger('parking_lot_id')
                  ->constrained('parking_lots')
                  ->cascadeOnDelete();
            $table->string('spot_number');
            $table->enum('type', ['regular', 'small', 'large']);
            $table->boolean('is_active')->default(true);
            $table->unsignedBigInteger('current_session_id')->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parking_spots');
    }
};
