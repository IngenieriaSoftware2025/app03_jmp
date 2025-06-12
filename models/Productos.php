<?php

namespace Model;

use Model\ActiveRecord;

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
}