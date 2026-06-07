<?php

namespace App\Http\Controllers\GestionAcademicaCUP;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

class AulaController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $estado = $request->string('estado')->toString();

        $aulas = Aula::query()
            ->withCount([
                'horarios as horarios_activos_count' => fn ($query) => $query->where('estado', 'ACTIVO'),
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo', 'like', "%{$search}%")
                        ->orWhere('ubicacion', 'like', "%{$search}%");
                });
            })
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('gestion_academica_cup.aulas.index', [
            'aulas' => $aulas,
            'search' => $search,
            'estado' => $estado,
        ]);
    }

    public function create(): View
    {
        return view('gestion_academica_cup.aulas.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'codigo' => ['nullable', 'string', 'max:30', 'unique:aulas,codigo'],
            'capacidad' => ['required', 'integer', 'min:1'],
            'ubicacion' => ['nullable', 'string', 'max:150'],
            'estado' => ['nullable', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $aula = Aula::create([
            'nombre' => $validated['nombre'],
            'codigo' => $validated['codigo'] ?? null,
            'capacidad' => $validated['capacidad'],
            'ubicacion' => $validated['ubicacion'] ?? null,
            'estado' => $validated['estado'] ?? 'ACTIVO',
        ]);

        return redirect()
            ->route('gestion-academica-cup.aulas.show', $aula)
            ->with('success', 'Aula creada correctamente.');
    }

    public function show(Aula $aula): View
    {
        $horariosActivos = DB::table('horarios')
            ->join('grupos', 'grupos.id', '=', 'horarios.grupo_id')
            ->join('materias', 'materias.id', '=', 'horarios.materia_id')
            ->join('docentes', 'docentes.id', '=', 'horarios.docente_id')
            ->where('horarios.aula_id', $aula->id)
            ->where('horarios.estado', 'ACTIVO')
            ->orderByDesc('grupos.gestion')
            ->orderBy('horarios.dia_semana')
            ->orderBy('horarios.hora_inicio')
            ->select([
                'horarios.id',
                'grupos.gestion as gestion',
                'horarios.dia_semana',
                'horarios.hora_inicio',
                'horarios.hora_fin',
                'grupos.nombre as grupo_nombre',
                'materias.nombre as materia_nombre',
                'docentes.nombres as docente_nombres',
                'docentes.apellidos as docente_apellidos',
            ])
            ->get();

        return view('gestion_academica_cup.aulas.show', [
            'aula' => $aula,
            'horariosActivos' => $horariosActivos,
        ]);
    }

    public function edit(Aula $aula): View
    {
        return view('gestion_academica_cup.aulas.edit', [
            'aula' => $aula,
        ]);
    }

    public function update(Request $request, Aula $aula): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'codigo' => ['nullable', 'string', 'max:30', Rule::unique('aulas', 'codigo')->ignore($aula->id)],
            'capacidad' => ['required', 'integer', 'min:1'],
            'ubicacion' => ['nullable', 'string', 'max:150'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $aula->update($validated);

        return redirect()
            ->route('gestion-academica-cup.aulas.show', $aula)
            ->with('success', 'Aula actualizada correctamente.');
    }

    public function destroy(Aula $aula): RedirectResponse
    {
        try {
            $updated = DB::table('aulas')
                ->where('id', $aula->id)
                ->update([
                    'estado' => 'INACTIVO',
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            Log::error('Error real al desactivar aula', [
                'aula_id' => $aula->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('gestion-academica-cup.aulas.index')
                ->withErrors(['aula' => 'No se pudo desactivar el aula: ' . $exception->getMessage()]);
        }

        if ($updated !== 1) {
            return redirect()
                ->route('gestion-academica-cup.aulas.index')
                ->withErrors(['aula' => 'No se pudo desactivar el aula seleccionada.']);
        }

        return redirect()
            ->route('gestion-academica-cup.aulas.index')
            ->with('success', 'Aula desactivada correctamente.');
    }

    public function activar(Aula $aula): RedirectResponse
    {
        try {
            $updated = DB::table('aulas')
                ->where('id', $aula->id)
                ->update([
                    'estado' => 'ACTIVO',
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            Log::error('Error real al activar aula', [
                'aula_id' => $aula->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('gestion-academica-cup.aulas.index')
                ->withErrors(['aula' => 'No se pudo activar el aula: ' . $exception->getMessage()]);
        }

        if ($updated !== 1) {
            return redirect()
                ->route('gestion-academica-cup.aulas.index')
                ->withErrors(['aula' => 'No se pudo activar el aula seleccionada.']);
        }

        return redirect()
            ->route('gestion-academica-cup.aulas.show', $aula)
            ->with('success', 'Aula activada correctamente.');
    }
}
