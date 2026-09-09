<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off')
        || (isset($_SERVER['SERVER_PORT']) && (int) $_SERVER['SERVER_PORT'] === 443);

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $secure,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    session_start();
}

$now = time();
$idleTimeout = 1800;
$absoluteTimeout = 43200;
if (
    (!empty($_SESSION['_last_activity']) && $now - (int) $_SESSION['_last_activity'] > $idleTimeout)
    || (!empty($_SESSION['_created_at']) && $now - (int) $_SESSION['_created_at'] > $absoluteTimeout)
) {
    session_unset();
    session_regenerate_id(true);
}
$_SESSION['_created_at'] = $_SESSION['_created_at'] ?? $now;
$_SESSION['_last_activity'] = $now;

require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/core/http.php';
require_once __DIR__ . '/core/security.php';
require_once __DIR__ . '/core/password.php';
require_once __DIR__ . '/core/forms.php';
require_once __DIR__ . '/support/time.php';

require_once __DIR__ . '/notifications/functions.php';
require_once __DIR__ . '/users/functions.php';
require_once __DIR__ . '/users/presenters.php';
require_once __DIR__ . '/auth/functions.php';
require_once __DIR__ . '/auth/mail/send_code.php';

require_once __DIR__ . '/interactions/block/functions.php';
require_once __DIR__ . '/interactions/follow/functions.php';
require_once __DIR__ . '/posts/functions.php';
require_once __DIR__ . '/interactions/likes/functions.php';
require_once __DIR__ . '/interactions/comments/functions.php';

require_once __DIR__ . '/messages/functions.php';
require_once __DIR__ . '/search/functions.php';
require_once __DIR__ . '/admin/functions.php';

csrfToken();
