<?php
	class M_gl_mutasi_kas extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function get_no_bukti($kode_kantor)
		{
			$query =
				"
					SELECT CONCAT(ORD,'/".$kode_kantor."/MTS/',MROMAWI,'/',Y) AS no_bukti
					FROM
					(
						SELECT 
						CONCAT(Y,M,D) AS FRMTGL
						,Y
						,M
						,CASE 
							WHEN M = '01' THEN 'I'
							WHEN M = '02' THEN 'II'
							WHEN M = '03' THEN 'III'
							WHEN M = '04' THEN 'IV'
							WHEN M = '05' THEN 'V'
							WHEN M = '06' THEN 'VI'
							WHEN M = '07' THEN 'VII'
							WHEN M = '08' THEN 'VIII'
							WHEN M = '09' THEN 'IX'
							WHEN M = '10' THEN 'X'
							WHEN M = '11' THEN 'XI'
							WHEN M = '12' THEN 'XII'
							ELSE
								M
							END AS MROMAWI
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
							From tb_mutasi_kas
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
		
		function list_mutasi_kas_limit($cari,$order_by,$limit,$offset)
		{
			$query = "
				SELECT
					A.*
					,COALESCE(B.kode_akun,'') AS KODE_AKUN_IN
					,COALESCE(B.nama_kode_akun,'') AS NAMA_AKUN_IN
					,COALESCE(C.kode_akun,'') AS KODE_AKUN_OUT
					,COALESCE(C.nama_kode_akun,'') AS NAMA_AKUN_OUT
				FROM tb_mutasi_kas AS A
				LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kode_in = B.id_kode_akun
				LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = B.kode_kantor AND A.id_kode_out = C.id_kode_akun
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
		
		function count_mutasi_kas_limit($cari)
		{
			$query = "
				SELECT
					COUNT(A.id_mutasi) AS JUMLAH
				FROM tb_mutasi_kas AS A
				LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kode_in = B.id_kode_akun
				LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = B.kode_kantor AND A.id_kode_out = B.id_kode_akun
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
			$id_kode_in,
			$id_kode_out,
			$no_bukti,
			$tgl_transaksi,
			$nominal,
			$ket_mutasi,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_mutasi_kas
				(
				
					id_mutasi,
					id_kode_in,
					id_kode_out,
					no_bukti,
					tgl_transaksi,
					nominal,
					ket_mutasi,
					user_ins,
					user_updt,
					tgl_ins,
					tgl_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_mutasi
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
								COALESCE(MAX(CAST(RIGHT(id_mutasi,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_mutasi_kas
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_kode_in."',
					'".$id_kode_out."',
					'".$no_bukti."',
					'".$tgl_transaksi."',
					'".$nominal."',
					'".$ket_mutasi."',
					'".$user_ins."',
					'',
					NOW(),
					NOW(),
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_mutasi,
			$id_kode_in,
			$id_kode_out,
			$no_bukti,
			$tgl_transaksi,
			$nominal,
			$ket_mutasi,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_mutasi_kas SET
						id_kode_in = '".$id_kode_in."',
						id_kode_out = '".$id_kode_out."',
						no_bukti = '".$no_bukti."',
						tgl_transaksi = '".$tgl_transaksi."',
						nominal = '".$nominal."',
						ket_mutasi = '".$ket_mutasi."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_mutasi = '".$id_mutasi
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_mutasi)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_mutasi_kas WHERE ".$berdasarkan." = '".$id_mutasi."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_mutasi_cari($cari)
        {
            //$query = $this->db->get_where('tb_satuan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
			
			$query = "
				SELECT * FROM tb_mutasi_kas ".$cari.";
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