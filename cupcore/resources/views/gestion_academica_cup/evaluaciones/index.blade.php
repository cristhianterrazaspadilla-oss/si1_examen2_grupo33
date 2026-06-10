@extends('layouts.app')

@section('title', 'Evaluaciones | CUPCore')

@section('content')
    <x-page-title title="Evaluaciones" subtitle="CU9 Administrar Materias y Evaluaciones" />

    @if (session('success'))
        <div class="mb-6">
            <x-alert type="success" :message="session('success')" />
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6">
            <x-alert type="error" :message="session('error')" />
        </div>
    @endif

    @if (session('warning'))
        <div class="mb-6">
            <x-alert type="warning" :message="session('warning')" />
        </div>
    @endif

    @if (session('info'))
        <div class="mb-6">
            <x-alert type="info" :message="session('info')" />
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 space-y-2">
            @foreach ($errors->all() as $error)
                <x-alert type="error" :message="$error" />
            @endforeach
        </div>
    @endif

    <x-card title="Listado inicial">
        <p class="mb-4">Vista base de Evaluaciones para la estructura inicial del proyecto.</p>

        <div class="flex flex-wrap gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-primary w-full sm:w-auto">Volver</a>
            <button type="button" class="btn btn-outline w-full sm:w-auto">Acción pendiente</button>
        </div>
    </x-card>
@endsection
