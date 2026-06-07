@extends('layouts.app')

@section('title', 'Autenticación | CUPCore')

@section('content')
    <x-page-title title="Autenticación" subtitle="CU1 Iniciar sesión / CU2 Cerrar sesión" />

    <x-card title="Formulario de edición">
        <p class="mb-4">Vista base de Autenticación para la estructura inicial del proyecto.</p>
            <p class='text-sm text-base-content/70'>Registro de referencia: {{ $id ?? 'pendiente' }}</p>
        <div class="flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-primary">Volver</a>
            <button type="button" class="btn btn-outline">Acción pendiente</button>
        </div>
    </x-card>
@endsection
