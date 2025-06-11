<?php

namespace Controllers;

use MVC\Router;
use Model\Marcas;
use Model\ActiveRecord;
use PDO;
use Exception;

class MarcaController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('marcas/index', []);
    }

    // Método responder unificado como en ClienteController
    private static function responder($codigo, $mensaje, $data = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200); // SIEMPRE 200 para que JavaScript procese bien
        
        $respuesta = ['codigo' => $codigo, 'mensaje' => $mensaje];
        if ($data !== null) $respuesta['data'] = $data;
        
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Validar datos de marca
    private static function validarMarca($datos)
    {
        if (empty($datos['marca_nombre']) || strlen(trim($datos['marca_nombre'])) < 2) {
            return 'El nombre de la marca debe tener al menos 2 caracteres';
        }
        
        if (strlen($datos['marca_nombre']) > 50) {
            return 'El nombre de la marca no puede exceder 50 caracteres';
        }
        
        if (!empty($datos['marca_descripcion']) && strlen($datos['marca_descripcion']) > 200) {
            return 'La descripción no puede exceder 200 caracteres';
        }
        
        return null;
    }

    // Verificar duplicados
    private static function verificarDuplicados($datos, $excluirId = null)
    {
        if (!empty($datos['marca_nombre'])) {
            $sql = "SELECT * FROM marcas WHERE marca_nombre = '" . trim($datos['marca_nombre']) . "' AND situacion = 1";
            $resultado = self::SQL($sql);
            $marcaExistente = $resultado->fetch(PDO::FETCH_ASSOC);
            
            if ($marcaExistente && (!$excluirId || $marcaExistente['MARCA_ID'] != $excluirId)) {
                return 'Ya existe una marca con ese nombre';
            }
        }
        
        return null;
    }

    // Limpiar datos
    private static function limpiarDatos($datos)
    {
        return [
            'marca_nombre' => ucwords(strtolower(trim(htmlspecialchars($datos['marca_nombre'])))),
            'marca_descripcion' => ucfirst(strtolower(trim(htmlspecialchars($datos['marca_descripcion'] ?? '')))),
            'situacion' => 1
        ];
    }

    public static function buscarAPI()
    {
        getHeadersApi();
        
        try {
            $sql = "SELECT * FROM marcas WHERE situacion = 1 ORDER BY marca_nombre";
            $marcas = Marcas::fetchArray($sql);
            
            if (count($marcas) > 0) {
                self::responder(1, 'Marcas encontradas', $marcas);
            } else {
                self::responder(0, 'No hay marcas disponibles', []);
            }
        } catch (Exception $e) {
            error_log("Error en buscarAPI marcas: " . $e->getMessage());
            self::responder(0, 'Error al buscar marcas: ' . $e->getMessage());
        }
    }

    public static function guardarAPI()
    {
        getHeadersApi();
        
        try {
            $error = self::validarMarca($_POST);
            if ($error) {
                self::responder(0, $error);
                return;
            }
            
            $error = self::verificarDuplicados($_POST);
            if ($error) {
                self::responder(0, $error);
                return;
            }
            
            $datosLimpios = self::limpiarDatos($_POST);
            $marca = new Marcas($datosLimpios);
            
            $errores = $marca->validar();
            if (!empty($errores)) {
                self::responder(0, implode(', ', $errores));
                return;
            }
            
            $resultado = $marca->crear();
            
            if ($resultado['resultado'] >= 0) {
                self::responder(1, 'Marca guardada correctamente');
            } else {
                self::responder(0, 'Error al guardar la marca');
            }
        } catch (Exception $e) {
            error_log("Error en guardarAPI marcas: " . $e->getMessage());
            self::responder(0, 'Error: ' . $e->getMessage());
        }
    }

    public static function modificarAPI()
    {
        getHeadersApi();
        
        try {
            if (empty($_POST['marca_id']) || !is_numeric($_POST['marca_id'])) {
                self::responder(0, 'ID de marca requerido y debe ser numérico');
                return;
            }
            
            $error = self::validarMarca($_POST);
            if ($error) {
                self::responder(0, $error);
                return;
            }
            
            $error = self::verificarDuplicados($_POST, $_POST['marca_id']);
            if ($error) {
                self::responder(0, $error);
                return;
            }
            
            $id = filter_var($_POST['marca_id'], FILTER_SANITIZE_NUMBER_INT);
            $marca = Marcas::find($id);
            
            if (!$marca) {
                self::responder(0, 'La marca no existe');
                return;
            }
            
            $datosLimpios = self::limpiarDatos($_POST);
            $marca->sincronizar($datosLimpios);
            
            $errores = $marca->validar();
            if (!empty($errores)) {
                self::responder(0, implode(', ', $errores));
                return;
            }
            
            $resultado = $marca->actualizar();
            
            if ($resultado['resultado'] >= 0) {
                self::responder(1, 'Marca modificada correctamente');
            } else {
                self::responder(0, 'Error al modificar la marca');
            }
        } catch (Exception $e) {
            error_log("Error en modificarAPI marcas: " . $e->getMessage());
            self::responder(0, 'Error: ' . $e->getMessage());
        }
    }

    public static function eliminarAPI()
    {
        getHeadersApi();
        
        try {
            if (empty($_POST['marca_id']) || !is_numeric($_POST['marca_id'])) {
                self::responder(0, 'ID de marca requerido y debe ser numérico');
                return;
            }
            
            $id = filter_var($_POST['marca_id'], FILTER_SANITIZE_NUMBER_INT);
            $marca = Marcas::find($id);
            
            if (!$marca) {
                self::responder(0, 'La marca no existe');
                return;
            }
            
            // Verificar si la marca tiene productos asociados
            $verificarProductos = "SELECT COUNT(*) as total FROM productos WHERE marca_id = $id AND situacion = 1";
            $resultadoProductos = self::SQL($verificarProductos);
            $productos = $resultadoProductos->fetch(PDO::FETCH_ASSOC);
            
            if ($productos && isset($productos['total']) && $productos['total'] > 0) {
                self::responder(0, 'No se puede eliminar la marca porque tiene productos asociados');
                return;
            }
            
            $marca->sincronizar(['situacion' => 0]);
            $resultado = $marca->actualizar();
            
            if ($resultado['resultado'] >= 0) {
                self::responder(1, 'Marca eliminada correctamente');
            } else {
                self::responder(0, 'Error al eliminar la marca');
            }
        } catch (Exception $e) {
            error_log("Error en eliminarAPI marcas: " . $e->getMessage());
            self::responder(0, 'Error: ' . $e->getMessage());
        }
    }
}