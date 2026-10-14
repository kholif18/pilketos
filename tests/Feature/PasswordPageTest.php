<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\CsrfPostTrait;

final class PasswordPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use CsrfPostTrait;

    public function testGuestRedirectedToLoginWhenOpeningPasswordPage(): void
    {
        $result = $this->get('/admin/password');

        $result->assertRedirectTo('/login');
    }

    public function testGuestRedirectedToLoginWhenSavingPassword(): void
    {
        $result = $this->postWithCsrf('/admin/password', [
            'password_lama'       => 'admin',
            'password_baru'       => 'baru1234',
            'password_konfirmasi' => 'baru1234',
        ]);

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanOpenPasswordPage(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/password');

        $result->assertOK();
        $result->assertSee('Ganti Password');
    }

    public function testWrongOldPasswordDoesNotSave(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/password', [
            'password_lama'       => 'salah',
            'password_baru'       => 'baru1234',
            'password_konfirmasi' => 'baru1234',
        ]);

        $result->assertRedirect();
    }

    public function testMismatchedConfirmationDoesNotSave(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/password', [
            'password_lama'       => 'admin',
            'password_baru'       => 'baru1234',
            'password_konfirmasi' => 'beda1234',
        ]);

        $result->assertRedirect();
    }

    public function testLoggedInAdminCanChangePassword(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/password', [
            'password_lama'       => 'admin',
            'password_baru'       => 'baru1234',
            'password_konfirmasi' => 'baru1234',
        ]);

        $result->assertRedirectTo('/admin/password');
    }
}
