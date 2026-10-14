<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Koneksi API Sekolah</title>
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
    <script src="/assets/js/admin.js?v=<?= (int) @filemtime(FCPATH . 'assets/js/admin.js') ?>" defer></script>
</head>
<body class="dash">
    <div class="dash-shell" data-dash-shell>
        <?= view("admin/_sidebar", ["active" => "connect-api", "username" => $username ?? null, "school" => $school ?? []]) ?>

        <div class="dash-main">
            <?= view("admin/_topbar", ["username" => $username ?? null]) ?>

            <main class="dash-page">
                <div class="dash-page-head">
                    <div>
                        <h1>Koneksi API Sekolah</h1>
                        <p>Sinkron data siswa, guru, dan kelas dari website sekolah</p>
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
                        <h2>Konfigurasi Koneksi</h2>
                    </div>
                    <div class="panel-body">
                        <div class="api-grid">
                            <div class="api-panel">
                                <div class="panel-body">
                                    <form method="post" action="/admin/connect-api">
                                        <?= csrf_field() ?>
                                        <div class="form-grid">
                                            <div class="field full-width">
                                                <label for="app_name">Nama Aplikasi</label>
                                                <input type="text" id="app_name" name="app_name" value="<?= esc($config['app_name'] ?? '') ?>" placeholder="Pilketos Admin" required>
                                            </div>

                                            <div class="field full-width">
                                                <label for="api_url">URL API Sekolah</label>
                                                <input type="url" id="api_url" name="api_url" value="<?= esc($config['api_url'] ?? '') ?>" placeholder="https://sekolah.local" required>
                                            </div>

                                            <div class="field full-width">
                                                <label for="api_token">Token API</label>
                                                <input type="text" id="api_token" name="api_token" value="<?= esc($config['api_token'] ?? '') ?>" placeholder="masukkan token aplikasi">
                                            </div>

                                            <div class="field">
                                                <label for="timeout">Timeout (detik)</label>
                                                <input type="number" id="timeout" name="timeout" min="5" max="60" value="<?= esc($config['timeout'] ?? 10) ?>">
                                            </div>
                                        </div>

                                        <div class="form-actions">
                                            <button type="submit" class="btn btn-primary">Simpan Konfigurasi</button>
                                            <button type="submit" class="btn btn-primary" formaction="/admin/connect-api/test">Test Koneksi</button>
                                            <button type="submit" class="btn btn-primary" formaction="/admin/connect-api/sync">Sync Data Sekolah</button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <div class="api-panel">
                                <div class="panel-body">
                                    <div class="status-box">
                                        <strong>Status Koneksi</strong>
                                        <div><?= esc($config['last_status'] ?? 'Belum dikonfigurasi') ?></div>
                                        <?php if (!empty($config['last_sync'])): ?>
                                            <div class="small-note">Terakhir diperbarui: <?= esc($config['last_sync']) ?></div>
                                        <?php endif; ?>
                                    </div>

                                    <div class="small-note">
                                        Endpoint yang akan digunakan nanti:<br>
                                        • /api/connect/health<br>
                                        • /api/connect/kelas<br>
                                        • /api/connect/siswa<br>
                                        • /api/connect/guru<br>
                                        • /api/connect/sync
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-header">
                        <h2>Riwayat Sinkronisasi</h2>
                    </div>
                    <div class="panel-body">
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th width="170">Waktu</th>
                                        <th width="110">Sumber</th>
                                        <th width="90">Berhasil</th>
                                        <th width="90">Gagal</th>
                                        <th>Keterangan</th>
                                        <th width="120">Oleh</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($riwayat)): ?>
                                        <tr>
                                            <td colspan="6" class="empty-state">Belum ada riwayat sinkronisasi.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($riwayat as $item): ?>
                                            <tr>
                                                <td><?= esc($item['waktu'] ?? '-') ?></td>
                                                <td><?= esc($item['sumber'] ?? '-') ?></td>
                                                <td><?= esc($item['jumlah'] ?? 0) ?></td>
                                                <td><?= esc($item['gagal'] ?? 0) ?></td>
                                                <td><?= esc($item['pesan'] ?? '-') ?></td>
                                                <td><?= esc($item['oleh'] ?? '-') ?></td>
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
