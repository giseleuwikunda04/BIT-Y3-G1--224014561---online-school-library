<?php
declare(strict_types=1);

use App\Services\Csrf;
use App\Support\Auth;
use App\Support\View;

$isEdit = !empty($ebook);
$titleVal = $old['title'] ?? ($ebook['title'] ?? '');
$authorVal = $old['author'] ?? ($ebook['author'] ?? '');
$categoryVal = $old['category'] ?? ($ebook['category'] ?? '');
$fileUrlVal = $old['file_url'] ?? ($ebook['file_url'] ?? '/online-school-library/public/storage/books/clean_code.txt');
$totalPagesVal = $old['total_pages'] ?? ($ebook['total_pages'] ?? 150);
$descVal = $old['description'] ?? ($ebook['description'] ?? '');
$colorVal = $old['cover_color'] ?? ($ebook['cover_color'] ?? '#163A5F');
?>

<div class="content-container">
    <div class="breadcrumb-nav">
        <a href="<?= Auth::BASE ?>/ebooks">← Back to Catalogue</a>
    </div>

    <div class="form-card-container">
        <div class="form-card">
            <div class="form-card-header">
                <h2><?= $isEdit ? 'Edit E-Book: ' . View::e($ebook['title']) : 'Add New E-Book to Catalogue' ?></h2>
                <p>Register or update curriculum textbooks, literature, and study resources for school students.</p>
            </div>

            <form action="<?= Auth::BASE ?>/ebooks/save" method="POST" class="standard-form" novalidate>
                <?= Csrf::field() ?>
                <?php if ($isEdit): ?>
                    <input type="hidden" name="ebook_id" value="<?= (int)$ebook['ebook_id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label for="title" class="form-label">E-Book Title <span class="req">*</span></label>
                    <input type="text" id="title" name="title" class="form-control <?= isset($errors['title']) ? 'is-invalid' : '' ?>" 
                           placeholder="e.g. Fundamentals of Database Systems" value="<?= View::e($titleVal) ?>" required>
                    <?php if (isset($errors['title'])): ?>
                        <span class="field-error"><?= View::e($errors['title']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-row">
                    <div class="form-group col-half">
                        <label for="author" class="form-label">Author(s) <span class="req">*</span></label>
                        <input type="text" id="author" name="author" class="form-control <?= isset($errors['author']) ? 'is-invalid' : '' ?>" 
                               placeholder="e.g. Ramez Elmasri & Shamkant Navathe" value="<?= View::e($authorVal) ?>" required>
                        <?php if (isset($errors['author'])): ?>
                            <span class="field-error"><?= View::e($errors['author']) ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="form-group col-half">
                        <label for="category" class="form-label">Subject Category <span class="req">*</span></label>
                        <input type="text" id="category" name="category" list="categoryList" class="form-control <?= isset($errors['category']) ? 'is-invalid' : '' ?>" 
                               placeholder="e.g. Computer Science, Mathematics" value="<?= View::e($categoryVal) ?>" required>
                        <datalist id="categoryList">
                            <option value="Computer Science">
                            <option value="Mathematics">
                            <option value="Natural Sciences">
                            <option value="African Literature">
                            <option value="History & Social Studies">
                            <option value="Philosophy & Ethics">
                        </datalist>
                        <?php if (isset($errors['category'])): ?>
                            <span class="field-error"><?= View::e($errors['category']) ?></span>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group col-half">
                        <label for="total_pages" class="form-label">Total Pages <span class="req">*</span></label>
                        <input type="number" id="total_pages" name="total_pages" class="form-control <?= isset($errors['total_pages']) ? 'is-invalid' : '' ?>" 
                               min="1" max="5000" value="<?= View::e($totalPagesVal) ?>" required>
                        <?php if (isset($errors['total_pages'])): ?>
                            <span class="field-error"><?= View::e($errors['total_pages']) ?></span>
                        <?php else: ?>
                            <small class="field-hint">Used for student reading progress calculation (1-5000).</small>
                        <?php endif; ?>
                    </div>

                    <div class="form-group col-half">
                        <label for="cover_color" class="form-label">Cover Theme Color</label>
                        <div class="color-picker-row">
                            <input type="color" id="cover_color" name="cover_color" class="color-input" value="<?= View::e($colorVal) ?>">
                            <div class="color-preset-pills">
                                <span class="color-swatch" style="background: #163A5F;" onclick="document.getElementById('cover_color').value='#163A5F'"></span>
                                <span class="color-swatch" style="background: #2E7D5B;" onclick="document.getElementById('cover_color').value='#2E7D5B'"></span>
                                <span class="color-swatch" style="background: #102A43;" onclick="document.getElementById('cover_color').value='#102A43'"></span>
                                <span class="color-swatch" style="background: #D4A373;" onclick="document.getElementById('cover_color').value='#D4A373'"></span>
                                <span class="color-swatch" style="background: #2563EB;" onclick="document.getElementById('cover_color').value='#2563EB'"></span>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="file_url" class="form-label">Digital File URL / Storage Path <span class="req">*</span></label>
                    <input type="text" id="file_url" name="file_url" class="form-control <?= isset($errors['file_url']) ? 'is-invalid' : '' ?>" 
                           placeholder="/online-school-library/public/storage/books/sample.txt" value="<?= View::e($fileUrlVal) ?>" required>
                    <?php if (isset($errors['file_url'])): ?>
                        <span class="field-error"><?= View::e($errors['file_url']) ?></span>
                    <?php else: ?>
                        <small class="field-hint">File path or download URL for student digital access.</small>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="description" class="form-label">Synopsis / Book Description</label>
                    <textarea id="description" name="description" class="form-control" rows="4" 
                              placeholder="Brief summary of syllabus contents, chapters, and topics covered..."><?= View::e($descVal) ?></textarea>
                </div>

                <div class="form-actions mt-4">
                    <button type="submit" class="btn btn-primary">
                        <span><?= $isEdit ? 'Save Changes' : 'Publish to Catalogue' ?></span>
                    </button>
                    <a href="<?= Auth::BASE ?>/ebooks" class="btn btn-outline-danger">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
