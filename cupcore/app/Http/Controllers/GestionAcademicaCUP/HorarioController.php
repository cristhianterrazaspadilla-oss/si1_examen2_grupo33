<?php

namespace App\Http\Controllers\GestionAcademicaCUP;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use App\Models\DocenteAsignacion;
use App\Models\Horario;
use App\Support\BitacoraHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Throwable;

class HorarioController extends Controller
{
    public function index(Request $request): View
    {
        $gestion = $request->string('gestion')->toString();
        $grupoId = $request->string('grupo_id')->toString();
        $docenteId = $request->string('docente_id')->toString();
        $aulaId = $request->string('aula_id')->toString();
        $dia = $request->string('dia_semana')->toString();
        $estado = $request->string('estado')->toString();

        $horarios = Horario::query()
            ->select('horarios.*')
            ->join('grupos', 'grupos.id', '=', 'horarios.grupo_id')
            ->with(['grupo', 'materia', 'docente', 'aula'])
            ->when($gestion !== '', fn ($query) => $query->where('grupos.gestion', $gestion))
            ->when($grupoId !== '', fn ($query) => $query->where('grupo_id', $grupoId))
            ->when($docenteId !== '', fn ($query) => $query->where('docente_id', $docenteId))
            ->when($aulaId !== '', fn ($query) => $query->where('aula_id', $aulaId))
            ->when($dia !== '', fn ($query) => $query->where('horarios.dia_semana', $dia))
            ->when($estado !== '', fn ($query) => $query->where('horarios.estado', $estado))
            ->orderByDesc('grupos.gestion')
            ->orderBy('horarios.dia_semana')
            ->orderBy('horarios.hora_inicio')
            ->paginate(10)
            ->withQueryString();

        return view('gestion_academica_cup.horarios.index', [
            'horarios' => $horarios,
            'gestion' => $gestion,
            'grupoId' => $grupoId,
            'docenteId' => $docenteId,
            'aulaId' => $aulaId,
            'dia' => $dia,
            'estado' => $estado,
            'gestionesAcademicas' => $this->gestionesAcademicas(),
            'diasSemana' => $this->diasSemana(),
            'grupos' => DB::table('grupos')->where('estado', 'ACTIVO')->orderByDesc('gestion')->orderBy('nombre')->get(),
            'docentes' => DB::table('docentes')->where('estado', 'ACTIVO')->orderBy('apellidos')->orderBy('nombres')->get(),
            'aulas' => DB::table('aulas')->where('estado', 'ACTIVO')->orderBy('nombre')->get(),
        ]);
    }

