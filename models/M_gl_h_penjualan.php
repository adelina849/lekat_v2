<?php
	class M_gl_h_penjualan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function view_query_general($query)
		{
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
		
		function exec_query_general($query)
		{
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($query);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function cek_apakah_ada_jasa($kode_kantor,$id_h_penjualan)
		{
			/*
			$query =
			"
				SELECT SUM(JUM_JASA) AS JUM_JASA FROM
				(
					SELECT
						CASE WHEN isProduk = 'JASA' THEN
							1
						ELSE
							0
						END AS JUM_JASA
					FROM tb_d_penjualan WHERE id_h_penjualan = '".$id_h_penjualan."' AND kode_kantor = '".$kode_kantor."'
				) AS AA;
			";
			*/
			
			$query = 
				"
					SELECT SUM(JUM_JASA) AS JUM_JASA FROM
					(
						SELECT
							CASE WHEN COALESCE(B.isProduk,'') = 'JASA' THEN
								1
							ELSE
								0
							END AS JUM_JASA
						FROM tb_d_penjualan AS A 
						LEFT JOIN tb_produk AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
						WHERE A.id_h_penjualan = '".$id_h_penjualan."' AND A.kode_kantor = '".$kode_kantor."'
					) AS AA;
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
		
		function update_hpp_dari_tb_satuan_konversi($kode_kantor,$id_h_penjualan)
		{
			$strquery =
					"
						UPDATE tb_d_penjualan
						INNER JOIN
						(
							SELECT A.*,COALESCE(B.kode_satuan,'') AS kode_satuan 
							FROM tb_satuan_konversi AS A
							LEFT JOIN tb_satuan AS B ON A.id_satuan = B.id_satuan
						) AS B ON tb_d_penjualan.kode_kantor = B.kode_kantor AND tb_d_penjualan.id_produk = B.id_produk AND tb_d_penjualan.satuan_jual = B.kode_satuan
						SET tb_d_penjualan.harga_dasar_ori = B.harga_jual
						WHERE tb_d_penjualan.kode_kantor = '".$kode_kantor."' AND tb_d_penjualan.id_h_penjualan = '".$id_h_penjualan."';
					";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_outbox($no_hp,$pesan)
		{
			$query =
					"
						INSERT INTO outbox(DestinationNumber, TextDecoded, CreatorID) VALUES ('".$no_hp."', '".$pesan."', 'Gammu');
					";
			$this->db->query($query);
		}
		
		function list_harga_produk($cari)
		{
			$query =
			"
				SELECT DISTINCT
					A.kode_kantor
					,A.id_produk
					,A.kode_produk
					,A.nama_produk
					,COALESCE(B.media,'') AS media
					,COALESCE(D.nama_kat_costumer,'') AS nama_kat_costumer
					,COALESCE(C.kode_satuan,'') AS kode_satuan
					,COALESCE(B.harga,'') AS harga
				FROM tb_produk AS A
				LEFT JOIN
				(
					SELECT DISTINCT kode_kantor,id_produk,id_satuan,id_costumer,media,harga
					FROM tb_produk_harga_satuan_costumer
					GROUP BY kode_kantor,id_produk,id_satuan,id_costumer,media,harga
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
				LEFT JOIN
				(
					SELECT DISTINCT kode_kantor,id_satuan,kode_satuan
					FROM tb_satuan
					GROUP BY kode_kantor,id_satuan,kode_satuan
				) AS C ON A.kode_kantor = C.kode_kantor AND B.id_satuan = C.id_satuan
				LEFT JOIN
				(
					SELECT DISTINCT kode_kantor,id_kat_costumer,nama_kat_costumer
					FROM tb_kat_costumer
					GROUP BY kode_kantor,id_kat_costumer,nama_kat_costumer
				) AS D ON A.kode_kantor = D.kode_kantor AND B.id_costumer = D.id_kat_costumer
				".$cari."
				GROUP BY A.kode_kantor
					,A.id_produk
					,A.kode_produk
					,A.nama_produk
					,COALESCE(B.media,'')
					,COALESCE(C.kode_satuan,'')
					,COALESCE(D.nama_kat_costumer,'')
					,COALESCE(B.harga,'')
				ORDER BY A.nama_produk ASC,COALESCE(B.media,'') ASC,COALESCE(D.nama_kat_costumer,'') ASC,COALESCE(C.kode_satuan,'') ASC
				
				LIMIT 0,30;
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
		
		function list_rekam_medis($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT
					DISTINCT
					A.*
					,COALESCE(B.no_karyawan,'') AS no_dokter
					,COALESCE(B.nama_karyawan,'') AS nama_dokter
					,COALESCE(B.pnd,'') AS pnd
					,COALESCE(B.avatar_url,'') AS avatar_url_dokter
					,COALESCE(B.avatar,'') AS avatar_dokter
					,COALESCE(C.nama_karyawan,'') AS nama_karyawan
					,DATE_FORMAT(A.tgl_h_penjualan, '%d %M %Y') AS format_tgl_h_penjualan
					,TIMESTAMPDIFF(DAY,DATE(A.tgl_h_penjualan),DATE(NOW())) AS selisih_hari
					,
						
						CASE
						WHEN (A.waktu_mulai_pemeriksaan != '0000-00-00 00:00:00') AND (A.waktu_selesai_pemeriksaan != '0000-00-00 00:00:00') THEN
							TIMESTAMPDIFF
							(
								MINUTE,
								A.waktu_mulai_pemeriksaan,
								A.waktu_selesai_pemeriksaan
							) 
						ELSE
							0
						END
						AS LAMA_KONSUL
					, 
						CASE 
						WHEN (A.type_h_penjualan <> 'PENJUALAN LANGSUNG') AND (A.sts_penjualan = 'SELESAI') THEN
							TIMESTAMPDIFF
							(
								MINUTE,
								A.waktu_mulai_tindakan,
								A.tgl_updt
							) 
						ELSE
							0
						END
						AS LAMA_TINDAKAN
				FROM tb_h_penjualan AS A
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_karyawan,no_karyawan,nama_karyawan,pnd,avatar_url,avatar
					FROM tb_karyawan
					GROUP BY kode_kantor,id_karyawan,no_karyawan,nama_karyawan,pnd,avatar_url,avatar
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_dokter = B.id_karyawan
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_karyawan,nama_karyawan
					FROM tb_karyawan
					GROUP BY kode_kantor,id_karyawan,nama_karyawan
				) AS C ON A.kode_kantor = C.kode_kantor AND A.id_karyawan = C.id_karyawan
				INNER JOIN 
				(
					SELECT DISTINCT kode_kantor,id_h_penjualan,id_produk
					FROM tb_d_penjualan
					GROUP BY kode_kantor,id_h_penjualan,id_produk
				) AS D ON A.id_h_penjualan = D.id_h_penjualan AND A.kode_kantor = D.kode_kantor
				".$cari."
				".$order_by."
				LIMIT ".$offset.",".$limit."
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
		
		function count_rekam_medis($cari)
		{
			$query =
			"
				SELECT
					COUNT(A.id_h_penjualan) AS JUMLAH
				FROM tb_h_penjualan AS A
				LEFT JOIN tb_karyawan AS B ON A.kode_kantor = B.kode_kantor AND A.id_dokter = B.id_karyawan
				".$cari."
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
		
		function get_id_h_penjualan($kode_kantor)
		{
			$query ="
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_h_penjualan
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
								COALESCE(MAX(CAST(RIGHT(id_h_penjualan,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_h_penjualan
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
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
		
		function get_no_antrian($kode_kantor)
		{
			$query =
			"
				SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_h_penjualan,CONCAT('".$kode_kantor."',FRMTGL,ORD) AS no_faktur,CAST(RIGHT(no_antrian,5) AS UNSIGNED) AS no_antrian
				FROM
				(
					SELECT CONCAT(Y,M,D) AS FRMTGL
					 ,CASE
						WHEN (ORD >= 10 AND ORD < 99) THEN CONCAT('000',CAST(ORD AS CHAR))
						WHEN (ORD >= 100 AND ORD < 999) THEN CONCAT('00',CAST(ORD AS CHAR))
						WHEN (ORD >= 1000 AND ORD < 9999) THEN CONCAT('0',CAST(ORD AS CHAR))
						WHEN ORD >= 10000 THEN CAST(ORD AS CHAR)
						ELSE CONCAT('0000',CAST(ORD AS CHAR))
						END As ORD
						
						,no_antrian
					From
					(
						SELECT
						CAST(LEFT(NOW(),4) AS CHAR) AS Y,
						CAST(MID(NOW(),6,2) AS CHAR) AS M,
						MID(NOW(),9,2) AS D,
						COALESCE(MAX(CAST(RIGHT(id_h_penjualan,5) AS UNSIGNED)) + 1,1) AS ORD
						,COALESCE(MAX(CAST(RIGHT(no_antrian,5) AS UNSIGNED)) + 1,1) AS no_antrian
						From tb_h_penjualan
						WHERE DATE(tgl_ins) = DATE(NOW())
						AND kode_kantor = '".$kode_kantor."'
					) AS A
				) AS AA;
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
		
		function get_no_faktur_no_antrian($kode_kantor)
		{
			$query =
			"
				SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_h_penjualan,CONCAT('".$kode_kantor."',FRMTGL,ORD) AS no_faktur,CAST(RIGHT(no_antrian,5) AS UNSIGNED) AS no_antrian
				FROM
				(
					SELECT CONCAT(Y,M,D) AS FRMTGL
					 ,CASE
						WHEN (ORD >= 10 AND ORD < 99) THEN CONCAT('000',CAST(ORD AS CHAR))
						WHEN (ORD >= 100 AND ORD < 999) THEN CONCAT('00',CAST(ORD AS CHAR))
						WHEN (ORD >= 1000 AND ORD < 9999) THEN CONCAT('0',CAST(ORD AS CHAR))
						WHEN ORD >= 10000 THEN CAST(ORD AS CHAR)
						ELSE CONCAT('0000',CAST(ORD AS CHAR))
						END As ORD
						
						,no_antrian
					From
					(
						SELECT
						CAST(LEFT(NOW(),4) AS CHAR) AS Y,
						CAST(MID(NOW(),6,2) AS CHAR) AS M,
						MID(NOW(),9,2) AS D,
						COALESCE(MAX(CAST(RIGHT(id_h_penjualan,5) AS UNSIGNED)) + 1,1) AS ORD
						-- ,COALESCE(MAX(CAST(RIGHT(no_antrian,5) AS UNSIGNED)) + 1,1) AS no_antrian
						,COALESCE(MAX(CAST(RIGHT(no_antrian,5) AS UNSIGNED)) ,1) AS no_antrian
						From tb_h_penjualan
						WHERE DATE(tgl_ins) = DATE(NOW())
						AND kode_kantor = '".$kode_kantor."'
					) AS A
				) AS AA;
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
		
		function list_pendaftaran($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT
					A.*
					,
					CASE 
					WHEN (COALESCE(A.media_transaksi,'') = '') AND ((SELECT nama_media FROM tb_media_transaksi WHERE kode_kantor = A.kode_kantor AND isDefault = 1) != '') THEN
						(SELECT nama_media FROM tb_media_transaksi WHERE kode_kantor = A.kode_kantor AND isDefault = 1)
					ELSE
						COALESCE(A.media_transaksi,'')
					END AS media_fix
					,COALESCE(B.no_costumer,'') AS kode_costumer
					,COALESCE(B.nama_lengkap,'') AS nama_costumer
					,COALESCE(C.nama_diskon,'') AS nama_diskon
					,COALESCE(D.nama_karyawan,'') AS nama_karyawan
					,COALESCE(E.nama_karyawan,'') AS nama_sales
					,CONCAT
					(
						COALESCE(F.provinsi,''),' ',
						COALESCE(F.kabkota,''),' ',
						COALESCE(F.kecamatan,''),' ',
						COALESCE(F.desa,''),' (',
						COALESCE(F.kodepos,''),') ',
						COALESCE(F.detail_alamat,''),' /(',
						COALESCE(F.telepon,''),') '
					) AS ALAMAT_KIRIM
					,COALESCE(F.provinsi,'') AS cek_provinsi
				FROM tb_h_penjualan AS A
				LEFT JOIN tb_costumer AS B ON A.id_costumer = B.id_costumer AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_h_diskon AS C ON A.id_h_diskon = C.id_h_diskon AND A.kode_kantor = C.kode_kantor
				LEFT JOIN tb_karyawan AS D ON A.id_karyawan = D.id_karyawan AND A.kode_kantor = D.kode_kantor
				LEFT JOIN tb_karyawan AS E ON A.id_sales = E.id_karyawan AND A.kode_kantor = E.kode_kantor
				LEFT JOIN tb_alamat AS F ON A.id_gedung = F.id_alamat
				".$cari."
				".$order_by."
				LIMIT ".$offset.",".$limit."
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
		
		function count_list_pendaftaran($cari)
		{
			$query =
			"
				SELECT
					COUNT(A.id_h_penjualan) AS JUMLAH
				FROM tb_h_penjualan AS A
				LEFT JOIN tb_costumer AS B ON A.id_costumer = B.id_costumer AND A.kode_kantor = B.kode_kantor
				".$cari."
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
		
		function list_pendaftaran_perawatan_lanjutan($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT
					A.*
					,COALESCE(B.no_costumer,'') AS kode_costumer
					,COALESCE(B.nama_lengkap,'') AS nama_costumer
					,COALESCE(B.avatar,'') AS avatar
					,COALESCE(B.avatar_url,'') AS avatar_url
					
					
					,COALESCE(C.avatar,'') AS avatar_dokter2
					,COALESCE(C.avatar_url,'') AS avatar_url_dokter2
					,COALESCE(C.nama_karyawan,'') AS nama_dokter
					,COALESCE(C.no_karyawan,'') AS no_dokter
					,DATEDIFF(DATE(NOW()),COALESCE(DATE(C.tgl_diterima),'1900-01-01')) AS lama_kerja_dokter2
					
					
					,COALESCE(D.avatar,'') AS avatar_ass
					,COALESCE(D.avatar_url,'') AS avatar_url_ass
					,COALESCE(D.nama_karyawan,'') AS nama_ass
					,COALESCE(D.no_karyawan,'') AS no_ass
					,DATEDIFF(DATE(NOW()),COALESCE(DATE(D.tgl_diterima),'1900-01-01')) AS lama_kerja_ass
					
					,COALESCE(F.avatar,'') AS avatar_ass2
					,COALESCE(F.avatar_url,'') AS avatar_url_ass2
					,COALESCE(F.nama_karyawan,'') AS nama_ass2
					,COALESCE(F.no_karyawan,'') AS no_ass2
					,DATEDIFF(DATE(NOW()),COALESCE(DATE(F.tgl_diterima),'1900-01-01')) AS lama_kerja_ass2
					
					,COALESCE(E.nama_diskon,'') AS nama_diskon
					
					,
						
						CASE
						WHEN (A.waktu_mulai_pemeriksaan != '0000-00-00 00:00:00') AND (A.waktu_selesai_pemeriksaan != '0000-00-00 00:00:00') THEN
							TIMESTAMPDIFF
							(
								HOUR,
								A.waktu_mulai_pemeriksaan,
								A.waktu_selesai_pemeriksaan
							) 
						ELSE
							0
						END
						AS LAMA_KONSUL_HOUR
					,
						
						CASE
						WHEN (A.waktu_mulai_pemeriksaan != '0000-00-00 00:00:00') AND (A.waktu_selesai_pemeriksaan != '0000-00-00 00:00:00') THEN
							TIMESTAMPDIFF
							(
								MINUTE,
								A.waktu_mulai_pemeriksaan,
								A.waktu_selesai_pemeriksaan
							) 
						ELSE
							0
						END
						AS LAMA_KONSUL
						
					/*
					,TIMESTAMPDIFF
						(
						MINUTE,
						A.tgl_updt,
						NOW()
					) AS MENIT_TUNGGU
					*/
					
					,
					CASE WHEN A.waktu_mulai_pemeriksaan = '0000-00-00 00:00:00' THEN
						TIMESTAMPDIFF
						(
							MINUTE,
							A.tgl_ins,
							NOW()
						)
					ELSE
						TIMESTAMPDIFF
						(
							MINUTE,
							A.tgl_ins,
							A.waktu_mulai_pemeriksaan
						)
					END
					AS MENIT_TUNGGU
					
				FROM tb_h_penjualan AS A
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_costumer,no_costumer,nama_lengkap,avatar,avatar_url
					FROM tb_costumer
					GROUP BY kode_kantor,id_costumer,no_costumer,nama_lengkap,avatar,avatar_url
				) AS B ON A.id_costumer = B.id_costumer AND A.kode_kantor = B.kode_kantor
				LEFT JOIN
				(
					SELECT DISTINCT kode_kantor,id_karyawan,avatar,avatar_url,nama_karyawan,no_karyawan,tgl_diterima
					FROM tb_karyawan
					GROUP BY kode_kantor,id_karyawan,avatar,avatar_url,nama_karyawan,no_karyawan,tgl_diterima
				) AS C ON A.id_dokter2 = C.id_karyawan AND A.kode_kantor = C.kode_kantor
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_karyawan,avatar,avatar_url,nama_karyawan,no_karyawan,tgl_diterima
					FROM tb_karyawan
					GROUP BY kode_kantor,id_karyawan,avatar,avatar_url,nama_karyawan,no_karyawan,tgl_diterima
				) AS D ON A.id_ass_dok = D.id_karyawan AND A.kode_kantor = D.kode_kantor
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_h_diskon,nama_diskon
					FROM tb_h_diskon
					GROUP BY kode_kantor,id_h_diskon,nama_diskon
				) AS E ON A.id_h_diskon = E.id_h_diskon AND A.kode_kantor = E.kode_kantor
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_karyawan,avatar,avatar_url,nama_karyawan,no_karyawan,tgl_diterima
					FROM tb_karyawan
					GROUP BY kode_kantor,id_karyawan,avatar,avatar_url,nama_karyawan,no_karyawan,tgl_diterima
				) AS F ON A.id_ass_dok2 = F.id_karyawan AND A.kode_kantor = F.kode_kantor
				".$cari."
				".$order_by."
				LIMIT ".$offset.",".$limit."
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
		
		function count_pendaftaran_perawatan_lanjutan($cari)
		{
			$query =
			"
				SELECT
					COUNT(A.id_h_penjualan) AS JUMLAH
				
				/*
				FROM tb_h_penjualan AS A
				LEFT JOIN tb_costumer AS B ON A.id_costumer = B.id_costumer AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_karyawan AS C ON A.id_dokter2 = C.id_karyawan AND A.kode_kantor = C.kode_kantor
				LEFT JOIN tb_karyawan AS D ON A.id_ass_dok = D.id_karyawan AND A.kode_kantor = D.kode_kantor
				LEFT JOIN tb_h_diskon AS E ON A.id_h_diskon = E.id_h_diskon AND A.kode_kantor = E.kode_kantor
				*/
				
				FROM tb_h_penjualan AS A
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_costumer,no_costumer,nama_lengkap,avatar,avatar_url
					FROM tb_costumer
					GROUP BY kode_kantor,id_costumer,no_costumer,nama_lengkap,avatar,avatar_url
				) AS B ON A.id_costumer = B.id_costumer AND A.kode_kantor = B.kode_kantor
				LEFT JOIN
				(
					SELECT DISTINCT kode_kantor,id_karyawan,avatar,avatar_url,nama_karyawan,no_karyawan,tgl_diterima
					FROM tb_karyawan
					GROUP BY kode_kantor,id_karyawan,avatar,avatar_url,nama_karyawan,no_karyawan,tgl_diterima
				) AS C ON A.id_dokter2 = C.id_karyawan AND A.kode_kantor = C.kode_kantor
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_karyawan,avatar,avatar_url,nama_karyawan,no_karyawan,tgl_diterima
					FROM tb_karyawan
					GROUP BY kode_kantor,id_karyawan,avatar,avatar_url,nama_karyawan,no_karyawan,tgl_diterima
				) AS D ON A.id_ass_dok = D.id_karyawan AND A.kode_kantor = D.kode_kantor
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_h_diskon,nama_diskon
					FROM tb_h_diskon
					GROUP BY kode_kantor,id_h_diskon,nama_diskon
				) AS E ON A.id_h_diskon = E.id_h_diskon AND A.kode_kantor = E.kode_kantor
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_karyawan,avatar,avatar_url,nama_karyawan,no_karyawan,tgl_diterima
					FROM tb_karyawan
					GROUP BY kode_kantor,id_karyawan,avatar,avatar_url,nama_karyawan,no_karyawan,tgl_diterima
				) AS F ON A.id_ass_dok2 = F.id_karyawan AND A.kode_kantor = F.kode_kantor
				
				".$cari."
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
		
		function list_pendaftaran_periksa_dokter($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT
					A.*
					,COALESCE(B.no_costumer,'') AS kode_costumer
					,COALESCE(B.nama_lengkap,'') AS nama_costumer
					,COALESCE(B.avatar,'') AS avatar
					,COALESCE(B.avatar_url,'') AS avatar_url
					,COALESCE(C.nama_karyawan,'') AS nama_dokter
					,COALESCE(C.no_karyawan,'') AS no_karyawan
					,COALESCE(D.nama_karyawan,'') AS nama_karyawan
					,COALESCE(E.nama_diskon,'') AS nama_diskon
					,TIMESTAMPDIFF
						(
						MINUTE,
						A.tgl_ins,
						NOW()
					) AS MENIT_TUNGGU
					,
					CONCAT
					(
						COALESCE(F.provinsi,''),' ',
						COALESCE(F.kabkota,''),' ',
						COALESCE(F.kecamatan,''),' ',
						COALESCE(F.desa,''),' (',
						COALESCE(F.kodepos,''),') ',
						COALESCE(F.detail_alamat,''),' /(',
						COALESCE(F.telepon,''),') '
					) AS ALAMAT_KIRIM
				FROM tb_h_penjualan AS A
				LEFT JOIN tb_costumer AS B ON A.id_costumer = B.id_costumer AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_karyawan AS C ON A.id_dokter = C.id_karyawan AND A.kode_kantor = C.kode_kantor
				LEFT JOIN tb_karyawan AS D ON A.id_karyawan = D.id_karyawan AND A.kode_kantor = D.kode_kantor
				LEFT JOIN tb_h_diskon AS E ON A.id_h_diskon = E.id_h_diskon AND A.kode_kantor = E.kode_kantor
				LEFT JOIN tb_alamat AS F ON A.id_gedung = F.id_alamat
				".$cari."
				".$order_by."
				LIMIT ".$offset.",".$limit."
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
		
		function count_list_pendaftaran_periksa_dokter($cari)
		{
			$query =
			"
				SELECT
					COUNT(A.id_h_penjualan) AS JUMLAH
				FROM tb_h_penjualan AS A
				LEFT JOIN tb_costumer AS B ON A.id_costumer = B.id_costumer AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_karyawan AS C ON A.id_dokter = C.id_karyawan AND A.kode_kantor = C.kode_kantor
				".$cari."
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
		
		function simpan
		(
			$id_h_pemesanan,
			$id_costumer,
			$id_karyawan,
			$id_h_retur,
			$no_faktur,
			$no_costmer,
			$nama_costumer,
			$id_kat_costumer,
			$nama_kat_costumer,
			$kode_kantor_costumer,
			$biaya,
			$diskon,
			$nominal_retur,
			$nominal_transaksi,
			$modal,
			$bayar,
			$bayar_debit,
			$bayar_detail,
			$bayar_tabungan,
			$tgl_pengajuan,
			$tgl_h_penjualan,
			$tgl_tempo,
			$status_penjualan,
			$isTunai,
			$ket_penjualan,
			$nama_set_roles,
			$bulan,
			$up,
			$optr_up,
			$naik_persen,
			$optr_naik_persen,
			$prsn_cicilan,
			$prsn_pokok,
			$prsn_marketing,
			$optr_marketing,
			$denda,
			$optr_denda,
			$frek_denda,
			$type_denda,
			$toleransi,
			$perkiraan_nominal,
			$type_h_penjualan,
			$point,
			$donasi,
			$kat_donasi,
			$point_fixed,
			$isKirim,
			$user_ins,
			$kode_kantor,
			$sts_penjualan
		)
		{
			$strquery = "
				INSERT INTO tb_h_penjualan
				(
					id_h_penjualan,
					id_h_pemesanan,
					id_costumer,
					id_karyawan,
					id_h_retur,
					no_faktur,
					no_costmer,
					nama_costumer,
					id_kat_costumer,
					nama_kat_costumer,
					kode_kantor_costumer,
					biaya,
					diskon,
					nominal_retur,
					nominal_transaksi,
					modal,
					bayar,
					bayar_debit,
					bayar_detail,
					bayar_tabungan,
					tgl_pengajuan,
					tgl_h_penjualan,
					tgl_tempo,
					status_penjualan,
					isTunai,
					ket_penjualan,
					nama_set_roles,
					bulan,
					up,
					optr_up,
					naik_persen,
					optr_naik_persen,
					prsn_cicilan,
					prsn_pokok,
					prsn_marketing,
					optr_marketing,
					denda,
					optr_denda,
					frek_denda,
					type_denda,
					toleransi,
					perkiraan_nominal,
					type_h_penjualan,
					point,
					donasi,
					kat_donasi,
					point_fixed,
					isKirim,
					tgl_ins,
					tgl_updt,
					user_updt,
					user_ins,
					kode_kantor,
					sts_penjualan
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_h_penjualan
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
								COALESCE(MAX(CAST(RIGHT(id_h_penjualan,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_h_penjualan
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_h_pemesanan."',
					'".$id_costumer."',
					'".$id_karyawan."',
					'".$id_h_retur."',
					'".$no_faktur."',
					'".$no_costmer."',
					'".$nama_costumer."',
					'".$id_kat_costumer."',
					'".$nama_kat_costumer."',
					'".$kode_kantor_costumer."',
					'".$biaya."',
					'".$diskon."',
					'".$nominal_retur."',
					'".$nominal_transaksi."',
					'".$modal."',
					'".$bayar."',
					'".$bayar_debit."',
					'".$bayar_detail."',
					'".$bayar_tabungan."',
					'".$tgl_pengajuan."',
					'".$tgl_h_penjualan."',
					'".$tgl_tempo."',
					'".$status_penjualan."',
					'".$isTunai."',
					'".$ket_penjualan."',
					'".$nama_set_roles."',
					'".$bulan."',
					'".$up."',
					'".$optr_up."',
					'".$naik_persen."',
					'".$optr_naik_persen."',
					'".$prsn_cicilan."',
					'".$prsn_pokok."',
					'".$prsn_marketing."',
					'".$optr_marketing."',
					'".$denda."',
					'".$optr_denda."',
					'".$frek_denda."',
					'".$type_denda."',
					'".$toleransi."',
					'".$perkiraan_nominal."',
					'".$type_h_penjualan."',
					'".$point."',
					'".$donasi."',
					'".$kat_donasi."',
					'".$point_fixed."',
					'".$isKirim."',
					NOW(), 
					NOW(), 
					'',
					'".$user_ins."',
					'".$kode_kantor."',
					'".$sts_penjualan."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_h_penjualan_awal(
			$id_h_penjualan,
			$id_costumer,
			$id_karyawan,
			$id_dokter,
			$id_h_diskon,
			$no_faktur,
			$no_antrian,
			$no_costmer,
			$nama_costumer,
			$nama_kat_costumer,
			$id_kat_costumer,
			$kode_kantor_costumer,
			$tgl_h_penjualan,
			$status_penjualan,
			$ket_penjualan,
			$type_h_penjualan,
			$user_ins,
			$kode_kantor,
			$sts_penjualan,
			$media_transaksi
			
		)
		{
			$strquery = "
				INSERT INTO tb_h_penjualan
				(
					id_h_penjualan,
					id_costumer,
					id_karyawan,
					id_dokter,
					id_h_diskon,
					no_faktur,
					no_antrian,
					no_costmer,
					nama_costumer,
					id_kat_costumer,
					nama_kat_costumer,
					kode_kantor_costumer,
					tgl_h_penjualan,
					status_penjualan,
					ket_penjualan,
					type_h_penjualan,
					tgl_ins,
					tgl_updt,
					user_ins,
					kode_kantor,
					sts_penjualan,
					media_transaksi
				)
				VALUES
				(
					
					'".$id_h_penjualan."',
					'".$id_costumer."',
					'".$id_karyawan."',
					'".$id_dokter."',
					'".$id_h_diskon."',
					'".$no_faktur."',
					'".$no_antrian."',
					'".$no_costmer."',
					'".$nama_costumer."',
					'".$id_kat_costumer."',
					'".$nama_kat_costumer."',
					'".$kode_kantor_costumer."',
					'".$tgl_h_penjualan."',
					'".$status_penjualan."',
					'".$ket_penjualan."',
					'".$type_h_penjualan."',
					NOW(),
					NOW(),
					'".$user_ins."',
					'".$kode_kantor."',
					'".$sts_penjualan."',
					'".$media_transaksi."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_h_penjualan_awal
		(
			$id_h_penjualan,
			$id_costumer,
			$id_karyawan,
			$id_dokter,
			$id_h_diskon,
			$no_costmer,
			$nama_costumer,
			$nama_kat_costumer,
			$id_kat_costumer,
			$kode_kantor_costumer,
			$tgl_h_penjualan,
			$status_penjualan,
			$ket_penjualan,
			$type_h_penjualan,
			$user_ins,
			$kode_kantor,
			$sts_penjualan
		)
		{
			$strquery=
			"
				UPDATE tb_h_penjualan SET
					id_costumer = '".$id_costumer."',
					id_karyawan = '".$id_karyawan."',
					id_dokter = '".$id_dokter."',
					id_h_diskon = '".$id_h_diskon."',
					no_costmer = '".$no_costmer."',
					nama_costumer = '".$nama_costumer."',
					nama_kat_costumer = '".$nama_kat_costumer."',
					id_kat_costumer = '".$id_kat_costumer."',
					kode_kantor_costumer = '".$kode_kantor_costumer."',
					tgl_h_penjualan = '".$tgl_h_penjualan."',
					status_penjualan = '".$status_penjualan."',
					ket_penjualan = '".$ket_penjualan."',
					type_h_penjualan = '".$type_h_penjualan."',
					user_ins = '".$user_ins."'

				WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_h_penjualan,
			$id_h_pemesanan,
			$id_costumer,
			$id_karyawan,
			$id_h_retur,
			$no_faktur,
			$no_costmer,
			$nama_costumer,
			$id_kat_costumer,
			$nama_kat_costumer,
			$kode_kantor_costumer,
			$biaya,
			$diskon,
			$nominal_retur,
			$nominal_transaksi,
			$modal,
			$bayar,
			$bayar_debit,
			$bayar_detail,
			$bayar_tabungan,
			$tgl_pengajuan,
			$tgl_h_penjualan,
			$tgl_tempo,
			$status_penjualan,
			$isTunai,
			$ket_penjualan,
			$nama_set_roles,
			$bulan,
			$up,
			$optr_up,
			$naik_persen,
			$optr_naik_persen,
			$prsn_cicilan,
			$prsn_pokok,
			$prsn_marketing,
			$optr_marketing,
			$denda,
			$optr_denda,
			$frek_denda,
			$type_denda,
			$toleransi,
			$perkiraan_nominal,
			$type_h_penjualan,
			$point,
			$donasi,
			$kat_donasi,
			$point_fixed,
			$isKirim,
			$user_updt,
			$kode_kantor,
			$sts_penjualan
		)
		{
			$strquery = "
					UPDATE tb_h_penjualan SET
						id_h_pemesanan = '".$id_h_pemesanan."',
						id_costumer = '".$id_costumer."',
						id_karyawan = '".$id_karyawan."',
						id_h_retur = '".$id_h_retur."',
						no_faktur = '".$no_faktur."',
						no_costmer = '".$no_costmer."',
						nama_costumer = '".$nama_costumer."',
						id_kat_costumer = '".$id_kat_costumer."',
						nama_kat_costumer = '".$nama_kat_costumer."',
						kode_kantor_costumer = '".$kode_kantor_costumer."',
						biaya = '".$biaya."',
						diskon = '".$diskon."',
						nominal_retur = '".$nominal_retur."',
						nominal_transaksi = '".$nominal_transaksi."',
						modal = '".$modal."',
						bayar = '".$bayar."',
						bayar_debit = '".$bayar_debit."',
						bayar_detail = '".$bayar_detail."',
						bayar_tabungan = '".$bayar_tabungan."',
						tgl_pengajuan = '".$tgl_pengajuan."',
						tgl_h_penjualan = '".$tgl_h_penjualan."',
						tgl_tempo = '".$tgl_tempo."',
						status_penjualan = '".$status_penjualan."',
						isTunai = '".$isTunai."',
						ket_penjualan = '".$ket_penjualan."',
						nama_set_roles = '".$nama_set_roles."',
						bulan = '".$bulan."',
						up = '".$up."',
						optr_up = '".$optr_up."',
						naik_persen = '".$naik_persen."',
						optr_naik_persen = '".$optr_naik_persen."',
						prsn_cicilan = '".$prsn_cicilan."',
						prsn_pokok = '".$prsn_pokok."',
						prsn_marketing = '".$prsn_marketing."',
						optr_marketing = '".$optr_marketing."',
						denda = '".$denda."',
						optr_denda = '".$optr_denda."',
						frek_denda = '".$frek_denda."',
						type_denda = '".$type_denda."',
						toleransi = '".$toleransi."',
						perkiraan_nominal = '".$perkiraan_nominal."',
						type_h_penjualan = '".$type_h_penjualan."',
						point = '".$point."',
						donasi = '".$donasi."',
						kat_donasi = '".$kat_donasi."',
						point_fixed = '".$point_fixed."',
						isKirim = '".$isKirim."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."',
						kode_kantor = '".$kode_kantor."',
						sts_penjualan = '".$sts_penjualan."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		//function hapus($berdasarkan,$id_kat_costumer)
		function hapus($cari)
		{
			/*HAPUS JABATAN*/
				$strquery =
				"
					DELETE FROM tb_h_penjualan ".$cari."
				";
				
				//$strquery = "DELETE FROM tb_h_penjualan WHERE ".$berdasarkan." = '".$id_kat_costumer."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_h_penjualan($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_h_penjualan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_h_penjualan_cari($cari)
        {
			$strquery =
			"
				SELECT * FROM tb_h_penjualan ".$cari."
			";
			
            //$query = $this->db->get_where('tb_h_penjualan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            $query = $this->db->query($strquery);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
        }
		
		function get_d_penjualan_cari($cari)
        {
			$strquery =
			"
				SELECT * FROM tb_d_penjualan ".$cari."
			";
			
            //$query = $this->db->get_where('tb_h_penjualan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            $query = $this->db->query($strquery);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
        }
		
		function get_d_penjualan_with_satuan_cari($cari)
        {
			$strquery =
			"
				SELECT * FROM tb_d_penjualan AS A LEFT JOIN tb_satuan AS B ON A.kode_kantor = B.kode_kantor AND A.satuan_jual = B.kode_satuan ".$cari."
			";
			
            //$query = $this->db->get_where('tb_h_penjualan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            $query = $this->db->query($strquery);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
        }
	
		function get_harga_dasar_satuan_konversi($cari)
		{
			$query =
			"
				SELECT A.* 
					,COALESCE(B.kode_satuan,'') AS kode_satuan
					,COALESCE(B.nama_satuan,'') AS nama_satuan
				FROM tb_satuan_konversi AS A 
				LEFT JOIN tb_satuan AS B ON A.kode_kantor = B.kode_kantor AND A.id_satuan = B.id_satuan
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
	
		function simpan_d_penjualan_with_kategori
		(
			$id_h_penjualan,
			$id_d_penerimaan,
			$id_produk,
			$id_h_diskon,
			$id_d_diskon,
			$id_kat_produk,
			$jumlah,
			$status_konversi,
			$konversi,
			$satuan_jual,
			$jumlah_konversi,
			$diskon,
			$optr_diskon,
			$besar_diskon_ori,
			$harga,
			$harga_konversi,
			$harga_ori,
			$harga_dasar_ori,
			$stock,
			$ket_d_penjualan,
			$isProduk,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_d_penjualan
				(
					id_d_penjualan,
					id_h_penjualan,
					id_d_penerimaan,
					id_produk,
					id_h_diskon,
					id_d_diskon,
					id_kat_produk,
					jumlah,
					status_konversi,
					konversi,
					satuan_jual,
					jumlah_konversi,
					diskon,
					optr_diskon,
					besar_diskon_ori,
					harga,
					harga_konversi,
					harga_ori,
					harga_dasar_ori,
					stock,
					ket_d_penjualan,
					isProduk,
					tgl_ins,
					tgl_updt,
					user_updt,
					user_ins,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_d_penjualan
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
								COALESCE(MAX(CAST(RIGHT(id_d_penjualan,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_d_penjualan
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_h_penjualan."',
					'".$id_d_penerimaan."',
					'".$id_produk."',
					'".$id_h_diskon."',
					'".$id_d_diskon."',
					'".$id_kat_produk."',
					'".$jumlah."',
					'".$status_konversi."',
					'".$konversi."',
					'".$satuan_jual."',
					'".$jumlah_konversi."',
					'".$diskon."',
					'".$optr_diskon."',
					'".$besar_diskon_ori."',
					'".$harga."',
					'".$harga_konversi."',
					'".$harga_ori."',
					
					
					COALESCE(
								(
									SELECT
										distinct
										A.harga_jual AS harga_modal
										/*
										A.*
										,COALESCE(B.kode_satuan,'') AS kode_satuan
										,COALESCE(B.nama_satuan,'') AS nama_satuan
										*/
									FROM tb_satuan_konversi AS A
									INNER JOIN tb_satuan AS B ON A.kode_kantor = B.kode_kantor AND A.id_satuan = B.id_satuan
									WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_produk = '".$id_produk."' AND kode_satuan = '".$satuan_jual."'
									GROUP BY A.harga_jual
								)
								,".$harga_dasar_ori.")
					,
					
					-- '".$harga_dasar_ori."',
					
					
					'".$stock."',
					'".$ket_d_penjualan."',
					'".$isProduk."',
					NOW(),
					NOW(),
					'',
					'".$user_ins."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function simpan_d_penjualan
		(
			$id_h_penjualan,
			$id_d_penerimaan,
			$id_produk,
			$id_h_diskon,
			$id_d_diskon,
			$jumlah,
			$status_konversi,
			$konversi,
			$satuan_jual,
			$jumlah_konversi,
			$diskon,
			$optr_diskon,
			$besar_diskon_ori,
			$harga,
			$harga_konversi,
			$harga_ori,
			$harga_dasar_ori,
			$stock,
			$ket_d_penjualan,
			$isProduk,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_d_penjualan
				(
					id_d_penjualan,
					id_h_penjualan,
					id_d_penerimaan,
					id_produk,
					id_h_diskon,
					id_d_diskon,
					jumlah,
					status_konversi,
					konversi,
					satuan_jual,
					jumlah_konversi,
					diskon,
					optr_diskon,
					besar_diskon_ori,
					harga,
					harga_konversi,
					harga_ori,
					harga_dasar_ori,
					stock,
					ket_d_penjualan,
					isProduk,
					tgl_ins,
					tgl_updt,
					user_updt,
					user_ins,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_d_penjualan
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
								COALESCE(MAX(CAST(RIGHT(id_d_penjualan,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_d_penjualan
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_h_penjualan."',
					'".$id_d_penerimaan."',
					'".$id_produk."',
					'".$id_h_diskon."',
					'".$id_d_diskon."',
					'".$jumlah."',
					'".$status_konversi."',
					'".$konversi."',
					'".$satuan_jual."',
					'".$jumlah_konversi."',
					'".$diskon."',
					'".$optr_diskon."',
					'".$besar_diskon_ori."',
					'".$harga."',
					'".$harga_konversi."',
					'".$harga_ori."',
					
					COALESCE(
								(
									SELECT
										distinct
										A.harga_jual AS harga_modal
										/*
										A.*
										,COALESCE(B.kode_satuan,'') AS kode_satuan
										,COALESCE(B.nama_satuan,'') AS nama_satuan
										*/
									FROM tb_satuan_konversi AS A
									INNER JOIN tb_satuan AS B ON A.kode_kantor = B.kode_kantor AND A.id_satuan = B.id_satuan
									WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_produk = '".$id_produk."' AND kode_satuan = '".$satuan_jual."'
									GROUP BY A.harga_jual
								)
								,".$harga_dasar_ori.")
					,
					
					-- '".$harga_dasar_ori."',
					
					'".$stock."',
					'".$ket_d_penjualan."',
					'".$isProduk."',
					NOW(),
					NOW(),
					'',
					'".$user_ins."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function ubah_d_penjualan(
			$cari,
			$id_h_penjualan,
			$id_d_penerimaan,
			$id_produk,
			$id_h_diskon,
			$id_d_diskon,
			$jumlah,
			$status_konversi,
			$konversi,
			$satuan_jual,
			$jumlah_konversi,
			$diskon,
			$optr_diskon,
			$besar_diskon_ori,
			$harga,
			$harga_konversi,
			$harga_ori,
			$harga_dasar_ori,
			$stock,
			$ket_d_penjualan,
			$aturan_minum,
			$user_updt,
			$kode_kantor

		)
		{
			$strquery=
			"
				UPDATE tb_d_penjualan SET
				
					id_h_penjualan = '".$id_h_penjualan."',
					id_d_penerimaan = '".$id_d_penerimaan."',
					id_produk = '".$id_produk."',
					id_h_diskon = '".$id_h_diskon."',
					id_d_diskon = '".$id_d_diskon."',
					jumlah = '".$jumlah."',
					status_konversi = '".$status_konversi."',
					konversi = '".$konversi."',
					satuan_jual = '".$satuan_jual."',
					jumlah_konversi = '".$jumlah_konversi."',
					diskon = '".$diskon."',
					optr_diskon = '".$optr_diskon."',
					besar_diskon_ori = '".$besar_diskon_ori."',
					harga = '".$harga."',
					harga_konversi = '".$harga_konversi."',
					-- harga_ori = '".$harga_ori."',
					harga_ori = '".$harga_konversi."',
					
					-- harga_dasar_ori = '".$harga_dasar_ori."',
					harga_dasar_ori = COALESCE(
													(
														SELECT
															distinct
															A.harga_jual AS harga_modal
															/*
															A.*
															,COALESCE(B.kode_satuan,'') AS kode_satuan
															,COALESCE(B.nama_satuan,'') AS nama_satuan
															*/
														FROM tb_satuan_konversi AS A
														INNER JOIN tb_satuan AS B ON A.kode_kantor = B.kode_kantor AND A.id_satuan = B.id_satuan
														WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_produk = '".$id_produk."' AND kode_satuan = '".$satuan_jual."'
														GROUP BY A.harga_jual
													)
													,".$harga_konversi.")
										,
					
					
					stock = '".$stock."',
					ket_d_penjualan = '".$ket_d_penjualan."',
					aturan_minum = '".$aturan_minum."',
					tgl_updt = NOW(),
					user_updt = '".$user_updt."'

				
				".$cari."
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
			
			
			
			// QUERY UPDATE PEMBELIAN, JIKA PEMESSNAN CABANG
			$strquery=
			"
				UPDATE tb_d_pembelian AS A
				INNER JOIN 
				(
					SELECT
						A.*
						,COALESCE(B.ket_penjualan,'') AS ket_penjualan
						,COALESCE(C.id_h_pembelian,'') AS id_h_pembelian
						,COALESCE(C.kode_kantor,'') AS kode_kantor_beli
					FROM tb_d_penjualan AS A 
					INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
					INNER JOIN tb_h_pembelian AS C ON B.ket_penjualan = C.no_h_pembelian
					WHERE A.id_h_penjualan = '".$id_h_penjualan."'
					AND A.id_produk = '".$id_produk."'
					AND A.satuan_jual = '".$satuan_jual."'
					AND A.kode_kantor = '".$kode_kantor."'
				) AS B ON A.kode_kantor = B.kode_kantor_beli AND A.id_h_pembelian = B.id_h_pembelian AND A.id_produk = B.id_produk AND A.kode_satuan = B.satuan_jual
				SET
					A.jumlah = B.jumlah
				WHERE B.id_produk = '".$id_produk."';
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
			
		}
		
		
		function list_d_penjualan_faktur($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT 
					A.id_d_penjualan,
					A.id_h_penjualan,
					A.id_d_penerimaan,
					A.id_produk,
					A.id_h_diskon,
					A.id_d_diskon,
					A.jumlah,
					A.status_konversi,
					A.konversi,
					A.satuan_jual,
					A.jumlah_konversi,
					A.diskon,
					CASE WHEN A.optr_diskon = '' THEN
						'%'
					ELSE
						A.optr_diskon
					END AS optr_diskon,
					
					-- A.besar_diskon_ori,
					CASE WHEN (A.optr_diskon = '%') OR (A.optr_diskon = '') THEN
						((A.harga/100) * A.diskon)
					ELSE
						A.diskon
					END AS besar_diskon_ori,
					
					A.harga,
					/*
					CASE WHEN (A.optr_diskon = '%') OR (A.optr_diskon = '') THEN
						((A.harga/100) * A.diskon) + A.harga
					ELSE
						A.harga + A.diskon
					END AS harga,
					*/
					A.harga_konversi,
					A.harga_ori,
					A.harga_dasar_ori,
					A.stock,
					A.ket_d_penjualan,
					A.aturan_minum,
					A.isProduk,
					A.isReady,
					A.ket_ready,
					A.isTerima,
					
					A.id_dok,
					A.nama_dok,
					A.id_ass,
					A.nama_ass,
					
					A.tgl_ins,
					A.tgl_updt,
					A.user_updt,
					A.user_ins,
					A.kode_kantor
					
					,COALESCE(B.kode_produk,'') AS kode_produk
					,COALESCE(B.nama_produk,'') AS nama_produk_ori
					,CASE 
						WHEN COALESCE(B.nama_umum,'') = '' THEN
							COALESCE(B.nama_produk,'')
						ELSE
							COALESCE(B.nama_umum,'')
						END AS nama_produk
						
				FROM tb_d_penjualan AS A
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_produk,kode_produk,nama_produk,nama_umum,isProduk
					FROM tb_produk
					WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
					GROUP BY kode_kantor,id_produk,kode_produk,nama_produk,nama_umum,isProduk
				) AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor
				".$cari."
				".$order_by."
				LIMIT ".$offset.",".$limit."
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
		
		function list_d_penjualan($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT 
					A.id_d_penjualan,
					A.id_h_penjualan,
					A.id_d_penerimaan,
					A.id_produk,
					A.id_h_diskon,
					A.id_d_diskon,
					A.id_d_diskon2,
					A.id_kat_produk,
					A.jumlah,
					A.status_konversi,
					A.konversi,
					A.satuan_jual,
					A.jumlah_konversi,
					A.diskon,
					CASE WHEN A.optr_diskon = '' THEN
						'%'
					ELSE
						A.optr_diskon
					END AS optr_diskon,
					
					-- A.besar_diskon_ori,
					CASE WHEN (A.optr_diskon = '%') OR (A.optr_diskon = '') THEN
						((A.harga/100) * A.diskon)
					ELSE
						A.diskon
					END AS besar_diskon_ori,
					
					A.harga,
					/*
					CASE WHEN (A.optr_diskon = '%') OR (A.optr_diskon = '') THEN
						((A.harga/100) * A.diskon) + A.harga
					ELSE
						A.harga + A.diskon
					END AS harga,
					*/
					A.harga_konversi,
					A.harga_ori,
					A.harga_dasar_ori,
					A.stock,
					A.ket_d_penjualan,
					A.aturan_minum,
					A.isProduk,
					A.isReady,
					A.ket_ready,
					A.isTerima,
					A.tgl_ins,
					A.tgl_updt,
					A.user_updt,
					A.user_ins,
					A.kode_kantor
					,COALESCE(B.kode_produk,'') AS kode_produk	
					,COALESCE(B.nama_produk,'') AS nama_produk
					,COALESCE(C.nama_diskon,'') AS nama_diskon					
				FROM tb_d_penjualan AS A
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_produk,kode_produk,nama_produk,isProduk
					FROM tb_produk
					GROUP BY kode_kantor,id_produk,kode_produk,nama_produk,isProduk
				) AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_h_diskon,nama_diskon
					FROM tb_h_diskon
					GROUP BY kode_kantor,id_h_diskon,nama_diskon
				) AS C ON A.kode_kantor = C.kode_kantor 
					AND 
						CASE WHEN A.id_d_diskon2 <> '' THEN
							A.id_d_diskon2 = C.id_h_diskon
						ELSE
							A.id_h_diskon = C.id_h_diskon
						END
				".$cari."
				".$order_by."
				LIMIT ".$offset.",".$limit."
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
	
		function harga_costumer_satuanharga_costumer_satuan($kode_kantor,$id_produk,$id_costumer,$kode_satuan)
		{
			$query =
			"
				SELECT A.* 
					,COALESCE(B.harga_jual,'') AS harga_modal
					,COALESCE(B.status,'') AS status
					,COALESCE(B.besar_konversi,'') AS besar_konversi
					,COALESCE(C.kode_satuan,'') AS kode_satuan
				FROM tb_produk_harga_satuan_costumer AS A
				LEFT JOIN tb_satuan_konversi AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk AND A.id_satuan = B.id_satuan
				LEFT JOIN tb_satuan AS C ON A.kode_kantor = C.kode_kantor AND A.id_satuan = C.id_satuan
				WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_produk = '".$id_produk."' AND id_costumer = '".$id_costumer."' AND COALESCE(C.kode_satuan,'') ='".$kode_satuan."';
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
	
		function hapus_d_penjualan_with_cari($cari)
		{
			$strquery=
			"
				DELETE FROM tb_d_penjualan ".$cari."
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_d_pembelian_with_cari($query)
		{
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($query);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function list_fee
		(
			$kode_kantor,
			$diberikan_kepada,
			$jenis_poli,
			$cari
		)
		{
			$query =
			"
				SELECT * FROM tb_h_fee WHERE kode_kantor = '".$kode_kantor."' AND UPPER(diberikan_kepada) = '".strtoupper($diberikan_kepada)."' AND jenis_poli = '".$jenis_poli."' ".$cari." ;
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
		
		function hapus_d_penjualan_fee($id_h_penjualan,$kode_kantor,$group_by,$id_karyawan)
		{
			$strquery =
			"
				DELETE FROM tb_d_penjualan_fee WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."' AND group_by = '".$group_by."' AND id_karyawan = '".$id_karyawan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_d_penjualan_fee_cari($cari)
		{
			$strquery =
			"
				DELETE FROM tb_d_penjualan_fee ".$cari."
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_d_fee_penjualan
		(
			$id_h_penjualan,
			$id_h_fee,
			$id_karyawan,
			$group_by,
			$nama_fee,
			$nominal_fee,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_d_penjualan_fee
				(
					id_d_penjualan_fee,
					id_h_penjualan,
					id_h_fee,
					id_karyawan,
					group_by,
					nama_fee,
					nominal_fee,
					tgl_ins,
					kode_kantor

				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_d_penjualan_fee
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
								COALESCE(MAX(CAST(RIGHT(id_d_penjualan_fee,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_d_penjualan_fee
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_h_penjualan."',
					'".$id_h_fee."',
					'".$id_karyawan."',
					'".$group_by."',
					'".$nama_fee."',
					'".$nominal_fee."',
					NOW(),
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		
		function list_d_penjualan_bayar
		(
			$cari,
			$order_by,
			$limit,
			$offset
		)
		{
			$query =
			"
				SELECT * FROM tb_d_penjualan_bayar ".$cari." ".$order_by." LIMIT ".$offset.",".$limit."
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
		
		function list_d_penjualan_bayar_sum_nominal
		(
			$cari,
			$order_by,
			$limit,
			$offset
		)
		{
			$query =
			"
				SELECT SUM(nominal) AS nominal FROM tb_d_penjualan_bayar ".$cari." ".$order_by." LIMIT ".$offset.",".$limit."
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
	
	
		function simpan_d_penjualan_bayar
		(
			$id_h_penjualan,
			$id_bank,
			$id_costumer,
			$no_pembayaran,
			$isTabungan,
			$cara,
			$jenis,
			$norek,
			$nama_bank,
			$atas_nama,
			$tgl_pencairan,
			$nominal,
			$ket,
			$tgl_bayar,
			$dari,
			$diterima_oleh,
			$isCair,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_d_penjualan_bayar
				(
					id_d_bayar,
					id_h_penjualan,
					id_bank,
					id_costumer,
					no_pembayaran,
					isTabungan,
					cara,
					jenis,
					norek,
					nama_bank,
					atas_nama,
					tgl_pencairan,
					nominal,
					ket,
					tgl_bayar,
					dari,
					diterima_oleh,
					isCair,
					tgl_ins,
					tgl_updt,
					user_updt,
					kode_kantor

				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_d_bayar
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
								COALESCE(MAX(CAST(RIGHT(id_d_bayar,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_d_penjualan_bayar
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_h_penjualan."',
					'".$id_bank."',
					'".$id_costumer."',
					'".$no_pembayaran."',
					'".$isTabungan."',
					'".$cara."',
					'".$jenis."',
					'".$norek."',
					'".$nama_bank."',
					'".$atas_nama."',
					'".$tgl_pencairan."',
					'".$nominal."',
					'".$ket."',
					'".$tgl_bayar."',
					'".$dari."',
					'".$diterima_oleh."',
					'".$isCair."',
					NOW(),
					NOW(),
					'".$user_updt."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function edit_d_penjualan_bayar
		(
			$id_d_bayar,
			$id_h_penjualan,
			$id_bank,
			$id_costumer,
			$no_pembayaran,
			$isTabungan,
			$cara,
			$jenis,
			$norek,
			$nama_bank,
			$atas_nama,
			$tgl_pencairan,
			$nominal,
			$ket,
			$tgl_bayar,
			$dari,
			$diterima_oleh,
			$isCair,
			$user_updt,
			$kode_kantor,
			$cari
		)
		{
			$strquery=
			"
				UPDATE tb_d_penjualan_bayar SET
				
					id_h_penjualan = '".$id_h_penjualan."',
					id_bank = '".$id_bank."',
					id_costumer = '".$id_costumer."',
					no_pembayaran = '".$no_pembayaran."',
					isTabungan = '".$isTabungan."',
					cara = '".$cara."',
					jenis = '".$jenis."',
					norek = '".$norek."',
					nama_bank = '".$nama_bank."',
					atas_nama = '".$atas_nama."',
					tgl_pencairan = '".$tgl_pencairan."',
					nominal = '".$nominal."',
					ket = '".$ket."',
					tgl_bayar = '".$tgl_bayar."',
					dari = '".$dari."',
					diterima_oleh = '".$diterima_oleh."',
					isCair = '".$isCair."',
					tgl_updt = NOW(),
					user_updt = '".$user_updt."'
				".$cari."
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
	
		function hapus_d_penjualan_bayar
		(
			$cari
		)
		{
			$strquery=
			"
				DELETE FROM tb_d_penjualan_bayar ".$cari."
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function ubah_h_penjualan_sts_saja_for_slim($kode_kantor,$id_h_penjualan,$status,$ket_penjualan2)
		{
			$strquery=
			"
				UPDATE tb_h_penjualan 
					SET 
						sts_penjualan = '".$status."'
						, ket_penjualan2 = '".$ket_penjualan2."'
						, tgl_h_penjualan = CASE WHEN tgl_h_penjualan = '0000-00-00' THEN DATE(NOW()) ELSE tgl_h_penjualan END
						, tgl_updt = NOW() 
					WHERE kode_kantor = '".$kode_kantor."' 
					AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_h_penjualan_sts_saja($kode_kantor,$id_h_penjualan,$status,$pajak,$diskon,$ket_penjualan2)
		{
			$strquery=
			"
				UPDATE tb_h_penjualan 
				SET 
					sts_penjualan = '".$status."'
					, pajak = '".$pajak."'
					, diskon = '".$diskon."'
					, ket_penjualan2 = '".$ket_penjualan2."' 
					, tgl_tempo = (
										CASE WHEN tgl_tempo = '0000-00-00 00:00:00' THEN
											NOW()
										ELSE
											tgl_tempo
										end
									)
					, tgl_updt = NOW() 
				WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_h_penjualan_sts_saja2($kode_kantor,$id_h_penjualan,$status)
		{
			$strquery=
			"
				UPDATE tb_h_penjualan SET sts_penjualan = '".$status."', tgl_updt = NOW() WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_h_penjualan_dkokter_therapist_tindakan($kode_kantor,$id_h_penjualan,$id_dokter,$id_therapist, $id_therapist2,$id_gedung)
		{
			$strquery=
			"
				UPDATE tb_h_penjualan SET id_dokter2 = '".$id_dokter."', id_ass_dok = '".$id_therapist."' , id_ass_dok2 = '".$id_therapist2."', id_gedung = '".$id_gedung."', waktu_mulai_tindakan = NOW() WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function ubah_h_penjualan_d_diskon_paket($kode_kantor,$id_h_penjualan,$id_h_diskon)
		{
			$strquery=
			"
				UPDATE tb_h_penjualan SET id_h_diskon = '".$id_h_diskon."' WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_h_penjualan_ubah_waktu_mulai_pemeriksaan($kode_kantor,$id_h_penjualan)
		{
			$strquery=
			"
				UPDATE tb_h_penjualan SET waktu_mulai_pemeriksaan = NOW() WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_h_penjualan_ubah_waktu_selesai_pemeriksaan($kode_kantor,$id_h_penjualan)
		{
			$strquery=
			"
				UPDATE tb_h_penjualan SET waktu_selesai_pemeriksaan = NOW() WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_h_penjualan_media_transaksi($id_h_penjualan,$kode_kantor,$media_transaksi,$diagnosa,$id_sales,$isKirim,$tgl_tempo,$tgl_h_penjualan)
		{
			$strquery=
			"
				UPDATE tb_h_penjualan 
					SET 
						media_transaksi = '".$media_transaksi."'
						, ket_penjualan2 = '".$diagnosa."' 
						, id_sales = '".$id_sales."'
						, isKirim = '".$isKirim."'
						, tgl_h_penjualan = '".$tgl_h_penjualan."'
						, tgl_tempo = '".$tgl_tempo."'
				WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_d_penjualan_therapist($kode_kantor,$id_h_penjualan,$id_d_penjualan,$id_therapist,$nama_therapist)
		{
			//UBAH D PENJUALAN
				$strquery=
				"
					UPDATE tb_d_penjualan SET id_ass = '".$id_therapist."' , nama_ass = '".$nama_therapist."' WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."' AND id_d_penjualan = '".$id_d_penjualan."'
				";
				
				/*SIMPAN DAN CATAT QUERY*/
					$this->M_gl_log->simpan_query($strquery);
				/*SIMPAN DAN CATAT QUERY*/
			//UBAH D PENJUALAN
			
			
			//UBAH H PENJUALAN THERAPIST
				$strquery=
				"
					UPDATE tb_h_penjualan SET
						waktu_mulai_tindakan = 
											CASE WHEN waktu_mulai_tindakan = '0000-00-00 00:00:00' THEN 
												NOW()
											ELSE
												waktu_mulai_tindakan
											END
						,id_ass_dok = COALESCE((SELECT id_ass FROM tb_d_penjualan WHERE id_h_penjualan = '".$id_h_penjualan."' AND id_ass <> '' AND kode_kantor = '".$kode_kantor."' GROUP BY id_ass LIMIT 0,1),'')
						,id_ass_dok2 = COALESCE((SELECT id_ass FROM tb_d_penjualan WHERE id_h_penjualan = '".$id_h_penjualan."' AND id_ass <> '' AND kode_kantor = '".$kode_kantor."' GROUP BY id_ass LIMIT 1,1),'')
					WHERE id_h_penjualan = '".$id_h_penjualan."' AND kode_kantor = '".$kode_kantor."';
				";
				//SIMPAN DAN CATAT QUERY
					$this->M_gl_log->simpan_query($strquery);
				//SIMPAN DAN CATAT QUERY
			//UBAH H PENJUALAN THERAPIST
			
			
			/*
			//UBAH H PENJUALAN THERAPIST 1
				$strquery=
				"
					UPDATE tb_h_penjualan SET 
					id_ass_dok = CASE WHEN id_ass_dok = '".$id_therapist."' THEN id_ass_dok ELSE '".$id_therapist."' END 
					
					WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
				";
				
				//SIMPAN DAN CATAT QUERY
					$this->M_gl_log->simpan_query($strquery);
				//SIMPAN DAN CATAT QUERY
			//UBAH H PENJUALAN THERAPIST 1
			
			//UBAH H PENJUALAN THERAPIST 2
				$strquery=
				"
					UPDATE tb_h_penjualan SET 
					id_ass_dok2 = CASE WHEN (id_ass_dok <> '".$id_therapist."') AND (id_ass_dok2 <> '') AND (id_ass_dok2 <> '".$id_therapist."') THEN '".$id_therapist."' ELSE id_ass_dok2 END 
					
					WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
				";
				
				//SIMPAN DAN CATAT QUERY
					$this->M_gl_log->simpan_query($strquery);
				//SIMPAN DAN CATAT QUERY
			//UBAH H PENJUALAN THERAPIST 2
			*/
		}
		
		function ubah_d_penjualan_dokter($kode_kantor,$id_h_penjualan,$id_d_penjualan,$id_dok,$nama_dok)
		{
			//UBAH D PENJUALAN
				$strquery=
				"
					UPDATE tb_d_penjualan SET id_dok = '".$id_dok."' , nama_dok = '".$nama_dok."' WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."' AND id_d_penjualan = '".$id_d_penjualan."'
				";
				
				/*SIMPAN DAN CATAT QUERY*/
					$this->M_gl_log->simpan_query($strquery);
				/*SIMPAN DAN CATAT QUERY*/
			//UBAH D PENJUALAN
			
			
			//UBAH H PENJUALAN THERAPIST
				/*
				$strquery=
				"
					UPDATE tb_h_penjualan SET 
						id_dokter = COALESCE((SELECT id_dok FROM tb_d_penjualan WHERE id_h_penjualan = '".$id_h_penjualan."' AND id_dok <> '' AND kode_kantor = '".$kode_kantor."' GROUP BY id_dok LIMIT 0,1),'')
						,id_dokter2 = COALESCE((SELECT id_dok FROM tb_d_penjualan WHERE id_h_penjualan = '".$id_h_penjualan."' AND id_dok <> '' AND kode_kantor = '".$kode_kantor."' GROUP BY id_dok LIMIT 1,1),'')
					WHERE id_h_penjualan = '".$id_h_penjualan."' AND kode_kantor = '".$kode_kantor."';
				";
				//SIMPAN DAN CATAT QUERY
					$this->M_gl_log->simpan_query($strquery);
				//SIMPAN DAN CATAT QUERY
				*/
			//UBAH H PENJUALAN THERAPIST
			
			
			/*
			//UBAH H PENJUALAN THERAPIST 1
				$strquery=
				"
					UPDATE tb_h_penjualan SET 
					id_ass_dok = CASE WHEN id_ass_dok = '".$id_therapist."' THEN id_ass_dok ELSE '".$id_therapist."' END 
					
					WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
				";
				
				//SIMPAN DAN CATAT QUERY
					$this->M_gl_log->simpan_query($strquery);
				//SIMPAN DAN CATAT QUERY
			//UBAH H PENJUALAN THERAPIST 1
			
			//UBAH H PENJUALAN THERAPIST 2
				$strquery=
				"
					UPDATE tb_h_penjualan SET 
					id_ass_dok2 = CASE WHEN (id_ass_dok <> '".$id_therapist."') AND (id_ass_dok2 <> '') AND (id_ass_dok2 <> '".$id_therapist."') THEN '".$id_therapist."' ELSE id_ass_dok2 END 
					
					WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
				";
				
				//SIMPAN DAN CATAT QUERY
					$this->M_gl_log->simpan_query($strquery);
				//SIMPAN DAN CATAT QUERY
			//UBAH H PENJUALAN THERAPIST 2
			*/
		}
		
		
		function ubah_status_therapist_pada_h_penjualan_masal($id_h_penjualan,$kode_kantor)
		{
			//UBAH H PENJUALAN THERAPIST
				$strquery=
				"
					UPDATE tb_h_penjualan SET 
						id_ass_dok = COALESCE((SELECT id_ass FROM tb_d_penjualan WHERE id_h_penjualan = '".$id_h_penjualan."' AND id_ass <> '' AND kode_kantor = '".$kode_kantor."' GROUP BY id_ass LIMIT 0,1),'')
						,id_ass_dok2 = COALESCE((SELECT id_ass FROM tb_d_penjualan WHERE id_h_penjualan = '".$id_h_penjualan."' AND id_ass <> '' AND kode_kantor = '".$kode_kantor."' GROUP BY id_ass LIMIT 1,1),'')
					WHERE id_h_penjualan = '".$id_h_penjualan."' AND kode_kantor = '".$kode_kantor."';
				";
				//SIMPAN DAN CATAT QUERY
					$this->M_gl_log->simpan_query($strquery);
				//SIMPAN DAN CATAT QUERY
			//UBAH H PENJUALAN THERAPIST
		}
		
		function ubah_d_penjualan_tambahkan_produk_saja($kode_kantor,$id_d_penjualan,$id_produk,$satuan_jual,$harga_ori)
		{
		
			//UBAH D PENJUALAN THERAPIST
				$strquery=
				"
					UPDATE tb_d_penjualan SET 
						id_produk = '".$id_produk."',
						status_konversi = '*',
						konversi = '1',
						jumlah_konversi = jumlah,
						satuan_jual = '".$satuan_jual."',
						harga_ori = '".$harga_ori."',
						harga_dasar_ori = '".$harga_ori."'
						
					WHERE id_d_penjualan = '".$id_d_penjualan."' AND kode_kantor = '".$kode_kantor."';
				";
				//SIMPAN DAN CATAT QUERY
					$this->M_gl_log->simpan_query($strquery);
				//SIMPAN DAN CATAT QUERY
			//UBAH D PENJUALAN THERAPIST
		}
		
		function ubah_d_penjualan_id_h_diskon($kode_kantor,$id_h_penjualan,$id_produk,$id_d_diskon2,$satuan_jual)
		{
		
			//UBAH D PENJUALAN THERAPIST
				$strquery=
				"
					UPDATE tb_d_penjualan SET 
						id_d_diskon2 = '".$id_d_diskon2."'
						
					WHERE id_h_penjualan = '".$id_h_penjualan."' AND id_produk = '".$id_produk."' AND satuan_jual = '".$satuan_jual."' AND kode_kantor = '".$kode_kantor."';
				";
				//SIMPAN DAN CATAT QUERY
					$this->M_gl_log->simpan_query($strquery);
				//SIMPAN DAN CATAT QUERY
			//UBAH D PENJUALAN THERAPIST
		}
		
		function simpan_penjualan_dari_req($kode_kantor,$id_h_penjualan,$id_h_pembelian,$kode_cabang_pemesan)
		{
			/*HAPUS JABATAN*/
				$strquery = "

				INSERT INTO tb_d_penjualan
				(	
						id_d_penjualan,
						id_h_penjualan,
						id_d_penerimaan,
						id_produk,
						id_h_diskon,
						id_d_diskon,
						id_kat_produk,
						jumlah,
						status_konversi,
						konversi,
						satuan_jual,
						jumlah_konversi,
						diskon,
						optr_diskon,
						besar_diskon_ori,
						harga,
						harga_konversi,
						harga_ori,
						harga_dasar_ori,
						stock,
						ket_d_penjualan,
						aturan_minum,
						isProduk,
						isReady,
						ket_ready,
						isTerima,
						id_ass,
						nama_ass,
						tgl_ins,
						tgl_updt,
						user_updt,
						user_ins,
						kode_kantor

				)
			
				SELECT
				
				CASE WHEN (SELECT id_d_penjualan FROM tb_d_penjualan WHERE kode_kantor = '".$kode_cabang_pemesan."' AND id_d_penjualan = CONCAT(id_d_pembelian,'2')) = CONCAT(id_d_pembelian,'2') THEN
					CONCAT('".$this->session->userdata('ses_kode_kantor')."',id_d_pembelian)
				ELSE
					CONCAT('".$this->session->userdata('ses_kode_kantor')."',id_d_pembelian)
				END
				,
				'".$id_h_penjualan."',
				'',
				id_produk,
				'',
				'',
				'',
				jumlah,
				status_konversi,
				konversi,
				kode_satuan,
				jumlah * konversi,
				diskon,
				optr_diskon,
				0,
				harga,
				harga,
				harga,
				harga,
				0,
				'',
				'',
				'',
				1,
				'',
				1,
				'',
				'',
				NOW(),
				NOW(),
				'".$this->session->userdata('ses_is_karyawan')."',
				'".$this->session->userdata('ses_is_karyawan')."', 
				'".$this->session->userdata('ses_kode_kantor')."'
				FROM tb_d_pembelian WHERE MD5(id_h_pembelian) = '".$id_h_pembelian."' AND MD5(kode_kantor) = '".$kode_cabang_pemesan."'
			";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_penjualan_dari_req_by_procedure($kode_kantor,$id_h_penjualan,$id_h_pembelian,$kode_cabang_pemesan)
		{
			/*EKS QUERY*/
				$strquery = "
					
CALL SP_LOOP_PO_CABANG_TO_D_PENJUALAN('".$kode_cabang_pemesan."','".$id_h_pembelian."','".$kode_kantor."','".$id_h_penjualan."','".$this->session->userdata('ses_is_karyawan')."');
			";
			/*EKS QUERY*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
			
			
			
			
			/*EKS QUERY UNTUK UPDATE HARGA DASAR PROSES PESANAN CABANG*/
				$strquery = "
					UPDATE tb_d_penjualan SET
					harga = CASE WHEN 'YA' = 'YA' THEN
							(
								SELECT DISTINCT (A.harga_jual) AS harga_jual 
								FROM tb_satuan_konversi AS A
								LEFT JOIN tb_satuan AS B ON A.kode_kantor = B.kode_kantor AND A.id_satuan = B.id_satuan
								WHERE A.kode_kantor = tb_d_penjualan.kode_kantor -- 'PST' 
								AND A.id_produk = tb_d_penjualan.id_produk -- 'OFJKT2020070700048'  -- Ambil Dari Looping
								AND COALESCE(B.kode_satuan,'') = tb_d_penjualan.satuan_jual -- 'PCS' -- Ambil Dari Looping
								GROUP BY A.harga_jual
							)
						ELSE
							tb_d_penjualan.harga
						END
					,harga_konversi = CASE 
								WHEN 'YA' = 'YA' THEN
									CASE WHEN status_konversi = '*' THEN
										(
											SELECT DISTINCT (A.harga_jual) AS harga_jual 
											FROM tb_satuan_konversi AS A
											LEFT JOIN tb_satuan AS B ON A.kode_kantor = B.kode_kantor AND A.id_satuan = B.id_satuan
											WHERE A.kode_kantor = tb_d_penjualan.kode_kantor -- 'PST' 
											AND A.id_produk = tb_d_penjualan.id_produk -- 'OFJKT2020070700048'  -- Ambil Dari Looping
											AND COALESCE(B.kode_satuan,'') = tb_d_penjualan.satuan_jual -- 'PCS' -- Ambil Dari Looping
											GROUP BY A.harga_jual
										)
										/
										tb_d_penjualan.konversi
									ELSE
										(
											SELECT DISTINCT (A.harga_jual) AS harga_jual 
											FROM tb_satuan_konversi AS A
											LEFT JOIN tb_satuan AS B ON A.kode_kantor = B.kode_kantor AND A.id_satuan = B.id_satuan
											WHERE A.kode_kantor = tb_d_penjualan.kode_kantor -- 'PST' 
											AND A.id_produk = tb_d_penjualan.id_produk -- 'OFJKT2020070700048'  -- Ambil Dari Looping
											AND COALESCE(B.kode_satuan,'') = tb_d_penjualan.satuan_jual -- 'PCS' -- Ambil Dari Looping
											GROUP BY A.harga_jual
										)
										*
										tb_d_penjualan.konversi
									END 
								ELSE
									harga_konversi  -- Ambil Dari Looping
								END
							,harga_dasar_ori = (
													SELECT DISTINCT (A.harga_jual) AS harga_jual 
													FROM tb_satuan_konversi AS A
													LEFT JOIN tb_satuan AS B ON A.kode_kantor = B.kode_kantor AND A.id_satuan = B.id_satuan
													WHERE A.kode_kantor = tb_d_penjualan.kode_kantor -- 'PST' 
													AND A.id_produk = tb_d_penjualan.id_produk -- 'OFJKT2020070700048'  -- Ambil Dari Looping
													AND COALESCE(B.kode_satuan,'') = tb_d_penjualan.satuan_jual -- 'PCS' -- Ambil Dari Looping
													GROUP BY A.harga_jual
												)
					WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."';
				";
			/*EKS QUERY UNTUK UPDATE HARGA DASAR PROSES PESANAN CABANG*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function ubah_h_penjualan_by_kriteria_query($strQuery)
		{
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strQuery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_harga_dasar_modal()
		{
		}
	
	}
?>