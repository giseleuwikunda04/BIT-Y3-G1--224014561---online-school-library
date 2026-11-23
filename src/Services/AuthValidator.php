<?php
declare(strict_types=1);
namespace App\Services;

use App\Models\User;

final class AuthValidator {
    public static function validateRegister(array $data): array {
        $errors = [];

        $username = trim($data['username'] ?? '');
        $email    = trim($data['email'] ?? '');
        $password = (string)($data['password'] ?? '');
        $confirm  = (string)($data['password_confirm'] ?? '');
        $role     = trim($data['role'] ?? 'student');

        if ($username === '') {
            $errors['username'] = 'Username is required.';
        } elseif (strlen($username) < 3 || strlen($username) > 50) {
            $errors['username'] = 'Username must be between 3 and 50 characters.';
        } elseif (!preg_match('/^[a-zA-Z0-9_.-]+$/', $username)) {
            $errors['username'] = 'Username may only contain letters, numbers, underscores, dots, and dashes.';
        } elseif (User::findByUsername($username) !== null) {
            $errors['username'] = 'That username is already taken. Please choose another.';
        }

        if ($email === '') {
            $errors['email'] = 'Email address is required.';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Please enter a valid email address.';
        } elseif (strlen($email) > 100) {
            $errors['email'] = 'Email must not exceed 100 characters.';
        } elseif (User::findByEmail($email) !== null) {
            $errors['email'] = 'An account with that email already exists.';
        }

        if ($password === '') {
            $errors['password'] = 'Password is required.';
        } elseif (strlen($password) < 6) {
            $errors['password'] = 'Password must be at least 6 characters.';
        }

        if ($confirm !== $password) {
            $errors['password_confirm'] = 'Passwords do not match.';
        }

        if (!in_array($role, ['student', 'librarian'], true)) {
            $errors['role'] = 'Invalid role selected.';
        }

        return $errors;
    }

    public static function validateLogin(array $data): array {
        $errors = [];
        $identity = trim($data['identity'] ?? '');
        $password = (string)($data['password'] ?? '');

        if ($identity === '') {
            $errors['identity'] = 'Please enter your username or email address.';
        }

        if ($password === '') {
            $errors['password'] = 'Please enter your password.';
        }

        return $errors;
    }
}
