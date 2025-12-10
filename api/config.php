<?php
// api/config.php
// --- Banana Logic Hunt global config ---

// DB credentials – change to match phpMyAdmin
if (!defined('DB_HOST')) define('DB_HOST', 'localhost');
if (!defined('DB_NAME')) define('DB_NAME', 'banana_game');
if (!defined('DB_USER')) define('DB_USER', 'root');
if (!defined('DB_PASS')) define('DB_PASS', '');  // or your password

// Banana API
if (!defined('BANANA_API_URL')) {
    define('BANANA_API_URL', 'https://marcconrad.com/uob/banana/api.php');
}

// Game constants
if (!defined('INITIAL_LIVES'))   define('INITIAL_LIVES',   3);
if (!defined('INITIAL_LEVEL'))   define('INITIAL_LEVEL',   1);
if (!defined('MAX_LEVEL'))       define('MAX_LEVEL',       15);
if (!defined('POINTS_CORRECT'))  define('POINTS_CORRECT',  50);
if (!defined('POINTS_WRONG'))    define('POINTS_WRONG',    0);
if (!defined('POINTS_TIMEOUT'))  define('POINTS_TIMEOUT',  0);

// Timer per puzzle (seconds)
if (!defined('PUZZLE_TIME'))     define('PUZZLE_TIME',     40);
