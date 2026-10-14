<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password</title>
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
    <script src="/assets/js/admin.js?v=<?= (int) @filemtime(FCPATH . 'assets/js/admin.js') ?>" defer></script>
</head>
<body class="dash">
    <div class="dash-shell" data-dash-shell>
        <?= view("admin/_sidebar", ["active" => "password", "username" => $username ?? null, "school" => $school ?? []]) ?>

        <div class="dash-main">
            <?= view("admin/_topbar", ["username" => $username ?? null]) ?>

            <main class="dash-page">
                <div class="dash-page-head">
                    <div>
                        <h1>Ganti Password</h1>
                        <p>Perbarui password akun administrator yang sedang login.</p>
                    </div>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert"><?= esc($success) ?></div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error" role="alert"><?= esc($error) ?></div>
                <?php endif; ?>

                <section class="panel">
                    <div class="panel-header">
                        <h2>Password Baru</h2>
                    </div>
                    <div class="panel-body">
                        <form method="post" action="/admin/password" class="login-form">
                            <?= csrf_field() ?>
                            <div class="field">
                                <label for="password_lama">Password Lama</label>
                                <div class="password-input-wrap">
                                    <input type="password" id="password_lama" name="password_lama" placeholder="Masukkan password saat ini" autocomplete="current-password" required>
                                </div>
                            </div>

                            <div class="field">
                                <label for="password_baru">Password Baru</label>
                                <div class="password-input-wrap">
                                    <input type="password" id="password_baru" name="password_baru" minlength="4" placeholder="Minimal 4 karakter" autocomplete="new-password" required>
                                </div>
                            </div>

                            <div class="field">
                                <label for="password_konfirmasi">Konfirmasi Password Baru</label>
                                <div class="password-input-wrap">
                                    <input type="password" id="password_konfirmasi" name="password_konfirmasi" minlength="4" placeholder="Ulangi password baru" autocomplete="new-password" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary">Simpan Password</button>
                        </form>
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
