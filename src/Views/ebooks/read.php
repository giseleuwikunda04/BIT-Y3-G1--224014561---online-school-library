<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Support\Auth;
use App\Support\View;

$totalPages = (int)$ebook['total_pages'];
$percent    = $totalPages > 0 ? min(100, round(($currentPages / $totalPages) * 100)) : 0;
?>

<div class="reader-page-wrapper">
    <!-- Reader Top Navigation Bar -->
    <div class="reader-topbar">
        <div class="reader-topbar-left">
            <a href="<?= Auth::BASE ?>/ebooks/show?id=<?= (int)$ebook['ebook_id'] ?>" class="btn-sm btn-outline-primary">
                ← Exit Reader
            </a>
            <div class="reader-book-info">
                <h3><?= View::e($ebook['title']) ?></h3>
                <small>by <?= View::e($ebook['author']) ?> • <?= View::e($ebook['category']) ?></small>
            </div>
        </div>

        <div class="reader-topbar-right">
            <!-- Reader Font Size Controls -->
            <div class="reader-font-controls">
                <button type="button" class="btn-sm btn-icon js-font-size" data-action="decrease" title="Smaller text">A-</button>
                <button type="button" class="btn-sm btn-icon js-font-size" data-action="increase" title="Larger text">A+</button>
            </div>

            <!-- Reading Progress Badge -->
            <div class="reader-stat-badge">
                <span id="readerProgressLabel">Page <strong id="readerPageVal"><?= $currentPages ?></strong> of <?= $totalPages ?> (<?= $percent ?>%)</span>
            </div>
        </div>
    </div>

    <!-- Reader Progress Bar Across Top -->
    <div class="reader-progress-track">
        <div class="reader-progress-indicator" id="readerProgressBar" style="width: <?= $percent ?>%;"></div>
    </div>

    <!-- Main Reader Layout -->
    <div class="reader-layout-grid">
        <!-- Center Book Reading Paper Canvas -->
        <div class="reader-paper-canvas" id="readerContent">
            <div class="book-reading-header">
                <span class="reading-badge"><?= View::e($ebook['category']) ?></span>
                <h1><?= View::e($ebook['title']) ?></h1>
                <p class="reading-author">Authored by <strong><?= View::e($ebook['author']) ?></strong></p>
                <hr class="paper-divider">
            </div>

            <div class="book-body-text">
                <?php if (!empty($bookContent)): ?>
                    <pre class="formatted-book-text"><?= View::e($bookContent) ?></pre>
                <?php else: ?>
                    <div class="sample-chapter-text">
                        <h3>Chapter 1: Foundational Principles & Core Concepts</h3>
                        <p>
                            Welcome to <em><?= View::e($ebook['title']) ?></em>. This academic volume covers key theoretical insights, practical demonstrations, and real-world case studies in <?= View::e($ebook['category']) ?>.
                        </p>
                        <p>
                            Rigorous study requires sustained reading, active note-taking, and critical synthesis. As you progress through each chapter, record your current page in the Reading Tracker panel to ensure your library milestones are documented for your instructor and school librarian.
                        </p>

                        <h3>Chapter 2: Structural Methodologies & Architecture</h3>
                        <p>
                            Effective learning happens systematically. By decomposing intricate challenges into verifiable components—much like normalizing database relations to Third Normal Form (3NF) or writing modular software functions—students cultivate deep understanding and lasting intellectual competence.
                        </p>
                        <p>
                            Continue reading through the upcoming exercises, problem sets, and analysis sections. Use the panel on the right to record your notes and chapter milestones.
                        </p>

                        <h3>Chapter 3: Analysis, Review & Comprehensive Summary</h3>
                        <p>
                            <?= nl2br(View::e($ebook['description'] ?: 'Complete this reading unit to solidify your understanding. Regular review and daily progress logging ensure optimal academic performance.')) ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right Side Progress & Reading Log Drawer -->
        <div class="reader-sidebar">
            <?php if ($borrow): ?>
                <div class="sidebar-box">
                    <div class="sidebar-box-header">
                        <h4>Log Reading Progress</h4>
                        <span class="badge-pill">Live AJAX</span>
                    </div>

                    <form id="ajaxReadingLogForm" action="<?= Auth::BASE ?>/api/reading-logs/log" method="POST" class="reader-log-form">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="borrow_id" value="<?= (int)$borrow['borrow_id'] ?>">
                        <input type="hidden" name="redirect_to" value="<?= Auth::BASE ?>/ebooks/read?id=<?= (int)$ebook['ebook_id'] ?>">

                        <div class="form-group">
                            <label for="pages_read" class="form-label">Current Page Read (Max: <?= $totalPages ?>)</label>
                            <input type="number" id="pages_read" name="pages_read" class="form-control" 
                                   min="0" max="<?= $totalPages ?>" value="<?= $currentPages ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="notes" class="form-label">Session Notes / Reflection</label>
                            <textarea id="notes" name="notes" class="form-control" rows="3" placeholder="e.g. Finished chapter 2 on algorithms..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" id="btnLogProgress">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                            <span>Save Progress (Instant AJAX)</span>
                        </button>
                        <div id="ajaxLogFeedback" class="ajax-feedback-msg" style="display: none;"></div>
                    </form>
                </div>

                <!-- Previous Logs for this Book -->
                <div class="sidebar-box mt-3">
                    <div class="sidebar-box-header">
                        <h4>Session History</h4>
                        <small><?= count($readingLogs) ?> logs</small>
                    </div>

                    <div class="reader-history-list" id="readerHistoryList">
                        <?php if (empty($readingLogs)): ?>
                            <p class="text-muted text-sm py-2">No previous sessions logged yet.</p>
                        <?php else: ?>
                            <?php foreach ($readingLogs as $rl): ?>
                                <div class="history-item">
                                    <div class="history-item-head">
                                        <span class="history-page">Page <?= (int)$rl['pages_read'] ?></span>
                                        <small><?= date('M j, H:i', strtotime($rl['last_read_at'])) ?></small>
                                    </div>
                                    <?php if (!empty($rl['notes'])): ?>
                                        <p class="history-note"><?= View::e($rl['notes']) ?></p>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php else: ?>
                <div class="sidebar-box">
                    <div class="sidebar-box-header">
                        <h4>Preview / Sample Mode</h4>
                    </div>
                    <p class="text-sm text-muted">
                        You are viewing this e-book in sample reader mode. To record reading logs and track your page progress, please borrow this e-book.
                    </p>
                    <form action="<?= Auth::BASE ?>/borrowings/borrow" method="POST" class="mt-3">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="ebook_id" value="<?= (int)$ebook['ebook_id'] ?>">
                        <input type="hidden" name="due_days" value="14">
                        <button type="submit" class="btn btn-primary btn-block">Borrow This E-Book (14 Days)</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
