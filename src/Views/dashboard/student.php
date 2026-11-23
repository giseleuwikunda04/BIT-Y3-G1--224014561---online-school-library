<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Support\Auth;
use App\Support\View;
?>

<div class="dashboard-container">
    <!-- Student Desk Banner -->
    <div class="dashboard-hero student-hero">
        <div class="hero-text">
            <span class="hero-badge">Student Reading Desk</span>
            <h1>Hello, <?= View::e(Auth::username()) ?> 📚</h1>
            <p>Welcome to your personal library space. Continue reading your borrowed e-books, log your daily progress, and explore academic materials.</p>
        </div>
        <div class="hero-actions">
            <a href="<?= Auth::BASE ?>/ebooks" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <span>Browse Catalogue</span>
            </a>
            <a href="<?= Auth::BASE ?>/borrowings" class="btn btn-outline-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path></svg>
                <span>My Active Loans (<?= count($myActiveBorrows) ?>)</span>
            </a>
        </div>
    </div>

    <!-- Student Stats -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon icon-blue">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Active Loans</span>
                <strong class="stat-value"><?= count($myActiveBorrows) ?></strong>
                <span class="stat-meta">Currently in your bookshelf</span>
            </div>
        </div>

        <div class="stat-card <?= !empty($myOverdueBorrows) ? 'card-alert' : '' ?>">
            <div class="stat-icon icon-red">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Overdue Books</span>
                <strong class="stat-value"><?= count($myOverdueBorrows) ?></strong>
                <span class="stat-meta"><?= !empty($myOverdueBorrows) ? 'Please return soon' : 'All loans on schedule' ?></span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-green">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Pages Read</span>
                <strong class="stat-value"><?= number_format($myTotalPagesRead) ?></strong>
                <span class="stat-meta">Across active books</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-icon icon-amber">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
            </div>
            <div class="stat-content">
                <span class="stat-label">Total Completed</span>
                <strong class="stat-value"><?= count(array_filter($myBorrows, fn($b) => !empty($b['returned_at']))) ?></strong>
                <span class="stat-meta">Returned books</span>
            </div>
        </div>
    </div>

    <!-- Active Books Section -->
    <div class="section-title-wrap">
        <h2>Currently Reading</h2>
        <span class="subtitle">E-books you are actively reading right now</span>
    </div>

    <?php if (empty($myActiveBorrows)): ?>
        <div class="empty-state-box">
            <div class="empty-icon">📖</div>
            <h3>Your bookshelf is empty!</h3>
            <p>You do not have any borrowed e-books right now. Explore the library catalogue to discover textbooks, science manuals, and literature.</p>
            <a href="<?= Auth::BASE ?>/ebooks" class="btn btn-primary mt-3">Explore E-Book Catalogue</a>
        </div>
    <?php else: ?>
        <div class="reading-cards-grid">
            <?php foreach ($myActiveBorrows as $b): 
                $pagesRead  = (int)($b['current_pages_read'] ?? 0);
                $totalPages = (int)($b['ebook_total_pages'] ?? 100);
                $percent    = $totalPages > 0 ? min(100, round(($pagesRead / $totalPages) * 100)) : 0;
                $isOverdue  = strtotime($b['due_date']) < time();
            ?>
                <div class="reading-card">
                    <div class="reading-card-cover" style="background: <?= View::e($b['cover_color']) ?>;">
                        <span class="cover-icon">📘</span>
                        <span class="cover-cat"><?= View::e($b['ebook_category']) ?></span>
                    </div>
                    <div class="reading-card-body">
                        <div class="reading-card-head">
                            <h4><?= View::e($b['ebook_title']) ?></h4>
                            <span class="author-name">by <?= View::e($b['ebook_author']) ?></span>
                        </div>

                        <div class="reading-progress-block">
                            <div class="progress-labels">
                                <span>Progress</span>
                                <strong><?= $pagesRead ?> / <?= $totalPages ?> pages (<?= $percent ?>%)</strong>
                            </div>
                            <div class="progress-bar-bg">
                                <div class="progress-bar-fill" style="width: <?= $percent ?>%;"></div>
                            </div>
                        </div>

                        <div class="loan-due-meta">
                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><polyline points="12 6 12 12 16 14"></polyline></svg>
                            <span>Due: <strong><?= date('M j, Y', strtotime($b['due_date'])) ?></strong></span>
                            <?php if ($isOverdue): ?>
                                <span class="status-badge badge-danger">Overdue</span>
                            <?php endif; ?>
                        </div>

                        <div class="reading-card-actions">
                            <a href="<?= Auth::BASE ?>/ebooks/read?id=<?= (int)$b['ebook_id'] ?>" class="btn btn-primary btn-sm flex-1">
                                <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                                <span>Read Online</span>
                            </a>
                            <form action="<?= Auth::BASE ?>/borrowings/return" method="POST" class="inline-form js-confirm-action" data-confirm="Are you sure you want to return this e-book?">
                                <?= Csrf::field() ?>
                                <input type="hidden" name="borrow_id" value="<?= (int)$b['borrow_id'] ?>">
                                <button type="submit" class="btn btn-outline-success btn-sm">Return</button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Recent Logs & Recommendations Grid -->
    <div class="dashboard-grid mt-4">
        <!-- My Recent Reading Logs -->
        <div class="panel-card">
            <div class="panel-header">
                <div class="panel-title">
                    <h3>My Reading Activity</h3>
                    <span class="panel-subtitle">Latest sessions logged</span>
                </div>
                <a href="<?= Auth::BASE ?>/reading-logs" class="btn-text">View All Logs →</a>
            </div>

            <?php if (empty($myReadingLogs)): ?>
                <p class="text-muted py-3">No reading progress recorded yet.</p>
            <?php else: ?>
                <div class="log-timeline">
                    <?php foreach ($myReadingLogs as $log): ?>
                        <div class="timeline-entry">
                            <div class="timeline-dot"></div>
                            <div class="timeline-body">
                                <div class="timeline-head">
                                    <strong><?= View::e($log['ebook_title']) ?></strong>
                                    <small><?= date('M j, Y H:i', strtotime($log['last_read_at'])) ?></small>
                                </div>
                                <div class="timeline-text">
                                    <span class="pages-badge">Page <?= (int)$log['pages_read'] ?> of <?= (int)$log['ebook_total_pages'] ?></span>
                                    <?php if (!empty($log['notes'])): ?>
                                        <p class="log-note-text">"<?= View::e($log['notes']) ?>"</p>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Featured Books to Read Next -->
        <div class="panel-card">
            <div class="panel-header">
                <div class="panel-title">
                    <h3>Recommended E-Books</h3>
                    <span class="panel-subtitle">Popular school collection titles</span>
                </div>
                <a href="<?= Auth::BASE ?>/ebooks" class="btn-text">Catalogue →</a>
            </div>

            <div class="quick-book-list">
                <?php foreach ($featuredBooks as $fb): ?>
                    <div class="quick-book-item">
                        <div class="book-mini-spine" style="background: <?= View::e($fb['cover_color']) ?>;">
                            <span>📚</span>
                        </div>
                        <div class="quick-book-meta">
                            <strong><a href="<?= Auth::BASE ?>/ebooks/show?id=<?= (int)$fb['ebook_id'] ?>"><?= View::e($fb['title']) ?></a></strong>
                            <small><?= View::e($fb['author']) ?> • <?= View::e($fb['category']) ?></small>
                        </div>
                        <a href="<?= Auth::BASE ?>/ebooks/show?id=<?= (int)$fb['ebook_id'] ?>" class="btn-sm btn-outline-primary">
                            Details
                        </a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>
