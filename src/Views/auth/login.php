<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Support\Auth;
use App\Support\View;
?>

<div class="auth-page-container">
    <div class="auth-card">
        <div class="auth-card-header">
            <div class="auth-logo-circle">📖</div>
            <h2>Sign In to Library</h2>
            <p>Access your digital school library desk, borrow e-books, and log reading milestones.</p>
        </div>

        <!-- Quick Demo Credentials for Grading -->
        <div class="demo-accounts-box">
            <div class="demo-accounts-title">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                <span>Quick Test Credentials (Click to Autofill):</span>
            </div>
            <div class="demo-pills">
                <button type="button" class="demo-pill js-fill-demo" data-user="librarian_marie" data-pass="password123">
                    <span class="role-badge role-librarian">Librarian</span>
                    <span>librarian_marie</span>
                </button>
                <button type="button" class="demo-pill js-fill-demo" data-user="eric_student" data-pass="password123">
                    <span class="role-badge role-student">Student</span>
                    <span>eric_student</span>
                </button>
            </div>
        </div>

        <form action="<?= Auth::BASE ?>/login" method="POST" class="auth-form" novalidate id="loginForm">
            <?= Csrf::field() ?>

            <div class="form-group">
                <label for="identity" class="form-label">Username or Email</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path><circle cx="12" cy="7" r="4"></circle></svg>
                    <input type="text" id="identity" name="identity" class="form-control <?= isset($errors['identity']) ? 'is-invalid' : '' ?>" 
                           placeholder="e.g. librarian_marie or student@school.edu" 
                           value="<?= View::e($old['identity'] ?? '') ?>" required autofocus>
                </div>
                <?php if (isset($errors['identity'])): ?>
                    <span class="field-error"><?= View::e($errors['identity']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-wrapper">
                    <svg class="input-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect><path d="M7 11V7a5 5 0 0 1 10 0v4"></path></svg>
                    <input type="password" id="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>" 
                           placeholder="••••••••" required>
                    <button type="button" class="toggle-password-btn js-toggle-password" data-target="password" title="Show password">👁️</button>
                </div>
                <?php if (isset($errors['password'])): ?>
                    <span class="field-error"><?= View::e($errors['password']) ?></span>
                <?php endif; ?>
            </div>

            <div class="form-actions">
                <button type="submit" class="btn btn-primary btn-block">
                    <span>Sign In to School Library</span>
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="5" y1="12" x2="19" y2="12"></line><polyline points="12 5 19 12 12 19"></polyline></svg>
                </button>
            </div>
        </form>

        <div class="auth-card-footer">
            <p>New to the school library? <a href="<?= Auth::BASE ?>/register">Register as Student</a></p>
        </div>
    </div>
</div>
