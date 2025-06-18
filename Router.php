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
        $this->postRoutes[$this->base .$url] = $fn;
    }

    public function setBaseURL($base){
        $this->base = $base;
    }

    public function comprobarRutas()
    {
        // ✅ CORRECCIÓN: Manejo mejorado de REQUEST_URI
        $requestUri = $_SERVER['REQUEST_URI'] ?? '';
        $queryString = $_SERVER['QUERY_STRING'] ?? '';
        
        // Remover query string solo si existe
        if (!empty($queryString)) {
            $currentUrl = str_replace("?" . $queryString, '', $requestUri);
        } else {
            $currentUrl = $requestUri;
        }
        
        // ✅ CORRECCIÓN: Si está vacío, usar la base como default
        if (empty($currentUrl) || $currentUrl === '/') {
            $currentUrl = $this->base . '/';
        }
        
        $method = $_SERVER['REQUEST_METHOD'];
        
        // ✅ DEBUG: Opcional - remover en producción
        if ($_ENV['DEBUG_MODE'] ?? 0) {
            error_log("Router Debug - Request URI: " . $requestUri);
            error_log("Router Debug - Current URL: " . $currentUrl);
            error_log("Router Debug - Method: " . $method);
            error_log("Router Debug - Base: " . $this->base);
        }
        
        if ($method === 'GET') {
            $fn = $this->getRoutes[$currentUrl] ?? null;
        } else {
            $fn = $this->postRoutes[$currentUrl] ?? null;
        }
        
        if ( $fn ) {
            // Call user fn va a llamar una función cuando no sabemos cual sera
            call_user_func($fn, $this); // This es para pasar argumentos
        } else {
            // ✅ CORRECCIÓN: Mejor manejo de errores 404
            if( empty($_SERVER['HTTP_X_REQUESTED_WITH'])){
                // ✅ DEBUG: Mostrar rutas disponibles en modo debug
                if ($_ENV['DEBUG_MODE'] ?? 0) {
                    echo "<h3>Ruta no encontrada: $currentUrl</h3>";
                    echo "<h4>Rutas GET disponibles:</h4><pre>";
                    print_r(array_keys($this->getRoutes));
                    echo "</pre><h4>Rutas POST disponibles:</h4><pre>";
                    print_r(array_keys($this->postRoutes));
                    echo "</pre>";
                } else {
                    $this->render('pages/notfound');
                }
            }else{
                getHeadersApi();
                echo json_encode([
                    "ERROR" => "PÁGINA NO ENCONTRADA",
                    "URL_SOLICITADA" => $currentUrl,
                    "METODO" => $method
                ]);
            }
        }
    }

    public function render($view, $datos = [], $layout = 'layout')
    {
        // Leer lo que le pasamos a la vista
        foreach ($datos as $key => $value) {
            $key = $value;  // Doble signo de dolar significa: variable variable, básicamente nuestra variable sigue siendo la original, pero al asignarla a otra no la reescribe, mantiene su valor, de esta forma el nombre de la variable se asigna dinamicamente
        }

        ob_start(); // Almacenamiento en memoria durante un momento...

        // entonces incluimos la vista en el layout
        include_once __DIR__ . "/views/$view.php";
        $contenido = ob_get_clean(); // Limpia el Buffer
        
        // ✅ CORRECCIÓN: Manejar layout con o sin "layouts/" prefix
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
            $$key = $value;  // Doble signo de dolar significa: variable variable, básicamente nuestra variable sigue siendo la original, pero al asignarla a otra no la reescribe, mantiene su valor, de esta forma el nombre de la variable se asigna dinamicamente
        }

        ob_start(); // Almacenamiento en memoria durante un momento...

        // entonces incluimos la vista en el layout
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