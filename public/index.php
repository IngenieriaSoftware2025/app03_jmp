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

// Rutas de clientes
$router->get('/clientes', [ClienteController::class, 'renderizarPagina']);
$router->post('/clientes/guardarAPI', [ClienteController::class, 'guardarAPI']);
$router->get('/clientes/buscarAPI', [ClienteController::class, 'buscarAPI']);
$router->post('/clientes/modificarAPI', [ClienteController::class, 'modificarAPI']);
$router->post('/clientes/eliminarAPI', [ClienteController::class, 'eliminarAPI']);

//Rutas de Marcas
$router->get('/marcas', [MarcaController::class,'renderizarPagina']);
$router->post('/marcas/buscar', [MarcaController::class,'buscarAPI']);
$router->post('/marcas/guardar', [MarcaController::class,'guardarAPI']);
$router->post('/marcas/modificar', [MarcaController::class,'modificarAPI']);
$router->post('/marcas/eliminar', [MarcaController::class,'eliminarAPI']);



$router->comprobarRutas();