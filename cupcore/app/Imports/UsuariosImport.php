<?php

namespace App\Imports;

use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class UsuariosImport implements ToCollection, WithHeadingRow
{
    protected int $importedCount = 0;

    protected array $errors = [];

    protected array $seenCi = [];

    protected array $seenCorreo = [];

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $rowNumber = $index + 2;

            try {
                $this->processRow($rowNumber, $row);
            } catch (\Throwable $exception) {
                $this->errors[] = "Fila {$rowNumber}: no se pudo procesar la fila. {$exception->getMessage()}";
            }
        }
    }

    public function getImportedCount(): int
    {
        return $this->importedCount;
    }

    public function getErrors(): array
    {
        return $this->errors;
    }

    protected function processRow(int $rowNumber, mixed $row): void
    {
        $data = [
            'rol' => $this->normalizeValue($row['rol'] ?? null),
            'nombre' => $this->normalizeValue($row['nombre'] ?? null),
            'apellido' => $this->normalizeValue($row['apellido'] ?? null),
            'ci' => $this->normalizeValue($row['ci'] ?? null),
            'correo' => $this->normalizeValue($row['correo'] ?? null),
            'telefono' => $this->normalizeValue($row['telefono'] ?? null),
            'estado' => $this->normalizeValue($row['estado'] ?? null) ?: 'ACTIVO',
        ];

        $rowErrors = [];
        $rol = null;

        if (! $data['rol']) {
            $rowErrors[] = 'el campo rol es obligatorio.';
        } else {
            $rol = Role::query()->where('nombre', $data['rol'])->first();

            if (! $rol) {
                $rowErrors[] = "el rol '{$data['rol']}' no existe en la tabla roles.";
            }
        }

        if (! $data['nombre']) {
            $rowErrors[] = 'el campo nombre es obligatorio.';
        }

        if (! $data['apellido']) {
            $rowErrors[] = 'el campo apellido es obligatorio.';
        }

        if (! $data['ci']) {
            $rowErrors[] = 'el campo ci es obligatorio.';
        } elseif (User::query()->where('ci', $data['ci'])->exists()) {
            $rowErrors[] = "el CI '{$data['ci']}' ya existe en usuarios.";
        } elseif (in_array($data['ci'], $this->seenCi, true)) {
            $rowErrors[] = "el CI '{$data['ci']}' está duplicado en el archivo.";
        }

        if (! $data['correo']) {
            $rowErrors[] = 'el campo correo es obligatorio.';
        } elseif (! filter_var($data['correo'], FILTER_VALIDATE_EMAIL)) {
            $rowErrors[] = "el correo '{$data['correo']}' no tiene un formato válido.";
        } elseif (User::query()->where('correo', $data['correo'])->exists()) {
            $rowErrors[] = "el correo '{$data['correo']}' ya existe en usuarios.";
        } elseif (in_array(mb_strtolower($data['correo']), $this->seenCorreo, true)) {
            $rowErrors[] = "el correo '{$data['correo']}' está duplicado en el archivo.";
        }

        if ($data['telefono'] !== null && mb_strlen($data['telefono']) > 30) {
            $rowErrors[] = 'el campo telefono no debe exceder 30 caracteres.';
        }

        if (! in_array($data['estado'], ['ACTIVO', 'INACTIVO'], true)) {
            $rowErrors[] = "el estado '{$data['estado']}' no es válido. Usa ACTIVO o INACTIVO.";
        }

        if ($rowErrors !== []) {
            foreach ($rowErrors as $error) {
                $this->errors[] = "Fila {$rowNumber}: {$error}";
            }

            return;
        }

        try {
            $usuario = User::create([
                'rol_id' => $rol->id,
                'nombre' => $data['nombre'],
                'apellido' => $data['apellido'],
                'ci' => $data['ci'],
                'correo' => $data['correo'],
                'telefono' => $data['telefono'],
                'password' => Hash::make('12345678'),
                'estado' => $data['estado'],
            ]);

            if (! $usuario->id || ! User::query()->whereKey($usuario->id)->exists()) {
                $this->errors[] = "Fila {$rowNumber}: el usuario no se guardó correctamente en la tabla usuarios.";

                Log::info('Importación de usuarios sin persistencia confirmada.', [
                    'fila' => $rowNumber,
                    'correo' => $data['correo'],
                ]);

                return;
            }

            $this->seenCi[] = $data['ci'];
            $this->seenCorreo[] = mb_strtolower($data['correo']);
            $this->importedCount++;
        } catch (\Throwable $exception) {
            $this->errors[] = "Fila {$rowNumber}: no se pudo crear el usuario. {$exception->getMessage()}";
        }
    }

    protected function normalizeValue(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }
}
