<?php

$config = require dirname(__DIR__, 2) . '/config/database.php';

$db = mysqli_connect(
    $config['host'],
    $config['user'],
    $config['pass'],
    $config['name']
) or die('cơ sở dữ liệu không được kết nối');

$db->set_charset($config['charset']);
