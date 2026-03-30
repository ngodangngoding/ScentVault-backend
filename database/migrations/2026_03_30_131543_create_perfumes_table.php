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
        Schema::create('perfumes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id');
            $table->string('name');
            $table->enum('concentration', ['extrait de parfum', 'eau de parfum', 'eau de toilette', 'eau de cologne']);
            $table->string('image')->nullable();
            $table->boolean('is_active');
            $table->integer('star_rating');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('perfumes');
    }
};
