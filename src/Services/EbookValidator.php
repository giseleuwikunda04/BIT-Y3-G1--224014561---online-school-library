<?php
declare(strict_types=1);
namespace App\Services;

final class EbookValidator {
    public static function validate(array $data): array {
        $errors = [];

        $title    = trim($data['title'] ?? '');
        $author   = trim($data['author'] ?? '');
        $category = trim($data['category'] ?? '');
        $file_url = trim($data['file_url'] ?? '');
        $pages    = (int)($data['total_pages'] ?? 0);

        if ($title === '') {
            $errors['title'] = 'E-Book title is required.';
        } elseif (strlen($title) < 2 || strlen($title) > 200) {
            $errors['title'] = 'Title must be between 2 and 200 characters.';
        }

        if ($author === '') {
            $errors['author'] = 'Author name is required.';
        } elseif (strlen($author) < 2 || strlen($author) > 120) {
            $errors['author'] = 'Author must be between 2 and 120 characters.';
        }

        if ($category === '') {
            $errors['category'] = 'Category is required.';
        } elseif (strlen($category) > 100) {
            $errors['category'] = 'Category must not exceed 100 characters.';
        }

        if ($file_url === '') {
            $errors['file_url'] = 'E-Book file URL or storage path is required.';
        } elseif (strlen($file_url) > 255) {
            $errors['file_url'] = 'File URL must not exceed 255 characters.';
        }

        if ($pages <= 0 || $pages > 5000) {
            $errors['total_pages'] = 'Total pages must be a realistic number between 1 and 5000.';
        }

        return $errors;
    }
}
