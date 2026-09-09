<?php

function passwordMatches($plainPassword, $storedPassword)
{
    $storedPassword = (string) $storedPassword;
    if ($storedPassword === '') {
        return false;
    }

    $info = password_get_info($storedPassword);
    if (($info['algoName'] ?? 'unknown') !== 'unknown') {
        return password_verify((string) $plainPassword, $storedPassword);
    }

    return hash_equals($storedPassword, (string) $plainPassword)
        || hash_equals($storedPassword, md5((string) $plainPassword));
}

function upgradePasswordHash($userId, $plainPassword, $storedPassword)
{
    global $db;

    $info = password_get_info((string) $storedPassword);
    if (($info['algoName'] ?? 'unknown') !== 'unknown') {
        return (string) $storedPassword;
    }

    $newHash = password_hash((string) $plainPassword, PASSWORD_DEFAULT);
    $stmt = $db->prepare("UPDATE users SET password = ?, password_text = '' WHERE id = ?");
    $userId = (int) $userId;
    $stmt->bind_param('si', $newHash, $userId);
    $stmt->execute();
    $stmt->close();

    return $newHash;
}
