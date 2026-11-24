<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alternative_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('alternative_id')->constrained('alternatives')->cascadeOnDelete();
            $table->foreignId('criteria_id')->constrained('criteria')->cascadeOnDelete();
            $table->decimal('raw_value', 20, 2);
            $table->unsignedTinyInteger('score')->nullable();
            $table->timestamps();

            $table->unique(['alternative_id', 'criteria_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alternative_values');
    }
};
