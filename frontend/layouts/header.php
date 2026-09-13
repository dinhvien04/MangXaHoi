<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <meta name="csrf-token" content="<?= e(csrfToken()) ?>">
    <meta name="referrer" content="same-origin">
    <meta name="theme-color" content="#1467ff">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
    <link href="public/css/novera.css" rel="stylesheet">
    <link rel="icon" type="image/svg+xml" href="public/images/novera-mark.svg">
    <script defer src="public/js/novera-ui.js"></script>
    <title><?= e($pageTitle ?? 'Novera Social') ?></title>
</head>
<body class="hb-body">
