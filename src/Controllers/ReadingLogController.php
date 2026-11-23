<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Borrowing;
use App\Models\Ebook;
use App\Models\ReadingLog;
use App\Services\Csrf;
use App\Services\ReadingLogValidator;
use App\Support\Auth;
use App\Support\Flash;
use App\Support\View;

final class ReadingLogController {
    public function index(): void {
        Auth::requireLogin();

        $user = Auth::user();
        $isLibrarian = Auth::isLibrarian();
        $userId = (int)$user['user_id'];

        if ($isLibrarian) {
            $logs = ReadingLog::all();
            $myActiveBorrows = [];
        } else {
            $logs = ReadingLog::byUser($userId);
            $myActiveBorrows = Borrowing::byUser($userId, 'active');
        }

        View::render('reading_logs/index', [
            'pageTitle'       => 'Reading Progress Logs — School Library',
            'logs'            => $logs,
            'isLibrarian'     => $isLibrarian,
            'myActiveBorrows' => $myActiveBorrows,
            'flash'           => Flash::get(),
        ]);
    }

    public function save(): void {
        Auth::requireLogin();
        Csrf::verifyPost();

        $borrowId  = (int)($_POST['borrow_id'] ?? 0);
        $pagesRead = (int)($_POST['pages_read'] ?? 0);
        $notes     = trim($_POST['notes'] ?? '');
        $logId     = isset($_POST['log_id']) && $_POST['log_id'] !== '' ? (int)$_POST['log_id'] : null;

        $borrow = Borrowing::findById($borrowId);
        if (!$borrow) {
            Flash::set('danger', 'Selected borrowing record not found.');
            header('Location: ' . Auth::BASE . '/reading-logs', true, 303);
            exit;
        }

        // Verify ownership if student
        if (!Auth::isLibrarian() && (int)$borrow['user_id'] !== Auth::id()) {
            Flash::set('danger', 'Unauthorized: You can only record progress on your own borrowed books.');
            header('Location: ' . Auth::BASE . '/reading-logs', true, 303);
            exit;
        }

        $totalPages = (int)($borrow['ebook_total_pages'] ?? 5000);
        $errors = ReadingLogValidator::validate([
            'borrow_id'  => $borrowId,
            'pages_read' => $pagesRead,
            'notes'      => $notes,
        ], $totalPages);

        if (!empty($errors)) {
            Flash::setErrors($errors);
            Flash::setOld($_POST);
            header('Location: ' . Auth::BASE . '/reading-logs', true, 303);
            exit;
        }

        if ($logId) {
            ReadingLog::update($logId, $pagesRead, $notes ?: null);
            Flash::set('success', 'Reading log updated to page ' . $pagesRead . '!');
        } else {
            ReadingLog::create($borrowId, $pagesRead, $notes ?: null);
            Flash::set('success', 'Logged ' . $pagesRead . ' pages read for "' . htmlspecialchars($borrow['ebook_title'], ENT_QUOTES, 'UTF-8') . '"!');
        }

        // If redirect URL provided (e.g., from Reader), send back there
        $redirect = !empty($_POST['redirect_to']) ? $_POST['redirect_to'] : Auth::BASE . '/reading-logs';
        header('Location: ' . $redirect, true, 303);
        exit;
    }

    public function delete(): void {
        Auth::requireLogin();
        Csrf::verifyPost();

        $logId = (int)($_POST['log_id'] ?? 0);
        $log = ReadingLog::findById($logId);

        if (!$log) {
            Flash::set('danger', 'Reading log not found.');
            header('Location: ' . Auth::BASE . '/reading-logs', true, 303);
            exit;
        }

        if (!Auth::isLibrarian() && (int)$log['user_id'] !== Auth::id()) {
            Flash::set('danger', 'Unauthorized: You can only delete your own reading logs.');
            header('Location: ' . Auth::BASE . '/reading-logs', true, 303);
            exit;
        }

        ReadingLog::delete($logId);
        Flash::set('success', 'Reading log entry removed.');
        header('Location: ' . Auth::BASE . '/reading-logs', true, 303);
        exit;
    }
}
