<?php
// api/mini1_finish.php
// Check Mini Game 1 answer and reward 1 life if correct

require __DIR__ . '/auth_guard.php';
require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$raw  = file_get_contents('php://input');
$data = $raw ? json_decode($raw, true) : null;

if (!$data || !isset($data['answer'])) {
    echo json_encode(['ok' => false, 'msg' => 'bad_payload']);
    exit;
}

$answer = (string)$data['answer'];

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['mini1_answer'])) {
        echo json_encode(['ok' => false, 'msg' => 'no_question']);
        exit;
    }

    $correct = (string)$_SESSION['mini1_answer'];

    // Load progress row
    $stmt = $pdo->prepare(
        "SELECT current_level, lives, score, highest_level
           FROM progress
          WHERE user_id = ?"
    );
    $stmt->execute([$userId]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) {
        $ins = $pdo->prepare(
            "INSERT INTO progress (user_id, current_level, lives, score, highest_level)
             VALUES (?, ?, ?, ?, ?)"
        );
        $ins->execute([$userId, INITIAL_LEVEL, INITIAL_LIVES, 0, INITIAL_LEVEL]);

        $p = [
            'current_level' => INITIAL_LEVEL,
            'lives'         => INITIAL_LIVES,
            'score'         => 0,
            'highest_level' => INITIAL_LEVEL,
        ];
    }

    $level        = (int)$p['current_level'];
    $lives        = (int)$p['lives'];
    $score        = (int)$p['score'];
    $highestLevel = (int)$p['highest_level'];

    $result  = 'wrong';
    $wonLife = false;

    // Compare trimmed text
    if (trim($answer) === trim($correct)) {
        $result = 'correct';

        // Only give +1 life if under max (INITIAL_LIVES, e.g. 3)
        if ($lives < INITIAL_LIVES) {
            $lives++;
            $wonLife = true;
        }
    }

    // Save progress (only lives/score/level touched)
    $upd = $pdo->prepare(
        "UPDATE progress
            SET current_level = ?,
                lives         = ?,
                score         = ?,
                highest_level = ?
          WHERE user_id = ?"
    );
    $upd->execute([$level, $lives, $score, $highestLevel, $userId]);

    echo json_encode([
        'ok'      => true,
        'result'  => $result,
        'wonLife' => $wonLife,
        'lives'   => $lives,
        'score'   => $score,
        'level'   => $level,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'mini1_finish_error']);
}
