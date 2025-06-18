<?php

namespace Controllers;

use MVC\Router;
use Model\ActiveRecord;
use Model\Ventas;
use Model\DetalleVentas;
use PDO;
use Exception;

class VentaController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        session_start();
        if(!isset($_SESSION['nombre'])) {
            header("Location: ./");
            exit;
        }
        $router->render('ventas/index', [], 'layouts/menu');
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

    public static function buscarClienteAPI()
    {
        isAuthApi();
        
        try {
            if (empty($_POST['telefono'])) {
                self::responder(0, 'Número de teléfono requerido');
                return;
            }
            
            $telefono = filter_var($_POST['telefono'], FILTER_SANITIZE_NUMBER_INT);
            
            $sql = "SELECT * FROM clientes WHERE cliente_telefono = '$telefono' AND cliente_situacion = 1";
            $resultado = self::SQL($sql);
            $cliente = $resultado->fetch(PDO::FETCH_ASSOC);
            
            if ($cliente) {
                self::responder(1, 'Cliente encontrado', $cliente);
            } else {
                self::responder(0, 'Cliente no encontrado');
            }
        } catch (Exception $e) {
            self::responder(0, 'Error en la búsqueda: ' . $e->getMessage());
        }
    }

    public static function buscarProductosAPI()
    {
        isAuthApi();
        
        try {
            $sql = "SELECT p.*, m.marca_nombre 
                    FROM productos p 
                    LEFT JOIN marcas m ON p.marca_id = m.marca_id 
                    WHERE p.situacion = 1 AND p.stock_actual > 0
                    ORDER BY p.nombre_producto";
            
            $productos = self::fetchArray($sql);
            
            if (count($productos) > 0) {
                self::responder(1, 'Productos disponibles encontrados', $productos);
            } else {
                self::responder(1, 'No hay productos disponibles', []);
            }
        } catch (Exception $e) {
            self::responder(0, 'Error al buscar productos: ' . $e->getMessage());
        }
    }

    public static function procesarVentaAPI()
    {
        isAuthApi();
        
        try {
            // Validar datos requeridos
            if (empty($_POST['cliente_id']) || empty($_POST['productos']) || empty($_POST['total'])) {
                self::responder(0, 'Datos incompletos para procesar la venta');
                return;
            }
            
            $clienteId = filter_var($_POST['cliente_id'], FILTER_SANITIZE_NUMBER_INT);
            $productos = json_decode($_POST['productos'], true);
            $total = filter_var($_POST['total'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $tipoVenta = $_POST['tipo_venta'] ?? 'venta';
            $descripcion = trim($_POST['descripcion'] ?? '');
            
            // Crear la venta
            $datosVenta = [
                'cliente_id' => $clienteId,
                'total' => $total,
                'tipo_venta' => $tipoVenta,
                'descripcion' => $descripcion,
                'situacion' => 1
            ];
            
            $venta = new Ventas($datosVenta);
            $resultadoVenta = $venta->crear();
            
            if ($resultadoVenta['resultado'] >= 0) {
                $ventaId = $resultadoVenta['id'];
                
                // Procesar productos de la venta
                foreach ($productos as $producto) {
                    $detalleVenta = [
                        'venta_id' => $ventaId,
                        'producto_id' => $producto['producto_id'],
                        'cantidad' => $producto['cantidad'],
                        'precio_unitario' => $producto['precio_unitario'],
                        'subtotal' => $producto['subtotal']
                    ];
                    
                    $detalle = new DetalleVentas($detalleVenta);
                    $detalle->crear();
                    
                    // Actualizar stock (solo para productos físicos)
                    if ($producto['tipo_producto'] !== 'servicio') {
                        $sqlStock = "UPDATE productos 
                                   SET stock_actual = stock_actual - {$producto['cantidad']} 
                                   WHERE producto_id = {$producto['producto_id']}";
                        self::SQL($sqlStock);
                    }
                }
                
                self::responder(1, 'Venta procesada exitosamente', ['venta_id' => $ventaId]);
            } else {
                self::responder(0, 'Error al procesar la venta');
            }
        } catch (Exception $e) {
            self::responder(0, 'Error al procesar venta: ' . $e->getMessage());
        }
    }

    public static function historialAPI()
    {
        isAuthApi();
        
        try {
            $sql = "SELECT v.*, c.cliente_nombres, c.cliente_apellidos 
                    FROM ventas v 
                    INNER JOIN clientes c ON v.cliente_id = c.cliente_id 
                    WHERE v.situacion = 1 
                    ORDER BY v.fecha_venta DESC 
                    LIMIT 50";
            
            $ventas = self::fetchArray($sql);
            
            if (count($ventas) > 0) {
                self::responder(1, 'Historial de ventas encontrado', $ventas);
            } else {
                self::responder(1, 'No hay ventas registradas', []);
            }
        } catch (Exception $e) {
            self::responder(0, 'Error al obtener historial: ' . $e->getMessage());
        }
    }
}