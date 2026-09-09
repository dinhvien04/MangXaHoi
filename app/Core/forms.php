<?php

function showError($field)
{
    if (!isset($_SESSION['error'])) {
        return;
    }

    $error = $_SESSION['error'];
    if (isset($error['field']) && $field === $error['field']) {
        $msg = e($error['msg'] ?? '');
        echo '<div class="alert alert-danger my-2" role="alert">' . $msg . '</div>';
    }
}

function showFormData($field)
{
    if (isset($_SESSION['formdata'][$field])) {
        return e($_SESSION['formdata'][$field]);
    }
    return '';
}
