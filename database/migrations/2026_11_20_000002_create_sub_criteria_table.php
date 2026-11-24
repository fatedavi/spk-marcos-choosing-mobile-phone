<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sub_criteria', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criteria_id')->constrained('criteria')->cascadeOnDelete();
            $table->unsignedTinyInteger('score');
            $table->decimal('min_value', 20, 2)->nullable();
            $table->string('label', 150)->nullable();
            $table->timestamps();

            $table->index(['criteria_id', 'min_value']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sub_criteria');
    }
};
