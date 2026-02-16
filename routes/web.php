<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\PersonaController;
use App\Http\Controllers\UserController;

// Agregar el controlador OCR de google api
use App\Http\Controllers\OcrController;

Route::get('/', function () {
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
});

Route::get('/dashboard', function (\Illuminate\Http\Request $request) {
    //return Inertia::render('Dashboard');

    // return Inertia::render('Dashboard', [
    //     'auth' => [
    //         'user' => $request->user(),
    //         'can' => [
    //         'validar' => $request->user()->can('validar registros'),
    //         ]
    //     ]
    // ]);
    return Inertia::render('Dashboard');

})->middleware(['auth', 'verified'])->name('dashboard');


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});



Route::middleware(['auth', 'verified'])->group(function () {
    // Rutas protegidas por roles
    Route::group(['middleware' => ['role:capturista|administrador']], function () {
        Route::get('/personas/crear', [PersonaController::class, 'create'])->name('personas.create');
        Route::post('/personas', [PersonaController::class, 'store'])->name('personas.store');

        Route::get('/personas/{persona}/documentos', [PersonaController::class, 'documentos'])->name('personas.documentos');
        Route::post('/personas/{persona}/documentos', [PersonaController::class, 'documentosStore'])->name('personas.documentos.store');

        Route::delete('/documentos/{documento}', [PersonaController::class, 'destruirDocumento'])->name('documentos.destroy');

        Route::post('/personas/{persona}/enviar', [PersonaController::class, 'enviarARevision'])->name('personas.enviar');
        
        // Rutas de OCR
        Route::get('/ocr', fn() => Inertia::render('Ocr'))->name('ocr.page');
        Route::post('/ocr-ine', [OcrController::class, 'procesarIne'])
            ->middleware('throttle:20,1')
            ->name('ocr.ine');

            //JAGA febrero 2026:: solo para prototipo de registro de candidatos (captura)
            Route::get('/registro-candidatos/crear', [PersonaController::class, 'create_rc'])->name('personas.create_rc');
            Route::get('/registro-candidatos/documentos', [PersonaController::class, 'documentos_rc'])->name('personas.documentos_rc');
    });

    Route::get('/personas', [PersonaController::class, 'index'])->name('personas.index');
});



Route::middleware(['auth', 'role:validador|administrador'])->group(function () {
    Route::get('/personas/{persona}/revisar', [PersonaController::class, 'revisar'])->name('personas.revisar');
    Route::post('/personas/{persona}/dictaminar', [PersonaController::class, 'dictaminar'])->name('personas.dictaminar');
});



//uso de administrador para la gestion de usuarios
Route::middleware(['auth', 'role:administrador'])->group(function () {
    Route::get('/usuarios', [UserController::class, 'index'])->name('users.index');
    Route::post('/usuarios', [UserController::class, 'store'])->name('users.store'); // <-- Nueva ruta
    Route::put('/usuarios/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/usuarios/{user}', [UserController::class, 'destroy'])->name('users.destroy');
});



require __DIR__.'/auth.php';
