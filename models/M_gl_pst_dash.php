<?php
	class M_gl_pst_dash extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function ST_KUNJUNGAN($cari,$dari,$sampai)
		{
			$query = 
			"
				SELECT A.kode_kantor,COALESCE(B.KUNJUNGAN ,0) AS KUNJUNGAN
				FROM tb_kantor AS A
				LEFT JOIN
				(
					SELECT
						kode_kantor,COUNT(id_h_penjualan) AS KUNJUNGAN
					FROM tb_h_penjualan
					WHERE sts_penjualan IN ('SELESAI','PEMBAYARAN') 
					AND DATE(tgl_h_penjualan) BETWEEN DATE('".$dari."') AND DATE('".$sampai."')
					GROUP BY kode_kantor
				) AS B ON A.kode_kantor = B.kode_kantor
				".$cari."
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
	
		function list_uang_masuk_perakun_bank()
		{
			$query = 
			"
				SELECT COUNT(A.id_h_penjualan) AS CNT, A.nama_bank,SUM(A.TUNAI) AS TUNAI, SUM(A.NONTUNAI) AS NONTUNAI
				FROM
				(
					SELECT A.kode_kantor,A.id_h_penjualan,A.nama_bank
						,
							CASE WHEN A.nama_bank = '' THEN
								SUM(nominal)
							ELSE
								0
							END AS TUNAI
						,
							CASE WHEN A.nama_bank <> '' THEN
								SUM(nominal)
							ELSE
								0
							END AS NONTUNAI
					FROM
					(
						SELECT
							COALESCE(C.id_d_bayar,'') AS id_d_bayar
							,A.id_h_penjualan
							,COALESCE(C.id_bank,'') AS id_bank
							,
								CASE WHEN COALESCE(C.id_bank,'') <> '' THEN
									CONCAT(COALESCE(B.nama_bank,''),' (',COALESCE(B.norek,''),')')
								ELSE
									'TUNAI'
								END AS nama_bank
							,COALESCE(C.cara,'') AS cara
							,COALESCE(C.nominal,0) AS nominal
							,A.kode_kantor
						FROM 
						tb_h_penjualan AS A
						LEFT JOIN tb_d_penjualan_bayar AS C ON A.id_h_penjualan = C.id_h_penjualan AND A.kode_kantor = C.kode_kantor
						LEFT JOIN tb_bank AS B ON A.kode_kantor = B.kode_kantor AND C.id_bank = B.id_bank
						WHERE COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
						AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) = DATE(NOW())
						AND C.nominal > 0
					) AS A
					GROUP BY A.kode_kantor,A.id_h_penjualan,A.nama_bank
				) AS A
				GROUP BY A.nama_bank;
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
		
		function list_produk_terlaris()
		{
			$query = 
			"
				SELECT
					kode_produk
					,nama_produk
					,SUM(jumlah) AS jumlah
					,satuan_jual
					,harga_setelah_diskon
					,SUM(jumlah) * harga_setelah_diskon AS SUBTOTAL
				FROM
				(
					SELECT 
						AA.* 
						,COALESCE(BB.kode_produk,'') AS kode_produk
						,COALESCE(BB.nama_produk,'') AS nama_produk
					FROM
					(
						SELECT
							kode_kantor,
							id_produk,
							SUM(jumlah) AS jumlah,
							satuan_jual,
							harga,
							harga_setelah_diskon,
							SUM(jumlah) * harga_setelah_diskon AS SUBTOTAL
						FROM
						(
							SELECT
								A.kode_kantor,
								A.id_produk,
								A.jumlah,
								A.satuan_jual,
								A.harga,
								CASE WHEN A.optr_diskon = '%' THEN
									A.harga - ((A.harga/100) * A.diskon)
								ELSE
									A.harga - A.diskon
								END AS harga_setelah_diskon
							FROM tb_d_penjualan AS A
							INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan  = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
							WHERE B.sts_penjualan IN ('SELESAI','PEMBAYARAN')
							AND DATE(COALESCE(B.tgl_h_penjualan,'')) = DATE(NOW())
							AND A.isProduk IN ('JASA','PRODUK')
							-- AND B.no_faktur ='JKT2020081800002'
						) AS AA
						GROUP BY 
							kode_kantor,
							id_produk,
							satuan_jual,
							harga,
							harga_setelah_diskon
					) AS AA
					LEFT JOIN tb_produk AS BB ON AA.id_produk = BB.id_produk AND AA.kode_kantor = BB.kode_kantor
				) AS AAA
				GROUP BY 
					kode_produk
					,nama_produk
					,satuan_jual
					,harga_setelah_diskon
				ORDER BY (SUM(jumlah) * harga_setelah_diskon) DESC
				LIMIT 0,20
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
		
		function list_count_perubahan_transaksi($cari,$dari,$sampai)
		{
			$query = 
			"
				SELECT A.kode_kantor,COALESCE(B.nama_kantor,'') AS nama_kantor,COUNT(A.isApprove) AS CNT_UBAH 
				FROM tb_h_penjualan AS A
				LEFT JOIN tb_kantor AS B ON A.kode_kantor = B.kode_kantor
				WHERE A.sts_penjualan IN ('SELESAI','PEMBAYARAN') 
				AND DATE(A.tgl_h_penjualan) BETWEEN DATE('".$dari."') AND DATE('".$sampai."') 
				AND A.isApprove > 0 ".$cari."
				GROUP BY A.kode_kantor,COALESCE(B.nama_kantor,'')
				ORDER BY COUNT(A.isApprove) DESC;
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
	
		function akumulasi_pendapatan_percabang($cari,$dari,$sampai)
		{
			$query = 
			"
				SELECT COALESCE(B.nama_kantor,'') AS nama_kantor, COUNT(A.id_h_penjualan) AS CNT, SUM(A.NONTUNAI) AS NONTUNAI
				FROM
				(
					SELECT A.kode_kantor,A.id_h_penjualan,A.nama_bank
						,
							CASE WHEN A.nama_bank = '' THEN
								SUM(nominal)
							ELSE
								0
							END AS TUNAI
						,
							CASE WHEN A.nama_bank <> '' THEN
								SUM(nominal)
							ELSE
								0
							END AS NONTUNAI
					FROM
					(
						SELECT
							COALESCE(C.id_d_bayar,'') AS id_d_bayar
							,A.id_h_penjualan
							,COALESCE(C.id_bank,'') AS id_bank
							,
								CASE WHEN COALESCE(C.id_bank,'') <> '' THEN
									CONCAT(COALESCE(B.nama_bank,''),' (',COALESCE(B.norek,''),')')
								ELSE
									'TUNAI'
								END AS nama_bank
							,COALESCE(C.cara,'') AS cara
							,COALESCE(C.nominal,0) AS nominal
							,A.kode_kantor
						FROM 
						tb_h_penjualan AS A
						LEFT JOIN 
						(
							SELECT DISTINCT kode_kantor,id_h_penjualan,id_d_bayar,id_bank,cara,nominal
							FROM tb_d_penjualan_bayar
							GROUP BY kode_kantor,id_h_penjualan,id_d_bayar,id_bank,cara,nominal
						) AS C ON A.id_h_penjualan = C.id_h_penjualan AND A.kode_kantor = C.kode_kantor
						LEFT JOIN 
						(
							SELECT DISTINCT kode_kantor,id_bank,nama_bank,norek
							FROM tb_bank
							GROUP BY kode_kantor,id_bank,nama_bank,norek
						) AS B ON A.kode_kantor = B.kode_kantor AND C.id_bank = B.id_bank
						WHERE COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
						AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN DATE('".$dari."') AND DATE('".$sampai."')
						AND C.nominal > 0
					) AS A
					GROUP BY A.kode_kantor,A.id_h_penjualan,A.nama_bank
				) AS A
				LEFT JOIN tb_kantor AS B ON A.kode_kantor = B.kode_kantor
				".$cari."
				GROUP BY COALESCE(B.nama_kantor,'') ORDER BY SUM(A.NONTUNAI) DESC;
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
		
		function akumulasi_pendapatan_percabang_perbulan($cari)
		{
			$query = 
			"
				SELECT COALESCE(B.nama_kantor,'') AS nama_kantor, COUNT(A.id_h_penjualan) AS CNT, SUM(A.NONTUNAI) AS NONTUNAI
				FROM
				(
					SELECT A.kode_kantor,A.id_h_penjualan,A.nama_bank
						,
							CASE WHEN A.nama_bank = '' THEN
								SUM(nominal)
							ELSE
								0
							END AS TUNAI
						,
							CASE WHEN A.nama_bank <> '' THEN
								SUM(nominal)
							ELSE
								0
							END AS NONTUNAI
					FROM
					(
						SELECT
							COALESCE(C.id_d_bayar,'') AS id_d_bayar
							,A.id_h_penjualan
							,COALESCE(C.id_bank,'') AS id_bank
							,
								CASE WHEN COALESCE(C.id_bank,'') <> '' THEN
									CONCAT(COALESCE(B.nama_bank,''),' (',COALESCE(B.norek,''),')')
								ELSE
									'TUNAI'
								END AS nama_bank
							,COALESCE(C.cara,'') AS cara
							,COALESCE(C.nominal,0) AS nominal
							,A.kode_kantor
						FROM 
						tb_h_penjualan AS A
						LEFT JOIN tb_d_penjualan_bayar AS C ON A.id_h_penjualan = C.id_h_penjualan AND A.kode_kantor = C.kode_kantor
						LEFT JOIN tb_bank AS B ON A.kode_kantor = B.kode_kantor AND C.id_bank = B.id_bank
						WHERE COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
						AND YEAR(COALESCE(A.tgl_h_penjualan,NOW())) = YEAR(NOW())
						AND MONTH(COALESCE(A.tgl_h_penjualan,NOW())) = MONTH(NOW())
						AND C.nominal > 0
					) AS A
					GROUP BY A.kode_kantor,A.id_h_penjualan,A.nama_bank
				) AS A
				LEFT JOIN tb_kantor AS B ON A.kode_kantor = B.kode_kantor
				".$cari."
				GROUP BY COALESCE(B.nama_kantor,'') ORDER BY SUM(A.NONTUNAI) DESC;
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
	
		function produk_terlaris_percabang($cari,$dari,$sampai)
		{
			$query = 
			"
				SELECT * FROM
				(
					SELECT
						AA.kode_kantor
						,MAX(AA.SUBTOTAL) AS SUBTOTAL
					FROM
					(
						SELECT
							kode_kantor
							,kode_produk
							,nama_produk
							,SUM(jumlah) AS jumlah
							,satuan_jual
							,harga_setelah_diskon
							,SUM(jumlah) * harga_setelah_diskon AS SUBTOTAL
						FROM
						(
							SELECT 
								AA.* 
								,COALESCE(BB.kode_produk,'') AS kode_produk
								,COALESCE(BB.nama_produk,'') AS nama_produk
							FROM
							(
								SELECT
									kode_kantor,
									id_produk,
									SUM(jumlah) AS jumlah,
									satuan_jual,
									harga,
									harga_setelah_diskon,
									SUM(jumlah) * harga_setelah_diskon AS SUBTOTAL
								FROM
								(
									SELECT
										A.kode_kantor,
										A.id_produk,
										A.jumlah,
										A.satuan_jual,
										A.harga,
										CASE WHEN A.optr_diskon = '%' THEN
											A.harga - ((A.harga/100) * A.diskon)
										ELSE
											A.harga - A.diskon
										END AS harga_setelah_diskon
									FROM tb_d_penjualan AS A
									INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan  = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
									WHERE B.sts_penjualan IN ('SELESAI','PEMBAYARAN')
									AND DATE(COALESCE(B.tgl_h_penjualan,'')) BETWEEN DATE('".$dari."') AND DATE('".$sampai."')
									-- AND A.isProduk IN ('JASA','PRODUK')
									AND A.isProduk IN ('PRODUK')
								) AS AA
								GROUP BY 
									kode_kantor,
									id_produk,
									satuan_jual,
									harga,
									harga_setelah_diskon
							) AS AA
							LEFT JOIN tb_produk AS BB ON AA.id_produk = BB.id_produk AND AA.kode_kantor = BB.kode_kantor
						) AS AAA
						GROUP BY 
							kode_kantor
							,kode_produk
							,nama_produk
							,satuan_jual
							,harga_setelah_diskon
					) AS AA
					GROUP BY 
						AA.kode_kantor
				) AS A
				LEFT JOIN
				(
					SELECT
						AA.kode_kantor
						,AA.kode_produk
						,AA.nama_produk
						,MAX(AA.SUBTOTAL) AS SUBTOTAL
					FROM
					(
						SELECT
							kode_kantor
							,kode_produk
							,nama_produk
							,SUM(jumlah) AS jumlah
							,satuan_jual
							,harga_setelah_diskon
							,SUM(jumlah) * harga_setelah_diskon AS SUBTOTAL
						FROM
						(
							SELECT 
								AA.* 
								,COALESCE(BB.kode_produk,'') AS kode_produk
								,COALESCE(BB.nama_produk,'') AS nama_produk
							FROM
							(
								SELECT
									kode_kantor,
									id_produk,
									SUM(jumlah) AS jumlah,
									satuan_jual,
									harga,
									harga_setelah_diskon,
									SUM(jumlah) * harga_setelah_diskon AS SUBTOTAL
								FROM
								(
									SELECT
										A.kode_kantor,
										A.id_produk,
										A.jumlah,
										A.satuan_jual,
										A.harga,
										CASE WHEN A.optr_diskon = '%' THEN
											A.harga - ((A.harga/100) * A.diskon)
										ELSE
											A.harga - A.diskon
										END AS harga_setelah_diskon
									FROM tb_d_penjualan AS A
									INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan  = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
									WHERE B.sts_penjualan IN ('SELESAI','PEMBAYARAN')
									AND DATE(COALESCE(B.tgl_h_penjualan,'')) BETWEEN DATE('".$dari."') AND DATE('".$sampai."')
									-- AND A.isProduk IN ('JASA','PRODUK')
									AND A.isProduk IN ('PRODUK')
								) AS AA
								GROUP BY 
									kode_kantor,
									id_produk,
									satuan_jual,
									harga,
									harga_setelah_diskon
							) AS AA
							LEFT JOIN tb_produk AS BB ON AA.id_produk = BB.id_produk AND AA.kode_kantor = BB.kode_kantor
						) AS AAA
						GROUP BY 
							kode_kantor
							,kode_produk
							,nama_produk
							,satuan_jual
							,harga_setelah_diskon
					) AS AA
					GROUP BY 
						AA.kode_kantor
						,AA.kode_produk
						,AA.nama_produk
				) AS B ON A.kode_kantor = B.kode_kantor AND A.SUBTOTAL = B.SUBTOTAL
				LEFT JOIN tb_kantor AS C ON A.kode_kantor = C.kode_kantor
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