<?php

namespace App\Http\Controllers\GestionPostulantesAdmision;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use App\Models\Postulante;
use App\Models\User;
use App\Support\BitacoraHelper;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Paquete: Gestión de Postulantes y Admisión
 * Caso de Uso: CU5 (Registrar Postulantes / Inscripción)
 * 
 * Gestiona el pre-registro e información demográfica de los postulantes.
 * Asocia postulantes a carreras de primera y segunda opción para el proceso de selección.
 */
class PostulanteController extends Controller
{
    // Controlador del caso de uso: CU5 Gestionar Inscripción de Postulantes
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));
        $estadoInscripcion = $request->string('estado_inscripcion')->toString();
        $estadoAdmision = $request->string('estado_admision')->toString();

        $postulantes = Postulante::query()
            ->with(['usuario', 'carreraPrimeraOpcion', 'carreraSegundaOpcion'])
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('ci', 'like', "%{$search}%")
                        ->orWhere('nombres', 'like', "%{$search}%")
                        ->orWhere('apellidos', 'like', "%{$search}%")
                        ->orWhere('correo', 'like', "%{$search}%");
                });
            })
            ->when($estadoInscripcion !== '', fn ($query) => $query->where('estado_inscripcion', $estadoInscripcion))
            ->when($estadoAdmision !== '', fn ($query) => $query->where('estado_admision', $estadoAdmision))
            ->orderBy('apellidos')
            ->orderBy('nombres')
            ->paginate(10)
            ->withQueryString();

        return view('gestion_postulantes_admision.postulantes.index', [
            'postulantes' => $postulantes,
            'search' => $search,
            'estadoInscripcion' => $estadoInscripcion,
            'estadoAdmision' => $estadoAdmision,
        ]);
    }

    public function create(): View
    {
        return view('gestion_postulantes_admision.postulantes.create', [
            'carreras' => $this->getCarrerasActivas(),
            'usuarios' => $this->getUsuariosPostulanteDisponibles(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validatePostulante($request);
        $validated['estado_inscripcion'] = 'PRE_REGISTRADO';
        $validated['estado_admision'] = 'PENDIENTE';

        $postulante = Postulante::create($validated);
        BitacoraHelper::registrar(
            'REGISTRAR_POSTULANTE',
            'Postulantes',
            'Se registro el postulante CI ' . $postulante->ci . '.'
        );

        return redirect()
            ->route('gestion-postulantes-admision.postulantes.show', $postulante)
            ->with('success', 'Pre-registro de postulante creado correctamente.');
    }

    public function show(Postulante $postulante): View
    {
        $postulante->load(['usuario.rol', 'carreraPrimeraOpcion', 'carreraSegundaOpcion']);

        return view('gestion_postulantes_admision.postulantes.show', compact('postulante'));
    }

    public function edit(Postulante $postulante): View
    {
        return view('gestion_postulantes_admision.postulantes.edit', [
            'postulante' => $postulante,
            'carreras' => $this->getCarrerasActivas(),
            'usuarios' => $this->getUsuariosPostulanteDisponibles($postulante),
        ]);
    }

    public function update(Request $request, Postulante $postulante): RedirectResponse
    {
        $validated = $this->validatePostulante($request, $postulante);
        $postulante->update($validated);
        BitacoraHelper::registrar(
            'ACTUALIZAR_POSTULANTE',
            'Postulantes',
            'Se actualizo el postulante CI ' . $postulante->ci . '.'
        );

        return redirect()
            ->route('gestion-postulantes-admision.postulantes.show', $postulante)
            ->with('success', 'Pre-registro de postulante actualizado correctamente.');
    }

    public function destroy(Postulante $postulante): RedirectResponse
    {
        $postulante->update(['estado_inscripcion' => 'OBSERVADO']);
        BitacoraHelper::registrar(
            'DESACTIVAR_POSTULANTE',
            'Postulantes',
            'Se desactivo el postulante CI ' . $postulante->ci . '.'
        );

        return redirect()
            ->route('gestion-postulantes-admision.postulantes.index')
            ->with('success', 'Postulante observado correctamente.');
    }

    protected function validatePostulante(Request $request, ?Postulante $postulante = null): array
    {
        $postulanteId = $postulante?->id;

        return $request->validate([
            'usuario_id' => [
                'nullable',
                'exists:usuarios,id',
                Rule::unique('postulantes', 'usuario_id')->ignore($postulanteId),
                function (string $attribute, mixed $value, \Closure $fail) use ($postulante): void {
                    if (blank($value)) {
                        return;
                    }

                    $usuario = User::with('rol')->find($value);

                    if (! $usuario || $usuario->estado !== 'ACTIVO') {
                        $fail('El usuario seleccionado no está activo.');
                        return;
                    }

                    if (optional($usuario->rol)->nombre !== 'Postulante') {
                        $fail('El usuario seleccionado debe tener rol Postulante.');
                        return;
                    }

                    if ($usuario->postulante && $usuario->postulante->id !== $postulante?->id) {
                        $fail('El usuario seleccionado ya está asociado a otro postulante.');
                    }
                },
            ],
            'ci' => ['required', 'string', 'max:20', Rule::unique('postulantes', 'ci')->ignore($postulanteId)],
            'nombres' => ['required', 'string', 'max:100'],
            'apellidos' => ['required', 'string', 'max:100'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', Rule::in(['Masculino', 'Femenino', 'Otro'])],
            'tipo_sangre' => ['nullable', 'string', 'max:5'],
            'direccion' => ['nullable', 'string'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'correo' => ['nullable', 'email', 'max:150', Rule::unique('postulantes', 'correo')->ignore($postulanteId)],
            'colegio_procedencia' => ['nullable', 'string', 'max:150'],
            'ciudad' => ['nullable', 'string', 'max:100'],
            'carrera_primera_opcion_id' => ['nullable', 'exists:carreras,id'],
            'carrera_segunda_opcion_id' => ['nullable', 'exists:carreras,id', 'different:carrera_primera_opcion_id'],
            'titulo_bachiller' => ['nullable', 'boolean'],
        ]);
    }

    protected function getCarrerasActivas()
    {
        return Carrera::query()
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();
    }

    protected function getUsuariosPostulanteDisponibles(?Postulante $postulante = null)
    {
        return User::query()
            ->with('rol')
            ->where('estado', 'ACTIVO')
            ->whereHas('rol', fn ($query) => $query->where('nombre', 'Postulante'))
            ->where(function ($query) use ($postulante): void {
                $query->whereDoesntHave('postulante');

                if ($postulante?->usuario_id) {
                    $query->orWhere('id', $postulante->usuario_id);
                }
            })
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();
    }
}
