<?php

use Illuminate\Support\Facades\Route;

test('the application returns a successful response', function () {
    $response = $this->get('/');

    $response->assertStatus(200);
});

test('trusted proxy headers generate secure urls', function () {
    Route::get('/test-secure-url', fn () => route('gestion-academica-cup.reportes.interpretar-comando'));

    $response = $this
        ->withServerVariables(['REMOTE_ADDR' => '10.0.0.1'])
        ->withHeaders([
            'X-Forwarded-Host' => 'si1-examen2-grupo33.onrender.com',
            'X-Forwarded-Port' => '443',
            'X-Forwarded-Proto' => 'https',
        ])
        ->get('/test-secure-url');

    $response
        ->assertOk()
        ->assertSeeText('https://si1-examen2-grupo33.onrender.com/gestion-academica-cup/reportes/interpretar-comando');
});
