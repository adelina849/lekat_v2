-- 2023-05-29
	-- 1. Melakukan penambahan field KLAP_ISAKTIF pada tb_klaporan
		-- tinyint(4)	
		-- untuk menentukan apakah laporan aktif atau tidak, 0 : aktif dan 1 tidak aktif
	-- 2. Melakukan penambahan field LAP_JUMROW pada tb_laporan
		-- tinyint(4)	
		-- Untuk menentukan jumlah row header pada laporan agar mempermudah cetak excel
-- 2023-06-19
		UPDATE tb_jabatan SET ket_jabatan = 'Admin Kecamatan adalah petugas yang bertugas untuk menjalankan aplikasi di lingkungan Kecamatan'
		WHERE id_jabatan = 0;

		UPDATE tb_jabatan SET ket_jabatan = 'Admin Kecamatan adalah petugas yang bertugas untuk menjalankan aplikasi di tingkat Kabupaten. Admin Kabupaten juga bertugas untuk melakukan pengaturan periode lapoaran untuk Admin Kecamatan'
		WHERE id_jabatan = 37;
		
		ALTER TABLE `tb_laporan` ADD `LAP_PJ` VARCHAR(50) NOT NULL COMMENT 'Untuk menentukan siapkah PIC yang bertugas mellihat laporan ini di admin kabuaten' AFTER `LAP_JUMROW`;
		
		ALTER TABLE `tb_laporan` ADD `LAP_ISAKTIF` TINYINT NOT NULL COMMENT 'untuk menentukan apakah laporan aktif atau tidak, 0 : aktif dan 1 tidak aktif' AFTER `LAP_PJ`;
		
		UPDATE tb_laporan SET LAP_ISAKTIF = '1' WHERE DATE(LAP_DTINS) < '2023-05-01';

-- 2023-06-26
	ALTER TABLE `tb_buat_laporan` ADD `BLAP_USER_CATATAN` VARCHAR(50) NOT NULL AFTER `BLAP_CATATAN`;
	
-- 2023-07-03
	ALTER TABLE `tb_kec` ADD `KEC_BUCAMAT` VARCHAR(50) NOT NULL AFTER `KEC_NAMA`;
	
	SELECT jawaban1, UPPER(jawaban1) AS user2,CONCAT(UPPER(jawaban1),'2023') AS user3, MD5(UPPER(jawaban1)) AS pass2, MD5( CONCAT(UPPER(jawaban1),'2023') ) AS pass3 FROM `tb_akun` WHERE user <> 'admin';

	UPDATE tb_akun SET user = CONCAT(UPPER(jawaban1),'2023'), pass = MD5( CONCAT(UPPER(jawaban1),'2023') ) WHERE user <> 'admin';
	
-- 2023-12-05
	ALTER TABLE `tb_karyawan` ADD `nom_id_desa` VARCHAR(25) NOT NULL DEFAULT '' COMMENT 'Isikan dengan id_desa' AFTER `status_kantor`, ADD `nom_nama_desa` VARCHAR(35) NOT NULL DEFAULT '' COMMENT 'Isikan dengan nama desa' AFTER `nom_id_desa`;
	
	ALTER TABLE `tb_karyawan` ADD `jenis_kelamin` VARCHAR(10) NOT NULL AFTER `nom_nama_desa`, ADD `tempat_lahir` VARCHAR(25) NOT NULL AFTER `jenis_kelamin`, ADD `tgl_lahir` DATE NULL DEFAULT NULL AFTER `tempat_lahir`, ADD `nip` VARCHAR(35) NOT NULL AFTER `tgl_lahir`, ADD `pangkat_gol` VARCHAR(35) NOT NULL AFTER `nip`, ADD `tmt_gol_ruang` VARCHAR(25) NOT NULL AFTER `pangkat_gol`, ADD `jabatan` VARCHAR(35) NOT NULL AFTER `tmt_gol_ruang`, ADD `unit_kerja` VARCHAR(35) NOT NULL AFTER `jabatan`, ADD `status_kepeg` VARCHAR(35) NOT NULL AFTER `unit_kerja`, ADD `keterangan` TEXT NOT NULL AFTER `status_kepeg`;
	
	ALTER TABLE `tb_karyawan` CHANGE `tgl_lahir` `tgl_lahir` DATE NULL DEFAULT NULL;
	
	ALTER TABLE `tb_karyawan` ADD `kelompok_jabatan` VARCHAR(20) NOT NULL AFTER `keterangan`, ADD `idx_jabatan` INT NOT NULL AFTER `kelompok_jabatan`;