<?php

namespace Controllers;

use MVC\Router;
use Model\ActiveRecord;
use PDO;
use Exception;

class ReparacionController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        session_start();
        if(!isset($_SESSION['nombre'])) {
            header("Location: ./");
            exit;
        }
        $router->render('reparaciones/index', [], 'layouts/menu');
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

    public static function guardarAPI()
    {
        isAuthApi();
        
        try {
            if (empty($_POST['cliente_id']) || empty($_POST['descripcion'])) {
                self::responder(0, 'Cliente y descripción del problema son requeridos');
                return;
            }
            
            $clienteId = filter_var($_POST['cliente_id'], FILTER_SANITIZE_NUMBER_INT);
            $descripcion = trim(htmlspecialchars($_POST['descripcion']));
            $equipoMarca = trim(htmlspecialchars($_POST['equipo_marca'] ?? ''));
            $equipoModelo = trim(htmlspecialchars($_POST['equipo_modelo'] ?? ''));
            $costoEstimado = filter_var($_POST['costo_estimado'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            
            $datosReparacion = [
                'cliente_id' => $clienteId,
                'total' => $costoEstimado,
                'tipo_venta' => 'reparacion',
                'descripcion' => "Equipo: $equipoMarca $equipoModelo - Problema: $descripcion",
                'situacion' => 1
            ];
            
            $sql = "INSERT INTO ventas (cliente_id, total, tipo_venta, descripcion, situacion) 
                    VALUES ($clienteId, $costoEstimado, 'reparacion', " . 
                    self::$db->quote($datosReparacion['descripcion']) . ", 1)";
            
            $resultado = self::$db->exec($sql);
            
            if ($resultado >= 0) {
                self::responder(1, 'Reparación registrada exitosamente');
            } else {
                self::responder(0, 'Error al registrar la reparación');
            }
        } catch (Exception $e) {
            self::responder(0, 'Error al guardar reparación: ' . $e->getMessage());
        }
    }

    public static function buscarAPI()
    {
        isAuthApi();
        
        try {
            $sql = "SELECT v.*, c.cliente_nombres, c.cliente_apellidos, c.cliente_telefono 
                    FROM ventas v 
                    INNER JOIN clientes c ON v.cliente_id = c.cliente_id 
                    WHERE v.tipo_venta = 'reparacion' AND v.situacion = 1 
                    ORDER BY v.fecha_venta DESC";
            
            $reparaciones = self::fetchArray($sql);
            
            if (count($reparaciones) > 0) {
                self::responder(1, 'Reparaciones encontradas', $reparaciones);
            } else {
                self::responder(1, 'No hay reparaciones registradas', []);
            }
        } catch (Exception $e) {
            self::responder(0, 'Error al buscar reparaciones: ' . $e->getMessage());
        }
    }

    public static function actualizarEstadoAPI()
    {
        isAuthApi();
        
        try {
            if (empty($_POST['reparacion_id']) || empty($_POST['estado'])) {
                self::responder(0, 'ID de reparación y estado son requeridos');
                return;
            }
            
            $reparacionId = filter_var($_POST['reparacion_id'], FILTER_SANITIZE_NUMBER_INT);
            $estado = trim($_POST['estado']);
            $observaciones = trim($_POST['observaciones'] ?? '');
            
            $sql = "UPDATE ventas 
                    SET descripcion = CONCAT(descripcion, ' - Estado: $estado', 
                        CASE WHEN " . self::$db->quote($observaciones) . " != '' 
                             THEN CONCAT(' - Observaciones: ', " . self::$db->quote($observaciones) . ") 
                             ELSE '' END)
                    WHERE venta_id = $reparacionId AND tipo_venta = 'reparacion'";
            
            $resultado = self::$db->exec($sql);
            
            if ($resultado >= 0) {
                self::responder(1, 'Estado de reparación actualizado');
            } else {
                self::responder(0, 'Error al actualizar estado');
            }
        } catch (Exception $e) {
            self::responder(0, 'Error al actualizar estado: ' . $e->getMessage());
        }
    }

    public static function finalizarAPI()
    {
        isAuthApi();
        
        try {
            if (empty($_POST['reparacion_id']) || empty($_POST['costo_final'])) {
                self::responder(0, 'ID de reparación y costo final son requeridos');
                return;
            }
            
            $reparacionId = filter_var($_POST['reparacion_id'], FILTER_SANITIZE_NUMBER_INT);
            $costoFinal = filter_var($_POST['costo_final'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $observaciones = trim($_POST['observaciones'] ?? '');
            
            $sql = "UPDATE ventas 
                    SET total = $costoFinal,
                        descripcion = CONCAT(descripcion, ' - FINALIZADA - Costo final: Q$costoFinal',
                        CASE WHEN " . self::$db->quote($observaciones) . " != '' 
                             THEN CONCAT(' - ', " . self::$db->quote($observaciones) . ") 
                             ELSE '' END)
                    WHERE venta_id = $reparacionId AND tipo_venta = 'reparacion'";
            
            $resultado = self::$db->exec($sql);
            
            if ($resultado >= 0) {
                self::responder(1, 'Reparación finalizada exitosamente');
            } else {
                self::responder(0, 'Error al finalizar reparación');
            }
        } catch (Exception $e) {
            self::responder(0, 'Error al finalizar reparación: ' . $e->getMessage());
        }
    }

    public static function entregarAPI()
    {
        isAuthApi();
        
        try {
            if (empty($_POST['reparacion_id'])) {
                self::responder(0, 'ID de reparación requerido');
                return;
            }
            
            $reparacionId = filter_var($_POST['reparacion_id'], FILTER_SANITIZE_NUMBER_INT);
            
            $sql = "UPDATE ventas 
                    SET descripcion = CONCAT(descripcion, ' - ENTREGADA')
                    WHERE venta_id = $reparacionId AND tipo_venta = 'reparacion'";
            
            $resultado = self::$db->exec($sql);
            
            if ($resultado >= 0) {
                self::responder(1, 'Reparación marcada como entregada');
            } else {
                self::responder(0, 'Error al marcar como entregada');
            }
        } catch (Exception $e) {
            self::responder(0, 'Error al entregar reparación: ' . $e->getMessage());
        }
    }
}