<?php
// api/mini1_start.php
// Mini Game 1 – serve one local MCQ question and remember the answer in session

require __DIR__ . '/auth_guard.php';
require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Ensure progress row exists for this user
    $stmt = $pdo->prepare(
        "SELECT current_level, lives, score
           FROM progress
          WHERE user_id = ?"
    );
    $stmt->execute([$userId]);
    $p = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$p) {
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

    // -----------------------------------------------------------------
    // LOCAL QUESTION BANK (you can change / add more questions here)
    // -----------------------------------------------------------------
    $questions = [
        [
            'q'     => 'How many bananas are in a dozen?',
            'ans'   => '12',
            'wrong' => ['10', '8', '6'],
        ],
        [
            'q'     => 'What is 5 × 3?',
            'ans'   => '15',
            'wrong' => ['12', '10', '20'],
        ],
        [
            'q'     => 'Which planet is known as the Red Planet?',
            'ans'   => 'Mars',
            'wrong' => ['Venus', 'Jupiter', 'Mercury'],
        ],
        [
            'q'     => 'Which animal is known as the King of the Jungle?',
            'ans'   => 'Lion',
            'wrong' => ['Tiger', 'Elephant', 'Giraffe'],
        ],
        [
            'q'     => 'What is the capital of Sri Lanka?',
            'ans'   => 'Sri Jayawardenepura Kotte',
            'wrong' => ['Colombo', 'Galle', 'Kandy'],
        ],
        [
            'q'     => 'How many sides does a triangle have?',
            'ans'   => '3',
            'wrong' => ['4', '5', '6'],
        ],
        [
            'q'     => 'What is 9 − 4?',
            'ans'   => '5',
            'wrong' => ['6', '4', '9'],
        ],
        [
            'q'     => 'Which one is NOT a primary color of light?',
            'ans'   => 'Yellow',
            'wrong' => ['Red', 'Green', 'Blue'],
        ],
        [
            'q'     => 'Which device is used to browse websites?',
            'ans'   => 'Web browser',
            'wrong' => ['Calculator', 'Notepad', 'Camera'],
        ],
        [
            'q'     => 'Which of these is a fruit?',
            'ans'   => 'Banana',
            'wrong' => ['Potato', 'Carrot', 'Broccoli'],
        ],
    ];

    // Pick one random question
    $item = $questions[array_rand($questions)];

    $questionText = $item['q'];
    $correct      = $item['ans'];
    $answers      = $item['wrong'];
    $answers[]    = $correct;
    shuffle($answers);

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Save correct answer in session for mini1_finish.php
    $_SESSION['mini1_answer'] = $correct;

    echo json_encode([
        'ok'           => true,
        'questionText' => $questionText,
        'answers'      => $answers,
        'lives'        => $lives,
        'score'        => $score,
        'level'        => $level,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'  => false,
        'msg' => 'mini1_start_error',
        // 'err' => $e->getMessage()  // for debugging if you want
    ]);
}
