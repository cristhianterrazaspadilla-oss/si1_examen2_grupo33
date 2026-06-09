<?php

namespace App\Http\Controllers\GestionAcademicaCUP;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Grupo;
use App\Models\Materia;
use App\Support\BitacoraHelper;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ReporteAcademicoController extends Controller
{
    protected const EXPORT_LIMIT = 5000;

    public function index(): RedirectResponse
    {
        $this->authorizeRoles();

        return redirect()->route('gestion-academica-cup.reportes.consulta');
    }

    public function dashboard(Request $request): View|RedirectResponse
    {
        $this->authorizeRoles();

        $filters = [
            'gestion' => $request->string('gestion')->toString(),
            'carrera_id' => $request->string('carrera_id')->toString(),
            'grupo_id' => $request->string('grupo_id')->toString(),
            'materia_id' => $request->string('materia_id')->toString(),
            'estado_resultado' => $request->string('estado_resultado')->toString(),
            'estado_inscripcion' => $request->string('estado_inscripcion')->toString(),
        ];

        $postulantesQuery = $this->postulantesQuery($filters);
        $pagosQuery = $this->pagosQuery($filters);
        $gruposQuery = $this->gruposQuery($filters);
        $docentesQuery = $this->docentesQuery($filters);
        $materiasQuery = $this->materiasQuery($filters);
        $evaluacionesQuery = $this->evaluacionesQuery($filters);
        $resultadosQuery = $this->resultadosQuery($filters);
        $cuposQuery = $this->cuposQuery($filters);
        $notasQuery = $this->notasQuery($filters);
        $asistenciasQuery = $this->asistenciasQuery($filters);

        $summary = [
            'total_postulantes' => (clone $postulantesQuery)->count('postulantes.id'),
            'total_inscritos' => (clone $postulantesQuery)->where('postulantes.estado_inscripcion', 'INSCRITO')->count('postulantes.id'),
            'total_pagos_confirmados' => (clone $pagosQuery)->where('pagos.estado_pago', 'CONFIRMADO')->count('pagos.id'),
            'total_grupos_activos' => (clone $gruposQuery)->where('grupos.estado', 'ACTIVO')->count('grupos.id'),
            'total_docentes_activos' => (clone $docentesQuery)->where('docentes.estado', 'ACTIVO')->distinct()->count('docentes.id'),
            'total_materias_activas' => (clone $materiasQuery)->where('materias.estado', 'ACTIVO')->count('materias.id'),
            'total_evaluaciones_activas' => (clone $evaluacionesQuery)->where('evaluaciones.estado', 'ACTIVO')->count('evaluaciones.id'),
            'total_resultados' => (clone $resultadosQuery)->count('resultados_admision.id'),
            'total_aprobados' => (clone $resultadosQuery)->where('resultados_admision.estado_resultado', 'APROBADO')->count('resultados_admision.id'),
            'total_reprobados' => (clone $resultadosQuery)->where('resultados_admision.estado_resultado', 'REPROBADO')->count('resultados_admision.id'),
            'total_sin_asignacion' => (clone $resultadosQuery)->where('resultados_admision.tipo_asignacion', 'SIN_ASIGNACION')->count('resultados_admision.id'),
            'promedio_general' => $this->formatNumber((clone $resultadosQuery)->avg('resultados_admision.promedio_final')),
            'cupos_ocupados' => (int) ((clone $cuposQuery)->sum('cupos_carrera.cupos_ocupados') ?? 0),
            'cupos_disponibles' => (int) ((clone $cuposQuery)->sum('cupos_carrera.cupos_disponibles') ?? 0),
        ];

        $aprobadosPorCarrera = (clone $resultadosQuery)
            ->leftJoin('carreras', 'carreras.id', '=', 'resultados_admision.carrera_asignada_id')
            ->where('resultados_admision.estado_resultado', 'APROBADO')
            ->groupBy('carreras.nombre')
            ->orderByDesc(DB::raw('COUNT(resultados_admision.id)'))
            ->get([
                DB::raw("COALESCE(carreras.nombre, 'Sin asignacion') as carrera"),
                DB::raw('COUNT(resultados_admision.id) as total'),
            ]);

        $promedioPorMateria = (clone $notasQuery)
            ->join('materias', 'materias.id', '=', 'notas.materia_id')
            ->groupBy('materias.nombre')
            ->orderBy('materias.nombre')
            ->get([
                'materias.nombre as materia',
                DB::raw('COUNT(notas.id) as total_notas'),
                DB::raw('AVG(notas.nota) as promedio'),
            ]);

        $notasPorMateria = (clone $notasQuery)
            ->join('materias', 'materias.id', '=', 'notas.materia_id')
            ->groupBy('materias.nombre')
            ->orderByDesc(DB::raw('COUNT(notas.id)'))
            ->get([
                'materias.nombre as materia',
                DB::raw('COUNT(notas.id) as total_notas'),
            ]);

        $gruposPorGestion = (clone $gruposQuery)
            ->where('grupos.estado', 'ACTIVO')
            ->groupBy('grupos.gestion')
            ->orderByDesc('grupos.gestion')
            ->get([
                'grupos.gestion',
                DB::raw('COUNT(grupos.id) as total_grupos'),
            ]);

        $estudiantesPorGrupo = DB::table('grupos')
            ->leftJoin('grupo_postulantes', function ($join): void {
                $join->on('grupo_postulantes.grupo_id', '=', 'grupos.id')
                    ->where('grupo_postulantes.estado', '=', 'ACTIVO');
            })
            ->where('grupos.estado', 'ACTIVO')
            ->when($filters['gestion'] !== '', fn ($query) => $query->where('grupos.gestion', $filters['gestion']))
            ->when($filters['grupo_id'] !== '', fn ($query) => $query->where('grupos.id', $filters['grupo_id']))
            ->groupBy('grupos.id', 'grupos.nombre', 'grupos.gestion')
            ->orderByDesc('grupos.gestion')
            ->orderBy('grupos.nombre')
            ->get([
                'grupos.nombre',
                'grupos.gestion',
                DB::raw('COUNT(grupo_postulantes.id) as total_estudiantes'),
            ]);

        $docentesConAsignaciones = DB::table('docentes')
            ->join('docente_asignaciones', 'docente_asignaciones.docente_id', '=', 'docentes.id')
            ->join('grupos', 'grupos.id', '=', 'docente_asignaciones.grupo_id')
            ->join('materias', 'materias.id', '=', 'docente_asignaciones.materia_id')
            ->where('docentes.estado', 'ACTIVO')
            ->where('docente_asignaciones.estado', 'ACTIVO')
            ->where('grupos.estado', 'ACTIVO')
            ->where('materias.estado', 'ACTIVO')
            ->whereColumn('docente_asignaciones.gestion', 'grupos.gestion')
            ->when($filters['gestion'] !== '', fn ($query) => $query->where('grupos.gestion', $filters['gestion']))
            ->when($filters['grupo_id'] !== '', fn ($query) => $query->where('grupos.id', $filters['grupo_id']))
            ->when($filters['materia_id'] !== '', fn ($query) => $query->where('materias.id', $filters['materia_id']))
            ->groupBy('docentes.id', 'docentes.nombres', 'docentes.apellidos')
            ->orderBy('docentes.apellidos')
            ->orderBy('docentes.nombres')
            ->get([
                'docentes.id',
                'docentes.nombres',
                'docentes.apellidos',
                DB::raw('COUNT(docente_asignaciones.id) as total_asignaciones'),
            ]);

        $asistenciasPorEstado = (clone $asistenciasQuery)
            ->groupBy('asistencias_docentes.estado_asistencia')
            ->orderBy('asistencias_docentes.estado_asistencia')
            ->get([
                'asistencias_docentes.estado_asistencia',
                DB::raw('COUNT(asistencias_docentes.id) as total'),
            ]);

        $cuposPorCarrera = (clone $cuposQuery)
            ->join('carreras', 'carreras.id', '=', 'cupos_carrera.carrera_id')
            ->groupBy('carreras.nombre')
            ->orderBy('carreras.nombre')
            ->get([
                'carreras.nombre as carrera',
                DB::raw('SUM(cupos_carrera.cupos_ocupados) as ocupados'),
                DB::raw('SUM(cupos_carrera.cupos_disponibles) as disponibles'),
            ]);

        return view('gestion_academica_cup.reportes.dashboard', [
            'filters' => $filters,
            'summary' => $summary,
            'aprobadosPorCarrera' => $aprobadosPorCarrera,
            'promedioPorMateria' => $promedioPorMateria,
            'notasPorMateria' => $notasPorMateria,
            'gruposPorGestion' => $gruposPorGestion,
            'estudiantesPorGrupo' => $estudiantesPorGrupo,
            'docentesConAsignaciones' => $docentesConAsignaciones,
            'asistenciasPorEstado' => $asistenciasPorEstado,
            'cuposPorCarrera' => $cuposPorCarrera,
            'formOptions' => [
                'gestiones' => $this->gestionesDisponibles(),
                'carreras' => Carrera::query()->where('estado', 'ACTIVO')->orderBy('nombre')->get(),
                'grupos' => Grupo::query()->where('estado', 'ACTIVO')->orderByDesc('gestion')->orderBy('nombre')->get(),
                'materias' => Materia::query()->where('estado', 'ACTIVO')->orderBy('nombre')->get(),
                'estadosResultado' => ['APROBADO', 'REPROBADO', 'PENDIENTE'],
                'estadosInscripcion' => ['PRE_REGISTRADO', 'REQUISITOS_APROBADOS', 'PAGO_PENDIENTE', 'INSCRITO', 'OBSERVADO'],
            ],
        ]);
    }

    public function consulta(Request $request): View|RedirectResponse
    {
        $this->authorizeRoles();

        $validation = $this->validateReportRequest($request, false);

        if ($validation instanceof RedirectResponse) {
            return $validation;
        }

        $filters = $validation;
        $reportData = null;

        if ($filters['tipo_reporte'] !== '') {
            $reportData = $this->getReporteData($request, true);
        }

        return view('gestion_academica_cup.reportes.consulta', [
            'filters' => $filters,
            'reportTypes' => $this->getReporteOptions(),
            'reportData' => $reportData,
            'formOptions' => $this->consultaFormOptions(),
            'appliedFilters' => $this->appliedFiltersSummary($filters),
        ]);
    }

    public function interpretarComando(Request $request): JsonResponse
    {
        $this->authorizeRoles();

        $validator = Validator::make($request->all(), [
            'comando' => ['required', 'string', 'max:500'],
        ], [
            'comando.required' => 'Debes proporcionar un comando para interpretar.',
            'comando.max' => 'El comando no puede superar los 500 caracteres.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first('comando'),
            ], 422);
        }

        $apiKey = config('services.groq.api_key');
        $model = (string) config('services.groq.model', 'llama-3.1-8b-instant');

        if (!is_string($apiKey) || trim($apiKey) === '') {
            return response()->json([
                'success' => false,
                'message' => 'GROQ_API_KEY no está configurada. Se usará la interpretación local.',
            ]);
        }

        $comando = trim($request->string('comando')->toString());
        $catalogo = $this->groqCatalogoOpciones();
        $systemPrompt = $this->groqSystemPrompt($catalogo);

        try {
            $response = Http::withToken($apiKey)
                ->timeout(20)
                ->acceptJson()
                ->post('https://api.groq.com/openai/v1/chat/completions', [
                    'model' => $model,
                    'temperature' => 0,
                    'max_tokens' => 500,
                    'response_format' => ['type' => 'json_object'],
                    'messages' => [
                        ['role' => 'system', 'content' => $systemPrompt],
                        ['role' => 'user', 'content' => $comando],
                    ],
                ]);

            if ($response->failed()) {
                Log::error('Error al interpretar comando con Groq', [
                    'status' => $response->status(),
                    'command_length' => mb_strlen($comando),
                    'error' => $response->json('error.message') ?? 'Respuesta no exitosa',
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo interpretar con IA. Puedes usar la interpretacion local.',
                ]);
            }

            $content = (string) data_get($response->json(), 'choices.0.message.content', '');
            $decoded = json_decode($content, true);

            if (!is_array($decoded)) {
                Log::error('Groq devolvio un JSON invalido para interpretacion de reportes', [
                    'command_length' => mb_strlen($comando),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo interpretar con IA. Puedes usar la interpretacion local.',
                ]);
            }

            $sanitized = $this->sanitizeGroqFilters($decoded, $catalogo, $comando);

            return response()->json([
                'success' => true,
                'filters' => $sanitized,
                'message' => 'Comando interpretado con IA.',
            ]);
        } catch (\Throwable $e) {
            Log::error('Excepcion al interpretar comando con Groq', [
                'command_length' => mb_strlen($comando),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'No se pudo interpretar con IA. Puedes usar la interpretacion local.',
            ]);
        }
    }

    public function exportarCsv(Request $request): StreamedResponse|RedirectResponse
    {
        $this->authorizeRoles();

        $validation = $this->validateReportRequest($request, true);

        if ($validation instanceof RedirectResponse) {
            return $validation;
        }

        $reportData = $this->getReporteData($request, false);
        $columns = $reportData['columns'];
        $rows = $reportData['rows'];
        $filename = 'reporte_' . $reportData['type'] . '_' . now()->format('Y-m-d') . '.csv';

        $this->registrarHistorialReporte(
            $reportData['type'],
            'CSV',
            $this->sanitizeReportFilters($validation),
            'descarga://' . $filename
        );

        BitacoraHelper::registrar(
            'EXPORTAR_REPORTE_CSV',
            'Reportes',
            'Se exporto el reporte ' . $reportData['type'] . ' en formato CSV.'
        );

        return response()->streamDownload(function () use ($columns, $rows): void {
            $handle = fopen('php://output', 'w');

            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, array_values($columns));

            foreach ($rows as $row) {
                $csvRow = [];

                foreach (array_keys($columns) as $key) {
                    $csvRow[] = $row[$key] ?? '';
                }

                fputcsv($handle, $csvRow);
            }

            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    public function exportarExcel(Request $request): StreamedResponse|RedirectResponse
    {
        $this->authorizeRoles();

        $validation = $this->validateReportRequest($request, true);

        if ($validation instanceof RedirectResponse) {
            return $validation;
        }

        $reportData = $this->getReporteData($request, false);
        $columns = $reportData['columns'];
        $rows = $reportData['rows'];
        $filename = 'reporte_' . $reportData['type'] . '_' . now()->format('Y-m-d') . '.xls';

        $this->registrarHistorialReporte(
            $reportData['type'],
            'EXCEL',
            $this->sanitizeReportFilters($validation),
            'descarga://' . $filename
        );

        BitacoraHelper::registrar(
            'EXPORTAR_REPORTE_EXCEL',
            'Reportes',
            'Se exporto el reporte ' . $reportData['type'] . ' en formato EXCEL.'
        );

        return response()->streamDownload(function () use ($columns, $rows): void {
            echo "\xEF\xBB\xBF";
            echo '<table border="1">';
            echo '<thead><tr>';

            foreach ($columns as $label) {
                echo '<th>' . e($label) . '</th>';
            }

            echo '</tr></thead><tbody>';

            if ($rows->count() === 0) {
                echo '<tr><td colspan="' . count($columns) . '">No existen datos para los filtros seleccionados.</td></tr>';
            } else {
                foreach ($rows as $row) {
                    echo '<tr>';

                    foreach (array_keys($columns) as $key) {
                        echo '<td>' . e((string) ($row[$key] ?? '')) . '</td>';
                    }

                    echo '</tr>';
                }
            }

            echo '</tbody></table>';
        }, $filename, [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
        ]);
    }

    public function imprimir(Request $request): View|RedirectResponse
    {
        $this->authorizeRoles();

        $validation = $this->validateReportRequest($request, true);

        if ($validation instanceof RedirectResponse) {
            return $validation;
        }

        $reportData = $this->getReporteData($request, false);
        $logicalName = 'reporte_' . $reportData['type'] . '_' . now()->format('Y-m-d');

        $this->registrarHistorialReporte(
            $reportData['type'],
            'PDF',
            $this->sanitizeReportFilters($validation),
            'impresion://' . $logicalName
        );

        BitacoraHelper::registrar(
            'EXPORTAR_REPORTE_PDF',
            'Reportes',
            'Se abrio la vista imprimible del reporte ' . $reportData['type'] . '.'
        );

        return view('gestion_academica_cup.reportes.imprimir', [
            'filters' => $validation,
            'reportData' => $reportData,
            'appliedFilters' => $this->appliedFiltersSummary($validation),
            'generatedAt' => now(),
        ]);
    }

    public function historial(Request $request): View|RedirectResponse
    {
        $this->authorizeRoles();

        $reportTypes = $this->getReporteOptions();

        $validator = Validator::make($request->query(), [
            'tipo_reporte' => ['nullable', 'string', Rule::in(array_keys($reportTypes))],
            'formato' => ['nullable', 'string', Rule::in(['CSV', 'PDF', 'EXCEL'])],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'busqueda' => ['nullable', 'string'],
        ], [
            'tipo_reporte.in' => 'Tipo de reporte no valido.',
            'formato.in' => 'Formato no valido.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('gestion-academica-cup.reportes.historial')
                ->withErrors($validator)
                ->withInput();
        }

        $filters = [
            'tipo_reporte' => $request->string('tipo_reporte')->toString(),
            'formato' => $request->string('formato')->toString(),
            'fecha_desde' => $request->string('fecha_desde')->toString(),
            'fecha_hasta' => $request->string('fecha_hasta')->toString(),
            'busqueda' => trim($request->string('busqueda')->toString()),
        ];

        $historial = DB::table('reportes')
            ->leftJoin('usuarios', 'usuarios.id', '=', 'reportes.usuario_id')
            ->when($filters['tipo_reporte'] !== '', fn ($query) => $query->where('reportes.tipo_reporte', $filters['tipo_reporte']))
            ->when($filters['formato'] !== '', fn ($query) => $query->where('reportes.formato', $filters['formato']))
            ->when($filters['fecha_desde'] !== '', fn ($query) => $query->whereDate('reportes.fecha_generacion', '>=', $filters['fecha_desde']))
            ->when($filters['fecha_hasta'] !== '', fn ($query) => $query->whereDate('reportes.fecha_generacion', '<=', $filters['fecha_hasta']))
            ->when($filters['busqueda'] !== '', function ($query) use ($filters): void {
                $term = '%' . $filters['busqueda'] . '%';
                $query->where(function ($subQuery) use ($term): void {
                    $subQuery
                        ->where('reportes.tipo_reporte', 'ILIKE', $term)
                        ->orWhere('reportes.formato', 'ILIKE', $term)
                        ->orWhere('reportes.ruta_archivo', 'ILIKE', $term)
                        ->orWhere('usuarios.nombre', 'ILIKE', $term)
                        ->orWhere('usuarios.apellido', 'ILIKE', $term)
                        ->orWhere('usuarios.correo', 'ILIKE', $term)
                        ->orWhere('usuarios.ci', 'ILIKE', $term);
                });
            })
            ->select([
                'reportes.id',
                'reportes.fecha_generacion',
                'reportes.tipo_reporte',
                'reportes.formato',
                'reportes.filtros',
                'reportes.ruta_archivo',
                DB::raw("TRIM(COALESCE(usuarios.nombre, '') || ' ' || COALESCE(usuarios.apellido, '')) as usuario"),
                'usuarios.correo as usuario_correo',
            ])
            ->orderByDesc('reportes.fecha_generacion')
            ->paginate(15)
            ->withQueryString();

        $historial->setCollection(
            $historial->getCollection()->map(function ($item) {
                $decoded = [];

                if (is_string($item->filtros) && $item->filtros !== '') {
                    $decoded = json_decode($item->filtros, true) ?: [];
                } elseif (is_array($item->filtros)) {
                    $decoded = $item->filtros;
                }

                $item->filtros_resumen = collect($decoded)
                    ->filter(fn ($value) => $value !== null && $value !== '')
                    ->map(fn ($value, $key) => $key . ': ' . $value)
                    ->values()
                    ->all();

                if ($item->filtros_resumen === [] && is_string($item->filtros) && trim($item->filtros) !== '') {
                    $item->filtros_resumen = [trim($item->filtros)];
                }

                return $item;
            })
        );

        return view('gestion_academica_cup.reportes.historial', [
            'filters' => $filters,
            'historial' => $historial,
            'reportTypes' => $reportTypes,
        ]);
    }

    protected function validateReportRequest(Request $request, bool $requireType): array|RedirectResponse
    {
        $reportTypes = $this->getReporteOptions();
        $tipoRule = $requireType ? ['required', 'string', Rule::in(array_keys($reportTypes))] : ['nullable', 'string', Rule::in(array_keys($reportTypes))];

        $validator = Validator::make($request->query(), [
            'tipo_reporte' => $tipoRule,
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
        ], [
            'tipo_reporte.required' => 'Debes seleccionar un tipo de reporte.',
            'tipo_reporte.in' => 'Tipo de reporte no valido.',
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('gestion-academica-cup.reportes.consulta')
                ->withErrors($validator)
                ->withInput();
        }

        return $this->consultaFilters($request);
    }

    protected function getReporteData(Request $request, bool $paginated = true): array
    {
        $filters = $this->consultaFilters($request);
        $tipoReporte = $filters['tipo_reporte'];
        $query = $this->buildReportQuery($tipoReporte, $filters);
        $columns = $this->getReporteColumns($tipoReporte);

        if ($paginated) {
            $results = $query->paginate(15)->withQueryString();
            $rows = $this->normalizeReporteRows(collect($results->items()), $tipoReporte);

            return [
                'type' => $tipoReporte,
                'label' => $this->getReporteTitle($tipoReporte),
                'columns' => $columns,
                'rows' => $rows,
                'results' => $results,
                'total' => $results->total(),
                'was_limited' => false,
                'limit' => self::EXPORT_LIMIT,
            ];
        }

        $total = DB::query()->fromSub(clone $query, 'report_rows')->count();
        $results = (clone $query)->limit(self::EXPORT_LIMIT)->get();
        $rows = $this->normalizeReporteRows($results, $tipoReporte);

        return [
            'type' => $tipoReporte,
            'label' => $this->getReporteTitle($tipoReporte),
            'columns' => $columns,
            'rows' => $rows,
            'results' => $results,
            'total' => $total,
            'was_limited' => $total > self::EXPORT_LIMIT,
            'limit' => self::EXPORT_LIMIT,
        ];
    }

    protected function registrarHistorialReporte(string $tipoReporte, string $formato, array $filtros, ?string $rutaArchivo): void
    {
        try {
            $usuarioId = $this->resolveValidReporteUserId();

            if ($usuarioId === null) {
                return;
            }

            DB::table('reportes')->insert([
                'usuario_id' => $usuarioId,
                'tipo_reporte' => $tipoReporte,
                'formato' => $formato,
                'filtros' => json_encode($filtros, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
                'ruta_archivo' => $rutaArchivo,
                'fecha_generacion' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('Error al registrar historial de reporte', [
                'tipo_reporte' => $tipoReporte,
                'formato' => $formato,
                'ruta_archivo' => $rutaArchivo,
                'error' => $e->getMessage(),
            ]);
        }
    }

    protected function resolveValidReporteUserId(): ?int
    {
        $usuarioId = auth()->id();

        if ($usuarioId !== null && DB::table('usuarios')->where('id', $usuarioId)->exists()) {
            return (int) $usuarioId;
        }

        $adminId = DB::table('usuarios')
            ->join('roles', 'roles.id', '=', 'usuarios.rol_id')
            ->where('usuarios.estado', 'ACTIVO')
            ->whereRaw('LOWER(roles.nombre) = ?', ['administrador'])
            ->orderBy('usuarios.id')
            ->value('usuarios.id');

        if ($adminId !== null) {
            return (int) $adminId;
        }

        $fallbackId = DB::table('usuarios')->orderBy('id')->value('id');

        return $fallbackId !== null ? (int) $fallbackId : null;
    }

    protected function sanitizeReportFilters(array $filters): array
    {
        return collect($filters)
            ->only($this->allowedReportFilterKeys())
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->all();
    }

    protected function groqCatalogoOpciones(): array
    {
        return [
            'tipos_reporte' => array_keys($this->getReporteOptions()),
            'gestiones' => $this->gestionesDisponibles()->values()->all(),
            'carreras' => Carrera::query()
                ->where('estado', 'ACTIVO')
                ->orderBy('nombre')
                ->get(['id', 'nombre'])
                ->map(fn ($carrera) => ['id' => (int) $carrera->id, 'nombre' => $carrera->nombre])
                ->values()
                ->all(),
            'grupos' => Grupo::query()
                ->where('estado', 'ACTIVO')
                ->orderByDesc('gestion')
                ->orderBy('nombre')
                ->get(['id', 'nombre', 'gestion'])
                ->map(fn ($grupo) => ['id' => (int) $grupo->id, 'nombre' => $grupo->nombre, 'gestion' => $grupo->gestion])
                ->values()
                ->all(),
            'materias' => Materia::query()
                ->where('estado', 'ACTIVO')
                ->orderBy('nombre')
                ->get(['id', 'nombre'])
                ->map(fn ($materia) => ['id' => (int) $materia->id, 'nombre' => $materia->nombre])
                ->values()
                ->all(),
            'estados_inscripcion' => ['PRE_REGISTRADO', 'REQUISITOS_APROBADOS', 'PAGO_PENDIENTE', 'INSCRITO', 'OBSERVADO'],
            'estados_pago' => ['PENDIENTE', 'CONFIRMADO', 'RECHAZADO'],
            'estados_resultado' => ['PENDIENTE', 'APROBADO', 'REPROBADO'],
            'estados_asistencia' => ['PRESENTE', 'RETRASO', 'AUSENTE', 'JUSTIFICADO'],
        ];
    }

    protected function groqSystemPrompt(array $catalogo): string
    {
        return implode("\n", [
            'Eres un interprete de comandos para reportes academicos del sistema CUPCore.',
            'Debes devolver SOLO JSON valido.',
            'No devuelvas explicaciones, markdown ni texto adicional.',
            'No inventes valores.',
            'No inventes filtros.',
            'No uses valores por defecto.',
            'No uses la primera opcion disponible.',
            'No generes SQL, nombres de tablas, nombres de columnas ni pseudocodigo.',
            'Usa solo tipos de reporte permitidos y filtros permitidos.',
            'Si no estas seguro, devuelve null en ese filtro.',
            'Para gestion, usa exactamente una de las gestiones disponibles.',
            'Para carrera_id, grupo_id y materia_id, usa solo IDs reales de las opciones disponibles.',
            'No selecciones carrera_id si el usuario no menciona una carrera.',
            'No selecciones grupo_id si el usuario no menciona un grupo.',
            'No selecciones materia_id si el usuario no menciona una materia.',
            'No selecciones gestion si el usuario no menciona una gestion, periodo o anio.',
            'No selecciones estado_inscripcion si el usuario no menciona inscripcion, inscrito, observado, pago pendiente o requisitos aprobados.',
            'La palabra asistencia no significa ausente.',
            'No asumas AUSENTE por defecto.',
            'Para asistencia_docente, deja estado_asistencia = null si el usuario no dice presente, retraso, ausente o justificado.',
            'Usa AUSENTE solo si el comando contiene ausente, ausentes, falta, faltas, inasistencia o inasistencias.',
            'Usa RETRASO solo si el comando contiene retraso, retrasos, tarde o tardanza.',
            'Usa PRESENTE solo si el comando contiene presente o presentes.',
            'Usa JUSTIFICADO solo si el comando contiene justificado, justificada, justificados o justificadas.',
            'Para "pagos confirmados", devuelve tipo_reporte = pagos_confirmados y estado_pago = CONFIRMADO, y deja el resto en null salvo que el usuario lo mencione explicitamente.',
            'Para "pagos pendientes", devuelve tipo_reporte = pagos_confirmados y estado_pago = PENDIENTE.',
            'Para "pagos rechazados", devuelve tipo_reporte = pagos_confirmados y estado_pago = RECHAZADO.',
            'Para "pagos" sin estado, devuelve tipo_reporte = pagos_confirmados y estado_pago = null.',
            'Si el usuario pide una busqueda por nombre o CI, usa busqueda.',
            'Devuelve exactamente esta estructura JSON: {"tipo_reporte":null,"gestion":null,"carrera_id":null,"grupo_id":null,"materia_id":null,"estado_inscripcion":null,"estado_pago":null,"estado_resultado":null,"estado_asistencia":null,"fecha_desde":null,"fecha_hasta":null,"busqueda":null,"confidence":0,"mensaje":""}',
            'Tipos de reporte permitidos: ' . json_encode($catalogo['tipos_reporte'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'Gestiones disponibles: ' . json_encode($catalogo['gestiones'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'Carreras activas: ' . json_encode($catalogo['carreras'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'Grupos activos: ' . json_encode($catalogo['grupos'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'Materias activas: ' . json_encode($catalogo['materias'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'Estados de inscripcion permitidos: ' . json_encode($catalogo['estados_inscripcion'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'Estados de pago permitidos: ' . json_encode($catalogo['estados_pago'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'Estados de resultado permitidos: ' . json_encode($catalogo['estados_resultado'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
            'Estados de asistencia permitidos: ' . json_encode($catalogo['estados_asistencia'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ]);
    }

    protected function sanitizeGroqFilters(array $payload, array $catalogo, string $command): array
    {
        $filters = [
            'tipo_reporte' => null,
            'gestion' => null,
            'carrera_id' => null,
            'grupo_id' => null,
            'materia_id' => null,
            'estado_inscripcion' => null,
            'estado_pago' => null,
            'estado_resultado' => null,
            'estado_asistencia' => null,
            'fecha_desde' => null,
            'fecha_hasta' => null,
            'busqueda' => null,
            'confidence' => 0,
            'mensaje' => '',
        ];

        $tipoReporte = is_string($payload['tipo_reporte'] ?? null) ? trim($payload['tipo_reporte']) : null;
        if ($tipoReporte !== null && in_array($tipoReporte, $catalogo['tipos_reporte'], true)) {
            $filters['tipo_reporte'] = $tipoReporte;
        }

        $gestion = is_string($payload['gestion'] ?? null) ? trim($payload['gestion']) : null;
        if ($gestion !== null && in_array($gestion, $catalogo['gestiones'], true)) {
            $filters['gestion'] = $gestion;
        }

        $filters['carrera_id'] = $this->sanitizeGroqId($payload['carrera_id'] ?? null, $catalogo['carreras']);
        $filters['grupo_id'] = $this->sanitizeGroqId($payload['grupo_id'] ?? null, $catalogo['grupos']);
        $filters['materia_id'] = $this->sanitizeGroqId($payload['materia_id'] ?? null, $catalogo['materias']);

        foreach ([
            'estado_inscripcion' => 'estados_inscripcion',
            'estado_pago' => 'estados_pago',
            'estado_resultado' => 'estados_resultado',
            'estado_asistencia' => 'estados_asistencia',
        ] as $field => $catalogKey) {
            $value = is_string($payload[$field] ?? null) ? trim($payload[$field]) : null;

            if ($value !== null && in_array($value, $catalogo[$catalogKey], true)) {
                $filters[$field] = $value;
            }
        }

        $filters['fecha_desde'] = $this->sanitizeGroqDate($payload['fecha_desde'] ?? null);
        $filters['fecha_hasta'] = $this->sanitizeGroqDate($payload['fecha_hasta'] ?? null);
        $filters['busqueda'] = $this->sanitizeGroqBusqueda($payload['busqueda'] ?? null);

        $confidence = $payload['confidence'] ?? 0;
        if (is_numeric($confidence)) {
            $filters['confidence'] = max(0, min(1, (float) $confidence));
        }

        $mensaje = is_string($payload['mensaje'] ?? null) ? trim($payload['mensaje']) : '';
        $filters['mensaje'] = Str::limit($mensaje, 160, '');

        return $this->sanitizeGroqFiltersAgainstCommand($filters, $command, $catalogo);
    }

    protected function sanitizeGroqFiltersAgainstCommand(array $filters, string $command, array $catalogo): array
    {
        $normalizedCommand = $this->normalizeGroqCommand($command);
        $today = now()->toDateString();

        if (!$this->commandMentionsGestion($normalizedCommand, $catalogo['gestiones'])) {
            $filters['gestion'] = null;
        }

        if (!$this->commandMentionsCatalogOption($normalizedCommand, $filters['carrera_id'], $catalogo['carreras'])) {
            $filters['carrera_id'] = null;
        }

        if (!$this->commandMentionsGroup($normalizedCommand, $filters['grupo_id'], $catalogo['grupos'])) {
            $filters['grupo_id'] = null;
        }

        if (!$this->commandMentionsCatalogOption($normalizedCommand, $filters['materia_id'], $catalogo['materias'])) {
            $filters['materia_id'] = null;
        }

        if (!$this->commandMentionsInscriptionState($normalizedCommand)) {
            $filters['estado_inscripcion'] = null;
        }

        $filters['estado_asistencia'] = $this->resolveAttendanceStateFromCommand($normalizedCommand);

        if (!$this->commandMentionsResultState($normalizedCommand)) {
            $filters['estado_resultado'] = null;
        }

        if ($this->commandContainsAny($normalizedCommand, [' hoy ', ' hoy', 'hoy ', 'hoy'])) {
            $filters['fecha_desde'] = $today;
            $filters['fecha_hasta'] = $today;
        } elseif ($this->commandContainsAny($normalizedCommand, [' ayer ', ' ayer', 'ayer ', 'ayer'])) {
            $yesterday = now()->copy()->subDay()->toDateString();
            $filters['fecha_desde'] = $yesterday;
            $filters['fecha_hasta'] = $yesterday;
        }

        $filters['estado_pago'] = $this->sanitizePaymentStateAgainstCommand(
            $normalizedCommand,
            $filters['tipo_reporte'],
            $filters['estado_pago']
        );

        if ($filters['busqueda'] !== null && !$this->commandMentionsSearchIntent($normalizedCommand, $filters['busqueda'])) {
            $filters['busqueda'] = null;
        }

        return $filters;
    }

    protected function normalizeGroqCommand(string $command): string
    {
        $normalized = Str::of($command)->lower()->ascii()->toString();
        $normalized = preg_replace('/[^a-z0-9\s\-\/\.]/', ' ', $normalized) ?? '';

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? '');
    }

    protected function commandMentionsGestion(string $normalizedCommand, array $gestiones): bool
    {
        if ($normalizedCommand === '') {
            return false;
        }

        if (preg_match('/\b[12]\s*[-\/\. ]?\s*(20\d{2}|\d{2})\b/', $normalizedCommand)) {
            return true;
        }

        $compactCommand = preg_replace('/[^0-9]/', '', $normalizedCommand) ?? '';

        foreach ($gestiones as $gestion) {
            $compactGestion = preg_replace('/[^0-9]/', '', (string) $gestion) ?? '';

            if ($compactGestion !== '' && str_contains($compactCommand, $compactGestion)) {
                return true;
            }

            if (preg_match('/^([12])-(20\d{2})$/', (string) $gestion, $matches)) {
                $short = $matches[1] . substr($matches[2], -2);

                if ($short !== '' && preg_match('/\b' . preg_quote($short, '/') . '\b/', $normalizedCommand)) {
                    return true;
                }
            }
        }

        return false;
    }

    protected function commandMentionsCatalogOption(string $normalizedCommand, ?int $selectedId, array $options): bool
    {
        if ($selectedId === null) {
            return false;
        }

        $option = collect($options)->firstWhere('id', $selectedId);

        if (!is_array($option)) {
            return false;
        }

        $normalizedName = $this->normalizeGroqCommand((string) ($option['nombre'] ?? ''));

        return $normalizedName !== '' && str_contains($normalizedCommand, $normalizedName);
    }

    protected function commandMentionsGroup(string $normalizedCommand, ?int $selectedId, array $groups): bool
    {
        if ($selectedId === null) {
            return false;
        }

        $group = collect($groups)->firstWhere('id', $selectedId);

        if (!is_array($group)) {
            return false;
        }

        $normalizedName = $this->normalizeGroqCommand((string) ($group['nombre'] ?? ''));
        $normalizedGestion = $this->normalizeGroqCommand((string) ($group['gestion'] ?? ''));

        if ($normalizedName !== '' && str_contains($normalizedCommand, $normalizedName)) {
            return true;
        }

        return str_contains($normalizedCommand, 'grupo') && $normalizedGestion !== '' && $this->commandMentionsGestion($normalizedCommand, [$group['gestion']]);
    }

    protected function commandMentionsInscriptionState(string $normalizedCommand): bool
    {
        return $this->commandContainsAny($normalizedCommand, [
            'pre registrado',
            'preregistrado',
            'requisitos aprobados',
            'pago pendiente',
            'inscrito',
            'inscripcion',
            'observado',
        ]);
    }

    protected function resolveAttendanceStateFromCommand(string $normalizedCommand): ?string
    {
        if ($this->commandContainsAny($normalizedCommand, [
            'ausente',
            'ausentes',
            'falta',
            'faltas',
            'inasistencia',
            'inasistencias',
        ])) {
            return 'AUSENTE';
        }

        if ($this->commandContainsAny($normalizedCommand, [
            'retraso',
            'retrasos',
            'tarde',
            'tardanza',
        ])) {
            return 'RETRASO';
        }

        if ($this->commandContainsAny($normalizedCommand, [
            'presente',
            'presentes',
        ])) {
            return 'PRESENTE';
        }

        if ($this->commandContainsAny($normalizedCommand, [
            'justificado',
            'justificada',
            'justificados',
            'justificadas',
        ])) {
            return 'JUSTIFICADO';
        }

        return null;
    }

    protected function commandMentionsResultState(string $normalizedCommand): bool
    {
        return $this->commandContainsAny($normalizedCommand, [
            'aprobado',
            'aprobados',
            'reprobado',
            'reprobados',
            'resultado',
            'resultados',
            'pendiente',
            'pendientes',
        ]);
    }

    protected function sanitizePaymentStateAgainstCommand(string $normalizedCommand, ?string $tipoReporte, ?string $estadoPago): ?string
    {
        if ($tipoReporte !== 'pagos_confirmados') {
            return null;
        }

        if ($this->commandContainsAny($normalizedCommand, ['confirmado', 'confirmados'])) {
            return 'CONFIRMADO';
        }

        if ($this->commandContainsAny($normalizedCommand, ['pendiente', 'pendientes'])) {
            return 'PENDIENTE';
        }

        if ($this->commandContainsAny($normalizedCommand, ['rechazado', 'rechazados'])) {
            return 'RECHAZADO';
        }

        return $this->commandContainsAny($normalizedCommand, ['pago', 'pagos']) ? null : $estadoPago;
    }

    protected function commandMentionsSearchIntent(string $normalizedCommand, string $search): bool
    {
        $normalizedSearch = $this->normalizeGroqCommand($search);

        return $normalizedSearch !== '' && (
            str_contains($normalizedCommand, $normalizedSearch) ||
            $this->commandContainsAny($normalizedCommand, ['buscar', 'busca', 'busqueda', 'nombre', 'ci'])
        );
    }

    protected function commandContainsAny(string $normalizedCommand, array $needles): bool
    {
        foreach ($needles as $needle) {
            if (str_contains($normalizedCommand, $needle)) {
                return true;
            }
        }

        return false;
    }

    protected function sanitizeGroqId(mixed $value, array $options): ?int
    {
        if (!is_numeric($value)) {
            return null;
        }

        $candidate = (int) $value;
        $allowedIds = collect($options)->pluck('id')->map(fn ($id) => (int) $id)->all();

        return in_array($candidate, $allowedIds, true) ? $candidate : null;
    }

    protected function sanitizeGroqDate(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return null;
        }

        return strtotime($value) !== false ? $value : null;
    }

    protected function sanitizeGroqBusqueda(mixed $value): ?string
    {
        if (!is_string($value)) {
            return null;
        }

        $value = trim($value);
        $value = preg_replace('/[^\pL\pN\s\-_\.]/u', '', $value) ?? '';
        $value = trim(preg_replace('/\s+/', ' ', $value) ?? '');

        if ($value === '') {
            return null;
        }

        return Str::limit($value, 100, '');
    }

    protected function allowedReportFilterKeys(): array
    {
        return [
            'tipo_reporte',
            'gestion',
            'carrera_id',
            'grupo_id',
            'materia_id',
            'estado_inscripcion',
            'estado_pago',
            'estado_resultado',
            'estado_asistencia',
            'fecha_desde',
            'fecha_hasta',
            'busqueda',
        ];
    }

    protected function getReporteOptions(): array
    {
        return [
            'postulantes_inscritos' => [
                'label' => 'Postulantes inscritos',
                'description' => 'Consulta estado de inscripcion, carreras elegidas, grupo actual y gestion asociada.',
            ],
            'pagos_confirmados' => [
                'label' => 'Pagos confirmados',
                'description' => 'Revisa pagos del proceso de admision con fecha, monto y estado por postulante.',
            ],
            'requisitos_postulantes' => [
                'label' => 'Requisitos de postulantes',
                'description' => 'Visualiza requisitos validados, observaciones y responsable de verificacion.',
            ],
            'grupos_academicos' => [
                'label' => 'Grupos academicos',
                'description' => 'Resume capacidad, asignacion real y estado de cada grupo por gestion.',
            ],
            'docentes_asignaciones' => [
                'label' => 'Docentes y asignaciones',
                'description' => 'Muestra docente, CI, grupo, materia y estado de cada asignacion.',
            ],
            'asistencia_docente' => [
                'label' => 'Asistencia docente',
                'description' => 'Consulta asistencia por fecha, horario, aula, grupo y estado registrado.',
            ],
            'notas_por_materia' => [
                'label' => 'Notas por materia',
                'description' => 'Lista notas de evaluaciones con grupo, materia, porcentaje y responsable.',
            ],
            'resultados_admision' => [
                'label' => 'Resultados de admision',
                'description' => 'Consulta promedios, estado del resultado y carrera asignada por gestion.',
            ],
            'cupos_carrera' => [
                'label' => 'Cupos por carrera',
                'description' => 'Resume cupos maximos, ocupados y disponibles por carrera y gestion.',
            ],
        ];
    }

    protected function getReporteTitle(string $tipoReporte): string
    {
        return $this->getReporteOptions()[$tipoReporte]['label'] ?? 'Reporte';
    }

    protected function getReporteColumns(string $tipoReporte): array
    {
        return match ($tipoReporte) {
            'postulantes_inscritos' => [
                'ci' => 'CI',
                'postulante' => 'Postulante',
                'correo' => 'Correo',
                'telefono' => 'Telefono',
                'estado_inscripcion' => 'Estado inscripcion',
                'primera_opcion' => 'Primera opcion',
                'segunda_opcion' => 'Segunda opcion',
                'grupo_actual' => 'Grupo actual',
                'gestion' => 'Gestion',
            ],
            'pagos_confirmados' => [
                'postulante' => 'Postulante',
                'ci' => 'CI',
                'monto' => 'Monto',
                'moneda' => 'Moneda',
                'estado_pago' => 'Estado pago',
                'fecha_pago' => 'Fecha pago',
                'observacion' => 'Observacion',
                'grupo_actual' => 'Grupo',
                'gestion' => 'Gestion',
            ],
            'requisitos_postulantes' => [
                'postulante' => 'Postulante',
                'ci' => 'CI',
                'requisito' => 'Requisito',
                'obligatorio' => 'Obligatorio',
                'estado_requisito' => 'Estado requisito',
                'fecha_validacion' => 'Fecha validacion',
                'observacion' => 'Observacion',
                'validado_por' => 'Validado por',
            ],
            'grupos_academicos' => [
                'grupo' => 'Grupo',
                'codigo' => 'Codigo',
                'gestion' => 'Gestion',
                'capacidad_maxima' => 'Capacidad maxima',
                'cantidad_estudiantes' => 'Cantidad registrada',
                'cantidad_real_asignados' => 'Cantidad real',
                'estado' => 'Estado',
            ],
            'docentes_asignaciones' => [
                'docente' => 'Docente',
                'ci' => 'CI docente',
                'grupo' => 'Grupo',
                'gestion' => 'Gestion',
                'materia' => 'Materia',
                'estado' => 'Estado asignacion',
            ],
            'asistencia_docente' => [
                'fecha' => 'Fecha',
                'docente' => 'Docente',
                'grupo' => 'Grupo',
                'gestion' => 'Gestion',
                'materia' => 'Materia',
                'aula' => 'Aula',
                'dia_semana' => 'Dia',
                'horario' => 'Horario',
                'hora_registro' => 'Hora registro',
                'estado_asistencia' => 'Estado asistencia',
                'observacion' => 'Observacion',
            ],
            'notas_por_materia' => [
                'postulante' => 'Postulante',
                'ci' => 'CI',
                'grupo' => 'Grupo',
                'gestion' => 'Gestion',
                'materia' => 'Materia',
                'evaluacion' => 'Evaluacion',
                'numero_evaluacion' => 'Nro.',
                'porcentaje' => 'Porcentaje',
                'nota' => 'Nota',
                'observacion' => 'Observacion',
                'registrado_por' => 'Registrado por',
            ],
            'resultados_admision' => [
                'postulante' => 'Postulante',
                'ci' => 'CI',
                'grupo' => 'Grupo',
                'gestion' => 'Gestion',
                'promedio_final' => 'Promedio final',
                'estado_resultado' => 'Estado resultado',
                'carrera_asignada' => 'Carrera asignada',
                'tipo_asignacion' => 'Tipo asignacion',
                'fecha_resultado' => 'Fecha resultado',
                'observacion' => 'Observacion',
            ],
            'cupos_carrera' => [
                'carrera' => 'Carrera',
                'codigo_carrera' => 'Codigo carrera',
                'gestion' => 'Gestion',
                'cupo_maximo' => 'Cupo maximo',
                'cupos_ocupados' => 'Cupos ocupados',
                'cupos_disponibles' => 'Cupos disponibles',
                'estado' => 'Estado',
            ],
            default => [],
        };
    }

    protected function normalizeReporteRows(Collection $rows, string $tipoReporte): Collection
    {
        return $rows->map(function ($row) use ($tipoReporte): array {
            return match ($tipoReporte) {
                'postulantes_inscritos' => [
                    'ci' => $row->ci,
                    'postulante' => trim($row->nombres . ' ' . $row->apellidos),
                    'correo' => $row->correo,
                    'telefono' => $row->telefono ?: 'Sin registro',
                    'estado_inscripcion' => $row->estado_inscripcion,
                    'primera_opcion' => $row->primera_opcion ?: 'Sin registro',
                    'segunda_opcion' => $row->segunda_opcion ?: 'Sin registro',
                    'grupo_actual' => $row->grupo_actual ?: 'Sin grupo',
                    'gestion' => $row->gestion ?: 'Sin gestion',
                ],
                'pagos_confirmados' => [
                    'postulante' => trim($row->nombres . ' ' . $row->apellidos),
                    'ci' => $row->ci,
                    'monto' => $this->formatNumber($row->monto),
                    'moneda' => $row->moneda,
                    'estado_pago' => $row->estado_pago,
                    'fecha_pago' => $this->formatDateTime($row->fecha_pago),
                    'observacion' => $row->observacion ?: 'Sin observacion',
                    'grupo_actual' => $row->grupo_actual ?: 'Sin grupo',
                    'gestion' => $row->gestion ?: 'Sin gestion',
                ],
                'requisitos_postulantes' => [
                    'postulante' => trim($row->nombres . ' ' . $row->apellidos),
                    'ci' => $row->ci,
                    'requisito' => $row->requisito,
                    'obligatorio' => $row->obligatorio ? 'Si' : 'No',
                    'estado_requisito' => $row->estado_requisito,
                    'fecha_validacion' => $this->formatDateTime($row->fecha_validacion),
                    'observacion' => $row->observacion ?: 'Sin observacion',
                    'validado_por' => trim((string) $row->validado_por) !== '' ? $row->validado_por : 'Sin registro',
                ],
                'grupos_academicos' => [
                    'grupo' => $row->nombre,
                    'codigo' => $row->codigo,
                    'gestion' => $row->gestion,
                    'capacidad_maxima' => (string) $row->capacidad_maxima,
                    'cantidad_estudiantes' => (string) $row->cantidad_estudiantes,
                    'cantidad_real_asignados' => (string) $row->cantidad_real_asignados,
                    'estado' => $row->estado,
                ],
                'docentes_asignaciones' => [
                    'docente' => trim($row->nombres . ' ' . $row->apellidos),
                    'ci' => $row->ci,
                    'grupo' => $row->grupo,
                    'gestion' => $row->gestion,
                    'materia' => $row->materia,
                    'estado' => $row->estado,
                ],
                'asistencia_docente' => [
                    'fecha' => $this->formatDate($row->fecha),
                    'docente' => trim($row->nombres . ' ' . $row->apellidos),
                    'grupo' => $row->grupo,
                    'gestion' => $row->gestion,
                    'materia' => $row->materia,
                    'aula' => $row->aula,
                    'dia_semana' => $row->dia_semana,
                    'horario' => $this->formatTime($row->hora_inicio) . ' - ' . $this->formatTime($row->hora_fin),
                    'hora_registro' => $row->hora_registro ? $this->formatTime($row->hora_registro) : 'Sin registro',
                    'estado_asistencia' => $row->estado_asistencia,
                    'observacion' => $row->observacion ?: 'Sin observacion',
                ],
                'notas_por_materia' => [
                    'postulante' => trim($row->nombres . ' ' . $row->apellidos),
                    'ci' => $row->ci,
                    'grupo' => $row->grupo ?: 'Sin grupo',
                    'gestion' => $row->gestion ?: 'Sin gestion',
                    'materia' => $row->materia,
                    'evaluacion' => $row->evaluacion,
                    'numero_evaluacion' => (string) $row->numero_evaluacion,
                    'porcentaje' => $this->formatNumber($row->porcentaje) . '%',
                    'nota' => $this->formatNumber($row->nota),
                    'observacion' => $row->observacion ?: 'Sin observacion',
                    'registrado_por' => trim((string) $row->registrado_por) !== '' ? $row->registrado_por : 'Sin registro',
                ],
                'resultados_admision' => [
                    'postulante' => trim($row->nombres . ' ' . $row->apellidos),
                    'ci' => $row->ci,
                    'grupo' => $row->grupo ?: 'Sin grupo',
                    'gestion' => $row->gestion ?: 'Sin gestion',
                    'promedio_final' => $this->formatNumber($row->promedio_final),
                    'estado_resultado' => $row->estado_resultado,
                    'carrera_asignada' => $row->carrera_asignada ?: 'Sin asignacion',
                    'tipo_asignacion' => $row->tipo_asignacion ?: 'Sin registro',
                    'fecha_resultado' => $this->formatDateTime($row->fecha_resultado),
                    'observacion' => $row->observacion ?: 'Sin observacion',
                ],
                'cupos_carrera' => [
                    'carrera' => $row->carrera,
                    'codigo_carrera' => $row->codigo_carrera,
                    'gestion' => $row->gestion,
                    'cupo_maximo' => (string) $row->cupo_maximo,
                    'cupos_ocupados' => (string) $row->cupos_ocupados,
                    'cupos_disponibles' => (string) $row->cupos_disponibles,
                    'estado' => $row->estado,
                ],
                default => [],
            };
        });
    }

    protected function buildReportQuery(string $tipoReporte, array $filters)
    {
        return match ($tipoReporte) {
            'postulantes_inscritos' => $this->buildPostulantesInscritosQuery($filters),
            'pagos_confirmados' => $this->buildPagosConfirmadosQuery($filters),
            'requisitos_postulantes' => $this->buildRequisitosPostulantesQuery($filters),
            'grupos_academicos' => $this->buildGruposAcademicosQuery($filters),
            'docentes_asignaciones' => $this->buildDocentesAsignacionesQuery($filters),
            'asistencia_docente' => $this->buildAsistenciaDocenteQuery($filters),
            'notas_por_materia' => $this->buildNotasPorMateriaQuery($filters),
            'resultados_admision' => $this->buildResultadosAdmisionQuery($filters),
            'cupos_carrera' => $this->buildCuposCarreraQuery($filters),
            default => throw new \InvalidArgumentException('Tipo de reporte no valido.'),
        };
    }

    protected function buildPostulantesInscritosQuery(array $filters)
    {
        $query = DB::table('postulantes')
            ->leftJoin('carreras as carrera_primera', 'carrera_primera.id', '=', 'postulantes.carrera_primera_opcion_id')
            ->leftJoin('carreras as carrera_segunda', 'carrera_segunda.id', '=', 'postulantes.carrera_segunda_opcion_id')
            ->leftJoin('grupo_postulantes', function ($join): void {
                $join->on('grupo_postulantes.postulante_id', '=', 'postulantes.id')
                    ->where('grupo_postulantes.estado', '=', 'ACTIVO');
            })
            ->leftJoin('grupos', function ($join): void {
                $join->on('grupos.id', '=', 'grupo_postulantes.grupo_id')
                    ->where('grupos.estado', '=', 'ACTIVO');
            });

        $this->applyPostulanteFilters($query, $filters);

        return $query
            ->select([
                'postulantes.ci',
                'postulantes.nombres',
                'postulantes.apellidos',
                'postulantes.correo',
                'postulantes.telefono',
                'postulantes.estado_inscripcion',
                'carrera_primera.nombre as primera_opcion',
                'carrera_segunda.nombre as segunda_opcion',
                'grupos.nombre as grupo_actual',
                'grupos.gestion',
            ])
            ->orderBy('postulantes.apellidos')
            ->orderBy('postulantes.nombres');
    }

    protected function buildPagosConfirmadosQuery(array $filters)
    {
        $query = DB::table('pagos')
            ->join('postulantes', 'postulantes.id', '=', 'pagos.postulante_id')
            ->leftJoin('grupo_postulantes', function ($join): void {
                $join->on('grupo_postulantes.postulante_id', '=', 'pagos.postulante_id')
                    ->where('grupo_postulantes.estado', '=', 'ACTIVO');
            })
            ->leftJoin('grupos', function ($join): void {
                $join->on('grupos.id', '=', 'grupo_postulantes.grupo_id')
                    ->where('grupos.estado', '=', 'ACTIVO');
            });

        $estadoPago = $filters['estado_pago'] !== '' ? $filters['estado_pago'] : 'CONFIRMADO';

        return $query
            ->where('pagos.estado_pago', $estadoPago)
            ->when($filters['gestion'] !== '', fn ($builder) => $builder->where('grupos.gestion', $filters['gestion']))
            ->when($filters['fecha_desde'] !== '', fn ($builder) => $builder->whereDate('pagos.fecha_pago', '>=', $filters['fecha_desde']))
            ->when($filters['fecha_hasta'] !== '', fn ($builder) => $builder->whereDate('pagos.fecha_pago', '<=', $filters['fecha_hasta']))
            ->when($filters['busqueda'] !== '', function ($builder) use ($filters): void {
                $term = '%' . $filters['busqueda'] . '%';
                $builder->where(function ($subQuery) use ($term): void {
                    $subQuery
                        ->where('postulantes.ci', 'ILIKE', $term)
                        ->orWhere('postulantes.nombres', 'ILIKE', $term)
                        ->orWhere('postulantes.apellidos', 'ILIKE', $term);
                });
            })
            ->select([
                'postulantes.ci',
                'postulantes.nombres',
                'postulantes.apellidos',
                'pagos.monto',
                'pagos.moneda',
                'pagos.estado_pago',
                'pagos.fecha_pago',
                'pagos.observacion',
                'grupos.nombre as grupo_actual',
                'grupos.gestion',
            ])
            ->orderByDesc('pagos.fecha_pago')
            ->orderBy('postulantes.apellidos');
    }

    protected function buildRequisitosPostulantesQuery(array $filters)
    {
        return DB::table('postulante_requisitos')
            ->join('postulantes', 'postulantes.id', '=', 'postulante_requisitos.postulante_id')
            ->join('requisitos', 'requisitos.id', '=', 'postulante_requisitos.requisito_id')
            ->leftJoin('usuarios', 'usuarios.id', '=', 'postulante_requisitos.validado_por')
            ->leftJoin('grupo_postulantes', function ($join): void {
                $join->on('grupo_postulantes.postulante_id', '=', 'postulante_requisitos.postulante_id')
                    ->where('grupo_postulantes.estado', '=', 'ACTIVO');
            })
            ->leftJoin('grupos', function ($join): void {
                $join->on('grupos.id', '=', 'grupo_postulantes.grupo_id')
                    ->where('grupos.estado', '=', 'ACTIVO');
            })
            ->when($filters['gestion'] !== '', fn ($builder) => $builder->where('grupos.gestion', $filters['gestion']))
            ->when($filters['estado_inscripcion'] !== '', fn ($builder) => $builder->where('postulantes.estado_inscripcion', $filters['estado_inscripcion']))
            ->when($filters['fecha_desde'] !== '', fn ($builder) => $builder->whereDate('postulante_requisitos.fecha_validacion', '>=', $filters['fecha_desde']))
            ->when($filters['fecha_hasta'] !== '', fn ($builder) => $builder->whereDate('postulante_requisitos.fecha_validacion', '<=', $filters['fecha_hasta']))
            ->when($filters['busqueda'] !== '', function ($builder) use ($filters): void {
                $term = '%' . $filters['busqueda'] . '%';
                $builder->where(function ($subQuery) use ($term): void {
                    $subQuery
                        ->where('postulantes.ci', 'ILIKE', $term)
                        ->orWhere('postulantes.nombres', 'ILIKE', $term)
                        ->orWhere('postulantes.apellidos', 'ILIKE', $term);
                });
            })
            ->select([
                'postulantes.ci',
                'postulantes.nombres',
                'postulantes.apellidos',
                'requisitos.nombre as requisito',
                'requisitos.obligatorio',
                'postulante_requisitos.estado as estado_requisito',
                'postulante_requisitos.fecha_validacion',
                'postulante_requisitos.observacion',
                DB::raw("TRIM(COALESCE(usuarios.nombre, '') || ' ' || COALESCE(usuarios.apellido, '')) as validado_por"),
            ])
            ->orderByDesc('postulante_requisitos.fecha_validacion')
            ->orderBy('postulantes.apellidos');
    }

    protected function buildGruposAcademicosQuery(array $filters)
    {
        return DB::table('grupos')
            ->leftJoin('grupo_postulantes', function ($join): void {
                $join->on('grupo_postulantes.grupo_id', '=', 'grupos.id')
                    ->where('grupo_postulantes.estado', '=', 'ACTIVO');
            })
            ->when($filters['gestion'] !== '', fn ($builder) => $builder->where('grupos.gestion', $filters['gestion']))
            ->when($filters['grupo_id'] !== '', fn ($builder) => $builder->where('grupos.id', $filters['grupo_id']))
            ->when($filters['busqueda'] !== '', function ($builder) use ($filters): void {
                $term = '%' . $filters['busqueda'] . '%';
                $builder->where(function ($subQuery) use ($term): void {
                    $subQuery
                        ->where('grupos.nombre', 'ILIKE', $term)
                        ->orWhere('grupos.codigo', 'ILIKE', $term);
                });
            })
            ->groupBy('grupos.id', 'grupos.nombre', 'grupos.codigo', 'grupos.gestion', 'grupos.capacidad_maxima', 'grupos.cantidad_estudiantes', 'grupos.estado')
            ->select([
                'grupos.nombre',
                'grupos.codigo',
                'grupos.gestion',
                'grupos.capacidad_maxima',
                'grupos.cantidad_estudiantes',
                'grupos.estado',
                DB::raw('COUNT(grupo_postulantes.id) as cantidad_real_asignados'),
            ])
            ->orderByDesc('grupos.gestion')
            ->orderBy('grupos.nombre');
    }

    protected function buildDocentesAsignacionesQuery(array $filters)
    {
        return DB::table('docente_asignaciones')
            ->join('docentes', 'docentes.id', '=', 'docente_asignaciones.docente_id')
            ->join('grupos', 'grupos.id', '=', 'docente_asignaciones.grupo_id')
            ->join('materias', 'materias.id', '=', 'docente_asignaciones.materia_id')
            ->when($filters['gestion'] !== '', function ($builder) use ($filters): void {
                $builder->where(function ($subQuery) use ($filters): void {
                    $subQuery
                        ->where('docente_asignaciones.gestion', $filters['gestion'])
                        ->orWhere('grupos.gestion', $filters['gestion']);
                });
            })
            ->when($filters['grupo_id'] !== '', fn ($builder) => $builder->where('grupos.id', $filters['grupo_id']))
            ->when($filters['materia_id'] !== '', fn ($builder) => $builder->where('materias.id', $filters['materia_id']))
            ->when($filters['busqueda'] !== '', function ($builder) use ($filters): void {
                $term = '%' . $filters['busqueda'] . '%';
                $builder->where(function ($subQuery) use ($term): void {
                    $subQuery
                        ->where('docentes.nombres', 'ILIKE', $term)
                        ->orWhere('docentes.apellidos', 'ILIKE', $term)
                        ->orWhere('docentes.ci', 'ILIKE', $term);
                });
            })
            ->select([
                'docentes.ci',
                'docentes.nombres',
                'docentes.apellidos',
                'grupos.nombre as grupo',
                'grupos.gestion',
                'materias.nombre as materia',
                'docente_asignaciones.estado',
            ])
            ->orderByDesc('grupos.gestion')
            ->orderBy('docentes.apellidos')
            ->orderBy('materias.nombre');
    }

    protected function buildAsistenciaDocenteQuery(array $filters)
    {
        return DB::table('asistencias_docentes')
            ->join('docentes', 'docentes.id', '=', 'asistencias_docentes.docente_id')
            ->join('horarios', 'horarios.id', '=', 'asistencias_docentes.horario_id')
            ->join('grupos', 'grupos.id', '=', 'horarios.grupo_id')
            ->join('materias', 'materias.id', '=', 'horarios.materia_id')
            ->join('aulas', 'aulas.id', '=', 'horarios.aula_id')
            ->when($filters['gestion'] !== '', fn ($builder) => $builder->where('grupos.gestion', $filters['gestion']))
            ->when($filters['grupo_id'] !== '', fn ($builder) => $builder->where('grupos.id', $filters['grupo_id']))
            ->when($filters['materia_id'] !== '', fn ($builder) => $builder->where('materias.id', $filters['materia_id']))
            ->when($filters['estado_asistencia'] !== '', fn ($builder) => $builder->where('asistencias_docentes.estado_asistencia', $filters['estado_asistencia']))
            ->when($filters['fecha_desde'] !== '', fn ($builder) => $builder->whereDate('asistencias_docentes.fecha', '>=', $filters['fecha_desde']))
            ->when($filters['fecha_hasta'] !== '', fn ($builder) => $builder->whereDate('asistencias_docentes.fecha', '<=', $filters['fecha_hasta']))
            ->when($filters['busqueda'] !== '', function ($builder) use ($filters): void {
                $term = '%' . $filters['busqueda'] . '%';
                $builder->where(function ($subQuery) use ($term): void {
                    $subQuery
                        ->where('docentes.nombres', 'ILIKE', $term)
                        ->orWhere('docentes.apellidos', 'ILIKE', $term);
                });
            })
            ->select([
                'asistencias_docentes.fecha',
                'docentes.nombres',
                'docentes.apellidos',
                'grupos.nombre as grupo',
                'grupos.gestion',
                'materias.nombre as materia',
                'aulas.nombre as aula',
                'horarios.dia_semana',
                'horarios.hora_inicio',
                'horarios.hora_fin',
                'asistencias_docentes.hora_registro',
                'asistencias_docentes.estado_asistencia',
                'asistencias_docentes.observacion',
            ])
            ->orderByDesc('asistencias_docentes.fecha')
            ->orderBy('docentes.apellidos');
    }

    protected function buildNotasPorMateriaQuery(array $filters)
    {
        return DB::table('notas')
            ->join('postulantes', 'postulantes.id', '=', 'notas.postulante_id')
            ->join('evaluaciones', 'evaluaciones.id', '=', 'notas.evaluacion_id')
            ->join('materias', 'materias.id', '=', 'notas.materia_id')
            ->leftJoin('usuarios', 'usuarios.id', '=', 'notas.registrado_por')
            ->leftJoin('grupo_postulantes', function ($join): void {
                $join->on('grupo_postulantes.postulante_id', '=', 'notas.postulante_id')
                    ->where('grupo_postulantes.estado', '=', 'ACTIVO');
            })
            ->leftJoin('grupos', function ($join): void {
                $join->on('grupos.id', '=', 'grupo_postulantes.grupo_id')
                    ->where('grupos.estado', '=', 'ACTIVO');
            })
            ->when($filters['gestion'] !== '', fn ($builder) => $builder->where('grupos.gestion', $filters['gestion']))
            ->when($filters['grupo_id'] !== '', fn ($builder) => $builder->where('grupos.id', $filters['grupo_id']))
            ->when($filters['materia_id'] !== '', fn ($builder) => $builder->where('materias.id', $filters['materia_id']))
            ->when($filters['fecha_desde'] !== '', fn ($builder) => $builder->whereDate('notas.created_at', '>=', $filters['fecha_desde']))
            ->when($filters['fecha_hasta'] !== '', fn ($builder) => $builder->whereDate('notas.created_at', '<=', $filters['fecha_hasta']))
            ->when($filters['busqueda'] !== '', function ($builder) use ($filters): void {
                $term = '%' . $filters['busqueda'] . '%';
                $builder->where(function ($subQuery) use ($term): void {
                    $subQuery
                        ->where('postulantes.ci', 'ILIKE', $term)
                        ->orWhere('postulantes.nombres', 'ILIKE', $term)
                        ->orWhere('postulantes.apellidos', 'ILIKE', $term);
                });
            })
            ->select([
                'postulantes.ci',
                'postulantes.nombres',
                'postulantes.apellidos',
                'grupos.nombre as grupo',
                'grupos.gestion',
                'materias.nombre as materia',
                'evaluaciones.nombre as evaluacion',
                'evaluaciones.numero_evaluacion',
                'evaluaciones.porcentaje',
                'notas.nota',
                'notas.observacion',
                DB::raw("TRIM(COALESCE(usuarios.nombre, '') || ' ' || COALESCE(usuarios.apellido, '')) as registrado_por"),
            ])
            ->orderByDesc('grupos.gestion')
            ->orderBy('materias.nombre')
            ->orderBy('postulantes.apellidos');
    }

    protected function buildResultadosAdmisionQuery(array $filters)
    {
        $grupoActivoSubquery = $this->activeGrupoPostulanteSubquery();

        return DB::table('resultados_admision')
            ->join('postulantes', 'postulantes.id', '=', 'resultados_admision.postulante_id')
            ->leftJoin('carreras', 'carreras.id', '=', 'resultados_admision.carrera_asignada_id')
            ->leftJoinSub($grupoActivoSubquery, 'grupo_postulante_activo', function ($join): void {
                $join->on('grupo_postulante_activo.postulante_id', '=', 'resultados_admision.postulante_id');
            })
            ->leftJoin('grupo_postulantes as grupo_postulante_actual', 'grupo_postulante_actual.id', '=', 'grupo_postulante_activo.grupo_postulante_id')
            ->leftJoin('grupos as grupo_actual', function ($join): void {
                $join->on('grupo_actual.id', '=', 'grupo_postulante_actual.grupo_id')
                    ->where('grupo_actual.estado', '=', 'ACTIVO');
            })
            ->when($filters['gestion'] !== '', fn ($builder) => $builder->where('grupo_actual.gestion', $filters['gestion']))
            ->when($filters['carrera_id'] !== '', fn ($builder) => $builder->where('resultados_admision.carrera_asignada_id', $filters['carrera_id']))
            ->when($filters['estado_resultado'] !== '', fn ($builder) => $builder->where('resultados_admision.estado_resultado', $filters['estado_resultado']))
            ->when($filters['fecha_desde'] !== '', fn ($builder) => $builder->whereDate('resultados_admision.fecha_resultado', '>=', $filters['fecha_desde']))
            ->when($filters['fecha_hasta'] !== '', fn ($builder) => $builder->whereDate('resultados_admision.fecha_resultado', '<=', $filters['fecha_hasta']))
            ->when($filters['busqueda'] !== '', function ($builder) use ($filters): void {
                $term = '%' . $filters['busqueda'] . '%';
                $builder->where(function ($subQuery) use ($term): void {
                    $subQuery
                        ->where('postulantes.ci', 'ILIKE', $term)
                        ->orWhere('postulantes.nombres', 'ILIKE', $term)
                        ->orWhere('postulantes.apellidos', 'ILIKE', $term);
                });
            })
            ->select([
                'postulantes.ci',
                'postulantes.nombres',
                'postulantes.apellidos',
                'grupo_actual.nombre as grupo',
                'grupo_actual.gestion',
                'resultados_admision.promedio_final',
                'resultados_admision.estado_resultado',
                'carreras.nombre as carrera_asignada',
                'resultados_admision.tipo_asignacion',
                'resultados_admision.fecha_resultado',
                'resultados_admision.observacion',
            ])
            ->orderByDesc('resultados_admision.fecha_resultado')
            ->orderBy('postulantes.apellidos');
    }

    protected function activeGrupoPostulanteSubquery()
    {
        return DB::table('grupo_postulantes')
            ->join('grupos', 'grupos.id', '=', 'grupo_postulantes.grupo_id')
            ->where('grupo_postulantes.estado', 'ACTIVO')
            ->where('grupos.estado', 'ACTIVO')
            ->groupBy('grupo_postulantes.postulante_id')
            ->select([
                'grupo_postulantes.postulante_id',
                DB::raw('MAX(grupo_postulantes.id) as grupo_postulante_id'),
            ]);
    }

    protected function buildCuposCarreraQuery(array $filters)
    {
        return DB::table('cupos_carrera')
            ->join('carreras', 'carreras.id', '=', 'cupos_carrera.carrera_id')
            ->when($filters['gestion'] !== '', fn ($builder) => $builder->whereIn('cupos_carrera.gestion', $this->gestionesCompatibles($filters['gestion'])))
            ->when($filters['carrera_id'] !== '', fn ($builder) => $builder->where('cupos_carrera.carrera_id', $filters['carrera_id']))
            ->when($filters['busqueda'] !== '', function ($builder) use ($filters): void {
                $term = '%' . $filters['busqueda'] . '%';
                $builder->where(function ($subQuery) use ($term): void {
                    $subQuery
                        ->where('carreras.nombre', 'ILIKE', $term)
                        ->orWhere('carreras.codigo', 'ILIKE', $term);
                });
            })
            ->select([
                'carreras.nombre as carrera',
                'carreras.codigo as codigo_carrera',
                'cupos_carrera.gestion',
                'cupos_carrera.cupo_maximo',
                'cupos_carrera.cupos_ocupados',
                'cupos_carrera.cupos_disponibles',
                'cupos_carrera.estado',
            ])
            ->orderByDesc('cupos_carrera.gestion')
            ->orderBy('carreras.nombre');
    }

    protected function authorizeRoles(): void
    {
        $roleName = Str::of((string) (auth()->user()?->rol?->nombre ?? ''))->lower()->ascii()->toString();

        abort_unless(in_array($roleName, ['administrador', 'coordinador', 'autoridad academica'], true), 403);
    }

    protected function consultaFilters(Request $request): array
    {
        return [
            'tipo_reporte' => $request->string('tipo_reporte')->toString(),
            'gestion' => $request->string('gestion')->toString(),
            'carrera_id' => $request->string('carrera_id')->toString(),
            'grupo_id' => $request->string('grupo_id')->toString(),
            'materia_id' => $request->string('materia_id')->toString(),
            'estado_inscripcion' => $request->string('estado_inscripcion')->toString(),
            'estado_pago' => $request->string('estado_pago')->toString(),
            'estado_resultado' => $request->string('estado_resultado')->toString(),
            'estado_asistencia' => $request->string('estado_asistencia')->toString(),
            'fecha_desde' => $request->string('fecha_desde')->toString(),
            'fecha_hasta' => $request->string('fecha_hasta')->toString(),
            'busqueda' => trim($request->string('busqueda')->toString()),
        ];
    }

    protected function consultaFormOptions(): array
    {
        return [
            'gestiones' => $this->gestionesDisponibles(),
            'carreras' => Carrera::query()->where('estado', 'ACTIVO')->orderBy('nombre')->get(),
            'grupos' => Grupo::query()->where('estado', 'ACTIVO')->orderByDesc('gestion')->orderBy('nombre')->get(),
            'materias' => Materia::query()->where('estado', 'ACTIVO')->orderBy('nombre')->get(),
            'estadosInscripcion' => ['PRE_REGISTRADO', 'REQUISITOS_APROBADOS', 'PAGO_PENDIENTE', 'INSCRITO', 'OBSERVADO'],
            'estadosPago' => ['PENDIENTE', 'CONFIRMADO', 'RECHAZADO'],
            'estadosResultado' => ['PENDIENTE', 'APROBADO', 'REPROBADO'],
            'estadosAsistencia' => ['PRESENTE', 'RETRASO', 'AUSENTE', 'JUSTIFICADO'],
        ];
    }

    protected function appliedFiltersSummary(array $filters): array
    {
        $labels = [
            'gestion' => 'Gestion',
            'carrera_id' => 'Carrera',
            'grupo_id' => 'Grupo',
            'materia_id' => 'Materia',
            'estado_inscripcion' => 'Estado inscripcion',
            'estado_pago' => 'Estado pago',
            'estado_resultado' => 'Estado resultado',
            'estado_asistencia' => 'Estado asistencia',
            'fecha_desde' => 'Fecha desde',
            'fecha_hasta' => 'Fecha hasta',
            'busqueda' => 'Busqueda',
        ];

        return collect($filters)
            ->except('tipo_reporte')
            ->filter(fn ($value) => $value !== '')
            ->mapWithKeys(function ($value, $key) use ($labels): array {
                return [$labels[$key] ?? $key => $value];
            })
            ->all();
    }

    protected function applyPostulanteFilters($query, array $filters): void
    {
        $query
            ->when($filters['gestion'] !== '', fn ($builder) => $builder->where('grupos.gestion', $filters['gestion']))
            ->when($filters['carrera_id'] !== '', function ($builder) use ($filters): void {
                $builder->where(function ($subQuery) use ($filters): void {
                    $subQuery
                        ->where('postulantes.carrera_primera_opcion_id', $filters['carrera_id'])
                        ->orWhere('postulantes.carrera_segunda_opcion_id', $filters['carrera_id']);
                });
            })
            ->when($filters['grupo_id'] !== '', fn ($builder) => $builder->where('grupos.id', $filters['grupo_id']))
            ->when($filters['estado_inscripcion'] !== '', fn ($builder) => $builder->where('postulantes.estado_inscripcion', $filters['estado_inscripcion']))
            ->when($filters['busqueda'] !== '', function ($builder) use ($filters): void {
                $term = '%' . $filters['busqueda'] . '%';
                $builder->where(function ($subQuery) use ($term): void {
                    $subQuery
                        ->where('postulantes.ci', 'ILIKE', $term)
                        ->orWhere('postulantes.nombres', 'ILIKE', $term)
                        ->orWhere('postulantes.apellidos', 'ILIKE', $term)
                        ->orWhere('postulantes.correo', 'ILIKE', $term);
                });
            });
    }

    protected function gestionesDisponibles()
    {
        return DB::table('grupos')
            ->whereNotNull('gestion')
            ->distinct()
            ->pluck('gestion')
            ->merge(
                DB::table('cupos_carrera')
                    ->whereNotNull('gestion')
                    ->distinct()
                    ->pluck('gestion')
            )
            ->filter()
            ->unique()
            ->sortDesc()
            ->values();
    }

    protected function gestionesCompatibles(string $gestion): array
    {
        $year = preg_match('/(\d{4})$/', $gestion, $matches) ? $matches[1] : null;

        return array_values(array_unique(array_filter([$gestion, $year])));
    }

    protected function postulantesQuery(array $filters)
    {
        return DB::table('postulantes')
            ->when($filters['estado_inscripcion'] !== '', fn ($query) => $query->where('postulantes.estado_inscripcion', $filters['estado_inscripcion']))
            ->when($filters['carrera_id'] !== '', function ($query) use ($filters): void {
                $query->where(function ($builder) use ($filters): void {
                    $builder
                        ->where('postulantes.carrera_primera_opcion_id', $filters['carrera_id'])
                        ->orWhere('postulantes.carrera_segunda_opcion_id', $filters['carrera_id']);
                });
            })
            ->when($filters['gestion'] !== '' || $filters['grupo_id'] !== '', function ($query) use ($filters): void {
                $query->whereExists(function ($subQuery) use ($filters): void {
                    $subQuery->selectRaw('1')
                        ->from('grupo_postulantes')
                        ->join('grupos', 'grupos.id', '=', 'grupo_postulantes.grupo_id')
                        ->whereColumn('grupo_postulantes.postulante_id', 'postulantes.id')
                        ->where('grupo_postulantes.estado', 'ACTIVO')
                        ->where('grupos.estado', 'ACTIVO')
                        ->when($filters['gestion'] !== '', fn ($builder) => $builder->where('grupos.gestion', $filters['gestion']))
                        ->when($filters['grupo_id'] !== '', fn ($builder) => $builder->where('grupos.id', $filters['grupo_id']));
                });
            });
    }

    protected function pagosQuery(array $filters)
    {
        return DB::table('pagos')
            ->join('postulantes', 'postulantes.id', '=', 'pagos.postulante_id')
            ->when($filters['estado_inscripcion'] !== '', fn ($query) => $query->where('postulantes.estado_inscripcion', $filters['estado_inscripcion']))
            ->when($filters['carrera_id'] !== '', function ($query) use ($filters): void {
                $query->where(function ($builder) use ($filters): void {
                    $builder
                        ->where('postulantes.carrera_primera_opcion_id', $filters['carrera_id'])
                        ->orWhere('postulantes.carrera_segunda_opcion_id', $filters['carrera_id']);
                });
            })
            ->when($filters['gestion'] !== '' || $filters['grupo_id'] !== '', function ($query) use ($filters): void {
                $query->whereExists(function ($subQuery) use ($filters): void {
                    $subQuery->selectRaw('1')
                        ->from('grupo_postulantes')
                        ->join('grupos', 'grupos.id', '=', 'grupo_postulantes.grupo_id')
                        ->whereColumn('grupo_postulantes.postulante_id', 'pagos.postulante_id')
                        ->where('grupo_postulantes.estado', 'ACTIVO')
                        ->where('grupos.estado', 'ACTIVO')
                        ->when($filters['gestion'] !== '', fn ($builder) => $builder->where('grupos.gestion', $filters['gestion']))
                        ->when($filters['grupo_id'] !== '', fn ($builder) => $builder->where('grupos.id', $filters['grupo_id']));
                });
            });
    }

    protected function gruposQuery(array $filters)
    {
        return DB::table('grupos')
            ->when($filters['gestion'] !== '', fn ($query) => $query->where('grupos.gestion', $filters['gestion']))
            ->when($filters['grupo_id'] !== '', fn ($query) => $query->where('grupos.id', $filters['grupo_id']));
    }

    protected function docentesQuery(array $filters)
    {
        return DB::table('docentes')
            ->leftJoin('docente_asignaciones', 'docente_asignaciones.docente_id', '=', 'docentes.id')
            ->leftJoin('grupos', 'grupos.id', '=', 'docente_asignaciones.grupo_id')
            ->leftJoin('materias', 'materias.id', '=', 'docente_asignaciones.materia_id')
            ->when($filters['gestion'] !== '', fn ($query) => $query->where('grupos.gestion', $filters['gestion']))
            ->when($filters['grupo_id'] !== '', fn ($query) => $query->where('grupos.id', $filters['grupo_id']))
            ->when($filters['materia_id'] !== '', fn ($query) => $query->where('materias.id', $filters['materia_id']));
    }

    protected function materiasQuery(array $filters)
    {
        return DB::table('materias')
            ->when($filters['materia_id'] !== '', fn ($query) => $query->where('materias.id', $filters['materia_id']));
    }

    protected function evaluacionesQuery(array $filters)
    {
        return DB::table('evaluaciones')
            ->join('materias', 'materias.id', '=', 'evaluaciones.materia_id')
            ->when($filters['materia_id'] !== '', fn ($query) => $query->where('materias.id', $filters['materia_id']));
    }

    protected function resultadosQuery(array $filters)
    {
        return DB::table('resultados_admision')
            ->join('postulantes', 'postulantes.id', '=', 'resultados_admision.postulante_id')
            ->when($filters['estado_resultado'] !== '', fn ($query) => $query->where('resultados_admision.estado_resultado', $filters['estado_resultado']))
            ->when($filters['estado_inscripcion'] !== '', fn ($query) => $query->where('postulantes.estado_inscripcion', $filters['estado_inscripcion']))
            ->when($filters['carrera_id'] !== '', fn ($query) => $query->where('resultados_admision.carrera_asignada_id', $filters['carrera_id']))
            ->when($filters['gestion'] !== '' || $filters['grupo_id'] !== '', function ($query) use ($filters): void {
                $query->whereExists(function ($subQuery) use ($filters): void {
                    $subQuery->selectRaw('1')
                        ->from('grupo_postulantes')
                        ->join('grupos', 'grupos.id', '=', 'grupo_postulantes.grupo_id')
                        ->whereColumn('grupo_postulantes.postulante_id', 'resultados_admision.postulante_id')
                        ->where('grupo_postulantes.estado', 'ACTIVO')
                        ->where('grupos.estado', 'ACTIVO')
                        ->when($filters['gestion'] !== '', fn ($builder) => $builder->where('grupos.gestion', $filters['gestion']))
                        ->when($filters['grupo_id'] !== '', fn ($builder) => $builder->where('grupos.id', $filters['grupo_id']));
                });
            });
    }

    protected function cuposQuery(array $filters)
    {
        return DB::table('cupos_carrera')
            ->when($filters['carrera_id'] !== '', fn ($query) => $query->where('cupos_carrera.carrera_id', $filters['carrera_id']))
            ->when($filters['gestion'] !== '', fn ($query) => $query->whereIn('cupos_carrera.gestion', $this->gestionesCompatibles($filters['gestion'])));
    }

    protected function notasQuery(array $filters)
    {
        return DB::table('notas')
            ->join('postulantes', 'postulantes.id', '=', 'notas.postulante_id')
            ->join('evaluaciones', 'evaluaciones.id', '=', 'notas.evaluacion_id')
            ->when($filters['materia_id'] !== '', fn ($query) => $query->where('notas.materia_id', $filters['materia_id']))
            ->when($filters['estado_inscripcion'] !== '', fn ($query) => $query->where('postulantes.estado_inscripcion', $filters['estado_inscripcion']))
            ->when($filters['carrera_id'] !== '', function ($query) use ($filters): void {
                $query->where(function ($builder) use ($filters): void {
                    $builder
                        ->where('postulantes.carrera_primera_opcion_id', $filters['carrera_id'])
                        ->orWhere('postulantes.carrera_segunda_opcion_id', $filters['carrera_id']);
                });
            })
            ->when($filters['gestion'] !== '' || $filters['grupo_id'] !== '', function ($query) use ($filters): void {
                $query->whereExists(function ($subQuery) use ($filters): void {
                    $subQuery->selectRaw('1')
                        ->from('grupo_postulantes')
                        ->join('grupos', 'grupos.id', '=', 'grupo_postulantes.grupo_id')
                        ->whereColumn('grupo_postulantes.postulante_id', 'notas.postulante_id')
                        ->where('grupo_postulantes.estado', 'ACTIVO')
                        ->where('grupos.estado', 'ACTIVO')
                        ->when($filters['gestion'] !== '', fn ($builder) => $builder->where('grupos.gestion', $filters['gestion']))
                        ->when($filters['grupo_id'] !== '', fn ($builder) => $builder->where('grupos.id', $filters['grupo_id']));
                });
            });
    }

    protected function asistenciasQuery(array $filters)
    {
        return DB::table('asistencias_docentes')
            ->join('horarios', 'horarios.id', '=', 'asistencias_docentes.horario_id')
            ->join('grupos', 'grupos.id', '=', 'horarios.grupo_id')
            ->when($filters['gestion'] !== '', fn ($query) => $query->where('grupos.gestion', $filters['gestion']))
            ->when($filters['grupo_id'] !== '', fn ($query) => $query->where('grupos.id', $filters['grupo_id']))
            ->when($filters['materia_id'] !== '', fn ($query) => $query->where('horarios.materia_id', $filters['materia_id']));
    }

    protected function formatNumber($value): string
    {
        if ($value === null || $value === '') {
            return '0.00';
        }

        return number_format((float) $value, 2, '.', '');
    }

    protected function formatDate($value): string
    {
        if ($value === null || $value === '') {
            return 'Sin fecha';
        }

        return date('Y-m-d', strtotime((string) $value));
    }

    protected function formatDateTime($value): string
    {
        if ($value === null || $value === '') {
            return 'Sin fecha';
        }

        return date('Y-m-d H:i', strtotime((string) $value));
    }

    protected function formatTime($value): string
    {
        if ($value === null || $value === '') {
            return '00:00';
        }

        return substr((string) $value, 0, 5);
    }
}
