<?php
declare(strict_types=1);
namespace App\Models;

use App\Config\Database;
use PDO;

final class Borrowing {
    public static function create(int $ebookId, int $userId, string $dueDate): int {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            "INSERT INTO `borrowings` (`ebook_id`, `user_id`, `due_date`)
             VALUES (:ebook_id, :user_id, :due_date)"
        );
        $stmt->execute([
            'ebook_id' => $ebookId,
            'user_id'  => $userId,
            'due_date' => $dueDate,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function findById(int $id): ?array {
        $stmt = Database::pdo()->prepare(
            "SELECT b.*, 
                    e.title AS ebook_title, e.author AS ebook_author, e.category AS ebook_category,
                    e.file_url AS ebook_file_url, e.total_pages AS ebook_total_pages, e.cover_color,
                    u.username AS student_username, u.email AS student_email,
                    (SELECT COALESCE(MAX(r.pages_read), 0) FROM `reading_logs` r WHERE r.borrow_id = b.borrow_id) AS current_pages_read
             FROM `borrowings` b
             JOIN `ebooks` e ON b.ebook_id = e.ebook_id
             JOIN `users` u ON b.user_id = u.user_id
             WHERE b.borrow_id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(?string $filter = null): array {
        $sql = "SELECT b.*, 
                       e.title AS ebook_title, e.author AS ebook_author, e.category AS ebook_category,
                       e.total_pages AS ebook_total_pages, e.cover_color,
                       u.username AS student_username, u.email AS student_email,
                       (SELECT COALESCE(MAX(r.pages_read), 0) FROM `reading_logs` r WHERE r.borrow_id = b.borrow_id) AS current_pages_read
                FROM `borrowings` b
                JOIN `ebooks` e ON b.ebook_id = e.ebook_id
                JOIN `users` u ON b.user_id = u.user_id";

        if ($filter === 'active') {
            $sql .= " WHERE b.returned_at IS NULL AND b.due_date >= NOW()";
        } elseif ($filter === 'overdue') {
            $sql .= " WHERE b.returned_at IS NULL AND b.due_date < NOW()";
        } elseif ($filter === 'returned') {
            $sql .= " WHERE b.returned_at IS NOT NULL";
        }

        $sql .= " ORDER BY b.borrow_id DESC";

        $stmt = Database::pdo()->query($sql);
        return $stmt->fetchAll();
    }

    public static function byUser(int $userId, ?string $filter = null): array {
        $sql = "SELECT b.*, 
                       e.title AS ebook_title, e.author AS ebook_author, e.category AS ebook_category,
                       e.file_url AS ebook_file_url, e.total_pages AS ebook_total_pages, e.cover_color,
                       (SELECT COALESCE(MAX(r.pages_read), 0) FROM `reading_logs` r WHERE r.borrow_id = b.borrow_id) AS current_pages_read
                FROM `borrowings` b
                JOIN `ebooks` e ON b.ebook_id = e.ebook_id
                WHERE b.user_id = :user_id";

        if ($filter === 'active') {
            $sql .= " AND b.returned_at IS NULL AND b.due_date >= NOW()";
        } elseif ($filter === 'overdue') {
            $sql .= " AND b.returned_at IS NULL AND b.due_date < NOW()";
        } elseif ($filter === 'returned') {
            $sql .= " AND b.returned_at IS NOT NULL";
        }

        $sql .= " ORDER BY b.borrow_id DESC";

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function hasActiveBorrow(int $ebookId, int $userId): bool {
        $stmt = Database::pdo()->prepare(
            "SELECT COUNT(*) FROM `borrowings` 
             WHERE `ebook_id` = :ebook_id AND `user_id` = :user_id AND `returned_at` IS NULL"
        );
        $stmt->execute([
            'ebook_id' => $ebookId,
            'user_id'  => $userId,
        ]);
        return ((int)$stmt->fetchColumn()) > 0;
    }

    public static function markReturned(int $borrowId): bool {
        $stmt = Database::pdo()->prepare(
            "UPDATE `borrowings` SET `returned_at` = NOW() WHERE `borrow_id` = :id"
        );
        return $stmt->execute(['id' => $borrowId]);
    }

    public static function updateDueDate(int $borrowId, string $newDueDate): bool {
        $stmt = Database::pdo()->prepare(
            "UPDATE `borrowings` SET `due_date` = :due_date WHERE `borrow_id` = :id"
        );
        return $stmt->execute(['id' => $borrowId, 'due_date' => $newDueDate]);
    }

    public static function delete(int $borrowId): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM `borrowings` WHERE `borrow_id` = :id");
        return $stmt->execute(['id' => $borrowId]);
    }

    public static function countAll(): int {
        return (int)Database::pdo()->query("SELECT COUNT(*) FROM `borrowings`")->fetchColumn();
    }

    public static function countActive(): int {
        return (int)Database::pdo()->query(
            "SELECT COUNT(*) FROM `borrowings` WHERE `returned_at` IS NULL AND `due_date` >= NOW()"
        )->fetchColumn();
    }

    public static function countOverdue(): int {
        return (int)Database::pdo()->query(
            "SELECT COUNT(*) FROM `borrowings` WHERE `returned_at` IS NULL AND `due_date` < NOW()"
        )->fetchColumn();
    }

    public static function countReturned(): int {
        return (int)Database::pdo()->query(
            "SELECT COUNT(*) FROM `borrowings` WHERE `returned_at` IS NOT NULL"
        )->fetchColumn();
    }
}
