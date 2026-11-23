<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Support\Auth;
use App\Support\View;
?>

<div class="content-container">
    <div class="page-header">
        <div>
            <h1>User Accounts & Access Management</h1>
            <p>Manage school library accounts, student registrations, and librarian staff privileges.</p>
        </div>
    </div>

    <div class="panel-card">
        <div class="panel-header">
            <div class="panel-title">
                <h3>Registered Users (<?= count($users) ?>)</h3>
                <span class="panel-subtitle">3NF entity: users</span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User ID</th>
                        <th>Username</th>
                        <th>Email Address</th>
                        <th>Current Role</th>
                        <th>Registered Date</th>
                        <th>Change Role</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $u): 
                        $isCurrent = (int)$u['user_id'] === Auth::id();
                    ?>
                        <tr>
                            <td><span class="id-tag">#<?= (int)$u['user_id'] ?></span></td>
                            <td>
                                <strong><?= View::e($u['username']) ?></strong>
                                <?php if ($isCurrent): ?>
                                    <span class="badge-pill bg-light text-primary">You</span>
                                <?php endif; ?>
                            </td>
                            <td><?= View::e($u['email']) ?></td>
                            <td>
                                <span class="role-pill role-<?= View::e($u['role']) ?>">
                                    <?= ucfirst(View::e($u['role'])) ?>
                                </span>
                            </td>
                            <td><?= date('M j, Y', strtotime($u['created_at'])) ?></td>
                            <td>
                                <form action="<?= Auth::BASE ?>/users/role" method="POST" class="inline-role-form">
                                    <?= Csrf::field() ?>
                                    <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                                    <select name="role" class="form-control form-control-sm" onchange="this.form.submit()" <?= $isCurrent && $u['role'] === 'librarian' ? 'disabled title="Cannot demote yourself"' : '' ?>>
                                        <option value="student" <?= $u['role'] === 'student' ? 'selected' : '' ?>>Student</option>
                                        <option value="librarian" <?= $u['role'] === 'librarian' ? 'selected' : '' ?>>Librarian</option>
                                    </select>
                                </form>
                            </td>
                            <td>
                                <?php if (!$isCurrent): ?>
                                    <form action="<?= Auth::BASE ?>/users/delete" method="POST" class="inline-form js-confirm-action" data-confirm="Permanently delete user '<?= View::e($u['username']) ?>'? All their borrowings and reading logs will be cascaded.">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="user_id" value="<?= (int)$u['user_id'] ?>">
                                        <button type="submit" class="btn-sm btn-icon btn-icon-danger" title="Delete User">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <span class="text-muted text-sm">—</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
