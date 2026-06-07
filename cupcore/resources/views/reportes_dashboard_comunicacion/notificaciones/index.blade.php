@extends('layouts.app')

@section('title', 'Notificaciones | CUPCore')

@section('content')
    <x-page-title title="Notificaciones" subtitle="CU19 Gestionar Notificaciones Internas" />

    <x-card title="Listado inicial">
        <p class="mb-4">Vista base de Notificaciones para la estructura inicial del proyecto.</p>

        <div class="flex gap-2">
            <a href="{{ url()->previous() }}" class="btn btn-primary">Volver</a>
            <button type="button" class="btn btn-outline">Acción pendiente</button>
        </div>
    </x-card>
@endsection
