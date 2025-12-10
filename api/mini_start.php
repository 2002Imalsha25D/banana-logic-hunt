<?php
// api/mini_start.php
// Mini Game 2 (catch bananas) – start round and return config + current state.

require __DIR__ . '/auth_guard.php';
require __DIR__ . '/config.php';

header('Content-Type: application/json');

try {
    // Ensure there is a progress row for this user
    $stmt = $pdo->prepare(
        "SELECT current_level, lives, score
           FROM progress
          WHERE user_id = ?"
    );
    $stmt->execute([$userId]);
    $p = $stmt->fetch();

    if (!$p) {
        $pdo->prepare(
            "INSERT INTO progress(user_id, current_level, lives, score)
             VALUES(?, ?, ?, ?)"
        )->execute([$userId, INITIAL_LEVEL, INITIAL_LIVES, 0]);

        $p = [
            'current_level' => INITIAL_LEVEL,
            'lives'         => INITIAL_LIVES,
            'score'         => 0
        ];
    }

    $level = (int)$p['current_level'];
    $lives = (int)$p['lives'];
    $score = (int)$p['score'];

    // Mini game config – you can tweak these numbers
    $TARGET_BANANAS = 10;
    $SECONDS        = 30;

    echo json_encode([
        'ok'      => true,
        'target'  => $TARGET_BANANAS,
        'seconds' => $SECONDS,
        'state'   => [
            'level' => $level,
            'lives' => $lives,
            'score' => $score,
        ],
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'mini_start_error']);
}
