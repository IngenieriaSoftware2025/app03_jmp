<?php

namespace Model;

use Model\ActiveRecord;

class Ventas extends ActiveRecord
{
    public static $tabla = 'ventas';
    public static $idTabla = 'venta_id';
    public static $columnasDB = [
        'venta_id',
        'cliente_id', 
        'fecha_venta',
        'total',
        'tipo_venta',
        'descripcion',
        'situacion'
    ];

    public $venta_id;
    public $cliente_id;
    public $fecha_venta;
    public $total;
    public $tipo_venta;
    public $descripcion;
    public $situacion;

    public function __construct($venta = [])
    {
        $this->venta_id = $venta['venta_id'] ?? null;
        $this->cliente_id = $venta['cliente_id'] ?? null;
        $this->fecha_venta = $venta['fecha_venta'] ?? null;
        $this->total = $venta['total'] ?? 0;
        $this->tipo_venta = $venta['tipo_venta'] ?? 'venta';
        $this->descripcion = $venta['descripcion'] ?? '';
        $this->situacion = $venta['situacion'] ?? 1;
    }
}