<?php
	class M_gl_karyawan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function get_data_kantor($cari)
		{
			$query = "SELECT * FROM tb_kantor ".$cari."";
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function get_karyawan_row($cari)
		{
			$query = "SELECT * FROM tb_karyawan ".$cari.";";
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function get_no_karyawan()
		{
			$query = 
			"
				SELECT CONCAT('GFD',ORD) AS NO_KRY
				FROM
				(
					SELECT
						CASE
						WHEN (ORD >= 10 AND ORD < 99) THEN CONCAT('000',CAST(ORD AS CHAR))
						WHEN (ORD >= 100 AND ORD < 999) THEN CONCAT('00',CAST(ORD AS CHAR))
						WHEN (ORD >= 1000 AND ORD < 9999) THEN CONCAT('0',CAST(ORD AS CHAR))
						WHEN ORD >= 10000 THEN CAST(ORD AS CHAR)
						ELSE CONCAT('0000',CAST(ORD AS CHAR))
						END As ORD
					From
					(
						SELECT COUNT(id_karyawan)+1 AS ORD FROM tb_karyawan
					) AS A
				) AS AA
			";
			
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function get_karyawan_jabatan_row_when_login($cari)
		{
			/*
			SELECT 
						A.*
						,COALESCE(B.nama_jabatan,'') AS nama_jabatan
						,COALESCE(C.nama_kantor,'') AS nama_kantor
						,COALESCE(C.pemilik,'') AS pemilik
						,COALESCE(C.alamat,'') AS alamat
						,COALESCE(C.tlp,'') AS tlp
					FROM tb_karyawan AS A
					LEFT JOIN tb_jabatan AS B ON A.id_jabatan = B.id_jabatan AND A.kode_kantor = B.kode_kantor
					LEFT JOIN tb_kantor AS C ON A.kode_kantor = C.kode_kantor
			*/
			$query = 
					"
					
					
					SELECT 
						A.id_karyawan,
						A.no_karyawan,
						A.nik_karyawan,
						A.nama_karyawan,
						A.pnd,
						A.tlp,
						A.email,
						A.tmp_lahir,
						A.tgl_lahir,
						A.kelamin,
						A.sts_nikah,
						A.avatar,
						A.avatar_url,
						A.alamat,
						A.ket_karyawan,
						A.isAktif,
						A.alasan_phk,
						A.tgl_phk,
						A.isDokter,
						A.lamar_via,
						A.nilai_ujian,
						A.ket_hasil_ujian,
						A.user,
						A.pass,
						A.tgl_updt_pass,
						A.tgl_ins,
						A.tgl_updt,
						A.user_ins,
						A.user_updt,
						A.kode_kantor,
						A.hirarki,

						COALESCE(B.id_jabatan,'') AS id_jabatan,
						CASE WHEN A.nama_karyawan = 'ADMIN APLIKASI' THEN
							'Admin Aplikasi'
						ELSE
							COALESCE(B.nama_jabatan,'')
						END AS nama_jabatan
						,COALESCE(B.kode_dept,'') AS kode_dept
						,COALESCE(B.nama_dept,'') AS nama_dept
						,COALESCE(B.tgl_dari,'') AS tgl_aktif
						,DATEDIFF(DATE(NOW()),COALESCE(B.tgl_dari,'')) AS MASA_KERJA
						,COALESCE(C.nama_kantor,'') AS nama_kantor
						,COALESCE(C.pemilik,'') AS pemilik
						,COALESCE(C.kota,'') AS kota
						,COALESCE(C.alamat,'') AS alamat
						,COALESCE(C.tlp,'') AS tlp
						,COALESCE(C.isModePst,'') AS isModePst
						,COALESCE(C.jenis_faktur,'') AS jenis_faktur
						,CASE 
							WHEN (YEAR(CURDATE()) - YEAR(A.tgl_lahir)) < 150 THEN 
								(YEAR(CURDATE()) - YEAR(A.tgl_lahir))
							ELSE
								0
							END
						AS USIA
					FROM tb_karyawan AS A
					LEFT JOIN
					(
						SELECT 
							A.id_kj,
							A.id_karyawan,
							A.id_jabatan,
							A.id_dept,
							A.kode_promosi,
							A.no_surat,
							A.periode,
							A.rekomendasi,
							A.tgl_dari,
							A.tgl_sampai,
							A.keterangan,
							A.tipe,
							A.masa_percobaan,
							A.status,
							A.isAktif,
							A.nilai,
							A.ket_nilai,
							A.tgl_ins,
							A.tgl_updt,
							A.user_ins,
							A.user_updt,
							A.kode_kantor
							,COALESCE(B.nama_jabatan,'') AS nama_jabatan
							,COALESCE(B.hirarki,0) AS hirarki
							,COALESCE(C.kode_dept,'') AS kode_dept
							,COALESCE(C.nama_dept,'') AS nama_dept
						FROM tb_karyawan_jabatan AS A
						LEFT JOIN tb_jabatan AS B ON A.id_jabatan = B.id_jabatan AND A.kode_kantor = B.kode_kantor
						LEFT JOIN tb_dept AS C ON A.id_dept = C.id_dept AND A.kode_kantor = C.kode_kantor
						WHERE A.status = 'LULUS' 
						GROUP BY
							A.id_kj,
							A.id_karyawan,
							A.id_jabatan,
							A.id_dept,
							A.kode_promosi,
							A.no_surat,
							A.periode,
							A.rekomendasi,
							A.tgl_dari,
							A.tgl_sampai,
							A.keterangan,
							A.tipe,
							A.masa_percobaan,
							A.status,
							A.isAktif,
							A.nilai,
							A.ket_nilai,
							A.tgl_ins,
							A.tgl_updt,
							A.user_ins,
							A.user_updt,
							A.kode_kantor
							,COALESCE(B.nama_jabatan,'')
							,COALESCE(B.hirarki,0)
							,COALESCE(C.kode_dept,'')
							,COALESCE(C.nama_dept,'')
							-- ,COALESCE(B.tgl_dari,'')
						HAVING MAX(A.tgl_ins)
						-- ORDER BY COALESCE(B.hirarki,0) DESC,A.tgl_ins DESC
						ORDER BY A.tgl_ins DESC
						-- LIMIT 0,1
					) AS B ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
					LEFT JOIN tb_kantor AS C ON A.kode_kantor = C.kode_kantor
					".$cari."
					LIMIT 0,1
					;";
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		
		
		function get_karyawan_jabatan_row($cari)
		{
			/*
			SELECT 
						A.*
						,COALESCE(B.nama_jabatan,'') AS nama_jabatan
						,COALESCE(C.nama_kantor,'') AS nama_kantor
						,COALESCE(C.pemilik,'') AS pemilik
						,COALESCE(C.alamat,'') AS alamat
						,COALESCE(C.tlp,'') AS tlp
					FROM tb_karyawan AS A
					LEFT JOIN tb_jabatan AS B ON A.id_jabatan = B.id_jabatan AND A.kode_kantor = B.kode_kantor
					LEFT JOIN tb_kantor AS C ON A.kode_kantor = C.kode_kantor
			*/
			$query = 
					"
					
					
					SELECT 
						A.id_karyawan,
						A.no_karyawan,
						A.nik_karyawan,
						A.nama_karyawan,
						A.pnd,
						A.tlp,
						A.email,
						A.tmp_lahir,
						A.tgl_lahir,
						A.kelamin,
						A.sts_nikah,
						A.avatar,
						A.avatar_url,
						A.alamat,
						A.ket_karyawan,
						A.isAktif,
						A.alasan_phk,
						A.tgl_phk,
						A.isDokter,
						A.lamar_via,
						A.nilai_ujian,
						A.ket_hasil_ujian,
						A.user,
						A.pass,
						A.tgl_updt_pass,
						A.tgl_ins,
						A.tgl_updt,
						A.user_ins,
						A.user_updt,
						A.kode_kantor
					FROM tb_karyawan AS A
					".$cari."
					;";
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		
		function list_karyawan_with_jabatan_terbaru($cari,$order_by)
		{
			/*
			SELECT 
						A.*
						,COALESCE(B.nama_jabatan,'') AS nama_jabatan
						,COALESCE(C.nama_kantor,'') AS nama_kantor
						,COALESCE(C.pemilik,'') AS pemilik
						,COALESCE(C.alamat,'') AS alamat
						,COALESCE(C.tlp,'') AS tlp
					FROM tb_karyawan AS A
					LEFT JOIN tb_jabatan AS B ON A.id_jabatan = B.id_jabatan AND A.kode_kantor = B.kode_kantor
					LEFT JOIN tb_kantor AS C ON A.kode_kantor = C.kode_kantor
			*/
			$query = 
					"
					
					
					SELECT 
						A.id_karyawan,
						A.no_karyawan,
						A.nik_karyawan,
						A.nama_karyawan,
						A.pnd,
						A.tlp,
						A.email,
						A.tmp_lahir,
						A.tgl_lahir,
						A.kelamin,
						A.sts_nikah,
						A.avatar,
						A.avatar_url,
						A.alamat,
						A.ket_karyawan,
						A.isAktif,
						A.alasan_phk,
						A.tgl_phk,
						A.isDokter,
						A.lamar_via,
						A.nilai_ujian,
						A.ket_hasil_ujian,
						A.user,
						A.pass,
						A.tgl_updt_pass,
						A.tgl_ins,
						A.tgl_updt,
						A.user_ins,
						A.user_updt,
						A.kode_kantor,
						A.hirarki,

						COALESCE(B.id_jabatan,'') AS id_jabatan,
						CASE WHEN A.nama_karyawan = 'ADMIN APLIKASI' THEN
							'Admin Aplikasi'
						ELSE
							COALESCE(B.nama_jabatan,'')
						END AS nama_jabatan
						,COALESCE(B.kode_dept,'') AS kode_dept
						,COALESCE(B.nama_dept,'') AS nama_dept
						,COALESCE(B.tgl_dari,'') AS tgl_aktif
						,DATEDIFF(DATE(NOW()),COALESCE(B.tgl_dari,'')) AS MASA_KERJA
						,COALESCE(C.nama_kantor,'') AS nama_kantor
						,COALESCE(C.pemilik,'') AS pemilik
						,COALESCE(C.kota,'') AS kota
						,COALESCE(C.alamat,'') AS alamat
						,COALESCE(C.tlp,'') AS tlp
						,COALESCE(C.isModePst,'') AS isModePst
						,CASE 
							WHEN (YEAR(CURDATE()) - YEAR(A.tgl_lahir)) < 150 THEN 
								(YEAR(CURDATE()) - YEAR(A.tgl_lahir))
							ELSE
								0
							END
						AS USIA
					FROM tb_karyawan AS A
					LEFT JOIN
					(
						SELECT 
							A.id_kj,
							A.id_karyawan,
							A.id_jabatan,
							A.id_dept,
							A.kode_promosi,
							A.no_surat,
							A.periode,
							A.rekomendasi,
							A.tgl_dari,
							A.tgl_sampai,
							A.keterangan,
							A.tipe,
							A.masa_percobaan,
							A.status,
							A.isAktif,
							A.nilai,
							A.ket_nilai,
							A.tgl_ins,
							A.tgl_updt,
							A.user_ins,
							A.user_updt,
							A.kode_kantor
							,COALESCE(B.nama_jabatan,'') AS nama_jabatan
							,COALESCE(B.hirarki,0) AS hirarki
							,COALESCE(C.kode_dept,'') AS kode_dept
							,COALESCE(C.nama_dept,'') AS nama_dept
						FROM tb_karyawan_jabatan AS A
						LEFT JOIN tb_jabatan AS B ON A.id_jabatan = B.id_jabatan AND A.kode_kantor = B.kode_kantor
						LEFT JOIN tb_dept AS C ON A.id_dept = C.id_dept AND A.kode_kantor = C.kode_kantor
						WHERE A.status = 'LULUS' 
						GROUP BY
							A.id_kj,
							A.id_karyawan,
							A.id_jabatan,
							A.id_dept,
							A.kode_promosi,
							A.no_surat,
							A.periode,
							A.rekomendasi,
							A.tgl_dari,
							A.tgl_sampai,
							A.keterangan,
							A.tipe,
							A.masa_percobaan,
							A.status,
							A.isAktif,
							A.nilai,
							A.ket_nilai,
							A.tgl_ins,
							A.tgl_updt,
							A.user_ins,
							A.user_updt,
							A.kode_kantor
							,COALESCE(B.nama_jabatan,'')
							,COALESCE(B.hirarki,0)
							,COALESCE(C.kode_dept,'')
							,COALESCE(C.nama_dept,'')
							-- ,COALESCE(B.tgl_dari,'')
						HAVING MAX(A.tgl_ins)
						-- ORDER BY COALESCE(B.hirarki,0) DESC,A.tgl_ins DESC
						ORDER BY A.tgl_ins DESC
						-- LIMIT 0,1
					) AS B ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
					LEFT JOIN tb_kantor AS C ON A.kode_kantor = C.kode_kantor
					".$cari."
					LIMIT 0,1
					;";
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_karyawan_with_jabatan_terbaru_for_transaksi($cari,$order_by)
		{
			/*
			SELECT 
						A.*
						,COALESCE(B.nama_jabatan,'') AS nama_jabatan
						,COALESCE(C.nama_kantor,'') AS nama_kantor
						,COALESCE(C.pemilik,'') AS pemilik
						,COALESCE(C.alamat,'') AS alamat
						,COALESCE(C.tlp,'') AS tlp
					FROM tb_karyawan AS A
					LEFT JOIN tb_jabatan AS B ON A.id_jabatan = B.id_jabatan AND A.kode_kantor = B.kode_kantor
					LEFT JOIN tb_kantor AS C ON A.kode_kantor = C.kode_kantor
			*/
			$query = 
					"
					
					
					SELECT 
						A.id_karyawan,
						A.no_karyawan,
						A.nik_karyawan,
						A.nama_karyawan,
						A.pnd,
						A.tlp,
						A.email,
						A.tmp_lahir,
						A.tgl_lahir,
						A.kelamin,
						A.sts_nikah,
						A.avatar,
						A.avatar_url,
						A.alamat,
						A.ket_karyawan,
						A.isAktif,
						A.alasan_phk,
						A.tgl_phk,
						A.isDokter,
						A.lamar_via,
						A.nilai_ujian,
						A.ket_hasil_ujian,
						A.user,
						A.pass,
						A.tgl_updt_pass,
						A.tgl_ins,
						A.tgl_updt,
						A.user_ins,
						A.user_updt,
						A.kode_kantor,
						A.hirarki,

						COALESCE(B.id_jabatan,'') AS id_jabatan,
						CASE WHEN A.nama_karyawan = 'ADMIN APLIKASI' THEN
							'Admin Aplikasi'
						ELSE
							COALESCE(B.nama_jabatan,'')
						END AS nama_jabatan
						,COALESCE(B.kode_dept,'') AS kode_dept
						,COALESCE(B.nama_dept,'') AS nama_dept
						,COALESCE(B.tgl_dari,'') AS tgl_aktif
						,DATEDIFF(DATE(NOW()),COALESCE(B.tgl_dari,'')) AS MASA_KERJA
						,COALESCE(C.nama_kantor,'') AS nama_kantor
						,COALESCE(C.pemilik,'') AS pemilik
						,COALESCE(C.kota,'') AS kota
						,COALESCE(C.alamat,'') AS alamat
						,COALESCE(C.tlp,'') AS tlp
						,COALESCE(C.isModePst,'') AS isModePst
						,CASE 
							WHEN (YEAR(CURDATE()) - YEAR(A.tgl_lahir)) < 150 THEN 
								(YEAR(CURDATE()) - YEAR(A.tgl_lahir))
							ELSE
								0
							END
						AS USIA
					FROM tb_karyawan AS A
					LEFT JOIN
					(
						SELECT 
							A.id_kj,
							A.id_karyawan,
							A.id_jabatan,
							A.id_dept,
							A.kode_promosi,
							A.no_surat,
							A.periode,
							A.rekomendasi,
							A.tgl_dari,
							A.tgl_sampai,
							A.keterangan,
							A.tipe,
							A.masa_percobaan,
							A.status,
							A.isAktif,
							A.nilai,
							A.ket_nilai,
							A.tgl_ins,
							A.tgl_updt,
							A.user_ins,
							A.user_updt,
							A.kode_kantor
							,COALESCE(B.nama_jabatan,'') AS nama_jabatan
							,COALESCE(B.hirarki,0) AS hirarki
							,COALESCE(C.kode_dept,'') AS kode_dept
							,COALESCE(C.nama_dept,'') AS nama_dept
						FROM tb_karyawan_jabatan AS A
						LEFT JOIN tb_jabatan AS B ON A.id_jabatan = B.id_jabatan AND A.kode_kantor = B.kode_kantor
						LEFT JOIN tb_dept AS C ON A.id_dept = C.id_dept AND A.kode_kantor = C.kode_kantor
						WHERE A.status = 'LULUS' 
						GROUP BY
							A.id_kj,
							A.id_karyawan,
							A.id_jabatan,
							A.id_dept,
							A.kode_promosi,
							A.no_surat,
							A.periode,
							A.rekomendasi,
							A.tgl_dari,
							A.tgl_sampai,
							A.keterangan,
							A.tipe,
							A.masa_percobaan,
							A.status,
							A.isAktif,
							A.nilai,
							A.ket_nilai,
							A.tgl_ins,
							A.tgl_updt,
							A.user_ins,
							A.user_updt,
							A.kode_kantor
							,COALESCE(B.nama_jabatan,'')
							,COALESCE(B.hirarki,0)
							,COALESCE(C.kode_dept,'')
							,COALESCE(C.nama_dept,'')
							-- ,COALESCE(B.tgl_dari,'')
						HAVING MAX(A.tgl_ins)
						-- ORDER BY COALESCE(B.hirarki,0) DESC,A.tgl_ins DESC
						ORDER BY A.tgl_ins DESC
						-- LIMIT 0,1
					) AS B ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
					LEFT JOIN tb_kantor AS C ON A.kode_kantor = C.kode_kantor
					".$cari."
					;";
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_karyawan_yang_tersedia_dokter($cari,$limit,$offset)
		{
			$query = 
			"
				SELECT A.id_karyawan,A.no_karyawan,A.nik_karyawan,A.nama_karyawan,A.avatar, A.avatar_url
					,DATEDIFF(DATE(NOW()),COALESCE(A.tgl_diterima,'')) AS lama_kerja
					
					
					,COALESCE(B.id_h_penjualan,'') AS id_h_penjualan1
					,COALESCE(B.id_dokter2,'') AS id_dokter2
					,COALESCE(B.no_faktur,'') AS no_faktur_ass1
					,COALESCE(B.no_costmer,'') AS no_costumer1
					,COALESCE(B.nama_costumer,'') AS nama_costumer1
					
				FROM tb_karyawan AS A
				LEFT JOIN
				(
					SELECT id_h_penjualan, kode_kantor, no_faktur, no_costmer, nama_costumer, id_dokter2 
					FROM tb_h_penjualan
					WHERE sts_penjualan = 'PEMBAYARAN' AND (no_faktur LIKE '%%' OR nama_costumer LIKE '%%')
					-- AND tgl_h_penjualan BETWEEN '2020-03-15' AND '2020-03-19'
					ANd DATE(tgl_h_penjualan) = DATE(NOW())
					-- HAVING MAX(tgl_ins)
					ORDER BY tgl_ins DESC
					-- LIMIT 0,1
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_karyawan = B.id_dokter2
				
				".$cari."
				
				-- WHERE A.id_karyawan = 'KRY2020011000002' 
				-- AND A.isDokter = 'THERAPIST'
				ORDER BY A.nama_karyawan DESC LIMIT ".$offset.",".$limit."
				;
			";
			
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_karyawan_yang_tersedia_therapist($cari,$limit,$offset)
		{
			$query = 
			"
				SELECT A.id_karyawan,A.no_karyawan,A.nik_karyawan,A.nama_karyawan
					,A.pnd, A.kelamin, A.alamat, id_jabatan,A.tlp, A.email, A.tmp_lahir, A.tgl_lahir, A.ket_karyawan
					,A.avatar, A.avatar_url
					,DATEDIFF(DATE(NOW()),COALESCE(A.tgl_diterima,'')) AS lama_kerja
					
					,COALESCE(B.id_h_penjualan,'') AS id_h_penjualan1
					,COALESCE(B.id_ass_dok,'') AS id_ass_dok1
					,COALESCE(B.no_faktur,'') AS no_faktur_ass1
					,COALESCE(B.no_costmer,'') AS no_costumer1
					,COALESCE(B.nama_costumer,'') AS nama_costumer1
					
					,COALESCE(C.id_h_penjualan,'') AS id_h_penjualan2
					,COALESCE(C.id_ass_dok2,'') AS id_ass_dok2
					,COALESCE(C.no_faktur,'') AS no_faktur_ass2
					,COALESCE(C.no_costmer,'') AS no_costumer2
					,COALESCE(C.nama_costumer,'') AS nama_costumer2
				FROM tb_karyawan AS A
				LEFT JOIN
				(
					SELECT id_h_penjualan, kode_kantor, no_faktur, no_costmer, nama_costumer, id_ass_dok 
					FROM tb_h_penjualan
					WHERE sts_penjualan = 'PEMBAYARAN' AND (no_faktur LIKE '%%' OR nama_costumer LIKE '%%')
					-- AND tgl_h_penjualan BETWEEN '2020-03-15' AND '2020-03-19'
					ANd DATE(tgl_h_penjualan) = DATE(NOW())
					-- HAVING MAX(tgl_ins)
					ORDER BY tgl_ins DESC
					-- LIMIT 0,1
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_karyawan = B.id_ass_dok
				LEFT JOIN
				(
					SELECT id_h_penjualan, kode_kantor, no_faktur, no_costmer, nama_costumer, id_ass_dok2 
					FROM tb_h_penjualan
					WHERE sts_penjualan = 'PEMBAYARAN' AND (no_faktur LIKE '%%' OR nama_costumer LIKE '%%')
					-- AND tgl_h_penjualan BETWEEN '2020-03-15' AND '2020-03-19'
					ANd DATE(tgl_h_penjualan) = DATE(NOW())
					-- HAVING MAX(tgl_ins)
					ORDER BY tgl_ins DESC
					-- LIMIT 0,1
				) AS C ON A.kode_kantor = C.kode_kantor AND A.id_karyawan = C.id_ass_dok2
				
				".$cari."
				
				-- WHERE A.id_karyawan = 'KRY2020011000002' 
				-- AND A.isDokter = 'THERAPIST'
				ORDER BY A.nama_karyawan DESC LIMIT ".$offset.",".$limit."
				;
			";
			
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		/*
		function get_karyawan_penangung_jawab_gedung($cari)
		{
			
			$query = 
					"
					SELECT
						A.*
					FROM tb_karyawan AS A
					LEFT JOIN tb_gedung AS B ON A.kode_kantor = B.kode_kantor AND A.id_karyawan = B.id_karyawan
					".$cari."
					
					";
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		*/
		
		function list_karyawan_limit($cari,$limit,$offset)
		{
			/*
			$query = "
				SELECT 
					*
					,CASE 
						WHEN (YEAR(CURDATE()) - YEAR(tgl_lahir)) < 150 THEN 
							(YEAR(CURDATE()) - YEAR(tgl_lahir))
						ELSE
							0
						END
					AS USIA
				FROM
				tb_karyawan ".$cari." 
				ORDER BY tgl_ins DESC LIMIT ".$offset.",".$limit
			;
			*/
			
			
			
			$query = "
				SELECT
					A.id_karyawan,
					A.id_jabatan,
					A.no_karyawan,
					A.nik_karyawan,
					A.nama_karyawan,
					A.pnd,
					A.tlp,
					A.email,
					A.tmp_lahir,
					A.tgl_lahir,
					A.kelamin,
					A.sts_nikah,
					A.avatar,
					A.avatar_url,
					A.alamat,
					A.ket_karyawan,
					A.isAktif,
					A.isSales,
					A.alasan_phk,
					A.tgl_phk,
					A.tgl_diterima,
					
					ROUND
					(
						TIMESTAMPDIFF(MONTH, A.tgl_diterima, NOW()) 
						/
						12
					,1
					)
					AS MASA_KERJA,
					
					DATEDIFF(DATE(NOW()),COALESCE(A.tgl_diterima,'')) AS lama_kerja,
					A.isDokter,
					A.lamar_via,
					A.nilai_ujian,
					A.ket_hasil_ujian,
					A.user,
					A.pass,
					A.tgl_updt_pass,
					A.bpjs_kes,
					A.bpjs_ket,
					A.tgl_ins,
					A.tgl_updt,
					A.user_ins,
					A.user_updt,
					A.kode_kantor,
					
					SUBSTRING(MAX(CONCAT(A.tgl_dari,A.nama_jabatan)),11,50) AS nama_jabatan,
					MAX(A.tgl_dari) AS tgl_dari,
					A.nama_kantor,
					A.pemilik,
					A.alamat_kantor,
					A.tlp_kantor,
					A.USIA,
					A.NOMINAL_PINJAM,
					A.NOMINAL_BAYAR
					
				
				FROM
				(
					SELECT 
						A.*
						,COALESCE(B.nama_jabatan,'') AS nama_jabatan
						,CASE
							WHEN COALESCE(B.tgl_dari,'') = '' THEN
								DATE(A.tgl_ins)
							ELSE
								COALESCE(B.tgl_dari,'')
							END
							AS tgl_dari
						,COALESCE(C.nama_kantor,'') AS nama_kantor
						,COALESCE(C.pemilik,'') AS pemilik
						,COALESCE(C.alamat,'') AS alamat_kantor
						,COALESCE(C.tlp,'') AS tlp_kantor
						,CASE 
							WHEN (YEAR(CURDATE()) - YEAR(A.tgl_lahir)) < 150 THEN 
								(YEAR(CURDATE()) - YEAR(A.tgl_lahir))
							ELSE
								0
							END
						AS USIA
						,COALESCE(D.NOMINAL_PINJAM,0) AS NOMINAL_PINJAM
						,COALESCE(E.NOMINAL_BAYAR,0) AS NOMINAL_BAYAR
					FROM tb_karyawan AS A
					LEFT JOIN
					(
						/*
						SELECT A.* 
							,COALESCE(B.nama_jabatan,'') AS nama_jabatan
							,COALESCE(B.hirarki,0) AS hirarki
						*/
						SELECT A.id_karyawan,A.tgl_dari,A.kode_kantor
							,A.tgl_ins
							,COALESCE(B.nama_jabatan,'') AS nama_jabatan
							,COALESCE(B.hirarki,0) AS hirarki
						FROM tb_karyawan_jabatan AS A
						LEFT JOIN tb_jabatan AS B ON A.id_jabatan = B.id_jabatan AND A.kode_kantor = B.kode_kantor
						WHERE A.status = 'LULUS' 
						-- AND A.id_karyawan = 'OFCJRT2020030200001'
						-- OFCJRT2020030200001
						-- ORDER BY COALESCE(B.hirarki,0) DESC,A.tgl_ins DESC
						GROUP BY A.id_karyawan,A.tgl_dari,A.kode_kantor,A.tgl_ins,COALESCE(B.nama_jabatan,''),COALESCE(B.hirarki,0)
						-- HAVING MAX(A.tgl_ins)
						ORDER BY A.tgl_ins ASC
						-- LIMIT 0,1
						-- LIMIT 1
					) AS B ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
					LEFT JOIN tb_kantor AS C ON A.kode_kantor = C.kode_kantor
					LEFT JOIN (SELECT id_karyawan,kode_kantor,SUM(nominal) AS NOMINAL_PINJAM FROM tb_uang_keluar GROUP BY id_karyawan,kode_kantor) AS D ON A.id_karyawan = D.id_karyawan AND A.kode_kantor = D.kode_kantor
					LEFT JOIN (SELECT id_karyawan,kode_kantor,SUM(nominal) AS NOMINAL_BAYAR FROM tb_uang_masuk GROUP BY id_karyawan,kode_kantor) AS E ON A.id_karyawan = E.id_karyawan AND A.kode_kantor = E.kode_kantor
					
					".$cari." 
				) AS A
				GROUP BY
				A.id_karyawan,
					A.id_jabatan,
					A.no_karyawan,
					A.nik_karyawan,
					A.nama_karyawan,
					A.pnd,
					A.tlp,
					A.email,
					A.tmp_lahir,
					A.tgl_lahir,
					A.kelamin,
					A.sts_nikah,
					A.avatar,
					A.avatar_url,
					A.alamat,
					A.ket_karyawan,
					A.isAktif,
					A.alasan_phk,
					A.tgl_phk,
					A.tgl_diterima,
					A.isDokter,
					A.lamar_via,
					A.nilai_ujian,
					A.ket_hasil_ujian,
					A.user,
					A.pass,
					A.tgl_updt_pass,
					A.bpjs_kes,
					A.bpjs_ket,
					A.tgl_ins,
					A.tgl_updt,
					A.user_ins,
					A.user_updt,
					A.kode_kantor,
					
					
					A.nama_kantor,
					A.pemilik,
					A.alamat_kantor,
					A.tlp_kantor,
					A.USIA,
					A.NOMINAL_PINJAM,
					A.NOMINAL_BAYAR
				ORDER BY A.tgl_ins DESC LIMIT ".$offset.",".$limit;
			
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_karyawan_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(A.id_karyawan) AS JUMLAH FROM tb_karyawan AS A ".$cari);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function mutasi($id_karyawan,$kode_kantor_lama,$kode_kantor_baru)
		{
			//MASUKAN DATA KARYAWAN DI KANTOR BARU
			$query = "
				INSERT INTO tb_karyawan
				(
					id_karyawan,
					id_jabatan,
					no_karyawan,
					nik_karyawan,
					nama_karyawan,
					pnd,
					tlp,
					email,
					tmp_lahir,
					tgl_lahir,
					kelamin,
					sts_nikah,
					avatar,
					avatar_url,
					alamat,
					ket_karyawan,
					isAktif,
					alasan_phk,
					tgl_phk,
					tgl_diterima,
					isDokter,
					lamar_via,
					nilai_ujian,
					ket_hasil_ujian,
					user,
					pass,
					tgl_updt_pass,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				SELECT
					id_karyawan,
					id_jabatan,
					no_karyawan,
					nik_karyawan,
					nama_karyawan,
					pnd,
					tlp,
					email,
					tmp_lahir,
					tgl_lahir,
					kelamin,
					sts_nikah,
					avatar,
					avatar_url,
					alamat,
					ket_karyawan,
					isAktif,
					alasan_phk,
					NOW(),
					tgl_diterima,
					isDokter,
					lamar_via,
					nilai_ujian,
					ket_hasil_ujian,
					user,
					pass,
					tgl_updt_pass,
					NOW(),
					NOW(),
					user_ins,
					user_updt,
					'".$kode_kantor_baru."'
				FROM tb_karyawan
				WHERE kode_kantor = '".$kode_kantor_lama."' AND id_karyawan = '".$id_karyawan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($query);
			/*SIMPAN DAN CATAT QUERY*/
			//MASUKAN DATA KARYAWAN DI KANTOR BARU
			
			
			//UBAH STATUS NYA DI TB_KARYAWAN
			$query = "
				UPDATE tb_karyawan SET
					isAktif = 'MUTASI',
					tgl_phk = NOW()
				WHERE kode_kantor = '".$kode_kantor_lama."' AND id_karyawan = '".$id_karyawan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($query);
			/*SIMPAN DAN CATAT QUERY*/
			//UBAH STATUS NYA DI TB_KARYAWAN
		}
		
		
		function simpan
		(	
			$id_jabatan,
			$no_karyawan,
			$nik_karyawan,
			$nama_karyawan,
			$pnd,
			$tlp,
			$email,
			$tmp_lahir,
			$tgl_lahir,
			$kelamin,
			$sts_nikah,
			$avatar,
			$avatar_url,
			$alamat,
			$ket_karyawan,
			$tgl_diterima,
			$isAktif,
			$isDokter,
			$lamar_via,
			
			$bpjs_kes,
			$bpjs_ket,
			
			$isSales,
			
			$user,
			$pass,
			$user_ins,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_karyawan
				(
					id_karyawan,
					id_jabatan,
					no_karyawan,
					nik_karyawan,
					nama_karyawan,
					pnd,
					tlp,
					email,
					tmp_lahir,
					tgl_lahir,
					kelamin,
					sts_nikah,
					avatar,
					avatar_url,
					alamat,
					ket_karyawan,
					tgl_diterima,
					isAktif,
					isDokter,
					lamar_via,
					
					bpjs_kes,
					bpjs_ket,
					isSales,
					
					user,
					pass,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_karyawan
						From
						(
							SELECT CONCAT(Y,M,D) AS FRMTGL
							 ,CASE
								WHEN (ORD >= 10 AND ORD < 99) THEN CONCAT('000',CAST(ORD AS CHAR))
								WHEN (ORD >= 100 AND ORD < 999) THEN CONCAT('00',CAST(ORD AS CHAR))
								WHEN (ORD >= 1000 AND ORD < 9999) THEN CONCAT('0',CAST(ORD AS CHAR))
								WHEN ORD >= 10000 THEN CAST(ORD AS CHAR)
								ELSE CONCAT('0000',CAST(ORD AS CHAR))
								END As ORD
							From
							(
								SELECT
								CAST(LEFT(NOW(),4) AS CHAR) AS Y,
								CAST(MID(NOW(),6,2) AS CHAR) AS M,
								MID(NOW(),9,2) AS D,
								COALESCE(MAX(CAST(RIGHT(id_karyawan,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_karyawan
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_jabatan."',
					
					(
						SELECT CONCAT('GFD',ORD) AS NO_KRY
						FROM
						(
							SELECT
								CASE
								WHEN (ORD >= 10 AND ORD < 99) THEN CONCAT('000',CAST(ORD AS CHAR))
								WHEN (ORD >= 100 AND ORD < 999) THEN CONCAT('00',CAST(ORD AS CHAR))
								WHEN (ORD >= 1000 AND ORD < 9999) THEN CONCAT('0',CAST(ORD AS CHAR))
								WHEN ORD >= 10000 THEN CAST(ORD AS CHAR)
								ELSE CONCAT('0000',CAST(ORD AS CHAR))
								END As ORD
							From
							(
								SELECT COUNT(id_karyawan)+1 AS ORD FROM tb_karyawan
							) AS A
						) AS AA
					),
					
					'".$nik_karyawan."',
					'".$nama_karyawan."',
					'".$pnd."',
					'".$tlp."',
					'".$email."',
					'".$tmp_lahir."',
					'".$tgl_lahir."',
					'".$kelamin."',
					'".$sts_nikah."',
					'".$avatar."',
					'".$avatar_url."',
					'".$alamat."',
					'".$ket_karyawan."',
					'".$tgl_diterima."',
					'".$isAktif."',
					'".$isDokter."',
					'".$lamar_via."',
					
					'".$bpjs_kes."',
					'".$bpjs_ket."',
					'".$isSales."',
					
					'".$user."',
					'".$pass."',
					NOW(),
					NOW(),
					'".$user_ins."',
					'".$user_updt."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_karyawan,
			$id_jabatan,
			$no_karyawan,
			$nik_karyawan,
			$nama_karyawan,
			$pnd,
			$tlp,
			$email,
			$tmp_lahir,
			$tgl_lahir,
			$kelamin,
			$sts_nikah,
			$avatar,
			$avatar_url,
			$alamat,
			$ket_karyawan,
			$tgl_diterima,
			$isAktif,
			$isDokter,
			$lamar_via,
			
			$bpjs_kes,
			$bpjs_ket,
			$isSales,
			
			$user,
			$pass,
			$user_updt,
			$kode_kantor
		)
		{
			if($avatar != "")
			{
				$strquery = "
				UPDATE tb_karyawan SET
				
					id_jabatan = '".$id_jabatan."',
					no_karyawan = '".$no_karyawan."',
					nik_karyawan = '".$nik_karyawan."',
					nama_karyawan = '".$nama_karyawan."',
					pnd = '".$pnd."',
					tlp = '".$tlp."',
					email = '".$email."',
					tmp_lahir = '".$tmp_lahir."',
					tgl_lahir = '".$tgl_lahir."',
					kelamin = '".$kelamin."',
					sts_nikah = '".$sts_nikah."',
					avatar = '".$avatar."',
					avatar_url = '".$avatar_url."',
					alamat = '".$alamat."',
					ket_karyawan = '".$ket_karyawan."',
					tgl_diterima = '".$tgl_diterima."',
					isDokter = '".$isDokter."',
					lamar_via = '".$lamar_via."',
					
					bpjs_kes = '".$bpjs_kes."',
					bpjs_ket = '".$bpjs_ket."',
					isSales = '".$isSales."',
					
					user = '".$user."',
					pass = '".$pass."',
					tgl_updt = NOW(),
					user_updt = '".$user_updt."'
				WHERE kode_kantor = '".$kode_kantor."' AND id_karyawan = '".$id_karyawan
				."'
				";
			}
			else
			{
				$strquery = "
				UPDATE tb_karyawan SET
				
					id_jabatan = '".$id_jabatan."',
					no_karyawan = '".$no_karyawan."',
					nik_karyawan = '".$nik_karyawan."',
					nama_karyawan = '".$nama_karyawan."',
					pnd = '".$pnd."',
					tlp = '".$tlp."',
					email = '".$email."',
					tmp_lahir = '".$tmp_lahir."',
					tgl_lahir = '".$tgl_lahir."',
					kelamin = '".$kelamin."',
					sts_nikah = '".$sts_nikah."',
					alamat = '".$alamat."',
					ket_karyawan = '".$ket_karyawan."',
					tgl_diterima = '".$tgl_diterima."',
					isDokter = '".$isDokter."',
					lamar_via = '".$lamar_via."',
					
					bpjs_kes = '".$bpjs_kes."',
					bpjs_ket = '".$bpjs_ket."',
					isSales = '".$isSales."',
					
					user = '".$user."',
					pass = '".$pass."',
					tgl_updt = NOW(),
					user_updt = '".$user_updt."'
				WHERE kode_kantor = '".$kode_kantor."' AND id_karyawan = '".$id_karyawan
				."'
				";
			}
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_profile
		(
			$id_karyawan,
			$nama_karyawan,
			$pnd,
			$tlp,
			$email,
			$tmp_lahir,
			$tgl_lahir,
			$kelamin,
			$sts_nikah,
			$avatar,
			$avatar_url,
			$alamat,
			$user,
			$pass,
			$pass2,
			$kode_kantor
		)
		{
			if($avatar != "")
			{
				if($pass == $pass2)
				{
					$strquery = "
					UPDATE tb_karyawan SET
					
						nama_karyawan = '".$nama_karyawan."',
						pnd = '".$pnd."',
						tlp = '".$tlp."',
						email = '".$email."',
						tmp_lahir = '".$tmp_lahir."',
						tgl_lahir = '".$tgl_lahir."',
						kelamin = '".$kelamin."',
						sts_nikah = '".$sts_nikah."',
						avatar = '".$avatar."',
						avatar_url = '".$avatar_url."',
						alamat = '".$alamat."',
						user = '".$user."',
						pass = '".base64_encode(md5($pass))."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_karyawan = '".$id_karyawan
					."'
					";
				}
				else
				{
					$strquery = "
					UPDATE tb_karyawan SET
					
						nama_karyawan = '".$nama_karyawan."',
						pnd = '".$pnd."',
						tlp = '".$tlp."',
						email = '".$email."',
						tmp_lahir = '".$tmp_lahir."',
						tgl_lahir = '".$tgl_lahir."',
						kelamin = '".$kelamin."',
						sts_nikah = '".$sts_nikah."',
						avatar = '".$avatar."',
						avatar_url = '".$avatar_url."',
						alamat = '".$alamat."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_karyawan = '".$id_karyawan
					."'
					";
				}
			}
			else
			{
				if($pass == $pass2)
				{
					$strquery = "
					UPDATE tb_karyawan SET
					
						nama_karyawan = '".$nama_karyawan."',
						pnd = '".$pnd."',
						tlp = '".$tlp."',
						email = '".$email."',
						tmp_lahir = '".$tmp_lahir."',
						tgl_lahir = '".$tgl_lahir."',
						kelamin = '".$kelamin."',
						sts_nikah = '".$sts_nikah."',
						alamat = '".$alamat."',
						user = '".$user."',
						pass = '".base64_encode(md5($pass))."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_karyawan = '".$id_karyawan
					."'
					";
				}
				else
				{
					$strquery = "
					UPDATE tb_karyawan SET
					
						nama_karyawan = '".$nama_karyawan."',
						pnd = '".$pnd."',
						tlp = '".$tlp."',
						email = '".$email."',
						tmp_lahir = '".$tmp_lahir."',
						tgl_lahir = '".$tgl_lahir."',
						kelamin = '".$kelamin."',
						sts_nikah = '".$sts_nikah."',
						alamat = '".$alamat."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_karyawan = '".$id_karyawan
					."'
					";
				}
			}
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_phk($kode_kantor,$id_karyawan,$isAktif,$tgl_phk,$alasan_phk)
		{
			$query =
			"
				UPDATE tb_karyawan SET
					isAktif = '".$isAktif."',
					tgl_phk = '".$tgl_phk."',
					alasan_phk = '".$alasan_phk."'
				WHERE kode_kantor = '".$kode_kantor."' AND id_karyawan = '".$id_karyawan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($query);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_proses_karyawan_req($id_karyawan,$isAktif,$nilai_ujian,$ket_hasil_ujian)
		{
			$strquery = "
				UPDATE tb_karyawan SET
					isAktif = '".$isAktif."',
					nilai_ujian = '".$nilai_ujian."',
					ket_hasil_ujian = '".$ket_hasil_ujian."'
				WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_karyawan = '".$id_karyawan
				."'
				";
			
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
			
			return "BERHASIL";
		}
		
		function pemberian_akun($id_karyawan,$user,$pass)
		{
			$strquery = "
				UPDATE tb_karyawan SET
					user = '".$user."',
					pass = '".$pass."'
				WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_karyawan = '".$id_karyawan
				."'
				";
			
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
			
			return "BERHASIL";
		}
		
		function hapus($id_karyawan)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_karyawan WHERE id_karyawan = '".$id_karyawan."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_karyawan($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_karyawan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_karyawan_cari($cari)
        {
			$query = "SELECT * FROM tb_karyawan ".$cari." ORDER BY nama_karyawan ASC";
            $query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
        }
		
	}
?>