<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Borrowing;
use App\Models\Ebook;
use App\Models\ReadingLog;
use App\Services\Csrf;
use App\Services\EbookValidator;
use App\Support\Auth;
use App\Support\Flash;
use App\Support\View;

final class EbookController {
    public function index(): void {
        Auth::requireLogin();

        $search   = trim($_GET['q'] ?? '');
        $category = trim($_GET['category'] ?? '');

        $ebooks     = Ebook::all($search, $category);
        $categories = Ebook::getCategories();

        View::render('ebooks/index', [
            'pageTitle'  => 'E-Book Catalogue — School Library',
            'ebooks'     => $ebooks,
            'categories' => $categories,
            'search'     => $search,
            'category'   => $category,
            'flash'      => Flash::get(),
        ]);
    }

    public function show(): void {
        Auth::requireLogin();

        $id = (int)($_GET['id'] ?? 0);
        $ebook = Ebook::findById($id);

        if (!$ebook) {
            Flash::set('danger', 'E-Book not found in library catalogue.');
            header('Location: ' . Auth::BASE . '/ebooks', true, 303);
            exit;
        }

        $userId = Auth::id();
        $isBorrowedByMe = $userId ? Borrowing::hasActiveBorrow($id, $userId) : false;
        $myActiveBorrow = null;

        if ($isBorrowedByMe && $userId) {
            $borrows = Borrowing::byUser($userId, 'active');
            foreach ($borrows as $b) {
                if ((int)$b['ebook_id'] === $id) {
                    $myActiveBorrow = $b;
                    break;
                }
            }
        }

        View::render('ebooks/show', [
            'pageTitle'      => $ebook['title'] . ' — School Library',
            'ebook'          => $ebook,
            'isBorrowedByMe' => $isBorrowedByMe,
            'myActiveBorrow' => $myActiveBorrow,
            'flash'          => Flash::get(),
        ]);
    }

    public function read(): void {
        Auth::requireLogin();

        $id = (int)($_GET['id'] ?? 0);
        $ebook = Ebook::findById($id);

        if (!$ebook) {
            Flash::set('danger', 'E-Book not found.');
            header('Location: ' . Auth::BASE . '/ebooks', true, 303);
            exit;
        }

        $userId = Auth::id();
        $userRole = Auth::role();

        // Students can read if they have an active borrow (or if sample reading is allowed)
        $borrow = null;
        if ($userId) {
            $userBorrows = Borrowing::byUser($userId, 'active');
            foreach ($userBorrows as $b) {
                if ((int)$b['ebook_id'] === $id) {
                    $borrow = $b;
                    break;
                }
            }
        }

        // Read physical or storage sample text if present
        $bookContent = '';
        $storagePath = dirname(__DIR__, 2) . '/public' . str_replace('/online-school-library/public', '', $ebook['file_url']);
        if (is_file($storagePath)) {
            $bookContent = file_get_contents($storagePath) ?: '';
        }

        // Get latest reading log progress if borrow exists
        $currentPages = 0;
        $readingLogs = [];
        if ($borrow) {
            $currentPages = (int)($borrow['current_pages_read'] ?? 0);
            $readingLogs = ReadingLog::byBorrowId((int)$borrow['borrow_id']);
        }

        View::render('ebooks/read', [
            'pageTitle'    => 'Reader: ' . $ebook['title'],
            'ebook'        => $ebook,
            'borrow'       => $borrow,
            'currentPages' => $currentPages,
            'readingLogs'  => $readingLogs,
            'bookContent'  => $bookContent,
            'flash'        => Flash::get(),
        ]);
    }

    public function create(): void {
        Auth::requireLibrarian();

        View::render('ebooks/form', [
            'pageTitle' => 'Add New E-Book — Catalogue Manager',
            'ebook'     => null,
            'old'       => Flash::old(),
            'errors'    => Flash::errors(),
            'flash'     => Flash::get(),
        ]);
    }

    public function edit(): void {
        Auth::requireLibrarian();

        $id = (int)($_GET['id'] ?? 0);
        $ebook = Ebook::findById($id);

        if (!$ebook) {
            Flash::set('danger', 'E-Book not found.');
            header('Location: ' . Auth::BASE . '/ebooks', true, 303);
            exit;
        }

        View::render('ebooks/form', [
            'pageTitle' => 'Edit E-Book: ' . $ebook['title'],
            'ebook'     => $ebook,
            'old'       => Flash::old(),
            'errors'    => Flash::errors(),
            'flash'     => Flash::get(),
        ]);
    }

    public function save(): void {
        Auth::requireLibrarian();
        Csrf::verifyPost();

        $id = isset($_POST['ebook_id']) && $_POST['ebook_id'] !== '' ? (int)$_POST['ebook_id'] : null;

        $errors = EbookValidator::validate($_POST);
        if (!empty($errors)) {
            Flash::setErrors($errors);
            Flash::setOld($_POST);
            $redirect = $id ? Auth::BASE . '/ebooks/edit?id=' . $id : Auth::BASE . '/ebooks/create';
            header('Location: ' . $redirect, true, 303);
            exit;
        }

        $data = [
            'title'       => trim($_POST['title']),
            'author'      => trim($_POST['author']),
            'category'    => trim($_POST['category']),
            'file_url'    => trim($_POST['file_url']),
            'total_pages' => (int)$_POST['total_pages'],
            'description' => trim($_POST['description'] ?? ''),
            'cover_color' => trim($_POST['cover_color'] ?? '#163A5F'),
        ];

        if ($id) {
            Ebook::update($id, $data);
            Flash::set('success', 'E-Book "' . htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8') . '" updated successfully.');
        } else {
            $newId = Ebook::create($data);
            Flash::set('success', 'E-Book "' . htmlspecialchars($data['title'], ENT_QUOTES, 'UTF-8') . '" added to catalogue.');
        }

        header('Location: ' . Auth::BASE . '/ebooks', true, 303);
        exit;
    }

    public function delete(): void {
        Auth::requireLibrarian();
        Csrf::verifyPost();

        $id = (int)($_POST['ebook_id'] ?? 0);
        $ebook = Ebook::findById($id);

        if ($ebook) {
            Ebook::delete($id);
            Flash::set('success', 'E-Book "' . htmlspecialchars($ebook['title'], ENT_QUOTES, 'UTF-8') . '" was removed from the catalogue.');
        } else {
            Flash::set('danger', 'Unable to delete: E-Book record not found.');
        }

        header('Location: ' . Auth::BASE . '/ebooks', true, 303);
        exit;
    }
}
