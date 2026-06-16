<?php

namespace App\Http\Controllers\ReportesDashboardComunicacion;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

/**
 * Paquete: Reportes, Dashboard y Comunicación (Consolidado en este namespace)
 * Caso de Uso: CU17 - Consulta de bitácora y auditoría del sistema.
 *
 * Proporciona a los administradores y auditoría la capacidad de rastrear las acciones
 * ejecutadas por los usuarios en el sistema, detallando el módulo, acción, descripción e IP.
 */
class BitacoraController extends Controller
{
    /**
     * Listado paginado de auditoría con filtros por usuario, acción, módulo y rango de fechas.
     */
    public function index(Request $request): View|RedirectResponse
    {
        $this->authorizeRoles();

        $validator = Validator::make($request->query(), [
            'usuario_id' => ['nullable', 'integer', Rule::exists('usuarios', 'id')],
            'accion' => ['nullable', 'string', 'max:100'],
            'modulo' => ['nullable', 'string', 'max:100'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'busqueda' => ['nullable', 'string', 'max:100'],
        ]);

        if ($validator->fails()) {
            return redirect()
                ->route('gestion-academica-cup.bitacoras.index')
                ->withErrors($validator)
                ->withInput();
        }

        $filters = $this->filters($request);
        $busquedaNormalizada = $this->normalizeSearchTerm($filters['busqueda']);
        $busquedaNormalizadaLike = '%'.$busquedaNormalizada.'%';
        $busquedaConGuionBajoLike = '%'.str_replace(' ', '_', $busquedaNormalizada).'%';

        $bitacoras = DB::table('bitacoras')
            ->leftJoin('usuarios', 'usuarios.id', '=', 'bitacoras.usuario_id')
            ->leftJoin('roles', 'roles.id', '=', 'usuarios.rol_id')
            ->when($filters['usuario_id'] !== '', fn ($query) => $query->where('bitacoras.usuario_id', $filters['usuario_id']))
            ->when($filters['accion'] !== '', fn ($query) => $query->where('bitacoras.accion', $filters['accion']))
            ->when($filters['modulo'] !== '', fn ($query) => $query->where('bitacoras.modulo', $filters['modulo']))
            ->when($filters['fecha_desde'] !== '', fn ($query) => $query->whereDate('bitacoras.fecha', '>=', $filters['fecha_desde']))
            ->when($filters['fecha_hasta'] !== '', fn ($query) => $query->whereDate('bitacoras.fecha', '<=', $filters['fecha_hasta']))
            ->when($filters['busqueda'] !== '', function ($query) use ($filters, $busquedaNormalizadaLike, $busquedaConGuionBajoLike): void {
                $term = '%'.$filters['busqueda'].'%';
                $query->where(function ($subQuery) use ($term, $busquedaNormalizadaLike, $busquedaConGuionBajoLike): void {
                    $subQuery
                        ->where('bitacoras.accion', 'ILIKE', $term)
                        ->orWhere('bitacoras.descripcion', 'ILIKE', $term)
                        ->orWhere('bitacoras.modulo', 'ILIKE', $term)
                        ->orWhere('bitacoras.ip_address', 'ILIKE', $term)
                        ->orWhere('usuarios.nombre', 'ILIKE', $term)
                        ->orWhere('usuarios.apellido', 'ILIKE', $term)
                        ->orWhere('usuarios.correo', 'ILIKE', $term)
                        ->orWhere('usuarios.ci', 'ILIKE', $term)
                        ->orWhereRaw('LOWER(bitacoras.accion) LIKE ?', [$busquedaConGuionBajoLike])
                        ->orWhereRaw("LOWER(REPLACE(bitacoras.accion, '_', ' ')) LIKE ?", [$busquedaNormalizadaLike])
                        ->orWhereRaw('LOWER(bitacoras.modulo) LIKE ?', [$busquedaConGuionBajoLike])
                        ->orWhereRaw("LOWER(REPLACE(bitacoras.modulo, '_', ' ')) LIKE ?", [$busquedaNormalizadaLike]);
                });
            })
            ->select([
                'bitacoras.id',
                'bitacoras.usuario_id',
                'bitacoras.accion',
                'bitacoras.descripcion',
                'bitacoras.modulo',
                'bitacoras.ip_address',
                'bitacoras.fecha',
                'bitacoras.created_at',
                'bitacoras.updated_at',
                'usuarios.nombre as usuario_nombre',
                'usuarios.apellido as usuario_apellido',
                'usuarios.correo as usuario_correo',
                'usuarios.ci as usuario_ci',
                'usuarios.estado as usuario_estado',
                'roles.nombre as rol_nombre',
            ])
            ->orderByDesc('bitacoras.fecha')
            ->orderByDesc('bitacoras.id')
            ->paginate(15)
            ->withQueryString();

        $usuarios = DB::table('bitacoras')
            ->join('usuarios', 'usuarios.id', '=', 'bitacoras.usuario_id')
            ->leftJoin('roles', 'roles.id', '=', 'usuarios.rol_id')
            ->select([
                'usuarios.id',
                'usuarios.nombre',
                'usuarios.apellido',
                'usuarios.correo',
                'usuarios.ci',
                'roles.nombre as rol_nombre',
            ])
            ->distinct()
            ->orderBy('usuarios.apellido')
            ->orderBy('usuarios.nombre')
            ->get();

        $acciones = DB::table('bitacoras')
            ->whereNotNull('accion')
            ->where('accion', '!=', '')
            ->distinct()
            ->orderBy('accion')
            ->pluck('accion');

        $modulos = DB::table('bitacoras')
            ->whereNotNull('modulo')
            ->where('modulo', '!=', '')
            ->distinct()
            ->orderBy('modulo')
            ->pluck('modulo');

        return view('reportes_dashboard_comunicacion.bitacoras.index', [
            'bitacoras' => $bitacoras,
            'filters' => $filters,
            'usuarios' => $usuarios,
            'acciones' => $acciones,
            'modulos' => $modulos,
        ]);
    }

    /**
     * Muestra el detalle particular de una acción auditada.
     */
    public function show(int $id): View
    {
        $this->authorizeRoles();

        $bitacora = DB::table('bitacoras')
            ->leftJoin('usuarios', 'usuarios.id', '=', 'bitacoras.usuario_id')
            ->leftJoin('roles', 'roles.id', '=', 'usuarios.rol_id')
            ->where('bitacoras.id', $id)
            ->select([
                'bitacoras.id',
                'bitacoras.usuario_id',
                'bitacoras.accion',
                'bitacoras.descripcion',
                'bitacoras.modulo',
                'bitacoras.ip_address',
                'bitacoras.fecha',
                'bitacoras.created_at',
                'bitacoras.updated_at',
                'usuarios.nombre as usuario_nombre',
                'usuarios.apellido as usuario_apellido',
                'usuarios.correo as usuario_correo',
                'usuarios.ci as usuario_ci',
                'usuarios.estado as usuario_estado',
                'roles.nombre as rol_nombre',
            ])
            ->firstOrFail();

        return view('reportes_dashboard_comunicacion.bitacoras.show', [
            'bitacora' => $bitacora,
        ]);
    }

    protected function authorizeRoles(): void
    {
        $roleName = Str::of((string) (auth()->user()?->rol?->nombre ?? ''))->lower()->ascii()->toString();

        abort_unless(in_array($roleName, ['administrador', 'autoridad academica'], true), 403);
    }

    protected function filters(Request $request): array
    {
        return [
            'usuario_id' => $request->string('usuario_id')->toString(),
            'accion' => trim($request->string('accion')->toString()),
            'modulo' => trim($request->string('modulo')->toString()),
            'fecha_desde' => $request->string('fecha_desde')->toString(),
            'fecha_hasta' => $request->string('fecha_hasta')->toString(),
            'busqueda' => trim($request->string('busqueda')->toString()),
        ];
    }

    protected function normalizeSearchTerm(string $value): string
    {
        $normalized = Str::of($value)->lower()->ascii()->toString();
        $normalized = str_replace('_', ' ', $normalized);
        $normalized = preg_replace('/[^a-z0-9\s]/', ' ', $normalized) ?? '';

        return trim(preg_replace('/\s+/', ' ', $normalized) ?? '');
    }
}
