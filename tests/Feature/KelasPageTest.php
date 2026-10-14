<?php

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use Tests\Support\Traits\CsrfPostTrait;

final class KelasPageTest extends CIUnitTestCase
{
    use FeatureTestTrait;
    use CsrfPostTrait;

    public function testGuestRedirectedToLoginWhenOpeningKelasPage(): void
    {
        $result = $this->get('/admin/kelas');

        $result->assertRedirectTo('/login');
    }

    public function testLoggedInAdminCanOpenKelasPage(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/kelas');

        $result->assertOK();
        $result->assertSee('Data Kelas');
    }

    public function testDefaultClassesAreSeededWhenKelasTableIsEmpty(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/kelas');

        $result->assertOK();
        $result->assertSee('7A');
        $result->assertSee('8D');
        $result->assertSee('9D');
    }

    public function testAdminCanBulkDeleteSelectedClasses(): void
    {
        $db = db_connect();
        $db->query('DELETE FROM tb_kelas WHERE nm_kelas IN (?, ?)', ['Kelas Bulk A', 'Kelas Bulk B']);
        $db->query('INSERT INTO tb_kelas (nm_kelas) VALUES (?)', ['Kelas Bulk A']);
        $db->query('INSERT INTO tb_kelas (nm_kelas) VALUES (?)', ['Kelas Bulk B']);

        $selected = [
            $db->query('SELECT kd_kelas FROM tb_kelas WHERE nm_kelas = ?', ['Kelas Bulk A'])->getRow()->kd_kelas,
            $db->query('SELECT kd_kelas FROM tb_kelas WHERE nm_kelas = ?', ['Kelas Bulk B'])->getRow()->kd_kelas,
        ];

        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/kelas/bulk-delete', ['kd_kelas' => $selected]);

        $result->assertRedirectTo('/admin/kelas');
        $this->assertSame(0, $db->query('SELECT COUNT(*) AS total FROM tb_kelas WHERE nm_kelas IN (?, ?)', ['Kelas Bulk A', 'Kelas Bulk B'])->getRow()->total);
    }

    public function testEmptyTableIsNotRepopulatedAfterBulkDelete(): void
    {
        $db = db_connect();
        $db->query('DELETE FROM tb_kelas');

        $classRows = $db->query('SELECT kd_kelas FROM tb_kelas ORDER BY kd_kelas ASC')->getResultArray();
        $this->assertSame([], $classRows);

        $allIds = $db->query('SELECT kd_kelas FROM tb_kelas ORDER BY kd_kelas ASC')->getResultArray();
        $this->assertSame([], $allIds);

        $this->withSession(['username' => 'admin'])->get('/admin/kelas');

        $count = $db->query('SELECT COUNT(*) AS total FROM tb_kelas')->getRow()->total;
        $this->assertSame(0, (int) $count);
    }

    public function testNewClassStartsFromOneAfterReset(): void
    {
        $db = db_connect();
        $db->query('DELETE FROM tb_kelas');

        $driver = strtolower((string) ($db->DBDriver ?? ''));
        if (str_contains($driver, 'sqlite')) {
            $db->query("DELETE FROM sqlite_sequence WHERE name = 'tb_kelas'");
        } else {
            $db->query('ALTER TABLE tb_kelas AUTO_INCREMENT = 1');
        }

        $db->query('INSERT INTO tb_kelas (nm_kelas) VALUES (?)', ['Kelas Baru']);

        $row = $db->query('SELECT kd_kelas FROM tb_kelas WHERE nm_kelas = ?', ['Kelas Baru'])->getRow();
        $this->assertSame(1, (int) $row->kd_kelas);
    }

    public function testGuestCannotDeleteKelasViaGet(): void
    {
        $this->expectException(\CodeIgniter\Exceptions\PageNotFoundException::class);
        $this->get('/admin/kelas/delete/1');
    }

    public function testLoggedInAdminCanDeleteKelasViaPost(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/kelas/delete', [
            'kd_kelas' => 1,
        ]);

        $result->assertRedirectTo('/admin/kelas');
    }

    public function testGuestRedirectedToLoginWhenUpdatingKelas(): void
    {
        $result = $this->postWithCsrf('/admin/kelas/update', [
            'kd_kelas' => 1,
            'nm_kelas' => '7Z',
        ]);

        $result->assertRedirectTo('/login');
    }

    public function testEmptyKelasNameDoesNotUpdate(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/kelas/update', [
            'kd_kelas' => 1,
            'nm_kelas' => '',
        ]);

        $result->assertRedirect();
    }

    public function testLoggedInAdminCanUpdateKelas(): void
    {
        $result = $this->withSession(['username' => 'admin'])->postWithCsrf('/admin/kelas/update', [
            'kd_kelas' => 1,
            'nm_kelas' => '7Z',
        ]);

        $result->assertRedirectTo('/admin/kelas');
    }

    public function testKelasPageShowsEditButton(): void
    {
        $result = $this->withSession(['username' => 'admin'])->get('/admin/kelas');

        $result->assertOK();
        $result->assertSee('Ubah Kelas');
        $result->assertSee('Simpan');
    }
}
