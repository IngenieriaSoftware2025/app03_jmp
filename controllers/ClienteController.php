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
        session_start();
        if(!isset($_SESSION['nombre'])) {
            header("Location: ./");
            exit;
        }
        $router->render('clientes/index', [], 'layouts/menu');
    }

    private static function responder($codigo, $mensaje, $data = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);

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
            if (!self::validarNIT($datos['cliente_nit'])) {
                return 'El NIT ingresado no es válido';
            }
        }

        return null;
    }

    public static function validarNIT($nit)
    {
        if (preg_match('/^(\d+)-?([\dkK])$/', $nit, $matches)) {
            $numero = $matches[1];
            $digitoVerificador = strtolower($matches[2]) === 'k' ? 10 : intval($matches[2]);
            
            $suma = 0;
            $factor = strlen($numero) + 1;
            
            for ($i = 0; $i < strlen($numero); $i++) {
                $suma += intval($numero[$i]) * $factor;
                $factor--;
            }
            
            $digitoCalculado = (11 - ($suma % 11)) % 11;
            return $digitoCalculado === $digitoVerificador;
        }
        return false;
    }

    private static function verificarDuplicados($datos, $excluirId = null)
    {
        if (!empty($datos['cliente_telefono'])) {
            $clienteExistente = self::buscarPorTelefono($datos['cliente_telefono']);
            if ($clienteExistente && (!$excluirId || $clienteExistente['CLIENTE_ID'] != $excluirId)) {
                return 'Ya existe un cliente con ese número de teléfono';
            }
        }

        if (!empty($datos['cliente_correo'])) {
            $clienteExistente = self::buscarPorCorreo($datos['cliente_correo']);
            if ($clienteExistente && (!$excluirId || $clienteExistente['CLIENTE_ID'] != $excluirId)) {
                return 'Ya existe un cliente con ese correo electrónico';
            }
        }

        if (!empty($datos['cliente_nit'])) {
            $clienteExistente = self::buscarPorNIT($datos['cliente_nit']);
            if ($clienteExistente && (!$excluirId || $clienteExistente['CLIENTE_ID'] != $excluirId)) {
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

    public static function clientesActivos()
    {
        $query = "SELECT * FROM clientes WHERE cliente_situacion = 1 ORDER BY cliente_nombres";
        return self::fetchArray($query);
    }

    public static function buscarPorTelefono($telefono)
    {
        $sql = "SELECT * FROM clientes WHERE cliente_telefono = '$telefono' AND cliente_situacion = 1";
        $resultado = self::SQL($sql);
        $data = $resultado->fetch(PDO::FETCH_ASSOC);
        return $data;
    }

    public static function buscarPorCorreo($correo)
    {
        $sql = "SELECT * FROM clientes WHERE cliente_correo = '$correo' AND cliente_situacion = 1";
        $resultado = self::SQL($sql);
        $data = $resultado->fetch(PDO::FETCH_ASSOC);
        return $data;
    }

    public static function buscarPorNIT($nit)
    {
        $sql = "SELECT * FROM clientes WHERE cliente_nit = '$nit' AND cliente_situacion = 1";
        $resultado = self::SQL($sql);
        $data = $resultado->fetch(PDO::FETCH_ASSOC);
        return $data;
    }

    public static function buscarConFiltros($filtros)
    {
        $sql = "SELECT * FROM clientes WHERE cliente_situacion = 1";
        
        $condiciones = [];
        
        if (!empty($filtros['busqueda'])) {
            $busqueda = $filtros['busqueda'];
            $condiciones[] = "(cliente_nombres LIKE '%$busqueda%' OR cliente_apellidos LIKE '%$busqueda%' OR cliente_telefono LIKE '%$busqueda%')";
        }
        
        if (!empty($condiciones)) {
            $sql .= " AND " . implode(" AND ", $condiciones);
        }
        
        $sql .= " ORDER BY cliente_nombres";
        
        return self::fetchArray($sql);
    }

    public static function estadisticasClientes()
    {
        $stats = [];
        
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE cliente_situacion = 1";
        $resultado = self::SQL($sql);
        $data = $resultado->fetch(PDO::FETCH_ASSOC);
        $stats['total_activos'] = $data['total'] ?? $data['TOTAL'] ?? 0;
        
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE cliente_situacion = 1 AND cliente_nit IS NOT NULL AND cliente_nit != ''";
        $resultado = self::SQL($sql);
        $data = $resultado->fetch(PDO::FETCH_ASSOC);
        $stats['con_nit'] = $data['total'] ?? $data['TOTAL'] ?? 0;
        
        $sql = "SELECT COUNT(*) as total FROM clientes WHERE cliente_situacion = 1 AND cliente_correo IS NOT NULL AND cliente_correo != ''";
        $resultado = self::SQL($sql);
        $data = $resultado->fetch(PDO::FETCH_ASSOC);
        $stats['con_correo'] = $data['total'] ?? $data['TOTAL'] ?? 0;
        
        return $stats;
    }

    public static function tieneVentas($clienteId)
    {
        $sql = "SELECT COUNT(*) as total FROM ventas WHERE cliente_id = $clienteId";
        $resultado = self::SQL($sql);
        
        $data = $resultado->fetch(PDO::FETCH_ASSOC);
        
        if ($data && isset($data['total'])) {
            return $data['total'] > 0;
        } else if ($data && isset($data['TOTAL'])) {
            return $data['TOTAL'] > 0;
        } else {
            return false;
        }
    }

    public static function guardarAPI()
    {
        isAuthApi();

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

            $resultado = $cliente->crear();
            if ($resultado['resultado'] >= 0) {
                self::responder(1, 'Cliente guardado exitosamente');
            } else {
                self::responder(0, 'Error al guardar el cliente');
            }
        } catch (Exception $e) {
            error_log("Error en guardarAPI clientes: " . $e->getMessage());
            self::responder(0, 'Error al guardar el cliente: ' . $e->getMessage());
        }
    }

    public static function buscarAPI()
    {
        isAuthApi();

        try {
            $clientes = self::clientesActivos();

            if (count($clientes) > 0) {
                self::responder(1, 'Clientes encontrados', $clientes);
            } else {
                self::responder(1, 'No hay clientes disponibles', []);
            }
        } catch (Exception $e) {
            error_log("Error en buscarAPI clientes: " . $e->getMessage());
            self::responder(1, 'No hay clientes disponibles - Tabla no inicializada', []);
        }
    }

    public static function buscarFiltradoAPI()
    {
        isAuthApi();
        
        try {
            $filtros = [
                'busqueda' => $_POST['busqueda'] ?? ''
            ];
            
            $clientes = self::buscarConFiltros($filtros);
            
            if (count($clientes) > 0) {
                self::responder(1, "Clientes filtrados encontrados", $clientes);
            } else {
                self::responder(1, 'No hay clientes con esos filtros', []);
            }
        } catch (Exception $e) {
            error_log("Error en buscarFiltradoAPI: " . $e->getMessage());
            self::responder(0, 'Error al filtrar clientes: ' . $e->getMessage());
        }
    }

    public static function estadisticasAPI()
    {
        isAuthApi();
        
        try {
            $stats = self::estadisticasClientes();
            self::responder(1, 'Estadísticas obtenidas', $stats);
        } catch (Exception $e) {
            error_log("Error en estadisticasAPI: " . $e->getMessage());
            self::responder(0, 'Error al obtener estadísticas: ' . $e->getMessage());
        }
    }

    public static function modificarAPI()
    {
        isAuthApi();

        if (empty($_POST['cliente_id']) || !is_numeric($_POST['cliente_id'])) {
            self::responder(0, 'ID de cliente requerido y debe ser numérico');
            return;
        }

        $error = self::validarCliente($_POST);
        if ($error) {
            self::responder(0, $error);
            return;
        }

        $error = self::verificarDuplicados($_POST, $_POST['cliente_id']);
        if ($error) {
            self::responder(0, $error);
            return;
        }

        try {
            $id = filter_var($_POST['cliente_id'], FILTER_SANITIZE_NUMBER_INT);
            $cliente = Clientes::find($id);

            if (!$cliente) {
                self::responder(0, 'Cliente no encontrado');
                return;
            }

            $datosLimpios = self::limpiarDatos($_POST);
            $cliente->sincronizar($datosLimpios);

            $resultado = $cliente->actualizar();
            if ($resultado['resultado'] >= 0) {
                self::responder(1, 'Cliente actualizado exitosamente');
            } else {
                self::responder(0, 'Error al actualizar el cliente');
            }
        } catch (Exception $e) {
            error_log("Error en modificarAPI: " . $e->getMessage());
            self::responder(0, 'Error al actualizar el cliente: ' . $e->getMessage());
        }
    }

    public static function eliminarAPI()
    {
        isAuthApi();

        if (empty($_POST['cliente_id']) || !is_numeric($_POST['cliente_id'])) {
            self::responder(0, 'ID de cliente requerido y debe ser numérico');
            return;
        }

        try {
            $id = filter_var($_POST['cliente_id'], FILTER_SANITIZE_NUMBER_INT);
            
            $cliente = Clientes::find($id);
            if (!$cliente) {
                self::responder(0, 'Cliente no encontrado');
                return;
            }

            try {
                $tieneVentas = self::tieneVentas($id);
                if ($tieneVentas) {
                    self::responder(0, 'No se puede eliminar el cliente porque tiene ventas o reparaciones asociadas');
                    return;
                }
            } catch (Exception $ventasError) {
                error_log("Advertencia: No se pudo verificar ventas para cliente $id: " . $ventasError->getMessage());
            }

            $cliente->sincronizar(['cliente_situacion' => 0]);
            $resultado = $cliente->actualizar();
            
            if ($resultado['resultado'] >= 0) {
                self::responder(1, 'El cliente ha sido eliminado correctamente');
            } else {
                self::responder(0, 'Error al eliminar el cliente');
            }
            
        } catch (Exception $e) {
            error_log("Error en eliminarAPI: " . $e->getMessage());
            self::responder(0, 'Error al eliminar el cliente: ' . $e->getMessage());
        }
    }

    public static function buscarPorTelefonoAPI()
    {
        isAuthApi();

        if (empty($_POST['telefono'])) {
            self::responder(0, 'Número de teléfono requerido');
        }

        try {
            $cliente = self::buscarPorTelefono($_POST['telefono']);

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