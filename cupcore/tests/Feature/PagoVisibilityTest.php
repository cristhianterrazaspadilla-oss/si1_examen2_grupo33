<?php

use App\Models\Pago;
use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

beforeEach(function () {
    Schema::dropIfExists('pagos');
    Schema::dropIfExists('postulantes');
    Schema::dropIfExists('usuarios');
    Schema::dropIfExists('roles');

    Schema::create('roles', function (Blueprint $table): void {
        $table->id();
        $table->string('nombre');
        $table->timestamps();
    });

    Schema::create('usuarios', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('rol_id');
        $table->string('nombre');
        $table->string('apellido');
        $table->string('correo');
        $table->string('password');
        $table->timestamps();
    });

    Schema::create('postulantes', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('usuario_id');
        $table->string('ci');
        $table->string('nombres');
        $table->string('apellidos');
        $table->string('correo');
        $table->string('estado_inscripcion');
        $table->timestamps();
    });

    Schema::create('pagos', function (Blueprint $table): void {
        $table->id();
        $table->foreignId('postulante_id');
        $table->decimal('monto', 10, 2);
        $table->string('moneda');
        $table->text('stripe_payment_link')->nullable();
        $table->string('stripe_payment_id')->nullable();
        $table->string('estado_pago');
        $table->dateTime('fecha_pago')->nullable();
        $table->text('observacion')->nullable();
        $table->timestamps();
    });
});

function createPaymentScenario(): array
{
    $postulanteRoleId = DB::table('roles')->insertGetId([
        'nombre' => 'Postulante',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $firstUserId = DB::table('usuarios')->insertGetId([
        'rol_id' => $postulanteRoleId,
        'nombre' => 'Javier',
        'apellido' => 'Mendoza',
        'correo' => 'javier@example.com',
        'password' => bcrypt('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $secondUserId = DB::table('usuarios')->insertGetId([
        'rol_id' => $postulanteRoleId,
        'nombre' => 'Mateo',
        'apellido' => 'Castro',
        'correo' => 'mateo@example.com',
        'password' => bcrypt('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $firstPostulanteId = DB::table('postulantes')->insertGetId([
        'usuario_id' => $firstUserId,
        'ci' => '9901002',
        'nombres' => 'Javier',
        'apellidos' => 'Mendoza Paz',
        'correo' => 'javier@example.com',
        'estado_inscripcion' => 'PAGO_PENDIENTE',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $secondPostulanteId = DB::table('postulantes')->insertGetId([
        'usuario_id' => $secondUserId,
        'ci' => '9901004',
        'nombres' => 'Mateo',
        'apellidos' => 'Castro Lima',
        'correo' => 'mateo@example.com',
        'estado_inscripcion' => 'PAGO_PENDIENTE',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $firstPaymentId = DB::table('pagos')->insertGetId([
        'postulante_id' => $firstPostulanteId,
        'monto' => 150,
        'moneda' => 'BOB',
        'estado_pago' => 'PENDIENTE',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $secondPaymentId = DB::table('pagos')->insertGetId([
        'postulante_id' => $secondPostulanteId,
        'monto' => 150,
        'moneda' => 'BOB',
        'estado_pago' => 'PENDIENTE',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    return [
        'user' => User::query()->findOrFail($firstUserId),
        'ownPayment' => Pago::query()->findOrFail($firstPaymentId),
        'otherPayment' => Pago::query()->findOrFail($secondPaymentId),
    ];
}

test('a postulante only sees their own payments', function () {
    $scenario = createPaymentScenario();

    $response = $this
        ->actingAs($scenario['user'])
        ->get(route('gestion-postulantes-admision.pagos.index'));

    $response
        ->assertOk()
        ->assertSeeText('Javier Mendoza Paz')
        ->assertDontSeeText('Mateo Castro Lima');
});

test('a postulante cannot open another postulante payment', function () {
    $scenario = createPaymentScenario();

    $this
        ->actingAs($scenario['user'])
        ->get(route('gestion-postulantes-admision.pagos.show', $scenario['otherPayment']))
        ->assertForbidden();
});

test('a postulante can open their own payment without administrative actions', function () {
    $scenario = createPaymentScenario();

    $response = $this
        ->actingAs($scenario['user'])
        ->get(route('gestion-postulantes-admision.pagos.show', $scenario['ownPayment']));

    $response
        ->assertOk()
        ->assertDontSeeText('Verificar pago')
        ->assertDontSeeText('Editar');
});

test('a postulante cannot access payment management actions', function () {
    $scenario = createPaymentScenario();

    $this
        ->actingAs($scenario['user'])
        ->get(route('gestion-postulantes-admision.pagos.create'))
        ->assertForbidden();

    $this
        ->actingAs($scenario['user'])
        ->get(route('gestion-postulantes-admision.pagos.edit', $scenario['ownPayment']))
        ->assertForbidden();

    $this
        ->actingAs($scenario['user'])
        ->post(route('gestion-postulantes-admision.pagos.verificar', $scenario['ownPayment']))
        ->assertForbidden();

    $this
        ->actingAs($scenario['user'])
        ->delete(route('gestion-postulantes-admision.pagos.destroy', $scenario['ownPayment']))
        ->assertForbidden();
});
