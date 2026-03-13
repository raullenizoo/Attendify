<?php
/* =============================================================
   ATTENDIFY BOOTSTRAP
   Place this file in: public/bootstrap.php
   ============================================================= */

// UNIVERSAL PROJECT ROOT DETECTION
// Works from ANY folder (public/, admin/, teacher/, student/, etc.)
$root = __DIR__;
while (!file_exists($root . '/config/db.php') && dirname($root) !== $root) {
    $root = dirname($root);
}

define('ROOT_PATH', $root);
define('BASE_URL', '/Attendifyv1/public/');   // ← ONLY change this if you rename the folder

// Core includes (no more ../ guessing ever again)
require_once ROOT_PATH . '/config/db.php';
require_once ROOT_PATH . '/includes/security.php';
?>