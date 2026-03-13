<?php
// security.php

// ─────────────────────────
// 1. Start secure session
// ─────────────────────────
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,       // session lasts until browser closes
        'path' => '/',         
        'secure' => false,     // change to true on HTTPS
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
}

// ─────────────────────────
// 2. Regenerate session occasionally
// ─────────────────────────
if (!isset($_SESSION['created'])) {
    $_SESSION['created'] = time();
} elseif (time() - $_SESSION['created'] > 300) { // regenerate every 5 minutes
    session_regenerate_id(true);
    $_SESSION['created'] = time();
}

// ─────────────────────────
// 3. Delete old or invalid cookies
// ─────────────────────────
foreach ($_COOKIE as $name => $value) {
    // Optional: only delete specific cookies
    if ($name === 'user_session' || $name === 'remember_me') {
        setcookie($name, '', time() - 3600, '/'); // expired
    }
}

// ─────────────────────────
// 4. Optional: IP logging / detection
// ─────────────────────────
function getUserIP() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return $_SERVER['HTTP_X_FORWARDED_FOR'];
    return $_SERVER['REMOTE_ADDR'];
}

$user_ip = getUserIP();

// ─────────────────────────
// 5. Rate limiting (less aggressive)
// ─────────────────────────
$max_requests = 100; // max requests per 60s
$time_window = 60;

if (!isset($_SESSION['request_times'])) {
    $_SESSION['request_times'] = [];
}

// Remove old timestamps
$current_time = time();
$_SESSION['request_times'] = array_filter(
    $_SESSION['request_times'],
    fn($timestamp) => $timestamp > ($current_time - $time_window)
);

// Optional: only log, don’t block aggressively
if (count($_SESSION['request_times']) >= $max_requests) {
    error_log("Rate limit reached for IP: $user_ip");
}

// Add current request
$_SESSION['request_times'][] = $current_time;
?>