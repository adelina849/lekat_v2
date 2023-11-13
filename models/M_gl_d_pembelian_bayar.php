<?php
	class M_gl_d_pembelian_bayar extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_d_pembelian_bayar($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT * FROM tb_d_pembelian_bayar AS A
				LEFT JOIN tb_bank AS B ON A.id_bank = B.id_bank AND A.kode_kantor = B.kode_kantor
				".$cari."
				".$order_by."
				LIMIT ".$offset.",".$limit."
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
		
		function count_d_pembelian_bayar($cari)
		{
			$query =
			"
				SELECT COUNT(A.id_d_pembelian_bayar) AS JUMLAH FROM tb_d_pembelian_bayar AS A
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
					'',
					'".$user_ins."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
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
		
		function hapus($cari)
		{
			/*HAPUS JABATAN*/
				//$strquery = "DELETE FROM tb_d_pembelian_bayar WHERE ".$berdasarkan." = '".$id_d_bayar."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
				$strquery = "DELETE FROM tb_d_pembelian_bayar ".$cari." ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_d_pembelian_bayar($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_d_pembelian_bayar', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_d_pembelian_bayar_by_query($cari)
        {
			$query =
			"
				SELECT * FROM tb_d_pembelian_bayar ".$cari."
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
		
		function get_d_pembelian_bayar_akumulasi_by_query($cari)
		{
			$query =
			"
				SELECT
					A.*
					, CASE 
						WHEN (A.id_bank = '') THEN
							'TUNAI'
						ELSE
							CONCAT(COALESCE(B.nama_bank,''),COALESCE(B.norek,''))
						END AS bank
				FROM
				(
					SELECT kode_kantor,id_h_pembelian,id_bank,SUM(nominal) AS nominal 
					FROM tb_d_pembelian_bayar 
					GROUP BY kode_kantor,id_h_pembelian,id_bank
				) AS A
				LEFT JOIN tb_bank AS B ON A.id_bank = B.id_bank AND A.kode_kantor = B.kode_kantor 
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
	}
?>