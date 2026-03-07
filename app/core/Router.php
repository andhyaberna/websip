<?php

class Router {
    protected $routes = [];

    public function register($method, $path, $handler) {
        // Convert {param} to regex capture group ([^/]+)
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        // Add start and end delimiters
        $regex = "#^" . $regex . "$#";
        
        $this->routes[$method][$path] = [
            'handler' => $handler,
            'regex' => $regex
        ];
    }

    public function dispatch($path = null) {
        $method = $_SERVER['REQUEST_METHOD'];
        
        if ($path === null) {
            $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            
            // Remove base path if exists (e.g. /websip/public)
            $scriptName = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
            if ($scriptName !== '/' && strpos($path, $scriptName) === 0) {
                $path = substr($path, strlen($scriptName));
            }
        }
        
        if ($path === false || $path === '') {
            $path = '/';
        }

        if (isset($this->routes[$method])) {
            foreach ($this->routes[$method] as $routePath => $route) {
                if (preg_match($route['regex'], $path, $matches)) {
                    array_shift($matches); // Remove full match
                    
                    $handler = $route['handler'];
                    
                    if (is_callable($handler)) {
                        call_user_func_array($handler, $matches);
                    } elseif (is_string($handler)) {
                        list($controller, $action) = explode('@', $handler);
                        require_once __DIR__ . "/../controllers/{$controller}.php";
                        $controllerInstance = new $controller();
                        call_user_func_array([$controllerInstance, $action], $matches);
                    }
                    return;
                }
            }
        }

        http_response_code(404);
        echo "404 Not Found";
    }
}
