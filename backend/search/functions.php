<?php

function searchUser($keyword, $limit = 10)
{
    global $db;
    $keyword = trim((string) $keyword);
    $limit = max(1, min(20, (int) $limit));
    $currentUserId = (int) ($_SESSION['userdata']['id'] ?? 0);
    if ($keyword === '' || mb_strlen($keyword) > 100 || $currentUserId <= 0) {
        return [];
    }

    $like = '%' . $keyword . '%';
    $stmt = $db->prepare("SELECT u.id, u.first_name, u.last_name, u.username, u.profile_pic, u.role
        FROM users u
        WHERE u.ac_status = 1
          AND (u.username LIKE ? OR u.first_name LIKE ? OR u.last_name LIKE ?)
          AND NOT EXISTS (SELECT 1 FROM block_list b WHERE (b.user_id = ? AND b.blocked_user_id = u.id) OR (b.user_id = u.id AND b.blocked_user_id = ?))
        ORDER BY u.id DESC LIMIT ?");
    $stmt->bind_param('sssiii', $like, $like, $like, $currentUserId, $currentUserId, $limit);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    return $rows;
}
