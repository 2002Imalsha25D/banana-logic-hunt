<?php
// C:\wamp64\www\Banana Logic Hunt\api\login.php
require __DIR__ . '/db.php';

// output JSON (with charset)
header('Content-Type: application/json; charset=utf-8');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false, 'msg' => 'bad_method']);
    exit;
}

// Read JSON body or fallback to normal POST
$raw  = file_get_contents('php://input');
$data = $raw ? json_decode($raw, true) : null;
if (!$data && !empty($_POST)) {
    $data = $_POST;
}

$login = trim($data['login'] ?? $data['username'] ?? '');
$pass  = $data['password'] ?? '';

if ($login === '' || $pass === '') {
    http_response_code(422);
    echo json_encode(['ok' => false, 'msg' => 'missing']);
    exit;
}

try {
    // login with username OR email
    $stmt = $pdo->prepare(
        "SELECT id, username, email, password_hash
         FROM users
         WHERE username = ? OR email = ?
         LIMIT 1"
    );
    $stmt->execute([$login, $login]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($pass, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode(['ok' => false, 'msg' => 'invalid']);
        exit;
    }

    // Valid login: set session
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['uid']      = (int)$user['id'];
    $_SESSION['username'] = $user['username'];

    echo json_encode([
        'ok'       => true,
        'username' => $user['username']
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'server']);
}
