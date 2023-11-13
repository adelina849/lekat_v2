<?php
	class M_gl_d_penjualan_bayar extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function cek_h_penjualan($cari)
		{
			$query = 
				"
					SELECT * FROM tb_h_penjualan ".$cari."
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
		
		function list_d_penjualan_bayar_limit($cari,$order_by)
		{
			$query = 
				"
					SELECT
						A.*
						,COALESCE(A.tgl_tempo_next,'') AS TGL_TEMPO_NEXT_FIX
						,COALESCE(B.norek,'') AS NOREK
						,COALESCE(B.nama_bank,'') AS NAMA_BANK
						,COALESCE(B.atas_nama,'') AS ATAS_NAMA
						,COALESCE(B.cabang,'') AS CABANG
					FROM tb_d_penjualan_bayar AS A
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
		
		function simpan
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
			$user_ins,
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
					user_ins,
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
					'".$user_ins."',
					'',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_tampilan_d_bayar
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
			$tgl_tempo_next,
			$dari,
			$diterima_oleh,
			$isCair,
			$user_ins,
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
					tgl_tempo_next,
					dari,
					diterima_oleh,
					isCair,
					tgl_ins,
					tgl_updt,
					user_ins,
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
					'".$tgl_tempo_next."',
					'".$dari."',
					'".$diterima_oleh."',
					'".$isCair."',
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
		
		function edit_tampilan_d_bayar
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
			$tgl_tempo_next,
			$dari,
			$diterima_oleh,
			$isCair,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
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
						tgl_tempo_next = '".$tgl_tempo_next."',
						dari = '".$dari."',
						diterima_oleh = '".$diterima_oleh."',
						isCair = '".$isCair."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_d_bayar = '".$id_d_bayar
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
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
			$kode_kantor
		)
		{
			$strquery = "
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
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_d_bayar = '".$id_d_bayar
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_d_bayar)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_d_penjualan_bayar WHERE ".$berdasarkan." = '".$id_d_bayar."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_d_penjualan_bayar($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_d_penjualan_bayar', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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