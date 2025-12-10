<?php
// api/reset_life.php
require __DIR__ . '/auth_guard.php';

header('Content-Type: application/json');

try {
    // add 1 life but max 3
    $stmt = $pdo->prepare("SELECT lives FROM progress WHERE user_id = ?");
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    if (!$row) {
        echo json_encode(['ok'=>false,'msg'=>'no_progress']);
        exit;
    }

    $lives = (int)$row['lives'];
    if ($lives < INITIAL_LIVES) {
        $lives++;
        $pdo->prepare("UPDATE progress SET lives = ? WHERE user_id = ?")
            ->execute([$lives, $userId]);
    }

    echo json_encode(['ok'=>true,'lives'=>$lives]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'server_life']);
}
