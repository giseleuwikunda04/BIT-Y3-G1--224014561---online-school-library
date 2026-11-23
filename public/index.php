<?php
declare(strict_types=1);

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require dirname(__DIR__) . '/src/Support/autoload.php';

use App\Controllers\AuthController;
use App\Controllers\DashboardController;
use App\Controllers\EbookController;
use App\Controllers\BorrowingController;
use App\Controllers\ReadingLogController;
use App\Controllers\UserController;
use App\Controllers\ApiController;
use App\Support\Auth;
use App\Support\View;

/* Base URL prefix for the application */
$BASE = '/online-school-library/public';

$uri  = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?: '/';

if ($BASE !== '' && str_starts_with($path, $BASE)) {
    $path = substr($path, strlen($BASE));
}

$path = rtrim($path, '/');
if ($path === '' || $path === '/index.php') {
    $path = '/';
}

$method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');

$routes = [
    // Home & Dashboard
    '/' => [
        'GET'  => [DashboardController::class, 'index'],
        'POST' => [DashboardController::class, 'index'],
    ],
    '/dashboard' => [
        'GET'  => [DashboardController::class, 'index'],
    ],

    // Authentication
    '/login' => [
        'GET'  => [AuthController::class, 'showLogin'],
        'POST' => [AuthController::class, 'login'],
    ],
    '/register' => [
        'GET'  => [AuthController::class, 'showRegister'],
        'POST' => [AuthController::class, 'register'],
    ],
    '/logout' => [
        'GET'  => [AuthController::class, 'logout'],
        'POST' => [AuthController::class, 'logout'],
    ],

    // E-Books CRUD & Reading
    '/ebooks' => [
        'GET'  => [EbookController::class, 'index'],
    ],
    '/ebooks/show' => [
        'GET'  => [EbookController::class, 'show'],
    ],
    '/ebooks/read' => [
        'GET'  => [EbookController::class, 'read'],
    ],
    '/ebooks/create' => [
        'GET'  => [EbookController::class, 'create'],
    ],
    '/ebooks/edit' => [
        'GET'  => [EbookController::class, 'edit'],
    ],
    '/ebooks/save' => [
        'POST' => [EbookController::class, 'save'],
    ],
    '/ebooks/delete' => [
        'POST' => [EbookController::class, 'delete'],
    ],

    // Borrowings CRUD
    '/borrowings' => [
        'GET'  => [BorrowingController::class, 'index'],
    ],
    '/borrowings/borrow' => [
        'POST' => [BorrowingController::class, 'borrow'],
    ],
    '/borrowings/return' => [
        'POST' => [BorrowingController::class, 'returnBook'],
    ],
    '/borrowings/delete' => [
        'POST' => [BorrowingController::class, 'delete'],
    ],

    // Reading Logs CRUD
    '/reading-logs' => [
        'GET'  => [ReadingLogController::class, 'index'],
    ],
    '/reading-logs/save' => [
        'POST' => [ReadingLogController::class, 'save'],
    ],
    '/reading-logs/delete' => [
        'POST' => [ReadingLogController::class, 'delete'],
    ],

    // User Management & Profile
    '/users' => [
        'GET'  => [UserController::class, 'index'],
    ],
    '/users/role' => [
        'POST' => [UserController::class, 'updateRole'],
    ],
    '/users/delete' => [
        'POST' => [UserController::class, 'delete'],
    ],
    '/profile' => [
        'GET'  => [UserController::class, 'profile'],
        'POST' => [UserController::class, 'updateProfile'],
    ],

    // JSON API Endpoints (Async & Fetch)
    '/api/ebooks/search' => [
        'GET'  => [ApiController::class, 'searchEbooks'],
    ],
    '/api/reading-logs/log' => [
        'POST' => [ApiController::class, 'logProgress'],
    ],
    '/api/stats' => [
        'GET'  => [ApiController::class, 'stats'],
    ],
    '/api/borrowings/check' => [
        'GET'  => [ApiController::class, 'checkAvailability'],
    ],
];

// Route resolution
if (isset($routes[$path][$method])) {
    [$controllerClass, $action] = $routes[$path][$method];
    $controller = new $controllerClass();
    $controller->$action();
    exit;
}

// 404 Not Found fallback
http_response_code(404);
View::render('errors/404', [
    'pageTitle' => 'Page Not Found — School Library',
    'path'      => $path,
]);
