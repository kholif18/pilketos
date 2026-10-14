<?php

namespace App\Controllers;

class Admin extends BaseController
{
    private function defaultSchool(): array
    {
        return [
            "npsn" => "1234567",
            "nm_sekolah" => "SMP Negeri 1 Ngluyu",
            "jln" => "Jl. Mayjend. Sungkono No 35",
            "desa" => "Ngluyu",
            "kec" => "Ngluyu",
            "kab" => "Nganjuk",
            "kpl_sekolah" => "Mujiono, S.Pd., M.Si.",
            "nip" => "",
        ];
    }

    private function getSchoolData(): array
    {
        $db = db_connect();

        try {
            $school = $db->query("SELECT * FROM tb_identitassekolah LIMIT 1")->getRowArray();
        } catch (\Throwable $e) {
            return $this->defaultSchool();
        }

        return array_replace($this->defaultSchool(), $school ?? []);
    }

    private function getApiConfigPath(): string
    {
        return WRITEPATH . 'api_connector.json';
    }

    private function getApiConfig(): array
    {
        $file = $this->getApiConfigPath();

        if (! is_file($file)) {
            return [
                'app_name' => '',
                'api_url' => '',
                'api_token' => '',
                'timeout' => 10,
                'last_status' => 'Belum dikonfigurasi',
                'last_sync' => null,
            ];
        }

        $content = @file_get_contents($file);
        if ($content === false) {
            return [
                'app_name' => '',
                'api_url' => '',
                'api_token' => '',
                'timeout' => 10,
                'last_status' => 'File konfigurasi tidak dapat dibaca',
                'last_sync' => null,
            ];
        }

        $decoded = json_decode($content, true);
        if (! is_array($decoded)) {
            return [
                'app_name' => '',
                'api_url' => '',
                'api_token' => '',
                'timeout' => 10,
                'last_status' => 'Format konfigurasi tidak valid',
                'last_sync' => null,
            ];
        }

        return [
            'app_name' => $decoded['app_name'] ?? '',
            'api_url' => $decoded['api_url'] ?? '',
            'api_token' => $decoded['api_token'] ?? '',
            'timeout' => (int) ($decoded['timeout'] ?? 10),
            'last_status' => $decoded['last_status'] ?? 'Belum dikonfigurasi',
            'last_sync' => $decoded['last_sync'] ?? null,
        ];
    }

    private function saveApiConfig(array $config): bool
    {
        $file = $this->getApiConfigPath();

        $payload = json_encode($config, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        if ($payload === false) {
            return false;
        }

        return file_put_contents($file, $payload . PHP_EOL) !== false;
    }

    public function index()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $db = db_connect();

        $school = $this->getSchoolData();

        try {
            $election = $db->query("SELECT * FROM tb_datapilketos WHERE id = 1 LIMIT 1")->getRowArray();
            $stats = [
                "calon" => (int) $db->query("SELECT COUNT(*) AS total FROM tb_pilihan")->getRow()->total,
                "pemilih" => (int) $db->query("SELECT COUNT(*) AS total FROM tb_siswa")->getRow()->total,
                "sudah_memilih" => (int) $db->query("SELECT COUNT(*) AS total FROM tb_pilih")->getRow()->total,
                "kelas" => (int) $db->query("SELECT COUNT(*) AS total FROM tb_kelas")->getRow()->total,
            ];
            $calon = $db->query(
                "SELECT p.nisn, p.nama, p.photo, p.no, COUNT(v.id_pilih) AS jumlah FROM tb_pilihan p LEFT JOIN tb_pilih v ON v.nisn = p.nisn GROUP BY p.nisn, p.nama, p.photo, p.no ORDER BY p.no ASC, p.nama ASC"
            )->getResultArray();
        } catch (\Throwable $e) {
            $election = null;
            $stats = [
                "calon" => 0,
                "pemilih" => 0,
                "sudah_memilih" => 0,
                "kelas" => 0,
            ];
            $calon = [];
        }

        $pemilih = (int) ($stats["pemilih"] ?? 0);
        $sudah = (int) ($stats["sudah_memilih"] ?? 0);
        $belum = max(0, $pemilih - $sudah);
        $partisipasi = $pemilih > 0 ? round($sudah / $pemilih * 100, 1) : 0.0;

        if ($pemilih === 0) {
            $statusPemilihan = "Belum ada data";
        } elseif ($belum === 0) {
            $statusPemilihan = "Selesai";
        } elseif ($sudah > 0) {
            $statusPemilihan = "Sedang Berlangsung";
        } else {
            $statusPemilihan = "Siap";
        }

        $data = [
            "username" => session()->get("username"),
            "school" => $school,
            "election" => $election,
            "stats" => $stats,
            "calon" => $calon,
            "belum_memilih" => $belum,
            "partisipasi" => $partisipasi,
            "statusPemilihan" => $statusPemilihan,
            "success" => session()->getFlashdata("success"),
            "error" => session()->getFlashdata("error"),
        ];

        return view("admin/index", $data);
    }

    public function identitas()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $school = $this->getSchoolData();

