<?php
declare(strict_types=1);

use App\Support\Auth;
?>
    </main>

    <!-- Professional Footer -->
    <footer class="app-footer">
        <div class="footer-container">
            <div class="footer-brand">
                <div class="footer-logo">
                    <span class="brand-icon">📚</span>
                    <strong>Online School Library</strong>
                </div>
                <p class="footer-desc">
                    A comprehensive 3NF digital reading platform where students borrow and read educational e-books, track their reading milestones, and librarians curate the academic catalogue.
                </p>
                <div class="student-badge">
                    <span>Student: <strong>UWIMANINKUNDA GISELE</strong></span>
                    <span>Reg No: <strong>224014561</strong></span>
                    <span>Entry: <strong>#104 (Group 2)</strong></span>
                </div>
            </div>

            <div class="footer-col">
                <h4>System Architecture</h4>
                <ul>
                    <li><span class="bullet">✓</span> Pure Vanilla PHP (MVC + PDO)</li>
                    <li><span class="bullet">✓</span> Relational MySQL (3NF Normalized)</li>
                    <li><span class="bullet">✓</span> Secure Password Hashing & CSRF</li>
                    <li><span class="bullet">✓</span> Async Fetch & Real-Time JSON API</li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Core Entities</h4>
                <ul>
                    <li><code>users</code> (Librarians & Students)</li>
                    <li><code>ebooks</code> (Digital Catalogue)</li>
                    <li><code>borrowings</code> (Loans & Return Dates)</li>
                    <li><code>reading_logs</code> (Progress & Notes)</li>
                </ul>
            </div>

            <div class="footer-col">
                <h4>Academic Info</h4>
                <p class="academic-note">
                    Developed for Web Development Coursework (Day 1 – Day 10).
                    Strictly adhering to clean design principles, light colors, zero external JS/CSS frameworks, and PDO prepared statements.
                </p>
            </div>
        </div>

        <div class="footer-bottom">
            <p>&copy; <?= date('Y') ?> Online School Library E-Book System. Built by UWIMANINKUNDA GISELE (224014561).</p>
        </div>
    </footer>

    <!-- App JavaScript -->
    <script src="<?= Auth::BASE ?>/assets/app.js"></script>
</body>
</html>
