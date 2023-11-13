<?php
	class M_gl_uang_keluar extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function get_no_uang_keluar($kode_kantor)
		{
			$query =
				"
					SELECT CONCAT(ORD,'/UK/',M,'/',Y) AS no_uang_keluar
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
							COALESCE(MAX(CAST(LEFT(no_uang_keluar,5) AS UNSIGNED)) + 1,1) AS ORD
							From tb_uang_keluar
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
						,COALESCE(F.kode_supplier,0) AS kode_supplier
						,COALESCE(F.nama_supplier,0) AS nama_supplier
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
					LEFT JOIN tb_supplier AS F ON A.kode_kantor = F.kode_kantor AND A.id_supplier = F.id_supplier
					
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
					LEFT JOIN tb_supplier AS F ON A.kode_kantor = F.kode_kantor AND A.id_supplier = F.id_supplier
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
		
		function hapus($berdasarkan,$id_uang_keluar)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_uang_keluar WHERE ".$berdasarkan." = '".$id_uang_keluar."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_uang_keluar($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_uang_keluar', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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