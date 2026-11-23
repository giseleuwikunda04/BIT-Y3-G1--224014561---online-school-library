<?php
declare(strict_types=1);
namespace App\Support;

final class Auth {
    public const BASE = '/online-school-library/public';

    private static function boot(): void {
        if (session_status() === PHP_SESSION_NONE && !headers_sent()) {
            session_start();
        }
    }

    public static function login(array $user): void {
        self::boot();
        // Prevent session fixation
        session_regenerate_id(true);
        $_SESSION['user'] = [
            'user_id'    => (int)$user['user_id'],
            'username'   => (string)$user['username'],
            'email'      => (string)$user['email'],
            'role'       => (string)$user['role'],
            'created_at' => (string)($user['created_at'] ?? ''),
        ];
    }

    public static function logout(): void {
        self::boot();
        $_SESSION['user'] = null;
        unset($_SESSION['user']);
        session_regenerate_id(true);
    }

    public static function user(): ?array {
        self::boot();
        return $_SESSION['user'] ?? null;
    }

    public static function check(): bool {
        return self::user() !== null;
    }

    public static function id(): ?int {
        $u = self::user();
        return $u ? (int)$u['user_id'] : null;
    }

    public static function username(): ?string {
        $u = self::user();
        return $u ? (string)$u['username'] : null;
    }

    public static function role(): ?string {
        $u = self::user();
        return $u ? (string)$u['role'] : null;
    }

    public static function isLibrarian(): bool {
        return self::role() === 'librarian';
    }

    public static function isStudent(): bool {
        return self::role() === 'student';
    }

    public static function requireLogin(): void {
        if (!self::check()) {
            Flash::set('warning', 'Please sign in to access the school library.');
            header('Location: ' . self::BASE . '/login', true, 303);
            exit;
        }
    }

    public static function requireLibrarian(): void {
        self::requireLogin();
        if (!self::isLibrarian()) {
            http_response_code(403);
            Flash::set('danger', 'Access restricted: Only librarians can perform catalogue and administrative operations.');
            header('Location: ' . self::BASE . '/dashboard', true, 303);
            exit;
        }
    }

    public static function requireStudent(): void {
        self::requireLogin();
        if (!self::isStudent()) {
            http_response_code(403);
            Flash::set('danger', 'This section is designed specifically for students.');
            header('Location: ' . self::BASE . '/dashboard', true, 303);
            exit;
        }
    }
}
