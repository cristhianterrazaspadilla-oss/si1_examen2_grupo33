@extends('layouts.app')

@section('title', 'Bitácoras | CUPCore')

@section('content')
    <x-page-title title="Bitácoras" subtitle="CU17 Consultar Bitácora del Sistema" />

    <x-card title="Formulario de registro">
        <p class="mb-4">Vista base de Bitácoras para la estructura inicial del proyecto.</p>

        <div class="flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-primary">Volver</a>
            <button type="button" class="btn btn-outline">Acción pendiente</button>
        </div>
    </x-card>
@endsection
