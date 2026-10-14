<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\CsrfPostTrait;

final class DataSiswaAndPdfPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use CsrfPostTrait;

    public function testGuestRedirectedToLoginWhenOpeningDataSiswaPage(): void
    {
        $result = $this->get('/admin/siswa');

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanOpenDataSiswaPage(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/siswa');

        $result->assertOK();
        $result->assertSee('Data Siswa');
    }

    public function testNisWajibEmpatDigitSaatMenyimpanSiswa(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/siswa', [
            'username' => '12',
            'nm_siswa' => 'Siswa Uji',
            'jk'       => 'L',
        ]);

        $result->assertRedirect();
    }

    public function testLoggedInAdminCanDownloadLaporanPdf(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/laporan/pdf');

        $result->assertOK();
    }

    public function testGuestRedirectedToLoginWhenDownloadingDaftarHadirPdf(): void
    {
        $result = $this->get('/admin/daftarhadir/pdf');

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanDownloadDaftarHadirPdf(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/daftarhadir/pdf');

        $result->assertOK();
        $result->assertHeader('Content-Type', 'application/pdf');
        $result->assertSee('Daftar Hadir Pemilihan Ketua OSIS');
    }

    public function testDaftarHadirPageShowsExportPdfButton(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/daftarhadir');

        $result->assertOK();
        $result->assertSee('Export PDF');
        $result->assertSee('/admin/daftarhadir/pdf');
    }

    public function testGuestCannotDeleteSiswaViaGet(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('/admin/siswa/delete/1234');
    }

    public function testLoggedInAdminCanDeleteSiswaViaPost(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/siswa/delete', [
            'username' => '1234',
        ]);

        $result->assertRedirectTo('/admin/siswa');
    }
}
