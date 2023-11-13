<?php
	class M_gl_dashboard extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function JUM_PASIEN()
		{
			$query = 
			"
				SELECT COUNT(id_costumer) AS JUMLAH_PASIEN FROM tb_costumer WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
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
		
		function JUM_DOKTER()
		{
			$query = 
			"
				SELECT COUNT(id_karyawan) AS JUMLAH_DOKTER FROM tb_karyawan WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND isDokter = 'DOKTER' AND isAktif NOT IN ('PHK','RESIGN')
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
		
		function JUM_KARYAWAN()
		{
			$query = 
			"
				SELECT COUNT(id_karyawan) AS JUMLAH_DOKTER FROM tb_karyawan WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND isAktif NOT IN ('PHK','RESIGN')
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
		
		function JUM_KUNJUNGAN()
		{
			$query =
			"
				SELECT COUNT(id_h_penjualan) AS JUMLAH_KUNJUNGAN FROM tb_h_penjualan WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND DATE(tgl_h_penjualan) = DATE(NOW())
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
		
		function list_h_diskon_limit($cari,$limit,$offset)
		{
			$query = 
			"
				
				SELECT
					A.id_h_diskon,
					A.id_kat_costumer,
					A.id,group_by,
					A.nama_diskon,
					A.besar_diskon,
					A.optr_diskon,
					A.ket_diskon,
					A.dari,
					A.sampai,
					A.satuan,
					A.satuan_diskon,
					A.optr_kondisi,
					A.besar_pembelian,
					A.set_default,
					A.tgl_ins,
					A.tgl_updt
					,A.user_updt
					,A.user_ins
					,A.kode_kantor
					,A.kode_produk
					,A.nama_produk
					,A.nama_kat_costumer
					,A.JUMLAH_PRODUKS
					,GROUP_CONCAT(DISTINCT hari ORDER BY hari ASC SEPARATOR ', ') AS HARI_AKTIF
				FROM
				(
					SELECT  
						A.id_h_diskon,
						A.id_kat_costumer,
						A.id,group_by,
						A.nama_diskon,
						A.besar_diskon,
						A.optr_diskon,
						A.ket_diskon,
						A.dari,
						A.sampai,
						A.satuan,
						A.satuan_diskon,
						A.optr_kondisi,
						A.besar_pembelian,
						A.set_default,
						A.tgl_ins,
						A.tgl_updt
						,A.user_updt
						,A.user_ins
						,A.kode_kantor
						,COALESCE(B.kode_produk,'') AS kode_produk
						,COALESCE(B.nama_produk,'') AS nama_produk
						,COALESCE(C.nama_kat_costumer,'') AS nama_kat_costumer
						,COALESCE(D.JUMLAH,0) AS JUMLAH_PRODUKS
						,COALESCE(E.hari,'') AS hari
					FROM tb_h_diskon AS A 
					LEFT JOIN tb_produk AS B ON A.id = B.id_produk AND A.kode_kantor = B.kode_kantor
					LEFT JOIN tb_kat_costumer AS C ON A.id_kat_costumer = C.id_kat_costumer AND A.kode_kantor = C.kode_kantor
					LEFT JOIN 
					(
						SELECT COUNT(id_d_diskon) AS JUMLAH, id_h_diskon,kode_kantor
						FROM tb_d_diskon
						GROUP BY id_h_diskon,kode_kantor
					) AS D ON A.id_h_diskon = D.id_h_diskon AND A.kode_kantor = D.kode_kantor
					LEFT JOIN tb_hari_diskon AS E ON A.id_h_diskon = E.id_h_diskon AND A.kode_kantor = E.kode_kantor
					
					".$cari." LIMIT ".$offset.",".$limit."
				) AS A
				GROUP BY A.id_h_diskon,
					A.id_kat_costumer,
					A.id,group_by,
					A.nama_diskon,
					A.besar_diskon,
					A.optr_diskon,
					A.ket_diskon,
					A.dari,
					A.sampai,
					A.satuan,
					A.satuan_diskon,
					A.optr_kondisi,
					A.besar_pembelian,
					A.set_default,
					A.tgl_ins,
					A.tgl_updt
					,A.user_updt
					,A.user_ins
					,A.kode_kantor
					,A.kode_produk
					,A.nama_produk
					,A.nama_kat_costumer
					,A.JUMLAH_PRODUKS
				ORDER BY A.tgl_ins DESC 
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
	
		function list_log_aktifitas_limit($cari,$order_by,$limit,$offset)
		{
			if (mysqli_more_results($this->db->conn_id)) 
			{
				mysqli_next_result($this->db->conn_id);
			}
		
			$query = 
				"
					SELECT 
						A.* 
						,COALESCE(B.no_karyawan,'') AS no_karyawan
						,COALESCE(B.nama_karyawan,'') AS nama_karyawan
						,COALESCE(B.avatar,'') AS avatar
						,COALESCE(B.avatar_url,'') AS avatar_url
					FROM tb_log AS A
					LEFT JOIN tb_karyawan AS B ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
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
	
		function list_statistik_kunjungan($kode_kantor)
		{
			$query = 
			"
				SELECT RIGHT(A.DATE_LIST,2) AS DATE_LIST,COALESCE(B.JUMLAH_KUNJUNGAN,0) AS JUM_KUNJUNGAN
				FROM
				(
					select * from
					(select adddate('1970-01-01',t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) AS DATE_LIST from
					 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
					 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
					 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
					 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3,
					 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t4) v
					where DATE_LIST BETWEEN DATE(CONCAT(YEAR(NOW()),'-',MONTH(NOW()),'-01')) AND DATE(CONCAT(YEAR(NOW()),'-',MONTH(NOW()),'-',DATE_FORMAT(LAST_DAY(NOW()),'%d') ))
				) AS A
				LEFT JOIN
				(
					SELECT DATE(tgl_h_penjualan) AS DT, COUNT(id_h_penjualan) AS JUMLAH_KUNJUNGAN 
					FROM tb_h_penjualan
					WHERE kode_kantor = '".$kode_kantor."' AND sts_penjualan = 'SELESAI'
					GROUP BY DATE(tgl_h_penjualan)
				) AS B ON A.DATE_LIST = B.DT ORDER BY A.DATE_LIST ASC; 
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
	
		function update_moving_produk()
		{
			$strquery = 
				"
					UPDATE tb_produk AS A
					LEFT JOIN
					(

						SELECT
							B.id_produk
							,COALESCE(B.RATA_JUMLAH,0) AS PENJUALAN
							,COALESCE(B.satuan_jual,0) AS SATUAN
							,COALESCE(B.kode_kantor,'') AS KANTOR
							,
							CASE WHEN 
									(
										(
											COALESCE(B.RATA_JUMLAH,0) 
											>= 
											(SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(nilai, '-', 1), '-', -1) AS first_name FROM tb_pengaturan WHERE id_pengaturan = '10')
										)
										AND
										(
											COALESCE(B.RATA_JUMLAH,0) 
											<= 
											(SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(nilai, '-', 2), '-', -1) AS first_name FROM tb_pengaturan WHERE id_pengaturan = '10')
										)
									) THEN
									'SLOW'
								WHEN 
									(
										(
											COALESCE(B.RATA_JUMLAH,0) 
											>= 
											(SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(nilai, '-', 1), '-', -1) AS first_name FROM tb_pengaturan WHERE id_pengaturan = '11')
										)
										AND
										(
											COALESCE(B.RATA_JUMLAH,0) 
											<= 
											(SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(nilai, '-', 2), '-', -1) AS first_name FROM tb_pengaturan WHERE id_pengaturan = '11')
										)
									) THEN
									'MIDDLE'
								WHEN 
									(
										(
											COALESCE(B.RATA_JUMLAH,0) 
											>= 
											(SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(nilai, '-', 1), '-', -1) AS first_name FROM tb_pengaturan WHERE id_pengaturan = '12')
										)
										AND
										(
											COALESCE(B.RATA_JUMLAH,0) 
											<= 
											(SELECT SUBSTRING_INDEX(SUBSTRING_INDEX(nilai, '-', 2), '-', -1) AS first_name FROM tb_pengaturan WHERE id_pengaturan = '12')
										)
									) THEN
									'FAST'
								ELSE
									''
								END AS JENIS_MOVING
								
								,ROUND( COALESCE(B.RATA_JUMLAH,0)/100, 2 ) * (SELECT nilai FROM tb_pengaturan WHERE id_pengaturan = '13') AS BUFFER
						FROM
						(
							SELECT
								-- COALESCE(B.no_faktur,'') AS no_faktur
								-- ,COALESCE(B.sts_penjualan,'') AS sts_penjualan
								A.id_produk
								-- ,ROUND(SUM(A.jumlah) / 90,2) AS RATA_JUMLAH
								,ROUND(SUM(A.jumlah) ,2) AS RATA_JUMLAH
								,A.satuan_jual
								,A.kode_kantor
							FROM tb_d_penjualan AS A 
							INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
							-- WHERE B.sts_penjualan IN ('SELESAI','PEMBAYARAN') AND C.isProduk IN ('PRODUK')
							WHERE B.tgl_h_penjualan BETWEEN DATE_ADD(DATE(NOW()),INTERVAL -60 DAY) AND DATE(NOW()) AND A.id_produk <> ''
							AND B.sts_penjualan IN ('SELESAI','PEMBAYARAN') 
							GROUP BY
								A.id_produk
								,A.satuan_jual
								,A.kode_kantor
						) AS B
						-- WHERE B.kode_kantor = 'CJR'
						-- AND B.isProduk IN ('PRODUK');
					) AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.KANTOR
					
					SET 
						A.jenis_moving = B.JENIS_MOVING
						,A.buf_stock = B.BUFFER
						,A.min_stock = B.BUFFER;
				";
				
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function nominal_stock_percabang()
		{
			$query = 
			"
				SELECT
					AA.TGL
					,ROUND(SUM(AA.stock * AA.harga),0) AS NOMINAL_STOCK
					,AA.kode_kantor
					,AA.nama_kantor
				FROM
				(
					SELECT
						MIN(A.dtstock) AS TGL
						,A.stock
						,COALESCE(B.harga_jual,0) AS harga
						,A.kode_kantor
						,COALESCE(C.nama_kantor,'') AS nama_kantor
					FROM tb_produk AS A
					INNER JOIN tb_satuan_konversi AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk AND B.besar_konversi = 1
					LEFT JOIN tb_kantor AS C ON A.kode_kantor = C.kode_kantor
					-- WHERE A.kode_kantor = 'CJR'
					WHERE B.besar_konversi = 1 AND COALESCE(B.harga_jual,0) > 0
					GROUP BY A.stock,COALESCE(B.harga_jual,0),A.kode_kantor,COALESCE(C.nama_kantor,'')
				) AS AA
				GROUP BY AA.TGL,AA.kode_kantor,AA.nama_kantor
				
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
	}
?>