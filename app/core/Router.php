<?php

namespace App\Core;

class Router {
    protected $routes = [];

    public function register($method, $path, $handler) {
        // Convert {param} to regex capture group ([^/]+)
        $regex = preg_replace('/\{([a-zA-Z0-9_]+)\}/', '([^/]+)', $path);
        // Add start and end delimiters
        $regex = "#^" . $regex . "$#";

        $this->routes[] = [
            'method' => $method,
            'path' => $path, // Original path for debugging
            'regex' => $regex,
            'handler' => $handler
        ];
    }

    public function dispatch($method, $uri) {
        // Remove query string
        $uri = parse_url($uri, PHP_URL_PATH);
        // Remove base path from URI if needed
        $scriptName = dirname($_SERVER['SCRIPT_NAME']);
        if ($scriptName !== '/' && strpos($uri, $scriptName) === 0) {
            $uri = substr($uri, strlen($scriptName));
        }
        if ($uri === '') $uri = '/';

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['regex'], $uri, $matches)) {
                array_shift($matches); // Remove full match
                
                // Handler can be 'Controller@method', [Controller::class, 'method'] or closure
                if (is_string($route['handler']) && strpos($route['handler'], '@') !== false) {
                    list($controllerName, $actionName) = explode('@', $route['handler']);
                } elseif (is_array($route['handler'])) {
                    $controllerName = $route['handler'][0];
                    $actionName = $route['handler'][1];
                }

                if (isset($controllerName) && isset($actionName)) {
                    // If controllerName is fully qualified, use it directly
                    // Otherwise assume it's in App\Controllers
                    if (strpos($controllerName, '\\') === false) {
                        $controllerClass = "App\\Controllers\\$controllerName";
                    } else {
                        $controllerClass = $controllerName;
                    }
                    
                    if (class_exists($controllerClass)) {
                        $controller = new $controllerClass();
                        if (method_exists($controller, $actionName)) {
                            // Pass parameters to the action
                            call_user_func_array([$controller, $actionName], $matches);
                            return;
                        }
                    } else {
                        // Fallback for global controllers if not found in App\Controllers
                         if (class_exists($controllerName)) {
                            $controller = new $controllerName();
                            if (method_exists($controller, $actionName)) {
                                call_user_func_array([$controller, $actionName], $matches);
                                return;
                            }
                        }
                    }
                } elseif (is_callable($route['handler'])) {
                    call_user_func_array($route['handler'], $matches);
                    return;
                }
            }
        }

        // 404 Not Found
        http_response_code(404);
        require __DIR__ . '/../views/errors/404.php';
    }
}
