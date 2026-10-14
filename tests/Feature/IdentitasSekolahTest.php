<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

final class IdentitasSekolahTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    public function testGuestRedirectedToLoginWhenOpeningSchoolProfilePage(): void
    {
        $result = $this->get('/admin/identitas');

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanOpenSchoolProfilePage(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/identitas');

        $result->assertOK();
        $result->assertSee('Identitas Sekolah');
    }
}
