<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class ConnectApiPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testGuestRedirectedToLoginWhenOpeningConnectApiPage(): void
    {
        $result = $this->get('/admin/connect-api');

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanOpenConnectApiPage(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/connect-api');

        $result->assertOK();
        $result->assertSee('Koneksi API Sekolah');
        $result->assertSee('Nama Aplikasi');
        $result->assertSee('Riwayat Sinkronisasi');
    }
}
