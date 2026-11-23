<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Support\Auth;
use App\Support\View;
?>

<div class="content-container">
    <div class="page-header">
        <div>
            <h1><?= $isLibrarian ? 'Manage School Borrowings' : 'My E-Book Loans' ?></h1>
            <p><?= $isLibrarian ? 'Monitor all active checkouts, overdue loan records, and completed student returns.' : 'Review your active digital loans, track return due dates, and return books when finished.' ?></p>
        </div>
        <?php if ($isLibrarian): ?>
            <button type="button" class="btn btn-primary js-open-modal" data-target="checkoutModal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>New Checkout</span>
            </button>
        <?php else: ?>
            <a href="<?= Auth::BASE ?>/ebooks" class="btn btn-primary">
                <span>Browse & Borrow Books</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- Status Tabs -->
    <div class="filter-tabs-bar">
        <a href="<?= Auth::BASE ?>/borrowings?status=all" class="tab-link <?= $filter === 'all' ? 'active' : '' ?>">All Records</a>
        <a href="<?= Auth::BASE ?>/borrowings?status=active" class="tab-link <?= $filter === 'active' ? 'active' : '' ?>">Active Loans</a>
        <a href="<?= Auth::BASE ?>/borrowings?status=overdue" class="tab-link <?= $filter === 'overdue' ? 'active' : '' ?>">Overdue Loans</a>
        <a href="<?= Auth::BASE ?>/borrowings?status=returned" class="tab-link <?= $filter === 'returned' ? 'active' : '' ?>">Returned</a>
    </div>

    <!-- Borrowings Table Panel -->
    <div class="panel-card mt-3">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Loan ID</th>
                        <?php if ($isLibrarian): ?>
                            <th>Student</th>
                        <?php endif; ?>
                        <th>E-Book</th>
                        <th>Borrowed At</th>
                        <th>Due Date</th>
                        <th>Reading Progress</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($borrowings)): ?>
                        <tr>
                            <td colspan="<?= $isLibrarian ? 8 : 7 ?>" class="text-center py-5 text-muted">
                                No borrowing records found in this category.
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($borrowings as $b): 
                            $isReturned = !empty($b['returned_at']);
                            $isOverdue  = !$isReturned && strtotime($b['due_date']) < time();
                            $pagesRead  = (int)($b['current_pages_read'] ?? 0);
                            $totalPages = (int)($b['ebook_total_pages'] ?? 100);
                            $percent    = $totalPages > 0 ? min(100, round(($pagesRead / $totalPages) * 100)) : 0;
                        ?>
                            <tr>
                                <td><span class="id-tag">#<?= (int)$b['borrow_id'] ?></span></td>
                                <?php if ($isLibrarian): ?>
                                    <td>
                                        <div class="user-inline-cell">
                                            <strong><?= View::e($b['student_username'] ?? '') ?></strong>
                                            <small><?= View::e($b['student_email'] ?? '') ?></small>
                                        </div>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <strong><a href="<?= Auth::BASE ?>/ebooks/show?id=<?= (int)$b['ebook_id'] ?>"><?= View::e($b['ebook_title']) ?></a></strong>
                                    <small class="d-block text-muted">by <?= View::e($b['ebook_author']) ?></small>
                                </td>
                                <td><?= date('M j, Y H:i', strtotime($b['borrowed_at'])) ?></td>
                                <td>
                                    <span class="<?= $isOverdue ? 'text-danger fw-bold' : '' ?>">
                                        <?= date('M j, Y', strtotime($b['due_date'])) ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="progress-cell">
                                        <div class="progress-bar-bg progress-bar-sm">
                                            <div class="progress-bar-fill" style="width: <?= $percent ?>%;"></div>
                                        </div>
                                        <small><?= $pagesRead ?> / <?= $totalPages ?> p. (<?= $percent ?>%)</small>
                                    </div>
                                </td>
                                <td>
                                    <?php if ($isReturned): ?>
                                        <span class="status-badge badge-success">Returned (<?= date('M j', strtotime($b['returned_at'])) ?>)</span>
                                    <?php elseif ($isOverdue): ?>
                                        <span class="status-badge badge-danger">Overdue</span>
                                    <?php else: ?>
                                        <span class="status-badge badge-primary">Active</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="table-actions-group">
                                        <?php if (!$isReturned): ?>
                                            <a href="<?= Auth::BASE ?>/ebooks/read?id=<?= (int)$b['ebook_id'] ?>" class="btn-sm btn-outline-primary" title="Read book">
                                                Read
                                            </a>
                                            <form action="<?= Auth::BASE ?>/borrowings/return" method="POST" class="inline-form js-confirm-action" data-confirm="Mark this e-book as returned?">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="borrow_id" value="<?= (int)$b['borrow_id'] ?>">
                                                <button type="submit" class="btn-sm btn-outline-success" title="Return Book">Return</button>
                                            </form>
                                        <?php else: ?>
                                            <a href="<?= Auth::BASE ?>/ebooks/show?id=<?= (int)$b['ebook_id'] ?>" class="btn-sm btn-text">View</a>
                                        <?php endif; ?>

                                        <?php if ($isLibrarian): ?>
                                            <form action="<?= Auth::BASE ?>/borrowings/delete" method="POST" class="inline-form js-confirm-action" data-confirm="Permanently delete borrowing record #<?= (int)$b['borrow_id'] ?>?">
                                                <?= Csrf::field() ?>
                                                <input type="hidden" name="borrow_id" value="<?= (int)$b['borrow_id'] ?>">
                                                <button type="submit" class="btn-sm btn-icon btn-icon-danger" title="Delete Record">
                                                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal: Librarian Checkout E-Book to Student -->
<?php if ($isLibrarian): ?>
    <div class="custom-modal" id="checkoutModal">
        <div class="custom-modal-dialog">
            <div class="custom-modal-header">
                <h3>Issue E-Book Checkout</h3>
                <button type="button" class="modal-close-btn js-close-modal">✕</button>
            </div>
            <form action="<?= Auth::BASE ?>/borrowings/borrow" method="POST" class="standard-form">
                <?= Csrf::field() ?>
                <div class="custom-modal-body">
                    <div class="form-group">
                        <label for="modal_user_id" class="form-label">Select Student <span class="req">*</span></label>
                        <select name="user_id" id="modal_user_id" class="form-control" required>
                            <option value="">-- Choose Student --</option>
                            <?php foreach ($students as $stu): ?>
                                <option value="<?= (int)$stu['user_id'] ?>">
                                    <?= View::e($stu['username']) ?> (<?= View::e($stu['email']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal_ebook_id" class="form-label">Select E-Book <span class="req">*</span></label>
                        <select name="ebook_id" id="modal_ebook_id" class="form-control" required>
                            <option value="">-- Choose E-Book --</option>
                            <?php foreach ($ebooks as $bk): ?>
                                <option value="<?= (int)$bk['ebook_id'] ?>">
                                    <?= View::e($bk['title']) ?> (<?= View::e($bk['category']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal_due_days" class="form-label">Loan Duration</label>
                        <select name="due_days" id="modal_due_days" class="form-control">
                            <option value="7">7 Days</option>
                            <option value="14" selected>14 Days (Standard)</option>
                            <option value="30">30 Days (Extended)</option>
                        </select>
                    </div>
                </div>
                <div class="custom-modal-footer">
                    <button type="button" class="btn btn-outline-danger js-close-modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Complete Checkout</button>
                </div>
            </form>
        </div>
    </div>
<?php endif; ?>
