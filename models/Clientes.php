<?php

// Crea nombre de espacio Model
namespace Model;

// Importa la clase ActiveRecord del nombre de espacio Model
use Model\ActiveRecord;

// Crea la clase de instancia Clientes y hereda las funciones de ActiveRecord
class Clientes extends ActiveRecord
{
    // Crea las propiedades de la clase
    public static $tabla = 'clientes';
    public static $idTabla = 'cliente_id';
    public static $columnasDB = [
        'cliente_nombres',
        'cliente_apellidos',
        'cliente_nit',
        'cliente_telefono',
        'cliente_correo',
        'cliente_situacion'
    ];

    // Crea las variables para almacenar los datos
    public $cliente_id;
    public $cliente_nombres;
    public $cliente_apellidos;
    public $cliente_nit;
    public $cliente_telefono;
    public $cliente_correo;
    public $cliente_situacion;

    public function __construct($cliente = [])
    {
        $this->cliente_id = $cliente['cliente_id'] ?? null;
        $this->cliente_nombres = $cliente['cliente_nombres'] ?? '';
        $this->cliente_apellidos = $cliente['cliente_apellidos'] ?? '';
        $this->cliente_nit = $cliente['cliente_nit'] ?? '';
        $this->cliente_telefono = $cliente['cliente_telefono'] ?? '';
        $this->cliente_correo = $cliente['cliente_correo'] ?? '';
        $this->cliente_situacion = $cliente['cliente_situacion'] ?? 1;
    }

    // Método para validar los datos del cliente
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

        return $errores;
    }

    // Método para obtener el nombre completo del cliente
    public function getNombreCompleto()
    {
        return trim($this->cliente_nombres . ' ' . $this->cliente_apellidos);
    }

    // Método para verificar si el cliente está activo
    public function estaActivo()
    {
        return $this->cliente_situacion == 1;
    }

    // Método estático para buscar clientes activos
    public static function clientesActivos()
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE cliente_situacion = 1 ORDER BY cliente_nombres";
        return static::fetchArray($query);
    }

    // Método estático para buscar cliente por teléfono
    public static function buscarPorTelefono($telefono)
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE cliente_telefono = ? AND cliente_situacion = 1";
        $resultado = static::fetchArray($query, [$telefono]);
        return !empty($resultado) ? $resultado[0] : null;
    }

    // Método estático para buscar cliente por correo
    public static function buscarPorCorreo($correo)
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE cliente_correo = ? AND cliente_situacion = 1";
        $resultado = static::fetchArray($query, [$correo]);
        return !empty($resultado) ? $resultado[0] : null;
    }

    // En tu modelo Clientes
    public static function all()
    {
        $consulta = "SELECT * FROM " . static::$tabla . " WHERE cliente_situacion = 1 ORDER BY cliente_nombres";
        return static::fetchArray($consulta);
    }
}
