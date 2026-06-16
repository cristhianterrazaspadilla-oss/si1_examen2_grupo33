@extends('layouts.app')

@section('title', 'CU17 Consultar Bitacora del Sistema | CUPCore')

@section('content')
    {{-- CU17 Bitácora (auditoría): listado cronológico de acciones del sistema.
        - Incluye filtros por usuario, acción, módulo, rango de fechas y búsqueda libre.
        - Muestra IP registrada como parte del registro de auditoría institucional.
        - Usar para investigar acciones de usuarios y generar reportes de cumplimiento.
    --}}
    <div class="flex flex-col gap-4 xl:flex-row xl:items-start xl:justify-between">
        <x-page-title title="CU17 Consultar Bitacora del Sistema" subtitle="Consulta el historial de acciones realizadas por los usuarios." />
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('dashboard') }}" class="btn btn-primary w-full sm:w-auto">Volver al Dashboard</a>
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

    <x-card title="Filtros de consulta">
        <form method="GET" action="{{ route('gestion-academica-cup.bitacoras.index') }}" class="grid grid-cols-1 gap-4 sm:grid-cols-2 2xl:grid-cols-6">
            <label class="form-control">
                <span class="label-text">Usuario</span>
                <select name="usuario_id" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($usuarios as $usuario)
                        <option value="{{ $usuario->id }}" @selected($filters['usuario_id'] === (string) $usuario->id)>
                            {{ trim($usuario->apellido . ' ' . $usuario->nombre) !== '' ? trim($usuario->apellido . ' ' . $usuario->nombre) : $usuario->correo }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="form-control">
                <span class="label-text">Accion</span>
                <select name="accion" class="select select-bordered">
                    <option value="">Todas</option>
                    @foreach ($acciones as $accion)
                        <option value="{{ $accion }}" @selected($filters['accion'] === $accion)>{{ $accion }}</option>
                    @endforeach
                </select>
            </label>

            <label class="form-control">
                <span class="label-text">Modulo</span>
                <select name="modulo" class="select select-bordered">
                    <option value="">Todos</option>
                    @foreach ($modulos as $modulo)
                        <option value="{{ $modulo }}" @selected($filters['modulo'] === $modulo)>{{ $modulo }}</option>
                    @endforeach
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
                <input type="text" name="busqueda" value="{{ $filters['busqueda'] }}" class="input input-bordered" placeholder="Accion, descripcion, modulo, IP, usuario, correo o CI">
            </label>

            <div class="sm:col-span-2 2xl:col-span-6 flex flex-wrap gap-3">
                <button type="submit" class="btn btn-primary w-full sm:w-auto">Filtrar</button>
                <a href="{{ route('gestion-academica-cup.bitacoras.index') }}" class="btn btn-outline w-full sm:w-auto">Limpiar filtros</a>
            </div>
        </form>
    </x-card>

    @if ($bitacoras->count() === 0)
        <div class="alert alert-info">
            <span>No existen registros de bitacora para los filtros seleccionados.</span>
        </div>
    @else
        <x-card title="Listado cronologico de acciones">
            <div class="mb-3 text-xs text-slate-300/80">
                La IP corresponde a la direccion registrada para auditoria en el momento de la accion.
            </div>
            <div class="overflow-x-auto">
                <table class="table min-w-[1080px] text-sm">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Usuario</th>
                            <th>Rol</th>
                            <th>Accion</th>
                            <th>Modulo</th>
                            <th>IP</th>
                            <th>Descripcion resumida</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($bitacoras as $bitacora)
                            <tr>
                                <td>{{ \Illuminate\Support\Carbon::parse($bitacora->fecha)->format('Y-m-d H:i') }}</td>
                                <td>
                                    <div>{{ trim(($bitacora->usuario_nombre ?? '') . ' ' . ($bitacora->usuario_apellido ?? '')) !== '' ? trim(($bitacora->usuario_nombre ?? '') . ' ' . ($bitacora->usuario_apellido ?? '')) : 'Usuario no disponible' }}</div>
                                    @if (!empty($bitacora->usuario_correo))
                                        <div class="text-xs text-slate-300/80">{{ $bitacora->usuario_correo }}</div>
                                    @endif
                                </td>
                                <td class="text-slate-100">{{ $bitacora->rol_nombre ?: 'Sin rol' }}</td>
                                <td><span class="badge border border-sky-400/30 bg-sky-500/20 text-sky-100">{{ $bitacora->accion }}</span></td>
                                <td><span class="badge border border-blue-300/25 bg-slate-800/80 text-slate-100">{{ $bitacora->modulo ?: 'Sin modulo' }}</span></td>
                                <td>
                                    <div class="text-slate-100">{{ $bitacora->ip_address ?: 'Sin IP' }}</div>
                                    <div class="text-[11px] text-slate-400">IP de auditoria</div>
                                </td>
                                <td class="max-w-sm whitespace-normal break-words text-sm leading-7 text-slate-100">
                                    {{ \Illuminate\Support\Str::limit((string) ($bitacora->descripcion ?? 'Sin descripcion'), 120) }}
                                </td>
                                <td>
                                    <a href="{{ route('gestion-academica-cup.bitacoras.show', $bitacora->id) }}" class="btn btn-sm btn-outline whitespace-nowrap">Ver detalle</a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-4">
                {{ $bitacoras->links() }}
            </div>
        </x-card>
    @endif
@endsection
