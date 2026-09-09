<?php

function searchUser($keyword)
{
    global $db;
    $keyword = trim((string) $keyword);
    if ($keyword === '') {
        return [];
    }

    $like = '%' . $keyword . '%';
    $stmt = $db->prepare("SELECT * FROM users WHERE role = 'User' AND (username LIKE ? OR first_name LIKE ? OR last_name LIKE ?) LIMIT 5");
    $stmt->bind_param('sss', $like, $like, $like);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}
