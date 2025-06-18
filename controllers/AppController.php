<?php

namespace Controllers;

use Exception;
use Model\ActiveRecord;
use MVC\Router;

class AppController
{
    public static function index(Router $router)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if(isset($_SESSION['nombre'])) {
            header("Location: ./inicio");
            exit;
        }
        $router->render('login/index', [], 'layouts/login');
    }

    public static function login()
    {
        getHeadersApi();

        try {
            $usuario = filter_var($_POST['usu_codigo'], FILTER_SANITIZE_NUMBER_INT);
            $contrasena = htmlspecialchars($_POST['usu_password']);

            $queryExisteUser = "SELECT usu_id, usu_nombre, usu_password FROM usuario WHERE usu_codigo = $usuario AND usu_situacion = 1";

            $ExisteUsuario = ActiveRecord::fetchArray($queryExisteUser);
            
            if (!empty($ExisteUsuario)) {
                $ExisteUsuario = $ExisteUsuario[0];
                $passDB = $ExisteUsuario['usu_password'];

                if (password_verify($contrasena, $passDB)) {
                    if (session_status() === PHP_SESSION_NONE) {
                        session_start();
                    }

                    $nombreUser = $ExisteUsuario['usu_nombre'];
                    $idUsuario = $ExisteUsuario['usu_id'];

                    $_SESSION['nombre'] = $nombreUser;
                    $_SESSION['codigo'] = $usuario;
                    $_SESSION['usuario_id'] = $idUsuario;
                    $_SESSION['auth_user'] = true; // Para isAuthApi()
                    $_SESSION['login'] = true;     // Para isAuth()

                    $sqlpermisos = "SELECT rol_nombre_ct as permiso FROM permiso 
                                  INNER JOIN rol ON permiso_rol = rol_id 
                                  WHERE permiso_usuario = $idUsuario";

                    $permisos = ActiveRecord::fetchArray($sqlpermisos);

                    foreach ($permisos as $key => $value) {
                       $_SESSION[$value['permiso']] = 1; 
                    }

                    echo json_encode([
                        'codigo' => 1,
                        'mensaje' => 'Usuario logueado exitosamente',
                    ]);
                } else {
                    echo json_encode([
                        'codigo' => 0,
                        'mensaje' => 'La contraseña que ingreso es incorrecta',
                    ]);
                }
            } else {
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El usuario que intenta loguearse NO EXISTE',
                ]);
            }
        } catch (Exception $e) {
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al intentar loguearse',
                'detalle' => $e->getMessage()
            ]);
        }
    }

    public static function logout()
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        $_SESSION = [];
        session_destroy();
        
        $login = $_ENV['APP_NAME'];
        header("Location: /$login");
        exit;
    }

    public static function renderInicio(Router $router)
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        if(!isset($_SESSION['nombre'])) {
            header("Location: ./");
            exit;
        }
        $router->render('pages/index', [], 'layouts/menu');
    }
}