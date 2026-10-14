<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Calon</title>
    <link rel="icon" href="<?= esc(!empty($school["favicon"] ?? "") ? "/uploads/sekolah/" . $school["favicon"] : "/logo.png") ?>">
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
    <script src="/assets/js/admin.js?v=<?= (int) @filemtime(FCPATH . 'assets/js/admin.js') ?>" defer></script>
</head>
<body class="dash">
    <div class="dash-shell" data-dash-shell>
        <?= view("admin/_sidebar", ["active" => "calon", "username" => $username ?? null, "school" => $school ?? []]) ?>

        <div class="dash-main">
            <?= view("admin/_topbar", ["username" => $username ?? null]) ?>

            <main class="dash-page">
                <div class="dash-page-head">
                    <div>
                        <h1>Kelola Calon</h1>
                        <p>Tambah, edit, dan lihat kandidat pemilihan</p>
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
                        <h2>Tambah Calon</h2>
                    </div>
                    <div class="panel-body">
                        <form method="post" action="/admin/calon" class="inline-form" enctype="multipart/form-data">
                            <?= csrf_field() ?>
                            <div class="field">
                                <label for="nis">NIS</label>
                                <input type="text" id="nis" name="nis" inputmode="numeric" maxlength="4" pattern="\d{4}" placeholder="Contoh: 2024" required>
                            </div>

                            <div class="field">
                                <label for="nama">Nama Calon</label>
                                <input type="text" id="nama" name="nama" placeholder="Contoh: Budi Santoso" required>
                            </div>

                            <div class="field">
                                <label for="no">Nomor Urut</label>
                                <input type="number" id="no" name="no" min="1" max="99" value="1" required>
                            </div>

                            <div class="field media-picker-field full-width">
                                <label for="photo">Foto Calon</label>
                                <div class="media-picker" data-picker>
                                    <div class="media-preview empty" data-preview>
                                        <img alt="Preview foto calon" src="" hidden>
                                    </div>
                                    <div class="media-picker-info">
                                        <button type="button" class="btn btn-secondary media-picker-button">Pilih Foto</button>
                                        <span class="media-picker-name">Belum ada file dipilih</span>
                                        <small class="media-picker-hint">Maksimal 1 MB. Format JPG, PNG, atau GIF. Kosongkan untuk memakai foto bawaan.</small>
                                    </div>
                                    <input type="file" id="photo" name="photo" accept=".jpg,.jpeg,.png,.gif,image/jpeg,image/png,image/gif" aria-label="Pilih foto calon">
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" class="btn btn-primary">Simpan Calon</button>
                            </div>
                        </form>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-header">
                        <h2>Daftar Calon</h2>
                    </div>
                    <div class="panel-body">
                        <div class="table-wrap">
                            <table class="data-table">
                                <thead>
                                    <tr>
                                        <th width="60">No</th>
                                        <th>NIS</th>
                                        <th>Nama Calon</th>
                                        <th>Nomor Urut</th>
                                        <th>Foto</th>
                                        <th width="120">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($calon)): ?>
                                        <tr>
                                            <td colspan="6" class="empty-state">Belum ada data calon. Tambahkan calon baru di form di atas.</td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($calon as $index => $item): ?>
                                            <?php
                                            $fotoCalon = (string) ($item['photo'] ?? 'default.jpg');
                                            if (! preg_match('/^[A-Za-z0-9._-]+$/', $fotoCalon)) {
                                                $fotoCalon = 'default.jpg';
                                            }
                                            $fotoAda = $fotoCalon !== 'default.jpg' && is_file(FCPATH . 'uploads/calon/' . $fotoCalon);
                                            ?>
                                            <tr>
                                                <td><?= esc($index + 1) ?></td>
                                                <td><?= esc($item['nisn'] ?? '-') ?></td>
                                                <td><?= esc($item['nama'] ?? '-') ?></td>
                                                <td><?= esc($item['no'] ?? '-') ?></td>
                                                <td>
                                                    <?php if ($fotoAda): ?>
                                                        <img class="calon-thumb" src="/uploads/calon/<?= esc($fotoCalon) ?>" alt="Foto <?= esc($item['nama'] ?? 'calon') ?>">
                                                    <?php else: ?>
                                                        <?= esc($fotoCalon) ?>
                                                    <?php endif; ?>
                                                </td>
                                                <td>
                                                    <form method="post" action="/admin/calon/delete" class="row-delete">
                                                        <?= csrf_field() ?>
                                                        <input type="hidden" name="nisn" value="<?= esc($item['nisn'] ?? '') ?>">
                                                        <button type="submit" class="btn btn-danger" data-confirm="Apakah Anda yakin ingin menghapus calon ini?" title="Hapus calon" aria-label="Hapus calon <?= esc($item['nama'] ?? '') ?>">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                        </button>
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
