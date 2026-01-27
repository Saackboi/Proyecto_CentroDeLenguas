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
        Schema::create('estudiante_contacto', function (Blueprint $table) {
            $table->id('id_emergencia');
            $table->string('id_estudiante', 30);
            $table->string('alergias', 255)->default('No');
            $table->string('tipo_sangre', 45);
            $table->string('contacto_nombre', 100);
            $table->string('contacto_telefono', 20);

            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiante_verano');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiante_contacto');
    }
};
