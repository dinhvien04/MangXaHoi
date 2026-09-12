<?php

declare(strict_types=1);

$failures = 0;
$assertions = 0;

function check($condition, string $message): void
{
    global $failures, $assertions;
    $assertions++;
    if ($condition) {
        echo "[PASS] {$message}\n";
        return;
    }
    $failures++;
    echo "[FAIL] {$message}\n";
}

require dirname(__DIR__, 2) . '/backend/bootstrap.php';

global $db;
$db->query('SET FOREIGN_KEY_CHECKS=0');
foreach (['rate_limits','notifications','messages','likes','comments','follow_list','block_list','posts','users'] as $table) {
    $db->query("TRUNCATE TABLE {$table}");
}
$db->query('SET FOREIGN_KEY_CHECKS=1');

$_SESSION = [];
$token = csrfToken();
check(strlen($token) === 64, 'CSRF token uses 32 random bytes');
check(isValidCsrfToken($token), 'Valid CSRF token is accepted');
check(!isValidCsrfToken('wrong-token'), 'Invalid CSRF token is rejected');

$otp = buildOtpState('test@example.com', 123456, 'test');
$result = verifyOtpState($otp, '123456');
check($result['ok'] === true, 'Correct OTP is accepted');
$otp = buildOtpState('test@example.com', 123456, 'test');
$otp['expires_at'] = time() - 1;
check(verifyOtpState($otp, '123456')['reason'] === 'expired', 'Expired OTP is rejected');
$otp = buildOtpState('test@example.com', 123456, 'test');
for ($i = 0; $i < HANDBOOK_OTP_MAX_ATTEMPTS; $i++) {
    verifyOtpState($otp, '000000');
}
check(verifyOtpState($otp, '000000')['reason'] === 'locked', 'OTP locks after maximum attempts');

function createTestUser(string $email, string $username, string $password = 'Password123!'): int
{
    $ok = createUser([
        'first_name' => 'Test', 'last_name' => 'User', 'email' => $email,
        'username' => $username, 'password' => $password, 'gender' => 1,
    ]);
    if (!$ok) return 0;
    global $db;
    return (int) $db->insert_id;
}

function activateUser(int $id): void
{
    global $db;
    $stmt = $db->prepare('UPDATE users SET ac_status=1 WHERE id=?');
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $stmt->close();
}

function loginAsUser(int $id): void
{
    $_SESSION['Auth'] = true;
    $_SESSION['userdata'] = getUser($id);
}

$alice = createTestUser('alice@example.com', 'alice');
$bob = createTestUser('bob@example.com', 'bob');
check($alice > 0 && $bob > 0, 'Users can be created');
activateUser($alice); activateUser($bob);
check(createTestUser('alice@example.com', 'alice2') === 0, 'Database rejects duplicate email');
check(createTestUser('alice2@example.com', 'alice') === 0, 'Database rejects duplicate username');

$unverified = createTestUser('pending@example.com', 'pendinguser');
loginAsUser($unverified);
check(validateUserSession(true)['ok'] === true, 'Unverified user is accepted only for verification flow');
check(validateUserSession(false)['reason'] === 'unverified', 'Unverified user cannot use protected application actions');
activateUser($unverified);

loginAsUser($alice);
check(validateUserSession(false)['ok'] === true, 'Active user session is valid');
check(followUser($alice) === false, 'User cannot follow self');
check(followUser(999999) === false, 'User cannot follow missing account');
check(followUser($bob) === true, 'User can follow active account');
check(followUser($bob) === false, 'Duplicate follow is rejected');

check(sendMessage($alice, 'hello') === false, 'User cannot message self');
check(sendMessage(999999, 'hello') === false, 'User cannot message missing account');
check(sendMessage($bob, 'hello Bob') === true, 'User can message active account');
check(sendMessage($bob, str_repeat('x', 2001)) === false, 'Oversized messages are rejected');
check(createNotification($alice, $alice, 'self notification') === false, 'Self notifications are rejected');
$searchRows = searchUser('bob');
check(count($searchRows) === 1 && (int)$searchRows[0]['id'] === $bob, 'Search returns an active matching user');

$stmt = $db->prepare("INSERT INTO posts(user_id,post_img,post_text,is_reported,is_approved) VALUES(?,'test.jpg','hello',0,1)");
$stmt->bind_param('i', $bob); $stmt->execute(); $postBob=(int)$db->insert_id; $stmt->close();
check(like(999999) === false, 'Cannot like missing post');
check(like($postBob) === true, 'Can like visible post');
check(like($postBob) === false, 'Duplicate like is rejected');
check(addComment(999999, 'ghost') === false, 'Cannot comment on missing post');
$commentId = addComment($postBob, 'nice post');
check(is_int($commentId) && $commentId > 0, 'Can comment on visible post');
check(addComment($postBob, str_repeat('x', 2001)) === false, 'Oversized comments are rejected');
check(updateOwnComment($commentId, 'edited comment') === true, 'Comment owner can edit comment');
check(updateOwnComment($commentId, 'edited comment') === true, 'Editing comment to identical text remains idempotent');
check(deletePost($postBob) === false, 'User cannot delete another user post');

check(blockUser($bob) === true, 'User can block active account');
check(checkFollowStatus($bob) === 0, 'Blocking removes follow relationship');
check(sendMessage($bob, 'blocked?') === false, 'Blocked users cannot message each other');
check(searchUser('bob') === [], 'Blocked users are hidden from search');
check(followUser($bob) === false, 'Blocked users cannot be followed');
check(unblockUser($bob) === true, 'User can unblock previously blocked account');
check(unblockUser($bob) === false, 'Unblock reports false when no relation exists');

