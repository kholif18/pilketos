<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Pilketos</title>
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
    <script src="/assets/js/admin.js?v=<?= (int) @filemtime(FCPATH . 'assets/js/admin.js') ?>" defer></script>
</head>
<body class="login-page">
    <div class="login-wrap auth-login-wrap">
        <div class="brand-panel">
            <div class="brand-inner">
                <span class="brand-badge">Pilketos</span>
                <h1 class="brand-title">Selamat Datang</h1>
                <p class="brand-copy">
                    Mewujudkan pemilihan Ketua OSIS yang jujur, transparan,
                    dan demokratis. Satu siswa, satu suara untuk pemimpin
                    terbaik sekolah.
                </p>
                <div class="brand-pills">
                    <span class="pill">Admin</span>
                    <span class="pill">Pemilih</span>
                </div>
            </div>
        </div>

        <div class="form-panel">
            <div class="card auth-card">
                <div class="card-header">
                    <h1>Masuk</h1>
                    <p>Gunakan akun Anda untuk memilih atau mengelola.</p>
                </div>

                <?php if (! empty($error)): ?>
                    <div class="error" role="alert"><?= esc($error) ?></div>
                <?php endif; ?>

                <form method="post" action="/login" class="login-form">
                    <?= csrf_field() ?>
                    <div class="field">
                        <label for="username">Username</label>
                        <input id="username" type="text" name="username" placeholder="Masukkan username" autocomplete="username" required>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <div class="password-input-wrap">
                            <input id="password" type="password" name="password" placeholder="Masukkan password" autocomplete="current-password" required>
                        </div>
                    </div>

                    <button type="submit">Masuk</button>
                </form>

                <div class="footer-note">Akun pemilih memakai NIS sebagai username dan password.</div>
            </div>
        </div>
    </div>
</body>
</html>
