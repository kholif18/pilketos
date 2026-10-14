<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\CsrfPostTrait;

final class PengaturanPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use CsrfPostTrait;

    public function testGuestRedirectedToLoginWhenOpeningPengaturanPage(): void
    {
        $result = $this->get('/admin/pengaturan');

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanOpenPengaturanPage(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/pengaturan');

        $result->assertOK();
        $result->assertSee('Pengaturan');
        $result->assertSee('Ganti Password');
        $result->assertSee('Avatar Admin');
        $result->assertSee('Logo dan Ikon Aplikasi');
    }

    public function testGuestRedirectedToLoginWhenSavingPassword(): void
    {
        $result = $this->postWithCsrf('/admin/pengaturan/password', [
            'password_lama'       => 'admin',
            'password_baru'       => 'baru1234',
            'password_konfirmasi' => 'baru1234',
        ]);

        $result->assertRedirectTo('/login');
    }

    public function testWrongOldPasswordDoesNotSave(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/pengaturan/password', [
            'password_lama'       => 'salah',
            'password_baru'       => 'baru1234',
            'password_konfirmasi' => 'beda1234',
        ]);

        $result->assertRedirect();
    }

    public function testMismatchedConfirmationDoesNotSave(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/pengaturan/password', [
            'password_lama'       => 'admin',
            'password_baru'       => 'baru1234',
            'password_konfirmasi' => 'beda1234',
        ]);

        $result->assertRedirect();
    }

    public function testLoggedInAdminCanChangePassword(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/pengaturan/password', [
            'password_lama'       => 'admin',
            'password_baru'       => 'baru1234',
            'password_konfirmasi' => 'baru1234',
        ]);

        $result->assertRedirectTo('/admin/pengaturan');
    }

    public function testGuestRedirectedToLoginWhenSavingAvatar(): void
    {
        $result = $this->postWithCsrf('/admin/pengaturan/avatar');

        $result->assertRedirectTo('/login');
    }

    public function testAvatarWithoutFileRedirectsBack(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/pengaturan/avatar');

        $result->assertRedirect();
    }

    public function testGuestRedirectedToLoginWhenSavingLogo(): void
    {
        $result = $this->postWithCsrf('/admin/pengaturan/logo');

        $result->assertRedirectTo('/login');
    }

    public function testLogoWithoutFileRedirectsBack(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/pengaturan/logo');

        $result->assertRedirect();
    }
}
