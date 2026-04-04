<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use function Laravel\Prompts\table;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('perfume_suitabilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfume_id');
            $table->enum('ideal_temperature', ['dingin', 'normal', 'panas']);
            $table->enum('ideal_time', ['pagi', 'siang', 'malam']);
            $table->enum('ideal_environment', ['indoor', 'outdoor', 'all around']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfume_suitabilities');
    }
};
