<?php

namespace Controllers;

use MVC\Router;
use Model\Marcas;
use Exception;

class MarcaController
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('marcas/index', []);
    }

    public static function buscarAPI()
    {
        getHeadersApi();
        
        try {
            $sql = "SELECT * FROM marcas WHERE situacion = 1";
            $marcas = Marcas::fetchArray($sql);
            
            http_response_code(200);
            echo json_encode([
                'codigo' => 1,
                'mensaje' => 'Marcas encontradas',
                'data' => $marcas
            ]);
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error al buscar marcas: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public static function guardarAPI()
    {
        getHeadersApi();
        
        try {
            $_POST['marca_nombre'] = ucwords(strtolower(trim(htmlspecialchars($_POST['marca_nombre']))));
            $_POST['marca_descripcion'] = ucfirst(strtolower(trim(htmlspecialchars($_POST['marca_descripcion']))));
            
            if(strlen($_POST['marca_nombre']) < 2) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'El nombre de la marca debe tener al menos 2 caracteres'
                ]);
                exit;
            }
            
            $sql = "SELECT COUNT(*) as total FROM marcas WHERE marca_nombre = '" . $_POST['marca_nombre'] . "'";
            $resultado = Marcas::SQL($sql);
            $fila = $resultado->fetch();
            
            if($fila['total'] > 0) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Esta marca ya existe'
                ]);
                exit;
            }
            
            $_POST['situacion'] = 1;
            
            $marca = new Marcas($_POST);
            $resultado = $marca->crear();
            
            if($resultado['resultado'] == 1) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Marca guardada correctamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al guardar la marca'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public static function modificarAPI()
    {
        getHeadersApi();
        
        try {
            $_POST['marca_nombre'] = ucwords(strtolower(trim(htmlspecialchars($_POST['marca_nombre']))));
            $_POST['marca_descripcion'] = ucfirst(strtolower(trim(htmlspecialchars($_POST['marca_descripcion']))));
            
            $marca = Marcas::find($_POST['marca_id']);
            
            if(!$marca) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'La marca no existe'
                ]);
                exit;
            }
            
            $marca->sincronizar($_POST);
            $resultado = $marca->actualizar();
            
            if($resultado['resultado'] == 1) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Marca modificada correctamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al modificar la marca'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }

    public static function eliminarAPI()
    {
        getHeadersApi();
        
        try {
            $marca = Marcas::find($_POST['marca_id']);
            
            if(!$marca) {
                http_response_code(400);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'La marca no existe'
                ]);
                exit;
            }
            
            $marca->sincronizar(['situacion' => 0]);
            $resultado = $marca->actualizar();
            
            if($resultado['resultado'] == 1) {
                http_response_code(200);
                echo json_encode([
                    'codigo' => 1,
                    'mensaje' => 'Marca eliminada correctamente'
                ]);
            } else {
                http_response_code(500);
                echo json_encode([
                    'codigo' => 0,
                    'mensaje' => 'Error al eliminar la marca'
                ]);
            }
        } catch (Exception $e) {
            http_response_code(500);
            echo json_encode([
                'codigo' => 0,
                'mensaje' => 'Error: ' . $e->getMessage()
            ]);
        }
        exit;
    }
}