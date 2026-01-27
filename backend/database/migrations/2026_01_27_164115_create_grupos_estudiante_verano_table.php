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
        Schema::create('grupos_estudiante_verano', function (Blueprint $table) {
            $table->string('id_grupo', 10);
            $table->string('id_estudiante', 30);
            $table->date('fecha_inicio');
            $table->date('fecha_cierre');
            $table->string('aula', 50);
            $table->integer('nota_final');

            $table->primary(['id_grupo', 'id_estudiante', 'fecha_inicio']);
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos');
            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiante_verano');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('grupos_estudiante_verano');
    }
};
