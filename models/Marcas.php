<?php
// crea nombre de espacio Model
namespace Model;
// Importa la clase ActiveRecord del nombre de espacio Model
use Model\ActiveRecord;
// Crea la clase de instancia Marcas y hereda las funciones de ActiveRecord
class Marcas extends ActiveRecord {
    
    // Crea las propiedades de la clase
    public static $tabla = 'marcas';
    public static $idTabla = 'marca_id';
    
    // CORREGIDO: Usar nombres de campos como están en la BD original
    public static $columnasDB = 
    [
        'marca_id',
        'marca_nombre',
        'marca_descripcion',
        'fecha_creacion',
        'situacion'    // MANTENIDO: como está en la BD original
    ];
    
    // CORREGIDO: Variables como están en la BD
    public $marca_id;
    public $marca_nombre;
    public $marca_descripcion;
    public $fecha_creacion;
    public $situacion;    // MANTENIDO: como está en la BD original
    
    public function __construct($marca = [])
    {
        $this->marca_id = $marca['marca_id'] ?? null;
        $this->marca_nombre = $marca['marca_nombre'] ?? '';
        $this->marca_descripcion = $marca['marca_descripcion'] ?? '';
        $this->fecha_creacion = $marca['fecha_creacion'] ?? null;
        $this->situacion = $marca['situacion'] ?? 1;  // MANTENIDO
    }
    
    // Método para validar los datos de la marca
    public function validar()
    {
        $errores = [];

        if (empty($this->marca_nombre)) {
            $errores[] = 'El nombre de la marca es obligatorio';
        }

        if (strlen($this->marca_nombre) < 2) {
            $errores[] = 'El nombre de la marca debe tener al menos 2 caracteres';
        }

        if (strlen($this->marca_nombre) > 50) {
            $errores[] = 'El nombre de la marca no puede exceder 50 caracteres';
        }

        if (!empty($this->marca_descripcion) && strlen($this->marca_descripcion) > 200) {
            $errores[] = 'La descripción no puede exceder 200 caracteres';
        }

        return $errores;
    }

    // CORREGIDO: Método usando campo 'situacion' como está en BD
    public static function marcasActivas()
    {
        $query = "SELECT * FROM " . static::$tabla . " WHERE situacion = 1 ORDER BY marca_nombre";
        return static::fetchArray($query);
    }

    // Método estático para verificar si existe una marca por nombre
    public static function existePorNombre($nombre, $excluirId = null)
    {
        $sql = "SELECT COUNT(*) as total FROM " . static::$tabla . " WHERE marca_nombre = ?";
        $params = [$nombre];
        
        if ($excluirId) {
            $sql .= " AND marca_id != ?";
            $params[] = $excluirId;
        }
        
        $stmt = static::$db->prepare($sql);
        $stmt->execute($params);
        $resultado = $stmt->fetch();
        
        return $resultado['total'] > 0;
    }

    // CORREGIDO: Método usando campo 'situacion'
    public function estaActiva()
    {
        return $this->situacion == 1;
    }
}