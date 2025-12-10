<?php
// api/auth_guard.php
// This file MUST run before any HTML is sent.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

if (empty($_SESSION['uid']) || empty($_SESSION['username'])) {
    header('Content-Type: application/json');
    http_response_code(401);
    echo json_encode([
        'ok'  => false,
        'msg' => 'unauthenticated'
    ]);
    exit;
}

$userId   = (int)$_SESSION['uid'];
$username = $_SESSION['username'];
