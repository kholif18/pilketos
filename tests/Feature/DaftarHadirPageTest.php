<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class DaftarHadirPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testGuestRedirectedToLoginWhenOpeningDaftarHadirPage(): void
    {
        $result = $this->get('/admin/daftarhadir');

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanOpenDaftarHadirPage(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/daftarhadir');

        $result->assertOK();
        $result->assertSee('Daftar Hadir');
    }
}
