<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

// Ini file pengaturan utama aplikasi Laravel (jantungnya aplikasi)
return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Daftarin rute mana aja yang dipakai (kita pakai web.php)
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Percayai semua proxy (berguna kalau akses lokal pakai ip atau ngrok/laragon)
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();