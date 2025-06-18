<?php

namespace Model;

use Model\ActiveRecord;

class Usuario extends ActiveRecord
{
    public static $tabla = 'usuario';
    public static $idTabla = 'usu_id';
    public static $columnasDB = [
        'usu_id',
        'usu_nombre',
        'usu_codigo',
        'usu_password',
        'usu_situacion'
    ];

    public $usu_id;
    public $usu_nombre;
    public $usu_codigo;
    public $usu_password;
    public $usu_situacion;

    public function __construct($usuario = [])
    {
        $this->usu_id = $usuario['usu_id'] ?? null;
        $this->usu_nombre = $usuario['usu_nombre'] ?? '';
        $this->usu_codigo = $usuario['usu_codigo'] ?? null;
        $this->usu_password = $usuario['usu_password'] ?? '';
        $this->usu_situacion = $usuario['usu_situacion'] ?? 1;
    }
}