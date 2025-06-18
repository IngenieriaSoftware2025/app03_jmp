<?php

namespace Model;

use Model\ActiveRecord;

class DetalleVentas extends ActiveRecord
{
    public static $tabla = 'detalle_ventas';
    public static $idTabla = 'detalle_id';
    public static $columnasDB = [
        'detalle_id',
        'venta_id',
        'producto_id',
        'cantidad', 
        'precio_unitario',
        'subtotal'
    ];

    public $detalle_id;
    public $venta_id;
    public $producto_id;
    public $cantidad;
    public $precio_unitario;
    public $subtotal;

    public function __construct($detalle = [])
    {
        $this->detalle_id = $detalle['detalle_id'] ?? null;
        $this->venta_id = $detalle['venta_id'] ?? null;
        $this->producto_id = $detalle['producto_id'] ?? null;
        $this->cantidad = $detalle['cantidad'] ?? 1;
        $this->precio_unitario = $detalle['precio_unitario'] ?? 0;
        $this->subtotal = $detalle['subtotal'] ?? 0;
    }
}