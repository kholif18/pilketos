<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class HasilLaporanPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testGuestRedirectedToLoginWhenOpeningHasilVotePage(): void
    {
        $result = $this->get('/admin/hasilvote');

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanOpenHasilVotePage(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/hasilvote');

        $result->assertOK();
        $result->assertSee('Hasil Voting');
    }

    public function testGuestCannotFetchLiveVoteData(): void
    {
        $result = $this->get('/admin/hasilvote/data');

        $result->assertStatus(401);
    }

    public function testLoggedInAdminCanFetchLiveVoteData(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/hasilvote/data');

        $result->assertOK();
        $result->assertSee('"hasil"');
        $result->assertSee('"diperbarui"');
    }

    public function testDashboardShowsResetControls(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin');

        $result->assertOK();
        $result->assertSee('Reset Data Pemilihan');
        $result->assertSee('Reset User DPT');
    }

    public function testGuestRedirectedToLoginWhenOpeningLaporanPage(): void
    {
        $result = $this->get('/admin/laporan');

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanOpenLaporanPage(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/laporan');

        $result->assertOK();
        $result->assertSee('Laporan');
    }
}
