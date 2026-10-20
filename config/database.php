<?php
/**
 * Konfigurasi Koneksi Database MySQL
 * Sistem Informasi Antrean Klinik / Puskesmas
 * Lingkungan: Laragon (Development)
 */

return [
    'host'      => 'localhost',
    'username'  => 'root',
    'password'  => '',           // Default password Laragon kosong
    'database'  => 'db_antrean_klinik',
    'charset'   => 'utf8mb4',
    'port'      => 3306,
    
    // Opsi tambahan untuk PDO Connection
    'options'   => [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]
];