<?php

namespace App\Http\Controllers\GestionAcademicaCUP;

use App\Http\Controllers\Controller;
use App\Models\Notificacion;
use App\Models\Role;
use App\Models\User;
use App\Support\BitacoraHelper;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * Paquete: Reportes, Dashboard y Comunicación (Consolidado en este namespace)
 * Caso de Uso: CU19 - Gestión de notificaciones internas.
 *
 * Canaliza la mensajería y alertas del sistema hacia los usuarios, soportando la comunicación
 * de hitos como confirmación de pagos, publicación de notas/resultados y cambios de horarios.
 */
class NotificacionController extends Controller
{
    /**
     * @var array<int, string>
     */
    protected array $tiposNotificacion = [
        'GENERAL',
        'PAGO',
        'RESULTADO',
        'ACADEMICO',
        'GRUPO',
        'HORARIO',
        'REQUISITO',
        'SISTEMA',
    ];

    /**
     * Muestra la bandeja de entrada del usuario actual con filtros de estado de lectura y tipo.
     */
    public function index(Request $request): View
    {
        $user = $request->user();
        $filters = $this->receivedFilters($request);
        $baseQuery = $this->receivedQuery((int) $user->id, $filters);
        $notificaciones = (clone $baseQuery)
            ->select([
                'notificaciones.*',
                'emisor.nombre as emisor_nombre',
                'emisor.apellido as emisor_apellido',
                'emisor.correo as emisor_correo',
                'rol_emisor.nombre as emisor_rol_nombre',
            ])
            ->orderBy('notificaciones.leido')
            ->orderByDesc('notificaciones.created_at')
            ->orderByDesc('notificaciones.id')
            ->paginate(15)
            ->withQueryString();

        $summaryQuery = $this->receivedQuery((int) $user->id, $filters);
        $totalRecibidas = (clone $summaryQuery)->count();
        $noLeidas = (clone $summaryQuery)->where('notificaciones.leido', false)->count();
        $leidas = (clone $summaryQuery)->where('notificaciones.leido', true)->count();
        $contadorNoLeidas = Notificacion::query()
            ->where('usuario_receptor_id', $user->id)
            ->where('leido', false)
            ->count();

        return view('gestion_academica_cup.notificaciones.index', [
            'notificaciones' => $notificaciones,
            'filters' => $filters,
            'tiposNotificacion' => $this->tiposNotificacion,
            'totalRecibidas' => $totalRecibidas,
            'noLeidas' => $noLeidas,
            'leidas' => $leidas,
            'contadorNoLeidas' => $contadorNoLeidas,
            'canSendNotifications' => $this->canSendNotifications($user),
        ]);
    }

    /**
     * Muestra la bandeja de salida (notificaciones enviadas) para usuarios autorizados.
     */
    public function enviadas(Request $request): View
    {
        $user = $request->user();
        abort_unless($this->canSendNotifications($user), 403);

        $filters = $this->sentFilters($request);
        $baseQuery = $this->sentQuery((int) $user->id, $filters);
        $notificaciones = (clone $baseQuery)
            ->select([
                'notificaciones.*',
                'receptor.nombre as receptor_nombre',
                'receptor.apellido as receptor_apellido',
                'receptor.correo as receptor_correo',
                'rol_receptor.nombre as receptor_rol_nombre',
            ])
            ->orderBy('notificaciones.leido')
            ->orderByDesc('notificaciones.created_at')
            ->orderByDesc('notificaciones.id')
            ->paginate(15)
            ->withQueryString();

        $summaryQuery = $this->sentQuery((int) $user->id, $filters);
        $totalEnviadas = (clone $summaryQuery)->count();
        $leidas = (clone $summaryQuery)->where('notificaciones.leido', true)->count();
        $noLeidas = (clone $summaryQuery)->where('notificaciones.leido', false)->count();

        return view('gestion_academica_cup.notificaciones.enviadas', [
            'notificaciones' => $notificaciones,
            'filters' => $filters,
            'tiposNotificacion' => $this->tiposNotificacion,
            'usuariosActivos' => $this->usuariosActivos(),
            'totalEnviadas' => $totalEnviadas,
            'leidas' => $leidas,
            'noLeidas' => $noLeidas,
        ]);
    }

    public function create(Request $request): View
    {
        $user = $request->user();
        abort_unless($this->canSendNotifications($user), 403);

        return view('gestion_academica_cup.notificaciones.create', [
            'usuariosActivos' => $this->usuariosActivos(),
            'rolesActivos' => $this->rolesActivos(),
            'tiposNotificacion' => $this->tiposNotificacion,
        ]);
    }

    /**
     * Guarda y despacha la notificación a su destinatario, registrando la operación en la bitácora.
     */
    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        abort_unless($this->canSendNotifications($user), 403);

