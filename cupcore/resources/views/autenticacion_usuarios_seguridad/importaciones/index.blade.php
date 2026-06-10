@extends('layouts.app')

@section('title', 'CU4 Importar Datos Masivos Excel/CSV')

@section('content')
    <x-page-title title="CU4 Importar Datos Masivos Excel/CSV" subtitle="Carga masiva de usuarios desde archivos Excel o CSV." />

    @if (session('success'))
        <div class="mb-6">
            <x-alert
                type="success"
                :message="session('success').' Usuarios importados correctamente: '.session('imported_count', 0).'.'"
            />
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-error mb-6">
            <div>
                <p class="font-semibold">Se encontraron errores de validacion.</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    @if (session('import_errors'))
        <div class="alert alert-warning mb-6">
            <div>
                <p class="font-semibold">La importacion finalizo con observaciones.</p>
                <ul class="mt-2 list-disc pl-5 text-sm">
                    @foreach (session('import_errors', []) as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 xl:grid-cols-[1.08fr_0.92fr] gap-6">
        <x-card title="Cargar archivo" class="h-full">
            <form method="POST" action="{{ route('autenticacion-usuarios-seguridad.importaciones.store') }}" enctype="multipart/form-data" class="space-y-6">
                @csrf

                <div class="rounded-[1.4rem] border border-blue-300/12 bg-slate-950/35 p-5">
                    <label class="form-control">
                        <span class="label-text">Archivo Excel o CSV</span>
                        <input type="file" name="archivo" class="file-input file-input-bordered mt-2 w-full" accept=".xlsx,.xls,.csv" required>
                        <span class="label-text-alt mt-3 block">Formatos permitidos: `.xlsx`, `.xls`, `.csv`. Tamano maximo: 5 MB.</span>
                    </label>
                </div>

                <div class="alert alert-info border border-cyan-400/18 bg-cyan-500/10 text-cyan-100 shadow-none">
                    <div>
                        <p class="font-semibold">Clave inicial para usuarios importados</p>
                        <p class="mt-2 text-sm leading-7">La contrasena por defecto para los usuarios importados sera <strong>12345678</strong>.</p>
                    </div>
                </div>

                <div class="rounded-[1.4rem] border border-blue-300/12 bg-slate-950/35 p-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-200/75">Recomendaciones</p>
                    <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-7 text-slate-300">
                         <li>Verifica que la primera fila contenga los encabezados exactos.</li>
                         <li>Usa roles existentes en la tabla `roles` para evitar observaciones.</li>
                         <li>Si una fila falla, el resto del archivo seguira procesandose.</li>
                    </ul>
                </div>

                <div class="flex flex-wrap gap-3">
                    <button type="submit" class="btn btn-primary min-w-48">Importar archivo</button>
                </div>
            </form>
        </x-card>

        <x-card title="Guia del formato esperado" class="h-full">
            <div class="space-y-5 text-sm">
                <p class="leading-7 text-slate-300">
                    La primera fila del archivo debe contener exactamente estos encabezados y respetar el orden recomendado.
                </p>

                <div class="overflow-x-auto rounded-[1.4rem] border border-blue-300/12 bg-slate-950/35">
                    <table class="table min-w-[650px]">
                        <thead>
                            <tr>
                                <th>rol</th>
                                <th>nombre</th>
                                <th>apellido</th>
                                <th>ci</th>
                                <th>correo</th>
                                <th>telefono</th>
                                <th>estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Docente</td>
                                <td>Pedro</td>
                                <td>Rivera</td>
                                <td>9010001</td>
                                <td>pedro.rivera@test.com</td>
                                <td>79991001</td>
                                <td><span class="badge badge-success">ACTIVO</span></td>
                            </tr>
                            <tr>
                                <td>Postulante</td>
                                <td>Maria</td>
                                <td>Lopez</td>
                                <td>9010002</td>
                                <td>maria.lopez@test.com</td>
                                <td>79991002</td>
                                <td><span class="badge badge-success">ACTIVO</span></td>
                            </tr>
                            <tr>
                                <td>Coordinador</td>
                                <td>Sofia</td>
                                <td>Martinez</td>
                                <td>9010003</td>
                                <td>sofia.martinez@test.com</td>
                                <td>79991003</td>
                                <td><span class="badge badge-success">ACTIVO</span></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="rounded-[1.4rem] border border-blue-300/12 bg-slate-950/35 p-5">
                    <p class="text-sm font-semibold uppercase tracking-[0.16em] text-blue-200/75">Reglas del formato</p>
                    <ul class="mt-4 list-disc space-y-2 pl-5 text-sm leading-7 text-slate-300">
                        <li>El rol se busca por `roles.nombre`.</li>
                        <li>Si `estado` viene vacio, se usara `ACTIVO`.</li>
                        <li>No se importan filas con `ci` o `correo` duplicados o invalidos.</li>
                        <li>Una fila con error no detiene la importacion del resto del archivo.</li>
                    </ul>
                </div>
            </div>
        </x-card>
    </div>
@endsection
