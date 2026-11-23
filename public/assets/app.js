/**
 * ====================================================================
 * Online School Library E-Book System — Master JavaScript
 * Student: UWIMANINKUNDA GISELE (Reg No: 224014561)
 * Features:
 *   1. Live Search Modal via Fetch/AJAX (Async JSON API)
 *   2. Real-Time In-Reader Reading Progress Logger (AJAX POST)
 *   3. Dashboard Live Stats Auto-Refresh
 *   4. Client-Side Form Validation (Registration, Login, Books, Logs)
 *   5. Interactive Reader Font Scaling & Dark/Paper Reading Modes
 *   6. Quick Demo Autofill (Librarian / Student)
 *   7. Mobile Navigation Drawer & Custom Modals
 *   8. Destructive Action Confirmations
 * ====================================================================
 */

document.addEventListener('DOMContentLoaded', () => {
    initLiveSearch();
    initAsyncReadingLogger();
    initLiveStats();
    initFormValidation();
    initReaderControls();
    initDemoAutofill();
    initModals();
    initMobileNav();
    initConfirmActions();
    initPasswordToggle();
});

const BASE_URL = window.__APP_BASE__ || '/online-school-library/public';
const CSRF_TOKEN = window.__CSRF_TOKEN__ || (document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '');

/**
 * --------------------------------------------------------------------
 * 1. Live Search Modal with Fetch/AJAX (Async JSON API Endpoint)
 * --------------------------------------------------------------------
 */
function initLiveSearch() {
    const modal = document.getElementById('searchModal');
    const input = document.getElementById('searchInput');
    const resultsContainer = document.getElementById('searchResults');
    const triggers = document.querySelectorAll('.js-search-trigger');
    const closeBtn = document.getElementById('searchClose');

    if (!modal || !input || !resultsContainer) return;

    function openModal() {
        modal.classList.add('open');
        input.value = '';
        resultsContainer.innerHTML = '<div class="search-placeholder">Type an e-book title, author, or category to search in real time...</div>';
        setTimeout(() => input.focus(), 60);
    }

    function closeModal() {
        modal.classList.remove('open');
    }

    triggers.forEach(t => t.addEventListener('click', openModal));
    if (closeBtn) closeBtn.addEventListener('click', closeModal);

    modal.addEventListener('click', (e) => {
        if (e.target === modal) closeModal();
    });

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            modal.classList.contains('open') ? closeModal() : openModal();
        } else if (e.key === 'Escape' && modal.classList.contains('open')) {
            closeModal();
        }
    });

    let debounceTimer = null;
    input.addEventListener('input', () => {
        clearTimeout(debounceTimer);
        const query = input.value.trim();

        if (query.length === 0) {
            resultsContainer.innerHTML = '<div class="search-placeholder">Type an e-book title, author, or category to search in real time...</div>';
            return;
        }

        resultsContainer.innerHTML = '<div class="search-placeholder"><span class="spinner">⏳</span> Searching library catalogue...</div>';

        debounceTimer = setTimeout(async () => {
            try {
                const response = await fetch(`${BASE_URL}/api/ebooks/search?q=${encodeURIComponent(query)}`, {
                    headers: { 'Accept': 'application/json' }
                });

                if (!response.ok) {
                    throw new Error(`Search failed with status ${response.status}`);
                }

                const data = await response.json();

                if (!data.success || !Array.isArray(data.data) || data.data.length === 0) {
                    resultsContainer.innerHTML = `<div class="search-placeholder">No matching e-books found for <strong>"${escapeHtml(query)}"</strong>.</div>`;
                    return;
                }

                let html = '';
                data.data.forEach(book => {
                    const desc = book.description ? escapeHtml(book.description.substring(0, 75)) + '...' : 'Available in school library.';
                    html += `
                        <div class="search-result-item">
                            <div class="search-result-meta">
                                <strong><a href="${escapeHtml(book.url)}">${escapeHtml(book.title)}</a></strong>
                                <small>by ${escapeHtml(book.author)} • <span class="badge-pill bg-light">${escapeHtml(book.category)}</span> • ${book.total_pages} pages</small>
                            </div>
                            <div class="search-result-tags">
                                <a href="${escapeHtml(book.read_url)}" class="btn-sm btn-primary">Read</a>
                                <a href="${escapeHtml(book.url)}" class="btn-sm btn-outline-primary">Details</a>
                            </div>
                        </div>
                    `;
                });

                resultsContainer.innerHTML = html;

            } catch (err) {
                console.error('Live search error:', err);
                resultsContainer.innerHTML = '<div class="search-placeholder text-danger">An error occurred while connecting to the library search API.</div>';
            }
        }, 250);
    });
}

/**
 * --------------------------------------------------------------------
 * 2. Asynchronous In-Reader Progress Logger (Fetch/JSON API)
 * --------------------------------------------------------------------
 */
