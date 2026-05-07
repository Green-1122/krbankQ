<?php
/**
 * KrBank - Bootstrap / Init File
 * Loaded at the top of every entry point
 */

// Load configuration
require_once __DIR__ . '/config/app.php';
require_once __DIR__ . '/config/database.php';

// Start secure session
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_secure', '0'); // Set to 1 in production with HTTPS
    ini_set('session.use_strict_mode', '1');
    ini_set('session.cookie_samesite', 'Strict');
    session_name(SESSION_NAME);
    session_start();
}

// Auto-load models and controllers
spl_autoload_register(function ($class) {
    $paths = [MODELS_PATH, CONTROLLERS_PATH];
    foreach ($paths as $path) {
        $file = $path . '/' . $class . '.php';
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

// CSRF Token Management
function generateCsrfToken(): string {
    if (empty($_SESSION[CSRF_TOKEN_NAME])) {
        $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
    }
    return $_SESSION[CSRF_TOKEN_NAME];
}

function verifyCsrfToken(string $token): bool {
    return isset($_SESSION[CSRF_TOKEN_NAME]) && hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
}

function csrfField(): string {
    return '<input type="hidden" name="' . CSRF_TOKEN_NAME . '" value="' . generateCsrfToken() . '">';
}

// Flash messages
function setFlash(string $type, string $message): void {
    $_SESSION['flash'][$type] = $message;
}

function getFlash(string $type): ?string {
    $msg = $_SESSION['flash'][$type] ?? null;
    unset($_SESSION['flash'][$type]);
    return $msg;
}

function hasFlash(string $type): bool {
    return isset($_SESSION['flash'][$type]);
}

// Authentication helpers
function isLoggedIn(): bool {
    return isset($_SESSION['user_id']);
}

function isAdmin(): bool {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

function requireLogin(): void {
    if (!isLoggedIn()) {
        setFlash('error', 'Please log in to continue.');
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function requireAdmin(): void {
    if (!isAdmin()) {
        setFlash('error', 'Access denied.');
        header('Location: ' . APP_URL . '/login.php');
        exit;
    }
}

function currentUserId(): ?int {
    return $_SESSION['user_id'] ?? null;
}

// Sanitization
function sanitize(string $input): string {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function formatCurrency(float $amount, string $currency = 'USD'): string {
    $symbols = ['USD' => '$', 'EUR' => '€', 'GBP' => '£', 'BTC' => '₿'];
    $sym = $symbols[$currency] ?? $currency . ' ';
    return $sym . number_format($amount, 2);
}

function formatDate(string $date, string $format = 'M d, Y'): string {
    return date($format, strtotime($date));
}

function redirect(string $url): void {
    header("Location: $url");
    exit;
}

function jsonResponse(array $data, int $code = 200): void {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

// Generate unique account number
function generateAccountNumber(): string {
    return 'KRB' . date('y') . str_pad(random_int(0, 99999999), 8, '0', STR_PAD_LEFT);
}

// Generate card number (mock)
function generateCardNumber(string $network = 'visa'): string {
    $prefixes = ['visa' => '4', 'mastercard' => '5', 'amex' => '3'];
    $prefix = $prefixes[$network] ?? '4';
    $num = $prefix;
    for ($i = 1; $i < 16; $i++) {
        $num .= random_int(0, 9);
    }
    return $num;
}
