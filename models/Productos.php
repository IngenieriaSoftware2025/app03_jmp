<?php

namespace Model;

use Model\ActiveRecord;
use PDO;

class Productos extends ActiveRecord
{
    public static $tabla = 'productos';
    public static $idTabla = 'producto_id';
    public static $columnasDB = [
        'producto_id',
        'marca_id',
        'nombre_producto',
        'tipo_producto',
        'modelo',
        'precio_compra',
        'precio_venta',
        'stock_actual',
        'stock_minimo',
        'descripcion',
        'fecha_creacion',
        'situacion'
    ];

    public $producto_id;
    public $marca_id;
    public $nombre_producto;
    public $tipo_producto;
    public $modelo;
    public $precio_compra;
    public $precio_venta;
    public $stock_actual;
    public $stock_minimo;
    public $descripcion;
    public $fecha_creacion;
    public $situacion;

    public function __construct($producto = [])
    {
        $this->producto_id = $producto['producto_id'] ?? null;
        $this->marca_id = $producto['marca_id'] ?? null;
        $this->nombre_producto = $producto['nombre_producto'] ?? '';
        $this->tipo_producto = $producto['tipo_producto'] ?? '';
        $this->modelo = $producto['modelo'] ?? '';
        $this->precio_compra = $producto['precio_compra'] ?? 0;
        $this->precio_venta = $producto['precio_venta'] ?? 0;
        $this->stock_actual = $producto['stock_actual'] ?? 0;
        $this->stock_minimo = $producto['stock_minimo'] ?? 0;
        $this->descripcion = $producto['descripcion'] ?? '';
        $this->fecha_creacion = $producto['fecha_creacion'] ?? null;
        $this->situacion = $producto['situacion'] ?? 1;
    }

    public function validar()
    {
        $errores = [];

        if (empty($this->nombre_producto)) {
            $errores[] = 'El nombre del producto es obligatorio';
        }

        if (empty($this->marca_id) || !is_numeric($this->marca_id)) {
            $errores[] = 'Debe seleccionar una marca válida';
        }

        if (empty($this->tipo_producto) || !in_array($this->tipo_producto, ['celular', 'repuesto', 'servicio'])) {
            $errores[] = 'Debe seleccionar un tipo de producto válido';
        }

        if (!is_numeric($this->precio_venta) || $this->precio_venta <= 0) {
            $errores[] = 'El precio de venta debe ser mayor a 0';
        }

        if (!is_numeric($this->precio_compra) || $this->precio_compra < 0) {
            $errores[] = 'El precio de compra debe ser mayor o igual a 0';
        }

        if ($this->precio_venta <= $this->precio_compra) {
            $errores[] = 'El precio de venta debe ser mayor al precio de compra';
        }

        if (!is_numeric($this->stock_actual) || $this->stock_actual < 0) {
            $errores[] = 'El stock actual debe ser mayor o igual a 0';
        }

        if (!is_numeric($this->stock_minimo) || $this->stock_minimo < 0) {
            $errores[] = 'El stock mínimo debe ser mayor o igual a 0';
        }

        return $errores;
    }

    // Método para obtener productos con información de marca
    public static function productosConMarca()
    {
        $sql = "SELECT p.*, m.marca_nombre 
                FROM productos p 
                LEFT JOIN marcas m ON p.marca_id = m.marca_id 
                WHERE p.situacion = 1 
                ORDER BY p.nombre_producto";
        return static::fetchArray($sql);
    }

// Verificar si el producto tiene ventas asociadas - CORREGIDO
public static function tieneVentas($productoId)
{
    $sql = "SELECT COUNT(*) as total FROM detalle_ventas WHERE producto_id = $productoId";
    $resultado = static::SQL($sql);
    
    // CORREGIDO: Usar fetch(PDO::FETCH_ASSOC) y verificar que existe
    $data = $resultado->fetch(PDO::FETCH_ASSOC);
    
    // DEBUG: Ver qué devuelve la consulta
    error_log("Verificación de ventas para producto $productoId: " . print_r($data, true));
    
    // CORREGIDO: Verificar que $data no sea null y tenga la clave correcta
    if ($data && isset($data['total'])) {
        return $data['total'] > 0;
    } else if ($data && isset($data['TOTAL'])) {
        // Si viene en MAYÚSCULAS desde Informix
        return $data['TOTAL'] > 0;
    } else {
        // Si no hay datos, asumir que no tiene ventas
        return false;
    }
}

    // Productos con stock bajo
    public static function stockBajo()
    {
        $sql = "SELECT p.*, m.marca_nombre 
                FROM productos p 
                LEFT JOIN marcas m ON p.marca_id = m.marca_id 
                WHERE p.situacion = 1 AND p.stock_actual <= p.stock_minimo 
                ORDER BY p.stock_actual ASC";
        return static::fetchArray($sql);
    }

    // Buscar productos por nombre
    public static function buscarPorNombre($nombre, $excluirId = null)
    {
        $sql = "SELECT * FROM productos WHERE nombre_producto = '$nombre' AND situacion = 1";
        if ($excluirId) {
            $sql .= " AND producto_id != $excluirId";
        }
        $resultado = static::SQL($sql);
        return $resultado->fetch();
    }

    public function estaActivo()
    {
        return $this->situacion == 1;
    }

    public function tieneStockBajo()
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    public function calcularGanancia()
    {
        return $this->precio_venta - $this->precio_compra;
    }

    public function calcularPorcentajeGanancia()
    {
        if ($this->precio_compra == 0) return 0;
        return (($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100;
    }
}