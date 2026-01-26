<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Persona extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'primer_apellido',
        'segundo_apellido',
        'curp',
        'fecha_nacimiento',
        'sexo',
        'domicilio',
        'domicilio_ciudad',
        'domicilio_estado',
        'clave_elector',
        'anio_emision_ine',
        'ine_cic',
        'status', // Aunque tenga un default, es bueno tenerlo aquí
        'observaciones', // <--- Agrega esta línea
        'user_id', // Muy importante para la relación con el capturista
    ];

    // Aprovechando que estás aquí, define la relación inversa
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    public function documentos()
    {
        return $this->hasMany(Documento::class);
    }
}
