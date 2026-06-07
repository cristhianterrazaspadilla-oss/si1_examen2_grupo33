<?php

namespace App\Http\Controllers\ReportesDashboardComunicacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    // Controlador base del caso de uso: CU16 Generar Reportes y Dashboard Académico
    public function index(): View
    {
        return view('reportes_dashboard_comunicacion.dashboard.index');
    }

    public function create(): View
    {
        return view('reportes_dashboard_comunicacion.dashboard.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('reportes-dashboard-comunicacion.dashboard.index');
    }

    public function show(string $id): View
    {
        return view('reportes_dashboard_comunicacion.dashboard.show', compact('id'));
    }

    public function edit(string $id): View
    {
        return view('reportes_dashboard_comunicacion.dashboard.edit', compact('id'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('reportes-dashboard-comunicacion.dashboard.show', $id);
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('reportes-dashboard-comunicacion.dashboard.index');
    }
}