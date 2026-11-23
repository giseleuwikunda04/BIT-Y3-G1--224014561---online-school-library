<?php
declare(strict_types=1);
namespace App\Models;

use App\Config\Database;
use PDO;

final class ReadingLog {
    public static function create(int $borrowId, int $pagesRead, ?string $notes = null): int {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            "INSERT INTO `reading_logs` (`borrow_id`, `pages_read`, `notes`)
             VALUES (:borrow_id, :pages_read, :notes)"
        );
        $stmt->execute([
            'borrow_id'  => $borrowId,
            'pages_read' => $pagesRead,
            'notes'      => $notes,
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function findById(int $id): ?array {
        $stmt = Database::pdo()->prepare(
            "SELECT r.*, 
                    b.user_id, b.ebook_id, b.borrowed_at, b.due_date, b.returned_at,
                    e.title AS ebook_title, e.author AS ebook_author, e.total_pages AS ebook_total_pages,
                    u.username AS student_username
             FROM `reading_logs` r
             JOIN `borrowings` b ON r.borrow_id = b.borrow_id
             JOIN `ebooks` e ON b.ebook_id = e.ebook_id
             JOIN `users` u ON b.user_id = u.user_id
             WHERE r.log_id = :id
             LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function byBorrowId(int $borrowId): array {
        $stmt = Database::pdo()->prepare(
            "SELECT r.*, e.total_pages AS ebook_total_pages
             FROM `reading_logs` r
             JOIN `borrowings` b ON r.borrow_id = b.borrow_id
             JOIN `ebooks` e ON b.ebook_id = e.ebook_id
             WHERE r.borrow_id = :borrow_id
             ORDER BY r.log_id DESC"
        );
        $stmt->execute(['borrow_id' => $borrowId]);
        return $stmt->fetchAll();
    }

    public static function byUser(int $userId): array {
        $stmt = Database::pdo()->prepare(
            "SELECT r.*, 
                    e.ebook_id, e.title AS ebook_title, e.author AS ebook_author, 
                    e.total_pages AS ebook_total_pages, e.cover_color,
                    b.borrowed_at, b.due_date, b.returned_at
             FROM `reading_logs` r
             JOIN `borrowings` b ON r.borrow_id = b.borrow_id
             JOIN `ebooks` e ON b.ebook_id = e.ebook_id
             WHERE b.user_id = :user_id
             ORDER BY r.log_id DESC"
        );
        $stmt->execute(['user_id' => $userId]);
        return $stmt->fetchAll();
    }

    public static function all(): array {
        $stmt = Database::pdo()->query(
            "SELECT r.*, 
                    e.title AS ebook_title, e.author AS ebook_author, e.total_pages AS ebook_total_pages,
                    u.username AS student_username, u.email AS student_email,
                    b.borrowed_at
             FROM `reading_logs` r
             JOIN `borrowings` b ON r.borrow_id = b.borrow_id
             JOIN `ebooks` e ON b.ebook_id = e.ebook_id
             JOIN `users` u ON b.user_id = u.user_id
             ORDER BY r.log_id DESC"
        );
        return $stmt->fetchAll();
    }

    public static function latestForBorrow(int $borrowId): ?array {
        $stmt = Database::pdo()->prepare(
            "SELECT * FROM `reading_logs` WHERE `borrow_id` = :id ORDER BY `log_id` DESC LIMIT 1"
        );
        $stmt->execute(['id' => $borrowId]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function update(int $logId, int $pagesRead, ?string $notes = null): bool {
        $stmt = Database::pdo()->prepare(
            "UPDATE `reading_logs` 
             SET `pages_read` = :pages_read, `notes` = :notes, `last_read_at` = NOW()
             WHERE `log_id` = :id"
        );
        return $stmt->execute([
            'id'         => $logId,
            'pages_read' => $pagesRead,
            'notes'      => $notes,
        ]);
    }

    public static function delete(int $logId): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM `reading_logs` WHERE `log_id` = :id");
        return $stmt->execute(['id' => $logId]);
    }

    public static function countAll(): int {
        return (int)Database::pdo()->query("SELECT COUNT(*) FROM `reading_logs`")->fetchColumn();
    }

    public static function totalReadPages(): int {
        return (int)Database::pdo()->query(
            "SELECT COALESCE(SUM(latest_pages), 0) FROM (
                SELECT MAX(pages_read) AS latest_pages FROM `reading_logs` GROUP BY `borrow_id`
             ) t"
        )->fetchColumn();
    }
}
