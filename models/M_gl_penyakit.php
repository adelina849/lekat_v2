<?php
	class M_gl_penyakit extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_penyakit_limit($cari,$limit,$offset)
		{
			$query = 
			"
				SELECT * FROM tb_penyakit ".$cari." ORDER BY tgl_ins ASC LIMIT ".$offset.",".$limit."
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
		
		function count_penyakit_limit($cari)
		{
			$query = 
			"
				SELECT COUNT(id_penyakit) AS JUMLAH FROM tb_penyakit ".$cari."
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
			$kode_penyakit,
			$nama_penyakit,
			$pengobatan,
			$ket_penyakit,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_penyakit
				(
					id_penyakit,
					kode_penyakit,
					nama_penyakit,
					pengobatan,
					ket_penyakit,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_penyakit
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
								COALESCE(MAX(CAST(RIGHT(id_penyakit,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_penyakit
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$kode_penyakit."',
					'".$nama_penyakit."',
					'".$pengobatan."',
					'".$ket_penyakit."',
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
			$id_penyakit,
			$kode_penyakit,
			$nama_penyakit,
			$pengobatan,
			$ket_penyakit,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_penyakit SET
						kode_penyakit = '".$kode_penyakit."',
						nama_penyakit = '".$nama_penyakit."',
						pengobatan = '".$pengobatan."',
						ket_penyakit = '".$ket_penyakit."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_penyakit = '".$id_penyakit
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_penyakit)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_penyakit WHERE ".$berdasarkan." = '".$id_penyakit."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_penyakit($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_penyakit', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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