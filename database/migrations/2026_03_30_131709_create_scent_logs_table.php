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
        Schema::create('scent_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('perfume_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('occasion_id')->constrained()->cascadeOnUpdate();
            $table->enum('environment', ['indoor', 'outdoor', 'all around']);
            $table->text('notes_review')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scent_logs');
    }
};
