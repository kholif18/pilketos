<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\CsrfPostTrait;

final class SiswaImportTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use CsrfPostTrait;

    public function testGuestRedirectedToLoginWhenImportingSiswa(): void
    {
        $result = $this->postWithCsrf('/admin/siswa/import');

        $result->assertRedirectTo('/login');
    }

    public function testGuestRedirectedToLoginWhenDownloadingTemplate(): void
    {
        $result = $this->get('/admin/siswa/template');

        $result->assertRedirectTo('/login');
    }

    public function testImportWithoutFileRedirectsBack(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/siswa/import');

        $result->assertRedirect();
    }

    public function testLoggedInAdminCanDownloadTemplate(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/siswa/template');

        $result->assertOK();
        $result->assertHeader('Content-Type', 'text/csv');
        $result->assertSee('nis,nama,jk,kelas');
    }

    public function testSiswaPageShowsImportPanel(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/siswa');

        $result->assertOK();
        $result->assertSee('Impor CSV');
        $result->assertSee('/admin/siswa/template');
    }
}
