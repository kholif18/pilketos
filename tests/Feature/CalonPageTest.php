<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\CsrfPostTrait;

final class CalonPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use CsrfPostTrait;

    public function testGuestRedirectedToLoginWhenOpeningCalonPage(): void
    {
        $result = $this->get('/admin/calon');

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanOpenCalonPage(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/calon');

        $result->assertOK();
        $result->assertSee('Kelola Calon');
        $result->assertSee('NIS');
        $result->assertSee('Pilih Foto');
    }

    public function testGuestRedirectedToLoginWhenSavingCalon(): void
    {
        $result = $this->postWithCsrf('/admin/calon', [
            'nis'  => '2024',
            'nama' => 'Budi Santoso',
            'no'   => '1',
        ]);

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanSaveCalonWithoutPhoto(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/calon', [
            'nis'  => '2024',
            'nama' => 'Budi Santoso',
            'no'   => '1',
        ]);

        $result->assertRedirectTo('/admin/calon');
    }

    public function testNisWajibEmpatDigitSaatMenyimpanCalon(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/calon', [
            'nis'  => '24',
            'nama' => 'Budi Santoso',
            'no'   => '1',
        ]);

        $result->assertRedirect();
    }

    public function testGuestCannotDeleteCalonViaGet(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('/admin/calon/delete/2024');
    }

    public function testLoggedInAdminCanDeleteCalonViaPost(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/calon/delete', [
            'nisn' => '2024',
        ]);

        $result->assertRedirectTo('/admin/calon');
    }
}
