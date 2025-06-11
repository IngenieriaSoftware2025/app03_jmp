<?php

require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\AppController;
use Controllers\ClienteController;
use Controllers\MarcaController;

$router = new Router();
$router->setBaseURL('/' . $_ENV['APP_NAME']);

// Ruta principal
$router->get('/', [AppController::class, 'index']);

// Rutas de clientes completas
$router->get('/clientes', [ClienteController::class, 'renderizarPagina']);
$router->post('/clientes/guardarAPI', [ClienteController::class, 'guardarAPI']);
$router->get('/clientes/buscarAPI', [ClienteController::class, 'buscarAPI']);
$router->post('/clientes/modificarAPI', [ClienteController::class, 'modificarAPI']);
$router->post('/clientes/eliminarAPI', [ClienteController::class, 'eliminarAPI']);
$router->post('/clientes/buscarPorTelefonoAPI', [ClienteController::class, 'buscarPorTelefonoAPI']);

// Rutas de Marcas estandarizadas
$router->get('/marcas', [MarcaController::class,'renderizarPagina']);
$router->post('/marcas/buscarAPI', [MarcaController::class,'buscarAPI']);
$router->post('/marcas/guardarAPI', [MarcaController::class,'guardarAPI']);
$router->post('/marcas/modificarAPI', [MarcaController::class,'modificarAPI']);
$router->post('/marcas/eliminarAPI', [MarcaController::class,'eliminarAPI']);

$router->comprobarRutas();