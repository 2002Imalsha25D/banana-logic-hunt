<?php
// api/leaderboard.php
require __DIR__ . '/auth_guard.php';
require __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');

try {
    // Use progress table so every player with a progress row appears
    $stmt = $pdo->query(
        "SELECT u.id   AS uid,
                u.username,
                p.score AS best_score
           FROM users    u
           JOIN progress p ON p.user_id = u.id
         ORDER BY p.score DESC, u.username ASC"
    );

    $rows    = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $entries = [];
    $rank    = 1;

    foreach ($rows as $r) {
        $entries[] = [
            'rank'   => $rank++,
            'player' => $r['username'],
            'score'  => (int)$r['best_score'],
            'isYou'  => ((int)$r['uid'] === (int)$userId),
        ];
    }

    echo json_encode([
        'ok'        => true,
        'entries'   => $entries,
        'your_name' => $username,
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        'ok'  => false,
        'msg' => 'server_lb',
        'err' => $e->getMessage(),   // remove later if you don't want to expose
    ]);
}
