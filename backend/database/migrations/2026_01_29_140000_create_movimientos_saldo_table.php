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
        Schema::create('movimientos_saldo', function (Blueprint $table) {
            $table->id();
            $table->string('id_estudiante', 30);
            $table->enum('tipo', ['cargo', 'abono', 'ajuste']);
            $table->decimal('monto', 8, 2);
            $table->string('motivo', 50);
            $table->string('id_grupo', 10)->nullable();
            $table->unsignedBigInteger('id_pago')->nullable();
            $table->timestamps();

            $table->foreign('id_estudiante')->references('id_estudiante')->on('estudiantes');
            $table->foreign('id_grupo')->references('id_grupo')->on('grupos');
            $table->foreign('id_pago')->references('id_pago')->on('pagos');
            $table->unique('id_pago');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_saldo');
    }
};
