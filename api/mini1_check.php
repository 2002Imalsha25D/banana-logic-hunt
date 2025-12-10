<?php
// api/mini1_check.php
// Check Mini Game 1 answer and reward +1 life (max INITIAL_LIVES)
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

$raw  = file_get_contents('php://input');
$data = $raw ? json_decode($raw, true) : null;

if (!$data || !isset($data['questionId'], $data['answerIndex'])) {
    echo json_encode(['ok' => false, 'msg' => 'bad_payload']);
    exit;
}

$qid  = (int)$data['questionId'];
$idx  = (int)$data['answerIndex'];

try {
    // Same static questions as mini1_question.php
    $questions = [
        [
            'id'      => 1,
            'text'    => 'What is 2 + 3 ?',
            'answers' => ['4', '5', '6', '7'],
            'correct' => 1
        ],
        [
            'id'      => 2,
            'text'    => 'What is 9 - 4 ?',
            'answers' => ['4', '5', '6', '9'],
            'correct' => 1
        ],
        [
            'id'      => 3,
            'text'    => 'Which number is even?',
            'answers' => ['3', '5', '8', '9'],
            'correct' => 2
        ],
        [
            'id'      => 4,
            'text'    => 'What is 3 × 3 ?',
            'answers' => ['6', '7', '8', '9'],
            'correct' => 3
        ],
        [
            'id'      => 5,
            'text'    => 'Which is the smallest number?',
            'answers' => ['4', '2', '7', '9'],
            'correct' => 1
        ],
    ];

    $map = [];
    foreach ($questions as $q) {
        $map[$q['id']] = $q;
    }

    if (!isset($map[$qid]) || $idx < 0 || $idx > 3) {
        echo json_encode(['ok' => false, 'msg' => 'invalid_question']);
        exit;
    }

    $question = $map[$qid];
    $isCorrect = ($question['correct'] === $idx);

    // Load progress row
    $stmt = $pdo->prepare(
        "SELECT current_level, lives, score
           FROM progress
          WHERE user_id = ?"
    );
    $stmt->execute([$userId]);
    $p = $stmt->fetch();

    if (!$p) {
        $ins = $pdo->prepare(
            "INSERT INTO progress (user_id, current_level, lives, score)
             VALUES (?, ?, ?, ?)"
        );
        $ins->execute([$userId, INITIAL_LEVEL, INITIAL_LIVES, 0]);

        $p = [
            'current_level' => INITIAL_LEVEL,
            'lives'         => INITIAL_LIVES,
            'score'         => 0
        ];
    }

    $level = (int)$p['current_level'];
    $lives = (int)$p['lives'];
    $score = (int)$p['score'];

    if ($lives < 0) $lives = 0;
    if ($lives > INITIAL_LIVES) $lives = INITIAL_LIVES;
    if ($score < 0) $score = 0;

    $result  = $isCorrect ? 'correct' : 'wrong';
    $wonLife = false;

    if ($isCorrect && $lives < INITIAL_LIVES) {
        $lives++;
        $wonLife = true;
    }

    // Save updated lives/score (score unchanged for mini game)
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
        'level'   => $level
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'mini1_check_error']);
}
