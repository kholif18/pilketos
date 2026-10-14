<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pilihan Calon</title>
    <link rel="icon" href="/logo.png">
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
</head>
<body class="voter-page">
    <div class="voter-shell">
        <header class="voter-topbar">
            <div>
                <span class="eyebrow">Pemilihan</span>
                <h1>Pilihan Calon</h1>
            </div>
            <div class="user-chip">
                <span>Login sebagai</span>
                <strong><?= esc($username) ?></strong>
            </div>
        </header>

        <div class="info-strip">
            <p>Silakan pilih satu calon yang Anda anggap paling tepat untuk memimpin dan mewakili suara Anda.</p>
        </div>

        <?php if (! empty($error)): ?>
            <div class="alert error"><?= esc($error) ?></div>
        <?php endif; ?>

        <form method="post" action="/user/vote" class="voter-form">
            <?= csrf_field() ?>
            <div class="candidate-grid">
                <?php foreach ($candidates as $candidate): ?>
                    <label class="candidate-card">
                        <input type="radio" name="nisn" value="<?= esc($candidate['nisn']) ?>" required>
                        <span class="candidate-check" aria-hidden="true">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            Dipilih
                        </span>
                        <div class="candidate-photo">
                            <?php
                            $fotoVoter = (string) ($candidate['photo'] ?? '');
                            $fotoVoterOk = $fotoVoter !== '' && $fotoVoter !== 'default.jpg'
                                && preg_match('/^[A-Za-z0-9._-]+$/', $fotoVoter)
                                && is_file(FCPATH . 'uploads/calon/' . $fotoVoter);
                            ?>
                            <?php if ($fotoVoterOk): ?>
                                <img src="/uploads/calon/<?= esc($fotoVoter) ?>" alt="<?= esc($candidate['nama']) ?>">
                            <?php else: ?>
                                <div class="photo-placeholder"><?= esc(substr($candidate['nama'], 0, 1)) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="candidate-meta">
                            <span class="candidate-number">No <?= esc($candidate['no']) ?></span>
                            <h2><?= esc($candidate['nama']) ?></h2>
                            <small>Calon <?= esc($candidate['no']) ?></small>
                        </div>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="submit-area">
                <button type="submit" class="primary-btn">Simpan Suara</button>
                <a href="/user/logout" class="secondary-btn">Keluar</a>
            </div>
        </form>
    </div>
</body>
</html>
