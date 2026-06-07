<?php

namespace App\Http\Controllers\GestionAcademicaCUP;

use App\Http\Controllers\Controller;
use App\Models\AsistenciaDocente;
use App\Models\Horario;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class AsistenciaDocenteController extends Controller
{
    public function index(Request $request): View
    {
        $fecha = $request->string('fecha')->toString();
        $docenteId = $request->string('docente_id')->toString();
        $grupoId = $request->string('grupo_id')->toString();
        $materiaId = $request->string('materia_id')->toString();
        $aulaId = $request->string('aula_id')->toString();
        $estadoAsistencia = $request->string('estado_asistencia')->toString();
        $gestion = $request->string('gestion')->toString();

        $asistencias = AsistenciaDocente::query()
            ->select('asistencias_docentes.*')
            ->join('horarios', 'horarios.id', '=', 'asistencias_docentes.horario_id')
            ->join('grupos', 'grupos.id', '=', 'horarios.grupo_id')
            ->with(['docente', 'horario.grupo', 'horario.materia', 'horario.aula'])
            ->when($fecha !== '', fn ($query) => $query->whereDate('asistencias_docentes.fecha', $fecha))
            ->when($docenteId !== '', fn ($query) => $query->where('asistencias_docentes.docente_id', $docenteId))
            ->when($grupoId !== '', fn ($query) => $query->where('horarios.grupo_id', $grupoId))
            ->when($materiaId !== '', fn ($query) => $query->where('horarios.materia_id', $materiaId))
            ->when($aulaId !== '', fn ($query) => $query->where('horarios.aula_id', $aulaId))
            ->when($estadoAsistencia !== '', fn ($query) => $query->where('asistencias_docentes.estado_asistencia', $estadoAsistencia))
            ->when($gestion !== '', fn ($query) => $query->where('grupos.gestion', $gestion))
            ->orderByDesc('asistencias_docentes.fecha')
            ->orderByDesc('grupos.gestion')
            ->orderBy('horarios.dia_semana')
            ->orderBy('horarios.hora_inicio')
            ->paginate(10)
            ->withQueryString();

        return view('gestion_academica_cup.asistencias_docentes.index', [
            'asistencias' => $asistencias,
            'fecha' => $fecha,
            'docenteId' => $docenteId,
            'grupoId' => $grupoId,
            'materiaId' => $materiaId,
            'aulaId' => $aulaId,
            'estadoAsistencia' => $estadoAsistencia,
            'gestion' => $gestion,
            'estadosAsistencia' => $this->estadosAsistencia(),
            'gestionesAcademicas' => $this->gestionesAcademicas(),
            'docentes' => DB::table('docentes')->where('estado', 'ACTIVO')->orderBy('apellidos')->orderBy('nombres')->get(),
            'grupos' => DB::table('grupos')->where('estado', 'ACTIVO')->orderByDesc('gestion')->orderBy('nombre')->get(),
            'materias' => DB::table('materias')->where('estado', 'ACTIVO')->orderBy('nombre')->get(),
            'aulas' => DB::table('aulas')->where('estado', 'ACTIVO')->orderBy('nombre')->get(),
            'totalAsistencias' => AsistenciaDocente::count(),
            'presentesCount' => AsistenciaDocente::where('estado_asistencia', 'PRESENTE')->count(),
            'ausentesCount' => AsistenciaDocente::where('estado_asistencia', 'AUSENTE')->count(),
            'retrasosCount' => AsistenciaDocente::where('estado_asistencia', 'RETRASO')->count(),
            'justificadasCount' => AsistenciaDocente::where('estado_asistencia', 'JUSTIFICADO')->count(),
        ]);
    }

    public function create(): View
    {
        return view('gestion_academica_cup.asistencias_docentes.create', [
            'horarios' => $this->horariosDisponibles(),
            'estadosAsistencia' => $this->estadosAsistencia(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateAsistenciaRequest($request);
        $horario = $this->resolveHorarioValido((int) $validated['horario_id']);
        $this->ensureNoDuplicate($horario->docente_id, $horario->id, $validated['fecha']);

        $asistenciaId = DB::table('asistencias_docentes')->insertGetId([
            'horario_id' => $horario->id,
            'docente_id' => $horario->docente_id,
            'fecha' => $validated['fecha'],
            'hora_registro' => $validated['hora_registro'] ?? null,
            'estado_asistencia' => $validated['estado_asistencia'],
            'observacion' => $validated['observacion'] ?? null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()
            ->route('gestion-academica-cup.asistencias-docentes.show', $asistenciaId)
            ->with('success', 'Asistencia registrada correctamente.');
    }

    public function show(AsistenciaDocente $asistenciaDocente): View
    {
        $asistenciaDocente->load(['docente', 'horario.grupo', 'horario.materia', 'horario.aula']);

        return view('gestion_academica_cup.asistencias_docentes.show', [
            'asistencia' => $asistenciaDocente,
        ]);
    }

    public function edit(AsistenciaDocente $asistenciaDocente): View
    {
        $asistenciaDocente->load(['horario.grupo', 'horario.materia', 'horario.docente', 'horario.aula']);

        return view('gestion_academica_cup.asistencias_docentes.edit', [
            'asistencia' => $asistenciaDocente,
            'horarios' => $this->horariosDisponibles($asistenciaDocente->horario_id),
            'estadosAsistencia' => $this->estadosAsistencia(),
        ]);
    }

    public function update(Request $request, AsistenciaDocente $asistenciaDocente): RedirectResponse
    {
        $validated = $this->validateAsistenciaRequest($request);
        $horario = $this->resolveHorarioValido((int) $validated['horario_id']);
        $this->ensureNoDuplicate($horario->docente_id, $horario->id, $validated['fecha'], $asistenciaDocente->id);

        DB::table('asistencias_docentes')
            ->where('id', $asistenciaDocente->id)
            ->update([
                'horario_id' => $horario->id,
                'docente_id' => $horario->docente_id,
                'fecha' => $validated['fecha'],
                'hora_registro' => $validated['hora_registro'] ?? null,
                'estado_asistencia' => $validated['estado_asistencia'],
                'observacion' => $validated['observacion'] ?? null,
                'updated_at' => now(),
            ]);

        return redirect()
            ->route('gestion-academica-cup.asistencias-docentes.show', $asistenciaDocente)
            ->with('success', 'Asistencia actualizada correctamente.');
    }

    protected function validateAsistenciaRequest(Request $request): array
    {
        return $request->validate([
            'fecha' => ['required', 'date'],
            'horario_id' => ['required', 'exists:horarios,id'],
            'estado_asistencia' => ['required', Rule::in($this->estadosAsistencia())],
            'hora_registro' => ['nullable', 'date_format:H:i'],
            'observacion' => ['nullable', 'string'],
        ]);
    }

    protected function resolveHorarioValido(int $horarioId): Horario
    {
        $horario = Horario::query()
            ->with(['docente', 'grupo', 'materia', 'aula'])
            ->findOrFail($horarioId);

        $errors = [];

        if ($horario->estado !== 'ACTIVO') {
            $errors['horario_id'] = 'Solo se pueden registrar asistencias sobre horarios activos.';
        } elseif (! $horario->docente || $horario->docente->estado !== 'ACTIVO') {
            $errors['horario_id'] = 'El docente del horario seleccionado esta inactivo.';
        } elseif (! $horario->grupo || $horario->grupo->estado !== 'ACTIVO') {
            $errors['horario_id'] = 'El grupo del horario seleccionado esta inactivo.';
        } elseif (! $horario->materia || $horario->materia->estado !== 'ACTIVO') {
            $errors['horario_id'] = 'La materia del horario seleccionado esta inactiva.';
        } elseif (! $horario->aula || $horario->aula->estado !== 'ACTIVO') {
            $errors['horario_id'] = 'El aula del horario seleccionado esta inactiva.';
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }

        return $horario;
    }

    protected function ensureNoDuplicate(int $docenteId, int $horarioId, string $fecha, ?int $ignoreId = null): void
    {
        $exists = DB::table('asistencias_docentes')
            ->where('docente_id', $docenteId)
            ->where('horario_id', $horarioId)
            ->whereDate('fecha', $fecha)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists();

        if ($exists) {
            throw ValidationException::withMessages([
                'horario_id' => 'Ya existe una asistencia registrada para el mismo docente, horario y fecha.',
            ]);
        }
    }

    protected function horariosDisponibles(?int $currentHorarioId = null)
    {
        return Horario::query()
            ->with(['docente', 'grupo', 'materia', 'aula'])
            ->where(function ($query) use ($currentHorarioId): void {
                $query->where('estado', 'ACTIVO');

                if ($currentHorarioId) {
                    $query->orWhere('id', $currentHorarioId);
                }
            })
            ->orderByDesc(
                DB::table('grupos')
                    ->select('gestion')
                    ->whereColumn('grupos.id', 'horarios.grupo_id')
                    ->limit(1)
            )
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get()
            ->filter(function (Horario $horario): bool {
                return $horario->docente?->estado === 'ACTIVO'
                    && $horario->grupo?->estado === 'ACTIVO'
                    && $horario->materia?->estado === 'ACTIVO'
                    && $horario->aula?->estado === 'ACTIVO';
            })
            ->values();
    }

    protected function estadosAsistencia(): array
    {
        return ['PRESENTE', 'AUSENTE', 'RETRASO', 'JUSTIFICADO'];
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
}
