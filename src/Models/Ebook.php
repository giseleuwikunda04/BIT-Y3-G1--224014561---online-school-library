<?php
declare(strict_types=1);
namespace App\Models;

use App\Config\Database;
use PDO;

final class Ebook {
    public static function create(array $data): int {
        $pdo = Database::pdo();
        $stmt = $pdo->prepare(
            "INSERT INTO `ebooks` (`title`, `author`, `category`, `file_url`, `total_pages`, `description`, `cover_color`)
             VALUES (:title, :author, :category, :file_url, :total_pages, :description, :cover_color)"
        );
        $stmt->execute([
            'title'       => $data['title'],
            'author'      => $data['author'],
            'category'    => $data['category'],
            'file_url'    => $data['file_url'],
            'total_pages' => (int)($data['total_pages'] ?? 100),
            'description' => $data['description'] ?? null,
            'cover_color' => $data['cover_color'] ?? '#163A5F',
        ]);
        return (int)$pdo->lastInsertId();
    }

    public static function findById(int $id): ?array {
        $stmt = Database::pdo()->prepare(
            "SELECT * FROM `ebooks` WHERE `ebook_id` = :id LIMIT 1"
        );
        $stmt->execute(['id' => $id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public static function all(string $search = '', string $category = ''): array {
        $sql = "SELECT e.*, 
                (SELECT COUNT(*) FROM `borrowings` b WHERE b.ebook_id = e.ebook_id AND b.returned_at IS NULL) AS active_borrow_count
                FROM `ebooks` e WHERE 1=1";
        $params = [];

        if ($search !== '') {
            $sql .= " AND (e.title LIKE :s1 OR e.author LIKE :s2 OR e.category LIKE :s3)";
            $term = "%{$search}%";
            $params['s1'] = $term;
            $params['s2'] = $term;
            $params['s3'] = $term;
        }

        if ($category !== '') {
            $sql .= " AND e.category = :cat";
            $params['cat'] = $category;
        }

        $sql .= " ORDER BY e.ebook_id DESC";

        $stmt = Database::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public static function countAll(): int {
        return (int)Database::pdo()->query("SELECT COUNT(*) FROM `ebooks`")->fetchColumn();
    }

    public static function getCategories(): array {
        $stmt = Database::pdo()->query(
            "SELECT DISTINCT `category` FROM `ebooks` ORDER BY `category` ASC"
        );
        return $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    public static function getRecent(int $limit = 6): array {
        $stmt = Database::pdo()->prepare(
            "SELECT * FROM `ebooks` ORDER BY `ebook_id` DESC LIMIT :lim"
        );
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll();
    }

    public static function isAvailable(int $ebookId): bool {
        // In school digital library, multiple students can read simultaneously or borrow
        // We track whether there's an active borrow for status display
        $stmt = Database::pdo()->prepare(
            "SELECT COUNT(*) FROM `borrowings` WHERE `ebook_id` = :id AND `returned_at` IS NULL"
        );
        $stmt->execute(['id' => $ebookId]);
        return ((int)$stmt->fetchColumn()) === 0;
    }

    public static function update(int $id, array $data): bool {
        $stmt = Database::pdo()->prepare(
            "UPDATE `ebooks` 
             SET `title` = :title, `author` = :author, `category` = :category, 
                 `file_url` = :file_url, `total_pages` = :total_pages, 
                 `description` = :description, `cover_color` = :cover_color
             WHERE `ebook_id` = :id"
        );
        return $stmt->execute([
            'id'          => $id,
            'title'       => $data['title'],
            'author'      => $data['author'],
            'category'    => $data['category'],
            'file_url'    => $data['file_url'],
            'total_pages' => (int)($data['total_pages'] ?? 100),
            'description' => $data['description'] ?? null,
            'cover_color' => $data['cover_color'] ?? '#163A5F',
        ]);
    }

    public static function delete(int $id): bool {
        $stmt = Database::pdo()->prepare("DELETE FROM `ebooks` WHERE `ebook_id` = :id");
        return $stmt->execute(['id' => $id]);
    }
}
