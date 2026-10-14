<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Hadir</title>
    <link rel="icon" href="<?= esc(!empty($school["favicon"] ?? "") ? "/uploads/sekolah/" . $school["favicon"] : "/logo.png") ?>">
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
    <script src="/assets/js/admin.js?v=<?= (int) @filemtime(FCPATH . 'assets/js/admin.js') ?>" defer></script>
</head>
<body class="dash">
    <div class="dash-shell" data-dash-shell>
        <?= view("admin/_sidebar", ["active" => "daftarhadir", "username" => $username ?? null, "school" => $school ?? []]) ?>

        <div class="dash-main">
            <?= view("admin/_topbar", ["username" => $username ?? null]) ?>

            <main class="dash-page">
                <div class="dash-page-head">
                    <div>
                        <h1>Daftar Hadir</h1>
                        <p>Rekap kehadiran siswa dalam pemilihan</p>
                    </div>
                    <div class="dash-page-actions">
                        <a class="btn btn-primary" href="/admin/daftarhadir/pdf">Export PDF</a>
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
                        <h2>Ringkasan Kehadiran</h2>
                    </div>
                    <div class="panel-body">
                        <div class="dash-stats dash-stats-two">
                            <div class="dash-stat">
                                <span class="dash-stat-icon is-green" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                <span class="dash-stat-label">Hadir</span>
                                <strong class="dash-stat-value"><?= esc($hadir ?? 0) ?></strong>
                            </div>

                            <div class="dash-stat">
                                <span class="dash-stat-icon is-red" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M5 12h14"/></svg>
                                </span>
                                <span class="dash-stat-label">Tidak Hadir</span>
                                <strong class="dash-stat-value"><?= esc($tidakHadir ?? 0) ?></strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-header">
                        <h2>Daftar Siswa</h2>
                    </div>
                    <div class="panel-body">
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th width="60">No</th>
                                        <th>NIS</th>
                                        <th>Nama Siswa</th>
                                        <th>Kelas</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($daftarHadir)): ?>
                                        <tr>
                                            <td colspan="5" class="empty-state">Belum ada data siswa. Silakan import atau isi data siswa terlebih dahulu.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($daftarHadir as $index => $item): ?>
                                            <tr>
                                                <td><?= esc($index + 1) ?></td>
                                                <td><?= esc($item['nis'] ?? '-') ?></td>
                                                <td><?= esc($item['nm_siswa'] ?? '-') ?></td>
                                                <td><?= esc($item['nm_kelas'] ?? '-') ?></td>
                                                <td>
                                                    <?php if (($item['hadir'] ?? '') === 'Hadir'): ?>
                                                        <span class="badge badge-success">Hadir</span>
                                                    <?php else: ?>
                                                        <span class="badge badge-warning">Tidak Hadir</span>
                                                    <?php endif; ?>
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
