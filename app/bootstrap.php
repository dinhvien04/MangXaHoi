<?php

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

require_once __DIR__ . '/Core/database.php';
require_once __DIR__ . '/Core/http.php';
require_once __DIR__ . '/Core/password.php';
require_once __DIR__ . '/Core/forms.php';
require_once __DIR__ . '/Core/view.php';
require_once __DIR__ . '/Support/time.php';

// Shared dependency first.
require_once __DIR__ . '/Notifications/functions.php';

// User/account features.
require_once __DIR__ . '/Users/functions.php';
require_once __DIR__ . '/Users/presenters.php';
require_once __DIR__ . '/Auth/functions.php';

// Social interactions.
require_once __DIR__ . '/Interactions/Block/functions.php';
require_once __DIR__ . '/Interactions/Follow/functions.php';
require_once __DIR__ . '/Posts/functions.php';
require_once __DIR__ . '/Interactions/Likes/functions.php';
require_once __DIR__ . '/Interactions/Comments/functions.php';

// Communication and discovery.
require_once __DIR__ . '/Messages/functions.php';
require_once __DIR__ . '/Search/functions.php';

// Administration.
require_once __DIR__ . '/Admin/functions.php';
