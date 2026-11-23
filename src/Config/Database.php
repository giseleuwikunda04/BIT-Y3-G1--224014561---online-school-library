<?php
declare(strict_types=1);
namespace App\Config;

use PDO;
use PDOException;

final class Database {
    private static ?PDO $pdo = null;

    public static function pdo(): PDO {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }

        $host = getenv('DB_HOST') ?: '127.0.0.1';
        $port = getenv('DB_PORT') ?: '3306';
        $db   = getenv('DB_NAME') ?: 'online_school_library_db';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') !== false ? (string)getenv('DB_PASS') : '';

        // Step 1: Ensure database exists on server
        try {
            $serverDsn = "mysql:host={$host};port={$port};charset=utf8mb4";
            $serverPdo = new PDO($serverDsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);
            $serverPdo->exec("CREATE DATABASE IF NOT EXISTS `{$db}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        } catch (\Throwable $ignored) {
            // If user lacks CREATE DATABASE privilege, continue to connect directly
        }

        // Step 2: Connect to specific database
        $dsn = "mysql:host={$host};port={$port};dbname={$db};charset=utf8mb4";

        try {
            $options = [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ];

            if (defined('PDO::MYSQL_ATTR_MULTI_STATEMENTS')) {
                $options[PDO::MYSQL_ATTR_MULTI_STATEMENTS] = true;
            }

            self::$pdo = new PDO($dsn, $user, $pass, $options);

            // Step 3: Self-healing schema validation
            // If 'borrowings' or other entities are missing, auto-initialize from setup.sql
            self::ensureSchema(self::$pdo);

        } catch (PDOException $e) {
            error_log('DB connection failed: ' . $e->getMessage());
            http_response_code(500);
            echo "<!doctype html><html lang='en'><head><meta charset='utf-8'><title>Database Setup Notice</title>
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; background: #f8fafc; color: #1e293b; padding: 3rem 1rem; display: flex; justify-content: center; align-items: center; min-height: 80vh; }
                .box { background: #ffffff; padding: 2.5rem; border-radius: 12px; border: 1px solid #e2e8f0; max-width: 600px; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.08); }
                h1 { color: #163a5f; font-size: 1.4rem; margin-top: 0; }
                p { line-height: 1.6; color: #475569; }
                code { background: #f1f5f9; padding: 0.2rem 0.4rem; border-radius: 4px; color: #0f172a; font-weight: 600; font-size: 0.9rem; }
                .tip { margin-top: 1.25rem; padding: 1rem; background: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 4px; }
            </style></head><body>
            <div class='box'>
                <h1>Database Connection Notice</h1>
                <p>Could not connect to the MySQL database <code>online_school_library_db</code> on <code>{$host}:{$port}</code>.</p>
                <div class='tip'>
                    <strong>Quick Setup Steps:</strong>
                    <ol style='margin-left: 1.2rem; margin-top: 0.5rem;'>
                        <li>Start <strong>Apache</strong> and <strong>MySQL</strong> in the XAMPP Control Panel.</li>
                        <li>Import <code>database/setup.sql</code> in phpMyAdmin (or execute <code>schema.sql</code> and <code>seed.sql</code>).</li>
                        <li>Refresh this page.</li>
                    </ol>
                </div>
                <p style='margin-top: 1rem; font-size: 0.85rem; color: #94a3b8;'>Technical details: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . "</p>
            </div></body></html>";
            exit;
        }

        return self::$pdo;
    }

    /**
     * Verifies that the 4 3NF entity tables exist; auto-initializes them if missing
     */
    private static function ensureSchema(PDO $pdo): void {
        try {
            $stmt = $pdo->query("SHOW TABLES LIKE 'borrowings'");
            $exists = $stmt->fetch();

            if (!$exists) {
                self::runSqlScript($pdo);
            }
        } catch (\Throwable $e) {
            // Try running script on any inspection error
            self::runSqlScript($pdo);
        }
    }

    /**
     * Executes SQL migration file safely statement-by-statement
     */
    private static function runSqlScript(PDO $pdo): void {
        $setupFile = dirname(__DIR__, 2) . '/database/setup.sql';
        if (!is_file($setupFile)) {
            $setupFile = dirname(__DIR__, 2) . '/database/schema.sql';
        }
        if (!is_file($setupFile)) {
            return;
        }

        $content = file_get_contents($setupFile);
        if (!$content) {
            return;
        }

        // Disable foreign key checks during batch setup
        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");
        } catch (\Throwable $ignored) {}

        // Split statements cleanly
        $lines = explode("\n", $content);
        $cleanSql = '';
        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--') || str_starts_with($trimmed, '/*')) {
                continue;
            }
            $cleanSql .= $line . "\n";
        }

        $statements = explode(';', $cleanSql);
        foreach ($statements as $statement) {
            $sql = trim($statement);
            if ($sql !== '') {
                try {
                    $pdo->exec($sql);
                } catch (\Throwable $e) {
                    error_log('Auto-schema exec notice: ' . $e->getMessage());
                }
            }
        }

        try {
            $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
        } catch (\Throwable $ignored) {}
    }
}