        return view("admin/identitas", [
            "username" => session()->get("username"),
            "school" => $school,
            "success" => session()->getFlashdata("success"),
            "error" => session()->getFlashdata("error"),
        ]);
    }

    public function saveIdentitas()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $data = [
            "npsn" => trim($this->request->getPost("npsn")),
            "nm_sekolah" => trim($this->request->getPost("nm_sekolah")),
            "jln" => trim($this->request->getPost("jln")),
            "desa" => trim($this->request->getPost("desa")),
            "kec" => trim($this->request->getPost("kec")),
            "kab" => trim($this->request->getPost("kab")),
            "kpl_sekolah" => trim($this->request->getPost("kpl_sekolah")),
            "nip" => trim($this->request->getPost("nip")),
        ];

        if ($data["nm_sekolah"] === "") {
            return redirect()->back()->with("error", "Nama sekolah wajib diisi.");
        }

        $db = db_connect();

        try {
            $exists = $db->query("SELECT 1 FROM tb_identitassekolah LIMIT 1")->getRowArray();

            if ($exists) {
                $db->query(
                    "UPDATE tb_identitassekolah SET npsn = ?, nm_sekolah = ?, jln = ?, desa = ?, kec = ?, kab = ?, kpl_sekolah = ?, nip = ?",
                    [$data["npsn"], $data["nm_sekolah"], $data["jln"], $data["desa"], $data["kec"], $data["kab"], $data["kpl_sekolah"], $data["nip"]]
                );
            } else {
                $db->query(
                    "INSERT INTO tb_identitassekolah (npsn, nm_sekolah, jln, desa, kec, kab, kpl_sekolah, nip) VALUES (?, ?, ?, ?, ?, ?, ?, ?)",
                    [$data["npsn"], $data["nm_sekolah"], $data["jln"], $data["desa"], $data["kec"], $data["kab"], $data["kpl_sekolah"], $data["nip"]]
                );
            }

            return redirect()->to("/admin/identitas")->with("success", "Data identitas sekolah berhasil diperbarui.");
        } catch (\Throwable $e) {
            return redirect()->back()->with("error", "Database belum siap. Silakan import schema sekolah terlebih dahulu.");
        }
    }

    private function ensureDatapilketos($db): void
    {
        $driver = strtolower((string) ($db->DBDriver ?? ''));

        try {
            $tableExists = str_contains($driver, 'sqlite')
                ? ! empty($db->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'tb_datapilketos' LIMIT 1")->getRowArray())
                : ! empty($db->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tb_datapilketos' LIMIT 1")->getRowArray());

            if (! $tableExists) {
                if (str_contains($driver, 'sqlite')) {
                    $db->query("CREATE TABLE IF NOT EXISTS tb_datapilketos (id INTEGER PRIMARY KEY, tapel TEXT NOT NULL DEFAULT '', tgl TEXT NOT NULL DEFAULT '')");
                } else {
                    $db->query("CREATE TABLE IF NOT EXISTS tb_datapilketos (id INT NOT NULL DEFAULT 1 PRIMARY KEY, tapel VARCHAR(10) NOT NULL DEFAULT '', tgl DATE NOT NULL)");
                }
            }

            $row = $db->query("SELECT 1 FROM tb_datapilketos WHERE id = 1 LIMIT 1")->getRowArray();
            if (empty($row)) {
                $db->query("INSERT INTO tb_datapilketos (id, tapel, tgl) VALUES (1, '', '0000-00-00')");
            }
        } catch (\Throwable $e) {
            // Ignore silently so the dashboard can still render a clean empty state when the schema is missing.
        }
    }

    public function savePeriode()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $tapel = trim((string) $this->request->getPost("tapel"));
        $tgl = trim((string) $this->request->getPost("tgl"));

        if ($tapel === "") {
            return redirect()->back()->with("error", "Tahun pelajaran wajib diisi.");
        }

        if ($tgl === "" || ! preg_match('/^\d{4}-\d{2}-\d{2}$/', $tgl)) {
            return redirect()->back()->with("error", "Tanggal pemilihan wajib diisi dengan format yang benar.");
        }

        $db = db_connect();
        $this->ensureDatapilketos($db);

        try {
            $exists = $db->query("SELECT 1 FROM tb_datapilketos WHERE id = 1 LIMIT 1")->getRowArray();

            if ($exists) {
                $db->query("UPDATE tb_datapilketos SET tapel = ?, tgl = ? WHERE id = 1", [$tapel, $tgl]);
            } else {
                $db->query("INSERT INTO tb_datapilketos (id, tapel, tgl) VALUES (1, ?, ?)", [$tapel, $tgl]);
            }

            return redirect()->to("/admin")->with("success", "Periode pemilihan berhasil disimpan.");
        } catch (\Throwable $e) {
            return redirect()->back()->with("error", "Database belum siap. Silakan import schema pilketos terlebih dahulu.");
        }
    }

    public function resetPeriode()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $db = db_connect();
        $this->ensureDatapilketos($db);

        try {
            $db->query("UPDATE tb_datapilketos SET tapel = '', tgl = '' WHERE id = 1");

            return redirect()->to("/admin")->with("success", "Periode pemilihan berhasil dikosongkan.");
        } catch (\Throwable $e) {
            return redirect()->back()->with("error", "Gagal mengosongkan periode pemilihan.");
        }
    }

    public function calon()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $db = db_connect();

        try {
            $calon = $db->query("SELECT * FROM tb_pilihan ORDER BY no ASC, nama ASC")->getResultArray();
        } catch (\Throwable $e) {
            $calon = [];
        }

        return view("admin/calon", [
            "username" => session()->get("username"),
            "school" => $this->getSchoolData(),
            "calon" => $calon,
            "success" => session()->getFlashdata("success"),
            "error" => session()->getFlashdata("error"),
        ]);
    }

    public function saveCalon()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $nis = trim((string) $this->request->getPost("nis"));
        if ($nis === "") {
            $nis = trim((string) $this->request->getPost("nisn"));
        }

        $nama = trim((string) $this->request->getPost("nama"));
        $nomor = (int) $this->request->getPost("no");

        if ($nis === "" || $nama === "") {
            return redirect()->back()->with("error", "NIS dan nama calon wajib diisi.");
        }

        if (! preg_match('/^\d{4}$/', $nis)) {
            return redirect()->back()->with("error", "NIS harus 4 digit angka sesuai data sekolah.");
        }

        if ($nomor <= 0) {
            $nomor = 1;
        }

        $db = db_connect();

        try {
            $driver = strtolower((string) ($db->DBDriver ?? ''));
            $tabelAda = str_contains($driver, 'sqlite')
                ? ! empty($db->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'tb_pilihan' LIMIT 1")->getRowArray())
                : ! empty($db->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tb_pilihan' LIMIT 1")->getRowArray());

            if (! $tabelAda) {
                if (str_contains($driver, 'sqlite')) {
                    $db->query("CREATE TABLE IF NOT EXISTS tb_pilihan (nisn TEXT PRIMARY KEY, nama TEXT NOT NULL, photo TEXT NOT NULL DEFAULT 'default.jpg', no INTEGER NOT NULL DEFAULT 1)");
                } else {
                    $db->query("CREATE TABLE IF NOT EXISTS tb_pilihan (nisn VARCHAR(32) NOT NULL PRIMARY KEY, nama VARCHAR(32) NOT NULL, photo VARCHAR(32) NOT NULL DEFAULT 'default.jpg', no INT NOT NULL DEFAULT 1)");
                }
            }

            $existing = $db->query("SELECT photo FROM tb_pilihan WHERE nisn = ? LIMIT 1", [$nis])->getRowArray();
            $photo = is_array($existing) ? (string) ($existing["photo"] ?? "default.jpg") : "default.jpg";

            $file = $this->request->getFile("photo");
            if ($file && $file->isValid() && ! $file->hasMoved()) {
                $boleh = ["image/jpeg" => "jpg", "image/png" => "png", "image/gif" => "gif"];
                $mime = (string) $file->getMimeType();

                if (! isset($boleh[$mime])) {
                    return redirect()->back()->with("error", "Format foto harus JPG, PNG, atau GIF.");
                }

                if ($file->getSize() > 1024 * 1024) {
                    return redirect()->back()->with("error", "Ukuran foto maksimal 1 MB.");
                }

                $dir = FCPATH . "uploads" . DIRECTORY_SEPARATOR . "calon";
                if (! is_dir($dir)) {
                    mkdir($dir, 0755, true);
                }

                $namaFile = $nis . "." . $boleh[$mime];

                try {
                    $file->move($dir, $namaFile, true);
                } catch (\Throwable $e) {
                    return redirect()->back()->with("error", "Gagal mengunggah foto. Periksa folder uploads/calon.");
                }

                if ($photo !== "default.jpg" && $photo !== $namaFile) {
                    $lama = $dir . DIRECTORY_SEPARATOR . basename($photo);
                    if (is_file($lama)) {
                        @unlink($lama);
                    }
                }

                $photo = $namaFile;
            }

            if ($existing) {
                $db->query(
                    "UPDATE tb_pilihan SET nama = ?, photo = ?, no = ? WHERE nisn = ?",
                    [$nama, $photo, $nomor, $nis]
                );
                return redirect()->to("/admin/calon")->with("success", "Data calon berhasil diperbarui.");
            }

            $db->query(
                "INSERT INTO tb_pilihan (nisn, nama, photo, no) VALUES (?, ?, ?, ?)",
                [$nis, $nama, $photo, $nomor]
            );

            return redirect()->to("/admin/calon")->with("success", "Data calon berhasil ditambahkan.");
        } catch (\Throwable $e) {
            return redirect()->back()->with("error", "Database belum siap. Silakan import schema calon terlebih dahulu.");
        }
    }

    public function deleteCalon()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $nisn = trim((string) $this->request->getPost("nisn"));
        if ($nisn === "") {
            return redirect()->back()->with("error", "NIS calon wajib diisi.");
        }

        $db = db_connect();

        try {
            $row = $db->query("SELECT photo FROM tb_pilihan WHERE nisn = ? LIMIT 1", [$nisn])->getRowArray();
            $db->query("DELETE FROM tb_pilihan WHERE nisn = ?", [$nisn]);

            $photo = is_array($row) ? (string) ($row["photo"] ?? "default.jpg") : "default.jpg";
            if ($photo !== "default.jpg" && preg_match('/^[A-Za-z0-9._-]+$/', $photo)) {
                $lama = FCPATH . "uploads" . DIRECTORY_SEPARATOR . "calon" . DIRECTORY_SEPARATOR . $photo;
                if (is_file($lama)) {
                    @unlink($lama);
                }
            }

            return redirect()->to("/admin/calon")->with("success", "Data calon berhasil dihapus.");
        } catch (\Throwable $e) {
            return redirect()->to("/admin/calon")->with("error", "Gagal menghapus data calon.");
        }
    }

    private function ensureDefaultKelas($db): void
    {
        $driver = strtolower((string) ($db->DBDriver ?? ''));

        try {
            $tableExists = str_contains($driver, 'sqlite')
                ? ! empty($db->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'tb_kelas' LIMIT 1")->getRowArray())
                : ! empty($db->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tb_kelas' LIMIT 1")->getRowArray());

            if (! $tableExists) {
                if (str_contains($driver, 'sqlite')) {
                    $db->query('CREATE TABLE IF NOT EXISTS tb_kelas (kd_kelas INTEGER PRIMARY KEY AUTOINCREMENT, nm_kelas TEXT NOT NULL)');
                } else {
                    $db->query('CREATE TABLE IF NOT EXISTS tb_kelas (kd_kelas INT NOT NULL AUTO_INCREMENT PRIMARY KEY, nm_kelas VARCHAR(50) NOT NULL)');
                }

                $defaultKelas = ["7A", "7B", "7C", "8A", "8B", "8C", "8D", "9A", "9B", "9C", "9D"];
                foreach ($defaultKelas as $namaKelas) {
                    $db->query("INSERT INTO tb_kelas (nm_kelas) VALUES (?)", [$namaKelas]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore silently so the view can still render a clean empty state when the schema is missing.
        }
    }

    public function kelas()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $db = db_connect();
        $this->ensureDefaultKelas($db);

        try {
            $kelas = $db->query("SELECT * FROM tb_kelas ORDER BY kd_kelas ASC")->getResultArray();
        } catch (\Throwable $e) {
            $kelas = [];
        }

        return view("admin/kelas", [
            "username" => session()->get("username"),
            "school" => $this->getSchoolData(),
            "kelas" => $kelas,
            "success" => session()->getFlashdata("success"),
            "error" => session()->getFlashdata("error"),
        ]);
    }

    public function saveKelas()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $namaKelas = trim($this->request->getPost("nm_kelas"));

        if ($namaKelas === "") {
            return redirect()->back()->with("error", "Nama kelas wajib diisi.");
        }

        $db = db_connect();

        try {
            $db->query("INSERT INTO tb_kelas (nm_kelas) VALUES (?)", [$namaKelas]);
            return redirect()->to("/admin/kelas")->with("success", "Data kelas berhasil ditambahkan.");
        } catch (\Throwable $e) {
            return redirect()->back()->with("error", "Database belum siap. Silakan import schema kelas terlebih dahulu.");
        }
    }

    public function deleteKelas()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $kdKelas = (int) $this->request->getPost("kd_kelas");
        if ($kdKelas <= 0) {
            return redirect()->back()->with("error", "Kode kelas wajib diisi.");
        }

        $db = db_connect();

        try {
            $db->query("DELETE FROM tb_kelas WHERE kd_kelas = ?", [$kdKelas]);
            $this->resetKelasAutoIncrement($db);
            return redirect()->to("/admin/kelas")->with("success", "Data kelas berhasil dihapus.");
        } catch (\Throwable $e) {
            return redirect()->to("/admin/kelas")->with("error", "Gagal menghapus data kelas.");
        }
    }

    public function bulkDeleteKelas()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $selected = $this->request->getPost('kd_kelas');
        if (empty($selected) || ! is_array($selected)) {
            return redirect()->back()->with('error', 'Pilih minimal satu kelas untuk dihapus.');
        }

        $ids = [];
        foreach ($selected as $value) {
            $id = (int) $value;
            if ($id > 0) {
                $ids[] = $id;
            }
        }

        if ($ids === []) {
            return redirect()->back()->with('error', 'Pilih minimal satu kelas untuk dihapus.');
        }

        $db = db_connect();

        try {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));
            $db->query("DELETE FROM tb_kelas WHERE kd_kelas IN ($placeholders)", $ids);
            $this->resetKelasAutoIncrement($db);
            return redirect()->to("/admin/kelas")->with("success", "Data kelas terpilih berhasil dihapus.");
        } catch (\Throwable $e) {
            return redirect()->to("/admin/kelas")->with("error", "Gagal menghapus data kelas terpilih.");
        }
    }

    private function resetKelasAutoIncrement($db): void
    {
        $driver = strtolower((string) ($db->DBDriver ?? ''));

        try {
            if (str_contains($driver, 'sqlite')) {
                $db->query("DELETE FROM sqlite_sequence WHERE name = 'tb_kelas'");
                return;
            }

            $db->query('ALTER TABLE tb_kelas AUTO_INCREMENT = 1');
        } catch (\Throwable $e) {
            // Ignore silently; the table can still be used without forcing the counter.
        }
    }

    private function normalizeSchoolRows(array $rows): array
    {
        $items = [];

        foreach ($rows as $row) {
            if (! is_array($row)) {
                continue;
            }

            $items[] = $row;
        }

        return $items;
    }

    private function resolveClassIdFromName(?string $className): ?int
    {
        $className = trim((string) ($className ?? ''));
        if ($className === '') {
            return null;
        }

        $db = db_connect();

        try {
            $existing = $db->query("SELECT kd_kelas FROM tb_kelas WHERE nm_kelas = ? LIMIT 1", [$className])->getRowArray();
            if ($existing) {
                return (int) ($existing['kd_kelas'] ?? 0);
            }

            $db->query("INSERT INTO tb_kelas (nm_kelas) VALUES (?)", [$className]);
            return (int) $db->insertID();
        } catch (\Throwable $e) {
            return null;
        }
    }

    public function siswa()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $db = db_connect();

        try {
            $kelas = $db->query("SELECT * FROM tb_kelas ORDER BY kd_kelas ASC")->getResultArray();
            $siswa = $db->query(
                "SELECT s.username, s.nm_siswa, s.password, s.jk, s.kd_kelas, s.hadir, k.nm_kelas FROM tb_siswa s LEFT JOIN tb_kelas k ON k.kd_kelas = s.kd_kelas ORDER BY k.kd_kelas ASC, s.nm_siswa ASC"
            )->getResultArray();
        } catch (\Throwable $e) {
            $kelas = [];
            $siswa = [];
        }

        return view("admin/siswa", [
            "username" => session()->get("username"),
            "school" => $this->getSchoolData(),
            "kelas" => $kelas,
            "siswa" => $siswa,
            "success" => session()->getFlashdata("success"),
            "error" => session()->getFlashdata("error"),
        ]);
    }

    public function saveSiswa()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $username = trim((string) $this->request->getPost("username"));
        $password = trim((string) $this->request->getPost("password"));
        $nama = trim((string) $this->request->getPost("nm_siswa"));
        $jk = strtoupper(trim((string) $this->request->getPost("jk")));
        $kdKelas = $this->request->getPost("kd_kelas");

        if ($username === '' || $nama === '') {
            return redirect()->back()->with("error", "Username dan nama siswa wajib diisi.");
        }

        if (! preg_match('/^\d{4}$/', $username)) {
            return redirect()->back()->with("error", "NIS harus 4 digit angka sesuai data sekolah.");
        }

        $db = db_connect();

        try {
            $kelasId = $kdKelas !== null && $kdKelas !== '' ? (int) $kdKelas : null;

            $existing = $db->query("SELECT password FROM tb_siswa WHERE username = ? LIMIT 1", [$username])->getRowArray();
            if ($existing) {
                $password = $password === '' ? (string) ($existing['password'] ?? $username) : $password;
                $db->query(
                    "UPDATE tb_siswa SET password = ?, nm_siswa = ?, jk = ?, kd_kelas = ? WHERE username = ?",
                    [$password, $nama, in_array($jk, ['L', 'P'], true) ? $jk : 'L', $kelasId, $username]
                );
            } else {
                $db->query(
                    "INSERT INTO tb_siswa (username, password, nm_siswa, jk, kd_kelas, hadir) VALUES (?, ?, ?, ?, ?, 'Tidak Hadir')",
                    [$username, $password === '' ? $username : $password, $nama, in_array($jk, ['L', 'P'], true) ? $jk : 'L', $kelasId]
                );
            }

            return redirect()->to("/admin/siswa")->with("success", "Data siswa berhasil disimpan.");
        } catch (\Throwable $e) {
            return redirect()->back()->with("error", "Gagal menyimpan data siswa. Pastikan tabel tb_siswa dan tb_kelas sudah tersedia.");
        }
    }

    public function deleteSiswa()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $username = trim((string) $this->request->getPost("username"));
        if ($username === "") {
            return redirect()->back()->with("error", "Username siswa wajib diisi.");
        }

        $db = db_connect();

        try {
            $db->query("DELETE FROM tb_siswa WHERE username = ?", [$username]);
            return redirect()->to("/admin/siswa")->with("success", "Data siswa berhasil dihapus.");
        } catch (\Throwable $e) {
            return redirect()->to("/admin/siswa")->with("error", "Gagal menghapus data siswa.");
        }
    }

    private function ensureSiswaTable($db): void
    {
        $driver = strtolower((string) ($db->DBDriver ?? ''));

        try {
            $tableExists = str_contains($driver, 'sqlite')
                ? ! empty($db->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'tb_siswa' LIMIT 1")->getRowArray())
                : ! empty($db->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tb_siswa' LIMIT 1")->getRowArray());

            if (! $tableExists) {
                if (str_contains($driver, 'sqlite')) {
                    $db->query("CREATE TABLE IF NOT EXISTS tb_siswa (username TEXT PRIMARY KEY, password TEXT NOT NULL, nm_siswa TEXT, jk TEXT NOT NULL DEFAULT 'L', kd_kelas INTEGER, hadir TEXT NOT NULL DEFAULT 'Tidak Hadir')");
                } else {
                    $db->query("CREATE TABLE IF NOT EXISTS tb_siswa (username VARCHAR(32) NOT NULL PRIMARY KEY, password VARCHAR(32) NOT NULL, nm_siswa VARCHAR(32), jk CHAR(1) NOT NULL DEFAULT 'L', kd_kelas INT, hadir VARCHAR(12) NOT NULL DEFAULT 'Tidak Hadir')");
                }
            }
        } catch (\Throwable $e) {
            // Ignore silently so the import can still report per-row errors when the schema is missing.
        }
    }

    private function ensureLogSyncTable($db): void
    {
        $driver = strtolower((string) ($db->DBDriver ?? ''));

        try {
            $tableExists = str_contains($driver, 'sqlite')
                ? ! empty($db->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'tb_log_sync' LIMIT 1")->getRowArray())
                : ! empty($db->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tb_log_sync' LIMIT 1")->getRowArray());

            if (! $tableExists) {
                if (str_contains($driver, 'sqlite')) {
                    $db->query("CREATE TABLE IF NOT EXISTS tb_log_sync (id INTEGER PRIMARY KEY AUTOINCREMENT, waktu TEXT NOT NULL, sumber TEXT NOT NULL, jumlah INTEGER NOT NULL DEFAULT 0, gagal INTEGER NOT NULL DEFAULT 0, pesan TEXT, oleh TEXT)");
                } else {
                    $db->query("CREATE TABLE IF NOT EXISTS tb_log_sync (id INT NOT NULL AUTO_INCREMENT PRIMARY KEY, waktu DATETIME NOT NULL, sumber VARCHAR(20) NOT NULL, jumlah INT NOT NULL DEFAULT 0, gagal INT NOT NULL DEFAULT 0, pesan VARCHAR(255), oleh VARCHAR(50))");
                }
            }
        } catch (\Throwable $e) {
            // Ignore silently so sync flows keep working when logging is unavailable.
        }
    }

    private function catatLogSync($db, string $sumber, int $jumlah, int $gagal, string $pesan): void
    {
        try {
            $db->query(
                "INSERT INTO tb_log_sync (waktu, sumber, jumlah, gagal, pesan, oleh) VALUES (?, ?, ?, ?, ?, ?)",
                [date("Y-m-d H:i:s"), $sumber, $jumlah, $gagal, mb_substr($pesan, 0, 250), (string) session()->get("username")]
            );
        } catch (\Throwable $e) {
            // Logging must never break the main flow.
        }
    }

    public function templateSiswa()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $csv = "nis,nama,jk,kelas\n1234,Budi Santoso,L,7A\n1235,Siti Aminah,P,7A\n";

        return $this->response
            ->setHeader("Content-Type", "text/csv")
            ->setHeader("Content-Disposition", 'attachment; filename="template-siswa.csv"')
            ->setBody($csv);
    }

    public function importSiswa()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $file = $this->request->getFile("file_csv");

        if (! $file || ! $file->isValid() || $file->hasMoved()) {
            return redirect()->back()->with("error", "Pilih berkas CSV terlebih dahulu.");
        }

        $ext = strtolower((string) pathinfo((string) $file->getClientName(), PATHINFO_EXTENSION));
        if (! in_array($ext, ["csv", "txt"], true)) {
            return redirect()->back()->with("error", "Format berkas harus CSV.");
        }

        if ($file->getSize() > 2 * 1024 * 1024) {
            return redirect()->back()->with("error", "Ukuran berkas maksimal 2 MB.");
        }

        $db = db_connect();
        $this->ensureDefaultKelas($db);
        $this->ensureSiswaTable($db);
        $this->ensureLogSyncTable($db);

        $handle = @fopen((string) $file->getTempName(), "r");
        if ($handle === false) {
            return redirect()->back()->with("error", "Berkas tidak dapat dibaca.");
        }

        $barisMentah = fgets($handle);
        if ($barisMentah === false) {
            fclose($handle);
            return redirect()->back()->with("error", "Berkas CSV kosong.");
        }

        $delimiter = str_contains($barisMentah, ";") ? ";" : ",";
        rewind($handle);

        $ok = 0;
        $lewat = 0;
        $contohSalah = [];
        $nomorBaris = 0;

        while (($kolom = fgetcsv($handle, 0, $delimiter)) !== false) {
            $nomorBaris++;
            $kolom = array_map(static fn($v) => trim((string) $v), $kolom);

            if ($nomorBaris === 1) {
                $kolom[0] = preg_replace('/^\xEF\xBB\xBF/', '', $kolom[0] ?? '');
                if (in_array(strtolower($kolom[0] ?? ''), ['nis', 'username', 'nisn', 'no'], true)) {
                    continue;
                }
            }

            $username = $kolom[0] ?? '';
            $nama = $kolom[1] ?? '';
            $jk = strtoupper($kolom[2] ?? 'L');
            $kelasNama = $kolom[3] ?? '';

            if ($username === '' && $nama === '') {
                continue;
            }

            if (! preg_match('/^\d{4}$/', $username) || $nama === '') {
                $lewat++;
                if (count($contohSalah) < 5) {
                    $contohSalah[] = "baris " . $nomorBaris . ": NIS/nama tidak valid";
                }
                continue;
            }

            try {
                $kelasId = $kelasNama !== '' ? $this->resolveClassIdFromName($kelasNama) : null;

                $existing = $db->query("SELECT 1 FROM tb_siswa WHERE username = ? LIMIT 1", [$username])->getRowArray();
                if ($existing) {
                    $db->query(
                        "UPDATE tb_siswa SET password = ?, nm_siswa = ?, jk = ?, kd_kelas = ? WHERE username = ?",
                        [$username, $nama, in_array($jk, ['L', 'P'], true) ? $jk : 'L', $kelasId, $username]
                    );
                } else {
                    $db->query(
                        "INSERT INTO tb_siswa (username, password, nm_siswa, jk, kd_kelas, hadir) VALUES (?, ?, ?, ?, ?, 'Tidak Hadir')",
                        [$username, $username, $nama, in_array($jk, ['L', 'P'], true) ? $jk : 'L', $kelasId]
                    );
                }

                $ok++;
            } catch (\Throwable $e) {
                $lewat++;
                if (count($contohSalah) < 5) {
                    $contohSalah[] = "baris " . $nomorBaris . ": gagal disimpan";
                }
            }
        }

        fclose($handle);

        if ($ok === 0) {
            $pesan = "Tidak ada data yang diimpor.";
            if ($contohSalah !== []) {
                $pesan .= " Contoh: " . implode("; ", $contohSalah) . ".";
            }
            $this->catatLogSync($db, 'csv', 0, $lewat, mb_substr($pesan, 0, 250));
            return redirect()->back()->with("error", $pesan);
        }

        $pesan = "Impor selesai: " . $ok . " data disimpan.";
        if ($lewat > 0) {
            $pesan .= " " . $lewat . " baris dilewati.";
            if ($contohSalah !== []) {
                $pesan .= " Contoh: " . implode("; ", $contohSalah) . ".";
            }
        }

        $this->catatLogSync($db, 'csv', $ok, $lewat, mb_substr($pesan, 0, 250));

        return redirect()->to("/admin/siswa")->with("success", $pesan);
    }

    private function upsertDptRow($db, string $username, string $nama, string $jk, ?int $kelasId): void
    {
        $jk = in_array($jk, ['L', 'P'], true) ? $jk : 'L';

        $existing = $db->query("SELECT 1 FROM tb_siswa WHERE username = ? LIMIT 1", [$username])->getRowArray();
        if ($existing) {
            $db->query(
                "UPDATE tb_siswa SET password = ?, nm_siswa = ?, jk = ?, kd_kelas = ? WHERE username = ?",
                [$username, $nama, $jk, $kelasId, $username]
            );
        } else {
            $db->query(
                "INSERT INTO tb_siswa (username, password, nm_siswa, jk, kd_kelas, hadir) VALUES (?, ?, ?, ?, ?, 'Tidak Hadir')",
                [$username, $username, $nama, $jk, $kelasId]
            );
        }
    }

    public function syncSiswaFromApi()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $config = $this->getApiConfig();
        if (empty($config['api_url'])) {
            return redirect()->back()->with("error", "URL API sekolah belum dikonfigurasi.");
        }

        $client = new \App\Services\SchoolApiClient($config['api_url'], $config['api_token'] ?? '', (int) ($config['timeout'] ?? 10));
        $result = $client->fetchSiswa();

        if (($result['ok'] ?? false) !== true || ! is_array($result['data'] ?? null)) {
            return redirect()->back()->with("error", "Gagal mengambil data siswa dari API sekolah. " . ($result['message'] ?? 'Cek endpoint dan token API.'));
        }

        $inserted = 0;
        $gagal = 0;
        $db = db_connect();
        $this->ensureLogSyncTable($db);

        foreach ($this->normalizeSchoolRows($result['data']) as $row) {
            $username = trim((string) ($row['username'] ?? $row['nis'] ?? $row['nisn'] ?? $row['NIS'] ?? ''));
            $nama = trim((string) ($row['nm_siswa'] ?? $row['nama'] ?? $row['name'] ?? $row['full_name'] ?? ''));
            if ($username === '' || $nama === '') {
                $gagal++;
                continue;
            }

            $kelasNama = trim((string) ($row['kelas'] ?? $row['nm_kelas'] ?? $row['kelas_nama'] ?? ''));
            $kelasId = null;
            if ($kelasNama !== '') {
                $kelasId = $this->resolveClassIdFromName($kelasNama);
            }

            $jk = strtoupper(trim((string) ($row['jk'] ?? $row['jenis_kelamin'] ?? 'L')));

            try {
                $this->upsertDptRow($db, $username, $nama, $jk, $kelasId);
                $inserted++;
            } catch (\Throwable $e) {
                $gagal++;
            }
        }

        $this->catatLogSync($db, 'api-siswa', $inserted, $gagal, $inserted . ' diperbarui, ' . $gagal . ' dilewati');

        return redirect()->to("/admin/siswa")->with("success", "Sinkronisasi data siswa berhasil: " . $inserted . " data diperbarui.");
    }

    public function syncSchoolData()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $config = $this->getApiConfig();
        if (empty($config['api_url'])) {
            return redirect()->back()->with("error", "URL API sekolah belum dikonfigurasi.");
        }

        try {
            $client = new \App\Services\SchoolApiClient($config['api_url'], $config['api_token'] ?? '', (int) ($config['timeout'] ?? 10));
            $result = $client->sync();

            if (($result['ok'] ?? false) !== true) {
                return redirect()->back()->with("error", "Sinkronisasi API sekolah gagal. " . ($result['error'] ?? 'Cek koneksi dan token API.'));
            }

            $classList = $result['responses']['kelas']['data'] ?? [];
            $studentList = $result['responses']['siswa']['data'] ?? [];
            $guruList = $result['responses']['guru']['data'] ?? [];

            $db = db_connect();
            $this->ensureLogSyncTable($db);

            foreach ($this->normalizeSchoolRows($classList) as $row) {
                $kelasNama = trim((string) ($row['nm_kelas'] ?? $row['nama_kelas'] ?? $row['kelas'] ?? ''));
                if ($kelasNama !== '') {
                    $this->resolveClassIdFromName($kelasNama);
                }
            }

            $jmlSiswa = 0;
            $lewat = 0;

            foreach ($this->normalizeSchoolRows($studentList) as $row) {
                $username = trim((string) ($row['username'] ?? $row['nis'] ?? $row['nisn'] ?? $row['NIS'] ?? ''));
                $nama = trim((string) ($row['nm_siswa'] ?? $row['nama'] ?? $row['name'] ?? $row['full_name'] ?? ''));
                if ($username === '' || $nama === '') {
                    $lewat++;
                    continue;
                }

                $kelasNama = trim((string) ($row['kelas'] ?? $row['nm_kelas'] ?? $row['kelas_nama'] ?? ''));
                $kelasId = $kelasNama !== '' ? $this->resolveClassIdFromName($kelasNama) : null;
                $jk = strtoupper(trim((string) ($row['jk'] ?? $row['jenis_kelamin'] ?? 'L')));

                try {
                    $this->upsertDptRow($db, $username, $nama, $jk, $kelasId);
                    $jmlSiswa++;
                } catch (\Throwable $ignored) {
                    $lewat++;
                }
            }

            // Guru dikelompokkan ke kelas khusus agar terbedakan dari siswa.
            $kelasGuruId = $this->resolveClassIdFromName('Guru');
            $jmlGuru = 0;

            foreach ($this->normalizeSchoolRows($guruList) as $row) {
                $username = trim((string) ($row['username'] ?? $row['nip'] ?? $row['NIP'] ?? $row['kode_guru'] ?? ''));
                $nama = trim((string) ($row['nm_siswa'] ?? $row['nama'] ?? $row['name'] ?? $row['full_name'] ?? ''));
                if ($username === '' || $nama === '') {
                    $lewat++;
                    continue;
                }

                $jk = strtoupper(trim((string) ($row['jk'] ?? $row['jenis_kelamin'] ?? 'L')));

                try {
                    $this->upsertDptRow($db, $username, $nama, $jk, $kelasGuruId);
                    $jmlGuru++;
                } catch (\Throwable $ignored) {
                    $lewat++;
                }
            }

            $this->catatLogSync($db, 'api-sekolah', $jmlSiswa + $jmlGuru, $lewat, $jmlSiswa . ' siswa, ' . $jmlGuru . ' guru, ' . $lewat . ' dilewati');

            return redirect()->to("/admin/connect-api")->with("success", "Sinkronisasi selesai: " . $jmlSiswa . " siswa dan " . $jmlGuru . " guru diperbarui.");
        } catch (\Throwable $e) {
            try {
                $dbLog = db_connect();
                $this->ensureLogSyncTable($dbLog);
                $this->catatLogSync($dbLog, 'api-sekolah', 0, 0, 'Gagal: ' . mb_substr($e->getMessage(), 0, 200));
            } catch (\Throwable $ignored) {
            }

            return redirect()->back()->with("error", "Sinkronisasi gagal: " . $e->getMessage());
        }
    }

    private function pdfText(string $s): string
    {
        $s = trim((string) $s);
        if (function_exists('iconv')) {
            $conv = @iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $s);
            if ($conv !== false) {
                $s = $conv;
            }
        }

        return str_replace(['\\', '(', ')'], ['\\\\', '\\(', '\\)'], $s);
    }

    private function pdfFit(string $s, float $maxWidth, int $size, bool $bold = false): string
    {
        $s = $this->pdfText($s);
        $perChar = $size * ($bold ? 0.6 : 0.55);
        $maxChars = max(1, (int) floor($maxWidth / $perChar));

        if (strlen($s) > $maxChars) {
            if ($maxChars <= 3) {
                return substr($s, 0, $maxChars);
            }
            return substr($s, 0, $maxChars - 3) . '...';
        }

        return $s;
    }

    private function pdfCenterX(string $s, int $size, bool $bold = false): float
    {
        return 297.5 - (strlen($s) * $size * ($bold ? 0.6 : 0.55) / 2);
    }

    /**
     * @param array<int, array{label: string, width: int, align: string}> $columns
     * @param array<int, array<int, string>> $rows
     */
    private function buildPdfTable(string $title, array $info, array $columns, array $rows, array $footer, int $perPage = 24): string
    {
        $chunks = array_chunk(array_values($rows), $perPage);
        if ($chunks === []) {
            $chunks = [[]];
        }

        $pageCount = count($chunks);
        $kids = [];
        for ($i = 0; $i < $pageCount; $i++) {
            $kids[] = (4 + $i * 2) . ' 0 R';
        }
        $fontReg = 3 + $pageCount * 2;
        $fontBold = 4 + $pageCount * 2;

        $x0 = 50;
        $edges = [$x0];
        foreach ($columns as $col) {
            $edges[] = end($edges) + (int) $col['width'];
        }
        $tableRight = end($edges);

        $objects = [];
        $objects[] = '<< /Type /Catalog /Pages 2 0 R >>';
        $objects[] = '<< /Type /Pages /Kids [' . implode(' ', $kids) . '] /Count ' . $pageCount . ' >>';

        foreach ($chunks as $pi => $chunk) {
            $c = '';
            $t = $this->pdfFit($title, 495, 18, true);
            $c .= 'BT /F1 18 Tf ' . number_format($this->pdfCenterX($t, 18, true), 1, '.', '') . ' 790 Td (' . $t . ') Tj ET' . "\n";

            $top = 758;
            if ($pi === 0) {
                $y = 764;
                foreach ($info as $line) {
                    $t = $this->pdfFit($line, 495, 11);
                    $c .= 'BT /F1 11 Tf 50 ' . $y . ' Td (' . $t . ') Tj ET' . "\n";
                    $y -= 15;
                }
                $top = $y - 12;
            }

            $rowH = 20;
            $headH = 22;
            $bottom = $top - $headH - count($chunk) * $rowH;

            $c .= '0.92 0.92 0.92 rg ' . $x0 . ' ' . ($top - $headH) . ' ' . ($tableRight - $x0) . ' ' . $headH . " re f\n";
            foreach ($chunk as $ri => $row) {
                if ($ri % 2 === 1) {
                    $ry = $top - $headH - ($ri + 1) * $rowH;
                    $c .= '0.96 0.96 0.96 rg ' . $x0 . ' ' . $ry . ' ' . ($tableRight - $x0) . ' ' . $rowH . " re f\n";
                }
            }

            $c .= "0.7 0.7 0.7 RG 0.8 w\n";
            foreach ($edges as $ex) {
                $c .= $ex . ' ' . $top . ' m ' . $ex . ' ' . $bottom . " l S\n";
            }
            $c .= $x0 . ' ' . $top . ' m ' . $tableRight . ' ' . $top . " l S\n";
            for ($r = 0; $r <= count($chunk); $r++) {
                $ly = $top - $headH - $r * $rowH;
                $c .= $x0 . ' ' . $ly . ' m ' . $tableRight . ' ' . $ly . " l S\n";
            }

            foreach ($columns as $ci => $col) {
                $t = $this->pdfFit((string) $col['label'], (int) $col['width'] - 12, 11, true);
                $w = strlen($t) * 11 * 0.6;
                $tx = ($col['align'] ?? 'left') === 'center'
                    ? $edges[$ci] + ((int) $col['width'] - $w) / 2
                    : $edges[$ci] + 6;
                $c .= 'BT /F2 11 Tf 0 0 0 rg ' . number_format($tx, 1, '.', '') . ' ' . ($top - 15) . ' Td (' . $t . ') Tj ET' . "\n";
            }

            foreach (array_values($chunk) as $ri => $row) {
                $baseline = $top - $headH - $ri * $rowH - 14;
                foreach ($columns as $ci => $col) {
                    $t = $this->pdfFit((string) ($row[$ci] ?? '-'), (int) $col['width'] - 12, 11);
                    $w = strlen($t) * 11 * 0.55;
                    $align = $col['align'] ?? 'left';
                    if ($align === 'center') {
                        $tx = $edges[$ci] + ((int) $col['width'] - $w) / 2;
                    } elseif ($align === 'right') {
                        $tx = $edges[$ci + 1] - $w - 6;
                    } else {
                        $tx = $edges[$ci] + 6;
                    }
                    $c .= 'BT /F1 11 Tf 0 0 0 rg ' . number_format($tx, 1, '.', '') . ' ' . $baseline . ' Td (' . $t . ') Tj ET' . "\n";
                }
            }

            if ($pi === $pageCount - 1) {
                $fy = 150;
                foreach ($footer as $line) {
                    $t = $this->pdfFit($line, 220, 11);
                    $c .= 'BT /F1 11 Tf 0 0 0 rg 350 ' . $fy . ' Td (' . $t . ') Tj ET' . "\n";
                    $fy -= 16;
                }
            }

            $pg = 'Halaman ' . ($pi + 1) . ' dari ' . $pageCount;
            $c .= 'BT /F1 9 Tf 0.45 0.45 0.45 rg ' . number_format($this->pdfCenterX($pg, 9), 1, '.', '') . ' 36 Td (' . $pg . ') Tj ET' . "\n";

            $objects[] = '<< /Length ' . strlen($c) . " >>\nstream\n" . $c . 'endstream';
            $contentNum = count($objects);
            $objects[] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Contents ' . $contentNum . ' 0 R /Resources << /Font << /F1 ' . $fontReg . ' 0 R /F2 ' . $fontBold . ' 0 R >> >> >>';
        }

        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica >>';
        $objects[] = '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold >>';

        $pdf = "%PDF-1.4\n";
        $offsets = [];
        foreach ($objects as $i => $object) {
            $offsets[$i + 1] = strlen($pdf);
            $pdf .= ($i + 1) . " 0 obj\n" . $object . "\nendobj\n";
        }

        $xrefPosition = strlen($pdf);
        $pdf .= 'xref' . "\n" . '0 ' . (count($objects) + 1) . "\n" . "0000000000 65535 f \n";
        for ($i = 1; $i <= count($objects); $i++) {
            $pdf .= sprintf("%010d 00000 n \n", $offsets[$i]);
        }

        $pdf .= 'trailer' . "\n" . '<< /Size ' . (count($objects) + 1) . ' /Root 1 0 R >>' . "\n" . 'startxref' . "\n" . $xrefPosition . "\n%%EOF";

        return $pdf;
    }

    public function laporanPdf()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $db = db_connect();

        try {
            $hasilVote = $db->query(
                "SELECT p.no, p.nama, COUNT(v.id_pilih) AS jumlah FROM tb_pilihan p LEFT JOIN tb_pilih v ON v.nisn = p.nisn GROUP BY p.nisn, p.no, p.nama ORDER BY p.no ASC"
            )->getResultArray();

            $totalPemilih = (int) $db->query("SELECT COUNT(*) AS total FROM tb_siswa")->getRow()->total;
            $totalSudahVote = (int) $db->query("SELECT COUNT(*) AS total FROM tb_pilih")->getRow()->total;
            $totalBelumVote = max(0, $totalPemilih - $totalSudahVote);
            $election = $db->query("SELECT * FROM tb_datapilketos WHERE id = 1 LIMIT 1")->getRowArray();
        } catch (\Throwable $e) {
            $hasilVote = [];
            $totalPemilih = 0;
            $totalSudahVote = 0;
            $totalBelumVote = 0;
            $election = null;
        }

        $school = $this->getSchoolData();

        $rows = [];
        foreach ($hasilVote as $item) {
            $rows[] = [(string) ($item['no'] ?? '-'), (string) ($item['nama'] ?? '-'), (string) ($item['jumlah'] ?? 0)];
        }

        $pdf = $this->buildPdfTable(
            'Laporan Pemilihan Ketua OSIS',
            [
                'Tahun Pelajaran: ' . (is_array($election) ? ($election['tapel'] ?? '-') : '-'),
                'Tanggal: ' . (is_array($election) && ! empty($election['tgl']) && $election['tgl'] !== '0000-00-00' ? $election['tgl'] : date('d-m-Y')),
                'DPT: ' . $totalPemilih . ' - Sudah Vote: ' . $totalSudahVote . ' - Belum Vote: ' . $totalBelumVote,
            ],
            [
                ['label' => 'No', 'width' => 50, 'align' => 'center'],
                ['label' => 'Nama Kandidat', 'width' => 275, 'align' => 'left'],
                ['label' => 'Jumlah Suara', 'width' => 170, 'align' => 'center'],
            ],
            $rows,
            [
                ($school['desa'] ?? '') . ', ' . date('d-m-Y'),
                'Kepala Sekolah',
                '',
                '',
                '',
                $school['kpl_sekolah'] ?? '-',
                'NIP: ' . ($school['nip'] ?? '-'),
            ]
        );

        return $this->response
            ->setHeader('Content-Type', 'application/pdf')
            ->setHeader('Content-Disposition', 'attachment; filename="laporan-pemilihan-' . date('YmdHis') . '.pdf"')
            ->setBody($pdf);
    }

    public function daftarHadir()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $db = db_connect();

        try {
            $daftarHadir = $db->query(
                "SELECT s.username AS nis, s.nm_siswa, k.nm_kelas, s.hadir FROM tb_siswa s LEFT JOIN tb_kelas k ON k.kd_kelas = s.kd_kelas ORDER BY k.kd_kelas ASC, s.nm_siswa ASC"
            )->getResultArray();

            $hadir = (int) $db->query("SELECT COUNT(*) AS total FROM tb_siswa WHERE hadir = 'Hadir'")->getRow()->total;
            $tidakHadir = (int) $db->query("SELECT COUNT(*) AS total FROM tb_siswa WHERE hadir != 'Hadir'")->getRow()->total;
        } catch (\Throwable $e) {
            $daftarHadir = [];
            $hadir = 0;
            $tidakHadir = 0;
        }

        return view("admin/daftarhadir", [
            "username" => session()->get("username"),
            "school" => $this->getSchoolData(),
            "daftarHadir" => $daftarHadir,
            "hadir" => $hadir,
            "tidakHadir" => $tidakHadir,
            "success" => session()->getFlashdata("success"),
            "error" => session()->getFlashdata("error"),
        ]);
    }

    public function daftarHadirPdf()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $school = $this->getSchoolData();
        $db = db_connect();

        try {
            $daftarHadir = $db->query(
                "SELECT s.username AS nis, s.nm_siswa, k.nm_kelas, s.hadir FROM tb_siswa s LEFT JOIN tb_kelas k ON k.kd_kelas = s.kd_kelas ORDER BY k.kd_kelas ASC, s.nm_siswa ASC"
            )->getResultArray();
        } catch (\Throwable $e) {
            $daftarHadir = [];
        }

        $hadirCount = count(array_filter($daftarHadir, static fn($r) => ($r["hadir"] ?? "") === "Hadir"));
        $tidakCount = count($daftarHadir) - $hadirCount;

        $rows = [];
        foreach ($daftarHadir as $index => $item) {
            $rows[] = [
                (string) ($index + 1),
                (string) ($item["nis"] ?? "-"),
                (string) ($item["nm_siswa"] ?? "-"),
                (string) ($item["nm_kelas"] ?? "-"),
                (string) ($item["hadir"] ?? "Tidak Hadir"),
            ];
        }

        $pdf = $this->buildPdfTable(
            "Daftar Hadir Pemilihan Ketua OSIS",
            [
                ($school["nm_sekolah"] ?? "") . " - Tanggal: " . date("d-m-Y"),
                "Hadir: " . $hadirCount . " - Tidak Hadir: " . $tidakCount,
            ],
            [
                ["label" => "No", "width" => 35, "align" => "center"],
                ["label" => "NIS", "width" => 85, "align" => "center"],
                ["label" => "Nama", "width" => 195, "align" => "left"],
                ["label" => "Kelas", "width" => 70, "align" => "center"],
                ["label" => "Keterangan", "width" => 110, "align" => "center"],
            ],
            $rows,
            [
                ($school["desa"] ?? "") . ", " . date("d-m-Y"),
                "Kepala Sekolah",
                "",
                "",
                "",
                $school["kpl_sekolah"] ?? "-",
                "NIP: " . ($school["nip"] ?? "-"),
            ]
        );

        return $this->response
            ->setHeader("Content-Type", "application/pdf")
            ->setHeader("Content-Disposition", 'attachment; filename="daftar-hadir-' . date("YmdHis") . '.pdf"')
            ->setBody($pdf);
    }

    public function hasilVote()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $db = db_connect();

        try {
            $hasilVote = $db->query(
                "SELECT p.no, p.nama, COUNT(v.id_pilih) AS jumlah FROM tb_pilihan p LEFT JOIN tb_pilih v ON v.nisn = p.nisn GROUP BY p.nisn, p.no, p.nama ORDER BY p.no ASC"
            )->getResultArray();

            $jmlPemilih = (int) $db->query("SELECT COUNT(*) AS total FROM tb_siswa")->getRow()->total;
            $jmlVote = (int) $db->query("SELECT COUNT(*) AS total FROM tb_pilih")->getRow()->total;
        } catch (\Throwable $e) {
            $hasilVote = [];
            $jmlPemilih = 0;
            $jmlVote = 0;
        }

        return view("admin/hasilvote", [
            "username" => session()->get("username"),
            "school" => $this->getSchoolData(),
            "hasilVote" => $hasilVote,
            "jmlPemilih" => $jmlPemilih,
            "jmlVote" => $jmlVote,
            "success" => session()->getFlashdata("success"),
            "error" => session()->getFlashdata("error"),
        ]);
    }

    public function hasilVoteData()
    {
        if (! session()->get("username")) {
            return $this->response->setStatusCode(401)->setJSON([
                "ok" => false,
                "message" => "Sesi berakhir. Silakan login kembali.",
            ]);
        }

        $db = db_connect();

        try {
            $rows = $db->query(
                "SELECT p.no, p.nama, COUNT(v.id_pilih) AS jumlah FROM tb_pilihan p LEFT JOIN tb_pilih v ON v.nisn = p.nisn GROUP BY p.nisn, p.no, p.nama ORDER BY p.no ASC"
            )->getResultArray();

            $jmlPemilih = (int) $db->query("SELECT COUNT(*) AS total FROM tb_siswa")->getRow()->total;
            $jmlVote = (int) $db->query("SELECT COUNT(*) AS total FROM tb_pilih")->getRow()->total;
        } catch (\Throwable $e) {
            $rows = [];
            $jmlPemilih = 0;
            $jmlVote = 0;
        }

        $hasil = [];
        foreach ($rows as $row) {
            $hasil[] = [
                "no" => (string) ($row["no"] ?? "-"),
                "nama" => (string) ($row["nama"] ?? "-"),
                "jumlah" => (int) ($row["jumlah"] ?? 0),
            ];
        }

        return $this->response->setJSON([
            "ok" => true,
            "pemilih" => $jmlPemilih,
            "sudah" => $jmlVote,
            "belum" => max(0, $jmlPemilih - $jmlVote),
            "hasil" => $hasil,
            "diperbarui" => date("H:i:s"),
        ]);
    }

    public function laporan()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $db = db_connect();

        try {
            $hasilVote = $db->query(
                "SELECT p.no, p.nama, COUNT(v.id_pilih) AS jumlah FROM tb_pilihan p LEFT JOIN tb_pilih v ON v.nisn = p.nisn GROUP BY p.nisn, p.no, p.nama ORDER BY p.no ASC"
            )->getResultArray();

            $totalPemilih = (int) $db->query("SELECT COUNT(*) AS total FROM tb_siswa")->getRow()->total;
            $totalSudahVote = (int) $db->query("SELECT COUNT(*) AS total FROM tb_pilih")->getRow()->total;
            $totalBelumVote = max(0, $totalPemilih - $totalSudahVote);
            $totalCalon = (int) $db->query("SELECT COUNT(*) AS total FROM tb_pilihan")->getRow()->total;
        } catch (\Throwable $e) {
            $hasilVote = [];
            $totalPemilih = 0;
            $totalSudahVote = 0;
            $totalBelumVote = 0;
            $totalCalon = 0;
        }

        return view("admin/laporan", [
            "username" => session()->get("username"),
            "school" => $this->getSchoolData(),
            "hasilVote" => $hasilVote,
            "totalPemilih" => $totalPemilih,
            "totalSudahVote" => $totalSudahVote,
            "totalBelumVote" => $totalBelumVote,
            "totalCalon" => $totalCalon,
            "success" => session()->getFlashdata("success"),
            "error" => session()->getFlashdata("error"),
        ]);
    }

    public function connectApi()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $config = $this->getApiConfig();
        $db = db_connect();
        $this->ensureLogSyncTable($db);

        try {
            $riwayat = $db->query("SELECT waktu, sumber, jumlah, gagal, pesan, oleh FROM tb_log_sync ORDER BY id DESC LIMIT 10")->getResultArray();
        } catch (\Throwable $e) {
            $riwayat = [];
        }

        return view("admin/connect-api", [
            "username" => session()->get("username"),
            "school" => $this->getSchoolData(),
            "config" => $config,
            "riwayat" => $riwayat,
            "success" => session()->getFlashdata("success"),
            "error" => session()->getFlashdata("error"),
        ]);
    }

    public function saveConnectApi()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $appName = trim((string) $this->request->getPost("app_name"));
        $apiUrl = trim((string) $this->request->getPost("api_url"));
        $apiToken = trim((string) $this->request->getPost("api_token"));
        $timeout = (int) $this->request->getPost("timeout");

        if ($appName === "") {
            return redirect()->back()->with("error", "Nama aplikasi wajib diisi.");
        }

        if ($apiUrl === "") {
            return redirect()->back()->with("error", "URL API sekolah wajib diisi.");
        }

        if ($timeout <= 0 || $timeout > 60) {
            $timeout = 10;
        }

        $config = $this->getApiConfig();
        $config['app_name'] = $appName;
        $config['api_url'] = rtrim($apiUrl, '/');
        $config['api_token'] = $apiToken;
        $config['timeout'] = $timeout;
        $config['last_status'] = 'Konfigurasi disimpan';
        $config['last_sync'] = date('Y-m-d H:i:s');

        if (! $this->saveApiConfig($config)) {
            return redirect()->back()->with("error", "Gagal menyimpan konfigurasi API.");
        }

        return redirect()->to("/admin/connect-api")->with("success", "Konfigurasi koneksi API berhasil disimpan.");
    }

    public function testConnectApi()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $config = $this->getApiConfig();
        $appName = trim((string) $this->request->getPost("app_name"));
        $apiUrl = trim((string) $this->request->getPost("api_url"));
        $apiToken = trim((string) $this->request->getPost("api_token"));

        if ($appName === "") {
            return redirect()->back()->with("error", "Nama aplikasi wajib diisi untuk test koneksi.");
        }

        if ($apiUrl === "") {
            return redirect()->back()->with("error", "URL API sekolah wajib diisi untuk test koneksi.");
        }

        $endpoint = rtrim($apiUrl, '/') . '/api/connect/health';
        $headers = [
            'Accept: application/json',
            'X-App-Name: ' . $appName,
            'Authorization: Bearer ' . $apiToken,
        ];

        $context = stream_context_create([
            'http' => [
                'header' => implode("\r\n", $headers),
                'ignore_errors' => true,
                'timeout' => max(5, (int) ($config['timeout'] ?? 10)),
                'method' => 'GET',
            ],
        ]);

        $response = @file_get_contents($endpoint, false, $context);

        $kode = 0;
        if (isset($http_response_header) && is_array($http_response_header)) {
            foreach ($http_response_header as $baris) {
                if (preg_match('#^HTTP/\S+\s+(\d{3})#', $baris, $m)) {
                    $kode = (int) $m[1];
                }
            }
        }

        if ($response === false || $kode >= 400) {
            return redirect()->back()->with("error", "Koneksi ke API sekolah gagal (HTTP " . $kode . "). Periksa URL dan token.");
        }

        $payload = json_decode($response, true);
        if (! is_array($payload) || ($payload['ok'] ?? false) !== true) {
            return redirect()->back()->with("error", "API sekolah menolak koneksi: " . (is_array($payload) ? ($payload['message'] ?? 'token tidak valid.') : 'respons tidak valid.'));
        }

        $status = $payload['status'] ?? 'ok';

        $config['app_name'] = $appName;
        $config['api_url'] = rtrim($apiUrl, '/');
        $config['api_token'] = $apiToken;
        $config['last_status'] = 'Test koneksi berhasil';
        $config['last_sync'] = date('Y-m-d H:i:s');
        $this->saveApiConfig($config);

        return redirect()->to("/admin/connect-api")->with("success", "Test koneksi berhasil. Status API: " . $status);
    }

    public function resetUser()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $username = trim((string) $this->request->getPost("username"));

        if ($username === "") {
            return redirect()->back()->with("error", "Username / NIS siswa wajib diisi untuk reset user.");
        }

        $db = db_connect();

        try {
            $db->transBegin();
            $db->query("DELETE FROM tb_pilih WHERE username = ?", [$username]);
            $db->query("UPDATE tb_siswa SET hadir = 'Tidak Hadir' WHERE username = ?", [$username]);
            $db->transCommit();

            return redirect()->to("/admin")->with("success", "Data suara dan status hadir user berhasil direset.");
        } catch (\Throwable $e) {
            if ($db->transStatus() === false) {
                $db->transRollback();
            }

            return redirect()->back()->with("error", "Gagal mereset user. Silakan cek data atau import schema terlebih dahulu.");
        }
    }

    public function resetData()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $db = db_connect();

        try {
            $db->transBegin();
            $db->query("DELETE FROM tb_pilih");
            $db->query("DELETE FROM tb_siswa");
            $db->query("DELETE FROM tb_pilihan");

            try {
                $db->query("UPDATE tb_datapilketos SET tapel = '', tgl = '' WHERE id = 1");
            } catch (\Throwable $ignored) {
            }

            $db->transCommit();

            return redirect()->to("/admin")->with("success", "Semua data pemilihan berhasil direset.");
        } catch (\Throwable $e) {
            if ($db->transStatus() === false) {
                $db->transRollback();
            }

            return redirect()->back()->with("error", "Gagal mereset data pemilihan. Silakan cek tabel yang tersedia.");
        }
    }

    private function ensureAdminTable($db): void
    {
        $driver = strtolower((string) ($db->DBDriver ?? ''));

        try {
            $tableExists = str_contains($driver, 'sqlite')
                ? ! empty($db->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'tb_admin' LIMIT 1")->getRowArray())
                : ! empty($db->query("SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = 'tb_admin' LIMIT 1")->getRowArray());

            if (! $tableExists) {
                if (str_contains($driver, 'sqlite')) {
                    $db->query('CREATE TABLE IF NOT EXISTS tb_admin (username TEXT PRIMARY KEY, password TEXT NOT NULL)');
                } else {
                    $db->query('CREATE TABLE IF NOT EXISTS tb_admin (username VARCHAR(50) NOT NULL PRIMARY KEY, password VARCHAR(32) NOT NULL)');
                }
            }

            $row = $db->query('SELECT username FROM tb_admin WHERE username = ? LIMIT 1', ['admin'])->getRowArray();
            if (empty($row)) {
                $adminExists = $db->query('SELECT 1 FROM tb_admin LIMIT 1')->getRowArray();
                if (empty($adminExists)) {
                    $db->query('INSERT INTO tb_admin (username, password) VALUES (?, ?)', ['admin', md5('admin')]);
                }
            }
        } catch (\Throwable $e) {
            // Ignore silently so the page can still render with an error notice when the schema is missing.
        }
    }

    public function password()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        return view("admin/password", [
            "username" => session()->get("username"),
            "school" => $this->getSchoolData(),
            "success" => session()->getFlashdata("success"),
            "error" => session()->getFlashdata("error"),
        ]);
    }

    public function savePassword()
    {
        if (! session()->get("username")) {
            return redirect()->to("/login");
        }

        $lama = (string) $this->request->getPost("password_lama");
        $baru = (string) $this->request->getPost("password_baru");
        $konfirmasi = (string) $this->request->getPost("password_konfirmasi");

        if ($lama === '' || $baru === '' || $konfirmasi === '') {
            return redirect()->back()->with("error", "Password lama, baru, dan konfirmasi wajib diisi.");
        }

        if (strlen($baru) < 4) {
            return redirect()->back()->with("error", "Password baru minimal 4 karakter.");
        }

        if ($baru !== $konfirmasi) {
            return redirect()->back()->with("error", "Konfirmasi password tidak cocok.");
        }

        $db = db_connect();
        $this->ensureAdminTable($db);

        try {
            $username = session()->get("username");
            $row = $db->query(
                'SELECT username FROM tb_admin WHERE username = ? AND password = ? LIMIT 1',
                [$username, md5($lama)]
            )->getRowArray();

            if (empty($row)) {
                return redirect()->back()->with("error", "Password lama salah.");
            }

            $db->query(
                'UPDATE tb_admin SET password = ? WHERE username = ?',
                [md5($baru), $username]
            );

            return redirect()->to("/admin/password")->with("success", "Password berhasil diperbarui.");
        } catch (\Throwable $e) {
            return redirect()->back()->with("error", "Gagal memperbarui password. Silakan coba lagi.");
        }
    }

    public function logout()
    {
        session()->remove("username");
        return redirect()->to("/login");
    }
}
