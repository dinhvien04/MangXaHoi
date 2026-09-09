<?php

$config = require dirname(__DIR__, 2) . '/config/database.php';

define('DB_NAME', $config['name']);
define('DB_HOST', $config['host']);
define('DB_USER', $config['user']);
define('DB_PASS', $config['pass']);
