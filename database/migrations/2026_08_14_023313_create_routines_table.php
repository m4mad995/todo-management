<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('routines', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('title');
            $table->text('notes')->nullable(); // Untuk catatan "siapkan apa saja"
            $table->date('target_date')->nullable(); // Untuk kegiatan di tanggal spesifik
            $table->boolean('is_everyday')->default(false);
            $table->json('days_of_week')->nullable(); // Menyimpan array hari [0,1,2,3,4,5,6]
            $table->boolean('is_completed_today')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('routines');
    }
};