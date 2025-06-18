<?php 

require_once __DIR__ . '/../includes/app.php';

use MVC\Router;
use Controllers\AppController;
use Controllers\LoginController;
use Controllers\ClienteController;
use Controllers\MarcaController;
use Controllers\ProductoController;
use Controllers\VentaController;
use Controllers\ReparacionController;
use Controllers\UsuarioController;

$router = new Router();
$router->setBaseURL('/' . $_ENV['APP_NAME']);

//login
$router->get('/', [LoginController::class,'renderizarPagina']);
$router->post('/API/login', [LoginController::class,'login']);
$router->get('/inicio', [AppController::class,'index']);
$router->get('/logout', [LoginController::class,'logout']);

// RUTAS DE CLIENTES
$router->get('/clientes', [ClienteController::class, 'renderizarPagina']);
$router->post('/clientes/guardarAPI', [ClienteController::class, 'guardarAPI']);
$router->post('/clientes/buscarAPI', [ClienteController::class, 'buscarAPI']);
$router->post('/clientes/modificarAPI', [ClienteController::class, 'modificarAPI']);
$router->post('/clientes/eliminarAPI', [ClienteController::class, 'eliminarAPI']);
$router->post('/clientes/buscarPorTelefonoAPI', [ClienteController::class, 'buscarPorTelefonoAPI']);
$router->post('/clientes/buscarFiltradoAPI', [ClienteController::class, 'buscarFiltradoAPI']);
$router->post('/clientes/estadisticasAPI', [ClienteController::class, 'estadisticasAPI']);

// RUTAS DE MARCAS (Solo Admin)
$router->get('/marcas', [MarcaController::class, 'renderizarPagina']);
$router->post('/marcas/buscarAPI', [MarcaController::class,'buscarAPI']);
$router->post('/marcas/guardarAPI', [MarcaController::class,'guardarAPI']);
$router->post('/marcas/modificarAPI', [MarcaController::class,'modificarAPI']);
$router->post('/marcas/eliminarAPI', [MarcaController::class,'eliminarAPI']);
$router->post('/marcas/buscarFiltradoAPI', [MarcaController::class, 'buscarFiltradoAPI']);
$router->post('/marcas/estadisticasAPI', [MarcaController::class, 'estadisticasAPI']);
$router->post('/marcas/marcasPopularesAPI', [MarcaController::class, 'marcasPopularesAPI']);

// RUTAS DE PRODUCTOS/INVENTARIO
$router->get('/productos', [ProductoController::class, 'renderizarPagina']);
$router->post('/productos/buscarAPI', [ProductoController::class, 'buscarAPI']);
$router->post('/productos/buscarMarcasAPI', [ProductoController::class, 'buscarMarcasAPI']);
$router->post('/productos/guardarAPI', [ProductoController::class, 'guardarAPI']);
$router->post('/productos/modificarAPI', [ProductoController::class, 'modificarAPI']);
$router->post('/productos/eliminarAPI', [ProductoController::class, 'eliminarAPI']);
$router->post('/productos/stockBajoAPI', [ProductoController::class, 'stockBajoAPI']);
$router->post('/productos/buscarFiltradoAPI', [ProductoController::class, 'buscarFiltradoAPI']);
$router->post('/productos/alertasStockAPI', [ProductoController::class, 'alertasStockAPI']);

// RUTAS DE VENTAS
$router->get('/ventas', [VentaController::class, 'renderizarPagina']);
$router->post('/ventas/buscarClienteAPI', [VentaController::class, 'buscarClienteAPI']);
$router->post('/ventas/buscarProductosAPI', [VentaController::class, 'buscarProductosAPI']);
$router->post('/ventas/procesarVentaAPI', [VentaController::class, 'procesarVentaAPI']);
$router->post('/ventas/historialAPI', [VentaController::class, 'historialAPI']);
$router->post('/ventas/obtenerDetalleVentaAPI', [VentaController::class, 'obtenerDetalleVentaAPI']);
$router->post('/ventas/anularVentaAPI', [VentaController::class, 'anularVentaAPI']);
$router->post('/ventas/estadisticasVentasAPI', [VentaController::class, 'estadisticasVentasAPI']);

// RUTAS DE REPARACIONES
$router->get('/reparaciones', [ReparacionController::class, 'renderizarPagina']);
$router->post('/reparaciones/guardarAPI', [ReparacionController::class, 'guardarAPI']);
$router->post('/reparaciones/buscarAPI', [ReparacionController::class, 'buscarAPI']);
$router->post('/reparaciones/actualizarEstadoAPI', [ReparacionController::class, 'actualizarEstadoAPI']);
$router->post('/reparaciones/finalizarAPI', [ReparacionController::class, 'finalizarAPI']);
$router->post('/reparaciones/entregarAPI', [ReparacionController::class, 'entregarAPI']);

// RUTAS DE USUARIOS (Solo Admin)
$router->get('/usuarios', [UsuarioController::class, 'renderizarPagina']);
$router->post('/usuarios/buscarAPI', [UsuarioController::class, 'buscarAPI']);
$router->post('/usuarios/guardarAPI', [UsuarioController::class, 'guardarAPI']);
$router->post('/usuarios/modificarAPI', [UsuarioController::class, 'modificarAPI']);
$router->post('/usuarios/eliminarAPI', [UsuarioController::class, 'eliminarAPI']);

$router->comprobarRutas();