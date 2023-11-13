<?php
	class M_gl_sop extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_sop_limit($cari,$limit,$offset)
		{
			$query = 
			"
				SELECT * FROM tb_sop AS A
				LEFT JOIN tb_dept AS B ON A.id_dept = B.id_dept AND A.kode_kantor = B.kode_kantor
				".$cari." ORDER BY A.tgl_ins DESC, A.kode_sop ASC LIMIT ".$offset.",".$limit."
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
		
		function count_sop_limit($cari)
		{
			$query = 
			"
				SELECT COUNT(A.id_sop) AS JUMLAH
				FROM tb_sop AS A
				LEFT JOIN tb_dept AS B ON A.id_dept = B.id_dept AND A.kode_kantor = B.kode_kantor
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
			$id_dept,
			$id_jabatan,
			$kode_sop,
			$nama_sop,
			$tipe_sop,
			$keterangan_sop,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_sop
				(
					id_sop,
					id_dept,
					id_jabatan,
					kode_sop,
					nama_sop,
					tipe_sop,
					keterangan_sop,
					user_ins,
					user_updt,
					tgl_ins,
					tgl_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_sop
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
								COALESCE(MAX(CAST(RIGHT(id_sop,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_sop
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_dept."',
					'".$id_jabatan."',
					'".$kode_sop."',
					'".$nama_sop."',
					'".$tipe_sop."',
					'".$keterangan_sop."',
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
			$id_sop,
			$id_dept,
			$id_jabatan,
			$kode_sop,
			$nama_sop,
			$tipe_sop,
			$keterangan_sop,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_sop SET
					
						id_dept = '".$id_dept."',
						id_jabatan = '".$id_jabatan."',
						kode_sop = '".$kode_sop."',
						nama_sop = '".$nama_sop."',
						tipe_sop = '".$tipe_sop."',
						keterangan_sop = '".$keterangan_sop."',
						user_updt = '".$user_updt."',
						tgl_updt = NOW()
					WHERE kode_kantor = '".$kode_kantor."' AND id_sop = '".$id_sop
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($id_sop)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_sop WHERE id_sop = '".$id_sop."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_sop($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_sop', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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