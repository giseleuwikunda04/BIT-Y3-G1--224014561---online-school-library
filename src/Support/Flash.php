<?php
declare(strict_types=1);
namespace App\Support;

final class Flash {
    private static function boot(): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    public static function set(string $type, string $message): void {
        self::boot();
        $_SESSION['flash'] = [
            'type'    => $type,
            'message' => $message,
        ];
    }

    public static function get(): ?array {
        self::boot();
        $f = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $f;
    }

    public static function setOld(array $input): void {
        self::boot();
        $_SESSION['old'] = $input;
    }

    public static function old(?string $key = null, mixed $default = null): mixed {
        self::boot();
        $o = $_SESSION['old'] ?? [];
        if ($key !== null) {
            return $o[$key] ?? $default;
        }
        unset($_SESSION['old']);
        return $o;
    }

    public static function setErrors(array $errors): void {
        self::boot();
        $_SESSION['errors'] = $errors;
    }

    public static function errors(): array {
        self::boot();
        $e = $_SESSION['errors'] ?? [];
        unset($_SESSION['errors']);
        return $e;
    }
}
