<?php
	class M_kat_video extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_kat_video_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT * FROM tb_kat_video
							".$cari." ORDER BY KVID_DTINS DESC LIMIT ".$offset.",".$limit
							);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_kat_video_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(KVID_ID) AS JUMLAH 
										FROM tb_kat_video
										".$cari
									);
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
			$KVID_KODE,
			$KVID_NAMA,
			$KVID_KET,
			$KVID_USERINS,
			$KVID_USERUPDT,
			$KVID_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_kat_video
				(
					KVID_ID,
					KVID_KODE,
					KVID_NAMA,
					KVID_KET,
					KVID_USERINS,
					KVID_USERUPDT,
					KVID_DTINS,
					KVID_DTUPDT,
					KVID_KODEKANTOR


				)
				VALUES
				(
					(
						SELECT CONCAT('KVID',FRMTGL,ORD) AS KVID_ID
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
								COALESCE(MAX(CAST(RIGHT(KVID_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_kat_video
								-- WHERE DATE_FORMAT(KVID_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(KVID_DTINS) = DATE(NOW())
								AND KVID_KODEKANTOR = '".$KVID_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$KVID_KODE."',
					'".$KVID_NAMA."',
					'".$KVID_KET."',
					'".$KVID_USERINS."',
					'".$KVID_USERUPDT."',
					NOW(),
					NOW(),
					'".$KVID_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$KVID_ID,
			$KVID_KODE,
			$KVID_NAMA,
			$KVID_KET,
			$KVID_USERUPDT,
			$KVID_KODEKANTOR
		)
		{
			/*$data = array
			(
			   'nama_jabatan' => $nama,
			   'ket_jabatan' => $ket,
			   'user' => $id_user
			);
			
			//$this->db->where('id_jabatan', $id);
			$this->db->update('tb_jabatan', $data,array('id_jabatan' => $id,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));*/
			
			$query = "
					UPDATE tb_kat_video SET
						KVID_KODE = '".$KVID_KODE."',
						KVID_NAMA = '".$KVID_NAMA."',
						KVID_KET = '".$KVID_KET."',
						KVID_USERUPDT = '".$KVID_USERUPDT."',
						KVID_DTUPDT = NOW()
					WHERE KVID_KODEKANTOR = '".$KVID_KODEKANTOR."' AND KVID_ID = '".$KVID_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($KVID_KODEKANTOR,$KVID_ID)
		{
			$this->db->query("
						DELETE FROM tb_kat_video 
						WHERE KVID_KODEKANTOR = '".$KVID_KODEKANTOR."'
						AND KVID_ID = '".$KVID_ID."'
					");
		}
		
		
		function get_kat_video($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_kat_video', array($berdasarkan => $cari,'KVID_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query->row();
            }
            else
            {
                return false;
            }
        }
	}
?>