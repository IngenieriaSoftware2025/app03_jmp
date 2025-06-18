<?php

function debuguear($variable) {
    echo "<pre>";
    var_dump($variable);
    echo "</pre>";
    exit;
}

// Escapa / Sanitizar el HTML
function s($html) {
    $s = htmlspecialchars($html);
    return $s;
}

// Función que revisa que el usuario este autenticado
function isAuth() {
    session_start();
    if(!isset($_SESSION['login'])) {
        header('Location: /');
    }
}
function isAuthApi() {
    getHeadersApi();
    session_start();
    if(!isset($_SESSION['auth_user'])) {
        echo json_encode([    
            "mensaje" => "No esta autenticado",

            "codigo" => 4,
        ]);
        exit;
    }
}

function isNotAuth(){
    session_start();
    if(isset($_SESSION['auth'])) {
        header('Location: /auth/');
    }
}


function hasPermission(array $permisos){

    $comprobaciones = [];
    foreach ($permisos as $permiso) {

        $comprobaciones[] = !isset($_SESSION[$permiso]) ? false : true;
      
    }

    if(array_search(true, $comprobaciones) !== false){}else{
        header('Location: /');
    }
}

function hasPermissionApi(array $permisos){
    getHeadersApi();
    $comprobaciones = [];
    foreach ($permisos as $permiso) {

        $comprobaciones[] = !isset($_SESSION[$permiso]) ? false : true;
      
    }

    if(array_search(true, $comprobaciones) !== false){}else{
        echo json_encode([     
            "mensaje" => "No tiene permisos",

            "codigo" => 4,
        ]);
        exit;
    }
}

function getHeadersApi(){
    return header("Content-type:application/json; charset=utf-8");
}

function asset($ruta){
    return "/". $_ENV['APP_NAME']."/public/" . $ruta;
}




/**
 * Función mejorada para verificación de autenticación
 * Puede ser usada tanto para páginas como para API
 * 
 * @param bool $esApi Indica si la verificación es para una API
 * @param array $rolesPermitidos Roles que tienen acceso (opcional)
 * @return bool True si está autenticado, false o redirige si no
 */
function verificarAutenticacion($esApi = false, $rolesPermitidos = []) {
    // Asegurar que la sesión está iniciada
    if (session_status() == PHP_SESSION_NONE) {
        session_start();
    }
    
    // Verificar autenticación básica (sesión existe)
    $autenticado = isset($_SESSION['user']) || isset($_SESSION['nombre']);
    
    // Verificación de roles si se especifican
    $tieneRol = true;
    if (!empty($rolesPermitidos) && $autenticado) {
        $tieneRol = false;
        
        // Verificar si el usuario tiene alguno de los roles permitidos
        if (isset($_SESSION['ADMIN']) && in_array('ADMIN', $rolesPermitidos)) {
            $tieneRol = true;
        } else if (isset($_SESSION['rol']) && in_array($_SESSION['rol'], $rolesPermitidos)) {
            $tieneRol = true;
        }
    }
    
    // Si no está autenticado o no tiene rol adecuado
    if (!$autenticado || !$tieneRol) {
        if ($esApi) {
            // Configurar headers para API
            header('Content-Type: application/json; charset=utf-8');
            $mensaje = !$autenticado ? 'No está autenticado' : 'No tiene permisos suficientes';
            echo json_encode([
                'codigo' => 0,
                'mensaje' => $mensaje
            ]);
            exit;
        } else {
            // Redireccionar para navegación normal
            header('Location: /app03_jmp/');
            exit;
        }
    }
    
    return true;
}