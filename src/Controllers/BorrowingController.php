<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Borrowing;
use App\Models\Ebook;
use App\Models\ReadingLog;
use App\Models\User;
use App\Services\BorrowingValidator;
use App\Services\Csrf;
use App\Support\Auth;
use App\Support\Flash;
use App\Support\View;

final class BorrowingController {
    public function index(): void {
        Auth::requireLogin();

        $user = Auth::user();
        $isLibrarian = Auth::isLibrarian();
        $filter = trim($_GET['status'] ?? 'all');
        if (!in_array($filter, ['all', 'active', 'overdue', 'returned'], true)) {
            $filter = 'all';
        }

        $filterParam = $filter === 'all' ? null : $filter;

        if ($isLibrarian) {
            $borrowings = Borrowing::all($filterParam);
            $students   = User::getStudents();
            $ebooks     = Ebook::all();
        } else {
            $borrowings = Borrowing::byUser((int)$user['user_id'], $filterParam);
            $students   = [];
            $ebooks     = [];
        }

        View::render('borrowings/index', [
            'pageTitle'   => $isLibrarian ? 'Manage Borrowings — School Library' : 'My Borrowed E-Books — School Library',
            'borrowings'  => $borrowings,
            'filter'      => $filter,
            'isLibrarian' => $isLibrarian,
            'students'    => $students,
            'ebooks'      => $ebooks,
            'flash'       => Flash::get(),
        ]);
    }

    public function borrow(): void {
        Auth::requireLogin();
        Csrf::verifyPost();

        $ebookId = (int)($_POST['ebook_id'] ?? 0);
        $userId  = Auth::isLibrarian() && !empty($_POST['user_id']) ? (int)$_POST['user_id'] : Auth::id();

        if ($ebookId <= 0 || !$userId) {
            Flash::set('danger', 'Invalid book or student selected for borrowing.');
            header('Location: ' . Auth::BASE . '/ebooks', true, 303);
            exit;
        }

        $ebook = Ebook::findById($ebookId);
        if (!$ebook) {
            Flash::set('danger', 'The selected e-book does not exist in the library.');
            header('Location: ' . Auth::BASE . '/ebooks', true, 303);
            exit;
        }

        // Check if student already has an active borrow of this book
        if (Borrowing::hasActiveBorrow($ebookId, $userId)) {
            Flash::set('warning', 'You currently already have an active borrowing record for "' . htmlspecialchars($ebook['title'], ENT_QUOTES, 'UTF-8') . '".');
            header('Location: ' . Auth::BASE . '/borrowings', true, 303);
            exit;
        }

        // Default 14 days loan period if not specified
        $dueDays = !empty($_POST['due_days']) ? (int)$_POST['due_days'] : 14;
        if ($dueDays < 1 || $dueDays > 60) {
            $dueDays = 14;
        }
        $dueDate = date('Y-m-d 23:59:59', strtotime("+{$dueDays} days"));

        $borrowId = Borrowing::create($ebookId, $userId, $dueDate);

        // Create an initial reading log entry at page 0
        ReadingLog::create($borrowId, 0, 'Borrowed e-book and started reading.');

        Flash::set('success', 'E-book "' . htmlspecialchars($ebook['title'], ENT_QUOTES, 'UTF-8') . '" borrowed successfully! Due on ' . date('M j, Y', strtotime($dueDate)) . '.');
        header('Location: ' . Auth::BASE . '/borrowings', true, 303);
        exit;
    }

    public function returnBook(): void {
        Auth::requireLogin();
        Csrf::verifyPost();

        $borrowId = (int)($_POST['borrow_id'] ?? 0);
        $borrow = Borrowing::findById($borrowId);

        if (!$borrow) {
            Flash::set('danger', 'Borrowing record not found.');
            header('Location: ' . Auth::BASE . '/borrowings', true, 303);
            exit;
        }

        // Check permission: Student can return their own book; Librarian can return any book
        if (!Auth::isLibrarian() && (int)$borrow['user_id'] !== Auth::id()) {
            Flash::set('danger', 'Unauthorized: You can only return books borrowed under your account.');
            header('Location: ' . Auth::BASE . '/borrowings', true, 303);
            exit;
        }

        Borrowing::markReturned($borrowId);
        Flash::set('success', 'E-Book "' . htmlspecialchars($borrow['ebook_title'], ENT_QUOTES, 'UTF-8') . '" marked as returned. Thank you!');
        header('Location: ' . Auth::BASE . '/borrowings', true, 303);
        exit;
    }

    public function delete(): void {
        Auth::requireLibrarian();
        Csrf::verifyPost();

        $borrowId = (int)($_POST['borrow_id'] ?? 0);
        $borrow = Borrowing::findById($borrowId);

        if ($borrow) {
            Borrowing::delete($borrowId);
            Flash::set('success', 'Borrowing record #' . $borrowId . ' was deleted.');
        } else {
            Flash::set('danger', 'Borrowing record not found.');
        }

        header('Location: ' . Auth::BASE . '/borrowings', true, 303);
        exit;
    }
}
