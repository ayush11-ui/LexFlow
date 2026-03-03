<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('track_rules', function (Blueprint $table) {
            $table->id();
            $table->string('case_type');
            $table->integer('document_threshold')->default(0);
            $table->decimal('duration_threshold', 5, 2)->default(0);
            $table->enum('assigned_track', ['fast', 'standard', 'complex']);
            $table->timestamps();

            $table->index('case_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('track_rules');
    }
};
