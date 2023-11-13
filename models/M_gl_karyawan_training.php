<?php
	class M_gl_karyawan_training extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_karyawan_training_limit($id_training_event,$cari,$limit,$offset)
		{
			$query = "
					
					SELECT
						A.*
						,COALESCE(B.id_kt,'') AS id_kt
						,COALESCE(B.id_training_event,'') AS id_training_event
						,COALESCE(B.nilai,'0') AS nilai
						,COALESCE(B.ket_karyawan_training,'') AS ket_karyawan_training
					FROM tb_karyawan AS A
					LEFT JOIN 
					(
						SELECT * FROM  tb_karyawan_training WHERE id_training_event = '".$id_training_event."'
					) AS B ON A.kode_kantor = B.kode_kantor AND A.id_karyawan = B.id_karyawan
					".$cari."
					ORDER BY id_kt DESC
					LIMIT ".$offset.",".$limit;
			
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
		
		function count_karyawan_training_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(id_karyawan) AS JUMLAH FROM tb_karyawan AS A ".$cari);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function get_karyawan_training($id_karyawan,$id_training_event)
        {
            $query = $this->db->query("SELECT * FROM tb_karyawan_training WHERE id_karyawan = '".$id_karyawan."' AND id_training_event = '".$id_training_event."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;");
            if($query->num_rows() > 0)
            {
                return $query->row();
            }
            else
            {
                return false;
            }
        }
		
		function get_list_karyawan_training($cari,$limit,$offset)
		{
			$query = 
			"
				SELECT A.* 
					,COALESCE(B.nama_event,'') AS nama_event
					,COALESCE(B.panitia,'') AS panitia
					,COALESCE(B.alamat,'') AS alamat
					,YEAR(COALESCE(B.tgl_event,'1900-01-01')) AS tahun
				FROM tb_karyawan_training AS A
				LEFT JOIN tb_training_event AS B ON A.id_training_event = B.id_training_event AND A.kode_kantor = B.kode_kantor
				".$cari."
				ORDER BY tgl_ins DESC
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
		
		function simpan(
		
			$id_karyawan,
			$id_training,
			$id_training_event,
			$nilai,
			$ket_karyawan_training,
			$user_ins,
			$user_updt,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_karyawan_training
				(
					id_kt,
					id_karyawan,
					id_training,
					id_training_event,
					nilai,
					ket_karyawan_training,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor

				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_kt
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
								COALESCE(MAX(CAST(RIGHT(id_kt,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_karyawan_training
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_karyawan."',
					'".$id_training."',
					'".$id_training_event."',
					'".$nilai."',
					'".$ket_karyawan_training."',
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
		
		function edit($id_karyawan,$id_training,$id_training_event,$nilai,$ket_karyawan_training)
		{
			/*HAPUS training*/
				$strquery = "
					UPDATE tb_karyawan_training SET
						nilai = '".$nilai."',
						ket_karyawan_training = '".$ket_karyawan_training."'
				WHERE id_karyawan = '".$id_karyawan."' AND id_training = '".$id_training."' AND id_training_event = '".$id_training_event."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS training*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($id_karyawan,$id_training_event)
		{
			/*HAPUS training*/
				$strquery = "DELETE FROM tb_karyawan_training WHERE id_karyawan = '".$id_karyawan."' AND id_training_event = '".$id_training_event."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS training*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_2($berdasarkan,$id)
		{
			/*HAPUS training*/
				$strquery = "DELETE FROM tb_karyawan_training WHERE ".$berdasarkan." = '".$id."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS training*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
	}
?>