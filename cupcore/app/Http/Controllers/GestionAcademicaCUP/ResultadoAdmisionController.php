<?php

namespace App\Http\Controllers\GestionAcademicaCUP;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\CupoCarrera;
use App\Models\Evaluacion;
use App\Models\GrupoPostulante;
use App\Models\Materia;
use App\Models\Postulante;
use App\Models\ResultadoAdmision;
use App\Support\BitacoraHelper;
use App\Support\NotificacionHelper;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

/**
 * Paquete: Gestión Académica del CUP
 * Caso de Uso: CU15 - Generación y recalculo de resultados de admisión.
 *
 * Evalúa el promedio final de notas de los postulantes, define su estado de aprobación (min. 51.00) 
 * y les asigna carrera/cupo disponible según sus opciones de preferencia (1ra y 2da opción).
 */
class ResultadoAdmisionController extends Controller
{
    protected float $notaMinimaAprobacion = 51.00;

    /**
     * Listado general de resultados de admisión con filtros de gestión, carrera y estado del resultado.
     */
    public function index(Request $request): View
    {
        $this->authorizeRoles();
        $filters = $this->extractFilters($request);
        $query = $this->buildResultadosQuery($filters);

        $resultados = (clone $query)
            ->orderByDesc('fecha_resultado')
            ->paginate(10)
            ->withQueryString();

        $grupoMap = $this->buildGrupoActivoMap($resultados->pluck('postulante_id')->all());

        return view('gestion_academica_cup.resultados.index', [
            'resultados' => $resultados,
            'grupoMap' => $grupoMap,
            'filters' => $filters,
            'formOptions' => $this->formOptions(),
            'totales' => [
                'total' => (clone $query)->count('resultados_admision.id'),
                'aprobados' => (clone $query)->where('estado_resultado', 'APROBADO')->count('resultados_admision.id'),
                'reprobados' => (clone $query)->where('estado_resultado', 'REPROBADO')->count('resultados_admision.id'),
                'sin_asignacion' => (clone $query)->where('tipo_asignacion', 'SIN_ASIGNACION')->count('resultados_admision.id'),
            ],
        ]);
    }

    public function create(): View
    {
        $this->authorizeRoles();
        return view('gestion_academica_cup.resultados.generar', [
            'postulantes' => $this->postulantesInscritosSinResultado(),
            'notaMinimaAprobacion' => number_format($this->notaMinimaAprobacion, 2, '.', ''),
        ]);
    }

    /**
     * Genera de forma individual el resultado de admisión para un postulante específico.
     */
    public function store(Request $request): RedirectResponse
    {
        $this->authorizeRoles();
        $validated = $request->validate([
            'postulante_id' => ['required', 'exists:postulantes,id'],
        ]);

        $postulante = Postulante::query()->findOrFail((int) $validated['postulante_id']);

        try {
            $resultado = $this->generarResultadoParaPostulante($postulante);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Error al generar resultado de admisión: ' . $exception->getMessage(), [
                'postulante_id' => $postulante->id,
            ]);

