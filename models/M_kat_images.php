<?php
	class M_kat_images extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_kat_images_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT * FROM tb_kat_images
							".$cari." ORDER BY KIMG_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_kat_images_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(KIMG_ID) AS JUMLAH 
										FROM tb_kat_images
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
			$KIMG_KODE,
			$KIMG_NAMA,
			$KIMG_KET,
			$KIMG_USERINS,
			$KIMG_USERUPDT,
			$KIMG_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_kat_images
				(
					KIMG_ID,
					KIMG_KODE,
					KIMG_NAMA,
					KIMG_KET,
					KIMG_USERINS,
					KIMG_USERUPDT,
					KIMG_DTINS,
					KIMG_DTUPDT,
					KIMG_KODEKANTOR


				)
				VALUES
				(
					(
						SELECT CONCAT('KIMG',FRMTGL,ORD) AS KIMG_ID
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
								COALESCE(MAX(CAST(RIGHT(KIMG_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_kat_images
								-- WHERE DATE_FORMAT(KIMG_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(KIMG_DTINS) = DATE(NOW())
								AND KIMG_KODEKANTOR = '".$KIMG_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$KIMG_KODE."',
					'".$KIMG_NAMA."',
					'".$KIMG_KET."',
					'".$KIMG_USERINS."',
					'".$KIMG_USERUPDT."',
					NOW(),
					NOW(),
					'".$KIMG_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$KIMG_ID,
			$KIMG_KODE,
			$KIMG_NAMA,
			$KIMG_KET,
			$KIMG_USERUPDT,
			$KIMG_KODEKANTOR
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
					UPDATE tb_kat_images SET
						KIMG_KODE = '".$KIMG_KODE."',
						KIMG_NAMA = '".$KIMG_NAMA."',
						KIMG_KET = '".$KIMG_KET."',
						KIMG_USERUPDT = '".$KIMG_USERUPDT."',
						KIMG_DTUPDT = NOW()
					WHERE KIMG_KODEKANTOR = '".$KIMG_KODEKANTOR."' AND KIMG_ID = '".$KIMG_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($KIMG_KODEKANTOR,$KIMG_ID)
		{
			$this->db->query("
						DELETE FROM tb_kat_images 
						WHERE KIMG_KODEKANTOR = '".$KIMG_KODEKANTOR."'
						AND KIMG_ID = '".$KIMG_ID."'
					");
		}
		
		
		function get_kat_images($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_kat_images', array($berdasarkan => $cari,'KIMG_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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