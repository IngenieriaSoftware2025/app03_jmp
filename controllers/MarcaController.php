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

    private static function responder($codigo, $mensaje, $data = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);
        
        $respuesta = ['codigo' => $codigo, 'mensaje' => $mensaje];
        if ($data !== null) $respuesta['data'] = $data;
        
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }


    
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

    private static function verificarDuplicados($datos, $excluirId = null)
    {
        if (!empty($datos['marca_nombre'])) {
            $sql = "SELECT * FROM marcas WHERE marca_nombre = '" . trim($datos['marca_nombre']) . "' AND situacion = 1";
            $resultado = self::SQL($sql);
            $marcaExistente = $resultado->fetch(PDO::FETCH_ASSOC);
            
            if ($marcaExistente && (!$excluirId || $marcaExistente['marca_id'] != $excluirId)) {
                return 'Ya existe una marca con ese nombre';
            }
        }
        
        return null;
    }

    private static function limpiarDatos($datos)
    {
        return [
            'marca_nombre' => ucwords(strtolower(trim(htmlspecialchars($datos['marca_nombre'])))),
            'marca_descripcion' => ucfirst(strtolower(trim(htmlspecialchars($datos['marca_descripcion'] ?? '')))),
            'situacion' => 1
        ];
    }


    // ========================================

    public static function marcasActivas()
    {
        $query = "SELECT * FROM marcas WHERE situacion = 1 ORDER BY marca_nombre";
        return self::fetchArray($query);
    }

    public static function buscarConFiltros($filtros)
    {
        $sql = "SELECT * FROM marcas WHERE situacion = 1";
        
        $condiciones = [];
        
        if (!empty($filtros['busqueda'])) {
            $busqueda = $filtros['busqueda'];
            $condiciones[] = "(marca_nombre LIKE '%$busqueda%' OR marca_descripcion LIKE '%$busqueda%')";
        }
        
        if (!empty($condiciones)) {
            $sql .= " AND " . implode(" AND ", $condiciones);
        }
        
        $sql .= " ORDER BY marca_nombre";
        
        return self::fetchArray($sql);
    }

    public static function estadisticasMarcas()
    {
        $stats = [];
        
        // Total marcas activas
        $sql = "SELECT COUNT(*) as total FROM marcas WHERE situacion = 1";
        $resultado = self::SQL($sql);
        $data = $resultado->fetch(PDO::FETCH_ASSOC);
        $stats['total_activas'] = $data['total'] ?? $data['TOTAL'] ?? 0;
        
        // Marcas con productos
        $sql = "SELECT COUNT(DISTINCT m.marca_id) as total 
                FROM marcas m 
                INNER JOIN productos p ON m.marca_id = p.marca_id 
                WHERE m.situacion = 1 AND p.situacion = 1";
        $resultado = self::SQL($sql);
        $data = $resultado->fetch(PDO::FETCH_ASSOC);
        $stats['con_productos'] = $data['total'] ?? $data['TOTAL'] ?? 0;
        
        // Marcas sin productos
        $stats['sin_productos'] = $stats['total_activas'] - $stats['con_productos'];
        
        return $stats;
    }

    public static function tieneProductos($marcaId)
    {
        $sql = "SELECT COUNT(*) as total FROM productos WHERE marca_id = $marcaId AND situacion = 1";
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

    public static function marcasPopulares()
    {
        $sql = "SELECT m.*, COUNT(p.producto_id) as total_productos
                FROM marcas m 
                LEFT JOIN productos p ON m.marca_id = p.marca_id AND p.situacion = 1
                WHERE m.situacion = 1 
                GROUP BY m.marca_id, m.marca_nombre, m.marca_descripcion, m.fecha_creacion, m.situacion
                ORDER BY total_productos DESC, m.marca_nombre ASC
                LIMIT 10";
        return self::fetchArray($sql);
    }


    public static function buscarAPI()
    {
        getHeadersApi();
        
        try {
            $marcas = self::marcasActivas();
            
            if (count($marcas) > 0) {
                self::responder(1, 'Marcas encontradas', $marcas);
            } else {
                self::responder(1, 'No hay marcas disponibles', []);
            }
        } catch (Exception $e) {
            error_log("Error en buscarAPI marcas: " . $e->getMessage());
            self::responder(1, 'No hay marcas disponibles - Tabla no inicializada', []);
        }
    }

    // NUEVA FUNCIONALIDAD: BÚSQUEDA CON FILTROS
    public static function buscarFiltradoAPI()
    {
        getHeadersApi();
        
        try {
            $filtros = [
                'busqueda' => $_POST['busqueda'] ?? ''
            ];
            
            $marcas = self::buscarConFiltros($filtros);
            
            if (count($marcas) > 0) {
                self::responder(1, "Marcas filtradas encontradas", $marcas);
            } else {
                self::responder(1, 'No hay marcas con esos filtros', []);
            }
        } catch (Exception $e) {
            error_log("Error en buscarFiltradoAPI: " . $e->getMessage());
            self::responder(0, 'Error al filtrar marcas: ' . $e->getMessage());
        }
    }

    // NUEVA FUNCIONALIDAD: ESTADÍSTICAS
    public static function estadisticasAPI()
    {
        getHeadersApi();
        
        try {
            $stats = self::estadisticasMarcas();
            self::responder(1, 'Estadísticas obtenidas', $stats);
        } catch (Exception $e) {
            error_log("Error en estadisticasAPI: " . $e->getMessage());
            self::responder(0, 'Error al obtener estadísticas: ' . $e->getMessage());
        }
    }

    // NUEVA FUNCIONALIDAD: MARCAS POPULARES
    public static function marcasPopularesAPI()
    {
        getHeadersApi();
        
        try {
            $marcas = self::marcasPopulares();
            
            if (count($marcas) > 0) {
                self::responder(1, 'Marcas populares encontradas', $marcas);
            } else {
                self::responder(1, 'No hay datos de marcas populares', []);
            }
        } catch (Exception $e) {
            error_log("Error en marcasPopularesAPI: " . $e->getMessage());
            self::responder(0, 'Error al obtener marcas populares: ' . $e->getMessage());
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
            try {
                $tieneProductos = self::tieneProductos($id);
                if ($tieneProductos) {
                    self::responder(0, 'No se puede eliminar la marca porque tiene productos asociados');
                    return;
                }
            } catch (Exception $productosError) {
                error_log("Advertencia: No se pudo verificar productos para marca $id: " . $productosError->getMessage());
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