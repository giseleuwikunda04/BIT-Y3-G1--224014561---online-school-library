<?php
declare(strict_types=1);
namespace App\Services;

use App\Support\Flash;

final class Csrf {
    private static function boot(): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    public static function token(): string {
        self::boot();
        if (empty($_SESSION['csrf_token'])) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
        return $_SESSION['csrf_token'];
    }

    public static function validate(?string $token): bool {
        self::boot();
        if (empty($token) || empty($_SESSION['csrf_token'])) {
            return false;
        }
        return hash_equals($_SESSION['csrf_token'], $token);
    }

    public static function field(): string {
        $token = htmlspecialchars(self::token(), ENT_QUOTES, 'UTF-8');
        return '<input type="hidden" name="csrf_token" value="' . $token . '">';
    }

    public static function verifyPost(): void {
        $submitted = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!self::validate($submitted)) {
            http_response_code(403);
            Flash::set('danger', 'Security check failed (invalid or expired CSRF token). Please try again.');
            $referer = $_SERVER['HTTP_REFERER'] ?? '/online-school-library/public/';
            header('Location: ' . $referer, true, 303);
            exit;
        }
    }
}
