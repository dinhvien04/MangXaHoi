<?php

function publicUserData($user)
{
    if (!$user) {
        return null;
    }

    return [
        'id' => (int) $user['id'],
        'first_name' => (string) $user['first_name'],
        'last_name' => (string) $user['last_name'],
        'username' => (string) $user['username'],
        'profile_pic' => (string) $user['profile_pic'],
    ];
}
