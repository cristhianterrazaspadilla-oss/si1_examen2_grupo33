@extends('layouts.app')

@section('title', 'Historial de reportes generados | CUPCore')

@section('content')
    <div class="flex flex-wrap items-center justify-between gap-4">
        <x-page-title title="Historial de reportes generados" subtitle="Exportaciones CSV y vistas imprimibles generadas desde CU16." />
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('gestion-academica-cup.reportes.consulta') }}" class="btn btn-outline">Reportes</a>
            <a href="{{ route('gestion-academica-cup.reportes.dashboard') }}" class="btn btn-primary">KPIs academicos</a>
        </div>
    </div>

    @if ($errors->any())
        <div class="alert alert-error">
            <div class="space-y-1">
                @foreach ($errors->all() as $error)
                    <p>{{ $error }}</p>
                @endforeach
            </div>
        </div>
    @endif

    <div class="alert alert-info">
        <span>El historial registra las exportaciones realizadas. Los archivos CSV, EXCEL y la vista imprimible/PDF se generan al momento de la descarga o apertura.</span>
    </div>

    <x-card title="Filtros del historial">
        <form method="GET" action="{{ route('gestion-academica-cup.reportes.historial') }}" class="grid gap-4 md:grid-cols-2 xl:grid-cols-5">
            <label class="form-control">
                <span class="label-text">Tipo de reporte</span>
                <select name="tipo_reporte" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($reportTypes as $key => $reportType)
                        <option value="{{ $key }}" @selected($filters['tipo_reporte'] === $key)>{{ $reportType['label'] }}</option>
                    @endforeach
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Formato</span>
                <select name="formato" class="select select-bordered">
                    <option value="">Todos</option>
                    <option value="CSV" @selected($filters['formato'] === 'CSV')>CSV</option>
                    <option value="EXCEL" @selected($filters['formato'] === 'EXCEL')>EXCEL</option>
                    <option value="PDF" @selected($filters['formato'] === 'PDF')>PDF</option>
                </select>
            </label>
            <label class="form-control">
                <span class="label-text">Fecha desde</span>
                <input type="date" name="fecha_desde" value="{{ $filters['fecha_desde'] }}" class="input input-bordered">
            </label>
            <label class="form-control">
                <span class="label-text">Fecha hasta</span>
                <input type="date" name="fecha_hasta" value="{{ $filters['fecha_hasta'] }}" class="input input-bordered">
            </label>
            <label class="form-control">
                <span class="label-text">Busqueda</span>
                <input type="text" name="busqueda" value="{{ $filters['busqueda'] }}" class="input input-bordered" placeholder="Usuario, correo, ruta o tipo">
            </label>
            <div class="xl:col-span-5 flex flex-wrap gap-3">
                <button type="submit" class="btn btn-primary">Aplicar filtros</button>
                <a href="{{ route('gestion-academica-cup.reportes.historial') }}" class="btn btn-outline">Limpiar</a>
            </div>
        </form>
    </x-card>

    @if ($historial->count() === 0)
        <div class="alert">
            <span>No existen reportes generados todavia.</span>
        </div>
    @else
        <x-card title="Registros del historial">
            <div class="overflow-x-auto">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo reporte</th>
                            <th>Formato</th>
                            <th>Usuario</th>
                            <th>Filtros</th>
                            <th>Ruta logica</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($historial as $item)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($item->fecha_generacion)->format('Y-m-d H:i') }}</td>
                                <td>{{ $reportTypes[$item->tipo_reporte]['label'] ?? $item->tipo_reporte }}</td>
                                <td>
                                    <span class="badge {{ $item->formato === 'CSV' ? 'badge-primary' : ($item->formato === 'EXCEL' ? 'badge-accent' : 'badge-secondary') }}">
                                        {{ $item->formato }}
                                    </span>
                                </td>
                                <td>
                                    <div>{{ trim((string) $item->usuario) !== '' ? $item->usuario : 'Sin usuario visible' }}</div>
                                    @if ($item->usuario_correo)
                                        <div class="text-xs text-base-content/70">{{ $item->usuario_correo }}</div>
                                    @endif
                                </td>
                                <td class="min-w-[220px]">
                                    <div class="flex flex-col gap-2 whitespace-normal break-words leading-relaxed">
                                        @forelse ($item->filtros_resumen as $filtro)
                                            <div class="rounded-xl border border-base-300/60 bg-base-200/60 px-3 py-2 text-sm text-base-content/80">
                                                {{ $filtro }}
                                            </div>
                                        @empty
                                            <div class="rounded-xl border border-base-300/60 bg-base-200/60 px-3 py-2 text-sm text-base-content/70">
                                                Sin filtros
                                            </div>
                                        @endforelse
                                    </div>
                                </td>
                                <td class="min-w-[220px] break-all text-sm">{{ $item->ruta_archivo ?: 'Sin ruta' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $historial->links() }}
            </div>
        </x-card>
    @endif
@endsection
