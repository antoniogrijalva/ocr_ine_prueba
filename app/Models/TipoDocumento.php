<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TipoDocumento extends Model
{
    protected $fillable = [
        'nombre',
        'nombre_corto',
        'descripcion',
        'instrucciones',
        'max_archivos',
        'es_obligatorio'
    ];
}
