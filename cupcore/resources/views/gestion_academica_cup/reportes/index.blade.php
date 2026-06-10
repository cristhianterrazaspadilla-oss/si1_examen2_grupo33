@extends('layouts.app')

@section('title', 'CU16 Reportes Academicos | CUPCore')

@section('content')
    <div class="flex items-center justify-between gap-4">
        <x-page-title title="CU16 Reportes Academicos" subtitle="Acceso rapido a reportes filtrables, historial y KPIs academicos del proceso de admision." />
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('gestion-academica-cup.reportes.consulta') }}" class="btn btn-primary">Reportes</a>
            <a href="{{ route('gestion-academica-cup.reportes.dashboard') }}" class="btn btn-outline">KPIs academicos</a>
            <a href="{{ route('gestion-academica-cup.reportes.historial') }}" class="btn btn-outline">Historial de reportes</a>
        </div>
    </div>

    <div class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        <x-card title="Alcance actual de CU16">
            <div class="space-y-3 text-sm text-base-content/80">
                <p>CU16 ya cuenta con KPIs academicos, reportes consultables en pantalla, exportacion CSV, Excel, vista imprimible/PDF e historial de exportaciones registradas en la tabla real <code>reportes</code>.</p>
                <p>Tambien incluye comandos por voz con interpretacion local y apoyo opcional desde Groq, siempre validado desde backend contra listas permitidas.</p>
            </div>
        </x-card>

        <x-card title="Acceso rapido">
            <div class="space-y-4">
                <p class="text-sm text-base-content/80">Usa reportes para filtrar datos, KPIs academicos para revisar indicadores agregados y el historial para auditar exportaciones CSV/PDF.</p>
                <div class="grid gap-3">
                    <a href="{{ route('gestion-academica-cup.reportes.consulta') }}" class="btn btn-primary w-full">Ir a reportes</a>
                    <a href="{{ route('gestion-academica-cup.reportes.dashboard') }}" class="btn btn-outline w-full">Ir a KPIs academicos</a>
                    <a href="{{ route('gestion-academica-cup.reportes.historial') }}" class="btn btn-outline w-full">Ir al historial de reportes</a>
                </div>
            </div>
        </x-card>
    </div>
@endsection
