<?php
declare(strict_types=1);
namespace App\Models;

use App\Config\Database;
use PDO;

final class User {
    public static function create(array $data): int {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            "INSERT INTO `users` (`username`, `email`, `password_hash`, `role`)
             VALUES (:username, :email, :password_hash, :role)"
        );
        $stmt->execute([
            'username'      => $data['username'],
            'email'         => $data['email'],
            'password_hash' => $data['password_hash'],
            'role'          => $data['role'] ?? 'student',
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function findById(int $id): ?array {
        $stmt = Database::pdo()->prepare(
            "SELECT `user_id`, `username`, `email`, `role`, `created_at`
             FROM `users` WHERE `user_id` = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByUsername(string $username): ?array {
        $stmt = Database::pdo()->prepare(
            "SELECT * FROM `users` WHERE `username` = :username LIMIT 1"
        );
        $stmt->execute(['username' => $username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByEmail(string $email): ?array {
        $stmt = Database::pdo()->prepare(
            "SELECT * FROM `users` WHERE `email` = :email LIMIT 1"
        );
        $stmt->execute(['email' => $email]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function findByIdentity(string $identity): ?array {
        $stmt = Database::pdo()->prepare(
            "SELECT * FROM `users` WHERE `username` = :u OR `email` = :e LIMIT 1"
        );
        $stmt->execute(['u' => $identity, 'e' => $identity]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(): array {
        $stmt = Database::pdo()->query(
            "SELECT `user_id`, `username`, `email`, `role`, `created_at`
             FROM `users` ORDER BY `created_at` DESC"
        );
        return $stmt->fetchAll();
    }

    public static function getStudents(): array {
        $stmt = Database::pdo()->query(
            "SELECT `user_id`, `username`, `email`, `created_at`
             FROM `users` WHERE `role` = 'student' ORDER BY `username` ASC"
        );
        return $stmt->fetchAll();
    }

    public static function countAll(): int {
        return (int)Database::pdo()->query("SELECT COUNT(*) FROM `users`")->fetchColumn();
    }

    public static function countByRole(string $role): int {
        $stmt = Database::pdo()->prepare("SELECT COUNT(*) FROM `users` WHERE `role` = :role");
        $stmt->execute(['role' => $role]);
        return (int)$stmt->fetchColumn();
    }

    public static function update(int $id, array $data): bool {
        $stmt = Database::pdo()->prepare(
            "UPDATE `users` SET `username` = :username, `email` = :email, `role` = :role
             WHERE `user_id` = :id"
        );
        return $stmt->execute([
            'id'       => $id,
            'username' => $data['username'],
            'email'    => $data['email'],
            'role'     => $data['role'] ?? 'student',
        ]);
    }

    public static function updatePassword(int $id, string $hash): bool {
        $stmt = Database::pdo()->prepare(
            "UPDATE `users` SET `password_hash` = :hash WHERE `user_id` = :id"
        );
        return $stmt->execute(['id' => $id, 'hash' => $hash]);
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM `users` WHERE `user_id` = :id");
        return $stmt->execute(['id' => $id]);
    }
}
