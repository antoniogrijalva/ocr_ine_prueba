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
        Schema::create('personas', function (Blueprint $table) {
            $table->id();


            // Datos Personales
            $table->string('nombre');
            $table->string('primer_apellido')->nullable(); // Puede ser opcional
            $table->string('segundo_apellido')->nullable(); // Puede ser opcional
            $table->string('curp', 18); // El CURP es único y de 18 caracteres
            $table->date('fecha_nacimiento');
            $table->char('sexo', 1); // 'H', 'M', o 'X'
            
            // Domicilio
            $table->string('domicilio');
            $table->string('domicilio_ciudad');
            $table->string('domicilio_estado');
            
            // Datos INE
            $table->string('clave_elector');
            $table->year('anio_emision_ine'); // Solo el año
            $table->string('ine_cic'); // El código CIC suele tener letras y números
            
            // Control de Estatus (Punto 3 y 4 de tu lista)
            // Por defecto empieza en 'borrador'
            $table->enum('status', ['borrador', 'en_revision', 'aceptado', 'rechazado'])
                ->default('borrador');
                
            // Auditoría
            $table->foreignId('user_id')->constrained(); // Quién registró a esta persona
            $table->timestamps();
            
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
