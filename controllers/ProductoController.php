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
        $router->render('productos/index', []);
    }

    private static function responder($codigo, $mensaje, $data = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200); // Siempre 200 para evitar errores en JavaScript
        
        $respuesta = ['codigo' => $codigo, 'mensaje' => $mensaje];
        if ($data !== null) $respuesta['data'] = $data;
        
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }

    // ========================================
    // NUEVA FUNCIONALIDAD: VALIDACIÓN DINÁMICA POR TIPO
    // ========================================
    
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
        
        // VALIDACIONES ESPECÍFICAS POR TIPO
        switch ($datos['tipo_producto']) {
            case 'servicio':
                // SERVICIOS: Solo validar descripción
                if (empty($datos['descripcion'])) {
                    return 'Los servicios deben tener una descripción detallada del trabajo a realizar';
                }
                break;
                
            case 'repuesto':
                // REPUESTOS: Validaciones completas + descripción obligatoria
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
                
                // IMPORTANTE: Descripción obligatoria para repuestos
                if (empty($datos['descripcion'])) {
                    return 'Los repuestos deben tener una descripción que especifique compatibilidad y características';
                }
                
                // Modelo opcional para repuestos (pueden ser genéricos)
                break;
                
            case 'celular':
                // CELULARES: Validaciones completas + modelo obligatorio
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
                
                // Modelo obligatorio para celulares
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
            $productoExistente = Productos::buscarPorNombre($datos['nombre_producto'], $excluirId);
            if ($productoExistente) {
                return 'Ya existe un producto con ese nombre';
            }
        }
        
        return null;
    }

    // ========================================
    // FUNCIÓN MEJORADA: LIMPIAR DATOS SEGÚN TIPO
    // ========================================
    
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
        
        // LÓGICA ESPECIAL: Para servicios, forzar valores por defecto
        if ($datos['tipo_producto'] === 'servicio') {
            $datosLimpios['modelo'] = '';
            $datosLimpios['precio_compra'] = 0;
            $datosLimpios['stock_actual'] = 0;
            $datosLimpios['stock_minimo'] = 0;
        }
        
        return $datosLimpios;
    }

    // ========================================
    // MÉTODOS API MEJORADOS
    // ========================================

    public static function buscarAPI()
    {
        ini_set('display_errors', 0);
        getHeadersApi();
        
        try {
            $productos = Productos::productosConMarca();
            
            if (count($productos) > 0) {
                self::responder(1, 'Productos encontrados', $productos);
            } else {
                self::responder(1, 'No hay productos registrados', []);
            }
        } catch (Exception $e) {
            error_log("Error en buscarAPI productos: " . $e->getMessage());
            self::responder(1, 'No hay productos disponibles - Tabla no inicializada', []);
        }
    }

    public static function buscarMarcasAPI()
    {
        ini_set('display_errors', 0);
        getHeadersApi();
        
        try {
            $marcas = Marcas::marcasActivas();
            
            if (count($marcas) > 0) {
                self::responder(1, 'Marcas encontradas', $marcas);
            } else {
                self::responder(0, 'No hay marcas disponibles', []);
            }
        } catch (Exception $e) {
            error_log("Error en buscarMarcasAPI: " . $e->getMessage());
            self::responder(0, 'Error al buscar marcas: ' . $e->getMessage());
        }
    }

    public static function guardarAPI()
    {
        ini_set('display_errors', 0);
        getHeadersApi();

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
            
            $errores = $producto->validar();
            if (!empty($errores)) {
                self::responder(0, implode(', ', $errores));
                return;
            }

            $resultado = $producto->crear();
            
            if ($resultado['resultado'] >= 0) {
                self::responder(1, 'Producto guardado correctamente');
            } else {
                self::responder(0, 'Error al guardar el producto');
            }
        } catch (Exception $e) {
            error_log("Error en guardarAPI productos: " . $e->getMessage());
            self::responder(0, 'Error al guardar el producto: ' . $e->getMessage());
        }
    }

    public static function modificarAPI()
    {
        ini_set('display_errors', 0);
        getHeadersApi();

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
            
            $errores = $producto->validar();
            if (!empty($errores)) {
                self::responder(0, implode(', ', $errores));
                return;
            }

            $resultado = $producto->actualizar();
            
            if ($resultado['resultado'] >= 0) {
                self::responder(1, 'Producto actualizado correctamente');
            } else {
                self::responder(0, 'Error al actualizar el producto');
            }
        } catch (Exception $e) {
            error_log("Error en modificarAPI productos: " . $e->getMessage());
            self::responder(0, 'Error al actualizar el producto: ' . $e->getMessage());
        }
    }

    public static function eliminarAPI()
    {
        ini_set('display_errors', 0);
        getHeadersApi();

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

            // MEJORADO: Verificar ventas con manejo robusto
            try {
                $tieneVentas = Productos::tieneVentas($id);
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
            self::responder(0, 'Error al eliminar el producto: ' . $e->getMessage());
        }
    }

    public static function stockBajoAPI()
    {
        ini_set('display_errors', 0);
        getHeadersApi();
        
        try {
            $productos = Productos::stockBajo();
            
            if (count($productos) > 0) {
                self::responder(1, 'Productos con stock bajo encontrados', $productos);
            } else {
                self::responder(1, 'No hay productos con stock bajo', []);
            }
        } catch (Exception $e) {
            error_log("Error en stockBajoAPI: " . $e->getMessage());
            self::responder(0, 'Error al buscar productos con stock bajo: ' . $e->getMessage());
        }
    }
}