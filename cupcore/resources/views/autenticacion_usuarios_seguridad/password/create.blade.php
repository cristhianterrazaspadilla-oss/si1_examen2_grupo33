@extends('layouts.app')

@section('title', 'Recuperación de Contraseña | CUPCore')

@section('content')
    <x-page-title title="Recuperación de Contraseña" subtitle="CU18 Recuperar Contraseña" />

    <x-card title="Formulario de registro">
        <p class="mb-4">Vista base de Recuperación de Contraseña para la estructura inicial del proyecto.</p>

        <div class="flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-primary">Volver</a>
            <button type="button" class="btn btn-outline">Acción pendiente</button>
        </div>
    </x-card>
@endsection
