<?php
declare(strict_types=1);

namespace App\Core;

/** Maps "METHOD /path/{id}" to a handler. {id} matches digits only. */
final class Router
{
    /** @var array<int, array{0:string,1:string,2:callable}> */
    private array $routes = [];

    public function get(string $path, callable $handler): void
    {
        $this->routes[] = ['GET', $path, $handler];
    }

    public function post(string $path, callable $handler): void
    {
        $this->routes[] = ['POST', $path, $handler];
    }

    /** Returns false when nothing matched. */
    public function dispatch(string $method, string $path): bool
    {
        $path = rtrim($path, '/') ?: '/';
        foreach ($this->routes as [$m, $pattern, $handler]) {
            $regex = '#^' . str_replace('\{id\}', '(\d+)', preg_quote(rtrim($pattern, '/') ?: '/', '#')) . '$#';
            if ($m === $method && preg_match($regex, $path, $match)) {
                $args = array_map('intval', array_slice($match, 1));
                $handler(...$args);
                return true;
            }
        }
        return false;
    }
}
