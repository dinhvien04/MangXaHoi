<?php

const HANDBOOK_OTP_TTL = 300;
const HANDBOOK_OTP_MAX_ATTEMPTS = 5;
const HANDBOOK_OTP_RESEND_COOLDOWN = 60;

function csrfToken()
{
    if (empty($_SESSION['_csrf']) || !is_string($_SESSION['_csrf'])) {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['_csrf'];
}

function csrfField()
{
    return '<input type="hidden" name="csrf_token" value="' . e(csrfToken()) . '">';
}

function submittedCsrfToken()
{
    $header = (string) ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($header !== '') {
        return $header;
    }
    return (string) ($_POST['csrf_token'] ?? '');
}

function isValidCsrfToken($token = null)
{
    $expected = (string) ($_SESSION['_csrf'] ?? '');
    $token = $token === null ? submittedCsrfToken() : (string) $token;
    return $expected !== '' && $token !== '' && hash_equals($expected, $token);
}

function requireCsrf()
{
    if (!isValidCsrfToken()) {
        http_response_code(419);
        if (isset($_GET['api'])) {
            jsonResponse(['status' => false, 'message' => 'CSRF token không hợp lệ.'], 419);
        }
        exit('CSRF token không hợp lệ. Vui lòng tải lại trang và thử lại.');
    }
}

function requirePostRequest()
{
    if (strtoupper((string) ($_SERVER['REQUEST_METHOD'] ?? 'GET')) !== 'POST') {
        header('Allow: POST');
        http_response_code(405);
        if (isset($_GET['api'])) {
            jsonResponse(['status' => false, 'message' => 'Chỉ chấp nhận POST.'], 405);
        }
        exit('Chỉ chấp nhận POST.');
    }
}

function clientIp()
{
    return (string) ($_SERVER['REMOTE_ADDR'] ?? 'unknown');
}

function sessionRateLimit($key, $maxAttempts, $windowSeconds)
{
    $now = time();
    $_SESSION['_rate_limits'] = $_SESSION['_rate_limits'] ?? [];
    $bucket = $_SESSION['_rate_limits'][$key] ?? ['count' => 0, 'started_at' => $now];
    if ($now - (int) $bucket['started_at'] >= $windowSeconds) {
        $bucket = ['count' => 0, 'started_at' => $now];
    }
    if ((int) $bucket['count'] >= $maxAttempts) {
        $_SESSION['_rate_limits'][$key] = $bucket;
        return false;
    }
    $bucket['count']++;
    $_SESSION['_rate_limits'][$key] = $bucket;
    return true;
}

function consumeRateLimit($scope, $subject, $maxAttempts, $windowSeconds)
{
    global $db;
    $rateKey = hash('sha256', (string) $scope . '|' . (string) $subject);
    $maxAttempts = max(1, (int) $maxAttempts);
    $windowSeconds = max(1, (int) $windowSeconds);
    $now = time();

    try {
        $db->begin_transaction();
        $stmt = $db->prepare('SELECT attempts, window_started_at FROM rate_limits WHERE rate_key = ? FOR UPDATE');
        $stmt->bind_param('s', $rateKey);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        if (!$row) {
            $stmt = $db->prepare('INSERT INTO rate_limits (rate_key, attempts, window_started_at) VALUES (?, 1, FROM_UNIXTIME(?))');
            $stmt->bind_param('si', $rateKey, $now);
            $stmt->execute();
            $stmt->close();
            $db->commit();
            return true;
        }

        $startedAt = strtotime((string) $row['window_started_at']) ?: $now;
        if ($now - $startedAt >= $windowSeconds) {
            $stmt = $db->prepare('UPDATE rate_limits SET attempts = 1, window_started_at = FROM_UNIXTIME(?) WHERE rate_key = ?');
            $stmt->bind_param('is', $now, $rateKey);
            $stmt->execute();
            $stmt->close();
            $db->commit();
            return true;
        }

        if ((int) $row['attempts'] >= $maxAttempts) {
            $db->commit();
            return false;
        }

        $stmt = $db->prepare('UPDATE rate_limits SET attempts = attempts + 1 WHERE rate_key = ?');
        $stmt->bind_param('s', $rateKey);
        $stmt->execute();
        $stmt->close();
        $db->commit();
        return true;
    } catch (Throwable $e) {
        try {
            if ($db->errno === 0 || $db->connect_errno === 0) {
                $db->rollback();
            }
        } catch (Throwable $ignored) {
        }
        error_log('Handbook rate limit fallback: ' . $e->getMessage());
        return sessionRateLimit($rateKey, $maxAttempts, $windowSeconds);
    }
}

function buildOtpState($email, $code, $purpose)
{
    return [
        'email' => strtolower(trim((string) $email)),
        'purpose' => (string) $purpose,
        'code_hash' => hash('sha256', (string) $code),
        'expires_at' => time() + HANDBOOK_OTP_TTL,
        'attempts' => 0,
        'last_sent_at' => time(),
    ];
}

function verifyOtpState(array &$state, $submittedCode)
{
    $submittedCode = trim((string) $submittedCode);
    if (!preg_match('/^\d{6}$/', $submittedCode)) {
        return ['ok' => false, 'reason' => 'format'];
    }
    if ((int) ($state['expires_at'] ?? 0) < time()) {
        return ['ok' => false, 'reason' => 'expired'];
    }
    if ((int) ($state['attempts'] ?? 0) >= HANDBOOK_OTP_MAX_ATTEMPTS) {
        return ['ok' => false, 'reason' => 'locked'];
    }

    $state['attempts'] = (int) ($state['attempts'] ?? 0) + 1;
    $expected = (string) ($state['code_hash'] ?? '');
    if ($expected !== '' && hash_equals($expected, hash('sha256', $submittedCode))) {
        return ['ok' => true, 'reason' => null];
    }
    return ['ok' => false, 'reason' => 'invalid'];
}

function canResendOtp(array $state)
{
    return time() - (int) ($state['last_sent_at'] ?? 0) >= HANDBOOK_OTP_RESEND_COOLDOWN;
}

function otpErrorMessage($reason)
{
    $messages = [
        'format' => 'Nhập mã gồm đúng 6 chữ số.',
        'expired' => 'Mã xác minh đã hết hạn. Vui lòng gửi lại mã mới.',
        'locked' => 'Bạn đã nhập sai quá nhiều lần. Vui lòng gửi lại mã mới.',
        'invalid' => 'Mã xác minh không chính xác.',
    ];
    return $messages[$reason] ?? 'Không thể xác minh mã.';
}