        $validated = $request->validate([
            'usuario_receptor_id' => ['required', 'integer', 'exists:usuarios,id'],
            'titulo' => ['required', 'string', 'max:150'],
            'mensaje' => ['required', 'string', 'max:1000'],
            'tipo' => ['nullable', 'string', 'max:50'],
        ]);

        $receptor = User::query()
            ->where('estado', 'ACTIVO')
            ->findOrFail($validated['usuario_receptor_id']);

        $tipo = Str::upper(trim((string) ($validated['tipo'] ?: 'GENERAL')));

        $notificacion = Notificacion::create([
            'usuario_emisor_id' => $user->id,
            'usuario_receptor_id' => $receptor->id,
            'titulo' => trim($validated['titulo']),
            'mensaje' => trim($validated['mensaje']),
            'tipo' => $tipo,
            'leido' => false,
            'fecha_lectura' => null,
        ]);

        BitacoraHelper::registrar(
            'CREAR_NOTIFICACION',
            'Notificaciones',
            'Se envio notificacion interna a ' . $receptor->correo . ' con tipo ' . $tipo . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.notificaciones.show', $notificacion)
            ->with('success', 'Notificacion interna enviada correctamente.');
    }

    public function show(Request $request, Notificacion $notificacion): View
    {
        $user = $request->user();
        abort_unless($this->canViewNotification($user, $notificacion), 403);

        $notificacion->load([
            'usuarioEmisor.rol',
            'usuarioReceptor.rol',
        ]);

        return view('gestion_academica_cup.notificaciones.show', [
            'notificacion' => $notificacion,
            'canSendNotifications' => $this->canSendNotifications($user),
            'canMarkAsRead' => (int) $notificacion->usuario_receptor_id === (int) $user->id && ! $notificacion->leido,
        ]);
    }

    /**
     * Marca la notificación recibida como leída registrando la marca de tiempo de lectura.
     */
    public function marcarLeida(Request $request, Notificacion $notificacion): RedirectResponse
    {
        $user = $request->user();
        abort_unless((int) $notificacion->usuario_receptor_id === (int) $user->id, 403);

        if ($notificacion->leido) {
            return redirect()
                ->route('gestion-academica-cup.notificaciones.show', $notificacion)
                ->with('info', 'La notificacion ya estaba marcada como leida.');
        }

        $notificacion->update([
            'leido' => true,
            'fecha_lectura' => now(),
        ]);

        BitacoraHelper::registrar(
            'MARCAR_NOTIFICACION_LEIDA',
            'Notificaciones',
            'Se marco como leida la notificacion ID ' . $notificacion->id . '.'
        );

        return redirect()
            ->route('gestion-academica-cup.notificaciones.show', $notificacion)
            ->with('success', 'Notificacion marcada como leida.');
    }

    public function marcarTodasLeidas(Request $request): RedirectResponse
    {
        $user = $request->user();
        $cantidad = Notificacion::query()
            ->where('usuario_receptor_id', $user->id)
            ->where('leido', false)
            ->count();

        if ($cantidad === 0) {
            return redirect()
                ->route('gestion-academica-cup.notificaciones.index')
                ->with('info', 'No tienes notificaciones pendientes de lectura.');
        }

        Notificacion::query()
            ->where('usuario_receptor_id', $user->id)
            ->where('leido', false)
            ->update([
                'leido' => true,
                'fecha_lectura' => now(),
                'updated_at' => now(),
            ]);

        BitacoraHelper::registrar(
            'MARCAR_TODAS_NOTIFICACIONES_LEIDAS',
            'Notificaciones',
            'Se marcaron como leidas ' . $cantidad . ' notificaciones.'
        );

        return redirect()
            ->route('gestion-academica-cup.notificaciones.index')
            ->with('success', 'Se marcaron como leidas ' . $cantidad . ' notificaciones.');
    }

    protected function receivedQuery(int $userId, array $filters): Builder
    {
        return DB::table('notificaciones')
            ->leftJoin('usuarios as emisor', 'emisor.id', '=', 'notificaciones.usuario_emisor_id')
            ->leftJoin('roles as rol_emisor', 'rol_emisor.id', '=', 'emisor.rol_id')
            ->where('notificaciones.usuario_receptor_id', $userId)
            ->when($filters['tipo'] !== '', fn (Builder $query) => $query->where('notificaciones.tipo', $filters['tipo']))
            ->when($filters['estado_lectura'] === 'leidas', fn (Builder $query) => $query->where('notificaciones.leido', true))
            ->when($filters['estado_lectura'] === 'no_leidas', fn (Builder $query) => $query->where('notificaciones.leido', false))
            ->when($filters['fecha_desde'] !== '', fn (Builder $query) => $query->whereDate('notificaciones.created_at', '>=', $filters['fecha_desde']))
            ->when($filters['fecha_hasta'] !== '', fn (Builder $query) => $query->whereDate('notificaciones.created_at', '<=', $filters['fecha_hasta']))
            ->when($filters['busqueda'] !== '', function (Builder $query) use ($filters): void {
                $term = '%' . $filters['busqueda'] . '%';

                $query->where(function (Builder $subQuery) use ($term): void {
                    $subQuery->where('notificaciones.titulo', 'like', $term)
                        ->orWhere('notificaciones.mensaje', 'like', $term)
                        ->orWhere('notificaciones.tipo', 'like', $term)
                        ->orWhere('emisor.nombre', 'like', $term)
                        ->orWhere('emisor.apellido', 'like', $term)
                        ->orWhere('emisor.correo', 'like', $term);
                });
            });
    }

