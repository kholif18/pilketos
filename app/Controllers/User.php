<?php

namespace App\Controllers;

class User extends BaseController
{
    private function tableExists(string $tableName): bool
    {
        try {
            $db = db_connect();
            $result = $db->query('SHOW TABLES LIKE ?', [$tableName])->getRow();
            return $result !== null;
        } catch (\Throwable $e) {
            return false;
        }
    }

    private function statusPemilihan($db): array
    {
        try {
            $row = $db->query("SELECT tgl FROM tb_datapilketos WHERE id = 1 LIMIT 1")->getRowArray();
            $tgl = trim((string) ($row['tgl'] ?? ''));
        } catch (\Throwable $e) {
            $tgl = '';
        }

        if ($tgl === '' || $tgl === '0000-00-00') {
            return ['buka' => false, 'tgl' => '', 'alasan' => 'belum'];
        }

        if ($tgl !== date('Y-m-d')) {
            return ['buka' => false, 'tgl' => $tgl, 'alasan' => 'tanggal'];
        }

        return ['buka' => true, 'tgl' => $tgl, 'alasan' => ''];
    }

    public function index()
    {
        if (! session()->get('voter_username')) {
            return redirect()->to('/login');
        }

        $db = db_connect();
        if ($this->tableExists('tb_pilih')) {
            $alreadyVoted = $db->query(
                'SELECT 1 FROM tb_pilih WHERE username = ? LIMIT 1',
                [session()->get('voter_username')]
            )->getRowArray();

            if ($alreadyVoted) {
                return redirect()->to('/user/success')->with('success', 'Anda sudah menggunakan hak pilih. Setiap pemilih hanya tercatat satu suara.');
            }
        }

        $status = $this->statusPemilihan($db);
        if (! $status['buka']) {
            return view('user/closed', [
                'username' => session()->get('voter_name') ?? session()->get('voter_username'),
                'tgl' => $status['tgl'],
                'alasan' => $status['alasan'],
            ]);
        }

        $candidates = [];
        if ($this->tableExists('tb_pilihan')) {
            $candidates = $db->query(
                'SELECT * FROM tb_pilihan ORDER BY no ASC, nama ASC'
            )->getResultArray();
        }

        return view('user/index', [
            'username' => session()->get('voter_name') ?? session()->get('voter_username'),
            'candidates' => $candidates,
            'error' => session()->getFlashdata('error'),
        ]);
    }

    public function submitVote()
    {
        if (! session()->get('voter_username')) {
            return redirect()->to('/login');
        }

        $username = session()->get('voter_username');
        $db = db_connect();

        if ($this->tableExists('tb_pilih')) {
            if ($db->query('SELECT 1 FROM tb_pilih WHERE username = ? LIMIT 1', [$username])->getRowArray()) {
                return redirect()->to('/user/success')->with('success', 'Anda sudah menggunakan hak pilih. Setiap pemilih hanya tercatat satu suara.');
            }
        }

        $status = $this->statusPemilihan($db);
        if (! $status['buka']) {
            return redirect()->to('/user')->with('error', 'Pemilihan sedang tidak dibuka.');
        }

        $nisn = trim((string) $this->request->getPost('nisn'));
        if ($nisn === '') {
            return redirect()->back()->with('error', 'Pilih salah satu calon sebelum menutup suara.');
        }

        if (! $this->tableExists('tb_pilihan') || ! $this->tableExists('tb_siswa')) {
            return redirect()->back()->with('error', 'Data pemilih atau calon belum tersedia di database.');
        }

        try {
            $db->query('INSERT INTO tb_pilih (nisn, username) VALUES (?, ?)', [$nisn, $username]);
            $db->query("UPDATE tb_siswa SET hadir = 'Hadir' WHERE username = ?", [$username]);
            return redirect()->to('/user/success');
        } catch (\Throwable $e) {
            try {
                $tercatat = $db->query('SELECT 1 FROM tb_pilih WHERE username = ? LIMIT 1', [$username])->getRowArray();
            } catch (\Throwable $ignored) {
                $tercatat = null;
            }

            if ($tercatat) {
                return redirect()->to('/user/success')->with('success', 'Anda sudah menggunakan hak pilih. Setiap pemilih hanya tercatat satu suara.');
            }

            return redirect()->back()->with('error', 'Gagal menyimpan suara. Silakan coba lagi.');
        }
    }

    public function success()
    {
        if (! session()->get('voter_username')) {
            return redirect()->to('/login');
        }

        return view('user/success', [
            'username' => session()->get('voter_name') ?? session()->get('voter_username'),
            'success' => session()->getFlashdata('success'),
        ]);
    }

    public function logout()
    {
        session()->remove('voter_username');
        session()->remove('voter_name');

        return redirect()->to('/login');
    }
}
