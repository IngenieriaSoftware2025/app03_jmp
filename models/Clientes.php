<?php

namespace Model;

use Model\ActiveRecord;
use PDO;

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

    public function validar()
    {
        $errores = [];

        if (empty($this->cliente_nombres)) {
            $errores[] = 'El nombre del cliente es obligatorio';
        }

        if (empty($this->cliente_apellidos)) {
            $errores[] = 'Los apellidos del cliente son obligatorios';
        }

        if (empty($this->cliente_telefono)) {
            $errores[] = 'El teléfono es obligatorio';
        } elseif (strlen($this->cliente_telefono) !== 8) {
            $errores[] = 'El teléfono debe tener exactamente 8 dígitos';
        }

        if (!empty($this->cliente_correo) && !filter_var($this->cliente_correo, FILTER_VALIDATE_EMAIL)) {
            $errores[] = 'El formato del correo electrónico no es válido';
        }

        if (!empty($this->cliente_nit) && !$this->validarNIT($this->cliente_nit)) {
            $errores[] = 'El NIT ingresado no es válido';
        }

        return $errores;
    }

    public function validarNIT($nit)
    {
        if (preg_match('/^(\d+)-?([\dkK])$/', $nit, $matches)) {
            $numero = $matches[1];
            $digitoVerificador = strtolower($matches[2]) === 'k' ? 10 : intval($matches[2]);
            
            $suma = 0;
            $factor = strlen($numero) + 1;
            
            for ($i = 0; $i < strlen($numero); $i++) {
                $suma += intval($numero[$i]) * $factor;
                $factor--;
            }
            
            $digitoCalculado = (11 - ($suma % 11)) % 11;
            return $digitoCalculado === $digitoVerificador;
        }
        return false;
    }

    public function getNombreCompleto()
    {
        return trim($this->cliente_nombres . ' ' . $this->cliente_apellidos);
    }

    public function estaActivo()
    {
        return $this->cliente_situacion == 1;
    }

    public static function clientesActivos()
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE cliente_situacion = 1 ORDER BY cliente_nombres";
        return static::fetchArray($query);
    }

   public static function buscarPorTelefono($telefono)
{
    $sql = "SELECT * FROM " . static::$tabla . " WHERE cliente_telefono = '$telefono' AND cliente_situacion = 1";
    $resultado = static::SQL($sql);
    $data = $resultado->fetch(PDO::FETCH_ASSOC);
    
    // DEBUG
    error_log("Buscando por teléfono: $telefono");
    error_log("Resultado: " . print_r($data, true));
    
    return $data;
}

public static function buscarPorCorreo($correo)
{
    $sql = "SELECT * FROM " . static::$tabla . " WHERE cliente_correo = '$correo' AND cliente_situacion = 1";
    $resultado = static::SQL($sql);
    $data = $resultado->fetch(PDO::FETCH_ASSOC);
    
    // DEBUG
    error_log("Buscando por correo: $correo");
    error_log("Resultado: " . print_r($data, true));
    
    return $data;
}

public static function buscarPorNIT($nit)
{
    $sql = "SELECT * FROM " . static::$tabla . " WHERE cliente_nit = '$nit' AND cliente_situacion = 1";
    $resultado = static::SQL($sql);
    $data = $resultado->fetch(PDO::FETCH_ASSOC);
    
    // DEBUG
    error_log("Buscando por NIT: $nit");
    error_log("Resultado: " . print_r($data, true));
    
    return $data;
}
}