<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Borrowing;
use App\Models\ReadingLog;
use App\Models\User;
use App\Services\Csrf;
use App\Support\Auth;
use App\Support\Flash;
use App\Support\View;

final class UserController {
    public function index(): void {
        Auth::requireLibrarian();

        $users = User::all();

        View::render('users/index', [
            'pageTitle' => 'User Management — School Library',
            'users'     => $users,
            'flash'     => Flash::get(),
        ]);
    }

    public function profile(): void {
        Auth::requireLogin();

        $userId = Auth::id();
        $user = User::findById($userId);

        if (!$user) {
            Flash::set('danger', 'User profile not found.');
            header('Location: ' . Auth::BASE . '/dashboard', true, 303);
            exit;
        }

        $myBorrows = Borrowing::byUser($userId);
        $myLogs    = ReadingLog::byUser($userId);

        View::render('users/profile', [
            'pageTitle' => 'My Profile — School Library',
            'user'      => $user,
            'borrows'   => $myBorrows,
            'logs'      => $myLogs,
            'errors'    => Flash::errors(),
            'flash'     => Flash::get(),
        ]);
    }

    public function updateProfile(): void {
        Auth::requireLogin();
        Csrf::verifyPost();

        $userId   = Auth::id();
        $username = trim($_POST['username'] ?? '');
        $email    = trim($_POST['email'] ?? '');
        $newPass  = (string)($_POST['new_password'] ?? '');
        $confirm  = (string)($_POST['confirm_password'] ?? '');

        $errors = [];
        if ($username === '') {
            $errors['username'] = 'Username cannot be blank.';
        }
        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'A valid email is required.';
        }

        // Check if username/email taken by someone else
        $existingUser = User::findByUsername($username);
        if ($existingUser && (int)$existingUser['user_id'] !== $userId) {
            $errors['username'] = 'That username is taken by another account.';
        }
        $existingEmail = User::findByEmail($email);
        if ($existingEmail && (int)$existingEmail['user_id'] !== $userId) {
            $errors['email'] = 'That email is registered to another account.';
        }

        if ($newPass !== '') {
            if (strlen($newPass) < 6) {
                $errors['new_password'] = 'Password must be at least 6 characters.';
            }
            if ($newPass !== $confirm) {
                $errors['confirm_password'] = 'Password confirmation does not match.';
            }
        }

        if (!empty($errors)) {
            Flash::setErrors($errors);
            header('Location: ' . Auth::BASE . '/profile', true, 303);
            exit;
        }

        $currentUser = User::findById($userId);
        User::update($userId, [
            'username' => $username,
            'email'    => $email,
            'role'     => $currentUser['role'],
        ]);

        if ($newPass !== '') {
            User::updatePassword($userId, password_hash($newPass, PASSWORD_DEFAULT));
        }

        // Update session
        $updated = User::findById($userId);
        if ($updated) {
            Auth::login($updated);
        }

        Flash::set('success', 'Profile updated successfully.');
        header('Location: ' . Auth::BASE . '/profile', true, 303);
        exit;
    }

    public function updateRole(): void {
        Auth::requireLibrarian();
        Csrf::verifyPost();

        $targetId = (int)($_POST['user_id'] ?? 0);
        $newRole  = trim($_POST['role'] ?? 'student');

        if (!in_array($newRole, ['student', 'librarian'], true)) {
            Flash::set('danger', 'Invalid role specified.');
            header('Location: ' . Auth::BASE . '/users', true, 303);
            exit;
        }

        // Prevent self-demotion if the current librarian is the only one
        if ($targetId === Auth::id() && $newRole !== 'librarian') {
            Flash::set('warning', 'You cannot demote yourself from the librarian role.');
            header('Location: ' . Auth::BASE . '/users', true, 303);
            exit;
        }

        $target = User::findById($targetId);
        if ($target) {
            User::update($targetId, [
                'username' => $target['username'],
                'email'    => $target['email'],
                'role'     => $newRole,
            ]);
            Flash::set('success', 'Role for ' . htmlspecialchars($target['username'], ENT_QUOTES, 'UTF-8') . ' updated to ' . ucfirst($newRole) . '.');
        }

        header('Location: ' . Auth::BASE . '/users', true, 303);
        exit;
    }

    public function delete(): void {
        Auth::requireLibrarian();
        Csrf::verifyPost();

        $targetId = (int)($_POST['user_id'] ?? 0);
        if ($targetId === Auth::id()) {
            Flash::set('danger', 'You cannot delete your own logged-in account.');
            header('Location: ' . Auth::BASE . '/users', true, 303);
            exit;
        }

        $target = User::findById($targetId);
        if ($target) {
            User::delete($targetId);
            Flash::set('success', 'User ' . htmlspecialchars($target['username'], ENT_QUOTES, 'UTF-8') . ' was removed.');
        } else {
            Flash::set('danger', 'User not found.');
        }

        header('Location: ' . Auth::BASE . '/users', true, 303);
        exit;
    }
}
