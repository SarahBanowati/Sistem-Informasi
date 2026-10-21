-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 21, 2026 at 12:59 AM
-- Server version: 8.4.3
-- PHP Version: 8.5.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `klinik_db`
--

DELIMITER $$
--
-- Procedures
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `sp_daftar_antrean` (IN `p_nama` VARCHAR(100), IN `p_telepon` VARCHAR(15), IN `p_id_poli` INT UNSIGNED, IN `p_tanggal` DATE, IN `p_tipe` VARCHAR(10), OUT `p_nomor_tiket` VARCHAR(15), OUT `p_status_code` VARCHAR(20), OUT `p_message` VARCHAR(255))   BEGIN
    DECLARE v_kuota_max INT UNSIGNED DEFAULT 0;
    DECLARE v_terisi INT UNSIGNED DEFAULT 0;
    DECLARE v_prefix CHAR(2) DEFAULT NULL;
    DECLARE v_next_angka INT UNSIGNED DEFAULT 1;
    DECLARE v_id_pasien INT UNSIGNED DEFAULT NULL;

    -- Jika terjadi kesalahan SQL, batalkan transaksi
    DECLARE EXIT HANDLER FOR SQLEXCEPTION
    BEGIN
        ROLLBACK;
        SET p_nomor_tiket = NULL;
        SET p_status_code = 'ERR_DATABASE';
        SET p_message =
            'Pendaftaran gagal karena kesalahan database.';
    END;

    SET p_nomor_tiket = NULL;
    SET p_status_code = NULL;
    SET p_message = NULL;

    -- Validasi input
    IF p_nama IS NULL OR TRIM(p_nama) = ''
       OR p_telepon IS NULL OR TRIM(p_telepon) = ''
       OR p_id_poli IS NULL
       OR p_tanggal IS NULL
       OR p_tipe IS NULL
       OR p_tipe NOT IN ('online', 'walk_in') THEN

        SET p_status_code = 'ERR_INPUT';
        SET p_message = 'Data pendaftaran tidak valid.';

    ELSE
        START TRANSACTION;

        -- Periksa poli dan ambil prefix serta kuota
        SELECT MAX(prefix_antrean), MAX(kuota_harian)
        INTO v_prefix, v_kuota_max
        FROM poli
        WHERE id_poli = p_id_poli
          AND status_aktif = 'buka'
        FOR UPDATE;

        IF v_prefix IS NULL THEN

            SET p_status_code = 'ERR_POLI_CLOSED';
            SET p_message =
                'Poli tidak ditemukan atau sedang tutup.';

        ELSE

            -- Buat jadwal jika belum tersedia
            INSERT INTO jadwal_kuota_poli
                (id_poli, tanggal, kuota_maksimal, terisi)
            VALUES
                (p_id_poli, p_tanggal, v_kuota_max, 0)
            ON DUPLICATE KEY UPDATE
                id_jadwal = id_jadwal;

            -- Kunci jadwal kuota selama transaksi
            SELECT kuota_maksimal, terisi
            INTO v_kuota_max, v_terisi
            FROM jadwal_kuota_poli
            WHERE id_poli = p_id_poli
              AND tanggal = p_tanggal
            FOR UPDATE;

            IF v_terisi >= v_kuota_max THEN

                SET p_status_code = 'ERR_KUOTA_FULL';
                SET p_message =
                    'Kuota pendaftaran untuk tanggal tersebut sudah penuh.';

            ELSE

                -- Simpan data pasien
                INSERT INTO pasien
                    (nama_lengkap, no_telepon)
                VALUES
                    (TRIM(p_nama), TRIM(p_telepon));

                SET v_id_pasien = LAST_INSERT_ID();

                -- Tentukan nomor antrean berikutnya
                SELECT COALESCE(MAX(angka_antrean), 0) + 1
                INTO v_next_angka
                FROM antrean
                WHERE id_poli = p_id_poli
                  AND tanggal_kunjungan = p_tanggal;

                -- Format tiket, contoh U-001
                SET p_nomor_tiket = CONCAT(
                    v_prefix, '-',
                    LPAD(v_next_angka, 3, '0')
                );

                -- Simpan antrean
                INSERT INTO antrean (
                    nomor_tiket,
                    angka_antrean,
                    id_pasien,
                    id_poli,
                    tanggal_kunjungan,
                    tipe_pendaftaran,
                    status_antrean
                )
                VALUES (
                    p_nomor_tiket,
                    v_next_angka,
                    v_id_pasien,
                    p_id_poli,
                    p_tanggal,
                    p_tipe,
                    'menunggu'
                );

                -- Tambah jumlah kuota terisi
                UPDATE jadwal_kuota_poli
                SET terisi = terisi + 1
                WHERE id_poli = p_id_poli
                  AND tanggal = p_tanggal;

                SET p_status_code = 'SUCCESS';
                SET p_message =
                    'Pendaftaran antrean berhasil.';

            END IF;
        END IF;

        -- Simpan perubahan transaksi
        COMMIT;
    END IF;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `antrean`
