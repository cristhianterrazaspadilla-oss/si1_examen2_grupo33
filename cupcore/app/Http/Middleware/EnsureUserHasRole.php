<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Protege el backend aunque una opción no aparezca en el menú.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        abort_unless($user, 401);

        $currentRole = $this->normalize((string) ($user->rol?->nombre ?? ''));
        $allowedRoles = array_map(fn (string $role): string => $this->normalize($role), $roles);

        abort_unless(
            $user->estado === 'ACTIVO'
                && $user->rol?->estado === 'ACTIVO'
                && in_array($currentRole, $allowedRoles, true),
            403
        );

        return $next($request);
    }

    private function normalize(string $role): string
    {
        return Str::of($role)->lower()->ascii()->trim()->toString();
    }
}
