<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\CsrfPostTrait;

final class PeriodePemilihanTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use CsrfPostTrait;

    public function testGuestRedirectedToLoginWhenSavingPeriode(): void
    {
        $result = $this->postWithCsrf('/admin/periode', [
            'tapel' => '2030/2031',
            'tgl'   => '2025-09-15',
        ]);

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanSavePeriode(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/periode', [
            'tapel' => '2030/2031',
            'tgl'   => '2025-09-15',
        ]);

        $result->assertRedirectTo('/admin');
    }

    public function testTapelKosongTidakMenyimpanPeriode(): void
    {
        $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/periode', [
            'tapel' => '',
            'tgl'   => '2025-09-15',
        ]);

        $result = $this->withSession(['username' => 'admin'])->get('/admin');

        $result->assertOK();
        $result->assertSee('Belum diatur');
        $result->assertDontSee('2030/2031');
    }

    public function testTanggalSalahFormatTidakMenyimpanPeriode(): void
    {
        $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/periode', [
            'tapel' => '2030/2031',
            'tgl'   => '15-09-2025',
        ]);

        $result = $this->withSession(['username' => 'admin'])->get('/admin');

        $result->assertOK();
        $result->assertSee('Belum diatur');
        $result->assertDontSee('2030/2031');
    }

    public function testGuestRedirectedToLoginWhenResettingPeriode(): void
    {
        $result = $this->postWithCsrf('/admin/periode/reset');

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanResetPeriode(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/periode/reset');

        $result->assertRedirectTo('/admin');
    }

    public function testDashboardShowsPeriodeForm(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin');

        $result->assertOK();
        $result->assertSee('Periode Pemilihan');
        $result->assertSee('Simpan Periode');
    }
}
