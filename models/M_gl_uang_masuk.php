<?php
	class M_gl_uang_masuk extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function get_no_uang_masuk($kode_kantor)
		{
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
							COALESCE(MAX(CAST(LEFT(no_bukti,5) AS UNSIGNED)) + 1,1) AS ORD
							From tb_uang_masuk
							WHERE LEFT(DATE(tgl_ins),7) = LEFT(DATE(NOW()),7)
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
		
		
		function list_transfer_to_akun($kode_kantor,$tgl)
		{
			$query =
				"
					SELECT
						A.tgl
						,D.id_bank
						,D.noRek
						,D.namaBank
						,D.atasNama
						,COALESCE(SUM(B.sum_bayar_IN),0) AS BIN
						,COALESCE(SUM(C.bayar_out),0) AS BOUT
						,COALESCE(SUM(B.sum_bayar_IN),0) - COALESCE(SUM(C.bayar_out),0) AS HTF
						,COALESCE(E.is_tf_to_akun,'') AS is_tf_to_akun
						,COALESCE(F.kode_akun,'') AS kode_akun
						,COALESCE(F.nama_akun,'') AS nama_akun
						,'".$kode_kantor."' AS kode_kantor
					FROM
					(
						SELECT '".$tgl."' AS tgl
					) AS A
					LEFT JOIN
					(
						SELECT 'TUNAI'AS id_bank,'TUNAI' AS noRek,'TUNAI' AS namaBank,'TUNAI' AS atasNama, '".$tgl."' AS tgl
						UNION ALL
						SELECT id_bank,norek,nama_bank,atas_nama, '".$tgl."' AS tgl
						FROM tb_bank AS A
						WHERE kode_kantor = '".$kode_kantor."'
						GROUP BY id_bank,norek,nama_bank,atas_nama
					) AS D ON A.tgl = D.tgl

					LEFT JOIN
					(
						SELECT 
							A.kode_kantor
							,CASE WHEN A.id_bank = '' THEN
								'TUNAI'
							ELSE
								id_bank
							END AS id_bank
							,A.tgl_bayar
							,SUM(A.sum_bayar_IN) AS sum_bayar_IN
						FROM
						(
							SELECT
								A.*
								,( (A.biaya + A.pajak) - (A.diskon + A.nominal_retur) )AS LAINNYA
								,COALESCE(B.bayar,0) AS sum_bayar_IN
								,COALESCE(B.id_bank,'') AS id_bank
								,COALESCE(B.tgl_bayar,'') AS tgl_bayar
							FROM tb_h_penjualan AS A
							LEFT JOIN
							(
								SELECT
									kode_kantor,id_h_penjualan,id_d_bayar,tgl_bayar
										,CASE WHEN id_bank = '' THEN 'TUNAI' ELSE id_bank END AS id_bank
									,SUM(nominal) AS bayar
								FROM tb_d_penjualan_bayar
								GROUP BY kode_kantor,id_h_penjualan,id_d_bayar,id_bank,tgl_bayar
							) AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							WHERE 
								A.kode_kantor = '".$kode_kantor."' AND 
								DATE(B.tgl_bayar) = '".$tgl."'
								AND COALESCE(A.sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
						) AS A
						GROUP BY A.kode_kantor,A.id_bank,A.tgl_bayar
					) AS B ON A.tgl = B.tgl_bayar AND D.id_bank = B.id_bank
					LEFT JOIN
					(
						SELECT
							A.kode_kantor
							,CASE WHEN A.id_bank = '' THEN
								'TUNAI'
							ELSE
								id_bank
							END AS id_bank
							,A.tgl_bayar
							,SUM(A.nominal) AS bayar_out
						FROM tb_d_pembelian_bayar AS A
						INNER JOIN tb_h_penerimaan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penerimaan = B.id_h_penerimaan
						WHERE A.kode_kantor = '".$kode_kantor."'
						AND (A.tgl_bayar) = '".$tgl."'
						GROUP BY A.kode_kantor,A.id_bank,A.tgl_bayar
					) AS C ON A.tgl = C.tgl_bayar AND D.id_bank = C.id_bank
					LEFT JOIN
					(
						SELECT
							*
							,CASE WHEN id_bank = '' THEN
								'TUNAI'
							ELSE
								id_bank
							END AS id_bank_2
						FROM
						tb_uang_masuk 
						WHERE TRIM(is_tf_to_akun) = TRIM('YA') AND kode_kantor = '".$kode_kantor."'
					) AS E ON A.tgl = E.tgl_uang_masuk AND D.id_bank = E.id_bank_2

					LEFT JOIN
					(
							SELECT  
									distinct
									kode_kantor
									,CASE 
										WHEN (id_bank = '') AND (target = 'BANK') THEN 
											'BANK'
										WHEN (id_bank = '') AND (target = 'KAS') THEN 
											'TUNAI' 
										ELSE
											id_bank 
										END AS id_bank
									-- ,id_bank
									,target
									,kode_akun
									,nama_kode_akun AS nama_akun
								FROM tb_kode_akun
								WHERE kode_kantor = '".$kode_kantor."' AND target IN ('BANK','KAS')
								GROUP BY 
								kode_kantor
								,id_bank
								,target
								,kode_akun
								,nama_kode_akun
					) AS F ON D.id_bank = F.id_bank

					GROUP BY
						A.tgl
						,D.id_bank
						,D.noRek
						,D.namaBank
						,D.atasNama
						,COALESCE(F.kode_akun,'')
						,COALESCE(F.nama_akun,'')
						
					ORDER BY D.namaBank ASC, D.noRek ASC
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
						
						/*
						,
						CASE WHEN A.isAwal = 'YA' THEN
							A.nominal
						ELSE
							COALESCE(E.sum_kredit,0) 
						END AS sum_kredit
						*/
						,COALESCE(E.sum_kredit,0) AS sum_kredit
						,COALESCE(F.nama_lengkap,'') AS nama_costumer
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
					
					LEFT JOIN tb_costumer AS F ON A.kode_kantor = F.kode_kantor AND A.id_costumer = F.id_costumer
					
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
					LEFT JOIN tb_costumer AS F ON A.kode_kantor = F.kode_kantor AND A.id_costumer = F.id_costumer
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
					LEFT JOIN tb_costumer AS F ON A.kode_kantor = F.kode_kantor AND A.id_costumer = F.id_costumer
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
			$is_tf_to_akun,
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
					is_tf_to_akun,
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
					'".$is_tf_to_akun."',
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
		
		function hapus($berdasarkan,$id_uang_masuk)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_uang_masuk WHERE ".$berdasarkan." = '".$id_uang_masuk."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_uang_masuk($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_uang_masuk', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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