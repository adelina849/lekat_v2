<?php
	class M_gl_training extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_training_limit($cari,$limit,$offset)
		{
			$query = "
				SELECT 
					A.*
					,COALESCE(B.CNT_EVENT,0) AS CNT_EVENT
					,COALESCE(B.CNT_KRY,0) AS CNT_KRY
				FROM tb_training AS A
				LEFT JOIN
				(
					SELECT A.id_training,COUNT(A.id_training_event) AS CNT_EVENT,SUM(COALESCE(B.CNT_KRY,0)) AS CNT_KRY,A.kode_kantor 
					FROM tb_training_event AS A
					LEFT JOIN
					(
						SELECT id_training_event,kode_kantor,COUNT(id_karyawan) AS CNT_KRY
						FROM tb_karyawan_training
						GROUP BY id_training_event,kode_kantor
					) AS B ON A.kode_kantor = B.kode_kantor AND A.id_training_event = B.id_training_event
					-- GROUP BY A.id_training,A.kode_kantor ,COALESCE(B.CNT_KRY,0)
					GROUP BY A.id_training,A.kode_kantor
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_training = B.id_training 
				".$cari." ORDER BY A.nama_training ASC, A.tgl_ins DESC LIMIT ".$offset.",".$limit;
			
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
		
		function count_training_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(A.id_training) AS JUMLAH FROM tb_training AS A ".$cari);
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
			$kode_training,
			$nama_training,
			$point_training,
			$hirarki,
			$ket_training,
			$user_ins,
			$user_updt,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_training
				(
					id_training,
					kode_training,
					nama_training,
					point_training,
					hirarki,
					ket_training,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor

				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_training
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
								COALESCE(MAX(CAST(RIGHT(id_training,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_training
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$kode_training."',
					'".$nama_training."',
					'".$point_training."',
					'".$hirarki."',
					'".$ket_training."',
					NOW(),
					NOW(),
					'".$user_ins."',
					'".$user_updt."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_training,
			$kode_training,
			$nama_training,
			$point_training,
			$hirarki,
			$ket_training,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_training SET
						kode_training = '".$kode_training."',
						nama_training = '".$nama_training."',
						point_training = '".$point_training."',
						hirarki = '".$hirarki."',
						ket_training = '".$ket_training."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_training = '".$id_training
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($id)
		{
			/*HAPUS training*/
				$strquery = "DELETE FROM tb_training WHERE id_training = '".$id."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS training*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_training($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_training', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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