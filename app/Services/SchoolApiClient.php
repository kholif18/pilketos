<?php

namespace App\Services;

class SchoolApiClient
{
    protected string $baseUrl;
    protected string $token;
    protected int $timeout;

    public function __construct(string $baseUrl = '', string $token = '', int $timeout = 10)
    {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $token;
        $this->timeout = max(5, min(60, $timeout));
    }

    public function healthCheck(): array
    {
        $response = $this->request('/api/connect/health');

        return [
            'ok' => $response['ok'] ?? false,
            'status' => $response['status'] ?? 'unknown',
            'message' => $response['message'] ?? 'No response',
            'raw' => $response,
        ];
    }

    public function fetchKelas(): array
    {
        return $this->request('/api/connect/kelas');
    }

    public function fetchSiswa(): array
    {
        return $this->request('/api/connect/siswa');
    }

    public function fetchGuru(): array
    {
        return $this->request('/api/connect/guru');
    }

    public function sync(): array
    {
        $result = [
            'ok' => true,
            'source' => 'pilketos-client',
            'synced_at' => date('c'),
            'items' => [
                'kelas' => 0,
                'siswa' => 0,
                'guru' => 0,
            ],
        ];

        $kelas = $this->fetchKelas();
        $siswa = $this->fetchSiswa();
        $guru = $this->fetchGuru();

        if (($kelas['ok'] ?? false) === false || ($siswa['ok'] ?? false) === false || ($guru['ok'] ?? false) === false) {
            $result['ok'] = false;
            $result['error'] = 'One or more API endpoints failed';
            $result['responses'] = [
                'kelas' => $kelas,
                'siswa' => $siswa,
                'guru' => $guru,
            ];

            return $result;
        }

        $result['items']['kelas'] = is_array($kelas['data'] ?? null) ? count($kelas['data']) : 0;
        $result['items']['siswa'] = is_array($siswa['data'] ?? null) ? count($siswa['data']) : 0;
        $result['items']['guru'] = is_array($guru['data'] ?? null) ? count($guru['data']) : 0;
        $result['responses'] = [
            'kelas' => $kelas,
            'siswa' => $siswa,
            'guru' => $guru,
        ];

        return $result;
    }

    protected function request(string $path): array
    {
        if ($this->baseUrl === '') {
            return [
                'ok' => false,
                'status' => 'not_configured',
                'message' => 'URL API belum dikonfigurasi',
            ];
        }

        $url = $this->baseUrl . $path;
        $headers = [
            'Accept: application/json',
            'Content-Type: application/json',
        ];

        if ($this->token !== '') {
            $headers[] = 'Authorization: Bearer ' . $this->token;
        }

        $context = stream_context_create([
            'http' => [
                'method' => 'GET',
                'header' => implode("\r\n", $headers),
                'timeout' => $this->timeout,
                'ignore_errors' => true,
            ],
        ]);

        $body = @file_get_contents($url, false, $context);
        if ($body === false) {
            return [
                'ok' => false,
                'status' => 'connection_error',
                'message' => 'Tidak dapat menghubungi endpoint API',
            ];
        }

        $decoded = json_decode($body, true);
        if (! is_array($decoded)) {
            return [
                'ok' => false,
                'status' => 'invalid_response',
                'message' => 'Respons API tidak valid',
                'raw' => $body,
            ];
        }

        return $decoded;
    }
}
