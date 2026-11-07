<?php
/**
 * Simple Router - Exact match + named params
 * CIS324 Real Estate Portal
 */

class Router
{
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $uri, callable|array|string $action): void
    {
        $this->routes['GET'][$this->normalize($uri)] = $action;
    }

    public function post(string $uri, callable|array|string $action): void
    {
        $this->routes['POST'][$this->normalize($uri)] = $action;
    }

    private function normalize(string $uri): string
    {
        return '/' . ltrim($uri, '/');
    }

    public function dispatch(string $method, string $uri): void
    {
        $method = strtoupper($method);
        $uri = parse_url($uri, PHP_URL_PATH) ?? '/';
        $uri = $this->normalize(rawurldecode($uri));

        // Exact match first
        if (isset($this->routes[$method][$uri])) {
            $this->execute($this->routes[$method][$uri], []);
            return;
        }

        // Param match: /properties/{id} -> /properties/123
        foreach ($this->routes[$method] as $pattern => $action) {
            if (str_contains($pattern, '{')) {
                $regex = '#^' . preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $pattern) . '$#';
                if (preg_match($regex, $uri, $matches)) {
                    $params = [];
                    foreach ($matches as $k => $v) if (!is_int($k)) $params[$k] = $v;
                    $this->execute($action, $params);
                    return;
                }
            }
        }

        http_response_code(404);
        include __DIR__ . '/../../app/View/errors/404.php';
    }

    private function execute(callable|array|string $action, array $params): void
    {
        if (is_callable($action)) {
            $action(...$params);
        } elseif (is_array($action) && count($action) === 2) {
            [$class, $method] = $action;
            (new $class())->$method(...$params);
        } elseif (is_string($action) && str_contains($action, '@')) {
            [$class, $method] = explode('@', $action);
            (new $class())->$method(...$params);
        } else {
            throw new Exception('Invalid route action');
        }
    }
}