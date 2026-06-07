@extends('layouts.app')

@section('title', 'Asignaciones Docentes | CUPCore')

@section('content')
    <x-page-title title="Asignaciones Docentes" subtitle="CU12 Gestionar Docentes y Asignaciones" />

    <x-card title="Formulario de registro">
        <p class="mb-4">Vista base de Asignaciones Docentes para la estructura inicial del proyecto.</p>

        <div class="flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-primary">Volver</a>
            <button type="button" class="btn btn-outline">Acción pendiente</button>
        </div>
    </x-card>
@endsection
