<?php
	class M_gl_training_event extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_training_event_limit($cari,$limit,$offset)
		{
			$query = "
				SELECT A.*
					,(COALESCE(B.CNT_KRY,0)) AS CNT_KRY 
				FROM tb_training_event AS A
				LEFT JOIN
				(
					SELECT id_training_event,kode_kantor,COUNT(id_karyawan) AS CNT_KRY
					FROM tb_karyawan_training
					GROUP BY id_training_event,kode_kantor
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_training_event = B.id_training_event 
				".$cari." ORDER BY A.nama_event ASC, A.tgl_ins DESC LIMIT ".$offset.",".$limit;
			
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
		
		function count_training_event_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(A.id_training_event) AS JUMLAH FROM tb_training_event AS A ".$cari);
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
			$id_training,
			$nama_event,
			$panitia,
			$tgl_event,
			$tgl_selesai,
			$alamat,
			$ket_event,
			$longi,
			$lati,
			$foto,
			$foto_url,
			$user_ins,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_training_event
				(
					id_training_event,
					id_training,
					nama_event,
					panitia,
					tgl_event,
					tgl_selesai,
					alamat,
					ket_event,
					longi,
					lati,
					foto,
					foto_url,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor

				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_training_event
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
								COALESCE(MAX(CAST(RIGHT(id_training_event,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_training_event
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_training."',
					'".$nama_event."',
					'".$panitia."',
					'".$tgl_event."',
					'".$tgl_selesai."',
					'".$alamat."',
					'".$ket_event."',
					'".$longi."',
					'".$lati."',
					'".$foto."',
					'".$foto_url."',
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
			
			$id_training_event,
			$id_training,
			$nama_event,
			$panitia,
			$tgl_event,
			$tgl_selesai,
			$alamat,
			$ket_event,
			$longi,
			$lati,
			$foto,
			$foto_url,
			$user_updt,
			$kode_kantor
		)
		{
			
			if($foto != "")
			{
				$strquery = "
				UPDATE tb_training_event SET
				
					id_training = '".$id_training."',
					nama_event = '".$nama_event."',
					panitia = '".$panitia."',
					tgl_event = '".$tgl_event."',
					tgl_selesai = '".$tgl_selesai."',
					alamat = '".$alamat."',
					ket_event = '".$ket_event."',
					longi = '".$longi."',
					lati = '".$lati."',
					foto = '".$foto."',
					foto_url = '".$foto_url."',
					tgl_updt = NOW(),
					user_updt = '".$user_updt."'
				WHERE kode_kantor = '".$kode_kantor."' AND id_training_event = '".$id_training_event
				."'
				";
			}
			else
			{
				$strquery = "
				UPDATE tb_training_event SET
				
					id_training = '".$id_training."',
					nama_event = '".$nama_event."',
					panitia = '".$panitia."',
					tgl_event = '".$tgl_event."',
					tgl_selesai = '".$tgl_selesai."',
					alamat = '".$alamat."',
					ket_event = '".$ket_event."',
					longi = '".$longi."',
					lati = '".$lati."',
					tgl_updt = NOW(),
					user_updt = '".$user_updt."'
				WHERE kode_kantor = '".$kode_kantor."' AND id_training_event = '".$id_training_event
				."'
				";
			}
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
			
		}
		
		function hapus($berdasarkan,$id)
		{
			/*HAPUS training*/
				$strquery = "DELETE FROM tb_training_event WHERE ".$berdasarkan." = '".$id."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS training*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_training_event($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_training_event', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function cek_peserta_event($id_training_event,$cari,$limit,$offset)
		{
			$query = "
				SELECT
					A.*
					,COALESCE(B.no_karyawan,'') AS no_karyawan
					,COALESCE(B.nik_karyawan,'') AS nik_karyawan
					,COALESCE(B.nama_karyawan,'') AS nama_karyawan
					,COALESCE(B.avatar,'') AS avatar
					,COALESCE(B.avatar_url,'') AS avatar_url
				FROM tb_karyawan_training AS A
				LEFT JOIN tb_karyawan AS B ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
				WHERE A.id_training_event = '".$id_training_event."' ".$cari."
				ORDER BY COALESCE(B.no_karyawan,'') ASC, COALESCE(B.nama_karyawan,'') ASC LIMIT ".$offset.",".$limit."
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
	}
?>