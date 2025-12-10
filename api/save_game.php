<?php
// api/save_game.php
// Handles main-game events: correct / wrong / timeout, updates score & lives.

require __DIR__ . '/auth_guard.php';
require __DIR__ . '/config.php';

header('Content-Type: application/json');

// ---------- read JSON from game.html ----------
$raw  = file_get_contents('php://input');
$data = $raw ? json_decode($raw, true) : null;

if (!$data || !isset($data['event'])) {
    echo json_encode(['ok' => false, 'msg' => 'bad_payload']);
    exit;
}

$event    = $data['event'];                        // 'answer' | 'timeout'
$answer   = isset($data['answer']) ? (int)$data['answer'] : null;
$timeLeft = isset($data['time_left']) ? (int)$data['time_left'] : 0;

try {
    // ---------- ensure progress row ----------
    $stmt = $pdo->prepare(
        "SELECT current_level, lives, score
           FROM progress
          WHERE user_id = ?"
    );
    $stmt->execute([$userId]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) {
        $pdo->prepare(
            "INSERT INTO progress (user_id, current_level, lives, score)
             VALUES (?, ?, ?, ?)"
        )->execute([$userId, INITIAL_LEVEL, INITIAL_LIVES, 0]);

        $p = [
            'current_level' => INITIAL_LEVEL,
            'lives'         => INITIAL_LIVES,
            'score'         => 0
        ];
    }

    $level  = (int)$p['current_level'];
    $lives  = (int)$p['lives'];
    $score  = (int)$p['score'];

    // ---------- constants / limits ----------
    $maxLives       = defined('INITIAL_LIVES')       ? INITIAL_LIVES       : 3;
    $maxLevel       = defined('MAX_LEVEL')           ? MAX_LEVEL           : 15;
    $maxPerLevel    = defined('SCORE_PER_LEVEL_MAX') ? SCORE_PER_LEVEL_MAX : 50;
    $puzzleTimeSecs = defined('PUZZLE_TIME')         ? PUZZLE_TIME         : 40;

    $result   = null;      // 'correct' | 'wrong' | 'timeout'
    $finished = false;
    $gameOver = false;

    // ---------- handle events ----------
    if ($event === 'timeout') {

        $result = 'timeout';
        if ($lives > 0) {
            $lives--;
        }
        if ($lives <= 0) {
            $lives   = 0;
            $gameOver = true;
        }

    } elseif ($event === 'answer') {

        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['banana_answer'])) {
            echo json_encode(['ok' => false, 'msg' => 'no_session_answer']);
            exit;
        }

        $correct = (int)$_SESSION['banana_answer'];
        unset($_SESSION['banana_answer']); // don’t reuse

        if ($answer === $correct) {
            // ---------- CORRECT ----------
            $result = 'correct';

            // scoring: up to maxPerLevel points based on time left
            if ($puzzleTimeSecs <= 0) {
                $puzzleTimeSecs = 40;
            }
            if ($timeLeft < 0) $timeLeft = 0;
            if ($timeLeft > $puzzleTimeSecs) $timeLeft = $puzzleTimeSecs;

            $delta = (int) floor(($timeLeft / $puzzleTimeSecs) * $maxPerLevel);
            if ($delta < 10) $delta = 10;                 // minimum for correct
            if ($delta > $maxPerLevel) $delta = $maxPerLevel;

            $score += $delta;

            // go to next level
            $level++;
            if ($level > $maxLevel) {
                $level    = $maxLevel;
                $finished = true;
                $gameOver = true;
            }

        } else {
            // ---------- WRONG ----------
            $result = 'wrong';
            if ($lives > 0) {
                $lives--;
            }
            if ($lives <= 0) {
                $lives   = 0;
                $gameOver = true;
            }
        }

    } else {
        echo json_encode(['ok' => false, 'msg' => 'unknown_event']);
        exit;
    }

    // clamp lives between 0 and maxLives
    if ($lives < 0) $lives = 0;
    if ($lives > $maxLives) $lives = $maxLives;

    // ---------- save back to DB ----------
    $upd = $pdo->prepare(
        "UPDATE progress
            SET current_level = ?,
                lives         = ?,
                score         = ?
          WHERE user_id = ?"
    );
    $upd->execute([$level, $lives, $score, $userId]);

    echo json_encode([
        'ok'       => true,
        'result'   => $result,   // 'correct' | 'wrong' | 'timeout'
        'lives'    => $lives,
        'score'    => $score,
        'level'    => $level,
        'finished' => $finished,
        'gameOver' => $gameOver
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'  => false,
        'msg' => 'server_error: ' . $e->getMessage()
    ]);
}
