<?php

namespace K1\Router;

class Router
{
    private static ?Router $instance = null;

    private array $handler;
    private string $current_url;
    private $handler_404 = null;
    private string $result = '';

    private function __construct()
    {
    }

    public static function getInstance(): ?Router
    {
        if (self::$instance === null) {
            self::$instance = new Router();
        }

        return self::$instance;
    }

    private function match($method, $pattern, $callback): void
    {
        foreach (explode('|', $method) as $item) {
            $this->handler[$item][] = [
                'pattern' => $pattern,
                'callback' => $callback
            ];
        }
    }

    public function all($pattern, $callback): void
    {
        $this->match('GET|POST', $pattern, $callback);
    }

    public function get($pattern, $callback): void
    {
        $this->match('GET', $pattern, $callback);
    }

    public function post($pattern, $callback): void
    {
        $this->match('POST', $pattern, $callback);
    }

    private function handler($pattern, $callback): bool
    {
        if (!preg_match("#^" . $pattern . "$#", $this->current_url, $match)) {
            return false;
        }

        $options = array_slice($match, 1);

        $this->invoke($callback, $options);

        return true;
    }

    private function invoke($callback, $options = []): void
    {
        if (is_callable($callback)) {
            ob_start();

            call_user_func_array($callback, $options);

            $this->result = ob_get_contents();
            ob_end_clean();
        }
    }

    public function clearHandler(): void
    {
        $this->handler = [];
    }

    public function run(): string
    {
        $requestedMethod = $_SERVER['REQUEST_METHOD'];
        $this->current_url = parse_url($_SERVER['REQUEST_URI'])['path'];

        $check = false;
        if (!empty($this->handler[$requestedMethod])) {
            foreach ($this->handler[$requestedMethod] as $item) {
                if ($check = $this->handler($item['pattern'], $item['callback'])) {
                    break;
                }
            }
        }

        if (!$check) {
            $this->handler404();
        }

        return $this->result;
    }

    public function set404($callback): void
    {
        $this->handler_404 = $callback;
    }

    public function handler404(): void
    {
        if ($this->handler_404 === null) {
            return;
        }

        http_response_code(404);

        $this->invoke($this->handler_404);
    }
}