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
        $curp = $this->extraerCurp($texto);
        $nombre_completo= $this->extraerNombreCompleto($texto)?? [];
        $datos_reverso = $this->extraerDatosReverso($texto) ?? [];

        //domicilio, ciudad y estado se extraen con un método específico
        $datos_domicilio = $this->extraerDomicilio($texto) ?? [];
        
        return [
            'curp' => $curp,
            'clave_elector' => $this->extraerClaveElector($texto),

            // CIC: Priorizar el del reverso, si no usar el del frent
            'cic' => $datos_reverso['cic'] ?? $this->extraerCic($texto),
        
            // OCR: Solo viene del reverso
            'ocr' => $datos_reverso['ocr'] ?? null, // ✅ NUEVO CAMPO
            'numero_emision' => $this->extraerNumeroEmision($texto), // ✅ NUEVO CAMPO
            'estado_nacimiento' => $this->extraerEstadoNacimiento($curp), // ✅ NUEVO CAMPO

             // Datos personales
            'nombre' => $nombre_completo['nombres'] ?? null,
            'primer_apellido' => $nombre_completo['primer_apellido'] ?? null,
            'segundo_apellido' => $nombre_completo['segundo_apellido'] ?? null,
            // 'fecha_nacimiento' => $this->extraerFechaNacimiento($texto),
            // 'sexo' => $this->extraerSexo($texto, $nombre_completo['curp'] ?? null),
            'fecha_nacimiento' => $this->extraerFechaNacimiento($texto, $curp), // ✅ CORREGIDO
            'sexo' => $this->extraerSexo($texto, $curp), // ✅ También corregido




           // Domicilio completo
            'domicilio_completo' => $datos_domicilio['domicilio_completo'] ?? null,
            'calle' => $datos_domicilio['calle'] ?? null,
            'num_exterior' => $datos_domicilio['num_exterior'] ?? null,
            'num_interior' => $datos_domicilio['num_interior'] ?? null,
            'colonia' => $datos_domicilio['colonia'] ?? null,
            'codigo_postal' => $datos_domicilio['codigo_postal'] ?? null,
            'ciudad' => $datos_domicilio['ciudad'] ?? null,
            'municipio' => $datos_domicilio['municipio'] ?? null,
            'localidad' => $datos_domicilio['localidad'] ?? null,
            'estado' => $datos_domicilio['estado'] ?? null,
            'seccion' => $datos_domicilio['seccion'] ?? null,
            
            // Datos adicionales
            'vigencia' => $this->extraerVigencia($texto),
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


    /**
     * Extrae el número de emisión de la credencial INE
     * El número de emisión viene DESPUÉS del año de registro
     * Formato común: "AÑO DE REGISTRO 2024 03" donde 03 es el número de emisión
     */
    private function extraerNumeroEmision($texto)
    {
        // Patrón 1: Después de "AÑO DE REGISTRO" + año de 4 dígitos + número de 2 dígitos
        if (preg_match('/A[ÑN]O\s+DE\s+REGISTRO[:\s]*(\d{4})\s+(\d{2})\b/iu', $texto, $matches)) {
            return str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        }
        
        // Patrón 2: Después de "REGISTRO" + año + número
        if (preg_match('/REGISTRO[:\s]*(\d{4})\s+(\d{2})\b/iu', $texto, $matches)) {
            return str_pad($matches[2], 2, '0', STR_PAD_LEFT);
        }
        
        // Patrón 3: Dos números de 2 dígitos después de un año (el primero es año de registro, el segundo emisión)
        // Ejemplo: "2024 03" o "REGISTRO 2024 03"
        if (preg_match('/(?:REGISTRO|A[ÑN]O).*?(\d{4})\s+(\d{1,2})\b/iu', $texto, $matches)) {
            $numero = $matches[2];
            // Validar que sea razonable (01-20)
            if ($numero >= 1 && $numero <= 20) {
                return str_pad($numero, 2, '0', STR_PAD_LEFT);
            }
        }
        
        // Patrón 4: Año de 4 dígitos seguido de número de 2 dígitos (sin palabra REGISTRO)
        // Buscar entre VIGENCIA y final para no confundir con otros años
        if (preg_match('/\b(20\d{2})\s+(\d{2})\b/u', $texto, $matches)) {
            $anio = $matches[1];
            $numero = $matches[2];
            
            // Validar que sea razonable para emisión (01-20)
            // Y que el año esté en rango 2000-2030
            if ($numero >= 1 && $numero <= 20 && $anio >= 2000 && $anio <= 2030) {
                return str_pad($numero, 2, '0', STR_PAD_LEFT);
            }
        }
        
        return null;
    }

    // ============= EXTRACCIÓN DE DATOS PERSONALES =============

   

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


    // ============= EXTRACCIÓN DE DOMICILIO =============

        private function extraerDomicilio($texto)
        {
            $datos = [
                'domicilio_completo' => null,
                'calle' => null,
                'num_exterior' => null,
                'num_interior' => null,
                'colonia' => null,
                'codigo_postal' => null,
                'ciudad' => null,
                'estado' => null,
                'seccion' => null,
                'municipio' => null,
                'localidad' => null,
            ];

            // Normalizar texto
            $textoLimpio = preg_replace('/\s+/', ' ', $texto);

            // 1. EXTRACCIÓN DE DOMICILIO COMPLETO
            $domicilioCompleto = null;
            
            // Patrón 1: Formato estándar con palabra DOMICILIO
            if (preg_match('/DOMICILIO[:\s]*\n?(.*?)(?=CIUDAD|ESTADO|SECCION|SECCIÓN|MUNICIPIO|LOCALIDAD|CURP|CLAVE)/siu', $texto, $matches)) {
                $domicilioCompleto = $this->limpiarDomicilio($matches[1]);
            }
            
            // Patrón 2: Sin palabra DOMICILIO explícita (credenciales viejas)
            if (!$domicilioCompleto) {
                if (preg_match('/NOMBRE.*?\n(.*?)(?=CIUDAD|ESTADO|SECCION|SECCIÓN|MUNICIPIO|LOCALIDAD|CURP)/siu', $texto, $matches)) {
                    $posibleDomicilio = $this->limpiarDomicilio($matches[1]);
                    if (preg_match('/\d+|CALLE|AVENIDA|AV\.|COLONIA|COL\.|FRACCIONAMIENTO|FRACC\./iu', $posibleDomicilio)) {
                        $domicilioCompleto = $posibleDomicilio;
                    }
                }
            }

            $datos['domicilio_completo'] = $domicilioCompleto;

            // 2. PARSEAR COMPONENTES DEL DOMICILIO
            if ($domicilioCompleto) {
                $componentes = $this->parsearDomicilioNuevoFormato($domicilioCompleto);
                $datos['calle'] = $componentes['calle'];
                $datos['num_exterior'] = $componentes['num_exterior'];
                $datos['num_interior'] = $componentes['num_interior'];
                $datos['colonia'] = $componentes['colonia'];
                $datos['codigo_postal'] = $componentes['codigo_postal'];
                $datos['municipio'] = $componentes['municipio'];
                $datos['estado'] = $componentes['estado'];
            }

            // 3. EXTRACCIÓN DE CÓDIGO POSTAL (también buscarlo directamente en el texto)
            if (!$datos['codigo_postal']) {
                if (preg_match('/\b(\d{5})\b/', $texto, $matches)) {
                    $cp = $matches[1];
                    if ($cp >= 10000 && $cp <= 99999) {
                        $datos['codigo_postal'] = $cp;
                    }
                }
            }

            // 4. EXTRACCIÓN DE CIUDAD/MUNICIPIO (método antiguo como respaldo)
            if (!$datos['municipio']) {
                if (preg_match('/(?:CIUDAD|MUNICIPIO)[:\s]*\n?(.*?)(?=ESTADO|SECCION|SECCIÓN|LOCALIDAD|CURP|CLAVE)/siu', $texto, $matches)) {
                    $datos['ciudad'] = trim(preg_replace('/\s+/', ' ', $matches[1]));
                    $datos['municipio'] = $datos['ciudad'];
                }
            }

            // 5. EXTRACCIÓN DE ESTADO (método antiguo como respaldo)
            if (!$datos['estado']) {
                if (preg_match('/ESTADO[:\s]*\n?(.*?)(?=SECCION|SECCIÓN|LOCALIDAD|MUNICIPIO|VIGENCIA|EMISIÓN|REGISTRO|CURP|CLAVE|\d{4})/siu', $texto, $matches)) {
                    $datos['estado'] = $this->normalizarEstado(trim($matches[1]));
                }
            }
            
            if (!$datos['estado']) {
                if (preg_match('/\b([A-Z]{2,3})\b(?=\s*(?:SECCION|SECCIÓN|LOCALIDAD|\d{4}))/u', $texto, $matches)) {
                    $abreviatura = $matches[1];
                    $datos['estado'] = $this->convertirAbreviaturaEstado($abreviatura);
                }
            }

            // 6. EXTRACCIÓN DE SECCIÓN ELECTORAL
            if (preg_match('/SECCI[OÓ]N[:\s]*(\d{1,4})/iu', $texto, $matches)) {
                $datos['seccion'] = str_pad($matches[1], 4, '0', STR_PAD_LEFT);
            }

            // 7. EXTRACCIÓN DE LOCALIDAD
            if (preg_match('/LOCALIDAD[:\s]*\n?(.*?)(?=SECCION|SECCIÓN|MUNICIPIO|ESTADO|VIGENCIA|EMISIÓN|CURP)/siu', $texto, $matches)) {
                $datos['localidad'] = trim(preg_replace('/\s+/', ' ', $matches[1]));
            }

            return $datos;
        }


        /**
             * Parsea domicilio en formato NUEVO de credenciales INE
             * Formato: CALLE NUMERO COL COLONIA MUNICIPIO ESTADO.
             * Ejemplo: "C GUSTAVO HODGERS 51 COL MODELO 83190 HERMOSILLO, SON."
         */
        private function parsearDomicilioNuevoFormato($domicilio)
        {
            $componentes = [
                'calle' => null,
                'num_exterior' => null,
                'num_interior' => null,
                'colonia' => null,
                'codigo_postal' => null,
                'municipio' => null,
                'estado' => null,
            ];

            // Normalizar texto
            $domicilio = trim($domicilio);
            $domicilioOriginal = $domicilio;

            // 1. EXTRAER ESTADO (abreviatura de 2-4 letras + punto al final)
            // Patrón: SON. JAL. NLE. CDMX. etc.
            if (preg_match('/,?\s*([A-Z]{2,4})\.?\s*$/iu', $domicilio, $matches)) {
                $abreviatura = strtoupper(str_replace('.', '', $matches[1]));
                $componentes['estado'] = $this->convertirAbreviaturaEstado($abreviatura);
                // Eliminar del domicilio
                $domicilio = preg_replace('/,?\s*' . preg_quote($matches[1], '/') . '\.?\s*$/iu', '', $domicilio);
                $domicilio = trim($domicilio);
            }

            // 2. EXTRAER CÓDIGO POSTAL (5 dígitos)
            if (preg_match('/\b(\d{5})\b/', $domicilio, $matches)) {
                $cp = $matches[1];
                if ($cp >= 10000 && $cp <= 99999) {
                    $componentes['codigo_postal'] = $cp;
                    // Eliminar CP del domicilio
                    $domicilio = str_replace($matches[0], '', $domicilio);
                    $domicilio = preg_replace('/\s+/', ' ', $domicilio); // Limpiar espacios dobles
                    $domicilio = trim($domicilio);
                }
            }

            // 3. EXTRAER COLONIA **ANTES** DEL MUNICIPIO (MEJORADO)
            // Ahora busca: COL, COL., COLONIA, FRACC, FRACC., FRACCIONAMIENTO
            if (preg_match('/\b(?:COL\.?|COLONIA|FRACC\.?|FRACCIONAMIENTO)\s+([A-ZÁÉÍÓÚÑ\s\d]+?)(?=\s*,|\s+[A-ZÁÉÍÓÚÑ]+\s*$)/iu', $domicilio, $matches)) {
                $componentes['colonia'] = trim($matches[1]);
                // Eliminar el identificador (COL, FRACC, etc.) y la colonia del domicilio
                $domicilio = preg_replace('/\b(?:COL\.?|COLONIA|FRACC\.?|FRACCIONAMIENTO)\s+' . preg_quote(trim($matches[1]), '/') . '/iu', '', $domicilio);
                $domicilio = preg_replace('/\s+/', ' ', $domicilio);
                $domicilio = trim($domicilio);
            }

            // 4. EXTRAER MUNICIPIO (última palabra o palabras después de limpiar colonia)
            // Ejemplo: "... HERMOSILLO," -> HERMOSILLO es el municipio
            if (preg_match('/,?\s*([A-ZÁÉÍÓÚÑ\s]+?)\s*,?\s*$/iu', $domicilio, $matches)) {
                $posibleMunicipio = trim($matches[1]);
                // Validar que tenga más de 2 caracteres
                if (mb_strlen($posibleMunicipio) > 2) {
                    $componentes['municipio'] = $posibleMunicipio;
                    // Eliminar del domicilio
                    $domicilio = preg_replace('/,?\s*' . preg_quote($posibleMunicipio, '/') . '\s*,?\s*$/iu', '', $domicilio);
                    $domicilio = trim($domicilio);
                }
            }

            // 5. EXTRAER NÚMERO EXTERIOR (MEJORADO - primer número encontrado)
            // Buscar cualquier secuencia de dígitos, opcionalmente seguida de letra
            if (preg_match('/\b(\d+[A-Z]?)\b/', $domicilio, $matches)) {
                $componentes['num_exterior'] = strtoupper($matches[1]);
                // Eliminar SOLO la primera ocurrencia del número
                $domicilio = preg_replace('/\b' . preg_quote($matches[1], '/') . '\b/', '', $domicilio, 1);
                $domicilio = preg_replace('/\s+/', ' ', $domicilio);
                $domicilio = trim($domicilio);
            }

            // 6. EXTRAER NÚMERO INTERIOR (si existe, después de INT)
            if (preg_match('/(?:INT\.?|INTERIOR)\s*(\d+[A-Z]?)/iu', $domicilio, $matches)) {
                $componentes['num_interior'] = strtoupper($matches[1]);
                $domicilio = preg_replace('/(?:INT\.?|INTERIOR)\s*' . preg_quote($matches[1], '/') . '/iu', '', $domicilio);
                $domicilio = preg_replace('/\s+/', ' ', $domicilio);
                $domicilio = trim($domicilio);
            }

            // 7. EXTRAER CALLE (lo que queda después de limpiar todo)
            $calle = trim($domicilio);
            
            // Limpiar prefijos comunes de calle (MEJORADO - ahora incluye RTNO)
            $calle = preg_replace('/^(?:C\.?|CALLE|AV\.?|AVENIDA|BLVD\.?|BOULEVARD|PRIV\.?|PRIVADA|RTNO\.?|RETORNO|CALZ\.?|CALZADA|ANDADOR|PASEO|VIA)\s+/iu', '', $calle);
            
            // Limpiar comas y espacios sobrantes
            $calle = preg_replace('/[,\s]+$/', '', $calle);
            $calle = preg_replace('/^[,\s]+/', '', $calle);
            
            // Limpiar múltiples espacios
            $calle = preg_replace('/\s+/', ' ', $calle);
            
            if (mb_strlen($calle) > 2) {
                $componentes['calle'] = trim($calle);
            }

            // DEBUG: Log para ver qué quedó en cada paso
            /*
            \Log::info('Parsing domicilio nuevo formato', [
                'original' => $domicilioOriginal,
                'estado' => $componentes['estado'],
                'cp' => $componentes['codigo_postal'],
                'colonia' => $componentes['colonia'],
                'municipio' => $componentes['municipio'],
                'num_exterior' => $componentes['num_exterior'],
                'calle' => $componentes['calle'],
                'domicilio_final' => $domicilio
            ]);
            */

            return $componentes;
        }

        

        /**
         * Limpia el texto del domicilio eliminando ruido
         */
        private function limpiarDomicilio($domicilio)
        {
            // Eliminar palabras de ruido comunes en OCR de INE
            $ruido = [
                'INSTITUTO', 'NACIONAL', 'ELECTORAL', 'MÉXICO', 
                'ESTADOS', 'UNIDOS', 'MEXICANOS', 'CREDENCIAL', 
                'PARA', 'VOTAR', 'IDENTIFICACIÓN', 'CIUDADANA'
            ];
            
            foreach ($ruido as $palabra) {
                $domicilio = preg_replace('/\b' . $palabra . '\b/iu', '', $domicilio);
            }

            // Limpiar múltiples espacios y saltos de línea
            $domicilio = preg_replace('/\s+/', ' ', $domicilio);
            
            // Eliminar líneas que solo tengan 1-2 caracteres
            $lineas = explode("\n", $domicilio);
            $lineas = array_filter($lineas, function($linea) {
                return mb_strlen(trim($linea)) > 2;
            });
            
            return trim(implode(' ', $lineas));
        }




        /**
         * Normaliza el nombre del estado (elimina ruido y acentos)
         */
        private function normalizarEstado($estado)
        {
            // Eliminar caracteres extraños
            $estado = preg_replace('/[^A-ZÁÉÍÓÚÑ\s]/u', '', strtoupper($estado));
            
            // Mapeo de nombres completos comunes
            $estados = [
                'AGUASCALIENTES' => 'AGUASCALIENTES',
                'BAJA CALIFORNIA' => 'BAJA CALIFORNIA',
                'BAJA CALIFORNIA SUR' => 'BAJA CALIFORNIA SUR',
                'CAMPECHE' => 'CAMPECHE',
                'CHIAPAS' => 'CHIAPAS',
                'CHIHUAHUA' => 'CHIHUAHUA',
                'CIUDAD DE MEXICO' => 'CIUDAD DE MÉXICO',
                'CDMX' => 'CIUDAD DE MÉXICO',
                'COAHUILA' => 'COAHUILA',
                'COLIMA' => 'COLIMA',
                'DURANGO' => 'DURANGO',
                'GUANAJUATO' => 'GUANAJUATO',
                'GUERRERO' => 'GUERRERO',
                'HIDALGO' => 'HIDALGO',
                'JALISCO' => 'JALISCO',
                'MEXICO' => 'MÉXICO',
                'MICHOACAN' => 'MICHOACÁN',
                'MORELOS' => 'MORELOS',
                'NAYARIT' => 'NAYARIT',
                'NUEVO LEON' => 'NUEVO LEÓN',
                'OAXACA' => 'OAXACA',
                'PUEBLA' => 'PUEBLA',
                'QUERETARO' => 'QUERÉTARO',
                'QUINTANA ROO' => 'QUINTANA ROO',
                'SAN LUIS POTOSI' => 'SAN LUIS POTOSÍ',
                'SINALOA' => 'SINALOA',
                'SONORA' => 'SONORA',
                'TABASCO' => 'TABASCO',
                'TAMAULIPAS' => 'TAMAULIPAS',
                'TLAXCALA' => 'TLAXCALA',
                'VERACRUZ' => 'VERACRUZ',
                'YUCATAN' => 'YUCATÁN',
                'ZACATECAS' => 'ZACATECAS',
            ];

            $estado = trim($estado);
            
            // Buscar coincidencia exacta o parcial
            foreach ($estados as $clave => $valor) {
                if (stripos($estado, $clave) !== false || stripos($clave, $estado) !== false) {
                    return $valor;
                }
            }

            return $estado;
        }

        /**
         * Convierte abreviatura de estado a nombre completo
         */
        private function convertirAbreviaturaEstado($abreviatura)
        {
            $abreviaturas = [
                'AGS' => 'AGUASCALIENTES',
                'BC' => 'BAJA CALIFORNIA',
                'BCS' => 'BAJA CALIFORNIA SUR',
                'CAM' => 'CAMPECHE',
                'CHS' => 'CHIAPAS',
                'CHI' => 'CHIHUAHUA',
                'CHIH' => 'CHIHUAHUA',
                'CDMX' => 'CIUDAD DE MÉXICO',
                'CMX' => 'CIUDAD DE MÉXICO',
                'COA' => 'COAHUILA',
                'COAH' => 'COAHUILA',
                'COL' => 'COLIMA',
                'DGO' => 'DURANGO',
                'DUR' => 'DURANGO',
                'GTO' => 'GUANAJUATO',
                'GRO' => 'GUERRERO',
                'GUE' => 'GUERRERO',
                'HGO' => 'HIDALGO',
                'HID' => 'HIDALGO',
                'JAL' => 'JALISCO',
                'MEX' => 'MÉXICO',
                'MIC' => 'MICHOACÁN',
                'MICH' => 'MICHOACÁN',
                'MOR' => 'MORELOS',
                'NAY' => 'NAYARIT',
                'NL' => 'NUEVO LEÓN',
                'NLE' => 'NUEVO LEÓN',
                'OAX' => 'OAXACA',
                'PUE' => 'PUEBLA',
                'QRO' => 'QUERÉTARO',
                'QR' => 'QUINTANA ROO',
                'QROO' => 'QUINTANA ROO',
                'SLP' => 'SAN LUIS POTOSÍ',
                'SIN' => 'SINALOA',
                'SON' => 'SONORA',
                'TAB' => 'TABASCO',
                'TAM' => 'TAMAULIPAS',
                'TAMPS' => 'TAMAULIPAS',
                'TLX' => 'TLAXCALA',
                'TLAX' => 'TLAXCALA',
                'VER' => 'VERACRUZ',
                'YUC' => 'YUCATÁN',
                'ZAC' => 'ZACATECAS',
            ];

            return $abreviaturas[strtoupper($abreviatura)] ?? $abreviatura;
        }
    // ============= EXTRACCIÓN DE DOMICILIO FIN =============


    private function extraerVigencia($texto)
    {
        // Buscar año de vigencia (4 dígitos al final del texto o después de VIGENCIA)
        if (preg_match('/VIGENCIA[:\s]*(\d{4})/iu', $texto, $matches)) {
            return $matches[1];
        }
        
        // Buscar patrón de año al final (credenciales modernas)
        if (preg_match('/\b(20\d{2})\b(?!.*\d{4})/', $texto, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    // ============= EXTRACCIÓN DE DATOS DEL REVERSO =============

    private function extraerDatosReverso($texto)
    {
        $datos = [
            'cic' => null,
            'ocr' => null,
        ];

        // 1. EXTRAER CIC (9 dígitos)
        // Patrón 1: Después de la palabra CIC explícita
        if (preg_match('/\bCIC[:\s]*(\d{9})\b/iu', $texto, $matches)) {
            $datos['cic'] = $matches[1];
        }
        
        // Patrón 2: Después de IDMEX (formato: IDMEX + 12 dígitos + espacio + CIC 9 dígitos)
        if (!$datos['cic']) {
            if (preg_match('/IDMEX\d{10,12}\s+(\d{9})\b/iu', $texto, $matches)) {
                $datos['cic'] = $matches[1];
            }
        }

        // Patrón 3: 9 dígitos cerca del final del texto (pero no el OCR que son 13)
        if (!$datos['cic']) {
            if (preg_match('/\b(\d{9})\b(?!.*\d{13})/u', $texto, $matches)) {
                $datos['cic'] = $matches[1];
            }
        }

        // 2. EXTRAER OCR (13 dígitos con separadores < o espacios)
        // Formato común: 1234<5678<9012<3 o 1234 5678 9012 3
        
        // Patrón 1: Con separadores < (más común en reverso INE)
        if (preg_match('/(\d{4}<\d{4}<\d{4}<\d{1})/u', $texto, $matches)) {
            // Limpiar separadores
            $datos['ocr'] = str_replace('<', '', $matches[1]);
        }
        
        // Patrón 2: 13 dígitos seguidos sin separadores
        if (!$datos['ocr']) {
            if (preg_match('/\b(\d{13})\b/u', $texto, $matches)) {
                $datos['ocr'] = $matches[1];
            }
        }

        // Patrón 3: Con espacios como separadores (formato: 1234 5678 9012 3)
        if (!$datos['ocr']) {
            if (preg_match('/\b(\d{4}\s+\d{4}\s+\d{4}\s+\d{1})\b/u', $texto, $matches)) {
                // Limpiar espacios
                $datos['ocr'] = str_replace(' ', '', $matches[1]);
            }
        }

        // Patrón 4: Después de la palabra OCR explícita
        if (!$datos['ocr']) {
            if (preg_match('/\bOCR[:\s]*[<]?(\d{4})[<\s]*(\d{4})[<\s]*(\d{4})[<\s]*(\d{1})/iu', $texto, $matches)) {
                $datos['ocr'] = $matches[1] . $matches[2] . $matches[3] . $matches[4];
            }
        }

        return $datos;
    }


    /**
     * Extrae el estado de nacimiento desde la CURP
     * Las posiciones 11-12 de la CURP (índice 10-11) corresponden al código del estado de nacimiento
     */
    private function extraerEstadoNacimiento($curp)
    {
        if (!$curp || strlen($curp) < 12) {
            return null;
        }

        // Extraer las posiciones 11-12 (índice 10-11)
        $codigoEstado = strtoupper(substr($curp, 11, 2));

        // Mapeo de códigos de estado de la CURP
        $estadosNacimiento = [
            'AS' => 'AGUASCALIENTES',
            'BC' => 'BAJA CALIFORNIA',
            'BS' => 'BAJA CALIFORNIA SUR',
            'CC' => 'CAMPECHE',
            'CS' => 'CHIAPAS',
            'CH' => 'CHIHUAHUA',
            'CL' => 'COAHUILA',
            'CM' => 'COLIMA',
            'DF' => 'CIUDAD DE MÉXICO',
            'DG' => 'DURANGO',
            'GT' => 'GUANAJUATO',
            'GR' => 'GUERRERO',
            'HG' => 'HIDALGO',
            'JC' => 'JALISCO',
            'MC' => 'MÉXICO',
            'MN' => 'MICHOACÁN',
            'MS' => 'MORELOS',
            'NT' => 'NAYARIT',
            'NL' => 'NUEVO LEÓN',
            'OC' => 'OAXACA',
            'PL' => 'PUEBLA',
            'QT' => 'QUERÉTARO',
            'QR' => 'QUINTANA ROO',
            'SP' => 'SAN LUIS POTOSÍ',
            'SL' => 'SINALOA',
            'SR' => 'SONORA',
            'TC' => 'TABASCO',
            'TS' => 'TAMAULIPAS',
            'TL' => 'TLAXCALA',
            'VZ' => 'VERACRUZ',
            'YN' => 'YUCATÁN',
            'ZS' => 'ZACATECAS',
            'NE' => 'NACIDO EN EL EXTRANJERO',
        ];

        return $estadosNacimiento[$codigoEstado] ?? null;
    }

}