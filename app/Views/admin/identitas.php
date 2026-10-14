<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Identitas Sekolah</title>
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
    <script src="/assets/js/admin.js?v=<?= (int) @filemtime(FCPATH . 'assets/js/admin.js') ?>" defer></script>
</head>
<body class="dash">
    <div class="dash-shell" data-dash-shell>
        <?= view("admin/_sidebar", ["active" => "identitas", "username" => $username ?? null, "school" => $school ?? []]) ?>

        <div class="dash-main">
            <?= view("admin/_topbar", ["username" => $username ?? null]) ?>

            <main class="dash-page">
                <div class="dash-page-head">
                    <div>
                        <h1>Identitas Sekolah</h1>
                        <p>Selamat datang, <?= esc($username ?? "admin") ?></p>
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
                        <h2>Profil Institusi</h2>
                    </div>
                    <div class="panel-body">
                        <form method="post" action="/admin/identitas">
                            <?= csrf_field() ?>
                            <div class="form-grid">
                                <div class="field">
                                    <label for="npsn">NPSN</label>
                                    <input id="npsn" name="npsn" type="text" value="<?= esc($school['npsn'] ?? '') ?>" placeholder="Contoh: 12345678">
                                </div>

                                <div class="field">
                                    <label for="nm_sekolah">Nama Sekolah</label>
                                    <input id="nm_sekolah" name="nm_sekolah" type="text" value="<?= esc($school['nm_sekolah'] ?? '') ?>" placeholder="Nama sekolah" required>
                                </div>

                                <div class="field full-width">
                                    <label for="jln">Alamat Jalan</label>
                                    <input id="jln" name="jln" type="text" value="<?= esc($school['jln'] ?? '') ?>" placeholder="Masukkan alamat jalan">
                                </div>

                                <div class="field">
                                    <label for="desa">Desa / Kelurahan</label>
                                    <input id="desa" name="desa" type="text" value="<?= esc($school['desa'] ?? '') ?>" placeholder="Desa / kelurahan">
                                </div>

                                <div class="field">
                                    <label for="kec">Kecamatan</label>
                                    <input id="kec" name="kec" type="text" value="<?= esc($school['kec'] ?? '') ?>" placeholder="Kecamatan">
                                </div>

                                <div class="field">
                                    <label for="kab">Kabupaten / Kota</label>
                                    <input id="kab" name="kab" type="text" value="<?= esc($school['kab'] ?? '') ?>" placeholder="Kabupaten atau kota">
                                </div>

                                <div class="field">
                                    <label for="kpl_sekolah">Nama Kepala Sekolah</label>
                                    <input id="kpl_sekolah" name="kpl_sekolah" type="text" value="<?= esc($school['kpl_sekolah'] ?? '') ?>" placeholder="Nama kepala sekolah">
                                </div>

                                <div class="field">
                                    <label for="nip">NIP</label>
                                    <input id="nip" name="nip" type="text" value="<?= esc($school['nip'] ?? '') ?>" placeholder="NIP kepala sekolah">
                                </div>
                            </div>

                            <div class="form-actions">
                                <button class="btn btn-primary" type="submit">Simpan Perubahan</button>
                                <a class="btn btn-secondary" href="/admin">Kembali ke Dashboard</a>
                            </div>
                        </form>
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
