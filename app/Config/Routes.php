<?php

use CodeIgniter\Router\RouteCollection;

/** @var RouteCollection $routes */
$routes->get('/', 'Auth::index');
$routes->get('login', 'Auth::index');
$routes->post('login', 'Auth::attemptLogin');

$routes->get('user', 'User::index');
$routes->post('user/vote', 'User::submitVote');
$routes->get('user/success', 'User::success');
$routes->get('user/logout', 'User::logout');

$routes->get('admin/logout', 'Admin::logout');
$routes->get('admin/password', 'Admin::password');
$routes->post('admin/password', 'Admin::savePassword');
$routes->get('admin', 'Admin::index');
$routes->get('admin/identitas', 'Admin::identitas');
$routes->post('admin/identitas', 'Admin::saveIdentitas');
$routes->post('admin/periode', 'Admin::savePeriode');
$routes->post('admin/periode/reset', 'Admin::resetPeriode');
$routes->get('admin/calon', 'Admin::calon');
$routes->post('admin/calon', 'Admin::saveCalon');
$routes->post('admin/calon/delete', 'Admin::deleteCalon');
$routes->get('admin/kelas', 'Admin::kelas');
$routes->post('admin/kelas', 'Admin::saveKelas');
$routes->post('admin/kelas/bulk-delete', 'Admin::bulkDeleteKelas');
$routes->post('admin/kelas/delete', 'Admin::deleteKelas');
$routes->get('admin/siswa', 'Admin::siswa');
$routes->post('admin/siswa', 'Admin::saveSiswa');
$routes->post('admin/siswa/delete', 'Admin::deleteSiswa');
$routes->post('admin/siswa/sync', 'Admin::syncSiswaFromApi');
$routes->post('admin/siswa/import', 'Admin::importSiswa');
$routes->get('admin/siswa/template', 'Admin::templateSiswa');
$routes->get('admin/daftarhadir', 'Admin::daftarHadir');
$routes->get('admin/daftarhadir/pdf', 'Admin::daftarHadirPdf');
$routes->get('admin/hasilvote', 'Admin::hasilVote');
$routes->get('admin/hasilvote/data', 'Admin::hasilVoteData');
$routes->get('admin/laporan', 'Admin::laporan');
$routes->get('admin/laporan/pdf', 'Admin::laporanPdf');
$routes->get('admin/connect-api', 'Admin::connectApi');
$routes->post('admin/connect-api', 'Admin::saveConnectApi');
$routes->post('admin/connect-api/test', 'Admin::testConnectApi');
$routes->post('admin/connect-api/sync', 'Admin::syncSchoolData');
$routes->post('admin/reset-data', 'Admin::resetData');
$routes->post('admin/reset-user', 'Admin::resetUser');
