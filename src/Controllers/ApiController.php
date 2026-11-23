<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Borrowing;
use App\Models\Ebook;
use App\Models\ReadingLog;
use App\Services\Csrf;
use App\Services\ReadingLogValidator;
use App\Support\Auth;

final class ApiController {
    private static function json(array $data, int $status = 200): void {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * 1. Live E-Book Search API Endpoint
     * GET /api/ebooks/search?q=...&category=...
     */
    public function searchEbooks(): void {
        $q        = trim($_GET['q'] ?? '');
        $category = trim($_GET['category'] ?? '');

        $results = Ebook::all($q, $category);

        $payload = array_map(function ($book) {
            return [
                'ebook_id'      => (int)$book['ebook_id'],
                'title'         => $book['title'],
                'author'        => $book['author'],
                'category'      => $book['category'],
                'total_pages'   => (int)$book['total_pages'],
                'cover_color'   => $book['cover_color'],
                'description'   => $book['description'],
                'file_url'      => $book['file_url'],
                'active_borrows'=> (int)($book['active_borrow_count'] ?? 0),
                'url'           => Auth::BASE . '/ebooks/show?id=' . $book['ebook_id'],
                'read_url'      => Auth::BASE . '/ebooks/read?id=' . $book['ebook_id'],
            ];
        }, $results);

        self::json([
            'success' => true,
            'count'   => count($payload),
            'query'   => $q,
            'category'=> $category,
            'data'    => $payload,
        ]);
    }

    /**
     * 2. Asynchronous Reading Progress Logger Endpoint
     * POST /api/reading-logs/log
     */
    public function logProgress(): void {
        if (!Auth::check()) {
            self::json(['success' => false, 'error' => 'Authentication required'], 401);
        }

        // Support JSON payload or Form-Data payload
        $raw = file_get_contents('php://input');
        $json = json_decode($raw, true);
        $input = is_array($json) ? $json : $_POST;

        // Verify CSRF
        $token = $input['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? null;
        if (!Csrf::validate($token)) {
            self::json(['success' => false, 'error' => 'Invalid or expired CSRF token'], 403);
        }

        $borrowId  = (int)($input['borrow_id'] ?? 0);
        $pagesRead = (int)($input['pages_read'] ?? 0);
        $notes     = trim((string)($input['notes'] ?? ''));

        $borrow = Borrowing::findById($borrowId);
        if (!$borrow) {
            self::json(['success' => false, 'error' => 'Borrowing record not found'], 404);
        }

        if (!Auth::isLibrarian() && (int)$borrow['user_id'] !== Auth::id()) {
            self::json(['success' => false, 'error' => 'Forbidden: not your borrowing record'], 403);
        }

        $totalPages = (int)($borrow['ebook_total_pages'] ?? 5000);
        $errors = ReadingLogValidator::validate([
            'borrow_id'  => $borrowId,
            'pages_read' => $pagesRead,
            'notes'      => $notes,
        ], $totalPages);

        if (!empty($errors)) {
            self::json(['success' => false, 'errors' => $errors], 422);
        }

        $logId = ReadingLog::create($borrowId, $pagesRead, $notes ?: null);
        $percent = $totalPages > 0 ? min(100, round(($pagesRead / $totalPages) * 100)) : 0;

        self::json([
            'success'    => true,
            'message'    => "Saved progress: page {$pagesRead} of {$totalPages} ({$percent}%)",
            'log_id'     => $logId,
            'pages_read' => $pagesRead,
            'percent'    => $percent,
            'last_read'  => date('M j, Y H:i'),
        ]);
    }

    /**
     * 3. Real-Time Library Statistics API Endpoint
     * GET /api/stats
     */
    public function stats(): void {
        $data = [
            'total_books'     => Ebook::countAll(),
            'active_borrows'  => Borrowing::countActive(),
            'overdue_borrows' => Borrowing::countOverdue(),
            'returned_borrows'=> Borrowing::countReturned(),
            'total_logs'      => ReadingLog::countAll(),
            'total_pages_read'=> ReadingLog::totalReadPages(),
            'timestamp'       => date('c'),
        ];

        self::json(['success' => true, 'data' => $data]);
    }

    /**
     * 4. E-Book Availability API Endpoint
     * GET /api/borrowings/check?ebook_id=...
     */
    public function checkAvailability(): void {
        $ebookId = (int)($_GET['ebook_id'] ?? 0);
        $ebook = Ebook::findById($ebookId);

        if (!$ebook) {
            self::json(['success' => false, 'error' => 'E-Book not found'], 404);
        }

        $userId = Auth::id();
        $isBorrowedByMe = $userId ? Borrowing::hasActiveBorrow($ebookId, $userId) : false;

        self::json([
            'success'          => true,
            'ebook_id'         => $ebookId,
            'title'            => $ebook['title'],
            'borrowed_by_user' => $isBorrowedByMe,
            'available_to_read'=> true,
        ]);
    }
}
