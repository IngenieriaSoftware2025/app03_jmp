<?php

namespace MVC;

class Router
{
    public $getRoutes = [];
    public $postRoutes = [];
    protected $base = '';

    public function get($url, $fn)
    {
        $this->getRoutes[$this->base . $url] = $fn;
    }

    public function post($url, $fn)
    {
        $this->postRoutes[$this->base . $url] = $fn;
    }

    public function setBaseURL($base){
        $this->base = $base;
    }

    public function comprobarRutas()
    {
        // Obtener la URI actual
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        // Remover query string si existe
        if (!empty($queryString)) {
            $currentUrl = str_replace("?" . $queryString, '', $requestUri);
        } else {
            $currentUrl = $requestUri;
        }
        
        // Si la URL está vacía o es solo '/', usar la base como default
        if (empty($currentUrl) || $currentUrl === '/') {
            $currentUrl = $this->base . '/';
        }
        
        // Depuración - descomentar si necesitas ver las URLs procesadas
        if (isset($_ENV['DEBUG_MODE']) && $_ENV['DEBUG_MODE']) {
            error_log("Router Debug - Base URL: " . $this->base);
            error_log("Router Debug - Request URI: " . $requestUri);
            error_log("Router Debug - Current URL: " . $currentUrl);
        }
        
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($method === 'GET') {
            $fn = $this->getRoutes[$currentUrl] ?? null;
        } else {
            $fn = $this->postRoutes[$currentUrl] ?? null;
        }
        
        if ($fn) {
            // Call user fn va a llamar una función cuando no sabemos cual sera
            call_user_func($fn, $this); // This es para pasar argumentos
        } else {
            // Depuración - mostrar rutas disponibles
            if (isset($_ENV['DEBUG_MODE']) && $_ENV['DEBUG_MODE']) {
                echo "<h3>Ruta no encontrada: $currentUrl</h3>";
                echo "<h4>Rutas GET disponibles:</h4><pre>";
                print_r(array_keys($this->getRoutes));
                echo "</pre><h4>Rutas POST disponibles:</h4><pre>";
                print_r(array_keys($this->postRoutes));
                echo "</pre>";
                echo "<h4>Información de URL:</h4>";
                echo "<p>Base URL: " . $this->base . "</p>";
                echo "<p>Request URI: " . $requestUri . "</p>";
            } else {
                if (empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
                    // Si es una solicitud normal del navegador, mostrar página 404
                    $this->render('pages/notfound');
                } else {
                    // Si es una solicitud AJAX, retornar error JSON
                    getHeadersApi();
                    echo json_encode([
                        "ERROR" => "PÁGINA NO ENCONTRADA",
                        "URL_SOLICITADA" => $currentUrl,
                        "METODO" => $method
                    ]);
                }
            }
        }
    }

    public function render($view, $datos = [], $layout = 'layout')
    {
        // Leer lo que le pasamos a la vista
        foreach ($datos as $key => $value) {
            $$key = $value;  // Corregido para usar variable variable correctamente
        }

        ob_start(); // Almacenamiento en memoria durante un momento...

        // Incluir la vista
        include_once __DIR__ . "/views/$view.php";
        $contenido = ob_get_clean(); // Limpia el Buffer
        
        // Incluir el layout
        if (strpos($layout, 'layouts/') === 0) {
            // Si ya tiene "layouts/", usar tal como viene
            include_once __DIR__ . "/views/$layout.php";
        } else {
            // Si no tiene "layouts/", agregarlo
            include_once __DIR__ . "/views/layouts/$layout.php";
        }
    }

    public function load($view, $datos = []){
        foreach ($datos as $key => $value) {
            $$key = $value;  // Corregido para usar variable variable correctamente
        }

        ob_start(); // Almacenamiento en memoria durante un momento...

        // Incluir la vista
        include_once __DIR__ . "/views/$view.php";
        $contenido = ob_get_clean(); // Limpia el Buffer
        return $contenido;
    }

    public function printPDF($ruta){
        header("Content-type: application/pdf");
        header("Content-Disposition: inline; filename=filename.pdf");
        @readfile(__DIR__ . '/storage/' . $ruta );
    }
}