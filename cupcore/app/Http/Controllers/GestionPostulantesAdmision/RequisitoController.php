<?php

namespace App\Http\Controllers\GestionPostulantesAdmision;

use App\Http\Controllers\Controller;
use App\Models\Requisito;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RequisitoController extends Controller
{
    // Controlador del caso de uso: CU6 Gestionar Requisitos de Admisión
    public function index(Request $request): View
    {
        $search = trim((string) $request->string('search'));

        $requisitos = Requisito::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($subQuery) use ($search): void {
                    $subQuery->where('nombre', 'like', "%{$search}%")
                        ->orWhere('descripcion', 'like', "%{$search}%");
                });
            })
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        return view('gestion_postulantes_admision.requisitos.index', compact('requisitos', 'search'));
    }

    public function create(): View
    {
        return view('gestion_postulantes_admision.requisitos.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $this->validateRequisito($request);
        $validated['obligatorio'] = (bool) ($validated['obligatorio'] ?? false);

        $requisito = Requisito::create($validated);

        return redirect()
            ->route('gestion-postulantes-admision.requisitos.show', $requisito)
            ->with('success', 'Requisito creado correctamente.');
    }

    public function show(Requisito $requisito): View
    {
        $requisito->loadCount('postulanteRequisitos');

        return view('gestion_postulantes_admision.requisitos.show', compact('requisito'));
    }

    public function edit(Requisito $requisito): View
    {
        return view('gestion_postulantes_admision.requisitos.edit', compact('requisito'));
    }

    public function update(Request $request, Requisito $requisito): RedirectResponse
    {
        $validated = $this->validateRequisito($request, $requisito);
        $validated['obligatorio'] = (bool) ($validated['obligatorio'] ?? false);

        $requisito->update($validated);

        return redirect()
            ->route('gestion-postulantes-admision.requisitos.show', $requisito)
            ->with('success', 'Requisito actualizado correctamente.');
    }

    public function destroy(Requisito $requisito): RedirectResponse
    {
        $requisito->update(['estado' => 'INACTIVO']);

        return redirect()
            ->route('gestion-postulantes-admision.requisitos.index')
            ->with('success', 'Requisito desactivado correctamente.');
    }

    protected function validateRequisito(Request $request, ?Requisito $requisito = null): array
    {
        return $request->validate([
            'nombre' => ['required', 'string', 'max:150', Rule::unique('requisitos', 'nombre')->ignore($requisito?->id)],
            'descripcion' => ['nullable', 'string'],
            'obligatorio' => ['nullable', 'boolean'],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ]);
    }
}
