<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Support\Auth;
use App\Support\View;

$isLibrarian = Auth::isLibrarian();
?>

<div class="content-container">
    <div class="page-header">
        <div>
            <h1>School E-Book Catalogue</h1>
            <p>Browse textbooks, reference manuals, literature, and academic resources available for reading and borrowing.</p>
        </div>
        <?php if ($isLibrarian): ?>
            <a href="<?= Auth::BASE ?>/ebooks/create" class="btn btn-primary">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"></line><line x1="5" y1="12" x2="19" y2="12"></line></svg>
                <span>Add E-Book</span>
            </a>
        <?php endif; ?>
    </div>

    <!-- Filters & Search Toolbar -->
    <div class="toolbar-card">
        <form action="<?= Auth::BASE ?>/ebooks" method="GET" class="filter-form" id="catalogueFilterForm">
            <div class="search-input-wrap">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"></circle><line x1="21" y1="21" x2="16.65" y2="16.65"></line></svg>
                <input type="text" name="q" class="form-control" placeholder="Search by title, author, or keyword..." value="<?= View::e($search) ?>" id="catalogueSearchInput">
            </div>

            <div class="category-select-wrap">
                <select name="category" class="form-control" onchange="this.form.submit()">
                    <option value="">All Categories</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= View::e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>>
                            <?= View::e($cat) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Filter</button>
            <?php if ($search !== '' || $category !== ''): ?>
                <a href="<?= Auth::BASE ?>/ebooks" class="btn btn-outline-danger">Clear</a>
            <?php endif; ?>
        </form>

        <!-- Category Pills Bar -->
        <div class="category-pills-bar">
            <a href="<?= Auth::BASE ?>/ebooks" class="cat-pill <?= $category === '' ? 'active' : '' ?>">All (<?= count($ebooks) ?>)</a>
            <?php foreach ($categories as $cat): ?>
                <a href="<?= Auth::BASE ?>/ebooks?category=<?= urlencode($cat) ?>" class="cat-pill <?= $category === $cat ? 'active' : '' ?>">
                    <?= View::e($cat) ?>
                </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- E-Books Grid -->
    <?php if (empty($ebooks)): ?>
        <div class="empty-state-box">
            <div class="empty-icon">🔍</div>
            <h3>No matching e-books found</h3>
            <p>Try clearing your search query or choosing a different subject category.</p>
            <a href="<?= Auth::BASE ?>/ebooks" class="btn btn-outline-primary mt-3">Reset Filter</a>
        </div>
    <?php else: ?>
        <div class="ebooks-grid">
            <?php foreach ($ebooks as $book): 
                $activeCount = (int)($book['active_borrow_count'] ?? 0);
            ?>
                <div class="ebook-card">
                    <div class="ebook-card-cover" style="background: <?= View::e($book['cover_color']) ?>;">
                        <span class="cover-symbol">📖</span>
                        <span class="category-tag"><?= View::e($book['category']) ?></span>
                        <span class="pages-tag"><?= (int)$book['total_pages'] ?> Pages</span>
                    </div>

                    <div class="ebook-card-content">
                        <h3 class="ebook-card-title">
                            <a href="<?= Auth::BASE ?>/ebooks/show?id=<?= (int)$book['ebook_id'] ?>">
                                <?= View::e($book['title']) ?>
                            </a>
                        </h3>
                        <p class="ebook-card-author">by <?= View::e($book['author']) ?></p>
                        
                        <?php if (!empty($book['description'])): ?>
                            <p class="ebook-card-desc"><?= View::e(mb_strimwidth($book['description'], 0, 100, '...')) ?></p>
                        <?php endif; ?>

                        <div class="ebook-card-footer">
                            <div class="borrow-status-badge">
                                <?php if ($activeCount > 0): ?>
                                    <span class="status-indicator in-use" title="Currently being read by students"></span>
                                    <small><?= $activeCount ?> active loan<?= $activeCount > 1 ? 's' : '' ?></small>
                                <?php else: ?>
                                    <span class="status-indicator available" title="Available"></span>
                                    <small>Available</small>
                                <?php endif; ?>
                            </div>

                            <div class="card-btn-group">
                                <a href="<?= Auth::BASE ?>/ebooks/read?id=<?= (int)$book['ebook_id'] ?>" class="btn-sm btn-primary" title="Open Interactive Reader">
                                    Read
                                </a>
                                <a href="<?= Auth::BASE ?>/ebooks/show?id=<?= (int)$book['ebook_id'] ?>" class="btn-sm btn-outline-primary" title="Details & Borrow">
                                    Details
                                </a>

                                <?php if ($isLibrarian): ?>
                                    <a href="<?= Auth::BASE ?>/ebooks/edit?id=<?= (int)$book['ebook_id'] ?>" class="btn-sm btn-icon" title="Edit E-Book">
                                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"></path><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"></path></svg>
                                    </a>
                                    <form action="<?= Auth::BASE ?>/ebooks/delete" method="POST" class="inline-form js-confirm-action" data-confirm="Are you sure you want to delete '<?= View::e($book['title']) ?>'?">
                                        <?= Csrf::field() ?>
                                        <input type="hidden" name="ebook_id" value="<?= (int)$book['ebook_id'] ?>">
                                        <button type="submit" class="btn-sm btn-icon btn-icon-danger" title="Delete E-Book">
                                            <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"></polyline><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"></path></svg>
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>
