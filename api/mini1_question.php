<?php
// api/mini1_question.php
// Serve a simple multiple-choice question for Mini Game 1
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Ensure progress row exists
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

    // -------------------------
    // Static question bank
    // id, text, answers[0..3], correct index
    // -------------------------
    $questions = [
        [
            'id'      => 1,
            'text'    => 'What is 2 + 3 ?',
            'answers' => ['4', '5', '6', '7'],
            'correct' => 1   // '5'
        ],
        [
            'id'      => 2,
            'text'    => 'What is 9 - 4 ?',
            'answers' => ['4', '5', '6', '9'],
            'correct' => 1   // '5'
        ],
        [
            'id'      => 3,
            'text'    => 'Which number is even?',
            'answers' => ['3', '5', '8', '9'],
            'correct' => 2   // '8'
        ],
        [
            'id'      => 4,
            'text'    => 'What is 3 × 3 ?',
            'answers' => ['6', '7', '8', '9'],
            'correct' => 3   // '9'
        ],
        [
            'id'      => 5,
            'text'    => 'Which is the smallest number?',
            'answers' => ['4', '2', '7', '9'],
            'correct' => 1   // '2'
        ],
    ];

    // Pick random question
    $q = $questions[array_rand($questions)];

    echo json_encode([
        'ok'           => true,
        'questionId'   => $q['id'],
        'questionText' => $q['text'],
        'answers'      => $q['answers'],
        'lives'        => $lives,
        'score'        => $score,
        'level'        => $level
    ]);

} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['ok' => false, 'msg' => 'mini1_question_error']);
}
