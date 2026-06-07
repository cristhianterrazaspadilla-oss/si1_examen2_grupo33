<?php

namespace App\Http\Controllers\ReportesDashboardComunicacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BitacoraController extends Controller
{
    // Controlador base del caso de uso: CU17 Consultar Bitácora del Sistema
    public function index(): View
    {
        return view('reportes_dashboard_comunicacion.bitacoras.index');
    }

    public function create(): View
    {
        return view('reportes_dashboard_comunicacion.bitacoras.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('reportes-dashboard-comunicacion.bitacoras.index');
    }

    public function show(string $id): View
    {
        return view('reportes_dashboard_comunicacion.bitacoras.show', compact('id'));
    }

    public function edit(string $id): View
    {
        return view('reportes_dashboard_comunicacion.bitacoras.edit', compact('id'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('reportes-dashboard-comunicacion.bitacoras.show', $id);
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('reportes-dashboard-comunicacion.bitacoras.index');
    }
}