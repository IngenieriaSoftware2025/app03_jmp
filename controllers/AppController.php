<?php

namespace Controllers;

use MVC\Router;

class AppController {
    public static function index(Router $router){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || !isset($_SESSION['rol'])) {
            header('Location: /app03_jmp/');
            exit;
        }
        
        $router->render('pages/index', []);
    }

    public static function renderInicio(Router $router){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || !isset($_SESSION['rol'])) {
            header('Location: /app03_jmp/');
            exit;
        }
        
        $router->render('pages/index', []);
    }

    public static function verificarSesion() {
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        
        if (!isset($_SESSION['user']) || !isset($_SESSION['rol'])) {
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
            'codigo' => $_SESSION['codigo'] ?? null,
            'usuario_id' => $_SESSION['usuario_id'] ?? null,
            'rol' => $_SESSION['rol'] ?? null
        ];
    }
}