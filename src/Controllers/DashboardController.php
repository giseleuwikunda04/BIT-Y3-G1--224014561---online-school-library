<?php
declare(strict_types=1);
namespace App\Controllers;

use App\Models\Borrowing;
use App\Models\Ebook;
use App\Models\ReadingLog;
use App\Models\User;
use App\Support\Auth;
use App\Support\Flash;
use App\Support\View;

final class DashboardController {
    public function index(): void {
        Auth::requireLogin();

        $user = Auth::user();
        $isLibrarian = Auth::isLibrarian();
        $userId = (int)$user['user_id'];

        if ($isLibrarian) {
            $totalBooks     = Ebook::countAll();
            $totalStudents  = User::countByRole('student');
            $activeBorrows  = Borrowing::countActive();
            $overdueBorrows = Borrowing::countOverdue();
            $recentBorrows  = Borrowing::all();
            $recentBooks    = Ebook::getRecent(6);
            $totalReadPages = ReadingLog::totalReadPages();

            View::render('dashboard/librarian', [
                'pageTitle'      => 'Librarian Dashboard — School Library',
                'totalBooks'     => $totalBooks,
                'totalStudents'  => $totalStudents,
                'activeBorrows'  => $activeBorrows,
                'overdueBorrows' => $overdueBorrows,
                'recentBorrows'  => array_slice($recentBorrows, 0, 5),
                'recentBooks'    => $recentBooks,
                'totalReadPages' => $totalReadPages,
                'flash'          => Flash::get(),
            ]);
        } else {
            // Student Dashboard
            $myBorrows       = Borrowing::byUser($userId);
            $myActiveBorrows = Borrowing::byUser($userId, 'active');
            $myOverdueBorrows= Borrowing::byUser($userId, 'overdue');
            $myReadingLogs   = ReadingLog::byUser($userId);
            $featuredBooks   = Ebook::getRecent(4);

            $myTotalPagesRead = 0;
            foreach ($myActiveBorrows as $b) {
                $myTotalPagesRead += (int)($b['current_pages_read'] ?? 0);
            }

            View::render('dashboard/student', [
                'pageTitle'        => 'My Reading Desk — School Library',
                'myBorrows'        => $myBorrows,
                'myActiveBorrows'  => $myActiveBorrows,
                'myOverdueBorrows' => $myOverdueBorrows,
                'myReadingLogs'    => array_slice($myReadingLogs, 0, 5),
                'featuredBooks'    => $featuredBooks,
                'myTotalPagesRead' => $myTotalPagesRead,
                'flash'            => Flash::get(),
            ]);
        }
    }
}
