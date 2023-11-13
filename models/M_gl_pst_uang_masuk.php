<?php
	class M_gl_pst_uang_masuk extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function get_no_uang_masuk($kode_kantor)
		{
			/*
			$query =
				"
					SELECT CONCAT(ORD,'/UM/',M,'/',Y) AS no_bukti
					FROM
					(
						SELECT 
						CONCAT(Y,M,D) AS FRMTGL
						,Y
						,M
						,D
						
						,CASE
							WHEN (ORD >= 10 AND ORD < 99) THEN CONCAT('000',CAST(ORD AS CHAR))
							WHEN (ORD >= 99 AND ORD < 999) THEN CONCAT('00',CAST(ORD AS CHAR))
							WHEN (ORD >= 999 AND ORD < 9999) THEN CONCAT('0',CAST(ORD AS CHAR))
							WHEN ORD >= 9999 THEN CAST(ORD AS CHAR)
							ELSE CONCAT('0000',CAST(ORD AS CHAR))
						END As ORD
							
						From
						(
							SELECT
							CAST(LEFT(NOW(),4) AS CHAR) AS Y,
							CAST(MID(NOW(),6,2) AS CHAR) AS M,
							MID(NOW(),9,2) AS D,
							COALESCE(MAX(CAST(LEFT(no_bukti,5) AS UNSIGNED)) + 1,1) AS ORD
							From tb_uang_masuk
							WHERE LEFT(DATE(tgl_ins),7) = LEFT(DATE(NOW()),7)
							AND kode_kantor = '".$kode_kantor."'
						) AS A
					) AS AA
				";
			*/
			
			$query="
					SELECT CONCAT(ORD,'/UK/','/',D,'/',M,'/',Y,'/".$kode_kantor."') AS no_bukti
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
							-- COALESCE(MAX(CAST(RIGHT(no_bukti,5) AS UNSIGNED)) + 1,1) AS ORD
							COALESCE(MAX(CAST(LEFT(no_bukti,5) AS UNSIGNED)) + 1,1) AS ORD
							From tb_uang_masuk
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
		
		function list_uang_masuk_limit($cari,$order_by,$limit,$offset)
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
					FROM tb_uang_masuk AS A
					LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
					LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
					LEFT JOIN tb_kode_akun AS D ON A.kode_kantor = D.kode_kantor AND A.id_kat_uang_masuk2 = D.id_kode_akun
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
		
		
		function list_uang_masuk_limit_untuk_induk($cari,$order_by,$limit,$offset)
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
						,
						CASE WHEN A.isAwal = 'YA' THEN
							A.nominal
						ELSE
							COALESCE(E.sum_kredit,0) 
						END AS sum_kredit
					FROM tb_uang_masuk AS A
					LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
					LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
					LEFT JOIN tb_kode_akun AS D ON A.kode_kantor = D.kode_kantor AND A.id_kat_uang_masuk2 = D.id_kode_akun
					
					LEFT JOIN
					(
						SELECT kode_kantor,id_induk_uang_masuk,SUM(nominal) AS sum_kredit 
						FROM tb_uang_masuk
						WHERE id_uang_masuk <> id_induk_uang_masuk
						GROUP BY kode_kantor,id_induk_uang_masuk
					) AS E ON A.kode_kantor = E.kode_kantor AND A.id_uang_masuk = E.id_induk_uang_masuk
					
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
		
		function count_uang_masuk_limit($cari)
		{
			$query =
				"
					SELECT
						COUNT(id_uang_masuk) AS JUMLAH
					FROM tb_uang_masuk AS A
					LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
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
		
		function sum_uang_masuk_limit($cari)
		{
			$query =
				"
					SELECT
						SUM(A.nominal) AS NOMINAL
					FROM tb_uang_masuk AS A
					LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
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
		
		function simpan
		(
			$id_kat_uang_masuk,
			$id_induk_uang_masuk,
			$id_costumer,
			$id_supplier,
			$id_bank,
			$id_retur_penjualan,
			$id_retur_pembelian,
			$id_karyawan,
			$id_d_assets,
			$no_bukti,
			$nama_uang_masuk,
			$terima_dari,
			$diterima_oleh,
			$untuk,
			$nominal,
			$ket_uang_masuk,
			$tgl_uang_masuk,
			$isTabungan,
			$isPiutang,
			$noPinjamanCos,
			$user_ins,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_uang_masuk
				(
					id_uang_masuk,
					id_kat_uang_masuk,
					id_induk_uang_masuk,
					id_costumer,
					id_supplier,
					id_bank,
					id_retur_penjualan,
					id_retur_pembelian,
					id_karyawan,
					id_d_assets,
					no_bukti,
					nama_uang_masuk,
					terima_dari,
					diterima_oleh,
					untuk,
					nominal,
					ket_uang_masuk,
					tgl_uang_masuk,
					isTabungan,
					isPiutang,
					noPinjamanCos,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_uang_masuk
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
								COALESCE(MAX(CAST(RIGHT(id_uang_masuk,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_uang_masuk
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_kat_uang_masuk."',
					'".$id_induk_uang_masuk."',
					'".$id_costumer."',
					'".$id_supplier."',
					'".$id_bank."',
					'".$id_retur_penjualan."',
					'".$id_retur_pembelian."',
					'".$id_karyawan."',
					'".$id_d_assets."',
					'".$no_bukti."',
					'".$nama_uang_masuk."',
					'".$terima_dari."',
					'".$diterima_oleh."',
					'".$untuk."',
					'".$nominal."',
					'".$ket_uang_masuk."',
					'".$tgl_uang_masuk."',
					'".$isTabungan."',
					'".$isPiutang."',
					'".$noPinjamanCos."',
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
		
		function simpan_utama
		(
			$id_kat_uang_masuk,
			$id_costumer,
			$id_supplier,
			$id_bank,
			$id_retur_penjualan,
			$id_retur_pembelian,
			$id_karyawan,
			$id_d_assets,
			$no_bukti,
			$nama_uang_masuk,
			$terima_dari,
			$diterima_oleh,
			$untuk,
			$nominal,
			$ket_uang_masuk,
			$tgl_uang_masuk,
			$isTabungan,
			$isPiutang,
			$noPinjamanCos,
			$isAwal,
			$user_ins,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_uang_masuk
				(
					id_uang_masuk,
					id_induk_uang_masuk,
					id_kat_uang_masuk,
					id_costumer,
					id_supplier,
					id_bank,
					id_retur_penjualan,
					id_retur_pembelian,
					id_karyawan,
					id_d_assets,
					no_bukti,
					nama_uang_masuk,
					terima_dari,
					diterima_oleh,
					untuk,
					nominal,
					ket_uang_masuk,
					tgl_uang_masuk,
					isTabungan,
					isPiutang,
					noPinjamanCos,
					isAwal,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_uang_masuk
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
								COALESCE(MAX(CAST(RIGHT(id_uang_masuk,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_uang_masuk
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_uang_masuk
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
								COALESCE(MAX(CAST(RIGHT(id_uang_masuk,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_uang_masuk
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_kat_uang_masuk."',
					'".$id_costumer."',
					'".$id_supplier."',
					'".$id_bank."',
					'".$id_retur_penjualan."',
					'".$id_retur_pembelian."',
					'".$id_karyawan."',
					'".$id_d_assets."',
					'".$no_bukti."',
					'".$nama_uang_masuk."',
					'".$terima_dari."',
					'".$diterima_oleh."',
					'".$untuk."',
					'".$nominal."',
					'".$ket_uang_masuk."',
					'".$tgl_uang_masuk."',
					'".$isTabungan."',
					'".$isPiutang."',
					'".$noPinjamanCos."',
					'".$isAwal."',
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
		
		function edit
		(
			$id_uang_masuk,
			$id_kat_uang_masuk,
			$id_costumer,
			$id_supplier,
			$id_bank,
			$id_retur_penjualan,
			$id_retur_pembelian,
			$id_karyawan,
			$id_d_assets,
			$no_bukti,
			$nama_uang_masuk,
			$terima_dari,
			$diterima_oleh,
			$untuk,
			$nominal,
			$ket_uang_masuk,
			$tgl_uang_masuk,
			$isTabungan,
			$isPiutang,
			$noPinjamanCos,
			$isAwal,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_uang_masuk SET
						
						id_uang_masuk = '".$id_uang_masuk."',
						id_kat_uang_masuk = '".$id_kat_uang_masuk."',
						id_costumer = '".$id_costumer."',
						id_supplier = '".$id_supplier."',
						id_bank = '".$id_bank."',
						id_retur_penjualan = '".$id_retur_penjualan."',
						id_retur_pembelian = '".$id_retur_pembelian."',
						id_karyawan = '".$id_karyawan."',
						id_d_assets = '".$id_d_assets."',
						no_bukti = '".$no_bukti."',
						nama_uang_masuk = '".$nama_uang_masuk."',
						terima_dari = '".$terima_dari."',
						diterima_oleh = '".$diterima_oleh."',
						untuk = '".$untuk."',
						nominal = '".$nominal."',
						ket_uang_masuk = '".$ket_uang_masuk."',
						tgl_uang_masuk = '".$tgl_uang_masuk."',
						isTabungan = '".$isTabungan."',
						isPiutang = '".$isPiutang."',
						noPinjamanCos = '".$noPinjamanCos."',
						isAwal = '".$isAwal."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_uang_masuk = '".$id_uang_masuk
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_uang_masuk,$kode_kantor)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_uang_masuk WHERE ".$berdasarkan." = '".$id_uang_masuk."' AND kode_kantor = '".$kode_kantor."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_uang_masuk($berdasarkan,$cari,$kode_kantor)
        {
            $query = $this->db->get_where('tb_uang_masuk', array($berdasarkan => $cari,'kode_kantor' => $kode_kantor));
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