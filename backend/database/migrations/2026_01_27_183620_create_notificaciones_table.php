<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notificaciones', function (Blueprint $table) {
            $table->id('id_notificacion');
            $table->string('id_estudiante', 30);
            $table->string('titulo', 150);
            $table->text('cuerpo');
            $table->string('tipo', 30)->default('general');
            $table->boolean('leida')->default(false);
            $table->timestamp('leida_en')->nullable();
            $table->timestamps();

            $table->index('id_estudiante');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notificaciones');
    }
};
