<?php

use CodeIgniter\Router\RouteCollection;

/**
 * @var RouteCollection $routes
 */

// Default route
$routes->get('/', 'Home::index');

// Public routes (tidak perlu login)
$routes->group('', function($routes) {
    // Halaman beranda & telusuri event
    $routes->get('/', 'Public\Home::index');
    $routes->get('home', 'Public\Home::index');
    $routes->get('telusuri', 'Public\Home::telusuri');
    $routes->post('telusuri', 'Public\Home::telusuri');
    
    // Detail & pendaftaran event untuk tamu
    $routes->get('event/(:num)', 'Public\EventController::detail/$1');
    $routes->get('event/(:num)/daftar', 'Public\EventController::daftar/$1');
    $routes->post('event/(:num)/daftar', 'Public\EventController::prosesDaftar/$1');
    
    // Cek tiket tanpa login
    $routes->get('cek-tiket', 'Public\TiketController::index');
    $routes->post('cek-tiket', 'Public\TiketController::cek');
    $routes->get('tiket/(:segment)', 'Public\TiketController::lihat/$1');
});

// Authentication routes
$routes->group('auth', function($routes) {
    $routes->get('login', 'AuthController::login');
    $routes->post('login', 'AuthController::processLogin');
    $routes->get('register', 'AuthController::register');
    $routes->post('register', 'AuthController::processRegister');
    $routes->get('logout', 'AuthController::logout');
});

// User routes (perlu login sebagai user)
$routes->group('user', ['filter' => 'auth'], function($routes) {
    $routes->get('dashboard', 'User\DashboardController::index');
    $routes->get('event/(:num)/daftar', 'User\EventController::daftar/$1');
    $routes->post('event/(:num)/daftar', 'User\EventController::prosesDaftar/$1');
    $routes->get('tiket', 'User\TiketController::index');
    $routes->get('tiket/(:num)/cetak', 'User\TiketController::cetak/$1');
    $routes->get('sertifikat', 'User\TiketController::sertifikat');
});

// Admin routes (perlu login sebagai admin)
$routes->group('admin', ['filter' => 'admin'], function($routes) {
    // Dashboard
    $routes->get('dashboard', 'Admin\DashboardController::index');
    
    // CRUD Event
    $routes->get('event', 'Admin\EventController::index');
    $routes->get('event/tambah', 'Admin\EventController::tambah');
    $routes->post('event/tambah', 'Admin\EventController::simpan');
    $routes->get('event/(:num)/edit', 'Admin\EventController::edit/$1');
    $routes->post('event/(:num)/edit', 'Admin\EventController::update/$1');
    $routes->delete('event/(:num)', 'Admin\EventController::hapus/$1');
    
    // Data Peserta
    $routes->get('peserta', 'Admin\PesertaController::index');
    $routes->get('peserta/event/(:num)', 'Admin\PesertaController::byEvent/$1');
    $routes->get('peserta/(:num)/sertifikat', 'Admin\PesertaController::generateSertifikat/$1');
    
    // Laporan & Statistik
    $routes->get('laporan', 'Admin\LaporanController::index');
    $routes->get('laporan/export', 'Admin\LaporanController::export');
});

// API routes untuk AJAX
$routes->group('api', function($routes) {
    $routes->get('events', 'Api\EventController::list');
    $routes->get('events/search', 'Api\EventController::search');
    $routes->post('tiket/validate', 'Api\TiketController::validate');
});