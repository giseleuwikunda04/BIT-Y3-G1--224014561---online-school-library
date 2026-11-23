<?php
declare(strict_types=1);
namespace App\Services;

final class BorrowingValidator {
    public static function validate(array $data): array {
        $errors = [];

        $ebook_id = (int)($data['ebook_id'] ?? 0);
        $user_id  = (int)($data['user_id'] ?? 0);
        $due_date = trim($data['due_date'] ?? '');

        if ($ebook_id <= 0) {
            $errors['ebook_id'] = 'A valid e-book must be selected.';
        }

        if ($user_id <= 0) {
            $errors['user_id'] = 'A valid student user must be selected.';
        }

        if ($due_date === '') {
            $errors['due_date'] = 'Due date is required.';
        } else {
            $d = strtotime($due_date);
            if ($d === false) {
                $errors['due_date'] = 'Invalid due date format.';
            }
        }

        return $errors;
    }
}
