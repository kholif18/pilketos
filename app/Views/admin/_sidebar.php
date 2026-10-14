<?php
$activeMenu = $active ?? "admin";
$namaPengguna = $username ?? "admin";
$namaSekolah = is_array($school ?? null) ? ($school["nm_sekolah"] ?? "Belum diisi") : "Belum diisi";
$inisialAdmin = strtoupper(mb_substr((string) $namaPengguna, 0, 1));
$tautan = [
    "admin"      => ["/admin", "Beranda", '<path d="m3 10 9-7 9 7v10a1 1 0 0 1-1 1h-5v-6h-6v6H4a1 1 0 0 1-1-1V10Z"/>'],
    "calon"      => ["/admin/calon", "Kandidat", '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>'],
    "siswa"      => ["/admin/siswa", "Pemilih", '<path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/>'],
    "kelas"      => ["/admin/kelas", "Kelas", '<path d="M22 10 12 5 2 10l10 5 10-5Z"/><path d="M6 12v5c0 1.7 2.7 3 6 3s6-1.3 6-3v-5"/>'],
    "hasilvote"  => ["/admin/hasilvote", "Hasil", '<path d="M3 3v18h18"/><path d="M7 15v3M12 10v8M17 6v12"/>'],
    "daftarhadir" => ["/admin/daftarhadir", "Daftar Hadir", '<rect x="8" y="2" width="8" height="4" rx="1"/><path d="M16 4h2a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2h2"/><path d="m9 14 2 2 4-4"/>'],
    "laporan"    => ["/admin/laporan", "Laporan", '<path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6Z"/><path d="M14 2v6h6M9 13h6M9 17h6"/>'],
    "identitas"  => ["/admin/identitas", "Identitas", '<circle cx="12" cy="12" r="10"/><path d="M12 16v-4M12 8h.01"/>'],
    "connect-api" => ["/admin/connect-api", "Koneksi API", '<path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/>'],
    "pengaturan" => ["/admin/pengaturan", "Pengaturan", '<circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 1 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 1 1-4 0v-.09a1.65 1.65 0 0 0-1-1.51 1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 1 1-2.83-2.83l.06-.06a1.65 1.65 0 0 0 .33-1.82 1.65 1.65 0 0 0-1.51-1H3a2 2 0 1 1 0-4h.09a1.65 1.65 0 0 0 1.51-1 1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 1 1 2.83-2.83l.06.06a1.65 1.65 0 0 0 1.82.33h.01a1.65 1.65 0 0 0 1-1.51V3a2 2 0 1 1 4 0v.09a1.65 1.65 0 0 0 1 1.51h.01a1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 1 1 2.83 2.83l-.06.06a1.65 1.65 0 0 0-.33 1.82v.01a1.65 1.65 0 0 0 1.51 1H21a2 2 0 1 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1Z"/>'],
];
?>
<?php
$logoBerkas = is_array($school ?? null) ? (string) ($school["logo"] ?? "") : "";
if (! preg_match('/^[A-Za-z0-9._-]+$/', $logoBerkas)) {
    $logoBerkas = "";
}
$logoAda = $logoBerkas !== "" && is_file(FCPATH . "uploads/sekolah/" . $logoBerkas);
$avatarSesi = (string) (session()->get("avatar") ?? "");
if (! preg_match('/^[A-Za-z0-9._-]+$/', $avatarSesi)) {
    $avatarSesi = "";
}
$avatarAda = $avatarSesi !== "" && is_file(FCPATH . "uploads/avatar/" . $avatarSesi);
?>
<aside class="dash-sidebar" id="sidebar" aria-label="Navigasi admin">
    <div class="dash-brand">
        <span class="dash-brand-mark" aria-hidden="true">
            <?php if ($logoAda): ?>
                <img class="dash-brand-logo" src="/uploads/sekolah/<?= esc($logoBerkas) ?>" alt="Logo <?= esc($namaSekolah) ?>">
            <?php elseif (is_file(FCPATH . "logo.png")): ?>
                <img class="dash-brand-logo" src="/logo.png" alt="Logo E-Voting OSIS">
            <?php else: ?>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 2 4 5v6c0 5 3.4 9.4 8 11 4.6-1.6 8-6 8-11V5l-8-3Z"/><path d="m9 12 2 2 4-4"/></svg>
            <?php endif; ?>
        </span>
        <span class="dash-brand-text">
            <strong>E-Voting OSIS</strong>
            <small><?= esc($namaSekolah) ?></small>
        </span>
    </div>

    <nav class="dash-nav">
        <?php foreach ($tautan as $kunci => $item): ?>
            <?php $sedangAktif = $kunci === $activeMenu; ?>
            <a class="dash-nav-link<?= $sedangAktif ? " is-active" : "" ?>" href="<?= esc($item[0]) ?>" title="<?= esc($item[1]) ?>"<?= $sedangAktif ? ' aria-current="page"' : "" ?>>
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><?= $item[2] ?></svg>
                <?= esc($item[1]) ?>
            </a>
        <?php endforeach; ?>
    </nav>

    <div class="dash-side-foot">
        <p class="dash-motto">Suara Anda<br>Masa Depan Sekolah</p>
        <div class="dash-user-mini">
            <span class="dash-avatar" aria-hidden="true"><?php if ($avatarAda): ?><img src="/uploads/avatar/<?= esc($avatarSesi) ?>" alt="Avatar <?= esc($namaPengguna) ?>"><?php else: ?><?= esc($inisialAdmin) ?><?php endif; ?></span>
            <span class="dash-user-mini-text">
                <strong><?= esc($namaPengguna) ?></strong>
                <small>Administrator</small>
            </span>
            <a class="dash-logout" href="/admin/logout" aria-label="Keluar dari sesi admin">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/></svg>
            </a>
        </div>
    </div>
</aside>
<div class="dash-backdrop" data-sidebar-backdrop aria-hidden="true"></div>
