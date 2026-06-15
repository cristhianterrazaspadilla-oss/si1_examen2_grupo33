<?php

use App\Models\Role;

test('the system only allows the institutional roles', function () {
    expect(Role::INSTITUTIONAL_NAMES)->toBe([
        'Administrador',
        'Coordinador',
        'Docente',
        'Postulante',
        'Autoridad Académica',
    ]);
});
