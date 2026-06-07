<?php

namespace App\Http\Controllers\ReportesDashboardComunicacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReporteController extends Controller
{
    // Controlador base del caso de uso: CU16 Generar Reportes y Dashboard Académico
    public function index(): View
    {
        return view('reportes_dashboard_comunicacion.reportes.index');
    }

    public function create(): View
    {
        return view('reportes_dashboard_comunicacion.reportes.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('reportes-dashboard-comunicacion.reportes.index');
    }

    public function show(string $id): View
    {
        return view('reportes_dashboard_comunicacion.reportes.show', compact('id'));
    }

    public function edit(string $id): View
    {
        return view('reportes_dashboard_comunicacion.reportes.edit', compact('id'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('reportes-dashboard-comunicacion.reportes.show', $id);
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('reportes-dashboard-comunicacion.reportes.index');
    }
}