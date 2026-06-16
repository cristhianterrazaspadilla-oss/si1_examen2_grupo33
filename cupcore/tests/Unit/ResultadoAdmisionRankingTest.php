<?php

use App\Http\Controllers\GestionPostulantesAdmision\ResultadoAdmisionController;
use App\Models\Postulante;
use Illuminate\Support\Collection;

function candidatoAdmision(int $postulanteId, float $promedioFinal): array
{
    $postulante = new Postulante;
    $postulante->id = $postulanteId;

    return [
        'postulante' => $postulante,
        'calculo' => ['promedio_final' => number_format($promedioFinal, 2, '.', '')],
        'promedio_final' => $promedioFinal,
    ];
}

test('ordena los candidatos por mejor promedio antes de asignar cupos', function () {
    $controller = new class extends ResultadoAdmisionController
    {
        public function ordenar(Collection $candidatos): Collection
        {
            return $this->ordenarCandidatosPorMerito($candidatos);
        }
    };

    $ordenados = $controller->ordenar(collect([
        candidatoAdmision(1, 65),
        candidatoAdmision(2, 90),
        candidatoAdmision(3, 75),
    ]));

    expect($ordenados->pluck('postulante.id')->all())->toBe([2, 3, 1]);
});

test('resuelve empates de promedio por el menor id de postulante', function () {
    $controller = new class extends ResultadoAdmisionController
    {
        public function ordenar(Collection $candidatos): Collection
        {
            return $this->ordenarCandidatosPorMerito($candidatos);
        }
    };

    $ordenados = $controller->ordenar(collect([
        candidatoAdmision(8, 80),
        candidatoAdmision(3, 80),
    ]));

    expect($ordenados->pluck('postulante.id')->all())->toBe([3, 8]);
});
