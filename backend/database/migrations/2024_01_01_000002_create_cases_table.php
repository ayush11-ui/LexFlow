<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->id();
            $table->string('case_number')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('case_type');
            $table->unsignedTinyInteger('urgency_level')->default(1);
            $table->string('complexity_level')->nullable();
            $table->decimal('estimated_duration', 5, 2)->default(1.00);
            $table->decimal('priority_score', 8, 2)->default(0);
            $table->enum('track', ['fast', 'standard', 'complex'])->default('standard');
            $table->enum('status', ['pending', 'scheduled', 'completed'])->default('pending');
            $table->foreignId('assigned_judge_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index('track');
            $table->index('status');
            $table->index('priority_score');
            $table->index('case_type');
            $table->index('urgency_level');
            $table->index(['track', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cases');
    }
};
