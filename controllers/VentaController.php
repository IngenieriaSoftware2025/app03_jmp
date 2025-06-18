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
        // Usar nuestra función de verificación
        verificarAutenticacion();
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
        // Verificar autenticación para API
        verificarAutenticacion(true);
        
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
            error_log("Error en buscarClienteAPI: " . $e->getMessage());
            self::responder(0, 'Error en la búsqueda: ' . $e->getMessage());
        }
    }

    public static function buscarProductosAPI()
    {
        verificarAutenticacion(true);
        
        try {
            // Consulta Informix específica con LEFT JOIN
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
            error_log("Error en buscarProductosAPI: " . $e->getMessage());
            self::responder(0, 'Error al buscar productos: ' . $e->getMessage());
        }
    }

    public static function procesarVentaAPI()
    {
        verificarAutenticacion(true);
        
        try {
            if (empty($_POST['cliente_id']) || empty($_POST['productos']) || empty($_POST['total'])) {
                self::responder(0, 'Datos incompletos para procesar la venta');
                return;
            }
            
            $clienteId = filter_var($_POST['cliente_id'], FILTER_SANITIZE_NUMBER_INT);
            $productos = json_decode($_POST['productos'], true);
            $total = filter_var($_POST['total'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $tipoVenta = $_POST['tipo_venta'] ?? 'venta';
            $descripcion = trim($_POST['descripcion'] ?? '');
            
            // Validar que productos sea un array
            if (!is_array($productos) || empty($productos)) {
                self::responder(0, 'Lista de productos inválida');
                return;
            }
            
            // Validar cliente
            $sqlCliente = "SELECT cliente_id FROM clientes WHERE cliente_id = $clienteId AND cliente_situacion = 1";
            $resultadoCliente = self::SQL($sqlCliente);
            if (!$resultadoCliente->fetch()) {
                self::responder(0, 'Cliente no encontrado o inactivo');
                return;
            }
            
            $datosVenta = [
                'cliente_id' => $clienteId,
                'total' => $total,
                'tipo_venta' => $tipoVenta,
                'descripcion' => $descripcion,
                'situacion' => 1
            ];
            
            // Crear la venta - específico para Informix
            $venta = new Ventas($datosVenta);
            $resultadoVenta = $venta->crear();
            
            if ($resultadoVenta['resultado'] >= 0) {
                $ventaId = $resultadoVenta['id'];
                
                // Procesar los productos de la venta
                foreach ($productos as $producto) {
                    if (!isset($producto['producto_id'], $producto['cantidad'], $producto['precio_unitario'], $producto['subtotal'])) {
                        continue; // Saltar productos con datos incompletos
                    }
                    
                    $detalleVenta = [
                        'venta_id' => $ventaId,
                        'producto_id' => $producto['producto_id'],
                        'cantidad' => $producto['cantidad'],
                        'precio_unitario' => $producto['precio_unitario'],
                        'subtotal' => $producto['subtotal']
                    ];
                    
                    $detalle = new DetalleVentas($detalleVenta);
                    $resultadoDetalle = $detalle->crear();
                    
                    // Solo actualizar stock para productos físicos
                    if (isset($producto['tipo_producto']) && $producto['tipo_producto'] !== 'servicio') {
                        // Actualización de stock - sintaxis específica para Informix
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
            error_log("Error en procesarVentaAPI: " . $e->getMessage());
            self::responder(0, 'Error al procesar venta: ' . $e->getMessage());
        }
    }

    public static function historialAPI()
    {
        verificarAutenticacion(true);
        
        try {
            // Consulta específica para Informix con DATETIME
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
            error_log("Error en historialAPI: " . $e->getMessage());
            self::responder(0, 'Error al obtener historial: ' . $e->getMessage());
        }
    }

    public static function obtenerDetalleVentaAPI()
    {
        verificarAutenticacion(true);
        
        try {
            if (empty($_POST['venta_id'])) {
                self::responder(0, 'ID de venta requerido');
                return;
            }
            
            $ventaId = filter_var($_POST['venta_id'], FILTER_SANITIZE_NUMBER_INT);
            
            // Consulta para obtener detalles de la venta
            $sql = "SELECT dv.*, p.nombre_producto, p.tipo_producto, m.marca_nombre 
                    FROM detalle_ventas dv 
                    INNER JOIN productos p ON dv.producto_id = p.producto_id
                    LEFT JOIN marcas m ON p.marca_id = m.marca_id
                    WHERE dv.venta_id = $ventaId";
                    
            $detalles = self::fetchArray($sql);
            
            // Consulta para obtener datos de la venta
            $sqlVenta = "SELECT v.*, c.cliente_nombres, c.cliente_apellidos, c.cliente_telefono 
                         FROM ventas v 
                         INNER JOIN clientes c ON v.cliente_id = c.cliente_id 
                         WHERE v.venta_id = $ventaId";
                         
            $venta = self::fetchFirst($sqlVenta);
            
            if ($venta) {
                self::responder(1, 'Detalles encontrados', [
                    'venta' => $venta,
                    'detalles' => $detalles
                ]);
            } else {
                self::responder(0, 'Venta no encontrada');
            }
        } catch (Exception $e) {
            error_log("Error en obtenerDetalleVentaAPI: " . $e->getMessage());
            self::responder(0, 'Error al obtener detalle: ' . $e->getMessage());
        }
    }

    public static function anularVentaAPI()
    {
        verificarAutenticacion(true, ['ADMIN']);
        
        try {
            if (empty($_POST['venta_id'])) {
                self::responder(0, 'ID de venta requerido');
                return;
            }
            
            $ventaId = filter_var($_POST['venta_id'], FILTER_SANITIZE_NUMBER_INT);
            
            // Verificar si la venta existe
            $sqlVerificar = "SELECT venta_id, tipo_venta FROM ventas WHERE venta_id = $ventaId AND situacion = 1";
            $resultado = self::SQL($sqlVerificar);
            $venta = $resultado->fetch(PDO::FETCH_ASSOC);
            
            if (!$venta) {
                self::responder(0, 'Venta no encontrada o ya anulada');
                return;
            }
            
            // Si es venta (no reparación), revertir stock
            if ($venta['tipo_venta'] === 'venta') {
                // Obtener los detalles para revertir el stock
                $sqlDetalles = "SELECT dv.producto_id, dv.cantidad, p.tipo_producto 
                                FROM detalle_ventas dv 
                                INNER JOIN productos p ON dv.producto_id = p.producto_id
                                WHERE dv.venta_id = $ventaId";
                $detalles = self::fetchArray($sqlDetalles);
                
                // Revertir el stock de cada producto
                foreach ($detalles as $detalle) {
                    if ($detalle['tipo_producto'] !== 'servicio') {
                        $productoId = $detalle['producto_id'];
                        $cantidad = $detalle['cantidad'];
                        
                        $sqlStock = "UPDATE productos 
                                   SET stock_actual = stock_actual + $cantidad 
                                   WHERE producto_id = $productoId";
                        self::SQL($sqlStock);
                    }
                }
            }
            
            // Anular la venta
            $sqlAnular = "UPDATE ventas SET situacion = 0 WHERE venta_id = $ventaId";
            $resultadoAnular = self::SQL($sqlAnular);
            
            if ($resultadoAnular) {
                self::responder(1, 'Venta anulada exitosamente');
            } else {
                self::responder(0, 'Error al anular la venta');
            }
        } catch (Exception $e) {
            error_log("Error en anularVentaAPI: " . $e->getMessage());
            self::responder(0, 'Error al anular venta: ' . $e->getMessage());
        }
    }

    public static function estadisticasVentasAPI()
    {
        verificarAutenticacion(true);
        
        try {
            // Total de ventas hoy
            $sqlHoy = "SELECT COUNT(*) as total, SUM(total) as monto 
                       FROM ventas 
                       WHERE DATE(fecha_venta) = CURRENT DATE 
                       AND tipo_venta = 'venta' 
                       AND situacion = 1";
            $resultadoHoy = self::SQL($sqlHoy);
            $ventasHoy = $resultadoHoy->fetch(PDO::FETCH_ASSOC);
            
            // Total de ventas este mes
            $sqlMes = "SELECT COUNT(*) as total, SUM(total) as monto 
                       FROM ventas 
                       WHERE MONTH(fecha_venta) = MONTH(CURRENT) 
                       AND YEAR(fecha_venta) = YEAR(CURRENT) 
                       AND tipo_venta = 'venta' 
                       AND situacion = 1";
            $resultadoMes = self::SQL($sqlMes);
            $ventasMes = $resultadoMes->fetch(PDO::FETCH_ASSOC);
            
            // Productos más vendidos
            $sqlProductos = "SELECT p.nombre_producto, m.marca_nombre, p.tipo_producto,
                               SUM(dv.cantidad) as cantidad_vendida, 
                               SUM(dv.subtotal) as total_vendido
                            FROM detalle_ventas dv
                            INNER JOIN productos p ON dv.producto_id = p.producto_id
                            LEFT JOIN marcas m ON p.marca_id = m.marca_id
                            INNER JOIN ventas v ON dv.venta_id = v.venta_id
                            WHERE v.tipo_venta = 'venta' AND v.situacion = 1
                            GROUP BY p.nombre_producto, m.marca_nombre, p.tipo_producto
                            ORDER BY cantidad_vendida DESC
                            LIMIT 5";
            $productosVendidos = self::fetchArray($sqlProductos);
            
            $stats = [
                'ventas_hoy' => [
                    'total' => $ventasHoy['total'] ?? 0,
                    'monto' => $ventasHoy['monto'] ?? 0
                ],
                'ventas_mes' => [
                    'total' => $ventasMes['total'] ?? 0,
                    'monto' => $ventasMes['monto'] ?? 0
                ],
                'productos_vendidos' => $productosVendidos
            ];
            
            self::responder(1, 'Estadísticas obtenidas', $stats);
        } catch (Exception $e) {
            error_log("Error en estadisticasVentasAPI: " . $e->getMessage());
            self::responder(0, 'Error al obtener estadísticas: ' . $e->getMessage());
        }
    }
}