            return back()
                ->withInput()
                ->withErrors(['resultado' => 'No se pudo generar el resultado de admisión. Inténtalo nuevamente.']);
        }

        BitacoraHelper::registrar(
            'GENERAR_RESULTADO',
            'Resultados',
            'Se genero resultado de admision para el postulante CI ' . $postulante->ci . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.resultados.show', $resultado)
            ->with('success', 'Resultado de admision generado correctamente.');
    }

    public function show(ResultadoAdmision $resultado): View
    {
        $this->authorizeRoles();
        $resultado->load([
            'postulante.carreraPrimeraOpcion',
            'postulante.carreraSegundaOpcion',
            'carreraAsignada',
            'modificadoPor',
        ]);

        $grupoActivo = $this->obtenerGrupoActivoDelPostulante($resultado->postulante);
        $calculo = $this->calcularPromediosPostulante($resultado->postulante);

        return view('gestion_academica_cup.resultados.show', [
            'resultado' => $resultado,
            'grupoActivo' => $grupoActivo,
            'calculo' => $calculo,
        ]);
    }

    public function edit(ResultadoAdmision $resultado): View
    {
        $this->authorizeRoles();
        $resultado->load([
            'postulante.carreraPrimeraOpcion',
            'postulante.carreraSegundaOpcion',
            'carreraAsignada',
            'modificadoPor',
        ]);

        return view('gestion_academica_cup.resultados.edit', [
            'resultado' => $resultado,
            'grupoActivo' => $this->obtenerGrupoActivoDelPostulante($resultado->postulante),
            'calculo' => $this->calcularPromediosPostulante($resultado->postulante),
        ]);
    }

    /**
     * Modifica las observaciones del resultado o gatilla el recalculo general de notas y cupos.
     * El recalculo se ejecuta bajo una transacción y exige una justificación de modificación.
     */
    public function update(Request $request, ResultadoAdmision $resultado): RedirectResponse
    {
        $this->authorizeRoles();
        $validated = $request->validate([
            'observacion' => ['nullable', 'string'],
            'justificacion_modificacion' => ['nullable', 'string'],
            'accion' => ['nullable', Rule::in(['actualizar', 'recalcular'])],
        ]);

        $accion = $validated['accion'] ?? 'actualizar';

        if ($accion === 'recalcular' && blank($validated['justificacion_modificacion'] ?? null)) {
            throw ValidationException::withMessages([
                'justificacion_modificacion' => 'La justificacion de modificacion es obligatoria para recalcular.',
            ]);
        }

        if ($accion === 'recalcular') {
            try {
                DB::transaction(function () use ($resultado, $validated): void {
                    $this->recalcularResultadoExistente($resultado, (string) $validated['justificacion_modificacion']);
                    $resultado->update([
                        'observacion' => $validated['observacion'] ?? $resultado->observacion,
                        'justificacion_modificacion' => $validated['justificacion_modificacion'],
                        'modificado_por' => auth()->id(),
                    ]);
                });
            } catch (ValidationException $exception) {
                throw $exception;
            } catch (Throwable $exception) {
                Log::error('Error al recalcular resultado de admisión: ' . $exception->getMessage(), [
                    'resultado_id' => $resultado->id,
                ]);

                return back()
                    ->withInput()
                    ->withErrors(['resultado' => 'No se pudo recalcular el resultado de admisión. Inténtalo nuevamente.']);
            }

            BitacoraHelper::registrar(
                'RECALCULAR_RESULTADO',
                'Resultados',
                'Se recalculo el resultado del postulante CI ' . (string) $resultado->postulante?->ci . '.'
            );

            return redirect()
                ->route('gestion-academica-cup.resultados.show', $resultado)
                ->with('success', 'Resultado recalculado correctamente.');
        }

        $resultado->update([
            'observacion' => $validated['observacion'] ?? null,
            'justificacion_modificacion' => $validated['justificacion_modificacion'] ?? null,
            'modificado_por' => auth()->id(),
        ]);

        BitacoraHelper::registrar(
            'ACTUALIZAR_RESULTADO',
            'Resultados',
            'Se actualizo la observacion del resultado del postulante CI ' . (string) $resultado->postulante?->ci . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.resultados.show', $resultado)
            ->with('success', 'Observacion y justificacion actualizadas correctamente.');
    }

    public function pendientes(Request $request): View
    {
        $this->authorizeRoles();
        $postulantes = Postulante::query()
            ->with(['carreraPrimeraOpcion', 'carreraSegundaOpcion'])
            ->where('estado_inscripcion', 'INSCRITO')
            ->whereDoesntHave('resultadoAdmision')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get()
            ->map(function (Postulante $postulante): array {
                $grupoActivo = $this->obtenerGrupoActivoDelPostulante($postulante);
                $calculo = $this->calcularPromediosPostulante($postulante);

                return [
                    'postulante' => $postulante,
                    'grupoActivo' => $grupoActivo,
                    'calculo' => $calculo,
                    'estado_notas' => $calculo['completo'] ? 'Completo' : 'Incompleto',
                    'faltantes' => $calculo['faltantes'],
                ];
            });

        return view('gestion_academica_cup.resultados.pendientes', [
            'pendientes' => $postulantes,
            'notaMinimaAprobacion' => number_format($this->notaMinimaAprobacion, 2, '.', ''),
        ]);
    }

    /**
     * Lote masivo que recorre todos los postulantes inscritos y genera su resultado
     * si todas sus calificaciones de materias están cargadas de forma completa.
     */
    public function generacionMasiva(Request $request): RedirectResponse
    {
        $this->authorizeRoles();
        $postulantes = Postulante::query()
            ->where('estado_inscripcion', 'INSCRITO')
            ->orderBy('id')
            ->get();

        $generados = 0;
        $omitidosIncompletos = 0;
        $omitidosExistentes = 0;
        $errores = [];

        foreach ($postulantes as $postulante) {
            if ($postulante->resultadoAdmision()->exists()) {
                $omitidosExistentes++;
                continue;
            }

            try {
                $this->generarResultadoParaPostulante($postulante);
                $generados++;
            } catch (ValidationException $exception) {
                $messages = collect($exception->errors())->flatten()->implode(' ');

                if (str_contains(Str::lower($messages), 'incomplet')) {
                    $omitidosIncompletos++;
                } else {
                    $errores[] = trim($postulante->nombres . ' ' . $postulante->apellidos) . ': ' . $messages;
                }
            } catch (Throwable $exception) {
                Log::error('Error en generacion masiva para postulante: ' . $exception->getMessage(), [
                    'postulante_id' => $postulante->id,
                ]);

                $errores[] = trim($postulante->nombres . ' ' . $postulante->apellidos) . ': No se pudo generar el resultado para este postulante.';
            }
        }

        $message = "Generacion masiva completada. Generados: {$generados}. Omitidos por notas incompletas: {$omitidosIncompletos}. Omitidos por resultado existente: {$omitidosExistentes}.";

        if ($generados > 0) {
            BitacoraHelper::registrar(
                'GENERAR_RESULTADO',
                'Resultados',
                'Se ejecuto generacion masiva de resultados. Generados: ' . $generados . '.'
            );
        }

        return redirect()
            ->route('gestion-academica-cup.resultados.index')
            ->with('success', $message)
            ->with('batch_errors', $errores);
    }

    protected function authorizeRoles(): void
    {
        $roleName = Str::of((string) (auth()->user()?->rol?->nombre ?? ''))->lower()->ascii()->toString();

        abort_unless(in_array($roleName, ['administrador', 'coordinador'], true), 403);
    }

    protected function extractFilters(Request $request): array
    {
        return [
            'gestion' => $request->string('gestion')->toString(),
            'carrera_asignada_id' => $request->string('carrera_asignada_id')->toString(),
            'estado_resultado' => $request->string('estado_resultado')->toString(),
            'tipo_asignacion' => $request->string('tipo_asignacion')->toString(),
            'search' => $request->string('search')->toString(),
        ];
    }

    protected function buildResultadosQuery(array $filters): Builder
    {
        return ResultadoAdmision::query()
            ->select('resultados_admision.*')
            ->with(['postulante', 'carreraAsignada', 'modificadoPor'])
            ->join('postulantes', 'postulantes.id', '=', 'resultados_admision.postulante_id')
            ->when($filters['carrera_asignada_id'] !== '', fn (Builder $query) => $query->where('resultados_admision.carrera_asignada_id', $filters['carrera_asignada_id']))
            ->when($filters['estado_resultado'] !== '', fn (Builder $query) => $query->where('resultados_admision.estado_resultado', $filters['estado_resultado']))
            ->when($filters['tipo_asignacion'] !== '', fn (Builder $query) => $query->where('resultados_admision.tipo_asignacion', $filters['tipo_asignacion']))
            ->when($filters['search'] !== '', function (Builder $query) use ($filters): void {
                $term = $filters['search'];
                $query->where(function (Builder $innerQuery) use ($term): void {
                    $innerQuery
                        ->where('postulantes.ci', 'like', '%' . $term . '%')
                        ->orWhere('postulantes.nombres', 'like', '%' . $term . '%')
                        ->orWhere('postulantes.apellidos', 'like', '%' . $term . '%');
                });
            })
            ->when($filters['gestion'] !== '', function (Builder $query) use ($filters): void {
                $query->whereExists(function ($subQuery) use ($filters): void {
                    $subQuery->selectRaw('1')
                        ->from('grupo_postulantes')
                        ->join('grupos', 'grupos.id', '=', 'grupo_postulantes.grupo_id')
                        ->whereColumn('grupo_postulantes.postulante_id', 'resultados_admision.postulante_id')
                        ->where('grupo_postulantes.estado', 'ACTIVO')
                        ->where('grupos.estado', 'ACTIVO')
                        ->where('grupos.gestion', $filters['gestion']);
                });
            });
    }

    protected function formOptions(): array
    {
        return [
            'carreras' => Carrera::query()->where('estado', 'ACTIVO')->orderBy('nombre')->get(),
            'gestiones' => GrupoPostulante::query()
                ->join('grupos', 'grupos.id', '=', 'grupo_postulantes.grupo_id')
                ->where('grupo_postulantes.estado', 'ACTIVO')
                ->where('grupos.estado', 'ACTIVO')
                ->distinct()
                ->orderByDesc('grupos.gestion')
                ->pluck('grupos.gestion'),
            'postulantes' => Postulante::query()
                ->where('estado_inscripcion', 'INSCRITO')
                ->orderBy('apellidos')
                ->orderBy('nombres')
                ->get(),
        ];
    }

    protected function postulantesInscritosSinResultado(): Collection
    {
        return Postulante::query()
            ->where('estado_inscripcion', 'INSCRITO')
            ->whereDoesntHave('resultadoAdmision')
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->get();
    }

    protected function obtenerGrupoActivoDelPostulante(Postulante $postulante): ?GrupoPostulante
    {
        return GrupoPostulante::query()
            ->with('grupo')
            ->where('postulante_id', $postulante->id)
            ->where('estado', 'ACTIVO')
            ->latest('id')
            ->first();
    }

    protected function obtenerGestionPostulante(Postulante $postulante): string
    {
        $grupoPostulante = $this->resolverGrupoActivoUnicoDelPostulante($postulante);

        if (! $grupoPostulante->grupo || $grupoPostulante->grupo->estado !== 'ACTIVO') {
            throw ValidationException::withMessages([
                'postulante_id' => 'El postulante no tiene un grupo activo con gestion valida.',
            ]);
        }

        return (string) $grupoPostulante->grupo->gestion;
    }

    protected function calcularPromediosPostulante(Postulante $postulante): array
    {
        $materias = Materia::query()
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();

        $notas = $postulante->notas()
            ->with('evaluacion')
            ->get()
            ->groupBy('materia_id');

        $materiasCalculo = [];
        $faltantes = [];
        $promediosCompletos = [];
        $completo = true;

        foreach ($materias as $materia) {
            $evaluaciones = Evaluacion::query()
                ->where('materia_id', $materia->id)
                ->where('estado', 'ACTIVO')
                ->orderBy('numero_evaluacion')
                ->get();

            $notasMateria = $notas->get($materia->id, collect())->keyBy('evaluacion_id');
            $evaluacionesDetalle = [];
            $ponderado = 0.0;
            $materiaCompleta = true;
            $materiaFaltantes = [];

            foreach ($evaluaciones as $evaluacion) {
                $nota = $notasMateria->get($evaluacion->id);

                if (! $nota) {
                    $materiaCompleta = false;
                    $completo = false;
                    $materiaFaltantes[] = $materia->nombre . ' - Evaluacion ' . $evaluacion->numero_evaluacion;
                } else {
                    $ponderado += ((float) $nota->nota) * (((float) $evaluacion->porcentaje) / 100);
                }

                $evaluacionesDetalle[] = [
                    'evaluacion' => $evaluacion,
                    'nota' => $nota,
                ];
            }

            if ($evaluaciones->count() < 3) {
                $materiaCompleta = false;
                $completo = false;
                for ($numero = $evaluaciones->count() + 1; $numero <= 3; $numero++) {
                    $materiaFaltantes[] = $materia->nombre . ' - Evaluacion ' . $numero;
                }
            }

            if ($materiaCompleta) {
                $promediosCompletos[] = $ponderado;
            }

            $materiasCalculo[] = [
                'materia' => $materia,
                'evaluaciones' => $evaluacionesDetalle,
                'promedio' => $materiaCompleta ? number_format($ponderado, 2, '.', '') : null,
                'estado' => $materiaCompleta ? 'Completo' : 'Incompleto',
                'faltantes' => $materiaFaltantes,
            ];

            $faltantes = array_merge($faltantes, $materiaFaltantes);
        }

        $promedioFinal = null;

        if ($completo && count($promediosCompletos) > 0) {
            $promedioFinal = number_format(array_sum($promediosCompletos) / count($promediosCompletos), 2, '.', '');
        }

        return [
            'materias' => $materiasCalculo,
            'faltantes' => $faltantes,
            'completo' => $completo && count($promediosCompletos) === $materias->count(),
            'promedio_final' => $promedioFinal,
        ];
    }

    protected function validarNotasCompletas(array $calculo): void
    {
        if (! $calculo['completo'] || $calculo['promedio_final'] === null) {
            $faltantes = $calculo['faltantes'] !== [] ? implode(', ', $calculo['faltantes']) : 'No se pudo completar el calculo.';

            throw ValidationException::withMessages([
                'postulante_id' => 'Notas incompletas: no se puede generar resultado. Faltantes: ' . $faltantes,
            ]);
        }
    }

    /**
     * Motor principal de generación de resultados. Realiza el cálculo de promedios, valida la disponibilidad 
     * de cupos en base a opciones de carrera e inserta el resultado atómicamente, actualizando los contadores de cupos.
     */
    protected function generarResultadoParaPostulante(Postulante $postulante): ResultadoAdmision
    {
        if ($postulante->estado_inscripcion !== 'INSCRITO') {
            throw ValidationException::withMessages([
                'postulante_id' => 'Solo se pueden generar resultados para postulantes INSCRITO.',
            ]);
        }

        if ($postulante->resultadoAdmision()->exists()) {
            throw ValidationException::withMessages([
                'postulante_id' => 'Ya existe un resultado de admision para este postulante.',
            ]);
        }

        try {
            $grupoPostulante = $this->resolverGrupoActivoUnicoDelPostulante($postulante);
            $grupo = $grupoPostulante->grupo;
            $gestion = (string) $grupo->gestion;
            $calculo = $this->calcularPromediosPostulante($postulante);
            $this->validarNotasCompletas($calculo);
            $promedioFinal = (float) $calculo['promedio_final'];
            $asignacion = $this->asignarCarrera($postulante, $gestion, $promedioFinal);
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            Log::error('Error al calcular resultado de admision', [
                'postulante_id' => $postulante->id,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        $estadoResultado = $asignacion['estado_resultado'];
        $carreraAsignadaId = $asignacion['carrera_asignada_id'];
        $tipoAsignacion = $asignacion['tipo_asignacion'];
        $cupoId = $asignacion['cupo']?->id;

        Log::info('Preparando generacion de resultado', [
            'postulante_id' => $postulante->id,
            'grupo_id' => $grupo->id,
            'gestion' => $gestion,
            'promedio_final' => $promedioFinal,
            'estado_resultado' => $estadoResultado,
            'carrera_asignada_id' => $carreraAsignadaId,
            'tipo_asignacion' => $tipoAsignacion,
            'cupo_id' => $cupoId,
        ]);

        $payload = [
            'postulante_id' => $postulante->id,
            'promedio_final' => number_format($promedioFinal, 2, '.', ''),
            'estado_resultado' => $estadoResultado,
            'carrera_asignada_id' => $carreraAsignadaId,
            'tipo_asignacion' => $tipoAsignacion,
            'observacion' => $asignacion['observacion'],
            'justificacion_modificacion' => null,
            'modificado_por' => null,
            'fecha_resultado' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        try {
            $resultadoId = DB::table('resultados_admision')->insertGetId($payload);
        } catch (Throwable $exception) {
            Log::error('Error al insertar resultado de admision', [
                'postulante_id' => $postulante->id,
                'gestion' => $gestion,
                'error' => $exception->getMessage(),
            ]);

            throw $exception;
        }

        if ($cupoId !== null) {
            try {
                $updated = DB::table('cupos_carrera')
                    ->where('id', $cupoId)
                    ->where('cupos_disponibles', '>', 0)
                    ->update([
                        'cupos_ocupados' => DB::raw('COALESCE(cupos_ocupados, 0) + 1'),
                        'cupos_disponibles' => DB::raw('COALESCE(cupos_disponibles, 0) - 1'),
                        'updated_at' => now(),
                    ]);
            } catch (Throwable $exception) {
                Log::error('Error al actualizar cupo durante generacion de resultado', [
                    'postulante_id' => $postulante->id,
                    'cupo_id' => $cupoId,
                    'error' => $exception->getMessage(),
                ]);

                try {
                    DB::table('resultados_admision')->where('id', $resultadoId)->delete();
                } catch (Throwable $deleteException) {
                    Log::error('Error al compensar eliminando resultado tras fallo de cupo', [
                        'resultado_id' => $resultadoId,
                        'postulante_id' => $postulante->id,
                        'error' => $deleteException->getMessage(),
                    ]);
                }

                throw $exception;
            }

            if ($updated !== 1) {
                try {
                    DB::table('resultados_admision')->where('id', $resultadoId)->delete();
                } catch (Throwable $deleteException) {
                    Log::error('Error al compensar eliminando resultado por cupo no actualizado', [
                        'resultado_id' => $resultadoId,
                        'postulante_id' => $postulante->id,
                        'error' => $deleteException->getMessage(),
                    ]);
                }

                throw ValidationException::withMessages([
                    'resultado' => 'No se pudo ocupar el cupo seleccionado. Intente nuevamente.',
                ]);
            }
        }

        $resultado = ResultadoAdmision::query()->findOrFail($resultadoId);

        NotificacionHelper::enviar(
            $postulante->usuario_id,
            'Resultado de admisión disponible',
            'Tu resultado de admisión ya está disponible. Estado: ' . $resultado->estado_resultado . '.',
            'RESULTADO'
        );

        return $resultado;
    }

    /**
     * Recalcula el promedio del postulante y realiza transiciones de cupos (liberando el anterior
     * y ocupando el nuevo) si la asignación de carrera varía como producto de nuevas notas registradas.
     */
    protected function recalcularResultadoExistente(ResultadoAdmision $resultado, string $justificacion): void
    {
        $resultado->load(['postulante', 'carreraAsignada']);
        $postulante = $resultado->postulante;

        if (! $postulante) {
            throw ValidationException::withMessages([
                'resultado' => 'El resultado no tiene postulante asociado.',
            ]);
        }

        $gestion = $this->obtenerGestionPostulante($postulante);
        $calculo = $this->calcularPromediosPostulante($postulante);
        $this->validarNotasCompletas($calculo);

        $promedioFinal = (float) $calculo['promedio_final'];
        $asignacion = $this->asignarCarrera($postulante, $gestion, $promedioFinal);

        $carreraAnteriorId = $resultado->carrera_asignada_id;
        $carreraNuevaId = $asignacion['carrera_asignada_id'];

        if ($carreraAnteriorId && $carreraAnteriorId !== $carreraNuevaId) {
            $cupoAnterior = $this->buscarCupoDisponible((int) $carreraAnteriorId, $gestion, false);

            if ($cupoAnterior) {
                $this->liberarCupo($cupoAnterior);
            }
        }

        if ($asignacion['cupo'] && $carreraAnteriorId !== $carreraNuevaId) {
            $this->ocuparCupo($asignacion['cupo']);
        }

        if ($carreraAnteriorId && $carreraAnteriorId === $carreraNuevaId) {
            $asignacion['cupo'] = null;
        }

        $resultado->update([
            'promedio_final' => $calculo['promedio_final'],
            'estado_resultado' => $asignacion['estado_resultado'],
            'carrera_asignada_id' => $carreraNuevaId,
            'tipo_asignacion' => $asignacion['tipo_asignacion'],
            'observacion' => $asignacion['observacion'],
            'justificacion_modificacion' => $justificacion,
            'modificado_por' => auth()->id(),
            'fecha_resultado' => now(),
        ]);
    }

    /**
     * Aplica las reglas del negocio de asignación:
     * Si el promedio final es menor a 51.00 es REPROBADO.
     * Si aprueba, evalúa disponibilidad de cupos en Carrera Primera Opción, luego en Segunda Opción.
     */
    protected function asignarCarrera(Postulante $postulante, string $gestion, float $promedioFinal): array
    {
        if ($promedioFinal < $this->notaMinimaAprobacion) {
            return [
                'estado_resultado' => 'REPROBADO',
                'carrera_asignada_id' => null,
                'tipo_asignacion' => 'SIN_ASIGNACION',
                'observacion' => 'Postulante reprobado segun promedio final.',
                'cupo' => null,
            ];
        }

        $primeraOpcion = $postulante->carreraPrimeraOpcion;
        $segundaOpcion = $postulante->carreraSegundaOpcion;

        if ($primeraOpcion) {
            $cupo = $this->buscarCupoDisponible($primeraOpcion->id, $gestion);

            if ($cupo) {
                return [
                    'estado_resultado' => 'APROBADO',
                    'carrera_asignada_id' => $primeraOpcion->id,
                    'tipo_asignacion' => 'PRIMERA_OPCION',
                    'observacion' => 'Postulante aprobado y asignado a su primera opcion.',
                    'cupo' => $cupo,
                ];
            }
        }

        if ($segundaOpcion) {
            $cupo = $this->buscarCupoDisponible($segundaOpcion->id, $gestion);

            if ($cupo) {
                return [
                    'estado_resultado' => 'APROBADO',
                    'carrera_asignada_id' => $segundaOpcion->id,
                    'tipo_asignacion' => 'SEGUNDA_OPCION',
                    'observacion' => 'Postulante aprobado y asignado a su segunda opcion.',
                    'cupo' => $cupo,
                ];
            }
        }

        return [
            'estado_resultado' => 'APROBADO',
            'carrera_asignada_id' => null,
            'tipo_asignacion' => 'SIN_ASIGNACION',
            'observacion' => 'Postulante aprobado sin cupo disponible en sus opciones.',
            'cupo' => null,
        ];
    }

    protected function buscarCupoDisponible(int $carreraId, string $gestion, bool $requireAvailability = true): ?CupoCarrera
    {
        $gestiones = array_unique(array_filter([
            $gestion,
            preg_match('/(\d{4})$/', $gestion, $matches) ? $matches[1] : null,
        ]));

        $query = CupoCarrera::query()
            ->where('carrera_id', $carreraId)
            ->where('estado', 'ACTIVO')
            ->whereIn('gestion', $gestiones)
            ->orderByRaw('CASE WHEN gestion = ? THEN 0 ELSE 1 END', [$gestion]);

        if ($requireAvailability) {
            $query->where('cupos_disponibles', '>', 0);
        }

        return $query->first();
    }

    protected function ocuparCupo(CupoCarrera $cupo): void
    {
        if ((int) $cupo->cupos_disponibles <= 0) {
            throw ValidationException::withMessages([
                'resultado' => 'No existen cupos disponibles para la carrera seleccionada.',
            ]);
        }

        $updated = DB::table('cupos_carrera')
            ->where('id', $cupo->id)
            ->where('cupos_disponibles', '>', 0)
            ->update([
                'cupos_ocupados' => DB::raw('cupos_ocupados + 1'),
                'cupos_disponibles' => DB::raw('cupos_disponibles - 1'),
                'updated_at' => now(),
            ]);

        if ($updated !== 1) {
            throw ValidationException::withMessages([
                'resultado' => 'No se pudo ocupar el cupo de carrera sin dejar valores negativos.',
            ]);
        }
    }

    protected function liberarCupo(CupoCarrera $cupo): void
    {
        DB::table('cupos_carrera')
            ->where('id', $cupo->id)
            ->update([
                'cupos_ocupados' => DB::raw('GREATEST(cupos_ocupados - 1, 0)'),
                'cupos_disponibles' => DB::raw('cupos_disponibles + 1'),
                'updated_at' => now(),
            ]);
    }

    protected function buildGrupoActivoMap(array $postulanteIds): array
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

    protected function resolverGrupoActivoUnicoDelPostulante(Postulante $postulante): GrupoPostulante
    {
        $gruposActivos = GrupoPostulante::query()
            ->with('grupo')
            ->where('postulante_id', $postulante->id)
            ->where('estado', 'ACTIVO')
            ->orderByDesc('id')
            ->get();

        if ($gruposActivos->isEmpty()) {
            throw ValidationException::withMessages([
                'postulante_id' => 'El postulante no tiene grupo activo asignado.',
            ]);
        }

        if ($gruposActivos->count() > 1) {
            throw ValidationException::withMessages([
                'postulante_id' => 'El postulante tiene mas de un grupo activo. Corrija la asignacion antes de generar el resultado.',
            ]);
        }

        $grupoPostulante = $gruposActivos->first();

        if (! $grupoPostulante || ! $grupoPostulante->grupo || $grupoPostulante->grupo->estado !== 'ACTIVO') {
            throw ValidationException::withMessages([
                'postulante_id' => 'El postulante no tiene un grupo activo con gestion valida.',
            ]);
        }

        return $grupoPostulante;
    }
}
