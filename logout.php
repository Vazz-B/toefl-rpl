<?php
/**
 * Logout
 */
require_once __DIR__ . '/includes/functions.php';

$_SESSION = [];
session_destroy();

// Start new session for flash message
session_start();
setFlash('success', 'Anda telah berhasil logout.');
redirect('/login.php');
