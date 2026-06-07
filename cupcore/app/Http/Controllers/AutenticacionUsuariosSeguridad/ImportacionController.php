<?php

namespace App\Http\Controllers\AutenticacionUsuariosSeguridad;

use App\Http\Controllers\Controller;
use App\Imports\UsuariosImport;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;

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

            return redirect()
                ->route('autenticacion-usuarios-seguridad.importaciones.index')
                ->with('success', 'Importación finalizada.')
                ->with('imported_count', $import->getImportedCount())
                ->with('import_errors', $import->getErrors());
        } catch (\Throwable $exception) {
            return redirect()
                ->route('autenticacion-usuarios-seguridad.importaciones.index')
                ->with('import_errors', ['No se pudo procesar el archivo: '.$exception->getMessage()])
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
