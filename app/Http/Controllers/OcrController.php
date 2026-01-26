<?php


namespace App\Http\Controllers;
use Illuminate\Http\Request;
use Google\Cloud\Vision\V1\Client\ImageAnnotatorClient;
use Google\Cloud\Vision\V1\Image;
use Google\Cloud\Vision\V1\AnnotateImageRequest;
use Google\Cloud\Vision\V1\BatchAnnotateImagesRequest;
use Google\Cloud\Vision\V1\Feature;
use Google\Cloud\Vision\V1\Feature\Type;
use Illuminate\Support\Facades\Log;
use Imagick;

class OcrController extends Controller
{
    public function procesarIne(Request $request)
    {
        Log::info('Iniciando procesamiento de OCR para archivo: ' . $request->file('imagen')->getClientOriginalName());

        $request->validate([
            'imagen' => 'required|file|mimes:jpg,jpeg,png,pdf|max:10240',
        ]);

        $keyPath = storage_path('app/google-key.json');

        if (!file_exists($keyPath)) {
            Log::error('Credenciales no encontradas en: ' . $keyPath);
            return response()->json(['error' => 'Credenciales no encontradas'], 500);
        }

        $archivo = $request->file('imagen');
        $extension = strtolower($archivo->getClientOriginalExtension());

        try {
            // Si es PDF, convertir a imagen primero
            if ($extension === 'pdf') {
                return $this->procesarPdfComoImagen($archivo, $keyPath);
            }

            // Procesamiento normal para imágenes
            return $this->procesarImagen($archivo, $keyPath);

        } catch (\Throwable $e) {
            Log::error("OCR error: " . $e->getMessage(), [
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'error' => 'Error en OCR', 
                'detalle' => $e->getMessage()
            ], 500);
        }
    }


   private function procesarImagen($archivo, $keyPath)
    {
        $imageAnnotator = new ImageAnnotatorClient([
            'credentials' => json_decode(file_get_contents($keyPath), true)
        ]);
        
        $imageContent = file_get_contents($archivo->getRealPath());
        $image = new Image();
        $image->setContent($imageContent);
        
        $feature = new Feature();
        $feature->setType(Type::TEXT_DETECTION);
        
        $annotateImageRequest = new AnnotateImageRequest();
        $annotateImageRequest->setImage($image);
        $annotateImageRequest->setFeatures([$feature]);
        
        $batchRequest = new BatchAnnotateImagesRequest();
        $batchRequest->setRequests([$annotateImageRequest]);
        
        $response = $imageAnnotator->batchAnnotateImages($batchRequest);
        $annotations = $response->getResponses()[0];
        
        // Intentar con textAnnotations primero
        $texts = $annotations->getTextAnnotations();
        $fullText = '';

        if (!empty($texts)) {
            $fullText = $texts[0]->getDescription();
        } else {
            // Intentar con fullTextAnnotation como fallback
            $fullTextAnnotation = $annotations->getFullTextAnnotation();
            if ($fullTextAnnotation && $fullTextAnnotation->getText()) {
                $fullText = $fullTextAnnotation->getText();
            }
        }

        if (empty($fullText)) {
            Log::warning('No se detectó texto en la imagen');
            return response()->json(['error' => 'Sin texto detectado'], 422);
        }

        Log::info('Texto extraído exitosamente', ['length' => strlen($fullText)]);

        $datosExtraidos = $this->extraerDatosIne($fullText);

        return response()->json([
            'success' => true,
            'datos' => $datosExtraidos,
            'texto_completo' => $fullText,
        ]);
    }

