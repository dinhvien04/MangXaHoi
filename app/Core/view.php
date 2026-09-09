<?php

function showPage($page, $data = '')
{
    $map = [
        'login' => 'Auth/views/login.php',
        'signup' => 'Auth/views/signup.php',
        'forgot_password' => 'Auth/views/forgot_password.php',
        'verify_email' => 'Auth/views/verify_email.php',
        'blocked' => 'Users/views/blocked.php',
        'edit_profile' => 'Users/views/edit_profile.php',
        'profile' => 'Users/views/profile.php',
        'user_not_found' => 'Users/views/user_not_found.php',
        'wall' => 'Posts/views/wall.php',
        'header' => 'Shared/views/header.php',
        'navbar' => 'Shared/views/navbar.php',
        'footer' => 'Shared/views/footer.php',
    ];

    if (!isset($map[$page])) {
        throw new RuntimeException('Không tìm thấy view: ' . $page);
    }

    $view = dirname(__DIR__) . '/' . $map[$page];
    include $view;
}
