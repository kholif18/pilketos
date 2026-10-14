<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Voting</title>
    <link rel="icon" href="<?= esc(!empty($school["favicon"] ?? "") ? "/uploads/sekolah/" . $school["favicon"] : "/logo.png") ?>">
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
    <script src="/assets/js/admin.js?v=<?= (int) @filemtime(FCPATH . 'assets/js/admin.js') ?>" defer></script>
</head>
<body class="dash">
    <div class="dash-shell" data-dash-shell>
        <?= view("admin/_sidebar", ["active" => "hasilvote", "username" => $username ?? null, "school" => $school ?? []]) ?>

        <div class="dash-main">
            <?= view("admin/_topbar", ["username" => $username ?? null]) ?>

            <main class="dash-page">
                <div class="dash-page-head">
                    <div>
                        <h1>Hasil Voting</h1>
                        <p>Perolehan suara kandidat pemilihan saat ini</p>
                        <p class="live-note" data-live-note>Diperbarui otomatis tiap 10 detik.</p>
                    </div>
                    <div class="dash-page-actions">
                        <button type="button" class="dash-icon-btn" data-fullscreen-toggle aria-label="Tampilkan layar penuh">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M8 3H5a2 2 0 0 0-2 2v3"/><path d="M16 3h3a2 2 0 0 1 2 2v3"/><path d="M8 21H5a2 2 0 0 1-2-2v-3"/><path d="M16 21h3a2 2 0 0 0 2-2v-3"/></svg>
                        </button>
                    </div>
                </div>

                <div class="public-board-head">
                    <p class="public-board-school"><?= esc($school["nm_sekolah"] ?? "E-Voting OSIS") ?></p>
                    <h2>Hasil Perolehan Suara</h2>
                </div>

                <section class="public-summary">
                    <div class="public-summary-card blue">
                        <span class="summary-label">Pemilih</span>
                        <strong data-live="pemilih"><?= esc($jmlPemilih ?? 0) ?></strong>
                    </div>
                    <div class="public-summary-card green">
                        <span class="summary-label">Sudah Vote</span>
                        <strong data-live="sudah"><?= esc($jmlVote ?? 0) ?></strong>
                    </div>
                    <div class="public-summary-card amber">
                        <span class="summary-label">Belum Vote</span>
                        <strong data-live="belum"><?= esc(max(0, (int) ($jmlPemilih ?? 0) - (int) ($jmlVote ?? 0))) ?></strong>
                    </div>
                </section>

                <section class="public-results-grid" data-live-grid>
                    <?php if (empty($hasilVote)): ?>
                        <div class="public-empty-state">Belum ada data suara masuk.</div>
                    <?php else: ?>
                        <?php
                        $totalSuara = (int) ($jmlVote ?? 0);
                        $suaraMax = 0;
                        foreach ($hasilVote as $baris) {
                            $suaraMax = max($suaraMax, (int) ($baris['jumlah'] ?? 0));
                        }
                        ?>
                        <?php foreach ($hasilVote as $item): ?>
                            <?php
                            $suara = (int) ($item['jumlah'] ?? 0);
                            $persenSuara = $totalSuara > 0 ? round($suara / $totalSuara * 100, 1) : 0.0;
                            $unggul = $suaraMax > 0 && $suara === $suaraMax;
                            ?>
                            <article class="public-result-card<?= $unggul ? ' winner' : '' ?>" data-calon-no="<?= esc($item['no'] ?? '-') ?>">
                                <div class="public-head-row" data-kepala>
                                    <span class="public-rank">No. <?= esc($item['no'] ?? '-') ?></span>
                                    <?php if ($unggul): ?>
                                        <span class="public-leader">Unggul</span>
                                    <?php endif; ?>
                                </div>
                                <div class="public-candidate-name"><?= esc($item['nama'] ?? '-') ?></div>
                                <div class="public-vote-box">
                                    <span>Suara</span>
                                    <strong data-suara><?= esc($suara) ?></strong>
                                </div>
                                <div class="public-share">
                                    <div class="public-bar" role="img" aria-label="<?= esc(number_format($persenSuara, 1, ',', '.')) ?> persen dari total suara masuk"><span data-bar style="width: <?= esc(number_format(min(100, max(0, $persenSuara)), 1, '.', '')) ?>%;"></span></div>
                                    <small data-persen><?= esc(number_format($persenSuara, 1, ',', '.')) ?>% dari total suara masuk</small>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </section>
            </main>
        </div>
    </div>
</body>
</html>