function initAsyncReadingLogger() {
    const form = document.getElementById('ajaxReadingLogForm');
    if (!form) return;

    const feedback = document.getElementById('ajaxLogFeedback');
    const submitBtn = document.getElementById('btnLogProgress');
    const pagesInput = document.getElementById('pages_read');
    const notesInput = document.getElementById('notes');
    const progressBar = document.getElementById('readerProgressBar');
    const pageVal = document.getElementById('readerPageVal');
    const progressLabel = document.getElementById('readerProgressLabel');
    const historyList = document.getElementById('readerHistoryList');

    form.addEventListener('submit', async (e) => {
        e.preventDefault();

        const borrowId = form.querySelector('input[name="borrow_id"]')?.value;
        const pagesRead = parseInt(pagesInput.value, 10);
        const notes = notesInput.value.trim();

        if (isNaN(pagesRead) || pagesRead < 0) {
            showFeedback('Please enter a valid page number.', 'error');
            return;
        }

        const maxPages = parseInt(pagesInput.getAttribute('max') || '5000', 10);
        if (pagesRead > maxPages) {
            showFeedback(`Pages read cannot exceed total pages (${maxPages}).`, 'error');
            return;
        }

        // Disable button during AJAX dispatch
        submitBtn.disabled = true;
        submitBtn.innerHTML = 'Saving...';

        try {
            const payload = {
                borrow_id: parseInt(borrowId, 10),
                pages_read: pagesRead,
                notes: notes,
                csrf_token: CSRF_TOKEN
            };

            const response = await fetch(`${BASE_URL}/api/reading-logs/log`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': CSRF_TOKEN
                },
                body: JSON.stringify(payload)
            });

            const data = await response.json();

            if (!response.ok || !data.success) {
                const errMsg = data.error || (data.errors ? Object.values(data.errors).join(' ') : 'Failed to save progress.');
                showFeedback(errMsg, 'error');
            } else {
                showFeedback(data.message || 'Progress saved successfully!', 'success');

                // Update UI elements dynamically
                if (progressBar) progressBar.style.width = `${data.percent}%`;
                if (pageVal) pageVal.textContent = data.pages_read;
                if (progressLabel) {
                    progressLabel.innerHTML = `Page <strong id="readerPageVal">${data.pages_read}</strong> of ${maxPages} (${data.percent}%)`;
                }

                // Prepend to history list
                if (historyList) {
                    const emptyMsg = historyList.querySelector('.text-muted');
                    if (emptyMsg) emptyMsg.remove();

                    const newEntry = document.createElement('div');
                    newEntry.className = 'history-item';
                    newEntry.style.animation = 'slideDown 0.3s ease-out';
                    newEntry.innerHTML = `
                        <div class="history-item-head">
                            <span class="history-page">Page ${data.pages_read}</span>
                            <small>${data.last_read}</small>
                        </div>
                        ${notes ? `<p class="history-note">"${escapeHtml(notes)}"</p>` : ''}
                    `;
                    historyList.prepend(newEntry);
                }

                notesInput.value = '';
            }
        } catch (err) {
            console.error('AJAX reading log error:', err);
            showFeedback('Network error: Unable to record reading progress.', 'error');
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = `
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"></polyline></svg>
                <span>Save Progress (Instant AJAX)</span>
            `;
        }
    });

    function showFeedback(msg, type) {
        if (!feedback) return;
        feedback.textContent = msg;
        feedback.className = `ajax-feedback-msg ajax-${type}`;
        feedback.style.display = 'block';
        setTimeout(() => {
            feedback.style.display = 'none';
        }, 4000);
    }
}

/**
 * --------------------------------------------------------------------
 * 3. Live Dashboard Stats Updater (Periodic Asynchronous Polling)
 * --------------------------------------------------------------------
 */
function initLiveStats() {
    const totalBooksEl = document.getElementById('statTotalBooks');
    const activeBorrowsEl = document.getElementById('statActiveBorrows');
    const overdueBorrowsEl = document.getElementById('statOverdueBorrows');
    const totalPagesEl = document.getElementById('statTotalPages');

    if (!totalBooksEl && !activeBorrowsEl) return;

    // Fetch refreshed stats every 45 seconds to keep counters in sync
    setInterval(async () => {
        try {
            const res = await fetch(`${BASE_URL}/api/stats`);
            if (!res.ok) return;
            const resData = await res.json();
            if (resData.success && resData.data) {
                const d = resData.data;
                if (totalBooksEl) totalBooksEl.textContent = d.total_books;
                if (activeBorrowsEl) activeBorrowsEl.textContent = d.active_borrows;
                if (overdueBorrowsEl) overdueBorrowsEl.textContent = d.overdue_borrows;
                if (totalPagesEl) totalPagesEl.textContent = Number(d.total_pages_read).toLocaleString();
            }
        } catch (e) {
            // Passive background sync
        }
    }, 45000);
}

/**
 * --------------------------------------------------------------------
 * 4. Client-Side Form Validation (Registration, Login, Books)
 * --------------------------------------------------------------------
 */
