# Spesifikasi Koneksi API Sekolah untuk Pilketos

Dokumen ini adalah kontrak API yang akan dipakai nanti oleh website sekolah agar aplikasi Pilketos bisa mengambil data siswa, guru, dan kelas.

Catatan penting:
- Dokumentasi ini dibuat dari sisi Pilketos, bukan implementasi di website sekolah.
- Belum ada perubahan yang dilakukan ke website sekolah.
- Tujuan utama adalah membuat kontrak yang jelas agar integrasi nanti aman, konsisten, dan mudah dipelihara.

## 1. Tujuan

Website sekolah menyediakan endpoint API yang digunakan oleh Pilketos untuk:
- mengecek kesehatan koneksi
- mengambil daftar kelas
- mengambil daftar siswa
- mengambil daftar guru
- sinkronisasi data yang diperlukan

## 2. Autentikasi

Semua endpoint protected memakai bearer token.

Header:

```http
X-App-Name: Pilketos Admin
Authorization: Bearer <token>
Accept: application/json
```

Di sisi provider, nama aplikasi juga harus diverifikasi agar hanya client yang terdaftar yang bisa mengakses data.

Token dapat disimpan di konfigurasi aplikasi Pilketos pada halaman Koneksi API.

## 3. Endpoint kesehatan

### GET /api/connect/health

Menguji apakah API aktif dan token valid.

Response berhasil:

```json
{
  "ok": true,
  "status": "ok",
  "message": "API sekolah aktif",
  "timestamp": "2026-10-13T10:00:00+07:00"
}
```

Response error:

```json
{
  "ok": false,
  "status": "unauthorized",
  "message": "Token tidak valid"
}
```

## 4. Endpoint kelas

### GET /api/connect/kelas

Response sukses:

```json
{
  "ok": true,
  "status": "ok",
  "data": [
    {
      "id": 1,
      "kode_kelas": "7A",
      "nama_kelas": "VII-A",
      "tingkat": "VII",
      "jurusan": "-",
      "wali_kelas": "Budi Santoso"
    }
  ]
}
```

## 5. Endpoint siswa

### GET /api/connect/siswa

Response sukses:

```json
{
  "ok": true,
  "status": "ok",
  "data": [
    {
      "id": 101,
      "nis": "2024001",
      "nama": "Andi Wijaya",
      "jenis_kelamin": "L",
      "kelas_id": 1,
      "kelas_nama": "VII-A",
      "status": "aktif",
      "email": "andi@example.com"
    }
  ]
}
```

## 6. Endpoint guru

### GET /api/connect/guru

Response sukses:

```json
{
  "ok": true,
  "status": "ok",
  "data": [
    {
      "id": 201,
      "nip": "1987001",
      "nama": "Siti Rahma",
      "jabatan": "Guru Matematika",
      "mapel": "Matematika",
      "status": "aktif",
      "email": "siti@example.com"
    }
  ]
}
```

## 7. Endpoint sinkronisasi

### POST /api/connect/sync

Digunakan saat Pilketos ingin mengirimkan hasil sinkronisasi atau meminta data terbaru dari website sekolah.

Request:

```json
{
  "source": "pilketos",
  "sync_type": "full",
  "last_sync": "2026-10-13T08:30:00+07:00"
}
```

Response sukses:

```json
{
  "ok": true,
  "status": "ok",
  "message": "Sinkronisasi berhasil",
  "data": {
    "kelas": 12,
    "siswa": 540,
    "guru": 36,
    "updated_at": "2026-10-13T10:00:00+07:00"
  }
}
```

## 8. Aturan mapping data

Dari sisi Pilketos, mapping yang disarankan:

- aplikasi client: `app_name`, `token`, `status`, `scope`
- siswa: `nis`, `nama`, `kelas_id`, `status`, `email`
- guru: `nip`, `nama`, `mapel`, `status`, `email`
- kelas: `kode_kelas`, `nama_kelas`, `tingkat`, `jurusan`

## 9. Prinsip keamanan

- semua endpoint harus memakai HTTPS
- token digunakan untuk auth
- hanya data yang dibutuhkan yang dipublikasikan
- log request dan sinkronisasi harus tersedia
- respons harus memakai JSON dengan struktur konsisten

## 10. Langkah integrasi nanti

1. Website sekolah menyiapkan endpoint dengan format di atas.
2. Pilketos mengisi URL API dan token pada halaman Koneksi API.
3. Pilketos memanggil /api/connect/health untuk validasi.
4. Pilketos mengambil data kelas, siswa, dan guru.
5. Data disimpan untuk kebutuhan voting, dashboard, dan admin.

## 11. Catatan implementasi

Implementasi ini hanya mempersiapkan kontrak API. Belum ada eksekusi atau perubahan ke website sekolah.
