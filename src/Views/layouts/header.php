<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Support\Auth;
use App\Support\Flash;
use App\Support\View;

$user        = Auth::user();
$isLoggedIn  = Auth::check();
$isLibrarian = Auth::isLibrarian();
$currentUri  = $_SERVER['REQUEST_URI'] ?? '';
$flash       = Flash::get();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= View::e($pageTitle ?? 'Online School Library E-Book System') ?></title>
    <meta name="csrf-token" content="<?= View::e(Csrf::token()) ?>">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= Auth::BASE ?>/assets/style.css">
    <script>
        window.__APP_BASE__ = '<?= Auth::BASE ?>';
        window.__CSRF_TOKEN__ = '<?= View::e(Csrf::token()) ?>';
    </script>
</head>
<body>
    <!-- Top Modern Navbar -->
    <header class="app-header">
        <div class="nav-container">
            <a href="<?= Auth::BASE ?>/dashboard" class="brand-logo">
                <span class="brand-icon">📚</span>
                <span class="brand-text">
                    <strong>School<span class="accent-text">Library</span></strong>
                    <small class="brand-tag">E-Book System</small>
                </span>
            </a>

            <!-- Search Quick Trigger Button -->
            <button type="button" class="search-trigger-btn js-search-trigger" title="Quick Search (Ctrl + K)">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <span>Search catalogue...</span>
                <kbd class="kbd-shortcut">Ctrl K</kbd>
            </button>

            <!-- Navigation Links -->
            <nav class="nav-menu" id="navMenu">
                <?php if ($isLoggedIn): ?>
                    <a href="<?= Auth::BASE ?>/dashboard" class="nav-link <?= str_contains($currentUri, '/dashboard') || $currentUri === Auth::BASE . '/' ? 'active' : '' ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"></rect><rect x="14" y="3" width="7" height="7"></rect><rect x="14" y="14" width="7" height="7"></rect><rect x="3" y="14" width="7" height="7"></rect></svg>
                        Dashboard
                    </a>
                    <a href="<?= Auth::BASE ?>/ebooks" class="nav-link <?= str_contains($currentUri, '/ebooks') ? 'active' : '' ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path></svg>
                        Catalogue
                    </a>
                    <a href="<?= Auth::BASE ?>/borrowings" class="nav-link <?= str_contains($currentUri, '/borrowings') ? 'active' : '' ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"></path><rect x="8" y="2" width="8" height="4" rx="1" ry="1"></rect></svg>
                        <?= $isLibrarian ? 'Borrowings' : 'My Loans' ?>
                    </a>
                    <a href="<?= Auth::BASE ?>/reading-logs" class="nav-link <?= str_contains($currentUri, '/reading-logs') ? 'active' : '' ?>">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path></svg>
                        Reading Logs
                    </a>
                    <?php if ($isLibrarian): ?>
                        <a href="<?= Auth::BASE ?>/users" class="nav-link <?= str_contains($currentUri, '/users') ? 'active' : '' ?>">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
                            Users
                        </a>
                    <?php endif; ?>
                <?php endif; ?>
            </nav>

            <!-- User Status / Auth Actions -->
            <div class="nav-user-actions">
                <?php if ($isLoggedIn): ?>
                    <a href="<?= Auth::BASE ?>/profile" class="user-badge" title="View profile">
                        <span class="user-avatar-initial"><?= strtoupper(substr($user['username'] ?? 'U', 0, 1)) ?></span>
                        <span class="user-info-text">
                            <strong><?= View::e($user['username']) ?></strong>
                            <small class="role-pill role-<?= View::e($user['role']) ?>"><?= ucfirst(View::e($user['role'])) ?></small>
                        </span>
                    </a>
                    <a href="<?= Auth::BASE ?>/logout" class="btn-sm btn-outline-danger" title="Sign out">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path><polyline points="16 17 21 12 16 7"></polyline><line x1="21" y1="12" x2="9" y2="12"></line></svg>
                        <span>Sign Out</span>
                    </a>
                <?php else: ?>
                    <a href="<?= Auth::BASE ?>/login" class="btn-sm btn-outline-primary">Sign In</a>
                    <a href="<?= Auth::BASE ?>/register" class="btn-sm btn-primary">Join Library</a>
                <?php endif; ?>

                <!-- Mobile Hamburger Toggle -->
                <button class="mobile-toggle js-mobile-toggle" aria-label="Toggle menu">
                    <span></span><span></span><span></span>
                </button>
            </div>
        </div>
    </header>

    <!-- Global Live Search Modal (Async feature) -->
    <div class="search-modal" id="searchModal" role="dialog" aria-modal="true" aria-labelledby="searchModalTitle">
        <div class="search-modal-box">
            <div class="search-modal-header">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" id="searchInput" class="search-modal-input" placeholder="Search by e-book title, author, or category..." autocomplete="off">
                <button type="button" class="search-modal-close" id="searchClose" aria-label="Close search">✕</button>
            </div>
            <div class="search-modal-results" id="searchResults">
                <div class="search-placeholder">Type an e-book title or author to search in real time...</div>
            </div>
            <div class="search-modal-footer">
                <span>Press <kbd>ESC</kbd> to close</span>
                <span>Real-Time Async JSON API</span>
            </div>
        </div>
    </div>

    <!-- Global Flash Notification Messages -->
    <?php if ($flash): ?>
        <div class="flash-wrapper">
            <div class="flash-alert flash-<?= View::e($flash['type'] ?? 'info') ?>">
                <div class="flash-icon">
                    <?php if (($flash['type'] ?? '') === 'success'): ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>
                    <?php elseif (($flash['type'] ?? '') === 'danger'): ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="15" y1="9" x2="9" y2="15"></line><line x1="9" y1="9" x2="15" y2="15"></line></svg>
                    <?php else: ?>
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                    <?php endif; ?>
                </div>
                <div class="flash-content">
                    <?= View::e($flash['message'] ?? '') ?>
                </div>
                <button type="button" class="flash-close" onclick="this.parentElement.remove();">×</button>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content Container -->
    <main class="main-content">
