<?php

namespace App\Http\Controllers\ReportesDashboardComunicacion;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificacionController extends Controller
{
    // Controlador base del caso de uso: CU19 Gestionar Notificaciones Internas
    public function index(): View
    {
        return view('reportes_dashboard_comunicacion.notificaciones.index');
    }

    public function create(): View
    {
        return view('reportes_dashboard_comunicacion.notificaciones.create');
    }

    public function store(Request $request): RedirectResponse
    {
        return redirect()->route('reportes-dashboard-comunicacion.notificaciones.index');
    }

    public function show(string $id): View
    {
        return view('reportes_dashboard_comunicacion.notificaciones.show', compact('id'));
    }

    public function edit(string $id): View
    {
        return view('reportes_dashboard_comunicacion.notificaciones.edit', compact('id'));
    }

    public function update(Request $request, string $id): RedirectResponse
    {
        return redirect()->route('reportes-dashboard-comunicacion.notificaciones.show', $id);
    }

    public function destroy(string $id): RedirectResponse
    {
        return redirect()->route('reportes-dashboard-comunicacion.notificaciones.index');
    }
}