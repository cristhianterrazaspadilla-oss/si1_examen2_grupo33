@extends('layouts.app')

@section('title', 'Evaluaciones | CUPCore')

@section('content')
    <x-page-title title="Evaluaciones" subtitle="CU9 Administrar Materias y Evaluaciones" />

    <x-card title="Detalle de registro">
        <p class="mb-4">Vista base de Evaluaciones para la estructura inicial del proyecto.</p>
            <p class='text-sm text-base-content/70'>Registro de referencia: {{ $id ?? 'pendiente' }}</p>
        <div class="flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-primary">Volver</a>
            <button type="button" class="btn btn-outline">Acción pendiente</button>
        </div>
    </x-card>
@endsection
