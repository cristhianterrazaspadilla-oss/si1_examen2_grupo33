<?php

namespace App\Http\Controllers\GestionAcademicaCUP;

use App\Http\Controllers\Controller;
use App\Models\Docente;
use App\Models\Evaluacion;
use App\Models\Grupo;
use App\Models\GrupoPostulante;
use App\Models\Materia;
use App\Models\Nota;
use App\Models\Postulante;
use App\Support\BitacoraHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/**
 * Paquete: Gestión Académica del CUP
 * Caso de Uso: CU14 - Registro de notas y seguimiento académico.
 *
 * Administra el registro de calificaciones de los postulantes para las evaluaciones académicas (1, 2 y 3).
 * Cuenta con control de alcance de visibilidad según el rol del usuario (ej. docente limitado a sus asignaciones).
 */
class NotaController extends Controller
{
    /**
     * Muestra el listado de calificaciones registradas con filtros por materia, evaluación y grupo.
     */
    public function index(Request $request): View
    {
        $filters = $this->extractFilters($request);
        $access = $this->accessContext();
        $query = $this->buildNotasQuery($filters, $access);

        $notas = (clone $query)
            ->orderByDesc('notas.created_at')
            ->paginate(10)
            ->withQueryString();

        $gruposActuales = $this->buildGrupoActualMap($notas->pluck('postulante_id')->all());

        return view('gestion_academica_cup.notas.index', [
            'notas' => $notas,
            'gruposActuales' => $gruposActuales,
            'filters' => $filters,
            'formOptions' => $this->formOptions($access),
            'totalNotasRegistradas' => (clone $query)->count('notas.id'),
            'postulantesEvaluados' => (clone $query)->distinct()->count('notas.postulante_id'),
            'materiasConNotas' => (clone $query)->distinct()->count('notas.materia_id'),
            'evaluacionesPendientes' => $this->pendingEvaluationsCount($filters, $access),
            'scopeMessage' => $this->scopeMessage($access),
        ]);
    }

    public function create(): View
    {
        $access = $this->accessContext();

        return view('gestion_academica_cup.notas.create', [
            'formOptions' => $this->formOptions($access),
            'scopeMessage' => $this->scopeMessage($access),
        ]);
    }

