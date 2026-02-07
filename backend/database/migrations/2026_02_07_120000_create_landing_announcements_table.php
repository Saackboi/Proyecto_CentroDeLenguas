<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('landing_announcements')) {
            return;
        }

        Schema::create('landing_announcements', function (Blueprint $table) {
            $table->id();
            $table->string('key', 50)->unique();
            $table->enum('status_code', ['abiertas', 'cerradas', 'proximamente', 'aviso']);
            $table->string('title', 120);
            $table->string('subtitle', 200);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('landing_announcements');
    }
};
