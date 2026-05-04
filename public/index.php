<?php

/*
|--------------------------------------------------------------------------
| Laravel Application Entry Point
|--------------------------------------------------------------------------
|
| This file is the entry point for all requests into a Laravel
| application. The autoloader included with these files is responsible
| for loading the classes needed by this application.
|
*/

if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

require __DIR__.'/../vendor/autoload.php';

$app = require_once __DIR__.'/../bootstrap/app.php';

$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);

$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$response->send();

$kernel->terminate($request, $response);
