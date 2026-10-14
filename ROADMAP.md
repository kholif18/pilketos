# Roadmap Pengembangan pilketos-v2

## Tujuan

Membangun aplikasi Pilketos versi CI4 yang siap digunakan untuk keperluan admin, pemilih, laporan, dan presentasi hasil voting secara publik. Fokus utama adalah menyamai fungsi aplikasi lama sambil memperbaiki pengalaman dan stabilitas aplikasi di lingkungan modern.

## Status saat ini

### ✅ Sudah selesai

- login umum admin dan pemilih
- halaman dashboard admin
- identitas sekolah
- data kelas dan default kelas 7A–9D
- pengelolaan data calon
- halaman daftar hadir
- halaman laporan dan hasil voting
- reset data dan reset pemilih
- komponen UI responsif untuk mobile
- fallback database untuk tabel dasar seperti admin, siswa, dan kelas
- CRUD dan sync data siswa (manual + API sekolah)
- export PDF dasar untuk laporan
- tombol test koneksi dan sync API terpisah
- halaman hasil voting + tombol fullscreen
- template sidebar dan header global untuk semua halaman admin
- pencarian global topbar (dropdown menu + kandidat, navigasi keyboard)
- form pengaturan tapel dan tanggal pemilihan di dashboard
- validasi NIS 4 digit di data siswa + ganti password admin

### ⏳ Belum selesai
- ✅ upload foto calon end-to-end (validasi, `public/uploads/calon`, thumbnail tabel + dashboard, hapus file saat ganti/hapus/reset)
- ✅ sync guru sisi pilketos (kelas "Guru" otomatis, password default NIP) — teruji live
- ✅ log audit sync (`tb_log_sync` + riwayat paginasi di connect-api)
- ✅ integrasi API sekolah end-to-end (endpoint + token + network + sync live)
- ✅ export PDF tabel siap pakai + cetak daftar hadir via PDF
- ✅ auto-refresh live + mode presentasi fullscreen
- ✅ CSRF aktif global
- ⏳ kop logo sekolah di PDF (perlu file aset logo)
- ⏳ filter laporan tanggal/kelas/status (opsional)
- ⏳ audit keamanan route/session + perkuat password admin
- ⏳ uji end-to-end browser nyata + deployment plan
- ⏳ polishing final sebelum deployment

## Prinsip pengembangan

- Prioritaskan fitur yang berdampak langsung ke operasional pemilihan.
- Hindari menambah fitur baru sebelum fitur inti stabil.
- Selalu validasi dengan test dan ujicoba real flow admin/pemilih.
- Jaga agar struktur aplikasi tetap konsisten dengan pola CI4 dan kode legacy yang sudah ada.

## Fase 1: Stabilkan data master dan autentikasi

Status: ✅ Selesai

### Target
Menyelesaikan data dasar yang dibutuhkan untuk semua fitur.

### Pekerjaan
- ✅ data kelas dan default kelas sudah dibuat
- ✅ CRUD, kelas, dan status hadir siswa sudah ada
- ✅ ubah kelas via modal + hapus massal kelas
- ✅ validasi format NIS 4 digit di data siswa
- ✅ form pengaturan tapel dan tanggal pemilihan di dashboard
- ✅ tanggal sebagai gerbang voting (hanya hari-H, tanpa jadwal = tutup) + halaman tutup
- ✅ kolom `tgl` nullable (MySQL strict menolak zero-date; seed + reset diselaraskan)
- ✅ ganti password admin (verifikasi lama, minimal 4 karakter, konfirmasi)
- ✅ autentikasi pemilih dan admin sudah berjalan
- ✅ satu pemilih satu suara dijamin (`UNIQUE tb_pilih.username` + guard double-submit/back/race)
- ✅ fallback database sudah dibuat untuk tabel dasar

### Output yang diharapkan
- semua siswa bisa diinput dengan benar
- kelas siap untuk dipakai di seluruh modul
- login admin dan pemilih stabil di semua skenario

## Fase 2: Sinkronisasi websekolah (siswa + guru sebagai DPT)

Status: ✅ Selesai

Import Excel massal DPT **dibatalkan** — diganti sync langsung dari
websekolah (`/home/seira/Projects/websitesekolah/src/websitesekolah/`,
container `sekolah-dev`) plus **impor CSV sebagai jalan darurat**
(`nis,nama,jk,kelas`, delimiter `,`/`;`, lewati header, password default NIS).
Siklus operasional: sync/impor → atur tapel/tanggal →
voting → laporan/cetak → tombol reset di dashboard.

### Target
Menarik data siswa dan guru dari websekolah menjadi DPT pilketos.

### Pekerjaan

Sisi websekolah:
- ✅ kolom Dapodik (`jenis_kelamin`, tempat/tanggal lahir, agama, alamat, no HP) + form + validasi
- ✅ endpoint `GET /api/connect/health|kelas|siswa|guru` + auth token (middleware `pilketos.token`)
- ✅ multi-aplikasi ala Dapodik: tabel `api_apps` (token hash, scope kelas/siswa/guru, aktif, last-used), menu tabel + tambah/revoke/regenerate/hapus, token tampil-sekali, `pilketos:token` mendukung multi-app
- ✅ 19 test endpoint + halaman API hijau; sync live pilketos tetap jalan setelah migrasi

Sisi pilketos:
- ✅ samakan network Docker (ci4_app gabung `network_app` eksternal) +
  `database.default.hostname = pilketos-db` (nama `db` tabrakan dengan
  service lain di `network_app`)
- ✅ mapping field tetap: siswa `nis→username`, `name→nm_siswa`,
  `kelas.nama_kelas→tb_kelas`; guru `nip→username`, `name→nm_siswa`,
  kelas khusus `"Guru"` (dibuat otomatis bila belum ada)
