<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan</title>
    <link rel="icon" href="<?= esc(!empty($school["favicon"] ?? "") ? "/uploads/sekolah/" . $school["favicon"] : "/logo.png") ?>">
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
    <script src="/assets/js/admin.js?v=<?= (int) @filemtime(FCPATH . 'assets/js/admin.js') ?>" defer></script>
</head>
<body class="dash">
    <?php
    $avatarBerkas = (string) ($avatar ?? "");
    if (! preg_match('/^[A-Za-z0-9._-]+$/', $avatarBerkas)) {
        $avatarBerkas = "";
    }
    $avatarAda = $avatarBerkas !== "" && is_file(FCPATH . "uploads/avatar/" . $avatarBerkas);
    $logoBerkas = is_array($school ?? null) ? (string) ($school["logo"] ?? "") : "";
    if (! preg_match('/^[A-Za-z0-9._-]+$/', $logoBerkas)) {
        $logoBerkas = "";
    }
    $logoAda = $logoBerkas !== "" && is_file(FCPATH . "uploads/sekolah/" . $logoBerkas);
    $ikonBerkas = is_array($school ?? null) ? (string) ($school["favicon"] ?? "") : "";
    if (! preg_match('/^[A-Za-z0-9._-]+$/', $ikonBerkas)) {
        $ikonBerkas = "";
    }
    $ikonAda = $ikonBerkas !== "" && is_file(FCPATH . "uploads/sekolah/" . $ikonBerkas);
    ?>
    <div class="dash-shell" data-dash-shell>
        <?= view("admin/_sidebar", ["active" => "pengaturan", "username" => $username ?? null, "school" => $school ?? []]) ?>

        <div class="dash-main">
            <?= view("admin/_topbar", ["username" => $username ?? null]) ?>

            <main class="dash-page">
                <div class="dash-page-head">
                    <div>
                        <h1>Pengaturan</h1>
                        <p>Kelola avatar admin, password, serta logo dan ikon aplikasi.</p>
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
                        <h2>Avatar Admin</h2>
                    </div>
                    <div class="panel-body">
                        <div class="setting-row">
                            <?php if ($avatarAda): ?>
                                <img class="setting-thumb" src="/uploads/avatar/<?= esc($avatarBerkas) ?>" alt="Avatar <?= esc($username ?? 'admin') ?>">
                            <?php endif; ?>
                            <form method="post" action="/admin/pengaturan/avatar" enctype="multipart/form-data" class="inline-form">
                                <?= csrf_field() ?>
                                <div class="field">
                                    <label for="avatar">Foto Avatar</label>
                                    <input type="file" id="avatar" name="avatar" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif" required>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan Avatar</button>
                            </form>
                        </div>
                        <p class="muted">JPG, PNG, atau GIF maksimal 1 MB.</p>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-header">
                        <h2>Ganti Password</h2>
                    </div>
                    <div class="panel-body">
                        <form method="post" action="/admin/pengaturan/password" class="login-form">
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

                <section class="panel">
                    <div class="panel-header">
                        <h2>Logo dan Ikon Aplikasi</h2>
                    </div>
                    <div class="panel-body">
                        <div class="setting-row">
                            <?php if ($logoAda): ?>
                                <img class="setting-thumb" src="/uploads/sekolah/<?= esc($logoBerkas) ?>" alt="Logo sekolah">
                            <?php endif; ?>
                            <?php if ($ikonAda): ?>
                                <img class="setting-thumb" src="/uploads/sekolah/<?= esc($ikonBerkas) ?>" alt="Ikon aplikasi">
                            <?php endif; ?>
                            <form method="post" action="/admin/pengaturan/logo" enctype="multipart/form-data" class="inline-form">
                                <?= csrf_field() ?>
                                <div class="field">
                                    <label for="logo">Logo Sekolah</label>
                                    <input type="file" id="logo" name="logo" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif" data-image-input>
                                    <small class="field-error" data-file-error hidden></small>
                                </div>
                                <div class="field">
                                    <label for="favicon">Ikon (Favicon)</label>
                                    <input type="file" id="favicon" name="favicon" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif" data-image-input>
                                    <small class="field-error" data-file-error hidden></small>
                                </div>
                                <button type="submit" class="btn btn-primary">Simpan Logo</button>
                            </form>
                        </div>
                        <p class="muted">Isi salah satu atau keduanya. JPG, PNG, atau GIF maksimal 1 MB. Tanpa unggahan baru, logo bawaan <code>/logo.png</code> dipakai.</p>
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
