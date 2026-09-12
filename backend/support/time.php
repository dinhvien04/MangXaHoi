<?php

function gettime($date)
{
    $weekdays = [
        'Monday' => 'Thứ Hai',
        'Tuesday' => 'Thứ Ba',
        'Wednesday' => 'Thứ Tư',
        'Thursday' => 'Thứ Năm',
        'Friday' => 'Thứ Sáu',
        'Saturday' => 'Thứ Bảy',
        'Sunday' => 'Chủ Nhật',
    ];

    $timestamp = strtotime($date);
    $weekday = $weekdays[date('l', $timestamp)] ?? '';
    return date('H:i', $timestamp) . " - $weekday, " . date('d/m/Y', $timestamp);
}

function show_time($time)
{
    $formatted = gettime($time);
    return '<time style="font-size:small" class="timeago text-muted text-small" datetime="' . htmlspecialchars((string) $time, ENT_QUOTES, 'UTF-8') . '">' . htmlspecialchars((string) $formatted, ENT_QUOTES, 'UTF-8') . '</time>';
}
