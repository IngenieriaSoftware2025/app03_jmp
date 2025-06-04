<?php

namespace Controllers;

use Exception;
use Model\ActiveRecord;
use Model\Clientes;
use MVC\Router;

class ClienteController extends ActiveRecord {

    public static function renderizarPagina(Router $router) {
        $router->render('clientes/index', []);
    }

    public static function guardarCliente() 
    {
        getHeadersApi();
        echo json_encode($_POST);

        //SANITIZA EL NOMBRE HJACE QUE INGRESE TODO EN INICINAL MAYUCULA
        $_POST['nombres'] = ucwords(strtolower(trim(htmlspecialchars($_POST['nombres']))));

        $cantidad_nombre = strlen($_POST['nombres']);
        if ($cantidad_nombre < 1) {
            http_response_code(400);
            echo json_encode([
                'codigo' =>0,
                'mensaje' => 'Nombre debe tener mas de 1 caracteres'
            ]);
            return;
        }




        $_POST['apellidos'] = ucwords(strtolower(trim(htmlspecialchars($_POST['apellidos']))));

        $cantidad_apellido = strlen($_POST['apellidos']);
        if ($cantidad_apellido < 3) {
          http_response_code(400);
            echo json_encode([
                'codigo' =>0,
                'mensaje' => 'Apellido debe tener mas de 3 caraacteres'
            ]);
            return;
        }


        $_POST['telefono'] = filter_var($_POST['telefono'], FILTER_SANITIZE_NUMBER_INT);
        if (strlen($_POST['telefono']) !=8) {
            http_response_code(400);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Telefono debe tener 8 numeros'
            ]);
            return;
        }
        
      

        $_POST['nit'] = filter_var($_POST['nit'], FILTER_SANITIZE_NUMBER_INT);




        $_POST['correo'] = filter_var($_POST['correo'], FILTER_SANITIZE_EMAIL) ;


        try {
            $cliente = new Clientes(
                [
                    'nombres' => $_POST['nombres'],
                    'apellidos' => $_POST['apellidos'],
                    'nit' => $_POST['nit'],
                    'telefono' => $_POST['telefono'],
                    'correo' => $_POST['telefono'],
                    'situacion' => 1
                ]
            );



            $crear = $cliente ->crear();
            http_response_code(400);
            echo json_encode([
                'codigo' =>1,
                'mensaje' => 'Exito al guardar cliente'
            ]);

        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode([
                'codigo' =>0,
                'mensaje' => 'Error al gaurdar cliente',
                'detalle' => $e->getMessage()
            ]);

        }

    }


    
}


