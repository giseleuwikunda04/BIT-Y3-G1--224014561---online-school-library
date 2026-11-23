<?php
declare(strict_types=1);
namespace App\Support;

final class View {
    public static function render(string $template, array $data = [], bool $withLayout = true): void {
        extract($data, EXTR_SKIP);
        if ($withLayout) {
            require dirname(__DIR__) . '/Views/layouts/header.php';
        }
        require dirname(__DIR__) . '/Views/' . $template . '.php';
        if ($withLayout) {
            require dirname(__DIR__) . '/Views/layouts/footer.php';
        }
    }

    public static function e(mixed $v): string {
        return htmlspecialchars((string)($v ?? ''), ENT_QUOTES, 'UTF-8');
    }
}