- ✅ password DPT = NIS/NIP saat sync (keputusan: hash bcrypt websekolah
  tidak dipakai karena login pemilih pilketos plaintext)
- ✅ `jk` dari API bila ada, default `L`; baris tanpa NIS/NIP dilewati
  dan dihitung gagal
- ✅ validasi data duplikat, kosong, dan format salah
- ✅ log proses sync untuk audit (`tb_log_sync` + riwayat)
- ✅ tombol test koneksi dan sync terpisah
- ✅ impor CSV DPT + template + laporan baris dilewati

### Output yang diharapkan
- ✅ sync live teruji: 4 siswa + 2 guru masuk DPT (tercatat di log audit)
- admin bisa menyinkronkan siswa+guru tanpa proses manual berulang
- data lokal konsisten dengan sumber utama
- error sync mudah dideteksi dan diatasi

## Fase 3: Laporan, daftar hadir, dan export PDF

Status: ⏳ Belum selesai

### Target
Menyediakan data yang siap dipakai untuk evaluasi dan dokumentasi.

### Pekerjaan
- ✅ daftar hadir halaman tersedia
- ✅ daftar hadir dari data pemilih (lengkap bila DPT terisi)
- ✅ cetak daftar hadir via export PDF
- ✅ laporan umum hasil voting
- ✅ export PDF tabel siap pakai (header bold, grid, zebra, tanda tangan, nomor halaman, multipage)
- ⏳ kop logo sekolah di PDF (perlu aset logo)
- ⏳ filter tanggal, kelas, atau status tertentu bila diperlukan

### Output yang diharapkan
- laporan dapat dicetak dan dibagikan
- data hasil mudah dipahami oleh admin dan pihak sekolah
- proses pencetakan tidak mengandalkan manual editing

## Fase 4: Hasil voting publik / smart board

Status: ⏳ Belum selesai

### Target
Membuat tampilan hasil voting yang mudah dibaca dari jarak jauh dan cocok dipajang di layar besar.

### Pekerjaan
- ✅ hasil voting halaman sudah ada
- ✅ kartu kandidat besar + skor + bar persen + lencana Unggul
- ✅ tombol fullscreen ikon + mode presentasi light (sembunyikan sidebar/header, angka besar, aman diproyeksikan)
- ✅ auto-refresh hasil tiap 10 detik via `GET /admin/hasilvote/data` (jeda saat tab disembunyikan, reload bila daftar kandidat berubah)
- ⏳ uji tampil smart board/monitor besar + selaraskan ukuran terakhir
- ⏳ konsistensi warna, ukuran teks, dan visibilitas

### Output yang diharapkan
- hasil voting bisa dipasang di layar publik tanpa perlu membuka dashboard admin
- orang banyak bisa membaca hasil dengan cepat
- presentasi terlihat profesional dan rapi

## Fase 5: Polishing, QA, dan deployment

Status: ⏳ Belum selesai

### Target
Membuat aplikasi siap dipakai secara rutin dan aman.

### Pekerjaan
- ⏳ ujicoba end-to-end admin dan pemilih
- ⏳ pengujian reset data dan pemilih
- ⏳ pengecekan keamanan route dan session
- ✅ CSRF aktif global + `csrf_field()` di 13 form + token di semua POST test
- ✅ hapus calon/kelas/siswa via POST (tutup lubang CSRF GET) + regenerate session saat login
- ⏳ perkuat password admin (aturan detail menyusul)
- ✅ optimasi tampilan mobile sudah mulai ditangani
- ⏳ tinjauan UX dan konsistensi layout
- ⏳ persiapan deployment dan rollback plan

### Output yang diharapkan
- aplikasi siap dipakai di lingkungan nyata
- tidak ada regresi signifikan pada fitur yang sudah ada
- proses penggunaan lebih mudah untuk admin dan pemilih

## Prioritas 30 hari

### 🔴 Prioritas tinggi
- audit keamanan route/session + perkuat password admin
- uji end-to-end browser nyata + deployment plan

### 🟠 Prioritas menengah
- uji tampil smart board + selaraskan ukuran terakhir
- kop logo PDF (butuh file logo)
- filter laporan (bila diperlukan)

### 🟡 Prioritas rendah
- fitur tambahan non-esensial
- optimasi cosmetic yang tidak berdampak pada fungsi utama

## Timeline rekomendasi

### Minggu 1–2
- ✅ default kelas sudah selesai
- ⏳ finalisasi data siswa dan kelas
- ⏳ validasi input NIS/NISN
- ⏳ perbaikan edge-case sync data

### Minggu 3–4
- ⏳ API sekolah selesai
- ⏳ import/sinkronisasi data berfungsi
- ⏳ laporan dasar siap diakses

### Minggu 5–6
- ⏳ PDF report dan daftar hadir selesai
- ⏳ hasil voting publik siap diperlihatkan

### Minggu 7–8
- ⏳ QA, polishing, bugfix, dan deployment readiness

## Kriteria keberhasilan

- ✅ semua fitur inti dasar Pilketos sudah ada di CI4
- ⏳ admin dan pemilih dapat menggunakan sistem tanpa hambatan secara penuh
- ⏳ data sekolah dan siswa konsisten
- ⏳ laporan dan hasil vote siap diproduksi secara rutin
- ⏳ tampilan publik siap dipasang di smart board atau layar besar

## Catatan

Roadmap ini bersifat living plan: prioritas dapat berubah sesuai kebutuhan sekolah, hasil ujicoba, atau kebutuhan operasional. Fokus utama tetap pada stabilitas, akurasi data, dan kemudahan penggunaan.
