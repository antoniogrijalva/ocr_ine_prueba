<?php

namespace App\Http\Controllers;

use App\Models\Persona;
use Illuminate\Http\Request;

use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;

 use App\Models\TipoDocumento;

use Illuminate\Support\Facades\Storage;
use App\Models\Documento;

class PersonaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
        // public function index()
        // {
        //     $personas = Persona::with('user')
        //         ->when(Auth::user()->hasRole('capturista'), function($query) {
        //             return $query->where('user_id', Auth::id());
        //         })
        //         ->get();

        //     return Inertia::render('Personas/Index', [
        //         'personas' => $personas
        //     ]);
        // }

    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Iniciamos la consulta con relaciones y conteo
        $query = Persona::with('user')->withCount('documentos');

        // LÓGICA DE BÚSQUEDA
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nombre', 'LIKE', "%{$search}%")
                ->orWhere('primer_apellido', 'LIKE', "%{$search}%")
                ->orWhere('segundo_apellido', 'LIKE', "%{$search}%")
                ->orWhere('curp', 'LIKE', "%{$search}%");
            });
        }

        // Filtros por Rol (Mantenemos tu lógica anterior)
        if ($user->hasRole('capturista')) {
            $query->where('user_id', $user->id);
        }
        if ($user->hasRole('validador')) {
            $query->whereIn('status', ['en_revision', 'aceptado', 'rechazado']);
        }

        return Inertia::render('Personas/Index', [
            'personas' => $query->latest()->get(),
            'filters' => $request->only(['search']) // Pasamos el término de vuelta a la vista
        ]);
    }



    // Método para que el Capturista envíe el expediente
    public function enviarARevision(Persona $persona)
    {
        //dd('Enviar a revisión');
        // Validar que el usuario sea el dueño o admin
        if (Auth::id() !== $persona->user_id && !Auth::user()->hasRole('administrador')) {
            abort(403);
        }

        $persona->update(['status' => 'en_revision','observaciones' => null ]);

        return back()->with('message', 'Expediente enviado a revisión correctamente.');
    }

    //revision por el validador

    // 1. Mostrar la pantalla de revisión
    public function revisar(Persona $persona)
    {
        // Solo permitimos revisar si el estatus es 'en_revision'
        // (Opcional: permitir que el Admin lo vea aunque ya esté aceptado/rechazado)
        if ($persona->status !== 'en_revision' && !Auth::user()->hasRole('administrador')) {
            return redirect()->route('personas.index')
                ->with('error', 'Este expediente no se encuentra en estado de revisión.');
        }

        // Cargamos la persona con sus documentos y el tipo de cada uno
        /*return Inertia::render('Personas/Revisar', [
            'persona' => $persona->load(['documentos.tipoDocumento', 'user'])
        ]);*/

          return Inertia::render('Personas/RevisarMejorado', [
            'persona' => $persona->load(['documentos.tipoDocumento', 'user'])
        ]);
    }

    // 2. Procesar el resultado (Aceptar/Rechazar)
    public function dictaminar(Request $request, Persona $persona)
    {
      //  obtener el total de documentos que vienen en el request
      $totalDocumentos = count($request->input('documentos'));
      //obtener cuantos fueron validados como aceptados
        $documentosAceptados = collect($request->input('documentos'))->where('status', 'valido')->count();
        

        //si el total de documentos no es igual a los aceptados, entonces hay rechazos
        if ($totalDocumentos !== $documentosAceptados) {
           $validated='rechazado';
        }else
        {
            // --todos fueron aceptados
            $validated='aceptado';

        }

         $persona->update([
            'status' => $validated,
            'observaciones' => $validated === 'rechazado' ? 'Registro Rechazado... ' : null,
        ]);



       

        $msg = $validated === 'aceptado' 
            ? "El expediente de {$persona->nombre} ha sido ACEPTADO." 
            : "El expediente de {$persona->nombre} ha sido RECHAZADO.";

        return redirect()->route('personas.index')->with('message', $msg);
    }

    //revision por el validador


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //dd('Crear persona');
        return Inertia::render('Personas/Create');
    }

    public function create_rc()
    {
        //dd('Crear persona');
        return Inertia::render('Personas/Candidatos_form');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

           // dd($request->all());
        $validated = $request->validate([
            'nombre' => 'required|string|max:255',
            'primer_apellido' => 'required|string|max:255',
            'segundo_apellido' => 'nullable|string|max:255',
            'curp' => 'required|string|size:18|unique:personas,curp',
            'fecha_nacimiento' => 'required|date',
            'sexo' => 'required|in:H,M,X',
            'domicilio' => 'required|string',
            'domicilio_ciudad' => 'required|string',
            'domicilio_estado' => 'required|string',
            'clave_elector' => 'required|string|unique:personas,clave_elector',
            'anio_emision_ine' => 'required|digits:4',
            'ine_cic' => 'required|string',
        ], [
            'nombre.required' => 'El campo "Nombre" es obligatorio.',
            'nombre.string' => 'El campo "Nombre" debe ser una cadena de texto.',
            'nombre.max' => 'El campo "Nombre" no debe exceder los 255 caracteres.',
            'primer_apellido.required' => 'El campo "Primer Apellido" es obligatorio.',
            'primer_apellido.string' => 'El campo "Primer Apellido" debe ser una cadena de texto.',
            'primer_apellido.max' => 'El campo "Primer Apellido" no debe exceder los 255 caracteres.',
            'segundo_apellido.string' => 'El campo "Segundo Apellido" debe ser una cadena de texto.',
            'segundo_apellido.max' => 'El campo "Segundo Apellido" no debe exceder los 255 caracteres.',
            'curp.required' => 'El campo "CURP" es obligatorio.',
            'curp.string' => 'El campo "CURP" debe ser una cadena de texto.',
            'curp.size' => 'El campo "CURP" debe tener exactamente 18 caracteres.',
            'curp.unique' => 'El "CURP" ingresado ya está registrado.',
            'fecha_nacimiento.required' => 'El campo "Fecha de Nacimiento" es obligatorio.',
            'fecha_nacimiento.date' => 'El campo "Fecha de Nacimiento" debe ser una fecha válida.',
            'sexo.required' => 'El campo "Sexo" es obligatorio.',
            'sexo.in' => 'El campo "Sexo" debe ser uno de los siguientes valores: H, M, X.',
            'domicilio.required' => 'El campo "Domicilio" es obligatorio.',
            'domicilio.string' => 'El campo "Domicilio" debe ser una cadena de texto.',
            'domicilio_ciudad.required' => 'El campo "Ciudad del Domicilio" es obligatorio.',
            'domicilio_ciudad.string' => 'El campo "Ciudad del Domicilio" debe ser una cadena de texto.',
            'domicilio_estado.required' => 'El campo "Estado del Domicilio" es obligatorio.',
            'domicilio_estado.string' => 'El campo "Estado del Domicilio" debe ser una cadena de texto.',
            'clave_elector.required' => 'El campo "Clave de Elector" es obligatorio.',
            'clave_elector.string' => 'El campo "Clave de Elector" debe ser una cadena de texto.',
            'clave_elector.unique' => 'La "Clave de Elector" ingresada ya está registrada.',
            'anio_emision_ine.required' => 'El campo "Año de Emisión del INE" es obligatorio.',
            'anio_emision_ine.digits' => 'El campo "Año de Emisión del INE" debe tener exactamente 4 dígitos.',
            'ine_cic.required' => 'El campo "Vencimiento del INE (CIC)" es obligatorio.',
            'ine_cic.string' => 'El campo "Vencimiento del INE (CIC)" debe ser una cadena de texto.',
        ]);

        //dd($request->all());
        // Asignamos el ID del usuario que está capturando
        $persona = Auth::user()->personas()->create($validated);

        //dd($persona);
        // Redirigimos al Paso 2: Carga de documentos
        return redirect()->route('personas.documentos', $persona->id)
            ->with('message', 'Datos personales guardados. Ahora sube los documentos.');
    
    }

    /**
     * Display the specified resource.
     */
    public function show(Persona $persona)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Persona $persona)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Persona $persona)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Persona $persona)
    {
        //
    }

   

    public function documentos(Persona $persona)
    {
        // Obtenemos el catálogo de documentos
        $tipos = TipoDocumento::all();
        
        // obtener el estatus general de la persona para si los documentos se pueden borrar o solo ver
        $estatus_editable = in_array($persona->status, ['borrador', 'rechazado']);

        // Obtenemos los documentos que ya haya subido (si existen)
        $documentosSubidos = $persona->documentos()->with('tipoDocumento')->get();

        return Inertia::render('Personas/Documentos', [
            'persona' => $persona,
            'tipos' => $tipos,
            'documentosSubidos' => $documentosSubidos,
            // Pasamos permisos básicos para la vista
            'can_manage' => (Auth::user()->hasRole('administrador') || $persona->user_id === Auth::id()) && $estatus_editable,
        ]);
    }

    public function documentosStore(Request $request, Persona $persona)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:pdf,jpg,png|max:2048',
            'tipo_documento_id' => 'required|exists:tipo_documentos,id'
        ]);

        $path = $request->file('archivo')->store('documentos/' . $persona->id, 'public');

        $persona->documentos()->create([
            'tipo_documento_id' => $request->tipo_documento_id,
            'ruta_archivo' => $path,
            'nombre_original' => $request->file('archivo')->getClientOriginalName(),
            'extension' => $request->file('archivo')->getClientOriginalExtension(),
        ]);

        return back()->with('message', 'Archivo subido correctamente');
    }

    public function destruirDocumento(Documento $documento)
    {
        $persona = $documento->persona;

        // Validación de seguridad: Solo admin o el dueño del registro
        if (Auth::user()->hasRole('administrador') || $persona->user_id === Auth::id()) {
            
            // Eliminar el archivo físico del disco
            Storage::disk('public')->delete($documento->ruta_archivo);
            
            // Eliminar el registro de la base de datos
            $documento->delete();

            return back()->with('message', 'Documento eliminado correctamente.');
        }

        abort(403, 'No tienes permiso para eliminar este documento.');
    }

}
