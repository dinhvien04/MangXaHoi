<?php

function renderView($view, array $data = [])
{
    $base = __DIR__;
    $map = [
        'layouts/header' => 'layouts/header.php',
        'layouts/navbar' => 'layouts/navbar.php',
        'layouts/footer' => 'layouts/footer.php',
        'user/auth/login' => 'user/auth/login.php',
        'user/auth/signup' => 'user/auth/signup.php',
        'user/auth/forgot-password' => 'user/auth/forgot-password.php',
        'user/auth/verify-email' => 'user/auth/verify-email.php',
        'user/profile/blocked' => 'user/profile/blocked.php',
        'user/profile/edit-profile' => 'user/profile/edit-profile.php',
        'user/profile/profile' => 'user/profile/profile.php',
        'user/profile/user-not-found' => 'user/profile/user-not-found.php',
        'user/posts/wall' => 'user/posts/wall.php',
    ];

    if (!isset($map[$view])) {
        throw new RuntimeException('Không tìm thấy giao diện: ' . $view);
    }
    extract($data, EXTR_SKIP);
    require $base . '/' . $map[$view];
}
