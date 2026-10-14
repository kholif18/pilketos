<?php
$namaPengguna = $username ?? "admin";
$inisialAdmin = strtoupper(mb_substr((string) $namaPengguna, 0, 1));
?>
<header class="dash-topbar">
    <button type="button" class="dash-icon-btn" data-sidebar-toggle aria-expanded="true" aria-controls="sidebar" aria-label="Buka atau tutup navigasi">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>
    <label class="dash-search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/></svg>
        <input type="search" data-candidate-search placeholder="Cari kandidat, informasi, atau menu..." aria-label="Cari kandidat">
    </label>
    <div class="dash-user-chip">
        <span class="dash-avatar" aria-hidden="true"><?= esc($inisialAdmin) ?></span>
        <span class="dash-user-chip-text">
            <strong><?= esc($namaPengguna) ?></strong>
            <small>Administrator</small>
        </span>
        <a class="dash-icon-btn" href="/admin/logout" aria-label="Keluar">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="m16 17 5-5-5-5M21 12H9"/></svg>
        </a>
    </div>
</header>
