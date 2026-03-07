<?php

class Router {
    protected $routes = [];

    public function register($method, $path, $handler) {
        $this->routes[$method][$path] = $handler;
    }

    public function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        
        // Remove base path if exists (e.g. /websip/public)
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && strpos($path, $scriptName) === 0) {
            $path = substr($path, strlen($scriptName));
        }
        if ($path === false || $path === '') {
            $path = '/';
        }

        if (isset($this->routes[$method][$path])) {
            $handler = $this->routes[$method][$path];
            
            if (is_callable($handler)) {
                call_user_func($handler);
            } elseif (is_string($handler)) {
                list($controller, $action) = explode('@', $handler);
                require_once __DIR__ . "/../controllers/{$controller}.php";
                $controllerInstance = new $controller();
                $controllerInstance->$action();
            }
        } else {
            http_response_code(404);
            echo "404 Not Found";
        }
    }
}
