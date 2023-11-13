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