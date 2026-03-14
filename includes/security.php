<?php
// security.php

// ─────────────────────────
// Helpers (used across the app)
// ─────────────────────────

/**
 * Detect if current request is over HTTPS (supports proxies).
 */
function isHttps(): bool
{
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && strtolower($_SERVER['HTTP_X_FORWARDED_PROTO']) === 'https') {
        return true;
    }
    return false;
}

/**
 * Return the current user's IP address (best effort).
 */
function getUserIP(): string
{
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    }
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // May be a comma-separated list
        return trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0]);
    }
    return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
}

/**
 * Generate or return a CSRF token stored in session.
 */
function csrf_token(): string
{
    if (session_status() !== PHP_SESSION_ACTIVE) {
        return '';
    }

    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

/**
 * Output a hidden CSRF input field.
 */
function csrf_input_field(): void
{
    $token = htmlspecialchars(csrf_token(), ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    echo "<input type=\"hidden\" name=\"csrf_token\" value=\"$token\">";
}

/**
 * Validate CSRF token (returns true when valid).
 */
function validate_csrf_token(string $token): bool
{
    return session_status() === PHP_SESSION_ACTIVE && isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// ─────────────────────────
// 1. Secure session configuration
// ─────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    // Enforce strict session mode to prevent session fixation
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');
    ini_set('session.cookie_samesite', 'Strict');

    // Only set secure cookies when HTTPS is detected
    ini_set('session.cookie_secure', isHttps() ? '1' : '0');

    // Use a custom session name to avoid default PHPSESSID
    session_name('ATTENDIFYSESSID');

    // Cookie lifetime is 0 (expires when browser closes)
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => isHttps(),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    session_start();
}

// ─────────────────────────
// 2. Security headers
// ─────────────────────────
header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');

if (isHttps()) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains; preload');
}

// Optional: prevent caching of sensitive pages
if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION['user_id'])) {
    header('Cache-Control: no-store, no-cache, must-revalidate, proxy-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
}

// ─────────────────────────
// 3. Session regeneration
// ─────────────────────────
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 300) { // regenerate every 5 minutes
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// ─────────────────────────
// 4. Rate limiting (basic)
// ─────────────────────────
$max_requests = 100; // max requests per 60 seconds
$time_window = 60;

if (!isset($_SESSION['request_times'])) {
    $_SESSION['request_times'] = [];
}

$current_time = time();
$_SESSION['request_times'] = array_filter(
    $_SESSION['request_times'],
    fn($timestamp) => $timestamp > ($current_time - $time_window)
);

$user_ip = getUserIP();

if (count($_SESSION['request_times']) >= $max_requests) {
    error_log("Rate limit reached for IP: $user_ip");
}

$_SESSION['request_times'][] = $current_time;