--

CREATE TABLE `antrean` (
  `id_antrean` bigint UNSIGNED NOT NULL,
  `nomor_tiket` varchar(15) NOT NULL,
  `angka_antrean` int UNSIGNED NOT NULL,
  `id_pasien` int UNSIGNED NOT NULL,
  `id_poli` int UNSIGNED NOT NULL,
  `tanggal_kunjungan` date NOT NULL,
  `tipe_pendaftaran` enum('online','walk_in') NOT NULL DEFAULT 'online',
  `status_antrean` enum('menunggu','dipanggil','dilayani','selesai','dilewati','batal') NOT NULL DEFAULT 'menunggu',
  `waktu_daftar` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `waktu_panggil` timestamp NULL DEFAULT NULL,
  `waktu_mulai_layani` timestamp NULL DEFAULT NULL,
  `waktu_selesai` timestamp NULL DEFAULT NULL,
  `id_petugas_pemanggil` int UNSIGNED DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `antrean`
--

INSERT INTO `antrean` (`id_antrean`, `nomor_tiket`, `angka_antrean`, `id_pasien`, `id_poli`, `tanggal_kunjungan`, `tipe_pendaftaran`, `status_antrean`, `waktu_daftar`, `waktu_panggil`, `waktu_mulai_layani`, `waktu_selesai`, `id_petugas_pemanggil`) VALUES
(1, 'U-001', 1, 1, 1, '2026-10-20', 'online', 'menunggu', '2026-10-20 14:31:22', NULL, NULL, NULL, NULL),
(2, 'U-002', 2, 2, 1, '2026-10-20', 'walk_in', 'menunggu', '2026-10-20 22:31:19', NULL, NULL, NULL, NULL),
(3, 'K-001', 1, 3, 3, '2026-10-20', 'walk_in', 'menunggu', '2026-10-20 22:32:29', NULL, NULL, NULL, NULL),
(4, 'U-003', 3, 4, 1, '2026-10-20', 'walk_in', 'menunggu', '2026-10-20 22:56:37', NULL, NULL, NULL, NULL),
(5, 'U-004', 4, 5, 1, '2026-10-20', 'walk_in', 'menunggu', '2026-10-20 22:58:39', NULL, NULL, NULL, NULL),
(6, 'U-005', 5, 6, 1, '2026-10-20', 'walk_in', 'menunggu', '2026-10-20 23:00:29', NULL, NULL, NULL, NULL),
(7, 'U-006', 6, 7, 1, '2026-10-20', 'walk_in', 'menunggu', '2026-10-20 23:00:41', NULL, NULL, NULL, NULL),
(8, 'G-001', 1, 8, 2, '2026-10-20', 'walk_in', 'menunggu', '2026-10-20 23:01:20', NULL, NULL, NULL, NULL),
(9, 'G-002', 2, 9, 2, '2026-10-20', 'walk_in', 'menunggu', '2026-10-20 23:03:10', NULL, NULL, NULL, NULL),
(10, 'G-003', 3, 10, 2, '2026-10-20', 'walk_in', 'menunggu', '2026-10-20 23:05:36', NULL, NULL, NULL, NULL),
(11, 'G-004', 4, 11, 2, '2026-10-20', 'walk_in', 'menunggu', '2026-10-20 23:33:23', NULL, NULL, NULL, NULL),
(12, 'K-002', 2, 12, 3, '2026-10-20', 'walk_in', 'menunggu', '2026-10-20 23:33:54', NULL, NULL, NULL, NULL),
(13, 'U-001', 1, 13, 1, '2026-10-21', 'walk_in', 'menunggu', '2026-10-21 00:36:56', NULL, NULL, NULL, NULL);

--
-- Triggers `antrean`
--
DELIMITER $$
CREATE TRIGGER `trg_after_update_antrean` AFTER UPDATE ON `antrean` FOR EACH ROW BEGIN
    IF NOT (OLD.status_antrean <=> NEW.status_antrean) THEN

        INSERT INTO log_antrean (
            id_antrean,
            status_lama,
            status_baru,
            keterangan
        )
        VALUES (
            NEW.id_antrean,
            OLD.status_antrean,
            NEW.status_antrean,
            CONCAT(
                'Status diubah oleh petugas ID: ',
                COALESCE(
                    CAST(NEW.id_petugas_pemanggil AS CHAR),
                    'System'
                )
            )
        );

    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_kuota_poli`
--

CREATE TABLE `jadwal_kuota_poli` (
  `id_jadwal` int UNSIGNED NOT NULL,
  `id_poli` int UNSIGNED NOT NULL,
  `tanggal` date NOT NULL,
  `kuota_maksimal` int UNSIGNED NOT NULL,
  `terisi` int UNSIGNED NOT NULL DEFAULT '0',
  `status_pelayanan` enum('buka','tutup','istirahat') NOT NULL DEFAULT 'buka',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `jadwal_kuota_poli`
--

INSERT INTO `jadwal_kuota_poli` (`id_jadwal`, `id_poli`, `tanggal`, `kuota_maksimal`, `terisi`, `status_pelayanan`, `created_at`) VALUES
(1, 1, '2026-10-20', 40, 6, 'buka', '2026-10-20 14:31:22'),
(3, 3, '2026-10-20', 25, 2, 'buka', '2026-10-20 22:32:29'),
(8, 2, '2026-10-20', 20, 4, 'buka', '2026-10-20 23:01:20'),
(13, 1, '2026-10-21', 40, 1, 'buka', '2026-10-21 00:36:56');

-- --------------------------------------------------------

--
-- Table structure for table `log_antrean`
--

CREATE TABLE `log_antrean` (
  `id_log` bigint UNSIGNED NOT NULL,
  `id_antrean` bigint UNSIGNED NOT NULL,
  `status_lama` varchar(20) DEFAULT NULL,
  `status_baru` varchar(20) NOT NULL,
  `waktu_perubahan` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `keterangan` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pasien`
--

CREATE TABLE `pasien` (
  `id_pasien` int UNSIGNED NOT NULL,
  `nik` char(16) DEFAULT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `no_telepon` varchar(15) NOT NULL,
  `alamat` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `pasien`
--

INSERT INTO `pasien` (`id_pasien`, `nik`, `nama_lengkap`, `no_telepon`, `alamat`, `created_at`) VALUES
(1, NULL, 'Budi Santoso', '081234567890', NULL, '2026-10-20 14:31:22'),
(2, NULL, 'Kristina', '085123456789', NULL, '2026-10-20 22:31:19'),
(3, NULL, 'Kristina', '085123456789', NULL, '2026-10-20 22:32:29'),
(4, NULL, 'Kristina', '085123456789', NULL, '2026-10-20 22:56:37'),
(5, NULL, 'Kristina', '085123456789', NULL, '2026-10-20 22:58:39'),
(6, NULL, 'Kristina', '085123456789', NULL, '2026-10-20 23:00:29'),
(7, NULL, 'Kristina', '085123456789', NULL, '2026-10-20 23:00:41'),
(8, NULL, 'andi', '08567842319', NULL, '2026-10-20 23:01:20'),
(9, NULL, 'andi', '08567842319', NULL, '2026-10-20 23:03:10'),
(10, NULL, 'andi', '08567842319', NULL, '2026-10-20 23:05:36'),
(11, NULL, 'andi', '08567842319', NULL, '2026-10-20 23:33:23'),
(12, NULL, 'ALBERTA', '08512435687', NULL, '2026-10-20 23:33:54'),
(13, NULL, 'Kristina', '085123456789', NULL, '2026-10-21 00:36:56');

-- --------------------------------------------------------

--
-- Table structure for table `petugas`
--

CREATE TABLE `petugas` (
  `id_petugas` int UNSIGNED NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `nama_lengkap` varchar(100) NOT NULL,
  `role` enum('admin','petugas_loket','dokter') NOT NULL,
  `id_poli` int UNSIGNED DEFAULT NULL,
  `status_petugas` enum('aktif','istirahat','off') NOT NULL DEFAULT 'off',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `petugas`
--

INSERT INTO `petugas` (`id_petugas`, `username`, `password`, `nama_lengkap`, `role`, `id_poli`, `status_petugas`, `created_at`) VALUES
(1, 'superadmin', '$2y$12$Hzuya4qDReXKKMLLcK7dZesp4HGL7G88/QaGlPuC0RpJC5vWzztx.', 'Administrator Sistem', 'admin', NULL, 'off', '2026-10-20 16:35:17'),
(2, 'dr_budi', '$2y$12$Hzuya4qDReXKKMLLcK7dZesp4HGL7G88/QaGlPuC0RpJC5vWzztx.', 'dr. Budi Santoso', 'dokter', 1, 'off', '2026-10-20 16:35:17'),
(3, 'dr_siti', '$2y$12$Hzuya4qDReXKKMLLcK7dZesp4HGL7G88/QaGlPuC0RpJC5vWzztx.', 'drg. Siti Rahma', 'dokter', 2, 'off', '2026-10-20 16:35:17'),
(4, 'petugas_loket', '$2y$12$Hzuya4qDReXKKMLLcK7dZesp4HGL7G88/QaGlPuC0RpJC5vWzztx.', 'Siti Loket', 'petugas_loket', NULL, 'off', '2026-10-20 16:35:17');

-- --------------------------------------------------------

--
-- Table structure for table `poli`
--

CREATE TABLE `poli` (
  `id_poli` int UNSIGNED NOT NULL,
  `kode_poli` varchar(10) NOT NULL,
  `nama_poli` varchar(100) NOT NULL,
  `prefix_antrean` char(2) NOT NULL,
  `kuota_harian` int UNSIGNED NOT NULL DEFAULT '50',
  `status_aktif` enum('buka','tutup') NOT NULL DEFAULT 'buka',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `poli`
--

INSERT INTO `poli` (`id_poli`, `kode_poli`, `nama_poli`, `prefix_antrean`, `kuota_harian`, `status_aktif`, `created_at`, `updated_at`) VALUES
(1, 'UMU', 'Poli Umum', 'U', 40, 'buka', '2026-10-20 14:31:21', '2026-10-20 14:31:21'),
(2, 'GIG', 'Poli Gigi', 'G', 20, 'buka', '2026-10-20 14:31:21', '2026-10-20 14:31:21'),
(3, 'KIA', 'Poli KIA & KB', 'K', 25, 'buka', '2026-10-20 14:31:21', '2026-10-20 14:31:21'),
(4, 'MAT', 'Poli Mata', 'M', 15, 'buka', '2026-10-20 14:31:21', '2026-10-20 14:31:21');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `antrean`
--
ALTER TABLE `antrean`
  ADD PRIMARY KEY (`id_antrean`),
  ADD UNIQUE KEY `unique_nomor_per_poli_tanggal` (`id_poli`,`tanggal_kunjungan`,`angka_antrean`),
  ADD KEY `idx_pencarian_antrean` (`tanggal_kunjungan`,`id_poli`,`status_antrean`),
  ADD KEY `id_pasien` (`id_pasien`),
  ADD KEY `id_petugas_pemanggil` (`id_petugas_pemanggil`);

--
-- Indexes for table `jadwal_kuota_poli`
--
ALTER TABLE `jadwal_kuota_poli`
  ADD PRIMARY KEY (`id_jadwal`),
  ADD UNIQUE KEY `unique_poli_tanggal` (`id_poli`,`tanggal`);

--
-- Indexes for table `log_antrean`
--
ALTER TABLE `log_antrean`
  ADD PRIMARY KEY (`id_log`),
  ADD KEY `id_antrean` (`id_antrean`);

--
-- Indexes for table `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`id_pasien`),
  ADD UNIQUE KEY `nik` (`nik`);

--
-- Indexes for table `petugas`
--
ALTER TABLE `petugas`
  ADD PRIMARY KEY (`id_petugas`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_poli` (`id_poli`);

--
-- Indexes for table `poli`
--
ALTER TABLE `poli`
  ADD PRIMARY KEY (`id_poli`),
  ADD UNIQUE KEY `kode_poli` (`kode_poli`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `antrean`
--
ALTER TABLE `antrean`
  MODIFY `id_antrean` bigint UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `jadwal_kuota_poli`
--
ALTER TABLE `jadwal_kuota_poli`
  MODIFY `id_jadwal` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `log_antrean`
--
ALTER TABLE `log_antrean`
  MODIFY `id_log` bigint UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pasien`
--
ALTER TABLE `pasien`
  MODIFY `id_pasien` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `petugas`
--
ALTER TABLE `petugas`
  MODIFY `id_petugas` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `poli`
--
ALTER TABLE `poli`
  MODIFY `id_poli` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `antrean`
--
ALTER TABLE `antrean`
  ADD CONSTRAINT `antrean_ibfk_1` FOREIGN KEY (`id_pasien`) REFERENCES `pasien` (`id_pasien`) ON DELETE CASCADE,
  ADD CONSTRAINT `antrean_ibfk_2` FOREIGN KEY (`id_poli`) REFERENCES `poli` (`id_poli`) ON DELETE CASCADE,
  ADD CONSTRAINT `antrean_ibfk_3` FOREIGN KEY (`id_petugas_pemanggil`) REFERENCES `petugas` (`id_petugas`) ON DELETE SET NULL;

--
-- Constraints for table `jadwal_kuota_poli`
--
ALTER TABLE `jadwal_kuota_poli`
  ADD CONSTRAINT `jadwal_kuota_poli_ibfk_1` FOREIGN KEY (`id_poli`) REFERENCES `poli` (`id_poli`) ON DELETE CASCADE;

--
-- Constraints for table `log_antrean`
--
ALTER TABLE `log_antrean`
  ADD CONSTRAINT `log_antrean_ibfk_1` FOREIGN KEY (`id_antrean`) REFERENCES `antrean` (`id_antrean`) ON DELETE CASCADE;

--
-- Constraints for table `petugas`
--
ALTER TABLE `petugas`
  ADD CONSTRAINT `petugas_ibfk_1` FOREIGN KEY (`id_poli`) REFERENCES `poli` (`id_poli`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
