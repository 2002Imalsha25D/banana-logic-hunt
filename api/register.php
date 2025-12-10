<?php
// api/register.php
require __DIR__ . '/db.php';

// JSON response (charset added to silence one of the DevTools hints)
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['ok' => false, 'msg' => 'wrong_method']);
    exit;
}

$raw  = file_get_contents('php://input');
$data = json_decode($raw, true);

if (!$data) {
    echo json_encode(['ok' => false, 'msg' => 'no_data']);
    exit;
}

$email    = trim($data['email']    ?? '');
$username = trim($data['username'] ?? '');
$pass     = $data['password']      ?? '';
$confirm  = $data['confirm']       ?? '';

// ---- validation ----
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['ok' => false, 'msg' => 'bad_email']);
    exit;
}
if (strlen($username) < 3) {
    echo json_encode(['ok' => false, 'msg' => 'short_username']);
    exit;
}
if (strlen($pass) < 8) {
    echo json_encode(['ok' => false, 'msg' => 'short_password']);
    exit;
}
if ($pass !== $confirm) {
    echo json_encode(['ok' => false, 'msg' => 'mismatch']);
    exit;
}

try {
    // check duplicates
    $check = $pdo->prepare(
        "SELECT id FROM users WHERE email = ? OR username = ? LIMIT 1"
    );
    $check->execute([$email, $username]);
    if ($check->fetch()) {
        echo json_encode(['ok' => false, 'msg' => 'taken']);
        exit;
    }

    // insert user
    $hash = password_hash($pass, PASSWORD_DEFAULT);

    $pdo->beginTransaction();

    $stmt = $pdo->prepare(
        "INSERT INTO users (email, username, password_hash)
         VALUES (?, ?, ?)"
    );
    $stmt->execute([$email, $username, $hash]);
    $uid = (int)$pdo->lastInsertId();

    // initial progress
    $stmt2 = $pdo->prepare(
        "INSERT INTO progress (user_id, current_level, lives, score)
         VALUES (?, 1, 3, 0)"
    );
    $stmt2->execute([$uid]);

    $pdo->commit();

    // start session for this new user
    $_SESSION['uid']      = $uid;
    $_SESSION['username'] = $username;

    echo json_encode(['ok' => true]);
} catch (Throwable $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode([
        'ok'     => false,
        'msg'    => 'server_error',
        'detail' => $e->getMessage(), // you can remove 'detail' later if you like
    ]);
}
