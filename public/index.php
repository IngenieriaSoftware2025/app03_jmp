<?php

require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\AppController;
use Controllers\ClienteController;
use Controllers\MarcaController;
use Controllers\ProductoController;

$router = new Router();
$router->setBaseURL('/' . $_ENV['APP_NAME']);

// Ruta principal
$router->get('/', [AppController::class, 'index']);

// Rutas de clientes
$router->get('/clientes', [ClienteController::class, 'renderizarPagina']);
$router->post('/clientes/guardarAPI', [ClienteController::class, 'guardarAPI']);
$router->post('/clientes/buscarAPI', [ClienteController::class, 'buscarAPI']);
$router->post('/clientes/modificarAPI', [ClienteController::class, 'modificarAPI']);
$router->post('/clientes/eliminarAPI', [ClienteController::class, 'eliminarAPI']);
$router->post('/clientes/buscarPorTelefonoAPI', [ClienteController::class, 'buscarPorTelefonoAPI']);
$router->post('/clientes/buscarFiltradoAPI', [ClienteController::class, 'buscarFiltradoAPI']);
$router->post('/clientes/estadisticasAPI', [ClienteController::class, 'estadisticasAPI']);

//rutas de marcas
$router->get('/marcas', [MarcaController::class, 'renderizarPagina']);
$router->post('/marcas/buscarAPI', [MarcaController::class,'buscarAPI']);
$router->post('/marcas/guardarAPI', [MarcaController::class,'guardarAPI']);
$router->post('/marcas/modificarAPI', [MarcaController::class,'modificarAPI']);
$router->post('/marcas/eliminarAPI', [MarcaController::class,'eliminarAPI']);
$router->post('/marcas/buscarFiltradoAPI', [MarcaController::class, 'buscarFiltradoAPI']);
$router->post('/marcas/estadisticasAPI', [MarcaController::class, 'estadisticasAPI']);
$router->post('/marcas/marcasPopularesAPI', [MarcaController::class, 'marcasPopularesAPI']);


// Rutas de Productos
$router->get('/productos', [ProductoController::class, 'renderizarPagina']);
$router->post('/productos/buscarAPI', [ProductoController::class, 'buscarAPI']);
$router->post('/productos/buscarMarcasAPI', [ProductoController::class, 'buscarMarcasAPI']);
$router->post('/productos/guardarAPI', [ProductoController::class, 'guardarAPI']);
$router->post('/productos/modificarAPI', [ProductoController::class, 'modificarAPI']);
$router->post('/productos/eliminarAPI', [ProductoController::class, 'eliminarAPI']);
$router->post('/productos/stockBajoAPI', [ProductoController::class, 'stockBajoAPI']);
$router->post('/productos/buscarFiltradoAPI', [ProductoController::class, 'buscarFiltradoAPI']);
$router->post('/productos/alertasStockAPI', [ProductoController::class, 'alertasStockAPI']);

$router->comprobarRutas();