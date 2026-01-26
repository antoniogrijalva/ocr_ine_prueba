<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;

use Illuminate\Database\Eloquent\Model;

class Documento extends Model
{
    use HasFactory;

    protected $fillable = [
        'persona_id',
        'tipo_documento_id',
        'ruta_archivo',
        'nombre_original',
        'extension',
    ];

    // Relación con el catálogo para saber qué tipo de documento es
    public function tipoDocumento()
    {
        return $this->belongsTo(TipoDocumento::class, 'tipo_documento_id');
    }

    // Relación con la persona a la que pertenece
    public function persona()
    {
        return $this->belongsTo(Persona::class);
    }
}
