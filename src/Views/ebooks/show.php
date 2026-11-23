<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Support\Auth;
use App\Support\View;

$isLibrarian = Auth::isLibrarian();
$userId      = Auth::id();
?>

<div class="content-container">
    <div class="breadcrumb-nav">
        <a href="<?= Auth::BASE ?>/ebooks">← Back to Catalogue</a>
    </div>

    <div class="ebook-detail-layout">
        <!-- Left Spine/Cover Card -->
        <div class="detail-cover-card" style="border-top: 6px solid <?= View::e($ebook['cover_color']) ?>;">
            <div class="detail-book-illustration" style="background: <?= View::e($ebook['cover_color']) ?>;">
                <span class="detail-big-icon">📚</span>
                <span class="detail-cover-cat"><?= View::e($ebook['category']) ?></span>
            </div>

            <div class="detail-meta-list">
                <div class="meta-row">
                    <span class="meta-label">Category:</span>
                    <strong><?= View::e($ebook['category']) ?></strong>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Total Pages:</span>
                    <strong><?= (int)$ebook['total_pages'] ?> pages</strong>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Storage Path:</span>
                    <code class="meta-code"><?= View::e($ebook['file_url']) ?></code>
                </div>
                <div class="meta-row">
                    <span class="meta-label">Added On:</span>
                    <span><?= date('M j, Y', strtotime($ebook['created_at'])) ?></span>
                </div>
            </div>

            <div class="detail-primary-actions">
                <a href="<?= Auth::BASE ?>/ebooks/read?id=<?= (int)$ebook['ebook_id'] ?>" class="btn btn-primary btn-block">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                    <span>Read In Interactive Reader</span>
                </a>
            </div>
        </div>

        <!-- Right Content & Loan Management -->
        <div class="detail-content-card">
            <div class="detail-header">
                <span class="category-pill"><?= View::e($ebook['category']) ?></span>
                <h1 class="detail-title"><?= View::e($ebook['title']) ?></h1>
                <p class="detail-author">Author: <strong><?= View::e($ebook['author']) ?></strong></p>
            </div>

            <div class="detail-synopsis">
                <h3>Synopsis & Learning Overview</h3>
                <p><?= nl2br(View::e($ebook['description'] ?: 'This textbook is part of the official curriculum collection. Students are encouraged to read the chapters and log their reading progress daily.')) ?></p>
            </div>

            <!-- Loan / Borrow Status Box -->
            <div class="borrow-action-card">
                <?php if ($isBorrowedByMe && $myActiveBorrow): ?>
                    <div class="active-loan-notice">
                        <div class="loan-icon">📘</div>
                        <div class="loan-info">
                            <h4>You currently have this e-book borrowed</h4>
                            <p>Due date: <strong><?= date('l, F j, Y', strtotime($myActiveBorrow['due_date'])) ?></strong> (Logged <?= (int)$myActiveBorrow['current_pages_read'] ?> of <?= (int)$ebook['total_pages'] ?> pages)</p>
                        </div>
                        <form action="<?= Auth::BASE ?>/borrowings/return" method="POST" class="inline-form js-confirm-action" data-confirm="Return this e-book now?">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="borrow_id" value="<?= (int)$myActiveBorrow['borrow_id'] ?>">
                            <button type="submit" class="btn btn-outline-success">Return Book</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="borrow-form-block">
                        <div class="borrow-form-header">
                            <h4>Borrow this E-Book</h4>
                            <p>Borrowing grants full digital access to all chapters and tracks your reading progress.</p>
                        </div>

                        <form action="<?= Auth::BASE ?>/borrowings/borrow" method="POST" class="borrow-now-form">
                            <?= Csrf::field() ?>
                            <input type="hidden" name="ebook_id" value="<?= (int)$ebook['ebook_id'] ?>">
                            
                            <div class="form-row align-items-end">
                                <div class="form-group col-half">
                                    <label for="due_days" class="form-label">Loan Duration</label>
                                    <select name="due_days" id="due_days" class="form-control">
                                        <option value="7">7 Days (Standard Reading)</option>
                                        <option value="14" selected>14 Days (Recommended - 2 Weeks)</option>
                                        <option value="30">30 Days (Semester Project)</option>
                                    </select>
                                </div>

                                <div class="form-group col-half">
                                    <button type="submit" class="btn btn-primary btn-block">
                                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path></svg>
                                        <span>Confirm Borrowing</span>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Librarian Management Tools -->
            <?php if ($isLibrarian): ?>
                <div class="librarian-admin-bar">
                    <span class="admin-label">Librarian Controls:</span>
                    <a href="<?= Auth::BASE ?>/ebooks/edit?id=<?= (int)$ebook['ebook_id'] ?>" class="btn-sm btn-outline-primary">Edit E-Book</a>
                    <form action="<?= Auth::BASE ?>/ebooks/delete" method="POST" class="inline-form js-confirm-action" data-confirm="Delete '<?= View::e($ebook['title']) ?>' permanently?">
                        <?= Csrf::field() ?>
                        <input type="hidden" name="ebook_id" value="<?= (int)$ebook['ebook_id'] ?>">
                        <button type="submit" class="btn-sm btn-outline-danger">Delete E-Book</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>
