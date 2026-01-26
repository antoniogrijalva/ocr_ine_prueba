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
        Schema::create('tipo_documentos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre'); // Ej: "Identificación Oficial"
            $table->string('nombre_corto')->unique(); // Ej: "ine"
            $table->text('descripcion')->nullable(); // Para qué sirve
            $table->text('instrucciones')->nullable(); // "Subir por ambos lados"
            $table->integer('max_archivos')->default(1); // Cuántos archivos permite
            $table->boolean('es_obligatorio')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tipo_documentos');
    }
};
