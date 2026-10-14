<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Terima Kasih</title>
    <link rel="icon" href="/logo.png">
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
</head>
<body class="voter-page">
    <div class="voter-shell thank-you-shell">
        <div class="success-card">
            <div class="success-icon">✓</div>
            <h1>Terima Kasih</h1>
            <p>Suara Anda telah berhasil tercatat.</p>
            <p class="small">Pemilih: <strong><?= esc($username) ?></strong></p>
            <?php if (! empty($success)): ?>
                <div class="alert success"><?= esc($success) ?></div>
            <?php endif; ?>
            <a href="/user/logout" class="primary-btn">Keluar</a>
        </div>
    </div>
</body>
</html>
