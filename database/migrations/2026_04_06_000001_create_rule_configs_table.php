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
        Schema::create('rule_configs', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['temperature', 'time']);
            $table->string('label');
            $table->float('min_value');
            $table->float('max_value');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rule_configs');
    }
};
