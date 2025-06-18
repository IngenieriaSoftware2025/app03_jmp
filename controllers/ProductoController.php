<?php

namespace Controllers;

use MVC\Router;
use Model\ActiveRecord;
use Model\Productos;
use Model\Marcas;
use PDO;
use Exception;

class ProductoController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        // Usar la nueva función de verificación de autenticación
        verificarAutenticacion();
        $router->render('productos/index', [], 'layouts/menu');
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
    
    private static function validarProducto($datos)
    {
        if (empty($datos['nombre_producto'])) {
            return 'El nombre del producto es obligatorio';
        }
        
        if (empty($datos['marca_id']) || !is_numeric($datos['marca_id'])) {
            return 'Debe seleccionar una marca válida';
        }
        
        if (empty($datos['tipo_producto']) || !in_array($datos['tipo_producto'], ['celular', 'repuesto', 'servicio'])) {
            return 'Debe seleccionar un tipo de producto válido';
        }
        
        if (!is_numeric($datos['precio_venta']) || $datos['precio_venta'] <= 0) {
            return 'El precio de venta debe ser mayor a 0';
        }
        
        switch ($datos['tipo_producto']) {
            case 'servicio':
                if (empty($datos['descripcion'])) {
                    return 'Los servicios deben tener una descripción detallada del trabajo a realizar';
                }
                break;
                
            case 'repuesto':
                if (!is_numeric($datos['precio_compra']) || $datos['precio_compra'] < 0) {
                    return 'El precio de compra debe ser mayor o igual a 0';
                }
                
                if ($datos['precio_venta'] <= $datos['precio_compra']) {
                    return 'El precio de venta debe ser mayor al precio de compra';
                }
                
                if (!is_numeric($datos['stock_actual']) || $datos['stock_actual'] < 0) {
                    return 'El stock actual debe ser mayor o igual a 0';
                }
                
                if (!is_numeric($datos['stock_minimo']) || $datos['stock_minimo'] < 0) {
                    return 'El stock mínimo debe ser mayor o igual a 0';
                }
                
                if (empty($datos['descripcion'])) {
                    return 'Los repuestos deben tener una descripción que especifique compatibilidad y características';
                }
                break;
                
            case 'celular':
                if (!is_numeric($datos['precio_compra']) || $datos['precio_compra'] < 0) {
                    return 'El precio de compra debe ser mayor o igual a 0';
                }
                
                if ($datos['precio_venta'] <= $datos['precio_compra']) {
                    return 'El precio de venta debe ser mayor al precio de compra';
                }
                
                if (!is_numeric($datos['stock_actual']) || $datos['stock_actual'] < 0) {
                    return 'El stock actual debe ser mayor o igual a 0';
                }
                
                if (!is_numeric($datos['stock_minimo']) || $datos['stock_minimo'] < 0) {
                    return 'El stock mínimo debe ser mayor o igual a 0';
                }
                
                if (empty($datos['modelo'])) {
                    return 'El modelo es obligatorio para celulares';
                }
                break;
        }
        
        return null;
    }

    private static function verificarDuplicados($datos, $excluirId = null)
    {
        if (!empty($datos['nombre_producto'])) {
            $productoExistente = self::buscarPorNombre($datos['nombre_producto'], $excluirId);
            if ($productoExistente) {
                return 'Ya existe un producto con ese nombre';
            }
        }
        
        return null;
    }

    private static function limpiarDatos($datos)
    {
        $datosLimpios = [
            'marca_id' => filter_var($datos['marca_id'], FILTER_SANITIZE_NUMBER_INT),
            'nombre_producto' => ucfirst(strtolower(trim(htmlspecialchars($datos['nombre_producto'])))),
            'tipo_producto' => strtolower(trim(htmlspecialchars($datos['tipo_producto']))),
            'modelo' => trim(htmlspecialchars($datos['modelo'] ?? '')),
            'precio_compra' => filter_var($datos['precio_compra'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'precio_venta' => filter_var($datos['precio_venta'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION),
            'stock_actual' => filter_var($datos['stock_actual'] ?? 0, FILTER_SANITIZE_NUMBER_INT),
            'stock_minimo' => filter_var($datos['stock_minimo'] ?? 0, FILTER_SANITIZE_NUMBER_INT),
            'descripcion' => trim(htmlspecialchars($datos['descripcion'] ?? '')),
            'situacion' => 1
        ];
        
        if ($datos['tipo_producto'] === 'servicio') {
            $datosLimpios['modelo'] = 'No aplica';
            $datosLimpios['precio_compra'] = 0;
            $datosLimpios['stock_actual'] = 0;
            $datosLimpios['stock_minimo'] = 0;
        }
        
        return $datosLimpios;
    }

    public static function productosConMarca()
    {
        $sql = "SELECT p.*, m.marca_nombre 
                FROM productos p 
                LEFT JOIN marcas m ON p.marca_id = m.marca_id 
                WHERE p.situacion = 1 
                ORDER BY p.nombre_producto";
        return self::fetchArray($sql);
    }

    public static function buscarConFiltros($filtros)
    {
        $sql = "SELECT p.*, m.marca_nombre 
                FROM productos p 
                LEFT JOIN marcas m ON p.marca_id = m.marca_id 
                WHERE p.situacion = 1";
        
        $condiciones = [];
        
        if (!empty($filtros['tipo'])) {
            $condiciones[] = "p.tipo_producto = '{$filtros['tipo']}'";
        }
        
        if (!empty($filtros['marca'])) {
            $condiciones[] = "p.marca_id = {$filtros['marca']}";
        }
        
        if (!empty($filtros['stock_bajo'])) {
            $condiciones[] = "p.stock_actual <= p.stock_minimo";
        }
        
        if (!empty($condiciones)) {
            $sql .= " AND " . implode(" AND ", $condiciones);
        }
        
        $sql .= " ORDER BY p.nombre_producto";
        
        return self::fetchArray($sql);
    }

    public static function stockBajo()
    {
        $sql = "SELECT p.*, m.marca_nombre 
                FROM productos p 
                LEFT JOIN marcas m ON p.marca_id = m.marca_id 
                WHERE p.situacion = 1 AND p.stock_actual <= p.stock_minimo AND p.tipo_producto != 'servicio'
                ORDER BY p.stock_actual ASC";
        return self::fetchArray($sql);
    }

    public static function stockCritico()
    {
        $sql = "SELECT p.*, m.marca_nombre 
                FROM productos p 
                LEFT JOIN marcas m ON p.marca_id = m.marca_id 
                WHERE p.situacion = 1 AND p.stock_actual = 0 AND p.tipo_producto != 'servicio'
                ORDER BY p.nombre_producto";
        return self::fetchArray($sql);
    }

    public static function buscarPorNombre($nombre, $excluirId = null)
    {
        $sql = "SELECT * FROM productos WHERE nombre_producto = '$nombre' AND situacion = 1";
        if ($excluirId) {
            $sql .= " AND producto_id != $excluirId";
        }
        $resultado = self::SQL($sql);
        return $resultado->fetch();
    }

    public static function tieneVentas($productoId)
    {
        $sql = "SELECT COUNT(*) as total FROM detalle_ventas WHERE producto_id = $productoId";
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

    public static function obtenerAlertas()
    {
        $alertas = [];
        
        $stockBajo = self::stockBajo();
        if (count($stockBajo) > 0) {
            $alertas[] = [
                'tipo' => 'warning',
                'titulo' => 'Stock Bajo',
                'mensaje' => count($stockBajo) . ' productos con stock bajo',
                'cantidad' => count($stockBajo),
                'productos' => array_slice($stockBajo, 0, 5)
            ];
        }
        
        $stockCritico = self::stockCritico();
        if (count($stockCritico) > 0) {
            $alertas[] = [
                'tipo' => 'danger',
                'titulo' => 'Stock Agotado',
                'mensaje' => count($stockCritico) . ' productos sin stock',
                'cantidad' => count($stockCritico),
                'productos' => array_slice($stockCritico, 0, 5)
            ];
        }
        
        return $alertas;
    }

    public static function marcasActivas()
    {
        $query = "SELECT * FROM marcas WHERE situacion = 1 ORDER BY marca_nombre";
        return self::fetchArray($query);
    }

    public static function buscarAPI()
    {
        // Verificar autenticación con la nueva función
        verificarAutenticacion(true);
        
        try {
            $productos = self::productosConMarca();
            
            if (count($productos) > 0) {
                self::responder(1, 'Productos encontrados', $productos);
            } else {
                self::responder(1, 'No hay productos registrados', []);
            }
        } catch (Exception $e) {
            error_log("Error en buscarAPI productos: " . $e->getMessage());
            self::responder(0, 'Error: ' . $e->getMessage());
        }
    }

    public static function buscarFiltradoAPI()
    {
        // Verificar autenticación con la nueva función
        verificarAutenticacion(true);
        
        try {
            $filtros = [
                'tipo' => $_POST['tipo'] ?? '',
                'marca' => $_POST['marca'] ?? '',
                'stock_bajo' => $_POST['stock_bajo'] ?? ''
            ];
            
            $productos = self::buscarConFiltros($filtros);
            
            if (count($productos) > 0) {
                self::responder(1, "Productos filtrados encontrados", $productos);
            } else {
                self::responder(1, 'No hay productos con esos filtros', []);
            }
        } catch (Exception $e) {
            error_log("Error en buscarFiltradoAPI: " . $e->getMessage());
            self::responder(0, 'Error: ' . $e->getMessage());
        }
    }

    public static function alertasStockAPI()
    {
        // Verificar autenticación con la nueva función
        verificarAutenticacion(true);
        
        try {
            $alertas = self::obtenerAlertas();
            
            if (count($alertas) > 0) {
                self::responder(1, 'Alertas de stock encontradas', $alertas);
            } else {
                self::responder(1, 'No hay alertas de stock', []);
            }
        } catch (Exception $e) {
            error_log("Error en alertasStockAPI: " . $e->getMessage());
            self::responder(0, 'Error: ' . $e->getMessage());
        }
    }

    public static function buscarMarcasAPI()
    {
        // Verificar autenticación con la nueva función
        verificarAutenticacion(true);
        
        try {
            $marcas = self::marcasActivas();
            
            if (count($marcas) > 0) {
                self::responder(1, 'Marcas encontradas', $marcas);
            } else {
                self::responder(0, 'No hay marcas disponibles', []);
            }
        } catch (Exception $e) {
            error_log("Error en buscarMarcasAPI: " . $e->getMessage());
            self::responder(0, 'Error: ' . $e->getMessage());
        }
    }

    public static function guardarAPI()
    {
        // Verificar autenticación con la nueva función
        verificarAutenticacion(true);

        $error = self::validarProducto($_POST);
        if ($error) {
            self::responder(0, $error);
            return;
        }

        $error = self::verificarDuplicados($_POST);
        if ($error) {
            self::responder(0, $error);
            return;
        }

        try {
            $datosLimpios = self::limpiarDatos($_POST);
            $producto = new Productos($datosLimpios);
            
            $resultado = $producto->crear();
            
            if ($resultado['resultado'] >= 0) {
                self::responder(1, 'Producto guardado correctamente');
            } else {
                self::responder(0, 'Error al guardar el producto');
            }
        } catch (Exception $e) {
            error_log("Error en guardarAPI productos: " . $e->getMessage());
            self::responder(0, 'Error: ' . $e->getMessage());
        }
    }

    public static function modificarAPI()
    {
        // Verificar autenticación con la nueva función
        verificarAutenticacion(true);

        if (empty($_POST['producto_id']) || !is_numeric($_POST['producto_id'])) {
            self::responder(0, 'ID de producto requerido y debe ser numérico');
            return;
        }

        $error = self::validarProducto($_POST);
        if ($error) {
            self::responder(0, $error);
            return;
        }

        $error = self::verificarDuplicados($_POST, $_POST['producto_id']);
        if ($error) {
            self::responder(0, $error);
            return;
        }

        try {
            $id = filter_var($_POST['producto_id'], FILTER_SANITIZE_NUMBER_INT);
            $producto = Productos::find($id);
            
            if (!$producto) {
                self::responder(0, 'Producto no encontrado');
                return;
            }

            $datosLimpios = self::limpiarDatos($_POST);
            $producto->sincronizar($datosLimpios);
            
            $resultado = $producto->actualizar();
            
            if ($resultado['resultado'] >= 0) {
                self::responder(1, 'Producto actualizado correctamente');
            } else {
                self::responder(0, 'Error al actualizar el producto');
            }
        } catch (Exception $e) {
            error_log("Error en modificarAPI productos: " . $e->getMessage());
            self::responder(0, 'Error: ' . $e->getMessage());
        }
    }

    public static function eliminarAPI()
    {
        // Verificar autenticación con la nueva función
        verificarAutenticacion(true);

        if (empty($_POST['producto_id']) || !is_numeric($_POST['producto_id'])) {
            self::responder(0, 'ID de producto requerido y debe ser numérico');
            return;
        }

        try {
            $id = filter_var($_POST['producto_id'], FILTER_SANITIZE_NUMBER_INT);
            
            $producto = Productos::find($id);
            if (!$producto) {
                self::responder(0, 'Producto no encontrado');
                return;
            }

            try {
                $tieneVentas = self::tieneVentas($id);
                if ($tieneVentas) {
                    self::responder(0, 'No se puede eliminar el producto porque tiene ventas asociadas');
                    return;
                }
            } catch (Exception $ventasError) {
                error_log("Advertencia: No se pudo verificar ventas para producto $id: " . $ventasError->getMessage());
            }

            $producto->sincronizar(['situacion' => 0]);
            $resultado = $producto->actualizar();
            
            if ($resultado['resultado'] >= 0) {
                self::responder(1, 'Producto eliminado correctamente');
            } else {
                self::responder(0, 'Error al eliminar el producto');
            }
        } catch (Exception $e) {
            error_log("Error en eliminarAPI productos: " . $e->getMessage());
            self::responder(0, 'Error: ' . $e->getMessage());
        }
    }

    public static function stockBajoAPI()
    {
        // Verificar autenticación con la nueva función
        verificarAutenticacion(true);
        
        try {
            $productos = self::stockBajo();
            
            if (count($productos) > 0) {
                self::responder(1, 'Productos con stock bajo encontrados', $productos);
            } else {
                self::responder(1, 'No hay productos con stock bajo', []);
            }
        } catch (Exception $e) {
            error_log("Error en stockBajoAPI: " . $e->getMessage());
            self::responder(0, 'Error: ' . $e->getMessage());
        }
    }
}