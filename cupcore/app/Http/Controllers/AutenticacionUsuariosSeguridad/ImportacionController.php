<?php

namespace App\Http\Controllers\AutenticacionUsuariosSeguridad;

use App\Http\Controllers\Controller;
use App\Imports\UsuariosImport;
use App\Support\BitacoraHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

/**
 * Paquete: Autenticación, Usuarios y Seguridad
 * Caso de Uso: CU4 (Importar Datos Masivos Excel/CSV)
 * 
 * Permite cargar masivamente cuentas de usuario desde archivos compatibles (XLSX, XLS, CSV).
 * Delega la validación de filas e inserciones a la clase de importación UsuariosImport, 
 * y audita el resultado final a través de BitacoraHelper.
 */
class ImportacionController extends Controller
{
    // Controlador del caso de uso: CU4 Importar Datos Masivos Excel/CSV
    public function index(): View
    {
        return view('autenticacion_usuarios_seguridad.importaciones.index');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'archivo' => ['required', 'file', 'mimes:xlsx,xls,csv', 'max:5120'],
        ]);

        try {
            $import = new UsuariosImport();

            Excel::import($import, $validated['archivo']);

            BitacoraHelper::registrar(
                'IMPORTAR_USUARIOS',
                'Usuarios',
                'Se importaron ' . $import->getImportedCount() . ' usuarios. Errores detectados: ' . count($import->getErrors()) . '.'
            );

            return redirect()
                ->route('autenticacion-usuarios-seguridad.importaciones.index')
                ->with('success', 'Importación finalizada.')
                ->with('imported_count', $import->getImportedCount())
                ->with('import_errors', $import->getErrors());
        } catch (\Throwable $exception) {
            Log::error('Error al procesar importación de usuarios: ' . $exception->getMessage());

            return redirect()
                ->route('autenticacion-usuarios-seguridad.importaciones.index')
                ->with('import_errors', ['No se pudo procesar el archivo. Verifica que sea un Excel o CSV válido e inténtalo nuevamente.'])
                ->withErrors([
                    'archivo' => 'Error al procesar el archivo. Revisa el formato e inténtalo nuevamente.',
                ]);
        }
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('autenticacion-usuarios-seguridad.importaciones.index');
    }

    public function show(string $id): RedirectResponse
    {
        return redirect()->route('autenticacion-usuarios-seguridad.importaciones.index');
    }

    public function edit(string $id): RedirectResponse
    {
        return redirect()->route('autenticacion-usuarios-seguridad.importaciones.index');
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('autenticacion-usuarios-seguridad.importaciones.index');
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('autenticacion-usuarios-seguridad.importaciones.index');
    }
}
