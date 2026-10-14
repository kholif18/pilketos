<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan</title>
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
    <script src="/assets/js/admin.js?v=<?= (int) @filemtime(FCPATH . 'assets/js/admin.js') ?>" defer></script>
</head>
<body class="dash">
    <div class="dash-shell" data-dash-shell>
        <?= view("admin/_sidebar", ["active" => "laporan", "username" => $username ?? null, "school" => $school ?? []]) ?>

        <div class="dash-main">
            <?= view("admin/_topbar", ["username" => $username ?? null]) ?>

            <main class="dash-page">
                <div class="dash-page-head">
                    <div>
                        <h1>Laporan</h1>
                        <p>Ringkasan pelaksanaan pemilihan</p>
                    </div>
                    <div class="dash-page-actions">
                        <a class="btn btn-primary" href="/admin/laporan/pdf">Export PDF</a>
                    </div>
                </div>

                <section class="panel">
                    <div class="panel-header">
                        <h2>Rekap Pemilihan</h2>
                    </div>
                    <div class="panel-body">
                        <div class="dash-stats">
                            <div class="dash-stat">
                                <span class="dash-stat-icon is-blue" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/></svg>
                                </span>
                                <span class="dash-stat-label">DPT</span>
                                <strong class="dash-stat-value"><?= esc($totalPemilih ?? 0) ?></strong>
                            </div>

                            <div class="dash-stat">
                                <span class="dash-stat-icon is-green" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                                </span>
                                <span class="dash-stat-label">Sudah Vote</span>
                                <strong class="dash-stat-value"><?= esc($totalSudahVote ?? 0) ?></strong>
                            </div>

                            <div class="dash-stat">
                                <span class="dash-stat-icon is-amber" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                </span>
                                <span class="dash-stat-label">Belum Vote</span>
                                <strong class="dash-stat-value"><?= esc($totalBelumVote ?? 0) ?></strong>
                            </div>

                            <div class="dash-stat">
                                <span class="dash-stat-icon is-purple" aria-hidden="true">
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                                </span>
                                <span class="dash-stat-label">Calon</span>
                                <strong class="dash-stat-value"><?= esc($totalCalon ?? 0) ?></strong>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-header">
                        <h2>Hasil Perolehan Suara</h2>
                    </div>
                    <div class="panel-body">
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th width="60">No</th>
                                        <th>Nama Calon</th>
                                        <th>Jumlah Suara</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($hasilVote)): ?>
                                        <tr>
                                            <td colspan="3" class="empty-state">Belum ada laporan suara yang tersedia.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($hasilVote as $item): ?>
                                            <tr>
                                                <td><?= esc($item['no'] ?? '-') ?></td>
                                                <td><?= esc($item['nama'] ?? '-') ?></td>
                                                <td><?= esc($item['jumlah'] ?? 0) ?></td>
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
