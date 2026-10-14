# Panduan Instalasi Pilketos v2

## Syarat

- Docker + Docker Compose v2
- Port bebas: `8081`, `3307`
- (Opsional, untuk test cepat) PHP 8.2+ di host — bila tidak ada, test dijalankan dari dalam container

## Langkah instalasi

1. Masuk ke direktori proyek:
   ```bash
   cd pilketos-v2
   ```
2. Siapkan environment (pertama kali saja):
   ```bash
   cp .env.example .env
   ```
3. Jalankan layanan:
   ```bash
   docker compose up --build -d
   ```
   Layanan yang berjalan:
   | Container | Isi | Akses |
   | --- | --- | --- |
   | `pilketos-v2-app` | Aplikasi CI4 | http://localhost:8081 |
   | `pilketos-db` | MySQL 8 + seed `db_pilketos` | `localhost:3307` |
4. Tunggu ±20 detik (Apache + MySQL siap), lalu buka http://localhost:8081
5. Login admin demo: `admin` / `admin`

> Struktur + data awal database berasal dari `database/db_pilketos.sql`
> yang dimuat otomatis saat volume database pertama kali dibuat.

## Konfigurasi penting

- `.env` — `app.baseURL` menyesuaikan host akses (aplikasi juga mengikuti
  `Host` request, jadi IP LAN tetap berfungsi); `database.default.*`
  menunjuk ke service `db` (nama unik `pilketos-db` agar tak tabrakan
  dengan service lain saat gabung network eksternal).
- `docker-compose.yml` — service `app` tergabung ke network eksternal
  `network_app` agar bisa memanggil web sekolah (`http://sekolah-dev`).

## Koneksi API web sekolah

1. Di web sekolah (admin): menu **API / APP** → daftarkan aplikasi → salin token.
2. Di Pilketos (admin): **Koneksi API** → isi URL (`http://sekolah-dev` bila
   satu Docker), token, simpan → **Test Koneksi** → **Sync Data Sekolah**.
3. Guru masuk kelas khusus `Guru`; password DPT = NIS/NIP.

## Menjalankan test

```bash
# via container (disarankan, tanpa PHP di host)
docker exec pilketos-v2-app bash -lc "cd /var/www/html && vendor/bin/phpunit"
```

## Reset data

- Dashboard → **Reset Data Pemilihan** (hapus suara, DPT, calon, periode)
- Dashboard → **Reset User DPT** (buka kunci satu pemilih)

## Masalah umum

| Gejala | Penyebab / Solusi |
| --- | --- |
| `404` setelah tambah route | Auto-routing mati — daftarkan di `app/Config/Routes.php` |
| CSS tidak tampil via IP LAN | Hard refresh; aset memakai URL root-relative + `?v=` otomatis |
| Login POST `403` | Token CSRF kedaluwarsa — muat ulang form |
| `:8081` kosong/rusak | `docker compose up -d --build` ulang |
| Sync gagal koneksi | Pastikan `sekolah-dev` hidup dan satu network (`network_app`) |