    protected function sentQuery(int $userId, array $filters): Builder
    {
        return DB::table('notificaciones')
            ->leftJoin('usuarios as receptor', 'receptor.id', '=', 'notificaciones.usuario_receptor_id')
            ->leftJoin('roles as rol_receptor', 'rol_receptor.id', '=', 'receptor.rol_id')
            ->where('notificaciones.usuario_emisor_id', $userId)
            ->when($filters['tipo'] !== '', fn (Builder $query) => $query->where('notificaciones.tipo', $filters['tipo']))
            ->when($filters['usuario_receptor_id'] !== '', fn (Builder $query) => $query->where('notificaciones.usuario_receptor_id', $filters['usuario_receptor_id']))
            ->when($filters['estado_lectura'] === 'leidas', fn (Builder $query) => $query->where('notificaciones.leido', true))
            ->when($filters['estado_lectura'] === 'no_leidas', fn (Builder $query) => $query->where('notificaciones.leido', false))
            ->when($filters['fecha_desde'] !== '', fn (Builder $query) => $query->whereDate('notificaciones.created_at', '>=', $filters['fecha_desde']))
            ->when($filters['fecha_hasta'] !== '', fn (Builder $query) => $query->whereDate('notificaciones.created_at', '<=', $filters['fecha_hasta']))
            ->when($filters['busqueda'] !== '', function (Builder $query) use ($filters): void {
                $term = '%' . $filters['busqueda'] . '%';

                $query->where(function (Builder $subQuery) use ($term): void {
                    $subQuery->where('notificaciones.titulo', 'like', $term)
                        ->orWhere('notificaciones.mensaje', 'like', $term)
                        ->orWhere('notificaciones.tipo', 'like', $term)
                        ->orWhere('receptor.nombre', 'like', $term)
                        ->orWhere('receptor.apellido', 'like', $term)
                        ->orWhere('receptor.correo', 'like', $term);
                });
            });
    }

    protected function usuariosActivos()
    {
        return User::query()
            ->with('rol')
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->orderBy('apellido')
            ->get();
    }

    protected function rolesActivos()
    {
        return Role::query()
            ->where('estado', 'ACTIVO')
            ->orderBy('nombre')
            ->get();
    }

    protected function canSendNotifications(?User $user): bool
    {
        $role = Str::of((string) $user?->rol?->nombre)->lower()->ascii()->toString();

        return in_array($role, ['administrador', 'coordinador'], true);
    }

    protected function canViewNotification(?User $user, Notificacion $notificacion): bool
    {
        if ($user === null) {
            return false;
        }

        if ((int) $notificacion->usuario_receptor_id === (int) $user->id || (int) $notificacion->usuario_emisor_id === (int) $user->id) {
            return true;
        }

        return $this->canSendNotifications($user);
    }

    /**
     * @return array{tipo:string,estado_lectura:string,fecha_desde:string,fecha_hasta:string,busqueda:string}
     */
    protected function receivedFilters(Request $request): array
    {
        return [
            'tipo' => trim((string) $request->input('tipo', '')),
            'estado_lectura' => trim((string) $request->input('estado_lectura', '')),
            'fecha_desde' => trim((string) $request->input('fecha_desde', '')),
            'fecha_hasta' => trim((string) $request->input('fecha_hasta', '')),
            'busqueda' => trim((string) $request->input('busqueda', '')),
        ];
    }

    /**
     * @return array{tipo:string,usuario_receptor_id:string,estado_lectura:string,fecha_desde:string,fecha_hasta:string,busqueda:string}
     */
    protected function sentFilters(Request $request): array
    {
        return [
            'tipo' => trim((string) $request->input('tipo', '')),
            'usuario_receptor_id' => trim((string) $request->input('usuario_receptor_id', '')),
            'estado_lectura' => trim((string) $request->input('estado_lectura', '')),
            'fecha_desde' => trim((string) $request->input('fecha_desde', '')),
            'fecha_hasta' => trim((string) $request->input('fecha_hasta', '')),
            'busqueda' => trim((string) $request->input('busqueda', '')),
        ];
    }
}
