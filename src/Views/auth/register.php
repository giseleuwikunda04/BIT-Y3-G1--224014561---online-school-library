<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Support\Auth;
use App\Support\View;
?>

<div class="auth-page-container">
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-logo-circle">🎓</div>
            <h2>Create Student Account</h2>
            <p>Join the Online School Library system to borrow e-books and keep track of your reading progress.</p>
        </div>

        <form action="<?= Auth::BASE ?>/register" method="POST" class="auth-form" novalidate id="registerForm">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label for="username" class="form-label">Username <span class="req">*</span></label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <input type="text" id="username" name="username" class="form-control <?= isset($errors['username']) ? 'is-invalid' : '' ?>" 
                           placeholder="e.g. gisele_student" 
                           value="<?= View::e($old['username'] ?? '') ?>" required>
                </div>
                <?php if (isset($errors['username'])): ?>
                    <span class="field-error"><?= View::e($errors['username']) ?></span>
                <?php else: ?>
                    <small class="field-hint">Between 3-50 letters, numbers, dots, dashes, underscores.</small>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="email" class="form-label">School Email Address <span class="req">*</span></label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"></path><polyline points="22,6 12,13 2,6"></polyline></svg>
                    <input type="email" id="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>" 
                           placeholder="student@school.edu" 
                           value="<?= View::e($old['email'] ?? '') ?>" required>
                </div>
                <?php if (isset($errors['email'])): ?>
                    <span class="field-error"><?= View::e($errors['email']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-row">
                <div class="form-group col-half">
                    <label for="password" class="form-label">Password <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                               placeholder="Min 6 characters" required>
                    </div>
                    <?php if (isset($errors['password'])): ?>
                        <span class="field-error"><?= View::e($errors['password']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group col-half">
                    <label for="password_confirm" class="form-label">Confirm Password <span class="req">*</span></label>
                    <div class="input-wrapper">
                        <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                        <input type="password" id="password_confirm" name="password_confirm" class="form-control <?= isset($errors['password_confirm']) ? 'is-invalid' : '' ?>" 
                               placeholder="Re-enter password" required>
                    </div>
                    <?php if (isset($errors['password_confirm'])): ?>
                        <span class="field-error"><?= View::e($errors['password_confirm']) ?></span>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-group">
                <label for="role" class="form-label">Account Role</label>
                <select name="role" id="role" class="form-control">
                    <option value="student" <?= ($old['role'] ?? 'student') === 'student' ? 'selected' : '' ?>>Student (Read & Borrow E-Books)</option>
                    <option value="librarian" <?= ($old['role'] ?? '') === 'librarian' ? 'selected' : '' ?>>Librarian (Manage Catalogue & Loans)</option>
                </select>
                <small class="field-hint">You can choose Student or Librarian for system evaluation.</small>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">
                    <span>Create Account & Start Reading</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>
            </div>
        </form>

        <div class="auth-card-footer">
            <p>Already have an account? <a href="<?= Auth::BASE ?>/login">Sign in here</a></p>
        </div>
    </div>
</div>
