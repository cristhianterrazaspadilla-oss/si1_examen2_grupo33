@extends('layouts.app')

@section('title', 'Notas | CUPCore')

@section('content')
    <x-page-title title="Notas" subtitle="CU14 Gestionar Notas y Seguimiento Académico" />

    <x-card title="Listado inicial">
        <p class="mb-4">Vista base de Notas para la estructura inicial del proyecto.</p>

        <div class="flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-primary">Volver</a>
            <button type="button" class="btn btn-outline">Acción pendiente</button>
        </div>
    </x-card>
@endsection
