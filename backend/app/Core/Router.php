<?php

namespace App\Core;

class Router 
{
    private array $routes = [];
    public function get (string $uri, string $controller, string $method) : void
    {
        $this->routes['GET'][$uri] = ['controller' => $controller, 'method' => $method];
    }

    public function post (string $uri, string $controller, string $method) : void
    {
        $this->routes['POST'][$uri] = ['controller' => $controller, 'method' => $method];
    }

    public function dispatch(): void
    {
        $httpMethod = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $basePath = dirname($_SERVER['SCRIPT_NAME']);
        if (str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }
        $uri = rtrim($uri, '/') ?: '/';
        //Buscar ruta exacta
        if (isset($this->routes[$httpMethod][$uri])) {
            $route = $this->routes[$httpMethod][$uri];
            $controller = new $route['controller']();
            $controller->{$route['method']}();
            return;
        }

        //Buscar ruta con parámetros
        foreach ($this->routes[$httpMethod] ?? [] as $pattern => $route) {
            $regex = preg_replace('/\/:([^\/]+)/', '/([^/]+)', $pattern);
            $regex = '#^' . $regex . '$#';
            if (preg_match($regex, $uri, $matches)){
                array_shift($matches);
                $controller = new $route['controller']();
                $controller->{$route['method']}(...$matches);
                return;
            }
        }

        http_response_code(404);
        echo json_encode(['error' => 'Ruta no encontrada']);
    }
}


?>