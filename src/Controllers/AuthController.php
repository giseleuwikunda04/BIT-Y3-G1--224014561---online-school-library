<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\User;
use App\Services\AuthValidator;
use App\Services\Csrf;
use App\Support\Auth;
use App\Support\Flash;
use App\Support\View;

final class AuthController {
    public function showLogin(): void {
        if (Auth::check()) {
            header('Location: ' . Auth::BASE . '/dashboard', true, 303);
            exit;
        }

        View::render('auth/login', [
            'pageTitle' => 'Sign In — School Library',
            'old'       => Flash::old(),
            'errors'    => Flash::errors(),
            'flash'     => Flash::get(),
        ]);
    }

    public function login(): void {
        Csrf::verifyPost();

        $errors = AuthValidator::validateLogin($_POST);
        if (!empty($errors)) {
            Flash::setErrors($errors);
            Flash::setOld($_POST);
            header('Location: ' . Auth::BASE . '/login', true, 303);
            exit;
        }

        $identity = trim($_POST['identity'] ?? '');
        $password = (string)($_POST['password'] ?? '');

        $user = User::findByIdentity($identity);

        if (!$user || !password_verify($password, (string)$user['password_hash'])) {
            Flash::set('danger', 'Invalid credentials. Please check your username/email and password.');
            Flash::setOld(['identity' => $identity]);
            header('Location: ' . Auth::BASE . '/login', true, 303);
            exit;
        }

        Auth::login($user);
        Flash::set('success', 'Welcome back, ' . htmlspecialchars($user['username'], ENT_QUOTES, 'UTF-8') . '! You are signed in as ' . ucfirst($user['role']) . '.');
        header('Location: ' . Auth::BASE . '/dashboard', true, 303);
        exit;
    }

    public function showRegister(): void {
        if (Auth::check()) {
            header('Location: ' . Auth::BASE . '/dashboard', true, 303);
            exit;
        }

        View::render('auth/register', [
            'pageTitle' => 'Student Registration — School Library',
            'old'       => Flash::old(),
            'errors'    => Flash::errors(),
            'flash'     => Flash::get(),
        ]);
    }

    public function register(): void {
        Csrf::verifyPost();

        $errors = AuthValidator::validateRegister($_POST);
        if (!empty($errors)) {
            Flash::setErrors($errors);
            Flash::setOld($_POST);
            header('Location: ' . Auth::BASE . '/register', true, 303);
            exit;
        }

        $userId = User::create([
            'username'      => trim($_POST['username']),
            'email'         => trim($_POST['email']),
            'password_hash' => password_hash($_POST['password'], PASSWORD_DEFAULT),
            'role'          => $_POST['role'] ?? 'student',
        ]);

        $user = User::findById($userId);
        if ($user) {
            Auth::login($user);
            Flash::set('success', 'Registration successful! Welcome to the Online School Library.');
            header('Location: ' . Auth::BASE . '/dashboard', true, 303);
            exit;
        }

        Flash::set('success', 'Account created! Please sign in with your credentials.');
        header('Location: ' . Auth::BASE . '/login', true, 303);
        exit;
    }

    public function logout(): void {
        Auth::logout();
        Flash::set('info', 'You have been safely signed out. See you next time!');
        header('Location: ' . Auth::BASE . '/login', true, 303);
        exit;
    }
}
