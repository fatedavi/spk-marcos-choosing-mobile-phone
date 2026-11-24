<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('criteria', function (Blueprint $table) {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name');
            $table->string('unit', 30)->nullable();
            $table->enum('type', ['cost', 'benefit'])->default('benefit');
            $table->enum('direction', ['asc', 'desc'])->default('asc');
            $table->decimal('default_weight', 10, 8)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('criteria');
    }
};