    public function create(): View
    {
        return view('gestion_academica_cup.horarios.create', [
            'asignaciones' => $this->asignacionesActivas(),
            'aulas' => $this->aulasActivas(),
            'gestionesAcademicas' => $this->gestionesAcademicas(),
            'diasSemana' => $this->diasSemana(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateHorarioRequest($request);
        $horaInicio = substr((string) $request->input('hora_inicio'), 0, 5);
        $horaFin = substr((string) $request->input('hora_fin'), 0, 5);

        if ($this->horaAMinutos($horaFin) <= $this->horaAMinutos($horaInicio)) {
            return back()
                ->withInput()
                ->withErrors(['hora_fin' => 'La hora de fin debe ser posterior a la hora de inicio.']);
        }

        $asignacion = $this->resolveAsignacionActiva((int) $validated['docente_asignacion_id']);

        $payload = $this->buildHorarioPayload($validated, $asignacion, $horaInicio, $horaFin);
        $this->validateHorarioDependencies($payload);
        $this->validateConflicts($payload);

        $horarioId = DB::table('horarios')->insertGetId([
            ...$this->persistableHorarioPayload($payload),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        BitacoraHelper::registrar(
            'CREAR_HORARIO',
            'Horarios',
            'Se creo horario para ' . ($asignacion->materia?->nombre ?? 'N/D') . ' en ' . ($asignacion->grupo?->nombre ?? 'N/D') . ', dia ' . $payload['dia_semana'] . ' de ' . $horaInicio . ' a ' . $horaFin . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.horarios.show', $horarioId)
            ->with('success', 'Horario creado correctamente.');
    }

    public function show(Horario $horario): View
    {
        $horario->load(['grupo', 'materia', 'docente', 'aula']);

        return view('gestion_academica_cup.horarios.show', [
            'horario' => $horario,
            'asignacionActual' => $this->findMatchingAsignacion($horario),
        ]);
    }

    public function edit(Horario $horario): View
    {
        $horario->load(['grupo', 'materia', 'docente', 'aula']);

        return view('gestion_academica_cup.horarios.edit', [
            'horario' => $horario,
            'asignaciones' => $this->asignacionesActivas($horario),
            'aulas' => $this->aulasActivas($horario->aula_id),
            'gestionesAcademicas' => $this->gestionesAcademicas(),
            'diasSemana' => $this->diasSemana(),
            'asignacionActual' => $this->findMatchingAsignacion($horario),
        ]);
    }

    public function update(Request $request, Horario $horario): RedirectResponse
    {
        $validated = $this->validateHorarioRequest($request);
        $horaInicio = substr((string) $request->input('hora_inicio'), 0, 5);
        $horaFin = substr((string) $request->input('hora_fin'), 0, 5);

        if ($this->horaAMinutos($horaFin) <= $this->horaAMinutos($horaInicio)) {
            return back()
                ->withInput()
                ->withErrors(['hora_fin' => 'La hora de fin debe ser posterior a la hora de inicio.']);
        }

        $asignacion = $this->resolveAsignacionActiva((int) $validated['docente_asignacion_id']);

        $payload = $this->buildHorarioPayload($validated, $asignacion, $horaInicio, $horaFin);
        $this->validateHorarioDependencies($payload);
        $this->validateConflicts($payload, $horario->id);

        DB::table('horarios')
            ->where('id', $horario->id)
            ->update([
                ...$this->persistableHorarioPayload($payload),
                'updated_at' => now(),
            ]);
        BitacoraHelper::registrar(
            'ACTUALIZAR_HORARIO',
            'Horarios',
            'Se actualizo horario para ' . ($asignacion->materia?->nombre ?? 'N/D') . ' en ' . ($asignacion->grupo?->nombre ?? 'N/D') . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.horarios.show', $horario)
            ->with('success', 'Horario actualizado correctamente.');
    }

    public function destroy(Horario $horario): RedirectResponse
    {
        try {
            $updated = DB::table('horarios')
                ->where('id', $horario->id)
                ->update([
                    'estado' => 'INACTIVO',
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            Log::error('Error real al desactivar horario', [
                'horario_id' => $horario->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('gestion-academica-cup.horarios.index')
                ->withErrors(['horario' => 'No se pudo desactivar el horario: ' . $exception->getMessage()]);
        }

        if ($updated !== 1) {
            return redirect()
                ->route('gestion-academica-cup.horarios.index')
                ->withErrors(['horario' => 'No se pudo desactivar el horario seleccionado.']);
        }

        BitacoraHelper::registrar(
            'DESACTIVAR_HORARIO',
            'Horarios',
            'Se desactivo horario ID ' . $horario->id . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.horarios.index')
            ->with('success', 'Horario desactivado correctamente.');
    }

    public function activar(Horario $horario): RedirectResponse
    {
        $payload = [
            'grupo_id' => $horario->grupo_id,
            'materia_id' => $horario->materia_id,
            'docente_id' => $horario->docente_id,
            'aula_id' => $horario->aula_id,
            'gestion_grupo' => $horario->grupo?->gestion,
            'dia_semana' => $horario->dia_semana,
            'hora_inicio' => $horario->hora_inicio,
            'hora_fin' => $horario->hora_fin,
            'estado' => 'ACTIVO',
        ];

        $this->validateHorarioDependencies($payload);
        $this->validateConflicts($payload, $horario->id);

        try {
            $updated = DB::table('horarios')
                ->where('id', $horario->id)
                ->update([
                    'estado' => 'ACTIVO',
                    'updated_at' => now(),
                ]);
        } catch (Throwable $exception) {
            Log::error('Error real al activar horario', [
                'horario_id' => $horario->id,
                'error' => $exception->getMessage(),
            ]);

            return redirect()
                ->route('gestion-academica-cup.horarios.show', $horario)
                ->withErrors(['horario' => 'No se pudo activar el horario: ' . $exception->getMessage()]);
        }

        if ($updated !== 1) {
            return redirect()
                ->route('gestion-academica-cup.horarios.show', $horario)
                ->withErrors(['horario' => 'No se pudo activar el horario seleccionado.']);
        }

        BitacoraHelper::registrar(
            'ACTIVAR_HORARIO',
            'Horarios',
            'Se activo horario ID ' . $horario->id . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.horarios.show', $horario)
            ->with('success', 'Horario activado correctamente.');
    }

    protected function validateHorarioRequest(Request $request): array
    {
        $validated = $request->validate([
            'docente_asignacion_id' => ['required', 'exists:docente_asignaciones,id'],
            'aula_id' => ['required', 'exists:aulas,id'],
            'dia_semana' => ['required', Rule::in($this->diasSemana())],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i'],
            'estado' => ['nullable', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);

        return $validated;
    }

    protected function resolveAsignacionActiva(int $asignacionId): DocenteAsignacion
    {
        $asignacion = DocenteAsignacion::query()
            ->with(['docente', 'grupo', 'materia'])
            ->findOrFail($asignacionId);

        if ($asignacion->estado !== 'ACTIVO') {
            throw ValidationException::withMessages([
                'docente_asignacion_id' => 'La asignacion seleccionada debe estar activa.',
            ]);
        }

        return $asignacion;
    }

    protected function buildHorarioPayload(array $validated, DocenteAsignacion $asignacion, string $horaInicio, string $horaFin): array
    {
        return [
            'grupo_id' => $asignacion->grupo_id,
            'materia_id' => $asignacion->materia_id,
            'docente_id' => $asignacion->docente_id,
            'aula_id' => (int) $validated['aula_id'],
            'gestion_grupo' => $asignacion->gestion,
            'dia_semana' => $validated['dia_semana'],
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'estado' => $validated['estado'] ?? 'ACTIVO',
        ];
    }

    protected function persistableHorarioPayload(array $payload): array
    {
        return collect($payload)
            ->except(['gestion_grupo'])
            ->all();
    }

    protected function validateHorarioDependencies(array $payload): void
    {
        $grupo = DB::table('grupos')->where('id', $payload['grupo_id'])->first();
        $materia = DB::table('materias')->where('id', $payload['materia_id'])->first();
        $docente = DB::table('docentes')->where('id', $payload['docente_id'])->first();
        $aula = DB::table('aulas')->where('id', $payload['aula_id'])->first();

        $errors = [];

        if (! $grupo || $grupo->estado !== 'ACTIVO') {
            $errors['docente_asignacion_id'] = 'Solo se pueden programar horarios para grupos activos.';
        } elseif (! $materia || $materia->estado !== 'ACTIVO') {
            $errors['docente_asignacion_id'] = 'Solo se pueden programar horarios para materias activas.';
        } elseif (! $docente || $docente->estado !== 'ACTIVO') {
            $errors['docente_asignacion_id'] = 'Solo se pueden programar horarios con docentes activos.';
        } elseif (! $aula || $aula->estado !== 'ACTIVO') {
            $errors['aula_id'] = 'Solo se pueden programar horarios en aulas activas.';
        } else {
            $asignacionValida = DB::table('docente_asignaciones')
                ->where('docente_id', $payload['docente_id'])
                ->where('grupo_id', $payload['grupo_id'])
                ->where('materia_id', $payload['materia_id'])
                ->where('gestion', $payload['gestion_grupo'])
                ->where('estado', 'ACTIVO')
                ->exists();

            if (! $asignacionValida) {
                $errors['docente_asignacion_id'] = 'No existe una asignacion docente activa para la combinacion seleccionada en esa gestion.';
            }
        }

        if ($errors !== []) {
            throw ValidationException::withMessages($errors);
        }
    }

    protected function validateConflicts(array $payload, ?int $ignoreHorarioId = null): void
    {
        if (($payload['estado'] ?? 'ACTIVO') !== 'ACTIVO') {
            return;
        }

        $horaInicio = $payload['hora_inicio'];
        $horaFin = $payload['hora_fin'];

        $resources = [
            'aula_id' => ['value' => $payload['aula_id'], 'message' => 'Ya existe un horario activo que cruza con el aula seleccionada.'],
            'docente_id' => ['value' => $payload['docente_id'], 'message' => 'Ya existe un horario activo que cruza con el docente seleccionado.'],
            'grupo_id' => ['value' => $payload['grupo_id'], 'message' => 'Ya existe un horario activo que cruza con el grupo seleccionado.'],
        ];

        foreach ($resources as $field => $resource) {
            $conflict = DB::table('horarios')
                ->join('grupos', 'grupos.id', '=', 'horarios.grupo_id')
                ->where($field, $resource['value'])
                ->where('grupos.gestion', $payload['gestion_grupo'])
                ->where('dia_semana', $payload['dia_semana'])
                ->where('horarios.estado', 'ACTIVO')
                ->where('horarios.hora_inicio', '<', $horaFin)
                ->where('horarios.hora_fin', '>', $horaInicio)
                ->when($ignoreHorarioId, fn ($query) => $query->where('horarios.id', '!=', $ignoreHorarioId))
                ->exists();

            if ($conflict) {
                throw ValidationException::withMessages([$field === 'aula_id' ? 'aula_id' : 'docente_asignacion_id' => $resource['message']]);
            }
        }
    }

    protected function asignacionesActivas(?Horario $horario = null)
    {
        $matching = $horario ? $this->findMatchingAsignacion($horario) : null;

        $query = DocenteAsignacion::query()
            ->with(['docente', 'grupo', 'materia'])
            ->where('estado', 'ACTIVO')
            ->whereHas('docente', fn ($subQuery) => $subQuery->where('estado', 'ACTIVO'))
            ->whereHas('grupo', fn ($subQuery) => $subQuery->where('estado', 'ACTIVO'))
            ->whereHas('materia', fn ($subQuery) => $subQuery->where('estado', 'ACTIVO'))
            ->orderByDesc('gestion')
            ->orderBy('grupo_id')
            ->orderBy('materia_id');

        $asignaciones = $query->get()->values();

        if ($matching && ! $asignaciones->contains('id', $matching->id)) {
            $asignaciones->push($matching);
        }

        return $asignaciones->unique('id')->values();
    }

    protected function aulasActivas(?int $currentAulaId = null)
    {
        return Aula::query()
            ->where(function ($query) use ($currentAulaId): void {
                $query->where('estado', 'ACTIVO');

                if ($currentAulaId) {
                    $query->orWhere('id', $currentAulaId);
                }
            })
            ->orderBy('nombre')
            ->get();
    }

    protected function findMatchingAsignacion(Horario $horario): ?DocenteAsignacion
    {
        return DocenteAsignacion::query()
            ->with(['docente', 'grupo', 'materia'])
            ->where('docente_id', $horario->docente_id)
            ->where('grupo_id', $horario->grupo_id)
            ->where('materia_id', $horario->materia_id)
            ->where('gestion', $horario->grupo?->gestion)
            ->orderByRaw("CASE WHEN estado = 'ACTIVO' THEN 0 ELSE 1 END")
            ->first();
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

    protected function diasSemana(): array
    {
        return ['LUNES', 'MARTES', 'MIERCOLES', 'JUEVES', 'VIERNES', 'SABADO'];
    }

    private function horaAMinutos(string $hora): int
    {
        [$horas, $minutos] = array_map('intval', explode(':', substr($hora, 0, 5)));

        return ($horas * 60) + $minutos;
    }
}
