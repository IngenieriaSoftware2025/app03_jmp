<?php

namespace Controllers;

use MVC\Router;

class AppController {
    public static function index(Router $router){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar sesión de manera flexible para mayor compatibilidad
        if (!isset($_SESSION['user'])) {
            header('Location: /app03_jmp/');
            exit;
        }
        
        // Renderizar con el layout adecuado
        $router->render('pages/index', [], 'layouts/menu');
    }

    public static function renderInicio(Router $router){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificar sesión de manera flexible para mayor compatibilidad
        if (!isset($_SESSION['user'])) {
            header('Location: /app03_jmp/');
            exit;
        }
        
        // Renderizar con el layout adecuado
        $router->render('pages/index', [], 'layouts/menu');
    }

    public static function verificarSesion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        // Verificación simplificada de sesión
        if (!isset($_SESSION['user'])) {
            header('Location: /app03_jmp/');
            exit;
        }
        
        return true;
    }

    public static function obtenerUsuarioSesion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        return [
            'usuario' => $_SESSION['user'] ?? null,
            'nombre' => $_SESSION['nombre'] ?? null,
            'codigo' => $_SESSION['codigo'] ?? null,
            'usuario_id' => $_SESSION['usuario_id'] ?? null,
            'rol' => $_SESSION['rol'] ?? null,
            'es_admin' => isset($_SESSION['ADMIN'])
        ];
    }
}