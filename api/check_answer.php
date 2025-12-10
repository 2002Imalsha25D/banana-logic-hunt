<?php
// api/check_answer.php
// Handles both answered + timeout events for main game
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/config.php';   // for POINTS_CORRECT, INITIAL_LIVES, MAX_LEVEL

header('Content-Type: application/json');

// Read JSON body
$raw  = file_get_contents('php://input');
$data = $raw ? json_decode($raw, true) : null;

if (!$data || !isset($data['status']) || !isset($data['answer'])) {
    http_response_code(422);
    echo json_encode(['ok'=>false,'msg'=>'bad_payload']);
    exit;
}

$status = $data['status'];        // 'answered' | 'timeout'
$answer = (int)$data['answer'];   // chosen answer (0 for timeout)

try {
    // get progress row
    $stmt = $pdo->prepare(
        "SELECT current_level, lives, score, highest_level
           FROM progress
          WHERE user_id = ?"
    );
    $stmt->execute([$userId]);
    $p = $stmt->fetch();

    if (!$p) {
        // initial progress row
        $pdo->prepare(
            "INSERT INTO progress(user_id, current_level, lives, score, highest_level)
             VALUES(?, ?, ?, ?, ?)"
        )->execute([$userId, INITIAL_LEVEL, INITIAL_LIVES, 0, INITIAL_LEVEL]);

        $p = [
            'current_level' => INITIAL_LEVEL,
            'lives'         => INITIAL_LIVES,
            'score'         => 0,
            'highest_level' => INITIAL_LEVEL
        ];
    }

    $level        = (int)$p['current_level'];
    $lives        = (int)$p['lives'];
    $score        = (int)$p['score'];
    $highestLevel = (int)$p['highest_level'];

    $result       = 'wrong';
    $gain         = 0;
    $lostLife     = false;
    $finishedGame = false;
    $gameOver     = false;

    if ($lives <= 0) {
        echo json_encode([
            'ok'       => true,
            'result'   => 'no_lives',
            'lives'    => 0,
            'score'    => $score,
            'level'    => $level,
            'gameOver' => true
        ]);
        exit;
    }

    if ($status === 'timeout') {
        // lose a life for timeout
        $lives--;
        $lostLife = true;
        $result   = 'timeout';
    } else { // answered
        if (!isset($_SESSION['banana_answer'])) {
            echo json_encode(['ok'=>false,'msg'=>'missing_session']);
            exit;
        }

        $correctAns = (int)$_SESSION['banana_answer'];

        if ($answer === $correctAns) {
            // correct
            $result = 'correct';
            $gain   = POINTS_CORRECT;
            $score += $gain;

            if ($level < MAX_LEVEL) {
                $level++;
                if ($level > $highestLevel) {
                    $highestLevel = $level;
                }
            } else {
                // finished all levels
                $finishedGame = true;
                $gameOver     = true;
            }
        } else {
            // wrong
            $result   = 'wrong';
            $lives--;
            $lostLife = true;
        }
    }

    if ($lives <= 0) {
        $gameOver = true;
    }

    // update progress
    $upd = $pdo->prepare(
        "UPDATE progress
            SET current_level = ?,
                lives         = ?,
                score         = ?,
                highest_level = ?
          WHERE user_id = ?"
    );
    $upd->execute([$level, $lives, $score, $highestLevel, $userId]);

    // update leaderboard best score
    $pdo->prepare(
        "INSERT INTO leaderboard(user_id, best_score)
         VALUES(?, ?)
         ON DUPLICATE KEY UPDATE best_score = GREATEST(best_score, VALUES(best_score))"
    )->execute([$userId, $score]);

    echo json_encode([
        'ok'           => true,
        'result'       => $result,        // 'correct' | 'wrong' | 'timeout' | 'no_lives'
        'gained'       => $gain,
        'lostLife'     => $lostLife,
        'lives'        => $lives,
        'score'        => $score,
        'level'        => $level,
        'finishedGame' => $finishedGame,
        'gameOver'     => $gameOver
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok'=>false,'msg'=>'server_check']);
}