    private function procesarPdfComoImagen($archivo, $keyPath)
    {
        // Verificar si Imagick está disponible
        if (!extension_loaded('imagick')) {
            // Fallback: intentar leer PDF directamente
            return $this->procesarPdfDirecto($archivo, $keyPath);
        }

        try {
            // Convertir PDF a imagen usando Imagick
            $imagick = new Imagick();
            $imagick->setResolution(300, 300); // Alta resolución para mejor OCR
            $imagick->readImage($archivo->getRealPath() . '[0]'); // Solo primera página
            $imagick->setImageFormat('jpg');
            $imagick->setImageCompressionQuality(95);
            
            $imageContent = $imagick->getImageBlob();
            $imagick->clear();
            $imagick->destroy();

            Log::info('PDF convertido a imagen exitosamente');

            // Procesar la imagen convertida
            $imageAnnotator = new ImageAnnotatorClient([
                'credentials' => json_decode(file_get_contents($keyPath), true)
            ]);
            
            $image = new Image();
            $image->setContent($imageContent);
            
            $feature = new Feature();
            $feature->setType(Type::TEXT_DETECTION);
            
            $annotateImageRequest = new AnnotateImageRequest();
            $annotateImageRequest->setImage($image);
            $annotateImageRequest->setFeatures([$feature]);
            
            $batchRequest = new BatchAnnotateImagesRequest();
            $batchRequest->setRequests([$annotateImageRequest]);
            
            $response = $imageAnnotator->batchAnnotateImages($batchRequest);
            $annotations = $response->getResponses()[0];
            
            $texts = $annotations->getTextAnnotations();
            $fullText = '';

            if (!empty($texts)) {
                $fullText = $texts[0]->getDescription();
            } else {
                $fullTextAnnotation = $annotations->getFullTextAnnotation();
                if ($fullTextAnnotation && $fullTextAnnotation->getText()) {
                    $fullText = $fullTextAnnotation->getText();
                }
            }

            if (empty($fullText)) {
                Log::warning('No se detectó texto en el PDF convertido');
                return response()->json([
                    'error' => 'Sin texto detectado en el PDF',
                    'sugerencia' => 'Asegúrate de que el PDF contenga texto legible o sea una imagen escaneada de buena calidad'
                ], 422);
            }

            Log::info('Texto extraído del PDF exitosamente', ['length' => strlen($fullText)]);

            $datosExtraidos = $this->extraerDatosIne($fullText);

            return response()->json([
                'success' => true,
                'datos' => $datosExtraidos,
                'texto_completo' => $fullText,
                'tipo' => 'pdf'
            ]);

        } catch (\Throwable $e) {
            Log::error('Error convirtiendo PDF con Imagick: ' . $e->getMessage());
            // Fallback: intentar procesamiento directo
            return $this->procesarPdfDirecto($archivo, $keyPath);
        }
    }

    private function procesarPdfDirecto($archivo, $keyPath)
    {
        $imageAnnotator = new ImageAnnotatorClient([
            'credentials' => json_decode(file_get_contents($keyPath), true)
        ]);
        
        $pdfContent = file_get_contents($archivo->getRealPath());
        
        $image = new Image();
        $image->setContent($pdfContent);
        
        $feature = new Feature();
        $feature->setType(Type::DOCUMENT_TEXT_DETECTION);
        
        $annotateImageRequest = new AnnotateImageRequest();
        $annotateImageRequest->setImage($image);
        $annotateImageRequest->setFeatures([$feature]);
        
        $batchRequest = new BatchAnnotateImagesRequest();
        $batchRequest->setRequests([$annotateImageRequest]);
        
        $response = $imageAnnotator->batchAnnotateImages($batchRequest);
        $annotations = $response->getResponses()[0];
        
        $fullTextAnnotation = $annotations->getFullTextAnnotation();
        $fullText = '';

        if ($fullTextAnnotation && $fullTextAnnotation->getText()) {
            $fullText = $fullTextAnnotation->getText();
        } else {
            // Intentar con textAnnotations
            $texts = $annotations->getTextAnnotations();
            if (!empty($texts)) {
                $fullText = $texts[0]->getDescription();
            }
        }

        if (empty($fullText)) {
            Log::warning('No se detectó texto en el PDF (procesamiento directo)');
            return response()->json([
                'error' => 'Sin texto detectado en el PDF',
                'sugerencia' => 'El PDF puede estar vacío, corrupto o sin texto legible. Intenta con una imagen JPG/PNG'
            ], 422);
        }

        Log::info('Texto extraído del PDF (directo) exitosamente', ['length' => strlen($fullText)]);

        $datosExtraidos = $this->extraerDatosIne($fullText);

        return response()->json([
            'success' => true,
            'datos' => $datosExtraidos,
            'texto_completo' => $fullText,
            'tipo' => 'pdf'
        ]);
    }

