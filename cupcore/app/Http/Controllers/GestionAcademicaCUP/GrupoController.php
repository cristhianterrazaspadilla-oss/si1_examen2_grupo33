<?php

namespace App\Http\Controllers\GestionAcademicaCUP;

use App\Http\Controllers\Controller;
use App\Models\Grupo;
use App\Models\GrupoPostulante;
use App\Models\Postulante;
use App\Support\BitacoraHelper;
use App\Support\NotificacionHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Throwable;

/**
 * Paquete: Gestión Académica del CUP
 * Caso de Uso: CU10 (Organizar Grupos Académicos)
 * 
 * Implementa el algoritmo de distribución y agrupamiento automático de postulantes inscritos.
 * Organiza a los estudiantes en grupos alfabéticos con capacidad parametrizable (máximo 70 estudiantes).
 */
class GrupoController extends Controller
{
    // Controlador del caso de uso: CU10 Organizar Grupos Academicos
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $gestion = $request->string('gestion')->toString();
        $estado = $request->string('estado')->toString();
        $gestionResumen = $gestion !== '' ? $gestion : $this->defaultGestion();

        $grupos = Grupo::query()
            ->withCount([
                'grupoPostulantes as asignaciones_activas_count' => fn ($query) => $query->where('estado', 'ACTIVO'),
            ])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('codigo', 'like', "%{$search}%")
                        ->orWhere('gestion', 'like', "%{$search}%");
                });
            })
            ->when($gestion !== '', fn ($query) => $query->where('gestion', $gestion))
            ->when($estado !== '', fn ($query) => $query->where('estado', $estado))
            ->orderByDesc('gestion')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        $resumenQuery = Grupo::query()
            ->where('estado', 'ACTIVO')
            ->where('gestion', $gestionResumen);

        $totalGruposActivos = (clone $resumenQuery)->count();
        $totalEstudiantesAsignados = (int) (clone $resumenQuery)->sum('cantidad_estudiantes');
        $capacidadTotal = (int) (clone $resumenQuery)->sum('capacidad_maxima');
        $postulantesSinGrupo = $this->getPostulantesDisponibles($gestionResumen)->count();

        return view('gestion_academica_cup.grupos.index', [
            'grupos' => $grupos,
            'search' => $search,
            'gestion' => $gestion,
            'estado' => $estado,
            'gestionesAcademicas' => $this->gestionesAcademicas(),
            'totalGruposActivos' => $totalGruposActivos,
            'totalEstudiantesAsignados' => $totalEstudiantesAsignados,
            'capacidadTotal' => $capacidadTotal,
            'postulantesSinGrupo' => $postulantesSinGrupo,
            'gestionResumen' => $gestionResumen,
        ]);
    }

    public function create(): View
    {
        $gestion = request()->string('gestion')->toString() ?: $this->defaultGestion();
        $capacidadMaxima = max(1, min(70, (int) request()->integer('capacidad_maxima', 70)));
        $resumen = $this->buildOrganizacionResumen($gestion, $capacidadMaxima);

        return view('gestion_academica_cup.grupos.create', [
            'gestionesAcademicas' => $this->gestionesAcademicas(),
            'gestion' => $gestion,
            'capacidadMaxima' => $capacidadMaxima,
            'resumen' => $resumen,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'gestion' => ['required', 'string', 'max:20', 'regex:/^[12]-[0-9]{4}$/'],
            'capacidad_maxima' => ['required', 'integer', 'min:1', 'max:70'],
        ]);

        $gestion = $validated['gestion'];
        $capacidadMaxima = (int) $validated['capacidad_maxima'];
        $postulantesDisponibles = $this->getPostulantesDisponibles($gestion)->values();
        $gruposActivosExistentes = Grupo::query()
            ->where('gestion', $gestion)
            ->where('estado', 'ACTIVO')
            ->count();

        if ($postulantesDisponibles->isEmpty()) {
            return redirect()
                ->route('gestion-academica-cup.grupos.create', ['gestion' => $gestion])
                ->with('info', $gruposActivosExistentes > 0
                    ? 'No existen postulantes INSCRITOS disponibles para la gestion seleccionada. Ya hay grupos activos para esa gestion.'
                    : 'No existen postulantes INSCRITOS disponibles para la gestion seleccionada.');
        }

        if ($gruposActivosExistentes > 0) {
            return redirect()
                ->route('gestion-academica-cup.grupos.create', ['gestion' => $gestion])
                ->withErrors(['gestion' => 'Ya existen grupos activos para esa gestion. Evita generar duplicados sin revision previa.']);
        }

        $cantidadGrupos = (int) ceil($postulantesDisponibles->count() / $capacidadMaxima);
        $codigosExistentes = Grupo::query()
            ->where('gestion', $gestion)
            ->pluck('codigo')
            ->filter()
            ->values()
            ->all();
        $nombresExistentes = Grupo::query()
            ->where('gestion', $gestion)
            ->pluck('nombre')
            ->filter()
            ->values()
            ->all();
        $chunkedPostulantes = $postulantesDisponibles->chunk($capacidadMaxima)->values();
        $gruposPlanificados = [];

        for ($index = 0; $index < $cantidadGrupos; $index++) {
            $label = $this->groupLabel($index);
            $nombreBase = 'Grupo ' . $label;
            $codigoBase = 'CUP-' . $label . '-' . $gestion;
            $nombre = $this->resolveUniqueValueInMemory($nombreBase, $nombresExistentes, ' ');
            $codigo = $this->resolveUniqueValueInMemory($codigoBase, $codigosExistentes, '-');

            $nombresExistentes[] = $nombre;
            $codigosExistentes[] = $codigo;

            $gruposPlanificados[] = [
                'nombre' => $nombre,
                'codigo' => $codigo,
                'gestion' => $gestion,
                'capacidad_maxima' => $capacidadMaxima,
                'postulantes' => $chunkedPostulantes->get($index, collect())->values()->all(),
            ];
        }

        foreach ($gruposPlanificados as $grupoPlanificado) {
            try {
                $grupo = Grupo::create([
                    'nombre' => $grupoPlanificado['nombre'],
                    'codigo' => $grupoPlanificado['codigo'],
                    'gestion' => $grupoPlanificado['gestion'],
                    'capacidad_maxima' => $grupoPlanificado['capacidad_maxima'],
                    'cantidad_estudiantes' => 0,
                    'estado' => 'ACTIVO',
                ]);
            } catch (Throwable $exception) {
                Log::error('Error real al crear grupo academico', [
                    'gestion' => $gestion,
                    'grupo_planificado' => [
                        'nombre' => $grupoPlanificado['nombre'],
                        'codigo' => $grupoPlanificado['codigo'],
                        'gestion' => $grupoPlanificado['gestion'],
                        'capacidad_maxima' => $grupoPlanificado['capacidad_maxima'],
                        'cantidad_postulantes' => count($grupoPlanificado['postulantes']),
                    ],
                    'error' => $exception->getMessage(),
                ]);

                return redirect()
                    ->back()
                    ->withInput()
                    ->withErrors(['organizacion' => 'No se pudo crear el grupo. Inténtalo nuevamente.']);
            }

            BitacoraHelper::registrar(
                'CREAR_GRUPO',
                'Grupos',
                'Se creo el grupo ' . $grupo->nombre . ' gestion ' . $grupo->gestion . '.'
            );

            foreach ($grupoPlanificado['postulantes'] as $postulante) {
                try {
                    GrupoPostulante::updateOrCreate(
                        [
                            'grupo_id' => $grupo->id,
                            'postulante_id' => $postulante->id,
                        ],
                        [
                            'fecha_asignacion' => now()->toDateString(),
                            'estado' => 'ACTIVO',
                        ]
                    );
                } catch (Throwable $exception) {
                    Log::error('Error real al asignar postulante a grupo', [
                        'grupo_id' => $grupo->id,
                        'postulante_id' => $postulante->id,
                        'gestion' => $gestion,
                        'error' => $exception->getMessage(),
                    ]);

                    return redirect()
                        ->back()
                        ->withInput()
                        ->withErrors(['organizacion' => 'No se pudo asignar el postulante al grupo. Inténtalo nuevamente.']);
                }

                BitacoraHelper::registrar(
                    'ASIGNAR_POSTULANTE_GRUPO',
                    'Grupos',
                    'Se asigno el postulante CI ' . $postulante->ci . ' al grupo ' . $grupo->nombre . '.'
                );

                NotificacionHelper::enviar(
                    $postulante->usuario_id,
                    'Asignación de grupo',
                    'Fuiste asignado al ' . $grupo->nombre . ' para la gestión ' . $grupo->gestion . '.',
                    'GRUPO'
                );
            }

            $grupo->cantidad_estudiantes = GrupoPostulante::query()
                ->where('grupo_id', $grupo->id)
                ->where('estado', 'ACTIVO')
                ->count();
            $grupo->save();
        }

        BitacoraHelper::registrar(
            'ORGANIZAR_GRUPOS',
            'Grupos',
            'Se organizo la distribucion de grupos academicos para la gestion ' . $gestion . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.grupos.index', ['gestion' => $gestion])
            ->with('success', 'Grupos generados correctamente para la gestion ' . $gestion . '.');
    }

    public function show(Grupo $grupo): View
    {
        $grupo->load([
            'grupoPostulantes' => fn ($query) => $query
                ->where('estado', 'ACTIVO')
                ->with('postulante')
                ->orderBy('fecha_asignacion'),
        ]);

        $ocupacion = $grupo->capacidad_maxima > 0
            ? round(($grupo->cantidad_estudiantes / $grupo->capacidad_maxima) * 100, 2)
            : 0;

        return view('gestion_academica_cup.grupos.show', [
            'grupo' => $grupo,
            'ocupacion' => $ocupacion,
        ]);
    }

    public function edit(Grupo $grupo): View
    {
        return view('gestion_academica_cup.grupos.edit', [
            'grupo' => $grupo,
            'gestionesAcademicas' => $this->gestionesAcademicas(),
        ]);
    }

    public function update(Request $request, Grupo $grupo): RedirectResponse
    {
        $validated = $request->validate([
            'nombre' => ['required', 'string', 'max:100'],
            'codigo' => ['required', 'string', 'max:50', Rule::unique('grupos', 'codigo')->ignore($grupo->id)],
            'gestion' => ['required', 'string', 'max:20', 'regex:/^[12]-[0-9]{4}$/'],
            'capacidad_maxima' => ['required', 'integer', 'min:' . max(1, $grupo->cantidad_estudiantes), 'max:70'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        $grupo->update($validated);
        BitacoraHelper::registrar(
            'ACTUALIZAR_GRUPO',
            'Grupos',
            'Se actualizo el grupo ' . $grupo->nombre . ' gestion ' . $grupo->gestion . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.grupos.show', $grupo)
            ->with('success', 'Grupo actualizado correctamente.');
    }

    public function destroy(Grupo $grupo): RedirectResponse
    {
        DB::transaction(function () use ($grupo): void {
            $grupo->update(['estado' => 'INACTIVO']);

            GrupoPostulante::query()
                ->where('grupo_id', $grupo->id)
                ->where('estado', 'ACTIVO')
                ->update([
                    'estado' => 'INACTIVO',
                    'updated_at' => now(),
                ]);
        });

        BitacoraHelper::registrar(
            'DESACTIVAR_GRUPO',
            'Grupos',
            'Se desactivo el grupo ' . $grupo->nombre . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.grupos.index')
            ->with('success', 'Grupo desactivado correctamente.');
    }

    protected function defaultGestion(): string
    {
        return '1-' . now()->year;
    }

    protected function gestionesAcademicas(): array
    {
        $currentYear = now()->year;
        $gestiones = [];

        for ($year = $currentYear; $year <= $currentYear + 5; $year++) {
            $gestiones[] = '1-' . $year;
            $gestiones[] = '2-' . $year;
        }

        return $gestiones;
    }

    protected function buildOrganizacionResumen(string $gestion, int $capacidadMaxima): array
    {
        $inscritos = Postulante::query()
            ->where('estado_inscripcion', 'INSCRITO')
            ->count();

        $yaAsignados = GrupoPostulante::query()
            ->where('estado', 'ACTIVO')
            ->whereHas('grupo', fn ($query) => $query->where('gestion', $gestion)->where('estado', 'ACTIVO'))
            ->distinct('postulante_id')
            ->count('postulante_id');

        $gruposActivosGestion = Grupo::query()
            ->where('gestion', $gestion)
            ->where('estado', 'ACTIVO')
            ->count();

        $disponibles = $this->getPostulantesDisponibles($gestion)->count();
        $gruposNecesarios = $disponibles > 0 ? (int) ceil($disponibles / max(1, $capacidadMaxima)) : 0;

        return [
            'inscritos' => $inscritos,
            'ya_asignados' => $yaAsignados,
            'disponibles' => $disponibles,
            'capacidad_maxima' => $capacidadMaxima,
            'grupos_necesarios' => $gruposNecesarios,
            'gestion' => $gestion,
            'grupos_activos_gestion' => $gruposActivosGestion,
        ];
    }

    protected function getPostulantesDisponibles(string $gestion)
    {
        return Postulante::query()
            ->where('estado_inscripcion', 'INSCRITO')
            ->whereDoesntHave('grupoPostulantes', function ($query) use ($gestion): void {
                $query->where('estado', 'ACTIVO')
                    ->whereHas('grupo', fn ($subQuery) => $subQuery->where('gestion', $gestion)->where('estado', 'ACTIVO'));
            })
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();
    }

    protected function groupLabel(int $index): string
    {
        $alphabet = range('A', 'Z');
        $label = '';
        $current = $index;

        do {
            $label = $alphabet[$current % 26] . $label;
            $current = intdiv($current, 26) - 1;
        } while ($current >= 0);

        return $label;
    }

    protected function resolveUniqueValueInMemory(string $baseValue, array $existingValues, string $separator): string
    {
        $value = $baseValue;
        $suffix = 2;

        while (in_array($value, $existingValues, true)) {
            $value = $baseValue . $separator . $suffix;
            $suffix++;
        }

        return $value;
    }
}
