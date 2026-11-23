<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Support\Auth;
use App\Support\View;

$userId = Auth::id();
?>

<div class="content-container">
    <div class="page-header">
        <div>
            <h1>Reading Progress Logs</h1>
            <p><?= $isLibrarian ? 'Review all student reading sessions, page completion rates, and learning notes.' : 'Record your reading progress, track chapters completed, and build your daily study habit.' ?></p>
        </div>
        <?php if (!$isLibrarian && !empty($myActiveBorrows)): ?>
            <button type="button" class="btn btn-primary js-open-modal" data-target="logSessionModal">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Log Reading Session</span>
            </button>
        <?php endif; ?>
    </div>

    <!-- Quick Log Form for Students with Active Books -->
    <?php if (!$isLibrarian && !empty($myActiveBorrows)): ?>
        <div class="card p-4 mb-4">
            <h3 class="mb-2">Quick Log Daily Reading</h3>
            <p class="text-muted text-sm mb-3">Record the latest page you finished reading today.</p>

            <form action="<?= Auth::BASE ?>/reading-logs/save" method="POST" class="form-row align-items-end">
                <?= Csrf::field() ?>
                
                <div class="form-group col-4">
                    <label for="borrow_id" class="form-label">Select Borrowed E-Book <span class="req">*</span></label>
                    <select name="borrow_id" id="borrow_id" class="form-control" required>
                        <?php foreach ($myActiveBorrows as $b): ?>
                            <option value="<?= (int)$b['borrow_id'] ?>">
                                <?= View::e($b['ebook_title']) ?> (Total: <?= (int)$b['ebook_total_pages'] ?> p.)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group col-3">
                    <label for="pages_read" class="form-label">Pages Read So Far <span class="req">*</span></label>
                    <input type="number" name="pages_read" id="pages_read" class="form-control" placeholder="e.g. 45" min="0" required>
                </div>

                <div class="form-group col-3">
                    <label for="notes" class="form-label">Notes (Optional)</label>
                    <input type="text" name="notes" id="notes" class="form-control" placeholder="e.g. Chapter 2 finished">
                </div>

                <div class="form-group col-2">
                    <button type="submit" class="btn btn-primary btn-block">
                        <span>Save Log</span>
                    </button>
                </div>
            </form>
        </div>
    <?php endif; ?>

    <!-- Logs Table Panel -->
    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <h3>Reading Activity History</h3>
                <span class="panel-subtitle"><?= count($logs) ?> milestone records logged</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Log ID</th>
                        <?php if ($isLibrarian): ?>
                            <th>Student</th>
                        <?php endif; ?>
                        <th>E-Book</th>
                        <th>Pages Read</th>
                        <th>Milestone %</th>
                        <th>Last Read At</th>
                        <th>Notes / Chapter</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($logs)): ?>
                        <tr>
                            <td colspan="<?= $isLibrarian ? 8 : 7 ?>" class="text-center py-5 text-muted">
                                No reading progress logged yet. Borrow a book and start reading!
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($logs as $log): 
                            $readP  = (int)$log['pages_read'];
                            $totalP = (int)($log['ebook_total_pages'] ?? 100);
                            $pct    = $totalP > 0 ? min(100, round(($readP / $totalP) * 100)) : 0;
                        ?>
                            <tr>
                                <td><span class="id-tag">#<?= (int)$log['log_id'] ?></span></td>
                                <?php if ($isLibrarian): ?>
                                    <td>
                                        <strong><?= View::e($log['student_username'] ?? '') ?></strong>
                                    </td>
                                <?php endif; ?>
                                <td>
                                    <strong><?= View::e($log['ebook_title'] ?? '') ?></strong>
                                    <?php if (!empty($log['ebook_author'])): ?>
                                        <small class="d-block text-muted">by <?= View::e($log['ebook_author']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <strong><?= $readP ?></strong> / <?= $totalP ?> pages
                                </td>
                                <td>
                                    <div class="progress-cell">
                                        <div class="progress-bar-bg progress-bar-sm">
                                            <div class="progress-bar-fill" style="width: <?= $pct ?>%;"></div>
                                        </div>
                                        <small><?= $pct ?>%</small>
                                    </div>
                                </td>
                                <td><?= date('M j, Y H:i', strtotime($log['last_read_at'])) ?></td>
                                <td>
                                    <?php if (!empty($log['notes'])): ?>
                                        <span class="note-pill">"<?= View::e($log['notes']) ?>"</span>
                                    <?php else: ?>
                                        <span class="text-muted text-sm">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <form action="<?= Auth::BASE ?>/reading-logs/delete" method="POST" class="inline-form js-confirm-action" data-confirm="Delete this reading log entry?">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="log_id" value="<?= (int)$log['log_id'] ?>">
                                        <button type="submit" class="btn-sm btn-icon btn-icon-danger" title="Delete Log">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
