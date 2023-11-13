<?php
	class M_gl_lap_pembelian extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_hutang_vipot($kode_kantor,$cari,$tgl_dari,$tgl_sampai)
		{
			//$query = $this->db->query("CALL SP_VIEW_HUTANG_2('". $kode_kantor ."','". $cari ."','". $tgl_dari ."','". $tgl_sampai ."')");
			
			//DIGANTI KARENA HUTANG DI AMBIL DARI SJ/INVOICE 2021-08-08
			$query = $this->db->query("CALL SP_VIEW_HUTANG_2_BY_INV('". $kode_kantor ."','". $cari ."','". $tgl_dari ."','". $tgl_sampai ."')");
			
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_pemesanan_dari_cabang_lain($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT
					DISTINCT
					A.id_d_pembelian
					,A.id_h_pembelian
					,A.id_produk
					,A.jumlah
					,A.harga
					,A.harga_dasar
					,A.diskon
					,A.optr_diskon
					,A.kode_satuan
					,A.status_konversi
					,A.konversi
					,
					CASE 
					WHEN A.optr_diskon = '%' THEN
						A.harga + ((A.harga/100) * A.diskon)
					ELSE
						A.harga + A.diskon
					END AS HARGA_FIX
						
					,COALESCE(B.no_h_pembelian,'') AS no_h_pembelian
					,COALESCE(B.ket_h_pembelian,'') AS ket_h_pembelian
					,DATE(COALESCE(B.tgl_h_pembelian,'')) AS tgl_h_pembelian
					,COALESCE(C.kode_produk,'') AS kode_produk
					,COALESCE(C.nama_produk,'') AS nama_produk
					,COALESCE(D.kode_supplier,'') AS kode_supplier
					,COALESCE(D.nama_supplier,'') AS nama_supplier
					,COALESCE(E.kode_kantor,'') AS kode_kantor_pemesan
					,COALESCE(E.nama_kantor,'') AS nama_kantor
					,COALESCE(E.tlp,'') AS tlp_kantor
					,COALESCE(E.alamat,'') AS alamat_kantor
					,COALESCE(F.no_faktur,'') AS no_faktur
					,CASE WHEN F.no_faktur IS NULL THEN
						0
					ELSE
						1
					END AS isProcess
				FROM tb_d_pembelian AS A
				INNER JOIN tb_h_pembelian AS B ON A.id_h_pembelian = B.id_h_pembelian
				LEFT JOIN tb_produk AS C ON A.id_produk = C.id_produk
				LEFT JOIN tb_supplier AS D ON B.id_supplier = D.id_supplier
				LEFT JOIN tb_kantor AS E ON A.kode_kantor = E.kode_kantor
				LEFT JOIN tb_h_penjualan AS F ON B.no_h_pembelian = F.ket_penjualan AND F.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
				-- AND F.sts_penjualan = 'SELESAI'
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
		
		function count_pemesanan_dari_cabang_lain($cari)
		{
			$query =
			"
				SELECT
					COUNT(A.id_d_pembelian) AS JUMLAH
				FROM tb_d_pembelian AS A
				INNER JOIN tb_h_pembelian AS B ON A.id_h_pembelian = B.id_h_pembelian
				LEFT JOIN tb_produk AS C ON A.id_produk = C.id_produk
				LEFT JOIN tb_supplier AS D ON B.id_supplier = D.id_supplier
				LEFT JOIN tb_kantor AS E ON A.kode_kantor = E.kode_kantor
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
		
		function list_lap_h_pembelian($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT
					A.*
					,COALESCE(D.kode_supplier,'') AS kode_supplier
					,COALESCE(D.nama_supplier,'') AS nama_supplier
					,COALESCE(D.alamat,'') AS alamat_supplier
					,COALESCE(B.SUBTOTAL,0) AS SUBTOTAL
					,COALESCE(C.BAYAR,0) AS BAYAR
					,COALESCE(E.no_faktur,'') AS NO_FAKTUR
					,COALESCE(F.no_surat_jalan,'') AS no_surat_jalan
				FROM
				tb_h_pembelian AS A
				LEFT JOIN
				(
				
					SELECT 
						kode_kantor
						,id_h_pembelian
						,COUNT(id_h_pembelian) AS JUM
						,SUM(acc) AS JUMACC
						,SUM(jumlah) AS JUMITEM
						,SUM(jumlah * (harga_fix - diskon_fix)) AS SUBTOTAL
					FROM
					(
						SELECT
							*
							,CASE WHEN optr_diskon = '%' THEN
								(harga/100) * diskon
							ELSE
								diskon
							END AS diskon_fix
							,ROUND(harga,0) AS harga_fix
						FROM tb_d_pembelian 
						-- WHERE id_h_pembelian = 'ONPST2020072200003';
					) AS A
					GROUP BY id_h_pembelian,kode_kantor
				
					/*
					SELECT kode_kantor,id_h_pembelian,COUNT(id_h_pembelian) AS JUM, SUM(acc) AS JUMACC,SUM(jumlah) AS JUMITEM, SUM(jumlah * harga) AS SUBTOTAL
					From tb_d_pembelian
					GROUP BY kode_kantor,id_h_pembelian
					*/
				) AS B ON A.id_h_pembelian = B.id_h_pembelian AND A.kode_kantor = B.kode_kantor
				LEFT JOIN
				(
					SELECT kode_kantor,id_h_pembelian,SUM(nominal) AS BAYAR
					FROM tb_d_pembelian_bayar
					GROUP BY kode_kantor,id_h_pembelian
				) AS C ON A.id_h_pembelian = C.id_h_pembelian AND A.kode_kantor = C.kode_kantor
				LEFT JOIN tb_supplier AS D ON A.id_supplier = D.id_supplier AND A.kode_kantor = D.kode_kantor
				LEFT JOIN 
				(
					SELECT DISTINCT no_faktur,ket_penjualan
					FROM tb_h_penjualan
					GROUP BY no_faktur,ket_penjualan
				) AS E ON A.no_h_pembelian = E.ket_penjualan
				LEFT JOIN
				(
					SELECT DISTINCT kode_kantor,id_h_pembelian,MAX(no_surat_jalan) AS no_surat_jalan
					FROM tb_h_penerimaan
					GROUP BY kode_kantor,id_h_pembelian
				) AS F ON A.kode_kantor = F.kode_kantor AND A.id_h_pembelian = F.id_h_pembelian
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
		
		function count_lap_h_pembelian_limit($cari)
		{
			$query =
			"
				SELECT
					COUNT(A.id_h_pembelian) AS JUMLAH
				FROM
				tb_h_pembelian AS A
				LEFT JOIN tb_supplier AS D ON A.id_supplier = D.id_supplier AND A.kode_kantor = D.kode_kantor
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
		
		function sum_lap_h_pembelian($cari)
		{
			$query =
			"
				SELECT
					SUM(COALESCE(B.SUBTOTAL,0)) AS SUBTOTAL
					,SUM(COALESCE(C.BAYAR,0)) AS BAYAR
				FROM
				tb_h_pembelian AS A
				LEFT JOIN
				(
					
					SELECT kode_kantor,id_h_pembelian,COUNT(id_h_pembelian) AS JUM, SUM(acc) AS JUMACC,SUM(jumlah) AS JUMITEM, SUM(jumlah * (harga_fix - diskon_fix)) AS SUBTOTAL
					FROM
					(
						SELECT
							*
							,CASE WHEN optr_diskon = '*' THEN
								(harga/100) * diskon
							ELSE
								diskon
							END AS diskon_fix
							,ROUND(harga,0) AS harga_fix
						FROM tb_d_pembelian 
						-- WHERE id_h_pembelian = 'ONPST2020072200003';
					) AS A
					GROUP BY id_h_pembelian,kode_kantor
				
					/*
					SELECT kode_kantor,id_h_pembelian,COUNT(id_h_pembelian) AS JUM, SUM(acc) AS JUMACC,SUM(jumlah) AS JUMITEM, SUM(jumlah * harga) AS SUBTOTAL
					From tb_d_pembelian
					GROUP BY kode_kantor,id_h_pembelian
					*/
				) AS B ON A.id_h_pembelian = B.id_h_pembelian AND A.kode_kantor = B.kode_kantor
				LEFT JOIN
				(
					SELECT kode_kantor,id_h_pembelian,SUM(nominal) AS BAYAR
					FROM tb_d_pembelian_bayar
					GROUP BY kode_kantor,id_h_pembelian
				) AS C ON A.id_h_pembelian = C.id_h_pembelian AND A.kode_kantor = C.kode_kantor
				LEFT JOIN tb_supplier AS D ON A.id_supplier = D.id_supplier AND A.kode_kantor = D.kode_kantor
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
	
		function list_lap_d_pembelian_with_penerimaan($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT
					A.*
					,
						CASE 
						WHEN optr_diskon = '%' THEN
							harga + ((harga/100) * diskon)
						ELSE
							harga + diskon
						END AS HARGA_FIX
					,COALESCE(B.no_h_pembelian,'') AS no_h_pembelian
					,DATE(COALESCE(B.tgl_h_pembelian,'')) AS tgl_h_pembelian
					,COALESCE(C.kode_produk,'') AS kode_produk
					,COALESCE(C.nama_produk,'') AS nama_produk
					,COALESCE(D.TERIMA,0) AS TERIMA
					-- ,COALESCE(E.kode_supplier,'') AS kode_supplier
					-- ,COALESCE(E.nama_supplier,'') AS nama_supplier
				FROM tb_d_pembelian AS A
				INNER JOIN tb_h_pembelian AS B ON A.id_h_pembelian = B.id_h_pembelian AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
				LEFT JOIN
				(
					SELECT 
						A.kode_kantor
						,COALESCE(B.id_h_pembelian,'') AS id_h_pembelian
						,id_produk
						,SUM(diterima_satuan_beli) AS TERIMA
					FROM tb_d_penerimaan AS A 
					INNER JOIN tb_h_penerimaan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penerimaan = B.id_h_penerimaan
					GROUP BY A.kode_kantor,id_produk,COALESCE(B.id_h_pembelian,'')
				) AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_pembelian = D.id_h_pembelian AND A.id_produk = D.id_produk
				-- LEFT JOIN tb_supplier AS E ON A.kode_kantor = E.kode_kantor AND B.id_supplier = E.id_supplier
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
		
		
		function list_lap_d_pembelian_with_penerimaan_dipakai_laporan($cari,$order_by,$limit,$offset)
		{
			//BERDASARKAN PENERIMAAN
			/*
			$query =
			"
				SELECT
					A.*
					,COALESCE(C.jumlah,'0') AS JUM_BELI
					,COALESCE(C.kode_satuan,'0') AS STN_BELI
					,COALESCE(D.no_h_pembelian,'') AS NO_PO
					,COALESCE(D.tgl_h_pembelian,'') AS TGL_PO
					,COALESCE(E.kode_supplier,'') AS kode_supplier
					,COALESCE(E.nama_supplier,'') AS nama_supplier
					,COALESCE(F.kode_produk,'') AS kode_produk
					,COALESCE(F.nama_produk,'') AS nama_produk
				FROM
				(
					SELECT 
						A.kode_kantor
						,A.id_d_penerimaan
						,A.id_d_pembelian
						,A.id_h_pembelian
						,A.id_produk
						,SUM(diterima_satuan_beli) AS TERIMA
						,B.tgl_terima
						,B.no_surat_jalan
						,A.tgl_ins
					FROM tb_d_penerimaan AS A 
					INNER JOIN tb_h_penerimaan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penerimaan = B.id_h_penerimaan
					GROUP BY A.kode_kantor,A.id_d_penerimaan,A.id_d_pembelian,A.id_h_pembelian,A.id_produk,B.tgl_terima,B.no_surat_jalan,A.tgl_ins
				) AS A
				INNER JOIN tb_d_pembelian AS C ON A.kode_kantor = C.kode_kantor AND A.id_d_pembelian = C.id_d_pembelian
				INNER JOIN tb_h_pembelian AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_pembelian = D.id_h_pembelian
				LEFT JOIN tb_supplier AS E ON A.kode_kantor = E.kode_kantor AND D.id_supplier = E.id_supplier
				LEFT JOIN tb_produk AS F ON A.kode_kantor = F.kode_kantor AND A.id_produk = F.id_produk
				".$cari."
				".$order_by."
				LIMIT ".$offset.",".$limit."
			";
			*/
			//BERDASARKAN PENERIMAAN
			
			//BERDASARKAN YANG BELUM DI TERIMA
			$query =
			"
				SELECT
					A.*
					,
						CASE 
						WHEN optr_diskon = '%' THEN
							harga + ((harga/100) * diskon)
						ELSE
							harga + diskon
						END AS HARGA_FIX
					,COALESCE(B.no_h_pembelian,'') AS no_h_pembelian
					,DATE(COALESCE(B.tgl_h_pembelian,'')) AS tgl_h_pembelian
					,COALESCE(C.kode_produk,'') AS kode_produk
					,COALESCE(C.nama_produk,'') AS nama_produk
					,COALESCE(D.TERIMA,0) AS TERIMA
					,COALESCE(E.kode_supplier,'') AS kode_supplier
					,COALESCE(E.nama_supplier,'') AS nama_supplier
				FROM tb_d_pembelian AS A
				INNER JOIN 
				(
					SELECT DISTINCT kode_kantor,id_h_pembelian,id_supplier,no_h_pembelian,tgl_h_pembelian,sts_pembelian
					FROM tb_h_pembelian
					GROUP BY kode_kantor,id_h_pembelian,id_supplier,no_h_pembelian,tgl_h_pembelian,sts_pembelian
				) AS B ON A.id_h_pembelian = B.id_h_pembelian AND A.kode_kantor = B.kode_kantor
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_produk,kode_produk,nama_produk
					FROM tb_produk
					GROUP BY kode_kantor,id_produk,kode_produk,nama_produk
				) AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
				LEFT JOIN
				(
					SELECT 
						A.kode_kantor
						,COALESCE(B.id_h_pembelian,'') AS id_h_pembelian
						,id_produk
						,SUM(diterima_satuan_beli) AS TERIMA
					FROM tb_d_penerimaan AS A 
					INNER JOIN 
					(
						SELECT DISTINCT kode_kantor,id_h_penerimaan,id_h_pembelian
						FROM tb_h_penerimaan
						GROUP BY kode_kantor,id_h_penerimaan,id_h_pembelian
					) AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penerimaan = B.id_h_penerimaan
					GROUP BY A.kode_kantor,id_produk,COALESCE(B.id_h_pembelian,'')
				) AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_pembelian = D.id_h_pembelian AND A.id_produk = D.id_produk
				LEFT JOIN tb_supplier AS E ON A.kode_kantor = E.kode_kantor AND B.id_supplier = E.id_supplier
				".$cari."
				".$order_by."
				LIMIT ".$offset.",".$limit."
			";
			//BERDASARKAN YANG BELUM DI TERIMA
			
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
		
		
		function count_lap_d_pembelian_with_penerimaan($cari)
		{
			$query =
			"
				SELECT
					COUNT(A.id_d_pembelian) AS JUMLAH
				FROM tb_d_pembelian AS A
				INNER JOIN tb_h_pembelian AS B ON A.id_h_pembelian = B.id_h_pembelian AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
				LEFT JOIN
				(
					SELECT 
						A.kode_kantor
						,COALESCE(B.id_h_pembelian,'') AS id_h_pembelian
						,id_produk
						,SUM(diterima_satuan_beli) AS TERIMA
					FROM tb_d_penerimaan AS A 
					INNER JOIN tb_h_penerimaan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penerimaan = B.id_h_penerimaan
					GROUP BY A.kode_kantor,id_produk,COALESCE(B.id_h_pembelian,'')
				) AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_pembelian = D.id_h_pembelian AND A.id_produk = D.id_produk
				LEFT JOIN tb_supplier AS E ON A.kode_kantor = E.kode_kantor AND B.id_supplier = E.id_supplier
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
		
		function list_lap_d_pembelian($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT
					A.*
					,
					CASE 
					WHEN optr_diskon = '%' THEN
						harga - ((harga/100) * diskon)
					ELSE
						harga - diskon
					END AS HARGA_FIX
						
					,COALESCE(B.no_h_pembelian,'') AS no_h_pembelian
					,DATE(COALESCE(B.tgl_h_pembelian,'')) AS tgl_h_pembelian
					,COALESCE(C.kode_produk,'') AS kode_produk
					,COALESCE(C.nama_produk,'') AS nama_produk
					,COALESCE(D.kode_supplier,'') AS kode_supplier
					,COALESCE(D.nama_supplier,'') AS nama_supplier
				FROM tb_d_pembelian AS A
				INNER JOIN tb_h_pembelian AS B ON A.id_h_pembelian = B.id_h_pembelian AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
				LEFT JOIN tb_supplier AS D ON A.kode_kantor = D.kode_kantor AND B.id_supplier = D.id_supplier
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
		
		function count_lap_d_pembelian($cari)
		{
			$query =
			"
				SELECT COUNT(A.id_d_pembelian) AS JUMLAH
				FROM tb_d_pembelian AS A
				INNER JOIN tb_h_pembelian AS B ON A.id_h_pembelian = B.id_h_pembelian AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
				LEFT JOIN tb_supplier AS D ON A.kode_kantor = D.kode_kantor AND B.id_supplier = D.id_supplier
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
	
		function get_h_pembelian($cari)
		{
			$query =
			"
				SELECT * FROM tb_h_pembelian ".$cari."
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
		
		function get_h_pembelian_with_supplier($cari)
		{
			$query =
			"
				SELECT 
					A.*
					,COALESCE(B.kode_supplier,'') AS kode_supplier
					,COALESCE(B.nama_supplier,'') AS nama_supplier
				FROM tb_h_pembelian AS A 
				LEFT JOIN tb_supplier AS B 
				ON A.kode_kantor = B.kode_kantor AND A.id_supplier = B.id_supplier 
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
		
		function hapus($table,$cari)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM ".$table." ".$cari." ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function list_d_pembelian_bayar_limit_for_inv($cari,$order_by)
		{
			$query = 
				"
					SELECT
						A.*
						,COALESCE(B.norek,'') AS NOREK
						,COALESCE(B.nama_bank,'') AS NAMA_BANK
						,COALESCE(B.atas_nama,'') AS ATAS_NAMA
						,COALESCE(B.cabang,'') AS CABANG
					FROM tb_d_pembelian_bayar AS A
					LEFT JOIN tb_bank AS B ON A.kode_kantor = B.kode_kantor AND A.id_bank = B.id_bank
					".$cari."
					".$order_by."
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
		
		function list_d_pembelian_bayar_limit($cari,$order_by)
		{
			$query = 
				"
					SELECT
						A.*
						,COALESCE(B.norek,'') AS NOREK
						,COALESCE(B.nama_bank,'') AS NAMA_BANK
						,COALESCE(B.atas_nama,'') AS ATAS_NAMA
						,COALESCE(B.cabang,'') AS CABANG
					FROM tb_d_pembelian_bayar AS A
					LEFT JOIN tb_bank AS B ON A.kode_kantor = B.kode_kantor AND A.id_bank = B.id_bank
					".$cari."
					".$order_by."
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
	
		function simpan_d_pembelian_bayar
		(
			$id_h_pembelian,
			$id_supplier,
			$id_bank,
			$id_retur_pembelian,
			$cara,
			$norek,
			$nama_bank,
			$atas_nama,
			$nominal,
			$ket,
			$type_bayar,
			$id_pembayaran_supplier,
			$isTabungan,
			$tgl_bayar,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_d_pembelian_bayar
				(
					id_d_bayar,
					id_h_pembelian,
					id_supplier,
					id_bank,
					id_retur_pembelian,
					cara,
					norek,
					nama_bank,
					atas_nama,
					nominal,
					ket,
					type_bayar,
					id_pembayaran_supplier,
					isTabungan,
					tgl_bayar,
					tgl_ins,
					tgl_updt,
					user_updt,
					user_ins,
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
								From tb_d_pembelian_bayar
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_h_pembelian."',
					'".$id_supplier."',
					'".$id_bank."',
					'".$id_retur_pembelian."',
					'".$cara."',
					'".$norek."',
					'".$nama_bank."',
					'".$atas_nama."',
					'".$nominal."',
					'".$ket."',
					'".$type_bayar."',
					'".$id_pembayaran_supplier."',
					'".$isTabungan."',
					'".$tgl_bayar."',
					NOW(),
					NOW(),
					'".$user_updt."',
					'',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_d_pembelian_bayar_by_inv
		(
			$id_h_pembelian,
			$id_h_penerimaan,
			$id_supplier,
			$id_bank,
			$id_retur_pembelian,
			$cara,
			$norek,
			$nama_bank,
			$atas_nama,
			$nominal,
			$ket,
			$type_bayar,
			$id_pembayaran_supplier,
			$isTabungan,
			$tgl_bayar,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_d_pembelian_bayar
				(
					id_d_bayar,
					id_h_pembelian,
					id_h_penerimaan,
					id_supplier,
					id_bank,
					id_retur_pembelian,
					cara,
					norek,
					nama_bank,
					atas_nama,
					nominal,
					ket,
					type_bayar,
					id_pembayaran_supplier,
					isTabungan,
					tgl_bayar,
					tgl_ins,
					tgl_updt,
					user_updt,
					user_ins,
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
								From tb_d_pembelian_bayar
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_h_pembelian."',
					'".$id_h_penerimaan."',
					'".$id_supplier."',
					'".$id_bank."',
					'".$id_retur_pembelian."',
					'".$cara."',
					'".$norek."',
					'".$nama_bank."',
					'".$atas_nama."',
					'".$nominal."',
					'".$ket."',
					'".$type_bayar."',
					'".$id_pembayaran_supplier."',
					'".$isTabungan."',
					'".$tgl_bayar."',
					NOW(),
					NOW(),
					'".$user_updt."',
					'',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_d_pembelian_bayar
		(
			$id_d_bayar,
			$id_h_pembelian,
			$id_supplier,
			$id_bank,
			$id_retur_pembelian,
			$cara,
			$norek,
			$nama_bank,
			$atas_nama,
			$nominal,
			$ket,
			$type_bayar,
			$id_pembayaran_supplier,
			$isTabungan,
			$tgl_bayar,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_d_pembelian_bayar SET
						
						id_h_pembelian = '".$id_h_pembelian."',
						id_supplier = '".$id_supplier."',
						id_bank = '".$id_bank."',
						id_retur_pembelian = '".$id_retur_pembelian."',
						cara = '".$cara."',
						norek = '".$norek."',
						nama_bank = '".$nama_bank."',
						atas_nama = '".$atas_nama."',
						nominal = '".$nominal."',
						ket = '".$ket."',
						type_bayar = '".$type_bayar."',
						id_pembayaran_supplier = '".$id_pembayaran_supplier."',
						isTabungan = '".$isTabungan."',
						tgl_bayar = '".$tgl_bayar."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_d_bayar = '".$id_d_bayar
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_d_pembelian_bayar_by_inv
		(
			$id_d_bayar,
			$id_h_pembelian,
			$id_h_penerimaan,
			$id_supplier,
			$id_bank,
			$id_retur_pembelian,
			$cara,
			$norek,
			$nama_bank,
			$atas_nama,
			$nominal,
			$ket,
			$type_bayar,
			$id_pembayaran_supplier,
			$isTabungan,
			$tgl_bayar,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_d_pembelian_bayar SET
						
						id_h_pembelian = '".$id_h_pembelian."',
						id_h_penerimaan = '".$id_h_penerimaan."',
						id_supplier = '".$id_supplier."',
						id_bank = '".$id_bank."',
						id_retur_pembelian = '".$id_retur_pembelian."',
						cara = '".$cara."',
						norek = '".$norek."',
						nama_bank = '".$nama_bank."',
						atas_nama = '".$atas_nama."',
						nominal = '".$nominal."',
						ket = '".$ket."',
						type_bayar = '".$type_bayar."',
						id_pembayaran_supplier = '".$id_pembayaran_supplier."',
						isTabungan = '".$isTabungan."',
						tgl_bayar = '".$tgl_bayar."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_d_bayar = '".$id_d_bayar
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_d_pembelian_bayar($berdasarkan,$id_d_bayar)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_d_pembelian_bayar WHERE ".$berdasarkan." = '".$id_d_bayar."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_d_pembelian_bayar_by_inv($cari)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_d_pembelian_bayar ".$cari." ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
	}
?>