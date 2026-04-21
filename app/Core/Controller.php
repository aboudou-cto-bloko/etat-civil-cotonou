<?php

declare(strict_types=1);

namespace App\Core;

abstract class Controller
{
    protected Request $request;
    protected View $view;

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->view    = new View();
    }

    protected function render(string $template, array $data = []): void
    {
        $this->view->render($template, $data);
    }

    protected function redirect(string $url): void
    {
        header('Location: ' . $url);
        exit;
    }

    protected function json(array $data, int $status = 200): void
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function flash(string $type, string $message): void
    {
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    protected function getFlash(): ?array
    {
        if (isset($_SESSION['flash'])) {
            $flash = $_SESSION['flash'];
            unset($_SESSION['flash']);
            return $flash;
        }
        return null;
    }

    protected function user(): ?array
    {
        return $_SESSION['user'] ?? null;
    }

    protected function arrondissementId(): ?int
    {
        return $this->request->getAttribute('arrondissement_id');
    }

    protected function abort(int $code = 403): void
    {
        http_response_code($code);
        $this->view->render("errors/{$code}", ['title' => "Erreur {$code}"]);
        exit;
    }
}
