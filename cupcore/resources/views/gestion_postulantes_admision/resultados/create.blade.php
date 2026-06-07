@extends('layouts.app')

@section('title', 'Resultados de Admisión | CUPCore')

@section('content')
    <x-page-title title="Resultados de Admisión" subtitle="CU15 Gestionar Resultados de Admisión" />

    <x-card title="Formulario de registro">
        <p class="mb-4">Vista base de Resultados de Admisión para la estructura inicial del proyecto.</p>

        <div class="flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-primary">Volver</a>
            <button type="button" class="btn btn-outline">Acción pendiente</button>
        </div>
    </x-card>
@endsection
