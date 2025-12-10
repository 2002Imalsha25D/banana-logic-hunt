<?php
// api/get_puzzle.php
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/config.php';

header('Content-Type: application/json');

try {
    // ---- progress for this user ----
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

    if ($lives <= 0) {
        echo json_encode([
            'ok'     => false,
            'reason' => 'no_lives',
            'lives'  => 0,
            'score'  => $score,
            'level'  => $level
        ]);
        exit;
    }

    // ---- call Banana API via cURL ----
    $ch = curl_init(BANANA_API_URL);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT        => 8,
        CURLOPT_SSL_VERIFYPEER => false, // for local dev
        CURLOPT_SSL_VERIFYHOST => false,
    ]);

    $resp = curl_exec($ch);
    if ($resp === false) {
        throw new Exception('banana_api_fail: ' . curl_error($ch));
    }
    curl_close($ch);

    $api = json_decode($resp, true);
    if (!is_array($api) || empty($api['question']) || !isset($api['solution'])) {
        throw new Exception('banana_bad_response');
    }

    $solution = (int)$api['solution'];
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    $_SESSION['banana_answer'] = $solution;

    // build 4 answer options
    $answers = [$solution];
    while (count($answers) < 4) {
        $offset    = rand(1, 9);
        $candidate = $solution + ($offset * (rand(0, 1) ? 1 : -1));
        if ($candidate < 0) continue;
        if (!in_array($candidate, $answers, true)) {
            $answers[] = $candidate;
        }
    }
    shuffle($answers);

    echo json_encode([
        'ok'             => true,
        'questionUrl'    => $api['question'],
        'answers'        => $answers,
        'lives'          => $lives,
        'score'          => $score,
        'level'          => $level,
        'secondsAllowed' => PUZZLE_TIME
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'     => false,
        'reason' => 'server_puzzle'
    ]);
}
