<?php

namespace Model;

use Model\ActiveRecord;

class Clientes extends ActiveRecord
{
    public static $tabla = 'clientes';
    public static $idTabla = 'cliente_id';
    public static $columnasDB = [
        'cliente_id',
        'cliente_nombres',
        'cliente_apellidos',
        'cliente_nit',
        'cliente_telefono',
        'cliente_correo',
        'cliente_direccion',
        'cliente_situacion'
    ];

    public $cliente_id;
    public $cliente_nombres;
    public $cliente_apellidos;
    public $cliente_nit;
    public $cliente_telefono;
    public $cliente_correo;
    public $cliente_direccion;
    public $cliente_situacion;

    public function __construct($cliente = [])
    {
        $this->cliente_id = $cliente['cliente_id'] ?? null;
        $this->cliente_nombres = $cliente['cliente_nombres'] ?? '';
        $this->cliente_apellidos = $cliente['cliente_apellidos'] ?? '';
        $this->cliente_nit = $cliente['cliente_nit'] ?? '';
        $this->cliente_telefono = $cliente['cliente_telefono'] ?? '';
        $this->cliente_correo = $cliente['cliente_correo'] ?? '';
        $this->cliente_direccion = $cliente['cliente_direccion'] ?? '';
        $this->cliente_situacion = $cliente['cliente_situacion'] ?? 1;
    }
}