<?php

namespace Controllers;

use MVC\Router;
use Model\ActiveRecord;
use Model\Clientes;
use Exception;

class ClienteController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('clientes/index', []);
    }

    // Función helper para respuestas JSON
    private static function responder($codigo, $mensaje, $data = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($codigo === 1 ? 200 : 400);
        
        $respuesta = ['codigo' => $codigo, 'mensaje' => $mensaje];
        if ($data !== null) $respuesta['data'] = $data;
        
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // Función helper para validar datos
    private static function validarCliente($datos)
    {
        if (empty($datos['cliente_nombres'])) {
            return 'El nombre del cliente es obligatorio';
        }
        
        if (empty($datos['cliente_apellidos'])) {
            return 'Los apellidos del cliente son obligatorios';
        }
        
        if (empty($datos['cliente_telefono']) || strlen($datos['cliente_telefono']) != 8) {
            return 'El teléfono debe tener 8 dígitos';
        }
        
        if (!empty($datos['cliente_correo']) && !filter_var($datos['cliente_correo'], FILTER_VALIDATE_EMAIL)) {
            return 'El correo electrónico no es válido';
        }
        
        return null; // Sin errores
    }

    // Función helper para limpiar datos
    private static function limpiarDatos($datos)
    {
        return [
            'cliente_nombres' => ucwords(strtolower(trim(htmlspecialchars($datos['cliente_nombres'])))),
            'cliente_apellidos' => ucwords(strtolower(trim(htmlspecialchars($datos['cliente_apellidos'])))),
            'cliente_nit' => htmlspecialchars($datos['cliente_nit'] ?? ''),
            'cliente_telefono' => filter_var($datos['cliente_telefono'], FILTER_SANITIZE_NUMBER_INT),
            'cliente_correo' => filter_var($datos['cliente_correo'] ?? '', FILTER_SANITIZE_EMAIL),
            'cliente_situacion' => 1
        ];
    }

    public static function guardarAPI()
    {
        getHeadersApi();

        // Validar datos
        $error = self::validarCliente($_POST);
        if ($error) {
            self::responder(0, $error);
        }

        try {
            $datosLimpios = self::limpiarDatos($_POST);
            $cliente = new Clientes($datosLimpios);
            $cliente->crear();
            
            self::responder(1, 'Cliente guardado exitosamente');
        } catch (Exception $e) {
            self::responder(0, 'Error al guardar el cliente', $e->getMessage());
        }
    }

    public static function buscarAPI()
    {
        getHeadersApi();
        
        try {
            $consulta = "SELECT * FROM clientes WHERE cliente_situacion = 1 ORDER BY cliente_nombres";
            $clientes = self::fetchArray($consulta);

            if (is_array($clientes) && count($clientes) > 0) {
                self::responder(1, 'Clientes encontrados', $clientes);
            } else {
                self::responder(0, 'No hay clientes disponibles', []);
            }
        } catch (Exception $e) {
            self::responder(0, 'Error de conexión', $e->getMessage());
        }
    }

    public static function modificarAPI()
    {
        getHeadersApi();

        // Validar ID
        if (empty($_POST['cliente_id']) || !is_numeric($_POST['cliente_id'])) {
            self::responder(0, 'ID de cliente requerido y debe ser numérico');
        }

        // Validar datos
        $error = self::validarCliente($_POST);
        if ($error) {
            self::responder(0, $error);
        }

        try {
            $id = filter_var($_POST['cliente_id'], FILTER_SANITIZE_NUMBER_INT);
            $cliente = Clientes::find($id);
            
            if (!$cliente) {
                self::responder(0, 'Cliente no encontrado');
            }

            $datosLimpios = self::limpiarDatos($_POST);
            $cliente->sincronizar($datosLimpios);
            $cliente->actualizar();

            self::responder(1, 'Cliente actualizado exitosamente');
        } catch (Exception $e) {
            self::responder(0, 'Error al actualizar el cliente', $e->getMessage());
        }
    }

    public static function eliminarAPI()
    {
        getHeadersApi();

        // Validar ID
        if (empty($_POST['cliente_id']) || !is_numeric($_POST['cliente_id'])) {
            self::responder(0, 'ID de cliente requerido y debe ser numérico');
        }

        try {
            $id = filter_var($_POST['cliente_id'], FILTER_SANITIZE_NUMBER_INT);
            
            // Verificar si existe
            $cliente = Clientes::find($id);
            if (!$cliente) {
                self::responder(0, 'Cliente no encontrado');
            }

            // Eliminación lógica
            $sql = "UPDATE clientes SET cliente_situacion = 0 WHERE cliente_id = 1";
            self::SQL($sql, [$id]);

            self::responder(1, 'El cliente ha sido eliminado correctamente');
        } catch (Exception $e) {
            self::responder(0, 'Error al eliminar el cliente', $e->getMessage());
        }
    }
}