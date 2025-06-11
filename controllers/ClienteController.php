<?php

namespace Controllers;

use MVC\Router;
use Model\ActiveRecord;
use Model\Clientes;
use PDO;
use Exception;

class ClienteController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        $router->render('clientes/index', []);
    }

    private static function responder($codigo, $mensaje, $data = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code($codigo === 1 ? 200 : 400);
        
        $respuesta = ['codigo' => $codigo, 'mensaje' => $mensaje];
        if ($data !== null) $respuesta['data'] = $data;
        
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }

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

        if (!preg_match('/^[2-7]/', $datos['cliente_telefono'])) {
            return 'El teléfono debe comenzar con un dígito válido (2-7)';
        }
        
        if (!empty($datos['cliente_correo']) && !filter_var($datos['cliente_correo'], FILTER_VALIDATE_EMAIL)) {
            return 'El correo electrónico no es válido';
        }

        if (!empty($datos['cliente_nit'])) {
            $cliente = new Clientes();
            if (!$cliente->validarNIT($datos['cliente_nit'])) {
                return 'El NIT ingresado no es válido';
            }
        }
        
        return null;
    }

    private static function verificarDuplicados($datos, $excluirId = null)
    {
        if (!empty($datos['cliente_telefono'])) {
            $clienteExistente = Clientes::buscarPorTelefono($datos['cliente_telefono']);
            if ($clienteExistente && (!$excluirId || $clienteExistente['cliente_id'] != $excluirId)) {
                return 'Ya existe un cliente con ese número de teléfono';
            }
        }

        if (!empty($datos['cliente_correo'])) {
            $clienteExistente = Clientes::buscarPorCorreo($datos['cliente_correo']);
            if ($clienteExistente && (!$excluirId || $clienteExistente['cliente_id'] != $excluirId)) {
                return 'Ya existe un cliente con ese correo electrónico';
            }
        }

        if (!empty($datos['cliente_nit'])) {
            $clienteExistente = Clientes::buscarPorNIT($datos['cliente_nit']);
            if ($clienteExistente && (!$excluirId || $clienteExistente['cliente_id'] != $excluirId)) {
                return 'Ya existe un cliente con ese NIT';
            }
        }

        return null;
    }

    private static function limpiarDatos($datos)
    {
        return [
            'cliente_nombres' => ucwords(strtolower(trim(htmlspecialchars($datos['cliente_nombres'])))),
            'cliente_apellidos' => ucwords(strtolower(trim(htmlspecialchars($datos['cliente_apellidos'])))),
            'cliente_nit' => trim(htmlspecialchars($datos['cliente_nit'] ?? '')),
            'cliente_telefono' => filter_var($datos['cliente_telefono'], FILTER_SANITIZE_NUMBER_INT),
            'cliente_correo' => filter_var($datos['cliente_correo'] ?? '', FILTER_SANITIZE_EMAIL),
            'cliente_direccion' => trim(htmlspecialchars($datos['cliente_direccion'] ?? '')),
            'cliente_situacion' => 1
        ];
    }

    public static function guardarAPI()
    {
        getHeadersApi();

        $error = self::validarCliente($_POST);
        if ($error) {
            self::responder(0, $error);
        }

        $error = self::verificarDuplicados($_POST);
        if ($error) {
            self::responder(0, $error);
        }

        try {
            $datosLimpios = self::limpiarDatos($_POST);
            $cliente = new Clientes($datosLimpios);
            
            $errores = $cliente->validar();
            if (!empty($errores)) {
                self::responder(0, implode(', ', $errores));
            }

            $cliente->crear();
            self::responder(1, 'Cliente guardado exitosamente');
        } catch (Exception $e) {
            self::responder(0, 'Error al guardar el cliente: ' . $e->getMessage());
        }
    }

    public static function buscarAPI()
    {
        getHeadersApi();
        
        try {
            $consulta = "SELECT * FROM clientes WHERE cliente_situacion = 1 ORDER BY cliente_nombres";
            $resultado = self::SQL($consulta);
            
            $clientes = [];
            while ($fila = $resultado->fetch(PDO::FETCH_ASSOC)) {
                $clientes[] = $fila;
            }

            if (count($clientes) > 0) {
                self::responder(1, 'Clientes encontrados', $clientes);
            } else {
                self::responder(0, 'No hay clientes disponibles', []);
            }
        } catch (Exception $e) {
            self::responder(0, 'Error de conexión: ' . $e->getMessage());
        }
    }

    public static function modificarAPI()
    {
        getHeadersApi();

        if (empty($_POST['cliente_id']) || !is_numeric($_POST['cliente_id'])) {
            self::responder(0, 'ID de cliente requerido y debe ser numérico');
        }

        $error = self::validarCliente($_POST);
        if ($error) {
            self::responder(0, $error);
        }

        $error = self::verificarDuplicados($_POST, $_POST['cliente_id']);
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
            
            $errores = $cliente->validar();
            if (!empty($errores)) {
                self::responder(0, implode(', ', $errores));
            }

            $cliente->actualizar();
            self::responder(1, 'Cliente actualizado exitosamente');
        } catch (Exception $e) {
            self::responder(0, 'Error al actualizar el cliente: ' . $e->getMessage());
        }
    }

    public static function eliminarAPI()
    {
        getHeadersApi();

        if (empty($_POST['cliente_id']) || !is_numeric($_POST['cliente_id'])) {
            self::responder(0, 'ID de cliente requerido y debe ser numérico');
        }

        try {
            $id = filter_var($_POST['cliente_id'], FILTER_SANITIZE_NUMBER_INT);
            
            $cliente = Clientes::find($id);
            if (!$cliente) {
                self::responder(0, 'Cliente no encontrado');
            }

            // Verificar si el cliente tiene ventas o reparaciones asociadas
            $verificarVentas = "SELECT COUNT(*) as total FROM ventas WHERE cliente_id = $id";
            $resultadoVentas = self::SQL($verificarVentas);
            $ventas = $resultadoVentas->fetch();

            if ($ventas['total'] > 0) {
                self::responder(0, 'No se puede eliminar el cliente porque tiene ventas o reparaciones asociadas');
            }

            $sql = "UPDATE clientes SET cliente_situacion = 0 WHERE cliente_id = $id";
            self::SQL($sql);

            self::responder(1, 'El cliente ha sido eliminado correctamente');
        } catch (Exception $e) {
            self::responder(0, 'Error al eliminar el cliente: ' . $e->getMessage());
        }
    }

    public static function buscarPorTelefonoAPI()
    {
        getHeadersApi();

        if (empty($_POST['telefono'])) {
            self::responder(0, 'Número de teléfono requerido');
        }

        try {
            $cliente = Clientes::buscarPorTelefono($_POST['telefono']);
            
            if ($cliente) {
                self::responder(1, 'Cliente encontrado', $cliente);
            } else {
                self::responder(0, 'Cliente no encontrado');
            }
        } catch (Exception $e) {
            self::responder(0, 'Error en la búsqueda: ' . $e->getMessage());
        }
    }
}