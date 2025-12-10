<?php
// api/mini1_finish.php
// Check Mini Game 1 answer and reward 1 life if correct
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/config.php';

header('Content-Type: application/json');

// Read JSON body
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

    // We must have asked a question before
    if (!isset($_SESSION['mini1_answer'])) {
        echo json_encode(['ok' => false, 'msg' => 'no_question']);
        exit;
    }

    $correct = (string)$_SESSION['mini1_answer'];

    // ---- Load or create progress row (NO highest_level here) ----
    $stmt = $pdo->prepare(
        "SELECT current_level, lives, score
           FROM progress
          WHERE user_id = ?"
    );
    $stmt->execute([$userId]);
    $p = $stmt->fetch();

    if (!$p) {
        // If for some reason this user has no row, create a fresh one
        $ins = $pdo->prepare(
            "INSERT INTO progress (user_id, current_level, lives, score)
             VALUES (?, ?, ?, ?)"
        );
        $ins->execute([$userId, INITIAL_LEVEL, INITIAL_LIVES, 0]);

        $p = [
            'current_level' => INITIAL_LEVEL,
            'lives'         => INITIAL_LIVES,
            'score'         => 0,
        ];
    }

    $level = (int)$p['current_level'];
    $lives = (int)$p['lives'];
    $score = (int)$p['score'];

    if ($lives < 0) $lives = 0;
    if ($lives > INITIAL_LIVES) $lives = INITIAL_LIVES;
    if ($score < 0) $score = 0;

    // ---- Check answer ----
    $result  = 'wrong';
    $wonLife = false;

    // compare trimmed text
    if (trim($answer) === trim($correct)) {
        $result = 'correct';

        // Reward +1 life up to INITIAL_LIVES (3)
        if ($lives < INITIAL_LIVES) {
            $lives++;
            $wonLife = true;
        }
    }

    // ---- Save back progress (still NO highest_level) ----
    $upd = $pdo->prepare(
        "UPDATE progress
            SET current_level = ?,
                lives         = ?,
                score         = ?
          WHERE user_id = ?"
    );
    $upd->execute([$level, $lives, $score, $userId]);

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
    echo json_encode([
        'ok'  => false,
        'msg' => 'mini1_finish_error'
    ]);
}
