<?php

declare(strict_types=1);

namespace App\Core;

class View
{
    private string $viewPath;
    private string $layoutPath;

    public function __construct()
    {
        $this->viewPath   = BASE_PATH . '/app/Views/';
        $this->layoutPath = BASE_PATH . '/app/Views/layouts/';
    }

    public function render(string $template, array $data = [], string $layout = 'app'): void
    {
        extract($data, EXTR_SKIP);

        ob_start();
        $templateFile = $this->viewPath . $template . '.php';

        if (!file_exists($templateFile)) {
            ob_end_clean();
            throw new \RuntimeException("Vue introuvable : {$template}");
        }

        include $templateFile;
        $content = ob_get_clean();

        // Les vues d'erreur et d'auth utilisent leurs propres layouts
        $layoutFile = $this->layoutPath . $layout . '.php';
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
    }

    public static function e(mixed $value): string
    {
        return self::escape($value);
    }

    public static function csrfField(): string
    {
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        $token = self::escape($_SESSION['csrf_token']);
        return "<input type=\"hidden\" name=\"_csrf_token\" value=\"{$token}\">";
    }
}
