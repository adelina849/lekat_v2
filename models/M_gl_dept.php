<?php
	class M_gl_dept extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_dept_limit($cari,$limit,$offset)
		{
			$query = $this->db->query("SELECT * FROM tb_dept ".$cari." ORDER BY kode_dept ASC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_dept_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(id_dept) AS JUMLAH FROM tb_dept ".$cari);
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
			$kode_dept,
			$nama_dept,
			$ket_dept,
			$hirarki,
			$user_ins,
			$user_updt,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_dept
				(
					id_dept,
					kode_dept,
					nama_dept,
					ket_dept,
					hirarki,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_dept
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
								COALESCE(MAX(CAST(RIGHT(id_dept,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_dept
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$kode_dept."',
					'".$nama_dept."',
					'".$ket_dept."',
					'".$hirarki."',
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
			$id_dept,
			$kode_dept,
			$nama_dept,
			$ket_dept,
			$hirarki,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_dept SET
						kode_dept = '".$kode_dept."',
						nama_dept = '".$nama_dept."',
						ket_dept = '".$ket_dept."',
						hirarki = '".$hirarki."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_dept = '".$id_dept
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($id_dept)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_dept WHERE id_dept = '".$id_dept."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_dept($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_dept', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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