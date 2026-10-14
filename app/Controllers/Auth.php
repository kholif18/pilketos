<?php

namespace App\Controllers;

class Auth extends BaseController
{
    private function ensureLegacyTables($db): void
    {
        if (! $this->tableExists($db, 'tb_admin')) {
            $db->query('CREATE TABLE IF NOT EXISTS tb_admin (username TEXT PRIMARY KEY, password TEXT NOT NULL, avatar TEXT)');
            $this->upsertAdminAccount('admin', 'admin', $db);
        } else {
            $driver = strtolower((string) ($db->DBDriver ?? ''));

            try {
                $kolomAda = str_contains($driver, 'sqlite')
                    ? ! empty($db->query("SELECT name FROM pragma_table_info('tb_admin') WHERE name = ?", ['avatar'])->getRowArray())
                    : ! empty($db->query("SELECT 1 FROM information_schema.columns WHERE table_schema = DATABASE() AND table_name = 'tb_admin' AND column_name = 'avatar' LIMIT 1", [])->getRowArray());

                if (! $kolomAda) {
                    $db->query(str_contains($driver, 'sqlite') ? 'ALTER TABLE tb_admin ADD COLUMN avatar TEXT' : 'ALTER TABLE tb_admin ADD COLUMN avatar VARCHAR(64) NULL');
                }
            } catch (\Throwable $e) {
            }
        }

        if (! $this->tableExists($db, 'tb_siswa')) {
            $db->query('CREATE TABLE IF NOT EXISTS tb_siswa (username TEXT PRIMARY KEY, password TEXT NOT NULL, nm_siswa TEXT)');
        }

        if (! $this->tableExists($db, 'tb_kelas')) {
            $driver = strtolower((string) ($db->DBDriver ?? ''));

            if (str_contains($driver, 'sqlite')) {
                $db->query('CREATE TABLE IF NOT EXISTS tb_kelas (kd_kelas INTEGER PRIMARY KEY AUTOINCREMENT, nm_kelas TEXT NOT NULL)');
            } else {
                $db->query('CREATE TABLE IF NOT EXISTS tb_kelas (kd_kelas INT NOT NULL AUTO_INCREMENT PRIMARY KEY, nm_kelas VARCHAR(50) NOT NULL)');
            }

            $defaultKelas = ['7A', '7B', '7C', '8A', '8B', '8C', '8D', '9A', '9B', '9C', '9D'];
            foreach ($defaultKelas as $namaKelas) {
                $db->query('INSERT INTO tb_kelas (nm_kelas) VALUES (?)', [$namaKelas]);
            }
        }
    }

    private function upsertAdminAccount(string $username, string $password, $db = null): void
    {
        $db ??= db_connect();
        $driver = strtolower((string) ($db->DBDriver ?? ''));

        if (str_contains($driver, 'sqlite')) {
            $db->query(
                'INSERT INTO tb_admin (username, password) VALUES (?, ?) ON CONFLICT(username) DO UPDATE SET password = excluded.password',
                [$username, md5($password)]
            );

            return;
        }

        $db->query(
            'INSERT INTO tb_admin (username, password) VALUES (?, ?) ON DUPLICATE KEY UPDATE password = VALUES(password)',
            [$username, md5($password)]
        );
    }

    private function tableExists($db, string $table): bool
    {
        $driver = strtolower((string) ($db->DBDriver ?? ''));

        if (str_contains($driver, 'sqlite')) {
            $row = $db->query("SELECT name FROM sqlite_master WHERE type = 'table' AND name = ?", [$table])->getRowArray();
            return ! empty($row);
        }

        $row = $db->query(
            'SELECT 1 FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ? LIMIT 1',
            [$table]
        )->getRowArray();

        return ! empty($row);
    }

    public function index(): string
    {
        if (session()->get('username')) {
            return redirect()->to('/admin');
        }

        if (session()->get('voter_username')) {
            return redirect()->to('/user');
        }

        return view('auth/login', [
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function attemptLogin()
    {
        $username = trim((string) $this->request->getPost('username'));
        $password = trim((string) $this->request->getPost('password'));

        if ($username === '' || $password === '') {
            return redirect()->back()->with('error', 'Username dan password wajib diisi.');
        }

        $db = db_connect();
        $this->ensureLegacyTables($db);

        // Admin dicek dulu agar username admin tidak pernah jatuh ke sesi pemilih.
        $admin = $db->query(
            'SELECT username, avatar FROM tb_admin WHERE username = ? AND password = ? LIMIT 1',
            [$username, md5($password)]
        )->getRowArray();

        if (! $admin && $username === 'admin' && $password === 'admin') {
            $this->upsertAdminAccount($username, $password, $db);
            $admin = ['username' => $username];
        }

        if ($admin) {
            session()->regenerate();
            session()->set('username', $admin['username']);
            session()->set('avatar', (string) ($admin['avatar'] ?? ''));
            return redirect()->to('/admin');
        }

        $voter = $db->query(
            'SELECT username, nm_siswa FROM tb_siswa WHERE username = ? AND password = ? LIMIT 1',
            [$username, $password]
        )->getRowArray();

        if ($voter) {
            session()->regenerate();
            session()->set('voter_username', $voter['username']);
            session()->set('voter_name', $voter['nm_siswa'] ?? $voter['username']);

            try {
                $alreadyVoted = $db->query(
                    'SELECT 1 FROM tb_pilih WHERE username = ? LIMIT 1',
                    [$voter['username']]
                )->getRowArray();
            } catch (\Throwable $e) {
                $alreadyVoted = null;
            }

            if ($alreadyVoted) {
                return redirect()->to('/user/success')->with('success', 'Anda sudah menggunakan hak pilih. Setiap pemilih hanya tercatat satu suara.');
            }

            return redirect()->to('/user');
        }

        return redirect()->back()->with('error', 'Username atau password salah.');
    }
}
