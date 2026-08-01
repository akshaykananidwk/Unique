<?php
declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private string $controllerNamespace;

    public function __construct(string $controllerNamespace = 'App\\Controllers\\')
    {
        $this->controllerNamespace = $controllerNamespace;
    }

    public function get(string $pattern, string|callable $handler): void
    {
        $this->add('GET', $pattern, $handler);
    }

    public function post(string $pattern, string|callable $handler): void
    {
        $this->add('POST', $pattern, $handler);
    }

    public function add(string $method, string $pattern, string|callable $handler): void
    {
        $regex = preg_replace('#\{([a-zA-Z_]+)\}#', '(?P<$1>[^/]+)', $pattern);
        $this->routes[] = [
            'method' => $method,
            'regex' => '#^' . $regex . '$#',
            'handler' => $handler,
        ];
    }

    public function dispatch(string $path): void
    {
        $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $path = '/' . trim($path, '/');
        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            if (preg_match($route['regex'], $path, $m)) {
                $params = array_filter($m, 'is_string', ARRAY_FILTER_USE_KEY);
                $this->invoke($route['handler'], $params);
                return;
            }
        }
        abort(404, 'Page not found.');
    }

    private function invoke(string|callable $handler, array $params): void
    {
        if (is_callable($handler)) {
            $handler(...array_values($params));
            return;
        }
        [$controller, $action] = explode('@', $handler);
        $class = $this->controllerNamespace . $controller;
        if (!class_exists($class) || !method_exists($class, $action)) {
            abort(404, 'Page not found.');
        }
        (new $class())->$action(...array_values($params));
    }
}
