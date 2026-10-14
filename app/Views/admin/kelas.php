<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Kelas</title>
    <link rel="icon" href="<?= esc(!empty($school["favicon"] ?? "") ? "/uploads/sekolah/" . $school["favicon"] : "/logo.png") ?>">
    <link rel="stylesheet" href="/assets/css/admin.css?v=<?= (int) @filemtime(FCPATH . 'assets/css/admin.css') ?>">
    <script src="/assets/js/admin.js?v=<?= (int) @filemtime(FCPATH . 'assets/js/admin.js') ?>" defer></script>
</head>
<body class="dash">
    <div class="dash-shell" data-dash-shell>
        <?= view("admin/_sidebar", ["active" => "kelas", "username" => $username ?? null, "school" => $school ?? []]) ?>

        <div class="dash-main">
            <?= view("admin/_topbar", ["username" => $username ?? null]) ?>

            <main class="dash-page">
                <div class="dash-page-head">
                    <div>
                        <h1>Data Kelas</h1>
                        <p>Kelola kelas dan kelompok siswa</p>
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
                        <h2>Tambah Kelas</h2>
                    </div>
                    <div class="panel-body">
                        <form method="post" action="/admin/kelas" class="inline-form">
                            <?= csrf_field() ?>
                            <div class="field">
                                <label for="nm_kelas">Nama Kelas</label>
                                <input type="text" id="nm_kelas" name="nm_kelas" placeholder="Contoh: X-1, XI IPA, XII IPS" required>
                            </div>
                            <button type="submit" class="btn btn-primary">Simpan Data</button>
                        </form>
                    </div>
                </section>

                <section class="panel">
                    <div class="panel-header">
                        <h2>Daftar Kelas</h2>
                    </div>
                    <div class="panel-body">
                        <div class="bulk-delete-toolbar">
                                <form id="bulk-delete-form" method="post" action="/admin/kelas/bulk-delete" class="bulk-delete-form" data-confirm="Apakah Anda yakin ingin menghapus kelas yang dipilih?">
                                    <?= csrf_field() ?>
                                    <div class="bulk-delete-actions">
                                        <label class="checkbox-inline">
                                            <input id="select-all-kelas" type="checkbox" aria-label="Pilih semua kelas">
                                            <span>Pilih semua</span>
                                        </label>
                                        <button type="submit" class="btn btn-danger">Bulk Delete</button>
                                    </div>
                                </form>
                            </div>

                            <div class="table-wrap">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th width="50">
                                                <span class="sr-only">Pilih</span>
                                            </th>
                                            <th width="80">No</th>
                                            <th>Kode Kelas</th>
                                            <th>Nama Kelas</th>
                                            <th width="130">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php if (empty($kelas)): ?>
                                            <tr>
                                                <td colspan="5" class="empty-state">Belum ada data kelas. Tambahkan kelas baru di form di atas.</td>
                                            </tr>
                                        <?php else: ?>
                                            <?php foreach ($kelas as $index => $item): ?>
                                                <tr>
                                                    <td>
                                                        <input type="checkbox" form="bulk-delete-form" name="kd_kelas[]" value="<?= esc($item['kd_kelas'] ?? 0) ?>" class="row-checkbox" aria-label="Pilih kelas <?= esc($item['nm_kelas'] ?? 'kelas') ?>">
                                                    </td>
                                                    <td><?= esc($index + 1) ?></td>
                                                    <td><?= esc($item['kd_kelas'] ?? '-') ?></td>
                                                    <td><?= esc($item['nm_kelas'] ?? '-') ?></td>
                                                <td>
                                                    <div class="row-actions">
                                                        <button type="button" class="btn btn-secondary" data-edit-kelas data-id="<?= esc($item['kd_kelas'] ?? 0) ?>" data-nama="<?= esc($item['nm_kelas'] ?? '') ?>" title="Ubah kelas" aria-label="Ubah kelas <?= esc($item['nm_kelas'] ?? '') ?>">
                                                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17 3a2.85 2.83 0 1 1 4 4L7.5 20.5 2 22l1.5-5.5Z"/></svg>
                                                        </button>
                                                        <form method="post" action="/admin/kelas/delete" class="row-delete">
                                                            <?= csrf_field() ?>
                                                            <input type="hidden" name="kd_kelas" value="<?= esc($item['kd_kelas'] ?? 0) ?>">
                                                            <button type="submit" class="btn btn-danger" data-confirm="Apakah Anda yakin ingin menghapus kelas ini?" title="Hapus kelas" aria-label="Hapus kelas <?= esc($item['nm_kelas'] ?? '') ?>">
                                                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 6h18"/><path d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6"/><path d="M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"/></svg>
                                                            </button>
                                                        </form>
                                                    </div>
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
    <div class="swal-overlay" data-edit-modal hidden>
        <div class="swal-card" role="dialog" aria-modal="true" aria-labelledby="edit-kelas-title">
            <h2 id="edit-kelas-title">Ubah Kelas</h2>
            <form method="post" action="/admin/kelas/update">
                <?= csrf_field() ?>
                <input type="hidden" name="kd_kelas" data-edit-id value="">
                <div class="field">
                    <label for="edit-nm-kelas">Nama Kelas</label>
                    <input type="text" id="edit-nm-kelas" name="nm_kelas" data-edit-nama value="" required>
                </div>
                <div class="swal-actions">
                    <button type="button" class="btn btn-outline" data-edit-batal>Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAll = document.getElementById('select-all-kelas');
            if (!selectAll) {
                return;
            }

            selectAll.addEventListener('change', function () {
                document.querySelectorAll('.row-checkbox').forEach(function (checkbox) {
                    checkbox.checked = selectAll.checked;
                });
            });
        });
    </script>
</body>
</html>
