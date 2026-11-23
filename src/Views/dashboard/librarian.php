<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Support\Auth;
use App\Support\View;
?>

<div class="dashboard-container">
    <!-- Welcome Header Banner -->
    <div class="dashboard-hero">
        <div class="hero-text">
            <span class="hero-badge">Librarian Operations Console</span>
            <h1>Welcome, <?= View::e(Auth::username()) ?> 👋</h1>
            <p>Manage the school e-book catalogue, oversee student loans, monitor reading milestones, and ensure prompt returns.</p>
        </div>
        <div class="hero-actions">
            <a href="<?= Auth::BASE ?>/ebooks/create" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add New E-Book</span>
            </a>
            <a href="<?= Auth::BASE ?>/borrowings" class="btn btn-outline-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path></svg>
                <span>Manage Borrowings</span>
            </a>
        </div>
    </div>

    <!-- Metric Stat Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total E-Books</span>
                <strong class="stat-value" id="statTotalBooks"><?= (int)$totalBooks ?></strong>
                <span class="stat-meta">In school catalogue</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle></svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Enrolled Students</span>
                <strong class="stat-value"><?= (int)$totalStudents ?></strong>
                <span class="stat-meta">Active school readers</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-amber">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path></svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Active Loans</span>
                <strong class="stat-value" id="statActiveBorrows"><?= (int)$activeBorrows ?></strong>
                <span class="stat-meta">Currently in reading</span>
            </div>
        </div>

        <div class="stat-card <?= $overdueBorrows > 0 ? 'card-alert' : '' ?>">
            <div class="stat-icon icon-red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Overdue Loans</span>
                <strong class="stat-value" id="statOverdueBorrows"><?= (int)$overdueBorrows ?></strong>
                <span class="stat-meta">Require student attention</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-purple">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Pages Read</span>
                <strong class="stat-value" id="statTotalPages"><?= number_format($totalReadPages) ?></strong>
                <span class="stat-meta">Student learning impact</span>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Split Sections -->
    <div class="dashboard-grid">
        <!-- Recent Loans Table -->
        <div class="panel-card col-span-2">
            <div class="panel-header">
                <div class="panel-title">
                    <h3>Recent Borrowing Activities</h3>
                    <span class="panel-subtitle">Latest student checkouts and return statuses</span>
                </div>
                <a href="<?= Auth::BASE ?>/borrowings" class="btn-text">View All Loans →</a>
            </div>

            <div class="table-responsive">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Student</th>
                            <th>E-Book</th>
                            <th>Due Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($recentBorrows)): ?>
                            <tr><td colspan="6" class="text-center py-4 text-muted">No borrowings recorded yet.</td></tr>
                        <?php else: ?>
                            <?php foreach ($recentBorrows as $b): 
                                $isReturned = !empty($b['returned_at']);
                                $isOverdue  = !$isReturned && strtotime($b['due_date']) < time();
                            ?>
                                <tr>
                                    <td><span class="id-tag">#<?= (int)$b['borrow_id'] ?></span></td>
                                    <td>
                                        <div class="user-inline-cell">
                                            <strong><?= View::e($b['student_username']) ?></strong>
                                            <small><?= View::e($b['student_email']) ?></small>
                                        </div>
                                    </td>
                                    <td>
                                        <strong><?= View::e($b['ebook_title']) ?></strong>
                                    </td>
                                    <td><?= date('M j, Y', strtotime($b['due_date'])) ?></td>
                                    <td>
                                        <?php if ($isReturned): ?>
                                            <span class="status-badge badge-success">Returned</span>
                                        <?php elseif ($isOverdue): ?>
                                            <span class="status-badge badge-danger">Overdue</span>
                                        <?php else: ?>
                                            <span class="status-badge badge-primary">Active</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!$isReturned): ?>
                                            <form action="<?= Auth::BASE ?>/borrowings/return" method="POST" class="inline-form js-confirm-action" data-confirm="Mark this book as returned?">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="borrow_id" value="<?= (int)$b['borrow_id'] ?>">
                                                <button type="submit" class="btn-sm btn-outline-success">Mark Returned</button>
                                            </form>
                                        <?php else: ?>
                                            <span class="text-muted text-sm">Completed</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Recent Catalogue Items -->
        <div class="panel-card">
            <div class="panel-header">
                <div class="panel-title">
                    <h3>Recent E-Books</h3>
                    <span class="panel-subtitle">Newly added to collection</span>
                </div>
                <a href="<?= Auth::BASE ?>/ebooks" class="btn-text">Catalogue →</a>
            </div>

            <div class="quick-book-list">
                <?php if (empty($recentBooks)): ?>
                    <p class="text-muted py-3">No books added yet.</p>
                <?php else: ?>
                    <?php foreach ($recentBooks as $book): ?>
                        <div class="quick-book-item">
                            <div class="book-mini-spine" style="background: <?= View::e($book['cover_color']) ?>;">
                                <span>📖</span>
                            </div>
                            <div class="quick-book-meta">
                                <strong><a href="<?= Auth::BASE ?>/ebooks/show?id=<?= (int)$book['ebook_id'] ?>"><?= View::e($book['title']) ?></a></strong>
                                <small><?= View::e($book['author']) ?> • <?= View::e($book['category']) ?></small>
                            </div>
                            <a href="<?= Auth::BASE ?>/ebooks/edit?id=<?= (int)$book['ebook_id'] ?>" class="btn-sm btn-icon" title="Edit book">
                                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                            </a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
