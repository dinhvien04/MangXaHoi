<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/core/database.php';
require_once __DIR__ . '/core/http.php';
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