check(followUser($bob) === true, 'Follow can be restored after unblock');
check(reportPost($postBob) === true, 'Another user can report a post');
check(reportPost($postBob) === false, 'Duplicate report does not report success');
$row = getPostRecord($postBob);
check((int)$row['is_reported'] === 1 && (int)$row['is_approved'] === 1, 'Reporting flags post without letting one user censor it');

$stmt = $db->prepare("INSERT INTO posts(user_id,post_img,post_text,is_reported,is_approved) VALUES(?,'mine.jpg','mine',0,1)");
$stmt->bind_param('i', $alice); $stmt->execute(); $postAlice=(int)$db->insert_id; $stmt->close();
check(reportPost($postAlice) === false, 'User cannot report own post');
check(updateOwnPost($postAlice, 'mine updated') === true, 'Post owner can edit own post');
check(updateOwnPost($postBob, 'hijack') === false, 'User cannot edit another user post');
check(deleteOwnComment($commentId) === true, 'Comment owner can delete own comment');
check(deleteOwnComment($commentId) === false, 'Deleting an already deleted comment reports false');

$adminId = createTestUser('admin@example.com', 'adminuser');
activateUser($adminId);
$db->query("UPDATE users SET role='Admin' WHERE id=" . (int)$adminId);

$adminLogin = checkUser(['username_email' => 'admin@example.com', 'password' => 'Password123!']);
check($adminLogin['status'] === true && $adminLogin['role'] === 'Admin', 'Unified login authenticates an Admin account from the users table');
loginAsUser($adminId);
check(validateUserSession(false)['ok'] === true, 'Admin account can use normal social features with the same session');
check(validateAdminSession()['ok'] === true, 'Same authenticated session grants Admin access when role is Admin');

$db->query("UPDATE users SET role='User' WHERE id=" . (int)$adminId);
$adminValidation = validateAdminSession();
check($adminValidation['ok'] === false && $adminValidation['reason'] === 'forbidden', 'Admin permission is revoked immediately after role is removed');
check(validateUserSession(false)['ok'] === true, 'Demoted Admin stays logged in as a normal User');

$db->query("UPDATE users SET role='Admin', ac_status=1 WHERE id=" . (int)$adminId);
loginAsUser($adminId);
check(validateAdminSession()['ok'] === true, 'Promoted active account immediately gains Admin access');

$charlie = createTestUser('charlie@example.com', 'charlie');
$db->query('UPDATE users SET ac_status=2 WHERE id=' . (int)$charlie);
check(verifyEmail('charlie@example.com') === false, 'Email verification cannot silently unblock an admin-blocked user');
check(unblockUserByAdmin($charlie) === true, 'Admin can explicitly unblock a blocked user');
check(blockUserByAdmin($charlie) === true, 'Admin can explicitly block an active user');
check(blockUserByAdmin($charlie) === false, 'Repeated admin block reports false');
check(updateUserRoleByAdmin($charlie, 'Admin') === false, 'Blocked users cannot be promoted to Admin');
check(verifyEmail('charlie@example.com') === false, 'Admin-blocked account cannot be activated through email verification');
check(unblockUserByAdmin($charlie) === true, 'Admin can unblock account before role promotion');
check(updateUserRoleByAdmin($charlie, 'Admin') === true, 'Active user can be promoted to Admin');
check(updateUserRoleByAdmin($charlie, 'User') === true, 'Admin can demote another admin back to User');

$db->query("UPDATE users SET ac_status=1, role='User' WHERE id=" . (int)$charlie);
$stmt = $db->prepare("INSERT INTO posts(user_id,post_img,post_text,is_reported,is_approved) VALUES(?,'charlie.jpg','cascade',0,1)");
$stmt->bind_param('i', $charlie); $stmt->execute(); $postCharlie=(int)$db->insert_id; $stmt->close();
$stmt = $db->prepare('INSERT INTO likes(post_id,user_id) VALUES(?,?)');
$stmt->bind_param('ii', $postCharlie, $alice); $stmt->execute(); $stmt->close();
$stmt = $db->prepare('INSERT INTO comments(post_id,user_id,comment) VALUES(?,?,?)');
$txt='cascade comment'; $stmt->bind_param('iis', $postCharlie, $alice, $txt); $stmt->execute(); $stmt->close();
check(deleteUserByAdmin($charlie) === true, 'Admin can delete normal user');
check(getUser($charlie) === null, 'Deleted user no longer exists');
$count = (int)$db->query('SELECT COUNT(*) AS c FROM posts WHERE user_id='.(int)$charlie)->fetch_assoc()['c'];
check($count === 0, 'Deleting user cascades their posts');
$count = (int)$db->query('SELECT COUNT(*) AS c FROM likes WHERE post_id='.(int)$postCharlie)->fetch_assoc()['c'];
check($count === 0, 'Deleting user cascades post likes');
$count = (int)$db->query('SELECT COUNT(*) AS c FROM comments WHERE post_id='.(int)$postCharlie)->fetch_assoc()['c'];
check($count === 0, 'Deleting user cascades post comments');

loginAsUser($bob);
$db->query('UPDATE users SET ac_status=2 WHERE id=' . (int)$bob);
$validation = validateUserSession(false);
check($validation['ok'] === false && $validation['reason'] === 'blocked', 'Blocked session is rejected by backend guard');
$db->query('UPDATE users SET ac_status=1 WHERE id=' . (int)$bob);
loginAsUser($bob);
$db->query('DELETE FROM users WHERE id=' . (int)$bob);
$validation = validateUserSession(false);
check($validation['ok'] === false && $validation['reason'] === 'invalid_session', 'Deleted account session is rejected by backend guard');

if ($failures > 0) {
    fwrite(STDERR, "\n{$failures} of {$assertions} assertions failed.\n");
    exit(1);
}

echo "\nAll {$assertions} backend integration assertions passed.\n";
