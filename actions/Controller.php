<?php

namespace App\Actions;

use App\Middleware\Auth;
use App\Config\Flash;

abstract class Controller
{
    protected array $data = [];
    protected string $layout = 'main';

    public function __construct()
    {
        $this->data['app_name'] = $GLOBALS['config']['app']['name'];
        $this->data['current_user'] = Auth::user();
        $this->data['flash'] = Flash::getAll();
    }

    protected function view(string $view, array $data = []): void
    {
        $this->data = array_merge($this->data, $data);
        extract($this->data);
        
        $viewPath = __DIR__ . "/../pages/{$view}.php";
        $layoutPath = __DIR__ . "/../pages/layouts/{$this->layout}.php";
        
        if (file_exists($layoutPath)) {
            ob_start();
            require $viewPath;
            $content = ob_get_clean();
            require $layoutPath;
        } else {
            require $viewPath;
        }
    }

    protected function redirect(string $url, int $code = 302): void
    {
        header("Location: {$url}", true, $code);
        exit;
    }

    protected function back(): void
    {
        $referer = $_SERVER['HTTP_REFERER'] ?? '/';
        $this->redirect($referer);
    }

    protected function json(array $data, int $code = 200): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data);
        exit;
    }

    protected function abort(int $code, string $message = ''): void
    {
        http_response_code($code);
        $this->view("errors/{$code}", ['message' => $message]);
        exit;
    }
}