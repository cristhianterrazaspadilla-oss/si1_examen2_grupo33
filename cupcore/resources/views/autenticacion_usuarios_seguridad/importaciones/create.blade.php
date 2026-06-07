@extends('layouts.app')

@section('title', 'Importaciones | CUPCore')

@section('content')
    <x-page-title title="Importaciones" subtitle="CU4 Importar Datos Masivos Excel/CSV" />

    <x-card title="Formulario de registro">
        <p class="mb-4">Vista base de Importaciones para la estructura inicial del proyecto.</p>

        <div class="flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-primary">Volver</a>
            <button type="button" class="btn btn-outline">Acción pendiente</button>
        </div>
    </x-card>
@endsection