    // ...existing code... (mantén todos los métodos de extracción igual)

    private function extraerDatosIne($texto)
    {
        $textoNormalizado = preg_replace('/\s+/', ' ', $texto);

         $nombre_completo= $this->extraerNombreCompleto($texto)?? [];
        $datos_reverso = $this->extraerDatosReverso($texto) ?? [];
        
        return [
            'curp' => $this->extraerCurp($texto),
            'clave_elector' => $this->extraerClaveElector($texto),
            'cic' => $this->extraerCic($texto),

             // Datos personales
            'nombre' => $nombre_completo['nombres'] ?? null,
            'primer_apellido' => $nombre_completo['primer_apellido'] ?? null,
            'segundo_apellido' => $nombre_completo['segundo_apellido'] ?? null,
            'fecha_nacimiento' => $this->extraerFechaNacimiento($texto),
            'sexo' => $this->extraerSexo($texto, $nombre_completo['curp'] ?? null),

            // 'nombre' => $this->extraerNombre($texto),
            // 'apellido_paterno' => $this->extraerApellidoPaterno($texto),
            // 'apellido_materno' => $this->extraerApellidoMaterno($texto),
            // 'fecha_nacimiento' => $this->extraerFechaNacimiento($texto),
            // 'sexo' => $this->extraerSexo($texto),


           // Domicilio
            'domicilio' =>'PENDIENTE',
            'ciudad' => 'PENDIENTE',
            'estado' => 'PENDIENTE',
            
            // Datos adicionales
            'seccion' => 'PENDIENTE',
            'municipio' => 'PENDIENTE',
            'localidad' => 'PENDIENTE',
            'vigencia' => 'PENDIENTE',
            'texto_completo' => $textoNormalizado,
        ];
    }

    // ...existing code... (todos los métodos de extracción)

    

    // ============= EXTRACCIÓN DE DATOS PRINCIPALES =============
    
    private function extraerCurp($texto)
    {
        // CURP: 18 caracteres - 4 letras, 6 números, 7 caracteres alfanuméricos, 1 dígito
        if (preg_match('/\b([A-Z]{4}[0-9]{6}[HM][A-Z]{5}[0-9A-Z]{2})\b/', $texto, $matches)) {
            return $matches[1];
        }
        return null;
    }

    private function extraerFechaNacimiento($texto, $curp = null)
    {
        // 1. Intentar buscar el patrón de fecha estándar (DD/MM/AAAA)
        if (preg_match('/(\d{2}\/\d{2}\/\d{4})/', $texto, $matches)) {
            //return $matches[1];
            $fecha = \DateTime::createFromFormat('d/m/Y', $matches[1]);
            return $fecha ? $fecha->format('Y-m-d') : null;
        }

        // 2. Respaldo: Extraer de la CURP (posiciones 5 a 10: YYMMDD)
        if ($curp && strlen($curp) >= 10) {
            $yy = substr($curp, 4, 2);
            $mm = substr($curp, 6, 2);
            $dd = substr($curp, 8, 2);
            
            // Determinar el siglo (si YY > 25, es 1900, si no, 2000)
            $siglo = ($yy > date('y')) ? '19' : '20';
            // return "$dd/$mm/$siglo$yy";
                return "$siglo$yy-$mm-$dd"; // Formato AAAA/MM/DD
            
        }

        return null;
    }

    

    private function extraerClaveElector($texto)
    {
        // 6 letras, 8 números, 1 letra, 3 números
        if (preg_match('/([A-Z]{6}\d{8}[A-Z]\d{3})/', $texto, $matches)) {
            $clave = $matches[1];
            // Opcional: Validar longitud exacta
            return (strlen($clave) === 18) ? $clave : null;
        }
        return null;
    }

    private function extraerSexo($texto, $curp = null)
        {
            // 1. Buscar explícitamente "SEXO H" o "SEXO M"
            if (preg_match('/SEXO\s*([HM])/ui', $texto, $matches)) {
                return strtoupper($matches[1]);
            }

            // 2. Respaldo infalible: Posición 11 de la CURP
            if ($curp && strlen($curp) >= 11) {
                $sexoCurp = strtoupper($curp[10]);
                if (in_array($sexoCurp, ['H', 'M'])) {
                    return $sexoCurp;
                }
            }

            return null;
        }

