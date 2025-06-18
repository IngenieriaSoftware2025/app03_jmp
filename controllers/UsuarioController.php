<?php

namespace Controllers;

use MVC\Router;
use Model\ActiveRecord;
use Model\Usuario;
use PDO;
use Exception;

class UsuarioController extends ActiveRecord
{
    public static function renderizarPagina(Router $router)
    {
        session_start();
        if(!isset($_SESSION['nombre']) || !isset($_SESSION['ADMIN'])) {
            header("Location: ./");
            exit;
        }
        $router->render('usuarios/index', [], 'layouts/menu');
    }

    private static function responder($codigo, $mensaje, $data = null)
    {
        header('Content-Type: application/json; charset=utf-8');
        http_response_code(200);
        
        $respuesta = ['codigo' => $codigo, 'mensaje' => $mensaje];
        if ($data !== null) $respuesta['data'] = $data;
        
        echo json_encode($respuesta, JSON_UNESCAPED_UNICODE);
        exit;
    }

    private static function validarUsuario($datos)
    {
        if (empty($datos['usu_nombre']) || strlen(trim($datos['usu_nombre'])) < 3) {
            return 'El nombre debe tener al menos 3 caracteres';
        }
        
        if (empty($datos['usu_codigo']) || !is_numeric($datos['usu_codigo'])) {
            return 'El código de usuario debe ser numérico';
        }
        
        if (strlen($datos['usu_codigo']) < 8) {
            return 'El código debe tener al menos 8 dígitos';
        }
        
        if (!empty($datos['usu_password']) && strlen($datos['usu_password']) < 6) {
            return 'La contraseña debe tener al menos 6 caracteres';
        }
        
        return null;
    }

    private static function verificarDuplicados($datos, $excluirId = null)
    {
        $sql = "SELECT * FROM usuario WHERE usu_codigo = {$datos['usu_codigo']} AND usu_situacion = 1";
        if ($excluirId) {
            $sql .= " AND usu_id != $excluirId";
        }
        
        $resultado = self::SQL($sql);
        $usuario = $resultado->fetch(PDO::FETCH_ASSOC);
        
        if ($usuario) {
            return 'Ya existe un usuario con ese código';
        }
        
        return null;
    }

    public static function buscarAPI()
    {
        hasPermissionApi(['ADMIN']);
        
        try {
            $sql = "SELECT u.usu_id, u.usu_nombre, u.usu_codigo, u.usu_situacion,
                           GROUP_CONCAT(r.rol_nombre) as roles
                    FROM usuario u
                    LEFT JOIN permiso p ON u.usu_id = p.permiso_usuario
                    LEFT JOIN rol r ON p.permiso_rol = r.rol_id
                    WHERE u.usu_situacion = 1
                    GROUP BY u.usu_id, u.usu_nombre, u.usu_codigo, u.usu_situacion
                    ORDER BY u.usu_nombre";
            
            $usuarios = self::fetchArray($sql);
            
            if (count($usuarios) > 0) {
                self::responder(1, 'Usuarios encontrados', $usuarios);
            } else {
                self::responder(1, 'No hay usuarios registrados', []);
            }
        } catch (Exception $e) {
            self::responder(0, 'Error al buscar usuarios: ' . $e->getMessage());
        }
    }

    public static function guardarAPI()
    {
        hasPermissionApi(['ADMIN']);
        
        try {
            $error = self::validarUsuario($_POST);
            if ($error) {
                self::responder(0, $error);
                return;
            }
            
            $error = self::verificarDuplicados($_POST);
            if ($error) {
                self::responder(0, $error);
                return;
            }
            
            $datosUsuario = [
                'usu_nombre' => strtoupper(trim($_POST['usu_nombre'])),
                'usu_codigo' => filter_var($_POST['usu_codigo'], FILTER_SANITIZE_NUMBER_INT),
                'usu_password' => password_hash($_POST['usu_password'], PASSWORD_DEFAULT),
                'usu_situacion' => 1
            ];
            
            $usuario = new Usuario($datosUsuario);
            $resultado = $usuario->crear();
            
            if ($resultado['resultado'] >= 0) {
                $usuarioId = $resultado['id'];
                
                $rolId = $_POST['rol_id'] ?? 2;
                $sqlPermiso = "INSERT INTO permiso (permiso_usuario, permiso_rol, permiso_situacion) 
                              VALUES ($usuarioId, $rolId, 1)";
                self::SQL($sqlPermiso);
                
                self::responder(1, 'Usuario creado exitosamente');
            } else {
                self::responder(0, 'Error al crear usuario');
            }
        } catch (Exception $e) {
            self::responder(0, 'Error al guardar usuario: ' . $e->getMessage());
        }
    }

    public static function modificarAPI()
    {
        hasPermissionApi(['ADMIN']);
        
        try {
            if (empty($_POST['usu_id'])) {
                self::responder(0, 'ID de usuario requerido');
                return;
            }
            
            $error = self::validarUsuario($_POST);
            if ($error) {
                self::responder(0, $error);
                return;
            }
            
            $usuarioId = filter_var($_POST['usu_id'], FILTER_SANITIZE_NUMBER_INT);
            
            $error = self::verificarDuplicados($_POST, $usuarioId);
            if ($error) {
                self::responder(0, $error);
                return;
            }
            
            $usuario = Usuario::find($usuarioId);
            if (!$usuario) {
                self::responder(0, 'Usuario no encontrado');
                return;
            }
            
            $datosActualizar = [
                'usu_nombre' => strtoupper(trim($_POST['usu_nombre'])),
                'usu_codigo' => filter_var($_POST['usu_codigo'], FILTER_SANITIZE_NUMBER_INT)
            ];
            
            if (!empty($_POST['usu_password'])) {
                $datosActualizar['usu_password'] = password_hash($_POST['usu_password'], PASSWORD_DEFAULT);
            }
            
            $usuario->sincronizar($datosActualizar);
            $resultado = $usuario->actualizar();
            
            if ($resultado['resultado'] >= 0) {
                self::responder(1, 'Usuario actualizado exitosamente');
            } else {
                self::responder(0, 'Error al actualizar usuario');
            }
        } catch (Exception $e) {
            self::responder(0, 'Error al modificar usuario: ' . $e->getMessage());
        }
    }

    public static function eliminarAPI()
    {
        hasPermissionApi(['ADMIN']);
        
        try {
            if (empty($_POST['usu_id'])) {
                self::responder(0, 'ID de usuario requerido');
                return;
            }
            
            $usuarioId = filter_var($_POST['usu_id'], FILTER_SANITIZE_NUMBER_INT);
            
            if ($usuarioId == $_SESSION['usuario_id']) {
                self::responder(0, 'No puede eliminar su propio usuario');
                return;
            }
            
            $usuario = Usuario::find($usuarioId);
            if (!$usuario) {
                self::responder(0, 'Usuario no encontrado');
                return;
            }
            
            $usuario->sincronizar(['usu_situacion' => 0]);
            $resultado = $usuario->actualizar();
            
            if ($resultado['resultado'] >= 0) {
                self::responder(1, 'Usuario eliminado exitosamente');
            } else {
                self::responder(0, 'Error al eliminar usuario');
            }
        } catch (Exception $e) {
            self::responder(0, 'Error al eliminar usuario: ' . $e->getMessage());
        }
    }
}