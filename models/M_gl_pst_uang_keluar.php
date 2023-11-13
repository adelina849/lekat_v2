<?php
	class M_gl_pst_uang_keluar extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function get_no_uang_keluar_jurnal_umum($kode_kantor)
		{
			$query="
					SELECT CONCAT(ORD,'/JU/','/',D,'/',M,'/',Y,'/".$kode_kantor."') AS no_uang_keluar
					From
					(
						SELECT CONCAT(Y,M,D) AS FRMTGL
							,Y
							,M
							,D
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
							-- COALESCE(MAX(CAST(RIGHT(no_uang_keluar,5) AS UNSIGNED)) + 1,1) AS ORD
							COALESCE(MAX(CAST(LEFT(no_uang_keluar,5) AS UNSIGNED)) + 1,1) AS ORD
							From tb_uang_keluar
							WHERE DATE(tgl_ins) = DATE(NOW())
							AND kode_kantor = '".$kode_kantor."'
						) AS A
					) AS AA
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
		
		function get_no_uang_keluar($kode_kantor)
		{
			$query="
					SELECT CONCAT(ORD,'/UK/','/',D,'/',M,'/',Y,'/".$kode_kantor."') AS no_uang_keluar
					From
					(
						SELECT CONCAT(Y,M,D) AS FRMTGL
							,Y
							,M
							,D
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
							-- COALESCE(MAX(CAST(RIGHT(no_uang_keluar,5) AS UNSIGNED)) + 1,1) AS ORD
							COALESCE(MAX(CAST(LEFT(no_uang_keluar,5) AS UNSIGNED)) + 1,1) AS ORD
							From tb_uang_keluar
							WHERE DATE(tgl_ins) = DATE(NOW())
							AND kode_kantor = '".$kode_kantor."'
						) AS A
					) AS AA
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
		
		function list_hutang_by_supp_uang_masuk_limit($kode_kantor,$id_supplier,$id_induk_uang_keluar)
		{
			$query =
				"
					SELECT 
						A.id_h_penerimaan,
						A.id_h_pembelian,
						A.id_gedung,
						A.no_surat_jalan,
						A.nama_pengirim,
						A.cara_pengiriman,
						A.diterima_oleh,
						A.biaya_kirim,
						A.tgl_kirim,
						DATE(A.tgl_terima) AS TGL_T,
						A.tgl_terima,
						A.ket_h_penerimaan,
						A.tgl_ins,
						A.tgl_updt,
						A.user_updt,
						A.user_ins,
						A.kode_kantor,
						COALESCE(D.no_h_pembelian,0) AS NO_PO,
						COALESCE(C.JUMLAH,0) AS JUMLAH,
						( COALESCE(C.NOMINAL,0) +  A.biaya_kirim) AS NOMINAL,
						
						-- COALESCE(F.BAYAR,0) AS BAYAR,
						-- COALESCE(C.NOMINAL,0) - COALESCE(F.BAYAR,0) AS SISA,
						
						( COALESCE(F.NOMINAL,0) + (COALESCE(I.BAYAR,0)) ) AS TLH_BAYAR,
						COALESCE(H.NOMINAL,0) AS CUR_BAYAR,
						( COALESCE(C.NOMINAL,0) +  A.biaya_kirim) - ( 
													( COALESCE(F.NOMINAL,0) + (COALESCE(I.BAYAR,0)) ) 
													+ COALESCE(H.NOMINAL,0)
												) AS SISA,
						
						COALESCE(F.BIAYA,0) AS BIAYA,
						COALESCE(F.PENDAPATAN,0) AS PENDAPATAN,
						
						COALESCE(H.BIAYA,0) AS CUR_BIAYA,
						COALESCE(H.PENDAPATAN,0) AS CUR_PENDAPATAN,
						
						COALESCE(E.id_supplier,'') AS id_supplier,
						COALESCE(E.kode_supplier,'') AS kode_supplier,
						COALESCE(E.nama_supplier,'') AS nama_supplier,
						
						COALESCE(G.id_kode_akun,'') AS id_kode_akun,
						COALESCE(G.kode_akun,'') AS nama_supplier
						
					FROM tb_h_penerimaan AS A
					LEFT JOIN
					(
						SELECT 
							id_h_penerimaan
							,kode_kantor
							,COUNT(id_produk) AS JUMLAH
							,SUM(diterima * harga_beli) AS NOMINAL
						FROM tb_d_penerimaan
						GROUP BY id_h_penerimaan,kode_kantor
					) AS C ON A.id_h_penerimaan = C.id_h_penerimaan AND A.kode_kantor = C.kode_kantor
					LEFT JOIN tb_h_pembelian AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_pembelian = D.id_h_pembelian
					LEFT JOIN tb_supplier AS E ON A.kode_kantor = E.kode_kantor AND D.id_supplier = E.id_supplier
					LEFT JOIN tb_kode_akun AS G ON A.kode_kantor = G.kode_kantor AND D.id_supplier = G.id_supplier AND G.target = 'PEMBELIAN'

					LEFT JOIN
					(
						-- UNTUK TELAH BAYAR
						-- SELECT kode_kantor,id_supplier,id_h_penerimaan,id_induk_uang_keluar,SUM(nominal) AS NOMINAL, MAX(biaya) AS BIAYA, MAX(pendapatan) AS PENDAPATAN
						SELECT kode_kantor,id_supplier,id_h_penerimaan,SUM(nominal) AS NOMINAL, MAX(biaya) AS BIAYA, MAX(pendapatan) AS PENDAPATAN
						FROM tb_uang_keluar
						WHERE id_induk_uang_keluar <> '".$id_induk_uang_keluar."'
						GROUP BY kode_kantor,id_supplier,id_h_penerimaan
						-- GROUP BY kode_kantor,id_supplier,id_h_penerimaan,id_induk_uang_keluar
						-- UNTUK TELAH BAYAR
					) AS F ON A.kode_kantor = F.kode_kantor AND A.id_h_penerimaan = F.id_h_penerimaan

					LEFT JOIN
					(
						-- CURENT BAYAR
						SELECT kode_kantor,id_supplier,id_h_penerimaan,id_induk_uang_keluar,SUM(nominal) AS NOMINAL, MAX(biaya) AS BIAYA, MAX(pendapatan) AS PENDAPATAN
						FROM tb_uang_keluar
						WHERE id_induk_uang_keluar = '".$id_induk_uang_keluar."'
						GROUP BY kode_kantor,id_supplier,id_h_penerimaan,id_induk_uang_keluar
						-- CURENT BAYAR
					) AS H ON A.kode_kantor = H.kode_kantor AND A.id_h_penerimaan = H.id_h_penerimaan

					-- AMBIL DARI JURNAL UMUM
					LEFT JOIN
					(
						SELECT 
							kode_kantor
							,id_supplier
							,id_h_penerimaan
							,GROUP_CONCAT(no_bukti SEPARATOR ', ') AS NO_BUKTI
							,SUM(debit) AS BAYAR
						FROM tb_jurnal_umum
						GROUP BY kode_kantor,id_supplier,id_h_penerimaan
					) AS I ON A.kode_kantor = I.kode_kantor AND A.id_h_penerimaan = I.id_h_penerimaan
					-- AMBIL DARI JURNAL UMUM
					
					/*
					LEFT JOIN
					(
						SELECT 
							kode_kantor
							,id_h_penerimaan
							,SUM(nominal) AS BAYAR
						FROM tb_d_pembelian_bayar
						GROUP BY kode_kantor,id_h_penerimaan
					) AS F ON A.kode_kantor = F.kode_kantor AND A.id_h_penerimaan = F.id_h_penerimaan
					*/

					WHERE 
						A.kode_kantor = '".$kode_kantor."' 
						AND E.id_supplier = '".$id_supplier."' 
						-- AND COALESCE(C.NOMINAL,0) - (COALESCE(F.NOMINAL,0) + COALESCE(H.NOMINAL,0)) > 0
						AND COALESCE(C.NOMINAL,0) - ( 
													( COALESCE(F.NOMINAL,0) + (COALESCE(I.BAYAR,0)) ) 
													+ COALESCE(H.NOMINAL,0)
												) > 0
						-- AND COALESCE(E.nama_supplier,'') LIKE '%IMORTAL%'
					GROUP BY A.id_h_penerimaan,
						A.id_h_pembelian,
						A.id_gedung,
						A.no_surat_jalan,
						A.nama_pengirim,
						A.cara_pengiriman,
						A.diterima_oleh,
						A.biaya_kirim,
						A.tgl_kirim,
						A.tgl_terima,
						A.ket_h_penerimaan,
						A.tgl_ins,
						A.tgl_updt,
						A.user_updt,
						A.user_ins,
						A.kode_kantor,
						COALESCE(D.no_h_pembelian,''),
						COALESCE(C.JUMLAH,0),
						COALESCE(C.NOMINAL,0),
						-- COALESCE(F.BAYAR,0),
						COALESCE(E.id_supplier,''),
						COALESCE(E.kode_supplier,''),
						COALESCE(E.nama_supplier,''),
						COALESCE(G.id_kode_akun,''),
						COALESCE(G.kode_akun,''),
						COALESCE(F.NOMINAL,0),
						COALESCE(F.BIAYA,0),
						COALESCE(F.PENDAPATAN,0) ,
						COALESCE(H.NOMINAL,0),
						COALESCE(H.BIAYA,0),
						COALESCE(H.PENDAPATAN,0)
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
		
		function list_uang_keluar_limit($cari,$order_by,$limit,$offset)
		{
			$query =
				"
					SELECT
						A.*
						,COALESCE(B.kode_akun,'') AS KODE_AKUN
						,COALESCE(B.nama_kode_akun,'') AS NAMA_AKUN
						,COALESCE(C.norek,'') AS NOREK
						,COALESCE(C.nama_bank,'') AS NAMA_BANK
						,COALESCE(C.atas_nama,'') AS ATAS_NAMA
						,COALESCE(D.kode_akun,'') AS KODE_AKUN2
						,COALESCE(D.nama_kode_akun,'') AS NAMA_AKUN2
					FROM tb_uang_keluar AS A
					LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_keluar = B.id_kode_akun
					LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
					LEFT JOIN tb_kode_akun AS D ON A.kode_kantor = D.kode_kantor AND A.id_kat_uang_keluar2 = D.id_kode_akun
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
		
		function list_uang_keluar_limit_hanya_uang_keluar_induk($cari,$order_by,$limit,$offset)
		{
			$query =
				"
					SELECT
						A.*
						,COALESCE(B.kode_akun,'') AS KODE_AKUN
						,COALESCE(B.nama_kode_akun,'') AS NAMA_AKUN
						,COALESCE(C.norek,'') AS NOREK
						,COALESCE(C.nama_bank,'') AS NAMA_BANK
						,COALESCE(C.atas_nama,'') AS ATAS_NAMA
						,COALESCE(D.kode_akun,'') AS KODE_AKUN2
						,COALESCE(D.nama_kode_akun,'') AS NAMA_AKUN2
						,COALESCE(E.sum_kredit,0) AS sum_kredit
						,COALESCE(E.id_h_penerimaan,'') AS concat_id_h_penerimaan
						,COALESCE(E.nama_uang_keluar,'') AS invoice
					FROM tb_uang_keluar AS A
					LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_keluar = B.id_kode_akun
					LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
					LEFT JOIN tb_kode_akun AS D ON A.kode_kantor = D.kode_kantor AND A.id_kat_uang_keluar2 = D.id_kode_akun
					LEFT JOIN
					(
						SELECT kode_kantor,id_induk_uang_keluar,SUM(nominal)+SUM(biaya) AS sum_kredit 
							,GROUP_CONCAT( id_h_penerimaan SEPARATOR ', ') AS id_h_penerimaan
							-- ,CONCAT('Invoice : ',GROUP_CONCAT( REPLACE(nama_uang_keluar,'Pembayaran Invoice ','') SEPARATOR ', ') ) AS nama_uang_keluar
							,GROUP_CONCAT( REPLACE(nama_uang_keluar,'Pembayaran Invoice ','') SEPARATOR ', ') AS nama_uang_keluar
						FROM tb_uang_keluar
						WHERE id_kat_uang_keluar2 <> ''
						GROUP BY kode_kantor,id_induk_uang_keluar
					) AS E ON A.kode_kantor = E.kode_kantor AND A.id_uang_keluar = E.id_induk_uang_keluar
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
		
		function count_uang_keluar_limit($cari)
		{
			$query =
				"
					SELECT
						COUNT(id_uang_keluar) AS JUMLAH
					FROM tb_uang_keluar AS A
					LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_keluar = B.id_kode_akun
					LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
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
		
		function sum_uang_keluar_limit($cari)
		{
			$query =
				"
					SELECT
						SUM(A.nominal) AS NOMINAL
					FROM tb_uang_keluar AS A
					LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_keluar = B.id_kode_akun
					LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
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
		
		function simpan_utama
		(
			$id_kat_uang_keluar,
			$id_kat_uang_keluar2,
			$id_costumer,
			$id_supplier,
			$id_karyawan,
			$id_proyek,
			$id_bank,
			$id_d_assets,
			$id_retur_penjualan,
			$id_retur_pembelian,
			$id_hadiah,
			$no_uang_keluar,
			$nama_uang_keluar,
			$diberikan_kepada,
			$diberikan_oleh,
			$untuk,
			$nominal,
			$jenis,
			$ket_uang_keluar,
			$isTabungan,
			$isPinjamanCos,
			$tgl_dikeluarkan,
			$tgl_diterima,
			$user_ins,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_uang_keluar
				(
					id_uang_keluar,
					id_kat_uang_keluar,
					id_kat_uang_keluar2,
					id_induk_uang_keluar,
					id_costumer,
					id_supplier,
					id_karyawan,
					id_proyek,
					id_bank,
					id_d_assets,
					id_retur_penjualan,
					id_retur_pembelian,
					id_hadiah,
					no_uang_keluar,
					nama_uang_keluar,
					diberikan_kepada,
					diberikan_oleh,
					untuk,
					nominal,
					jenis,
					ket_uang_keluar,
					isTabungan,
					isPinjamanCos,
					tgl_dikeluarkan,
					tgl_diterima,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_uang_keluar
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
								COALESCE(MAX(CAST(RIGHT(id_uang_keluar,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_uang_keluar
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_kat_uang_keluar."',
					'".$id_kat_uang_keluar2."',
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_uang_keluar
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
								COALESCE(MAX(CAST(RIGHT(id_uang_keluar,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_uang_keluar
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_costumer."',
					'".$id_supplier."',
					'".$id_karyawan."',
					'".$id_proyek."',
					'".$id_bank."',
					'".$id_d_assets."',
					'".$id_retur_penjualan."',
					'".$id_retur_pembelian."',
					'".$id_hadiah."',
					'".$no_uang_keluar."',
					'".$nama_uang_keluar."',
					'".$diberikan_kepada."',
					'".$diberikan_oleh."',
					'".$untuk."',
					'".$nominal."',
					'".$jenis."',
					'".$ket_uang_keluar."',
					'".$isTabungan."',
					'".$isPinjamanCos."',
					'".$tgl_dikeluarkan."',
					'".$tgl_diterima."',
					NOW(),
					NOW(),
					'".$user_ins."',
					'',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan
		(
			$id_kat_uang_keluar,
			$id_kat_uang_keluar2,
			$id_induk_uang_keluar,
			$id_costumer,
			$id_supplier,
			$id_karyawan,
			$id_proyek,
			$id_bank,
			$id_d_assets,
			$id_retur_penjualan,
			$id_retur_pembelian,
			$id_hadiah,
			$no_uang_keluar,
			$nama_uang_keluar,
			$diberikan_kepada,
			$diberikan_oleh,
			$untuk,
			$nominal,
			$jenis,
			$ket_uang_keluar,
			$isTabungan,
			$isPinjamanCos,
			$tgl_dikeluarkan,
			$tgl_diterima,
			$user_ins,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_uang_keluar
				(
					id_uang_keluar,
					id_kat_uang_keluar,
					id_kat_uang_keluar2,
					id_induk_uang_keluar,
					id_costumer,
					id_supplier,
					id_karyawan,
					id_proyek,
					id_bank,
					id_d_assets,
					id_retur_penjualan,
					id_retur_pembelian,
					id_hadiah,
					no_uang_keluar,
					nama_uang_keluar,
					diberikan_kepada,
					diberikan_oleh,
					untuk,
					nominal,
					jenis,
					ket_uang_keluar,
					isTabungan,
					isPinjamanCos,
					tgl_dikeluarkan,
					tgl_diterima,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_uang_keluar
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
								COALESCE(MAX(CAST(RIGHT(id_uang_keluar,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_uang_keluar
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_kat_uang_keluar."',
					'".$id_kat_uang_keluar2."',
					'".$id_induk_uang_keluar."',
					'".$id_costumer."',
					'".$id_supplier."',
					'".$id_karyawan."',
					'".$id_proyek."',
					'".$id_bank."',
					'".$id_d_assets."',
					'".$id_retur_penjualan."',
					'".$id_retur_pembelian."',
					'".$id_hadiah."',
					'".$no_uang_keluar."',
					'".$nama_uang_keluar."',
					'".$diberikan_kepada."',
					'".$diberikan_oleh."',
					'".$untuk."',
					'".$nominal."',
					'".$jenis."',
					'".$ket_uang_keluar."',
					'".$isTabungan."',
					'".$isPinjamanCos."',
					'".$tgl_dikeluarkan."',
					'".$tgl_diterima."',
					NOW(),
					NOW(),
					'".$user_ins."',
					'',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_uang_keluar_hutang
		(
			$id_kat_uang_keluar,
			$id_kat_uang_keluar2,
			$id_induk_uang_keluar,
			$id_costumer,
			$id_supplier,
			$id_karyawan,
			$id_proyek,
			$id_bank,
			$id_d_assets,
			$id_retur_penjualan,
			$id_retur_pembelian,
			$id_hadiah,
			$id_h_penerimaan,
			$no_uang_keluar,
			$nama_uang_keluar,
			$diberikan_kepada,
			$diberikan_oleh,
			$untuk,
			$nominal,
			$biaya,
			$pendapatan,
			$jenis,
			$ket_uang_keluar,
			$isTabungan,
			$isPinjamanCos,
			$tgl_dikeluarkan,
			$tgl_diterima,
			$user_ins,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_uang_keluar
				(
					id_uang_keluar,
					id_kat_uang_keluar,
					id_kat_uang_keluar2,
					id_induk_uang_keluar,
					id_costumer,
					id_supplier,
					id_karyawan,
					id_proyek,
					id_bank,
					id_d_assets,
					id_retur_penjualan,
					id_retur_pembelian,
					id_hadiah,
					id_h_penerimaan,
					no_uang_keluar,
					nama_uang_keluar,
					diberikan_kepada,
					diberikan_oleh,
					untuk,
					nominal,
					biaya,
					pendapatan,
					jenis,
					ket_uang_keluar,
					isTabungan,
					isPinjamanCos,
					tgl_dikeluarkan,
					tgl_diterima,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_uang_keluar
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
								COALESCE(MAX(CAST(RIGHT(id_uang_keluar,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_uang_keluar
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_kat_uang_keluar."',
					'".$id_kat_uang_keluar2."',
					'".$id_induk_uang_keluar."',
					'".$id_costumer."',
					'".$id_supplier."',
					'".$id_karyawan."',
					'".$id_proyek."',
					'".$id_bank."',
					'".$id_d_assets."',
					'".$id_retur_penjualan."',
					'".$id_retur_pembelian."',
					'".$id_hadiah."',
					'".$id_h_penerimaan."',
					'".$no_uang_keluar."',
					'".$nama_uang_keluar."',
					'".$diberikan_kepada."',
					'".$diberikan_oleh."',
					'".$untuk."',
					'".$nominal."',
					'".$biaya."',
					'".$pendapatan."',
					'".$jenis."',
					'".$ket_uang_keluar."',
					'".$isTabungan."',
					'".$isPinjamanCos."',
					'".$tgl_dikeluarkan."',
					'".$tgl_diterima."',
					NOW(),
					NOW(),
					'".$user_ins."',
					'',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_uang_keluar_nominal_saja($kode_kantor,$id_uang_keluar,$nominal,$biaya,$pendapatan,$user_updt)
		{
			$strquery = "
					UPDATE tb_uang_keluar SET
						
						nominal = '".$nominal."',
						biaya = '".$biaya."',
						pendapatan = '".$pendapatan."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_uang_keluar = '".$id_uang_keluar
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_uang_keluar,
			$id_kat_uang_keluar,
			$id_kat_uang_keluar2,
			$id_costumer,
			$id_supplier,
			$id_karyawan,
			$id_proyek,
			$id_bank,
			$id_d_assets,
			$id_retur_penjualan,
			$id_retur_pembelian,
			$id_hadiah,
			$no_uang_keluar,
			$nama_uang_keluar,
			$diberikan_kepada,
			$diberikan_oleh,
			$untuk,
			$nominal,
			$jenis,
			$ket_uang_keluar,
			$isTabungan,
			$isPinjamanCos,
			$tgl_dikeluarkan,
			$tgl_diterima,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_uang_keluar SET
						
						id_kat_uang_keluar = '".$id_kat_uang_keluar."',
						id_kat_uang_keluar2 = '".$id_kat_uang_keluar2."',
						id_costumer = '".$id_costumer."',
						id_supplier = '".$id_supplier."',
						id_karyawan = '".$id_karyawan."',
						id_proyek = '".$id_proyek."',
						id_bank = '".$id_bank."',
						id_d_assets = '".$id_d_assets."',
						id_retur_penjualan = '".$id_retur_penjualan."',
						id_retur_pembelian = '".$id_retur_pembelian."',
						id_hadiah = '".$id_hadiah."',
						no_uang_keluar = '".$no_uang_keluar."',
						nama_uang_keluar = '".$nama_uang_keluar."',
						diberikan_kepada = '".$diberikan_kepada."',
						diberikan_oleh = '".$diberikan_oleh."',
						untuk = '".$untuk."',
						nominal = '".$nominal."',
						jenis = '".$jenis."',
						ket_uang_keluar = '".$ket_uang_keluar."',
						isTabungan = '".$isTabungan."',
						isPinjamanCos = '".$isPinjamanCos."',
						tgl_dikeluarkan = '".$tgl_dikeluarkan."',
						tgl_diterima = '".$tgl_diterima."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_uang_keluar = '".$id_uang_keluar
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_uang_keluar,$kode_kantor)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_uang_keluar WHERE ".$berdasarkan." = '".$id_uang_keluar."' AND kode_kantor = '".$kode_kantor."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_uang_keluar($berdasarkan,$cari,$kode_kantor)
        {
            $query = $this->db->get_where('tb_uang_keluar', array($berdasarkan => $cari,'kode_kantor' => $kode_kantor));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_uang_keluar_cari($cari)
        {
            //$query = $this->db->get_where('tb_uang_keluar', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
			
			$query = "SELECT * FROM tb_uang_keluar ".$cari."";
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