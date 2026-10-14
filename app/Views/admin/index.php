<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin Pilketos</title>
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
    <script src="/assets/js/admin.js?v=<?= (int) @filemtime(FCPATH . 'assets/js/admin.js') ?>" defer></script>
</head>
<body class="dash">
    <?php
    $schoolName = $school["nm_sekolah"] ?? "Belum diisi";
    $tapel = trim((string) ($election["tapel"] ?? ""));
    $tgl = trim((string) ($election["tgl"] ?? ""));
    if ($tgl === "0000-00-00") {
        $tgl = "";
    }
    $tglInput = ($tgl !== "" && preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) ? $tgl : "";
    $periode = $tapel !== "" || $tgl !== "" ? trim($tapel . ($tapel !== "" && $tgl !== "" ? " • " : "") . $tgl) : "Belum diatur";
    $jmlCalon = (int) ($stats["calon"] ?? 0);
    $jmlPemilih = (int) ($stats["pemilih"] ?? 0);
    $jmlSudah = (int) ($stats["sudah_memilih"] ?? 0);
    $jmlBelum = (int) ($belum_memilih ?? max(0, $jmlPemilih - $jmlSudah));
    $persen = (float) ($partisipasi ?? 0);
    $persenTeks = number_format($persen, 1, ",", ".") . "%";
    $status = $statusPemilihan ?? "Siap";
    $isBerlangsung = $status === "Sedang Berlangsung";
    $lingkar = 2 * M_PI * 34;
    $terisi = $lingkar * min(100, max(0, $persen)) / 100;
    $daftarCalon = is_array($calon ?? null) ? $calon : [];
    ?>
    <div class="dash-shell" data-dash-shell>
        <?= view("admin/_sidebar", ["active" => "admin", "username" => $username ?? null, "school" => $school ?? []]) ?>

        <div class="dash-main">
            <?= view("admin/_topbar", ["username" => $username ?? null]) ?>

            <div class="dash-columns">
                <main class="dash-center">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success" role="alert"><?= esc($success) ?></div>
                    <?php endif; ?>
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-error" role="alert"><?= esc($error) ?></div>
                    <?php endif; ?>

                    <section class="dash-hero" aria-labelledby="hero-title">
                        <div class="dash-hero-text">
                            <span class="dash-eyebrow">Selamat datang di</span>
                            <h1 id="hero-title">E-Voting OSIS<br><?= esc($schoolName) ?></h1>
                            <p>Kelola kandidat, pemilih, dan hasil pemilihan ketua OSIS dari satu dashboard. Data di bawah ini diambil langsung dari database.</p>
                            <div class="dash-hero-actions">
                                <a class="btn btn-primary" href="/admin/hasilvote">Lihat Hasil Pemilihan</a>
                                <a class="btn btn-outline dash-hero-secondary" href="/admin/calon">Kelola Kandidat</a>
                            </div>
                        </div>
                        <div class="dash-hero-art" aria-hidden="true">
                            <svg viewBox="0 0 320 220" fill="none">
                                <rect x="8" y="8" width="304" height="204" rx="24" fill="#dbeafe"/>
                                <path d="M30 150 90 96l34 30 44-52 60 58 62-46v90a24 24 0 0 1-24 24H54a24 24 0 0 1-24-24v-26Z" fill="#bfdbfe"/>
                                <rect x="52" y="96" width="96" height="64" rx="6" fill="#eff6ff" stroke="#93c5fd" stroke-width="3"/>
                                <path d="M52 116h96M100 96V74m0 0-12 8m12-8 12 8" stroke="#2563eb" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                <rect x="66" y="128" width="20" height="32" rx="2" fill="#93c5fd"/>
                                <rect x="114" y="128" width="20" height="32" rx="2" fill="#93c5fd"/>
                                <rect x="176" y="84" width="110" height="92" rx="8" fill="#1d4ed8"/>
                                <rect x="176" y="84" width="110" height="22" rx="8" fill="#1e40af"/>
                                <rect x="214" y="44" width="34" height="46" rx="2" fill="#ffffff" stroke="#1e3a8a" stroke-width="3" transform="rotate(12 231 67)"/>
                                <path d="M248 30c8 6 14 12 18 20l-22 8c-3-8-6-14-10-19l14-9Z" fill="#fbbf24"/>
                                <rect x="243" y="34" width="26" height="34" rx="4" fill="#2563eb" transform="rotate(12 256 51)"/>
                                <circle cx="231" cy="140" r="20" fill="none" stroke="#ffffff" stroke-width="3"/>
                                <path d="m224 140 5 5 10-10" stroke="#ffffff" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"/>
                                <text x="231" y="172" text-anchor="middle" fill="#ffffff" font-size="14" font-weight="700" font-family="Arial, sans-serif">OSIS</text>
                            </svg>
                        </div>
                    </section>

                    <section class="dash-stats" aria-label="Ringkasan pemilihan">
                        <div class="dash-stat">
                            <span class="dash-stat-icon is-blue" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/></svg>
                            </span>
                            <span class="dash-stat-label">Periode Pemilihan</span>
                            <strong class="dash-stat-value is-small"><?= esc($periode) ?></strong>
                            <span class="badge <?= $isBerlangsung ? "badge-success" : "badge-warning" ?>"><?= esc($status) ?></span>
                        </div>
                        <div class="dash-stat">
                            <span class="dash-stat-icon is-purple" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                            </span>
                            <span class="dash-stat-label">Total Kandidat</span>
                            <strong class="dash-stat-value"><?= esc($jmlCalon) ?></strong>
                            <span class="dash-stat-sub">Pasangan Calon</span>
                        </div>
                        <div class="dash-stat">
                            <span class="dash-stat-icon is-green" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/></svg>
                            </span>
                            <span class="dash-stat-label">Total Pemilih</span>
                            <strong class="dash-stat-value"><?= esc($jmlPemilih) ?></strong>
                            <span class="dash-stat-sub">Siswa Terdaftar</span>
                        </div>
                        <div class="dash-stat">
                            <span class="dash-stat-icon is-amber" aria-hidden="true">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            </span>
                            <span class="dash-stat-label">Sudah Memilih</span>
                            <strong class="dash-stat-value"><?= esc($jmlSudah) ?></strong>
                            <span class="dash-stat-sub"><?= esc($persenTeks) ?> dari total pemilih</span>
                        </div>
                    </section>

                    <section class="dash-panel" aria-labelledby="periode-title">
                        <div class="dash-panel-head">
                            <div>
                                <h2 id="periode-title">Periode Pemilihan</h2>
                                <p>Atur tahun pelajaran dan tanggal pelaksanaan. Tampil di dashboard, laporan, dan dokumen cetak.</p>
                            </div>
                        </div>
                        <form method="post" action="/admin/periode" class="inline-form">
                            <?= csrf_field() ?>
                            <div class="field">
                                <label for="tapel">Tahun Pelajaran</label>
                                <input type="text" id="tapel" name="tapel" value="<?= esc($tapel) ?>" maxlength="10" placeholder="Contoh: 2025/2026" required>
                            </div>
                            <div class="field">
                                <label for="tgl">Tanggal Pemilihan</label>
                                <input type="date" id="tgl" name="tgl" value="<?= esc($tglInput) ?>" required>
                            </div>
                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Simpan Periode</button>
                                <button type="submit" class="btn btn-danger" formaction="/admin/periode/reset" data-confirm="Kosongkan tahun pelajaran dan tanggal pemilihan? Data pemilih, calon, dan suara tidak ikut terhapus.">Reset Periode</button>
                            </div>
                        </form>
                    </section>

                    <section class="dash-panel" aria-labelledby="kandidat-title">
                        <div class="dash-panel-head">
                            <div>
                                <h2 id="kandidat-title">Data Kandidat</h2>
                                <p>Klik nama kandidat untuk mengelola datanya. Pastikan data sudah sesuai sebelum pemungutan suara.</p>
                            </div>
                            <span class="dash-count" data-candidate-count><?= esc(count($daftarCalon)) ?> kandidat</span>
                        </div>
                        <?php if (empty($daftarCalon)): ?>
                            <p class="empty-state">Belum ada data calon. Tambahkan calon baru melalui halaman <a href="/admin/calon">Kelola Calon</a>.</p>
                        <?php else: ?>
                            <div class="dash-candidate-grid">
                                <?php foreach ($daftarCalon as $item): ?>
                                    <?php
                                    $namaCalon = (string) ($item["nama"] ?? "-");
                                    $nisn = (string) ($item["nisn"] ?? "-");
                                    $nomor = (string) ($item["no"] ?? "-");
                                    $suara = (int) ($item["jumlah"] ?? 0);
                                    $kunciCari = mb_strtolower($namaCalon . " " . $nisn . " " . $nomor);
                                    $inisial = strtoupper(implode("", array_map(static fn($kata) => mb_substr($kata, 0, 1), array_slice(preg_split("/\s+/", trim($namaCalon)) ?: [], 0, 2))));
                                    $persenCalon = $jmlSudah > 0 ? round($suara / $jmlSudah * 100, 1) : 0.0;
                                    ?>
                                    <article class="dash-candidate-card" data-candidate-card data-search="<?= esc($kunciCari) ?>">
                                        <span class="dash-candidate-no" aria-hidden="true"><?= esc($nomor) ?></span>
                                        <span class="dash-candidate-avatar" aria-hidden="true"><?= esc($inisial !== "" ? $inisial : "-") ?></span>
                                        <h3><?= esc($namaCalon) ?></h3>
                                        <p class="dash-candidate-meta">Calon Ketua OSIS</p>
                                        <p class="dash-candidate-nis">NIS <?= esc($nisn) ?> • No. <?= esc($nomor) ?></p>
                                        <p class="dash-candidate-vote"><strong><?= esc($suara) ?></strong> suara<?= $jmlSudah > 0 ? " (" . esc(number_format($persenCalon, 1, ",", ".")) . "%)" : "" ?></p>
                                        <a class="btn btn-outline dash-candidate-btn" href="/admin/calon">Kelola Kandidat</a>
                                    </article>
                                <?php endforeach; ?>
                            </div>
                            <p class="empty-state" data-candidate-empty hidden>Tidak ada kandidat yang cocok dengan pencarian.</p>
                        <?php endif; ?>
                        <div class="dash-panel-foot">
                            <p>Data suara diperbarui setiap kali ada pemilih yang memilih.</p>
                            <a class="btn btn-primary" href="/admin/hasilvote">Buka Hasil Lengkap</a>
                        </div>
                    </section>

                    <section class="dash-reset-grid" aria-label="Reset data">
                        <div class="reset-card danger-card">
                            <div class="reset-card-header">
                                <span class="reset-tag">Reset Data</span>
                                <h3>Reset Data Pemilihan</h3>
                            </div>
                            <p>Fitur ini diperlukan pada saat sekolah ingin membersihkan data pada database dan ingin melakukan pemilihan pada tahun berikutnya.</p>
                            <form method="post" action="/admin/reset-data" data-confirm="Apakah Anda yakin ingin menghapus semua data pemilihan?">
                                <?= csrf_field() ?>
                                <button type="submit" class="btn btn-danger btn-block">Reset Data Pemilihan</button>
                            </form>
                        </div>
                        <div class="reset-card">
                            <div class="reset-card-header">
                                <span class="reset-tag secondary">Reset User</span>
                                <h3>Reset User DPT</h3>
                            </div>
                            <p>Fitur ini digunakan apabila ada DPT yang mengajukan komplain bahwa dia belum pernah melakukan pemilihan, namun akunnya telah terkunci.</p>
                            <form method="post" action="/admin/reset-user" data-confirm="Apakah Anda yakin ingin mereset status pengguna ini?" class="reset-user-form">
                                <?= csrf_field() ?>
                                <label for="username" class="field-label">Username / NIS</label>
                                <input id="username" type="text" name="username" class="input" placeholder="Masukkan NIS siswa" autocomplete="off">
                                <button type="submit" class="btn btn-primary btn-block">Reset User DPT</button>
                            </form>
                        </div>
                    </section>
                </main>

                <aside class="dash-side" aria-label="Status dan informasi">
                    <section class="dash-panel dash-status" aria-labelledby="status-title">
                        <div class="dash-panel-head">
                            <h2 id="status-title">Status Pemilihan</h2>
                            <span class="badge <?= $isBerlangsung ? "badge-success" : "badge-warning" ?>"><?= esc($status) ?></span>
                        </div>
                        <div class="dash-status-body">
                            <div class="dash-donut" role="img" aria-label="Partisipasi <?= esc($persenTeks) ?>">
                                <svg viewBox="0 0 84 84" aria-hidden="true">
                                    <circle cx="42" cy="42" r="34" fill="none" stroke="#e2e8f0" stroke-width="10"/>
                                    <circle cx="42" cy="42" r="34" fill="none" stroke="#10b981" stroke-width="10" stroke-linecap="round" stroke-dasharray="<?= esc(number_format($terisi, 2, ".", "")) ?> <?= esc(number_format($lingkar, 2, ".", "")) ?>" transform="rotate(-90 42 42)"/>
                                </svg>
                                <span class="dash-donut-text"><strong><?= esc($persenTeks) ?></strong><small>Partisipasi</small></span>
                            </div>
                            <dl class="dash-status-list">
                                <div><dt>Total DPT</dt><dd><?= esc($jmlPemilih) ?></dd></div>
                                <div><dt>Sudah memilih</dt><dd><?= esc($jmlSudah) ?></dd></div>
                                <div><dt>Belum memilih</dt><dd><?= esc($jmlBelum) ?></dd></div>
                                <div><dt>Tahun pelajaran</dt><dd><?= esc($tapel !== "" ? $tapel : "-") ?></dd></div>
                            </dl>
                        </div>
                        <div class="dash-progress" aria-hidden="true"><span style="width: <?= esc(number_format(min(100, max(0, $persen)), 1, ".", "")) ?>%;"></span></div>
                    </section>

                    <section class="dash-panel" aria-labelledby="sekolah-title">
                        <div class="dash-panel-head">
                            <h2 id="sekolah-title">Informasi Sekolah</h2>
                            <a class="dash-link" href="/admin/identitas">Ubah</a>
                        </div>
                        <dl class="dash-info-list">
                            <div><dt>Nama Sekolah</dt><dd><?= esc($school["nm_sekolah"] ?? "Belum diisi") ?></dd></div>
                            <div><dt>NPSN</dt><dd><?= esc($school["npsn"] ?? "-") ?></dd></div>
                            <div><dt>Alamat</dt><dd><?= esc(trim(($school["jln"] ?? "-") . ", " . ($school["desa"] ?? "-"))) ?></dd></div>
                            <div><dt>Kepala Sekolah</dt><dd><?= esc($school["kpl_sekolah"] ?? "-") ?></dd></div>
                            <div><dt>Kabupaten</dt><dd><?= esc($school["kab"] ?? "-") ?></dd></div>
                            <div><dt>Tanggal Pemilihan</dt><dd><?= esc($tgl !== "" ? $tgl : "Belum diatur") ?></dd></div>
                        </dl>
                    </section>

                    <section class="dash-panel" aria-labelledby="aksi-title">
                        <div class="dash-panel-head">
                            <h2 id="aksi-title">Aksi Cepat</h2>
                        </div>
                        <div class="dash-actions">
                            <a href="/admin/calon"><strong>Kelola Calon</strong><span>Tambah, edit, dan lihat kandidat.</span></a>
                            <a href="/admin/siswa"><strong>Data Siswa</strong><span>Lihat dan sinkron daftar pemilih.</span></a>
                            <a href="/admin/daftarhadir"><strong>Daftar Hadir</strong><span>Rekap kehadiran pemilih.</span></a>
                            <a href="/admin/laporan"><strong>Laporan</strong><span>Ringkasan suara dan pemilih.</span></a>
                        </div>
                    </section>
                </aside>
            </div>
        </div>
    </div>
</body>
</html>