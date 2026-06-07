@extends('layouts.app')

@section('title', 'Dashboard Académico | CUPCore')

@section('content')
    <x-page-title title="Dashboard Académico" subtitle="CU16 Generar Reportes y Dashboard Académico" />

    <x-card title="Formulario de registro">
        <p class="mb-4">Vista base de Dashboard Académico para la estructura inicial del proyecto.</p>

        <div class="flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-primary">Volver</a>
            <button type="button" class="btn btn-outline">Acción pendiente</button>
        </div>
    </x-card>
@endsection