    private function extraerCic($texto)
    {
        // CIC puede estar precedido por "CIC", "IDMEX" o solo números
        if (preg_match('/(?:CIC|IDMEX)\s*[:.]?\s*([0-9]{9,10})/i', $texto, $matches)) {
            return $matches[1];
        }
        // Buscar secuencia de 9-10 dígitos cerca del final
        if (preg_match('/\b([0-9]{9,10})\b(?!.*[0-9]{6})/', $texto, $matches)) {
            return $matches[1];
        }
        return null;
    }

    // ============= EXTRACCIÓN DE DATOS PERSONALES =============

   /* private function extraerNombreCompleto($texto)
    {
        // Buscamos el bloque que empieza después de NOMBRE o APELLIDOS 
        // y termina antes de DOMICILIO o CURP
        if (preg_match('/NOMBRE[S]?\n?([A-ZÁÉÍÓÚÑ\s\n]+?)(?:DOMICILIO|CURP|FECHA)/u', $texto, $matches)) {
            $lineas = explode("\n", trim($matches[1]));
            $lineas = array_filter(array_map('trim', $lineas));
            
            return [
                'primer_apellido' => $lineas[0] ?? null,
                'segundo_apellido' => $lineas[1] ?? null,
                'nombres' => isset($lineas[2]) ? implode(' ', array_slice($lineas, 2)) : null,
            ];
        }
        return null;
    }*/

private function extraerNombreCompleto($texto)
{
    // 1. Ruido específico que detectamos en la imagen de Valencia (Sello Amarillo)
    $ruido = [
        'INSTITUTO', 'NACIONAL', 'ELECTORAL', 'MÉXICO', 
        'ESTADOS', 'UNIDOS', 'MEXICANOS', 'CREDENCIAL', 'PARA', 'VOTAR'
    ];
    
    // 2. Buscamos el bloque desde NOMBRE hasta el siguiente campo fuerte
    if (preg_match('/NOMBRE\s*\n?(.*?)\s*(?=DOMICILIO|CURP|CLAVE|FECHA|SEXO)/siu', $texto, $matches)) {
        $bloque = $matches[1];
        
        // Limpiamos palabras del sello
        foreach ($ruido as $palabra) {
            $bloque = preg_replace('/\b' . $palabra . '\b/iu', '', $bloque);
        }

        // 3. Dividimos en líneas y limpiamos profundamente
        $lineas = explode("\n", $bloque);
        $lineas = array_values(array_filter(array_map('trim', $lineas), function($linea) {
            // Quitamos líneas vacías, de un solo carácter o que solo tengan símbolos
            return mb_strlen($linea) > 1 && preg_match('/\p{L}/u', $linea); 
        }));

        // Log para que veas en tu terminal qué líneas está detectando:
        // \Log::info('Líneas de nombre detectadas:', $lineas);

        return [
            'primer_apellido'  => $lineas[0] ?? null,
            'segundo_apellido' => $lineas[1] ?? null,
            // MEJORA: Si hay más de 2 líneas, unimos TODO lo que sobre como "nombres"
            // Esto captura "JULIAN ALBERTO" aunque estén en líneas separadas
            'nombres'          => isset($lineas[2]) ? implode(' ', array_slice($lineas, 2)) : null,
        ];
    }
    return null;
}



    
    // ============= EXTRACCIÓN DE DATOS DEL REVERSO =============

    private function extraerDatosReverso($texto)
    {
        $datos = [];
        // IDMEX (12 dígitos usualmente al final de la primera línea)
        if (preg_match('/IDMEX(\d{10,12})/', $texto, $matches)) {
            $datos['idmex'] = $matches[1];
        }
        // CIC (9 dígitos al final)
        if (preg_match('/(\d{9})\b\s*$/m', $texto, $matches)) {
            $datos['cic'] = $matches[1];
        }
        return $datos;
    }

}