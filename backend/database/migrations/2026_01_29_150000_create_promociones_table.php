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
        Schema::create('promociones', function (Blueprint $table) {
            $table->id();
            $table->string('id_estudiante', 30);
            $table->string('id_grupo', 10);
            $table->enum('tipo', ['regular', 'verano']);
            $table->string('nivel_anterior', 10)->nullable();
            $table->string('nivel_nuevo', 10)->nullable();
            $table->string('aprobado_por', 100)->nullable();
            $table->timestamp('revertido_en')->nullable();
            $table->string('revertido_por', 100)->nullable();
            $table->timestamps();

            $table->index(['id_estudiante', 'id_grupo', 'tipo']);
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('promociones');
    }
};
