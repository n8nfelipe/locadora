<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rentals', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('reservation_id')->nullable()->constrained('reservations');
            $table->foreignUuid('user_id')->constrained('users');
            $table->foreignUuid('vehicle_id')->constrained('vehicles');
            $table->dateTime('start_date');
            $table->dateTime('end_date');
            $table->dateTime('actual_return_date')->nullable();
            $table->decimal('total_price', 10, 2)->default(0);
            $table->enum('status', ['ongoing', 'finished', 'late'])->default('ongoing');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rentals');
    }
};