    /**
     * Registra una nueva calificación en el sistema.
     * Resuelve el contexto académico y valida la pertenencia activa del postulante al grupo, registrando en bitácora.
     */
    public function store(Request $request): RedirectResponse
    {
        $access = $this->accessContext();
        $validated = $this->validateNotaRequest($request);
        $resolved = $this->resolveAcademicContext($validated, $access);

        $nota = Nota::create([
            'postulante_id' => $resolved['postulante']->id,
            'evaluacion_id' => $resolved['evaluacion']->id,
            'materia_id' => $resolved['materia']->id,
            'nota' => $validated['nota'],
            'observacion' => $validated['observacion'] ?? null,
            'registrado_por' => auth()->id(),
        ]);

        BitacoraHelper::registrar(
            'REGISTRAR_NOTA',
            'Notas',
            'Se registro nota para el postulante CI ' . $resolved['postulante']->ci . ' en ' . $resolved['materia']->nombre . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.notas.show', $nota)
            ->with('success', 'Nota registrada correctamente.');
    }

    public function show(Nota $nota): View
    {
        $nota->load(['postulante', 'evaluacion.materia', 'materia', 'registradoPor']);

        return view('gestion_academica_cup.notas.show', [
            'nota' => $nota,
            'grupoActual' => $this->grupoActualDelPostulante($nota->postulante_id),
        ]);
    }

    public function edit(Nota $nota): View
    {
        $access = $this->accessContext();
        $nota->load(['postulante', 'evaluacion', 'materia']);

        return view('gestion_academica_cup.notas.edit', [
            'nota' => $nota,
            'grupoActual' => $this->grupoActualDelPostulante($nota->postulante_id),
            'formOptions' => $this->formOptions($access),
            'scopeMessage' => $this->scopeMessage($access),
        ]);
    }

    /**
     * Actualiza una calificación previamente registrada.
     * Valida que no exista duplicidad y registra la actualización en la bitácora de auditoría.
     */
    public function update(Request $request, Nota $nota): RedirectResponse
    {
        $access = $this->accessContext();
        $validated = $this->validateNotaRequest($request);
        $resolved = $this->resolveAcademicContext($validated, $access, $nota);

        $nota->update([
            'postulante_id' => $resolved['postulante']->id,
            'evaluacion_id' => $resolved['evaluacion']->id,
            'materia_id' => $resolved['materia']->id,
            'nota' => $validated['nota'],
            'observacion' => $validated['observacion'] ?? null,
            'registrado_por' => auth()->id() ?? $nota->registrado_por,
        ]);

        BitacoraHelper::registrar(
            'ACTUALIZAR_NOTA',
            'Notas',
            'Se actualizo nota para el postulante CI ' . $resolved['postulante']->ci . ' en ' . $resolved['materia']->nombre . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.notas.show', $nota)
            ->with('success', 'Nota actualizada correctamente.');
    }

    /**
     * Muestra la sábana o matriz de seguimiento académico.
     * Permite visualizar de forma consolidada el avance y promedio ponderado de las 3 evaluaciones obligatorias por materia.
     */
    public function seguimiento(Request $request): View
    {
        $filters = $this->extractFilters($request);
        $access = $this->accessContext();
        $rows = $this->buildSeguimientoRows($filters, $access);

        return view('gestion_academica_cup.notas.seguimiento', [
            'rows' => $rows,
            'filters' => $filters,
            'formOptions' => $this->formOptions($access),
            'scopeMessage' => $this->scopeMessage($access),
        ]);
    }

    protected function validateNotaRequest(Request $request): array
    {
        return $request->validate([
            'grupo_id' => ['required', 'exists:grupos,id'],
            'postulante_id' => ['required', 'exists:postulantes,id'],
            'materia_id' => ['required', 'exists:materias,id'],
            'evaluacion_id' => ['required', 'exists:evaluaciones,id'],
            'nota' => ['required', 'numeric', 'min:0', 'max:100'],
            'observacion' => ['nullable', 'string'],
        ]);
    }

    protected function accessContext(): array
    {
        $user = auth()->user();
        $roleName = Str::of((string) ($user?->rol?->nombre ?? ''))->lower()->ascii()->toString();
        $docenteId = null;
        $isDocenteScope = false;

        if ($roleName === 'docente' && $user?->docente && $user->docente->estado === 'ACTIVO') {
            $docenteId = $user->docente->id;
            $isDocenteScope = true;
        }

        return [
            'role_name' => $roleName,
            'docente_id' => $docenteId,
            'is_docente_scope' => $isDocenteScope,
        ];
    }

    protected function scopeMessage(array $access): ?string
    {
        if (! $access['is_docente_scope']) {
            return null;
        }

        return 'Tu acceso esta limitado a grupos y materias con asignaciones activas de tu perfil docente.';
    }

    protected function buildNotasQuery(array $filters, array $access): Builder
    {
        return Nota::query()
            ->select('notas.*')
            ->with(['postulante', 'evaluacion', 'materia', 'registradoPor'])
            ->when($filters['postulante_id'] !== '', fn (Builder $query) => $query->where('notas.postulante_id', $filters['postulante_id']))
            ->when($filters['materia_id'] !== '', fn (Builder $query) => $query->where('notas.materia_id', $filters['materia_id']))
            ->when($filters['evaluacion_id'] !== '', fn (Builder $query) => $query->where('notas.evaluacion_id', $filters['evaluacion_id']))
            ->when($filters['grupo_id'] !== '' || $filters['gestion'] !== '' || $filters['docente_id'] !== '', function (Builder $query) use ($filters): void {
                $query->whereExists(function ($subQuery) use ($filters): void {
                    $subQuery->selectRaw('1')
                        ->from('grupo_postulantes')
                        ->join('grupos', 'grupos.id', '=', 'grupo_postulantes.grupo_id')
                        ->whereColumn('grupo_postulantes.postulante_id', 'notas.postulante_id')
                        ->where('grupo_postulantes.estado', 'ACTIVO')
                        ->where('grupos.estado', 'ACTIVO')
                        ->when($filters['grupo_id'] !== '', fn ($builder) => $builder->where('grupo_postulantes.grupo_id', $filters['grupo_id']))
                        ->when($filters['gestion'] !== '', fn ($builder) => $builder->where('grupos.gestion', $filters['gestion']))
                        ->when($filters['docente_id'] !== '', function ($builder) use ($filters): void {
                            $builder->whereExists(function ($assignmentQuery) use ($filters): void {
                                $assignmentQuery->selectRaw('1')
                                    ->from('docente_asignaciones')
                                    ->whereColumn('docente_asignaciones.grupo_id', 'grupo_postulantes.grupo_id')
                                    ->whereColumn('docente_asignaciones.materia_id', 'notas.materia_id')
                                    ->whereColumn('docente_asignaciones.gestion', 'grupos.gestion')
                                    ->where('docente_asignaciones.estado', 'ACTIVO')
                                    ->where('docente_asignaciones.docente_id', $filters['docente_id']);
                            });
                        });
                });
            })
            ->when($access['is_docente_scope'], function (Builder $query) use ($access): void {
                $query->whereExists(function ($subQuery) use ($access): void {
                    $subQuery->selectRaw('1')
                        ->from('grupo_postulantes')
                        ->join('grupos', 'grupos.id', '=', 'grupo_postulantes.grupo_id')
                        ->join('docente_asignaciones', function ($join): void {
                            $join->on('docente_asignaciones.grupo_id', '=', 'grupo_postulantes.grupo_id')
                                ->on('docente_asignaciones.materia_id', '=', 'notas.materia_id')
                                ->on('docente_asignaciones.gestion', '=', 'grupos.gestion');
                        })
                        ->whereColumn('grupo_postulantes.postulante_id', 'notas.postulante_id')
                        ->where('grupo_postulantes.estado', 'ACTIVO')
                        ->where('grupos.estado', 'ACTIVO')
                        ->where('docente_asignaciones.estado', 'ACTIVO')
                        ->where('docente_asignaciones.docente_id', $access['docente_id']);
                });
            });
    }

    /**
     * Resuelve y valida el contexto académico previo a registrar o editar una nota.
     * Verifica que el postulante esté INSCRITO, pertenezca al grupo activo, que la materia y evaluación existan y
     * correspondan, y que el docente tenga asignada la materia en la gestión correspondiente.
     */
    protected function resolveAcademicContext(array $validated, array $access, ?Nota $currentNota = null): array
    {
        $grupo = Grupo::query()->findOrFail((int) $validated['grupo_id']);
        $postulante = Postulante::query()->findOrFail((int) $validated['postulante_id']);
        $materia = Materia::query()->findOrFail((int) $validated['materia_id']);
        $evaluacion = Evaluacion::query()->findOrFail((int) $validated['evaluacion_id']);

        $errors = [];

        if ($grupo->estado !== 'ACTIVO') {
            $errors['grupo_id'] = 'Solo se puede registrar notas en grupos activos.';
        }

        if ($postulante->estado_inscripcion !== 'INSCRITO') {
            $errors['postulante_id'] = 'El postulante debe estar INSCRITO para registrar notas.';
        }

        if ($materia->estado !== 'ACTIVO') {
            $errors['materia_id'] = 'La materia seleccionada esta inactiva.';
        }

        if ($evaluacion->estado !== 'ACTIVO') {
            $errors['evaluacion_id'] = 'La evaluacion seleccionada esta inactiva.';
        }

        if ((int) $evaluacion->materia_id !== (int) $materia->id) {
            $errors['evaluacion_id'] = 'La evaluacion seleccionada no pertenece a la materia indicada.';
        }

        $belongsToGroup = GrupoPostulante::query()
            ->where('grupo_id', $grupo->id)
            ->where('postulante_id', $postulante->id)
            ->where('estado', 'ACTIVO')
            ->exists();

        if (! $belongsToGroup) {
            $errors['postulante_id'] = 'El postulante no pertenece activamente al grupo seleccionado.';
        }

        $asignacionQuery = DB::table('docente_asignaciones')
            ->where('grupo_id', $grupo->id)
            ->where('materia_id', $materia->id)
            ->where('gestion', $grupo->gestion)
            ->where('estado', 'ACTIVO');

        if ($access['is_docente_scope']) {
            $asignacionQuery->where('docente_id', $access['docente_id']);
        }

        if (! $asignacionQuery->exists()) {
            $errors['materia_id'] = $access['is_docente_scope']
                ? 'No tienes una asignacion activa para registrar notas de esa materia en el grupo seleccionado.'
                : 'No existe una asignacion docente activa para la materia y grupo seleccionados.';
        }

        $duplicateQuery = Nota::query()
            ->where('postulante_id', $postulante->id)
            ->where('evaluacion_id', $evaluacion->id);

        if ($currentNota) {
            $duplicateQuery->where('id', '!=', $currentNota->id);
        }

        if ($duplicateQuery->exists()) {
            $errors['evaluacion_id'] = 'Ya existe una nota registrada para el mismo postulante y evaluacion.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return compact('grupo', 'postulante', 'materia', 'evaluacion');
    }

    protected function formOptions(array $access): array
    {
        $assignmentRows = DB::table('docente_asignaciones')
            ->join('grupos', 'grupos.id', '=', 'docente_asignaciones.grupo_id')
            ->join('materias', 'materias.id', '=', 'docente_asignaciones.materia_id')
            ->join('docentes', 'docentes.id', '=', 'docente_asignaciones.docente_id')
            ->where('docente_asignaciones.estado', 'ACTIVO')
            ->where('grupos.estado', 'ACTIVO')
            ->where('materias.estado', 'ACTIVO')
            ->where('docentes.estado', 'ACTIVO')
            ->whereColumn('docente_asignaciones.gestion', 'grupos.gestion')
            ->when($access['is_docente_scope'], fn ($query) => $query->where('docente_asignaciones.docente_id', $access['docente_id']))
            ->orderByDesc('grupos.gestion')
            ->orderBy('grupos.nombre')
            ->orderBy('materias.nombre')
            ->get([
                'docente_asignaciones.docente_id',
                'docente_asignaciones.grupo_id',
                'docente_asignaciones.materia_id',
                'grupos.nombre as grupo_nombre',
                'grupos.gestion as grupo_gestion',
                'materias.nombre as materia_nombre',
                'docentes.nombres as docente_nombres',
                'docentes.apellidos as docente_apellidos',
            ]);

        $grupoIds = $assignmentRows->pluck('grupo_id')->unique()->values();
        $materiaIds = $assignmentRows->pluck('materia_id')->unique()->values();
        $docenteIds = $assignmentRows->pluck('docente_id')->unique()->values();

        $grupos = Grupo::query()
            ->whereIn('id', $grupoIds)
            ->orderByDesc('gestion')
            ->orderBy('nombre')
            ->get();

        $materias = Materia::query()
            ->whereIn('id', $materiaIds)
            ->orderBy('nombre')
            ->get();

        $evaluaciones = Evaluacion::query()
            ->with('materia')
            ->where('estado', 'ACTIVO')
            ->whereIn('materia_id', $materiaIds)
            ->orderBy('materia_id')
            ->orderBy('numero_evaluacion')
            ->get();

        $postulantes = Postulante::query()
            ->where('estado_inscripcion', 'INSCRITO')
            ->whereExists(function ($query) use ($grupoIds): void {
                $query->selectRaw('1')
                    ->from('grupo_postulantes')
                    ->whereColumn('grupo_postulantes.postulante_id', 'postulantes.id')
                    ->where('grupo_postulantes.estado', 'ACTIVO')
                    ->whereIn('grupo_postulantes.grupo_id', $grupoIds);
            })
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        $docentes = Docente::query()
            ->whereIn('id', $docenteIds)
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();

        return [
            'grupos' => $grupos,
            'materias' => $materias,
            'evaluaciones' => $evaluaciones,
            'postulantes' => $postulantes,
            'docentes' => $docentes,
            'groupAssignments' => $assignmentRows->groupBy('grupo_id'),
        ];
    }

    protected function buildGrupoActualMap(array $postulanteIds): array
    {
        if ($postulanteIds === []) {
            return [];
        }

        return GrupoPostulante::query()
            ->with('grupo')
            ->whereIn('postulante_id', $postulanteIds)
            ->where('estado', 'ACTIVO')
            ->orderByDesc('id')
            ->get()
            ->unique('postulante_id')
            ->mapWithKeys(fn (GrupoPostulante $grupoPostulante) => [$grupoPostulante->postulante_id => $grupoPostulante])
            ->all();
    }

    protected function grupoActualDelPostulante(int $postulanteId): ?GrupoPostulante
    {
        return GrupoPostulante::query()
            ->with('grupo')
            ->where('postulante_id', $postulanteId)
            ->where('estado', 'ACTIVO')
            ->latest('id')
            ->first();
    }

    protected function extractFilters(Request $request): array
    {
        return [
            'gestion' => $request->string('gestion')->toString(),
            'grupo_id' => $request->string('grupo_id')->toString(),
            'materia_id' => $request->string('materia_id')->toString(),
            'evaluacion_id' => $request->string('evaluacion_id')->toString(),
            'postulante_id' => $request->string('postulante_id')->toString(),
            'docente_id' => $request->string('docente_id')->toString(),
        ];
    }

    protected function pendingEvaluationsCount(array $filters, array $access): int
    {
        $rows = $this->buildSeguimientoRows($filters, $access);

        return $rows->sum(fn (array $row) => count($row['faltantes']));
    }

    /**
     * Obtiene los datos consolidados de postulantes y sus evaluaciones por materia.
     * Calcula dinámicamente el promedio ponderado final basándose en el porcentaje de cada evaluación (1, 2, 3)
     * e identifica si el registro académico del postulante está incompleto.
     */
    protected function buildSeguimientoRows(array $filters, array $access)
    {
        $pairs = DB::table('grupo_postulantes')
            ->join('grupos', 'grupos.id', '=', 'grupo_postulantes.grupo_id')
            ->join('postulantes', 'postulantes.id', '=', 'grupo_postulantes.postulante_id')
            ->join('docente_asignaciones', function ($join): void {
                $join->on('docente_asignaciones.grupo_id', '=', 'grupo_postulantes.grupo_id')
                    ->on('docente_asignaciones.gestion', '=', 'grupos.gestion');
            })
            ->join('materias', 'materias.id', '=', 'docente_asignaciones.materia_id')
            ->where('grupo_postulantes.estado', 'ACTIVO')
            ->where('grupos.estado', 'ACTIVO')
            ->where('postulantes.estado_inscripcion', 'INSCRITO')
            ->where('docente_asignaciones.estado', 'ACTIVO')
            ->where('materias.estado', 'ACTIVO')
            ->when($filters['gestion'] !== '', fn ($query) => $query->where('grupos.gestion', $filters['gestion']))
            ->when($filters['grupo_id'] !== '', fn ($query) => $query->where('grupo_postulantes.grupo_id', $filters['grupo_id']))
            ->when($filters['postulante_id'] !== '', fn ($query) => $query->where('grupo_postulantes.postulante_id', $filters['postulante_id']))
            ->when($filters['materia_id'] !== '', fn ($query) => $query->where('docente_asignaciones.materia_id', $filters['materia_id']))
            ->when($access['is_docente_scope'], fn ($query) => $query->where('docente_asignaciones.docente_id', $access['docente_id']))
            ->orderByDesc('grupos.gestion')
            ->orderBy('grupos.nombre')
            ->orderBy('postulantes.apellidos')
            ->orderBy('postulantes.nombres')
            ->select([
                'grupo_postulantes.postulante_id',
                'grupo_postulantes.grupo_id',
                'docente_asignaciones.materia_id',
                'grupos.nombre as grupo_nombre',
                'grupos.gestion as grupo_gestion',
                'postulantes.ci as postulante_ci',
                'postulantes.nombres as postulante_nombres',
                'postulantes.apellidos as postulante_apellidos',
                'materias.nombre as materia_nombre',
            ])
            ->distinct()
            ->get();

        if ($pairs->isEmpty()) {
            return collect();
        }

        $materiaIds = $pairs->pluck('materia_id')->unique()->values();
        $postulanteIds = $pairs->pluck('postulante_id')->unique()->values();

        $evaluaciones = Evaluacion::query()
            ->where('estado', 'ACTIVO')
            ->whereIn('materia_id', $materiaIds)
            ->orderBy('materia_id')
            ->orderBy('numero_evaluacion')
            ->get()
            ->groupBy('materia_id');

        $notas = Nota::query()
            ->with('evaluacion')
            ->whereIn('postulante_id', $postulanteIds)
            ->whereIn('materia_id', $materiaIds)
            ->get()
            ->groupBy(fn (Nota $nota) => $nota->postulante_id . '-' . $nota->materia_id);

        return $pairs->map(function ($pair) use ($evaluaciones, $notas): array {
            $materiaEvaluaciones = $evaluaciones->get($pair->materia_id, collect())->keyBy('numero_evaluacion');
            $notasPorClave = $notas->get($pair->postulante_id . '-' . $pair->materia_id, collect())
                ->keyBy(fn (Nota $nota) => (int) ($nota->evaluacion?->numero_evaluacion ?? 0));

            $faltantes = [];
            $promedio = null;
            $weightedSum = 0.0;
            $complete = true;
            $evaluacionesRow = [];

            foreach ([1, 2, 3] as $numero) {
                $evaluacion = $materiaEvaluaciones->get($numero);
                $nota = $notasPorClave->get($numero);

                if (! $evaluacion || ! $nota) {
                    $complete = false;
                    $faltantes[] = 'Evaluacion ' . $numero;
                }

                if ($evaluacion && $nota) {
                    $weightedSum += ((float) $nota->nota) * (((float) $evaluacion->porcentaje) / 100);
                }

                $evaluacionesRow[$numero] = [
                    'evaluacion' => $evaluacion,
                    'nota' => $nota,
                ];
            }

            if ($complete) {
                $promedio = number_format($weightedSum, 2, '.', '');
            }

            return [
                'postulante_id' => $pair->postulante_id,
                'postulante_nombre' => trim($pair->postulante_nombres . ' ' . $pair->postulante_apellidos),
                'postulante_ci' => $pair->postulante_ci,
                'grupo_nombre' => $pair->grupo_nombre,
                'gestion' => $pair->grupo_gestion,
                'materia_nombre' => $pair->materia_nombre,
                'evaluaciones' => $evaluacionesRow,
                'promedio' => $promedio,
                'estado' => $complete ? 'Completo' : 'Incompleto',
                'faltantes' => $faltantes,
            ];
        });
    }
}
