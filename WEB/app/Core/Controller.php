<?php
/**
 * Base Controller - Core methods only
 * CIS324 Real Estate Portal
 */

abstract class Controller
{
    protected array $data = [];
    protected string $layout = 'layout/main';

    protected function view(string $view, array $data = [], bool $return = false): string|void
    {
        $data = array_merge($this->data, $data);
        extract($data);
        $path = __DIR__ . "/../../app/View/{$view}.php";
        if (!file_exists($path)) throw new Exception("View not found: {$path}");
        ob_start();
        include $path;
        $content = ob_get_clean();
        if ($return) return $content;
        $layoutPath = __DIR__ . "/../../app/View/{$this->layout}.php";
        if (file_exists($layoutPath) && !$this->isAjax()) include $layoutPath;
        else echo $content;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }

    protected function jsonOk(array $data = [], string $msg = 'Success'): void
    {
        $this->json(['success' => true, 'message' => $msg, 'data' => $data]);
    }

    protected function jsonErr(string $msg, int $status = 400, array $err = []): void
    {
        $this->json(['success' => false, 'message' => $msg, 'errors' => $err], $status);
    }

    protected function redirect(string $url, int $code = 302): void
    {
        header("Location: {$url}", true, $code);
        exit;
    }

    protected function back(string $msg = '', string $type = 'success'): void
    {
        if ($msg) Session::flash($type, $msg);
        $this->redirect($_SERVER['HTTP_REFERER'] ?? '/');
    }

    private function isAjax(): bool
    {
        return (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest')
            || (isset($_SERVER['HTTP_ACCEPT']) && str_contains($_SERVER['HTTP_ACCEPT'], 'application/json'));
    }

    protected function input(string $key, mixed $def = null): mixed
    {
        return $_POST[$key] ?? $_GET[$key] ?? $def;
    }

    protected function share(string $key, mixed $val): void
    {
        $this->data[$key] = $val;
    }
}