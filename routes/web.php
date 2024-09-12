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
$router->post('/cuentas/{uid}/depositar', 'RequestBankController@procesar_deposito');
$router->post('/cuentas/{uid}/retirar', 'RequestBankController@procesar_retiro');
$router->post('/cuentas/{uid}/transferir', 'RequestBankController@procesar_transferencia');
$router->get('/cuentas/{uid}', 'RequestBankController@ver_detalle_cuenta');

