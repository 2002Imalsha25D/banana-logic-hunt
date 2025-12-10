<?php
// api/me.php
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/config.php';

header('Content-Type: application/json');

try {
    $stmt = $pdo->prepare(
        "SELECT current_level, lives, score
           FROM progress
          WHERE user_id = ?"
    );
    $stmt->execute([$userId]);
    $row = $stmt->fetch();

    if (!$row) {
        $pdo->prepare(
            "INSERT IGNORE INTO progress(user_id, current_level, lives, score)
             VALUES(?, ?, ?, ?)"
        )->execute([$userId, INITIAL_LEVEL, INITIAL_LIVES, 0]);

        $row = [
            'current_level' => INITIAL_LEVEL,
            'lives'         => INITIAL_LIVES,
            'score'         => 0
        ];
    }

    echo json_encode([
        'ok'   => true,
        'user' => [
            'id'       => $userId,
            'username' => $username
        ],
        'state' => [
            'current_level'     => (int)$row['current_level'],
            'lives'             => (int)$row['lives'],
            'score'             => (int)$row['score'],
            'seconds_per_level' => PUZZLE_TIME
        ]
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'server']);
}
