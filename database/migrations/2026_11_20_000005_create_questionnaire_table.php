<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('questionnaire', function (Blueprint $table) {
            $table->id();
            $table->foreignId('criteria_id')->unique()->constrained('criteria')->cascadeOnDelete();
            $table->unsignedInteger('n_ss')->default(0);
            $table->unsignedInteger('n_s')->default(0);
            $table->unsignedInteger('n_c')->default(0);
            $table->unsignedInteger('n_ts')->default(0);
            $table->unsignedInteger('n_sts')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('questionnaire');
    }
};
