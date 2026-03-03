<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('judges_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('judge_id')->constrained('users')->cascadeOnDelete();
            $table->date('date');
            $table->time('start_time');
            $table->time('end_time');
            $table->boolean('is_available')->default(true);
            $table->timestamps();

            $table->index(['judge_id', 'date']);
            $table->index(['date', 'is_available']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('judges_availability');
    }
};
