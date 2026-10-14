<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Siswa</title>
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
    <script src="/assets/js/admin.js?v=<?= (int) @filemtime(FCPATH . 'assets/js/admin.js') ?>" defer></script>
</head>
<body class="dash">
    <div class="dash-shell" data-dash-shell>
        <?= view("admin/_sidebar", ["active" => "siswa", "username" => $username ?? null, "school" => $school ?? []]) ?>

        <div class="dash-main">
            <?= view("admin/_topbar", ["username" => $username ?? null]) ?>

            <main class="dash-page">
                <div class="dash-page-head">
                    <div>
                        <h1>Data Siswa</h1>
                        <p>Kelola daftar pemilih dan kelas</p>
                    </div>
                </div>

                <?php if (!empty($success)): ?>
                    <div class="alert alert-success" role="alert"><?= esc($success) ?></div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-error" role="alert"><?= esc($error) ?></div>
                <?php endif; ?>

                <div class="dash-duo">
                <section class="panel">
                    <div class="panel-header">
                        <h2>Tambah / Ubah Siswa</h2>
                    </div>
                    <div class="panel-body">
                        <form method="post" action="/admin/siswa" class="inline-form">
                            <?= csrf_field() ?>
                            <div class="field">
                                <label for="username">Username / NIS</label>
                                <input type="text" id="username" name="username" inputmode="numeric" maxlength="4" pattern="\d{4}" placeholder="Contoh: 1234" required>
                            </div>

                            <div class="field">
                                <label for="password">Password</label>
                                <div class="password-input-wrap">
                                    <input type="password" id="password" name="password" placeholder="Kosongkan untuk default NIS" autocomplete="new-password">
                                </div>
                            </div>

                            <div class="field">
                                <label for="nm_siswa">Nama Siswa</label>
                                <input type="text" id="nm_siswa" name="nm_siswa" placeholder="Nama lengkap" required>
                            </div>

                            <div class="field">
                                <label for="jk">Jenis Kelamin</label>
                                <select id="jk" name="jk">
                                    <option value="L">Laki-laki</option>
                                    <option value="P">Perempuan</option>
                                </select>
                            </div>

                            <div class="field">
                                <label for="kd_kelas">Kelas</label>
                                <select id="kd_kelas" name="kd_kelas">
                                    <option value="">-- Pilih kelas --</option>
                                    <?php foreach ($kelas ?? [] as $item): ?>
                                        <option value="<?= esc($item['kd_kelas'] ?? '') ?>"><?= esc($item['nm_kelas'] ?? '-') ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary btn-block">Simpan Data</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-header">
                        <h2>Impor CSV</h2>
                    </div>
                    <div class="panel-body">
                        <p class="muted">Format kolom: NIS, Nama, JK (L/P), Kelas. Baris pertama boleh berupa header. <a href="/admin/siswa/template">Unduh template</a>.</p>
                        <form method="post" action="/admin/siswa/import" enctype="multipart/form-data" class="inline-form">
                            <?= csrf_field() ?>
                            <div class="field">
                                <label for="file_csv">Berkas CSV</label>
                                <input type="file" id="file_csv" name="file_csv" accept=".csv,.txt,text/csv,text/plain" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Impor Data</button>
                        </form>
                    </div>
                </section>
                </div>

                <section class="panel">
                    <div class="panel-header dash-list-head">
                        <h2>Daftar Siswa</h2>
                        <form method="post" action="/admin/siswa/sync">
                            <?= csrf_field() ?>
                            <button type="submit" class="btn btn-primary">Sync dari API Sekolah</button>
                        </form>
                    </div>
                    <div class="panel-body">
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th width="60">No</th>
                                        <th>Username / NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>JK</th>
                                        <th>Kelas</th>
                                        <th>Hadir</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($siswa)): ?>
                                        <tr>
                                            <td colspan="7" class="empty-state">Belum ada data siswa. Silakan tambah atau sinkronkan data dari API sekolah.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($siswa as $index => $item): ?>
                                            <tr>
                                                <td><?= esc($index + 1) ?></td>
                                                <td><?= esc($item['username'] ?? '-') ?></td>
                                                <td><?= esc($item['nm_siswa'] ?? '-') ?></td>
                                                <td><?= esc($item['jk'] ?? '-') ?></td>
                                                <td><?= esc($item['nm_kelas'] ?? '-') ?></td>
                                                <td><?= esc($item['hadir'] ?? 'Tidak Hadir') ?></td>
                                                <td>
                                                    <form method="post" action="/admin/siswa/delete" class="row-delete">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="username" value="<?= esc($item['username'] ?? '') ?>">
                                                        <button type="submit" class="btn btn-danger" data-confirm="Apakah Anda yakin ingin menghapus data siswa ini?">Hapus</button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
