<?php 
require_once __DIR__ . '/../includes/app.php';


use MVC\Router;
use Controllers\AppController;

//IMPORTAMOS LA CLASE CLIENTE CONTROLLER
use Controllers\ClienteController;

$router = new Router();
$router->setBaseURL('/' . $_ENV['APP_NAME']);

$router->get('/', [AppController::class,'index']);

//get es paraobtener una vista y POST es para enviar o presentar
//RUTAS PARA CLIENTES
$router->get('/clientes', [ClienteController::class,'renderizarPagina']);
$router->post('/clientes/guardarCliente', [ClienteController::class,'guardarCliente']);


// Comprueba y valida las rutas, que existan y les asigna las funciones del Controlador
$router->comprobarRutas();
