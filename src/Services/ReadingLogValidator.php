<?php
declare(strict_types=1);
namespace App\Services;

final class ReadingLogValidator {
    public static function validate(array $data, int $totalPages = 5000): array {
        $errors = [];

        $borrow_id  = (int)($data['borrow_id'] ?? 0);
        $pages_read = (int)($data['pages_read'] ?? 0);
        $notes      = trim($data['notes'] ?? '');

        if ($borrow_id <= 0) {
            $errors['borrow_id'] = 'Valid borrowing record required.';
        }

        if ($pages_read < 0) {
            $errors['pages_read'] = 'Pages read cannot be negative.';
        } elseif ($pages_read > $totalPages) {
            $errors['pages_read'] = "Pages read cannot exceed the book total ($totalPages pages).";
        }

        if (strlen($notes) > 255) {
            $errors['notes'] = 'Notes cannot exceed 255 characters.';
        }

        return $errors;
    }
}
