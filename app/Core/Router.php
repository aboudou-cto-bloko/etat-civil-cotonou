<?php

declare(strict_types=1);

namespace App\Core;

class Router
{
    private array $routes = [];
    private array $globalMiddleware = [];
    private Request $request;

    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('POST', $path, $handler, $middleware);
    }

    public function put(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('PUT', $path, $handler, $middleware);
    }

    public function delete(string $path, array $handler, array $middleware = []): void
    {
        $this->addRoute('DELETE', $path, $handler, $middleware);
    }

    public function addRoute(string $method, string $path, array $handler, array $middleware = []): void
    {
        $this->routes[] = [
            'method'     => strtoupper($method),
            'path'       => $path,
            'handler'    => $handler,
            'middleware' => array_merge($this->globalMiddleware, $middleware),
        ];
    }

    public function group(array $options, callable $callback): void
    {
        $previousGlobal = $this->globalMiddleware;
        $this->globalMiddleware = array_merge(
            $this->globalMiddleware,
            $options['middleware'] ?? []
        );

        $callback($this);

        $this->globalMiddleware = $previousGlobal;
    }

    public function resolve(): void
    {
        $method = $this->request->method();
        $path   = $this->request->path();

        // Support method override via _method champ POST
        if ($method === 'POST' && isset($_POST['_method'])) {
            $method = strtoupper($_POST['_method']);
        }

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $params = $this->matchPath($route['path'], $path);
            if ($params === null) {
                continue;
            }

            $this->request->setRouteParams($params);

            $this->runPipeline($route['middleware'], function () use ($route) {
                [$controllerClass, $action] = $route['handler'];
                $controller = new $controllerClass($this->request);
                $controller->$action($this->request);
            });

            return;
        }

        // 404
        http_response_code(404);
        $view = new View();
        $view->render('errors/404', ['title' => 'Page introuvable']);
    }

    private function matchPath(string $routePath, string $requestPath): ?array
    {
        $routePath   = rtrim($routePath, '/') ?: '/';
        $requestPath = rtrim($requestPath, '/') ?: '/';

        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $routePath);
        $pattern = '#^' . $pattern . '$#';

        if (!preg_match($pattern, $requestPath, $matches)) {
            return null;
        }

        return array_filter($matches, fn($k) => !is_int($k), ARRAY_FILTER_USE_KEY);
    }

    private function runPipeline(array $middlewareList, callable $destination): void
    {
        $pipeline = array_reduce(
            array_reverse($middlewareList),
            function (callable $next, string $middlewareClass) {
                return function () use ($next, $middlewareClass) {
                    (new $middlewareClass())->handle($this->request, $next);
                };
            },
            $destination
        );

        $pipeline();
    }
}
