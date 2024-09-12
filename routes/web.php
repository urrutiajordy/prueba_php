<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/healthy', function(){return 'Hello world';});
$router->get('/cuentas', 'RequestBankController@cuentas');
$router->post('/cuentas/{id}/depositar', 'RequestBankController@cuentas');
$router->post('/cuentas/{id}/retirar', 'RequestBankController@cuentas');
$router->post('/cuentas/{id}/transferir', 'RequestBankController@cuentas');
$router->get('/cuentas/{id}', 'RequestBankController@cuentas');
