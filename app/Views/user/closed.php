<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemilihan Belum Dibuka</title>
    <link rel="icon" href="/logo.png">
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
</head>
<body class="voter-page">
    <div class="voter-shell thank-you-shell">
        <div class="success-card">
            <div class="closed-icon" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
            </div>
            <h1>Pemilihan Belum Dibuka</h1>
            <?php if (($alasan ?? '') === 'tanggal'): ?>
                <p>Pemilihan dilaksanakan pada <?= esc($tgl) ?>. Hari ini di luar jadwal.</p>
            <?php else: ?>
                <p>Jadwal pemilihan belum ditetapkan admin. Silakan kembali lagi nanti.</p>
            <?php endif; ?>
            <p class="small">Pemilih: <strong><?= esc($username) ?></strong></p>
            <a href="/user/logout" class="secondary-btn">Keluar</a>
        </div>
    </div>
</body>
</html>
