<?php
	class M_gl_peraturan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_peraturan_limit($cari,$limit,$offset)
		{
			$query = 
			"
				SELECT * FROM tb_peraturan
				".$cari." ORDER BY tgl_ins DESC, kode_per ASC LIMIT ".$offset.",".$limit."
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
		
		function count_peraturan_limit($cari)
		{
			$query = 
			"
				SELECT COUNT(id_per) AS JUMLAH
				FROM tb_peraturan
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
			$kode_per,
			$nama_per,
			$bobot_per,
			$ket_per,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_peraturan
				(
					id_per,
					kode_per,
					nama_per,
					bobot_per,
					ket_per,
					user_ins,
					user_updt,
					tgl_ins,
					tgl_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_per
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
								COALESCE(MAX(CAST(RIGHT(id_per,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_peraturan
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$kode_per."',
					'".$nama_per."',
					'".$bobot_per."',
					'".$ket_per."',
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
			$id_per,
			$kode_per,
			$nama_per,
			$bobot_per,
			$ket_per,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_peraturan SET
					
						kode_per = '".$kode_per."',
						nama_per = '".$nama_per."',
						bobot_per = '".$bobot_per."',
						ket_per = '".$ket_per."',
						user_updt = '".$user_updt."',
						tgl_updt = NOW()

					WHERE kode_kantor = '".$kode_kantor."' AND id_per = '".$id_per
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($id_per)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_peraturan WHERE id_per = '".$id_per."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_peraturan($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_peraturan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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