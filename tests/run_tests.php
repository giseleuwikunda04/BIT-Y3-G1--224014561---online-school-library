<?php
declare(strict_types=1);

// Configure CLI session storage path for clean test execution
if (session_status() === PHP_SESSION_NONE) {
    @ini_set('session.save_path', sys_get_temp_dir());
    @session_start();
}

/**
 * ====================================================================
 * Online School Library E-Book System — Automated Test Suite
 * Student: UWIMANINKUNDA GISELE (Reg No: 224014561)
 * Purpose: Verifies core business logic, validators, security, and routes
 * ====================================================================
 */

require dirname(__DIR__) . '/src/Support/autoload.php';

use App\Services\Csrf;
use App\Services\EbookValidator;
use App\Services\BorrowingValidator;
use App\Services\ReadingLogValidator;
use App\Support\View;

$totalTests = 0;
$passedTests = 0;
$failedTests = 0;

function it(string $description, callable $test): void {
    global $totalTests, $passedTests, $failedTests;
    $totalTests++;
    try {
        $result = $test();
        if ($result === false) {
            throw new Exception("Assertion returned false");
        }
        $passedTests++;
        echo "  [PASS] {$description}\n";
    } catch (Throwable $e) {
        $failedTests++;
        echo "  [FAIL] {$description} -> " . $e->getMessage() . "\n";
    }
}

echo "========================================================\n";
echo "Online School Library E-Book System — Test Runner\n";
echo "Student: UWIMANINKUNDA GISELE (224014561)\n";
echo "========================================================\n\n";

// --- Group 1: CSRF & Security Tests ---
echo "Group 1: Security & Protection Tests\n";

it("TC-01: CSRF Token generates a 64-character hex string", function() {
    $token = Csrf::token();
    return is_string($token) && strlen($token) === 64 && ctype_xdigit($token);
});

it("TC-02: CSRF Validation passes with matching token and fails with tampered token", function() {
    $validToken = Csrf::token();
    $valid = Csrf::validate($validToken);
    $invalid = Csrf::validate("attacker_token_xyz");
    $empty = Csrf::validate("");
    return $valid === true && $invalid === false && $empty === false;
});

it("TC-03: View::e() neutralizes XSS injection payloads", function() {
    $xss = '<script>alert("XSS")</script>';
    $escaped = View::e($xss);
    return $escaped === '&lt;script&gt;alert(&quot;XSS&quot;)&lt;/script&gt;';
});

it("TC-04: Password hashing uses BCRYPT and verifies properly", function() {
    $password = "MySecurePass123!";
    $hash = password_hash($password, PASSWORD_BCRYPT);
    return password_verify($password, $hash) === true && password_verify("wrongpass", $hash) === false;
});

// --- Group 2: E-Book Entity Validation Tests ---
echo "\nGroup 2: E-Book Entity Validation Tests\n";

it("TC-05: EbookValidator accepts valid book payload (Happy Path)", function() {
    $errors = EbookValidator::validate([
        'title'       => 'Introduction to Web Architecture',
        'author'      => 'Dr. Jane Smith',
        'category'    => 'Computer Science',
        'file_url'    => '/online-school-library/public/storage/books/clean_code.txt',
        'total_pages' => 240,
    ]);
    return empty($errors);
});

it("TC-06: EbookValidator rejects empty title, invalid pages, missing author (Failure Path)", function() {
    $errors = EbookValidator::validate([
        'title'       => '',
        'author'      => '',
        'category'    => '',
        'file_url'    => '',
        'total_pages' => -10,
    ]);
    return isset($errors['title']) && isset($errors['author']) && isset($errors['category']) && isset($errors['total_pages']);
});

// --- Group 3: Borrowing Entity Validation Tests ---
echo "\nGroup 3: Borrowing Entity Validation Tests\n";

it("TC-07: BorrowingValidator accepts valid student, book, and due date (Happy Path)", function() {
    $errors = BorrowingValidator::validate([
        'ebook_id' => 1,
        'user_id'  => 3,
        'due_date' => '2026-12-15 23:59:59',
    ]);
    return empty($errors);
});

it("TC-08: BorrowingValidator rejects zero IDs and invalid due date formats (Failure Path)", function() {
    $errors = BorrowingValidator::validate([
        'ebook_id' => 0,
        'user_id'  => -5,
        'due_date' => 'not-a-valid-date-string',
    ]);
    return isset($errors['ebook_id']) && isset($errors['user_id']) && isset($errors['due_date']);
});

// --- Group 4: Reading Log Entity Validation Tests ---
echo "\nGroup 4: Reading Log Entity Validation Tests\n";

it("TC-09: ReadingLogValidator accepts pages within book bounds (Happy Path)", function() {
    $errors = ReadingLogValidator::validate([
        'borrow_id'  => 1,
        'pages_read' => 120,
        'notes'      => 'Completed Chapter 4',
    ], 280);
    return empty($errors);
});

it("TC-10: ReadingLogValidator rejects negative pages and pages exceeding total book size (Failure Path)", function() {
    $negativeErrors = ReadingLogValidator::validate([
        'borrow_id'  => 1,
        'pages_read' => -15,
        'notes'      => 'Error test',
    ], 280);

    $exceededErrors = ReadingLogValidator::validate([
        'borrow_id'  => 1,
        'pages_read' => 9999,
        'notes'      => 'Error test',
    ], 280);

    return isset($negativeErrors['pages_read']) && isset($exceededErrors['pages_read']);
});

// --- Group 5: Database & Artifact Integrity ---
echo "\nGroup 5: Database Schema & Storage Integrity\n";

it("TC-11: Database schema, seed, and setup SQL files exist and contain 3NF tables", function() {
    $schemaFile = dirname(__DIR__) . '/database/schema.sql';
    $seedFile   = dirname(__DIR__) . '/database/seed.sql';
    $setupFile  = dirname(__DIR__) . '/database/setup.sql';

    if (!is_file($schemaFile) || !is_file($seedFile) || !is_file($setupFile)) {
        return false;
    }

    $schema = file_get_contents($schemaFile);
    return str_contains($schema, 'CREATE TABLE `users`')
        && str_contains($schema, 'CREATE TABLE `ebooks`')
        && str_contains($schema, 'CREATE TABLE `borrowings`')
        && str_contains($schema, 'CREATE TABLE `reading_logs`')
        && str_contains($schema, 'FOREIGN KEY (`ebook_id`)')
        && str_contains($schema, 'FOREIGN KEY (`user_id`)')
        && str_contains($schema, 'FOREIGN KEY (`borrow_id`)');
});

it("TC-12: Digital text reader sample files are loaded and non-empty", function() {
    $sample1 = dirname(__DIR__) . '/public/storage/books/clean_code.txt';
    $sample2 = dirname(__DIR__) . '/public/storage/books/db_systems.txt';
    return is_file($sample1) && filesize($sample1) > 100 && is_file($sample2) && filesize($sample2) > 100;
});

echo "\n--------------------------------------------------------\n";
echo "SUMMARY: {$passedTests} / {$totalTests} tests passed ({$failedTests} failed)\n";
echo "--------------------------------------------------------\n";

if ($failedTests > 0) {
    exit(1);
}
echo "ALL TESTS PASSED SUCCESSFULLY! Ready for evaluation.\n";
exit(0);
