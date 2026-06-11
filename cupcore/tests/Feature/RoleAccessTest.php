<?php

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('usuarios');
    Schema::dropIfExists('roles');

    Schema::create('roles', function (Blueprint $table): void {
        $table->id();
        $table->string('nombre');
        $table->string('estado')->default('ACTIVO');
        $table->timestamps();
    });

    Schema::create('usuarios', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('rol_id');
        $table->string('nombre');
        $table->string('apellido');
        $table->string('correo');
        $table->string('password');
        $table->string('estado')->default('ACTIVO');
        $table->timestamps();
    });
});

function roleUser(string $role): User
{
    $roleId = DB::table('roles')->insertGetId([
        'nombre' => $role,
        'estado' => 'ACTIVO',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $userId = DB::table('usuarios')->insertGetId([
        'rol_id' => $roleId,
        'nombre' => 'Usuario',
        'apellido' => $role,
        'correo' => strtolower(str_replace(' ', '.', $role)) . '@example.com',
        'password' => bcrypt('password'),
        'estado' => 'ACTIVO',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return User::query()->findOrFail($userId);
}

test('postulante cannot access administrative modules by URL', function () {
    $user = roleUser('Postulante');

    foreach ([
        'autenticacion-usuarios-seguridad.usuarios.index',
        'gestion-postulantes-admision.requisitos-postulantes.index',
        'gestion-postulantes-admision.carreras.index',
        'gestion-academica-cup.materias.index',
        'gestion-academica-cup.notas.index',
        'gestion-academica-cup.reportes.consulta',
        'gestion-academica-cup.bitacoras.index',
    ] as $routeName) {
        $this->actingAs($user)->get(route($routeName))->assertForbidden();
    }
});

test('docente cannot access coordination or administration modules by URL', function () {
    $user = roleUser('Docente');

    foreach ([
        'autenticacion-usuarios-seguridad.roles.index',
        'gestion-postulantes-admision.postulantes.index',
        'gestion-postulantes-admision.pagos.index',
        'gestion-academica-cup.materias.index',
        'gestion-academica-cup.resultados.index',
        'gestion-academica-cup.reportes.consulta',
        'gestion-academica-cup.bitacoras.index',
    ] as $routeName) {
        $this->actingAs($user)->get(route($routeName))->assertForbidden();
    }
});

test('autoridad has read institutional access but cannot manage academic records', function () {
    $user = roleUser('Autoridad Academica');

    foreach ([
        'autenticacion-usuarios-seguridad.usuarios.index',
        'gestion-postulantes-admision.postulantes.index',
        'gestion-postulantes-admision.pagos.index',
        'gestion-academica-cup.materias.index',
        'gestion-academica-cup.notas.index',
    ] as $routeName) {
        $this->actingAs($user)->get(route($routeName))->assertForbidden();
    }
});

test('password recovery demo accepts a valid email without changing data', function () {
    $this->post(route('password.demo.store'), ['correo' => 'persona@example.com'])
        ->assertRedirect(route('password.demo'))
        ->assertSessionHas('success');
});