function initFormValidation() {
    // Register Form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            let valid = true;
            clearFieldErrors(registerForm);

            const username = registerForm.querySelector('#username');
            const email = registerForm.querySelector('#email');
            const pass = registerForm.querySelector('#password');
            const confirm = registerForm.querySelector('#password_confirm');

            if (username && username.value.trim().length < 3) {
                showFieldError(username, 'Username must be at least 3 characters.');
                valid = false;
            }

            if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email.value.trim())) {
                showFieldError(email, 'Please enter a valid email address.');
                valid = false;
            }

            if (pass && pass.value.length < 6) {
                showFieldError(pass, 'Password must be at least 6 characters.');
                valid = false;
            }

            if (confirm && pass && confirm.value !== pass.value) {
                showFieldError(confirm, 'Passwords do not match.');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    }

    // Login Form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            let valid = true;
            clearFieldErrors(loginForm);

            const identity = loginForm.querySelector('#identity');
            const pass = loginForm.querySelector('#password');

            if (identity && identity.value.trim() === '') {
                showFieldError(identity, 'Please enter your username or email.');
                valid = false;
            }

            if (pass && pass.value === '') {
                showFieldError(pass, 'Please enter your password.');
                valid = false;
            }

            if (!valid) {
                e.preventDefault();
            }
        });
    }

    function showFieldError(input, msg) {
        input.classList.add('is-invalid');
        const err = document.createElement('span');
        err.className = 'field-error';
        err.textContent = msg;
        input.closest('.form-group')?.appendChild(err);
    }

    function clearFieldErrors(form) {
        form.querySelectorAll('.field-error').forEach(e => e.remove());
        form.querySelectorAll('.is-invalid').forEach(i => i.classList.remove('is-invalid'));
    }
}

/**
 * --------------------------------------------------------------------
 * 5. Interactive Reader Font Scaling Controls
 * --------------------------------------------------------------------
 */
function initReaderControls() {
    const reader = document.getElementById('readerContent');
    const buttons = document.querySelectorAll('.js-font-size');

    if (!reader || buttons.length === 0) return;

    let currentSize = 1.05; // rem

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            const action = btn.getAttribute('data-action');
            if (action === 'increase' && currentSize < 1.45) {
                currentSize += 0.1;
            } else if (action === 'decrease' && currentSize > 0.85) {
                currentSize -= 0.1;
            }
            reader.style.fontSize = `${currentSize}rem`;
        });
    });
}

/**
 * --------------------------------------------------------------------
 * 6. Quick Demo Autofill Pills (Login Page)
 * --------------------------------------------------------------------
 */
function initDemoAutofill() {
    const pills = document.querySelectorAll('.js-fill-demo');
    const identityInput = document.getElementById('identity');
    const passwordInput = document.getElementById('password');

    if (!identityInput || !passwordInput) return;

    pills.forEach(pill => {
        pill.addEventListener('click', () => {
            const user = pill.getAttribute('data-user');
            const pass = pill.getAttribute('data-pass');
            if (user) identityInput.value = user;
            if (pass) passwordInput.value = pass;

            // Visual pulse effect
            identityInput.style.backgroundColor = '#EFF6FF';
            passwordInput.style.backgroundColor = '#EFF6FF';
            setTimeout(() => {
                identityInput.style.backgroundColor = '';
                passwordInput.style.backgroundColor = '';
            }, 600);
        });
    });
}

/**
 * --------------------------------------------------------------------
 * 7. Custom Modals (e.g. Checkout Modal)
 * --------------------------------------------------------------------
 */
function initModals() {
    const openBtns = document.querySelectorAll('.js-open-modal');
    const closeBtns = document.querySelectorAll('.js-close-modal');

    openBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const modal = document.getElementById(targetId);
            if (modal) modal.classList.add('open');
        });
    });

    closeBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const modal = btn.closest('.custom-modal');
            if (modal) modal.classList.remove('open');
        });
    });

    document.querySelectorAll('.custom-modal').forEach(m => {
        m.addEventListener('click', (e) => {
            if (e.target === m) m.classList.remove('open');
        });
    });
}

/**
 * --------------------------------------------------------------------
 * 8. Mobile Navigation Toggle
 * --------------------------------------------------------------------
 */
function initMobileNav() {
    const toggle = document.querySelector('.js-mobile-toggle');
    const menu = document.getElementById('navMenu');

    if (!toggle || !menu) return;

    toggle.addEventListener('click', () => {
        menu.classList.toggle('open');
    });
}

/**
 * --------------------------------------------------------------------
 * 9. Destructive Action Confirmations
 * --------------------------------------------------------------------
 */
function initConfirmActions() {
    document.querySelectorAll('.js-confirm-action').forEach(form => {
        form.addEventListener('submit', (e) => {
            const message = form.getAttribute('data-confirm') || 'Are you sure you want to perform this action?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/**
 * --------------------------------------------------------------------
 * 10. Password Reveal Toggle
 * --------------------------------------------------------------------
 */
function initPasswordToggle() {
    document.querySelectorAll('.js-toggle-password').forEach(btn => {
        btn.addEventListener('click', () => {
            const targetId = btn.getAttribute('data-target');
            const input = document.getElementById(targetId);
            if (!input) return;
            const isPassword = input.type === 'password';
            input.type = isPassword ? 'text' : 'password';
            btn.textContent = isPassword ? '🙈' : '👁️';
        });
    });
}

/**
 * Utility: HTML Escape
 */
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
