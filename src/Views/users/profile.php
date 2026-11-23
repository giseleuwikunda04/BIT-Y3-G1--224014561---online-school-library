<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Support\Auth;
use App\Support\View;
?>

<div class="content-container">
    <div class="page-header">
        <div>
            <h1>User Profile & Settings</h1>
            <p>Manage your account credentials, login email, and secure password.</p>
        </div>
    </div>

    <div class="profile-layout-grid">
        <!-- Account Overview Card -->
        <div class="panel-card">
            <div class="profile-card-top">
                <div class="profile-avatar-large">
                    <?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?>
                </div>
                <h3><?= View::e($user['username']) ?></h3>
                <span class="role-pill role-<?= View::e($user['role']) ?>"><?= ucfirst(View::e($user['role'])) ?></span>
                <p class="text-muted text-sm mt-1">Member since <?= date('F j, Y', strtotime($user['created_at'])) ?></p>
            </div>

            <hr class="paper-divider">

            <div class="profile-quick-stats">
                <div class="p-stat">
                    <strong><?= count($borrows) ?></strong>
                    <span>Total Loans</span>
                </div>
                <div class="p-stat">
                    <strong><?= count($logs) ?></strong>
                    <span>Reading Logs</span>
                </div>
            </div>
        </div>

        <!-- Edit Profile Form -->
        <div class="panel-card col-span-2">
            <div class="panel-header">
                <div class="panel-title">
                    <h3>Account Credentials</h3>
                    <span class="panel-subtitle">Update your username, email, or change your password</span>
                </div>
            </div>

            <form action="<?= Auth::BASE ?>/profile" method="POST" class="standard-form">
                <?= Csrf::field() ?>

                <div class="form-row">
                    <div class="form-group col-half">
                        <label for="username" class="form-label">Username <span class="req">*</span></label>
                        <input type="text" id="username" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                               value="<?= View::e($user['username']) ?>" required>
                        <?php if (isset($errors['username'])): ?>
                            <span class="field-error"><?= View::e($errors['username']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group col-half">
                        <label for="email" class="form-label">Email Address <span class="req">*</span></label>
                        <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                               value="<?= View::e($user['email']) ?>" required>
                        <?php if (isset($errors['email'])): ?>
                            <span class="field-error"><?= View::e($errors['email']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="password-change-box mt-3">
                    <h4 class="mb-1">Change Password (Optional)</h4>
                    <p class="text-muted text-sm mb-3">Leave blank if you do not wish to change your current password.</p>

                    <div class="form-row">
                        <div class="form-group col-half">
                            <label for="new_password" class="form-label">New Password</label>
                            <input type="password" id="new_password" name="new_password" class="form-control <?= isset($errors['new_password']) ? 'is-invalid' : '' ?>" placeholder="Min 6 characters">
                            <?php if (isset($errors['new_password'])): ?>
                                <span class="field-error"><?= View::e($errors['new_password']) ?></span>
                            <?php endif; ?>
                        </div>

                        <div class="form-group col-half">
                            <label for="confirm_password" class="form-label">Confirm New Password</label>
                            <input type="password" id="confirm_password" name="confirm_password" class="form-control <?= isset($errors['confirm_password']) ? 'is-invalid' : '' ?>" placeholder="Re-enter new password">
                            <?php if (isset($errors['confirm_password'])): ?>
                                <span class="field-error"><?= View::e($errors['confirm_password']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="form-actions mt-4">
                    <button type="submit" class="btn btn-primary">Save Profile Changes</button>
                    <a href="<?= Auth::BASE ?>/dashboard" class="btn btn-outline-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
