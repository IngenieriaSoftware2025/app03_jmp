<?php

namespace Model;

use Model\ActiveRecord;

class Clientes extends ActiveRecord {

    public static $tabla = 'clientes';
    public static $idTabla = 'cliente_id';
    public static $columnasDB = [

        'nombres',
        'apellidos',
        'nit',
        'telefono',
        'correo',
        'situacion'
       
    ];

    public $cliente_id;
    public $nombres;
    public $apellidos;
    public $nit;
    public $telefono;
    public $correo;
    public $situacion;

    public function __construct($args = []) {

        $this->cliente_id= $args['cliente_id'] ?? null;
        $this->nombres= $args['nombres'] ?? '';
        $this->apellidos= $args['apellidos'] ?? '';
        $this->nit= $args['nit'] ?? '';
        $this->telefono= $args['telefono'] ?? '';
        $this->correo= $args['correo'] ?? '';
        $this->situacion= $args['situacion'] ?? 1;
        
    }

}