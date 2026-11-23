<?php
declare(strict_types=1);

use App\Support\Auth;
use App\Support\View;
?>

<div class="content-container">
    <div class="empty-state-box py-5">
        <div class="empty-icon">404</div>
        <h2>Page Not Found</h2>
        <p>The library resource or page <code><?= View::e($path ?? '') ?></code> could not be found.</p>
        <div class="mt-4">
            <a href="<?= Auth::BASE ?>/dashboard" class="btn btn-primary">Return to Library Dashboard</a>
            <a href="<?= Auth::BASE ?>/ebooks" class="btn btn-outline-primary ml-2">Browse Catalogue</a>
        </div>
    </div>
</div>
