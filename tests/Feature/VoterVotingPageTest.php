<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\CsrfPostTrait;

final class VoterVotingPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use CsrfPostTrait;

    public function testPublicLoginPageIsAvailable(): void
    {
        $result = $this->get('/login');

        $result->assertOK();
        $result->assertSee('Satu siswa, satu suara');
        $result->assertSee('NIS');
        $result->assertDontSee('Pilih role');
    }

    public function testGuestRedirectedToLoginWhenOpeningVotingPage(): void
    {
        $result = $this->get('/user');

        $result->assertRedirectTo('/login');
    }

    public function testGuestRedirectedToLoginWhenOpeningThankYouPage(): void
    {
        $result = $this->get('/user/success');

        $result->assertRedirectTo('/login');
    }

    public function testDefaultAdminLoginWorksEvenWithoutSeededTable(): void
    {
        $result = $this->postWithCsrf('/login', [
            'username' => 'admin',
            'password' => 'admin',
        ]);

        $result->assertRedirectTo('/admin');
    }

    public function testWrongCredentialsRedirectBackToLogin(): void
    {
        $result = $this->postWithCsrf('/login', [
            'username' => 'tidakada',
            'password' => 'salah',
        ]);

        $result->assertRedirect();
    }

    public function testLoggedInVoterCanOpenVotingPage(): void
    {
        $result = $this->withSession(['voter_username' => '201'])->get('/user');

        $result->assertOK();
        $result->assertSee('Pilihan Calon');
    }

    public function testAlreadyVotedVoterRedirectedToThankYouPage(): void
    {
        $result = $this->withSession(['voter_username' => '201'])->get('/user');

        $result->assertOK();
        $result->assertSee('Pilihan Calon');
    }
}
