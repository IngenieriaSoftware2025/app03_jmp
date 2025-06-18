<?php

namespace Controllers;

use Model\ActiveRecord;
use MVC\Router;
use Exception;

class LoginController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('login/index', [], 'layouts/login');
    }

    public static function login() {
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        
        try {
            if (session_status() == PHP_SESSION_NONE) {
                session_start();
            }

            $codigo = htmlspecialchars($_POST['usu_codigo']);
            $contrasena = htmlspecialchars($_POST['usu_password']);

            $queryExisteUser = "SELECT usu_id, usu_nombre, usu_password FROM usuario WHERE usu_codigo = $codigo AND usu_situacion = 1";

            $resultados = ActiveRecord::fetchArray($queryExisteUser);

            if (empty($resultados)) {
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El usuario que intenta ingresar no existe'
                ]);
                return;
            }

            $existeUsuario = $resultados[0];
            $passDB = $existeUsuario['usu_password'];

            if (password_verify($contrasena, $passDB)) {
                $_SESSION['user'] = $existeUsuario['usu_nombre'];
                $_SESSION['codigo'] = $codigo;
                $_SESSION['usuario_id'] = $existeUsuario['usu_id'];
                $_SESSION['rol'] = 'USER';

                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Usuario iniciado exitosamente'
                ]);
            } else {
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'La contraseña que ingreso es incorrecta'
                ]);
            }

        } catch (Exception $e) {
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al intentar ingresar',
                'detalle' => $e->getMessage()
            ]);
        }
        
        exit;
    }

    public static function logout(){
        if (session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION = [];
        session_destroy();
        header("Location: /app03_jmp/");
        exit;
    }
}