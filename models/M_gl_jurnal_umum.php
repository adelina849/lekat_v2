<?php
	class M_gl_jurnal_umum extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function get_id_kode_akun($cari)
		{
			$query = "SELECT * FROM tb_kode_akun ".$cari." ";
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
		
		function get_cari_tb_h_jurnal_umum($cari,$order_by,$limit)
		{
			$query = "SELECT *,CASE WHEN idx_chld = idx_induk THEN 0 ELSE 1 END AS idx_mix FROM tb_jurnal_umum ".$cari." ".$order_by." ".$limit."";
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
		
		function get_cari_tb_h_jurnal_umum_group_by($cari)
		{
			$query = "SELECT DISTINCT no_bukti, tgl_transaksi FROM tb_jurnal_umum ".$cari." GROUP BY no_bukti, tgl_transaksi";
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
		
		function get_cari_tb_h_jurnal_umum_group_by_no($cari,$order_by,$limit)
		{
			$query = "SELECT distinct no_bukti FROM tb_jurnal_umum ".$cari." ".$order_by." ".$limit."";
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
		
		function get_no_jurnal_umum($kode_kantor)
		{
			$query="
					SELECT CONCAT(ORD,'/JU','/',D,'/',M,'/',Y,'/".$kode_kantor."') AS no_bukti
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
							From tb_jurnal_umum
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
		
		function get_id_jurnal_umum($kode_kantor)
		{
			$query ="
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_jurnal_umum
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
								COALESCE(MAX(CAST(RIGHT(id_jurnal_umum,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_jurnal_umum
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
		
		
		function simpan(
			$id_kode_akun,
			$id_supplier,
			$id_h_penerimaan,
			$no_bukti,
			$kode_akun,
			$nama_akun,
			$tgl_transaksi,
			$ket,
			$debit,
			$kredit,
			$isDebit,
			$idx_induk,
			$idx_chld,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_jurnal_umum
				(
					id_jurnal_umum,
					id_kode_akun,
					id_supplier,
					id_h_penerimaan,
					no_bukti,
					kode_akun,
					nama_akun,
					tgl_transaksi,
					ket,
					debit,
					kredit,
					isDebit,
					idx_induk,
					idx_chld,
					tgl_ins,
					user_ins,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_jurnal_umum
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
								COALESCE(MAX(CAST(RIGHT(id_jurnal_umum,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_jurnal_umum
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_kode_akun."',
					'".$id_supplier."',
					'".$id_h_penerimaan."',
					'".$no_bukti."',
					'".$kode_akun."',
					'".$nama_akun."',
					'".$tgl_transaksi."',
					'".$ket."',
					'".$debit."',
					'".$kredit."',
					'".$isDebit."',
					'".$idx_induk."',
					'".$idx_chld."',
					NOW(),
					'".$user_ins."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit(
			$id_jurnal_umum,
			$id_kode_akun,
			$id_supplier,
			$id_h_penerimaan,
			$no_bukti,
			$kode_akun,
			$nama_akun,
			$tgl_transaksi,
			$ket,
			$debit,
			$kredit,
			$isDebit,
			$idx_induk,
			$idx_chld,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_jurnal_umum SET
						id_kode_akun = '".$id_kode_akun."',
						
						id_supplier = CASE WHEN '".$id_supplier."' = '' THEN id_supplier ELSE '".$id_supplier."' END,
						id_h_penerimaan = CASE WHEN '".$id_h_penerimaan."' = '' THEN id_h_penerimaan ELSE '".$id_h_penerimaan."' END,
						
			
						no_bukti = '".$no_bukti."',
						kode_akun = '".$kode_akun."',
						nama_akun = '".$nama_akun."',
						tgl_transaksi = '".$tgl_transaksi."',
						ket = '".$ket."',
						debit = '".$debit."',
						kredit = '".$kredit."',
						isDebit = '".$isDebit."',
						idx_induk = '".$idx_induk."',
						idx_chld = '".$idx_chld."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_jurnal_umum = '".$id_jurnal_umum
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($cari)
		{
			$strquery = "DELETE FROM tb_jurnal_umum ".$cari;
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	}
?>