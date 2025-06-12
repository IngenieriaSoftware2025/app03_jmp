<?php

namespace Model;

use Model\ActiveRecord;

class Marcas extends ActiveRecord
{
    public static $tabla = 'marcas';
    public static $idTabla = 'marca_id';
    public static $columnasDB = [
        'marca_id',
        'marca_nombre',
        'marca_descripcion',
        'fecha_creacion',
        'situacion'
    ];

    public $marca_id;
    public $marca_nombre;
    public $marca_descripcion;
    public $fecha_creacion;
    public $situacion;

    public function __construct($marca = [])
    {
        $this->marca_id = $marca['marca_id'] ?? null;
        $this->marca_nombre = $marca['marca_nombre'] ?? '';
        $this->marca_descripcion = $marca['marca_descripcion'] ?? '';
        $this->fecha_creacion = $marca['fecha_creacion'] ?? null;
        $this->situacion = $marca['situacion'] ?? 1;
    }
}