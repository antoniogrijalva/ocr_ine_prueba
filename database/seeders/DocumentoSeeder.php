<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DocumentoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

   

        \App\Models\TipoDocumento::create([
            'nombre' => 'Credencial para Votar (INE)',
            'nombre_corto' => 'ine',
            'descripcion' => 'Identificación oficial vigente emitida por el INE.',
            'instrucciones' => 'Cargar el archivo por ambos lados en un solo PDF.',
            'max_archivos' => 2
        ]);
        
        \App\Models\TipoDocumento::create([
            'nombre' => 'Comprobante de Domicilio',
            'nombre_corto' => 'comprobante_dom',
            'descripcion' => 'Recibo de luz, agua o teléfono.',
            'instrucciones' => 'No mayor a 3 meses de antigüedad.',
            'max_archivos' => 1
        ]);

         \App\Models\TipoDocumento::create([
            'nombre' => 'Acta de Nacimiento',
            'nombre_corto' => 'acta_nac',
            'descripcion' => 'Documento oficial que certifica el nacimiento.',
            'instrucciones' => 'Debe ser el acta completa y legible.',
            'max_archivos' => 1
        ]);

         \App\Models\TipoDocumento::create([
            'nombre' => 'Formato de Solicitud (F1)',
            'nombre_corto' => 'formato_solicitud',
            'descripcion' => 'Formato oficial para la solicitud.',
            'instrucciones' => 'Debe estar completo y firmado.',
            'max_archivos' => 1
        ]);

         \App\Models\TipoDocumento::create([
            'nombre' => 'Formato para Notificaciones (F2)',
            'nombre_corto' => 'formato_notificaciones',
            'descripcion' => 'Formato oficial para notificaciones.',
            'instrucciones' => 'Debe estar completo y firmado.',
            'max_archivos' => 1
        ]);

        \App\Models\TipoDocumento::create([
            'nombre' => 'Formato para Notificaciones (F3)',
            'nombre_corto' => 'formato_3',
            'descripcion' => 'Formato oficial F3.',
            'instrucciones' => 'Debe estar completo y firmado.',
            'max_archivos' => 1
        ]);

        \App\Models\TipoDocumento::create([
            'nombre' => 'Formato para Notificaciones (F4)',
            'nombre_corto' => 'formato_4',
            'descripcion' => 'Formato oficial F4.',
            'instrucciones' => 'Debe estar completo y firmado.',
            'max_archivos' => 1
        ]);

        \App\Models\TipoDocumento::create([
            'nombre' => 'Formato para Notificaciones (F5)',
            'nombre_corto' => 'formato_5',
            'descripcion' => 'Formato oficial F5.',
            'instrucciones' => 'Debe estar completo y firmado.',
            'max_archivos' => 1
        ]);

        \App\Models\TipoDocumento::create([
            'nombre' => 'Formato para Notificaciones (F6)',
            'nombre_corto' => 'formato_6',
            'descripcion' => 'Formato oficial F6.',
            'instrucciones' => 'Debe estar completo y firmado.',
            'max_archivos' => 1
        ]);

        \App\Models\TipoDocumento::create([
            'nombre' => 'Formato para Notificaciones (F7)',
            'nombre_corto' => 'formato_7',
            'descripcion' => 'Formato oficial F7.',
            'instrucciones' => 'Debe estar completo y firmado.',
            'max_archivos' => 1
        ]);

        \App\Models\TipoDocumento::create([
            'nombre' => 'Formato para Notificaciones (F8)',
            'nombre_corto' => 'formato_8',
            'descripcion' => 'Formato oficial F8.',
            'instrucciones' => 'Debe estar completo y firmado.',
            'max_archivos' => 1
        ]);

        \App\Models\TipoDocumento::create([
            'nombre' => 'Formato para Notificaciones (F9)',
            'nombre_corto' => 'formato_9',
            'descripcion' => 'Formato oficial F9.',
            'instrucciones' => 'Debe estar completo y firmado.',
            'max_archivos' => 1
        ]);

        \App\Models\TipoDocumento::create([
            'nombre' => 'Formato para Notificaciones (F10)',
            'nombre_corto' => 'formato_10',
            'descripcion' => 'Formato oficial F10.',
            'instrucciones' => 'Debe estar completo y firmado.',
            'max_archivos' => 1
        ]);

        \App\Models\TipoDocumento::create([
            'nombre' => 'Formato para Notificaciones (F11)',
            'nombre_corto' => 'formato_11',
            'descripcion' => 'Formato oficial F11.',
            'instrucciones' => 'Debe estar completo y firmado.',
            'max_archivos' => 1
        ]);

        \App\Models\TipoDocumento::create([
            'nombre' => 'Formato para Notificaciones (F12)',
            'nombre_corto' => 'formato_12',
            'descripcion' => 'Formato oficial F12.',
            'instrucciones' => 'Debe estar completo y firmado.',
            'max_archivos' => 1
        ]);

        \App\Models\TipoDocumento::create([
            'nombre' => 'Formato para Notificaciones (F13)',
            'nombre_corto' => 'formato_13',
            'descripcion' => 'Formato oficial F13.',
            'instrucciones' => 'Debe estar completo y firmado.',
            'max_archivos' => 1
        ]);
    }
}
