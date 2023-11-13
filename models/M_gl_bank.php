<?php
	class M_gl_bank extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_bank_kode_akun($cari)
		{
			$query = "
						SELECT 
							A.*
						FROM tb_bank AS A
						LEFT JOIN tb_kode_akun AS B 
							ON A.kode_kantor = B.kode_kantor
							AND A.id_bank = B.id_bank
						".$cari."
						ORDER BY A.nama_bank ASC,A.norek ASC
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
		
		function list_bank_limit($cari,$limit,$offset)
		{
			$query = $this->db->query("SELECT * FROM tb_bank ".$cari." ORDER BY tgl_ins DESC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_bank_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(id_bank) AS JUMLAH FROM tb_bank ".$cari);
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
			$norek,
			$nama_bank,
			$atas_nama,
			$cabang,
			$tgl_pembuatan,
			$ket_bank,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_bank
				(
					id_bank,
					norek,
					nama_bank,
					atas_nama,
					cabang,
					tgl_pembuatan,
					ket_bank,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_bank
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
								COALESCE(MAX(CAST(RIGHT(id_bank,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_bank
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$norek."',
					'".$nama_bank."',
					'".$atas_nama."',
					'".$cabang."',
					'".$tgl_pembuatan."',
					'".$ket_bank."',
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
			$id_bank,
			$norek,
			$nama_bank,
			$atas_nama,
			$cabang,
			$tgl_pembuatan,
			$ket_bank,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_bank SET
						norek = '".$norek."',
						nama_bank = '".$nama_bank."',
						atas_nama = '".$atas_nama."',
						cabang = '".$cabang."',
						tgl_pembuatan = '".$tgl_pembuatan."',
						ket_bank = '".$ket_bank."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_bank = '".$id_bank
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_bank)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_bank WHERE ".$berdasarkan." = '".$id_bank."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_bank($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_bank', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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