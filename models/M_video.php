<?php
	class M_video extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_video_limit($cari,$limit,$offset)
		{
			$query = $this->db->query("
										SELECT A.*, COALESCE(B.KVID_NAMA,'') AS KVID_NAMA
										FROM tb_video AS A
										LEFT JOIN tb_kat_video AS B ON A.KVID_ID = B.KVID_ID AND A.VID_KODEKANTOR = B.KVID_KODEKANTOR
										".$cari." ORDER BY A.VID_DTINS DESC 
										LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_video_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.VID_ID) AS JUMLAH  
										FROM tb_video AS A
										LEFT JOIN tb_kat_video AS B ON A.KVID_ID = B.KVID_ID AND A.VID_KODEKANTOR = B.KVID_KODEKANTOR
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
			$KVID_ID,
			$VID_GRUP,
			$ID,
			$VID_KODE,
			$VID_NAMA,
			$VID_KET,
			$VID_LINK,
			$VID_USERINS,
			$VID_USERUPDT,
			$VID_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_video
				(
					VID_ID,
					KVID_ID,
					VID_GRUP,
					ID,
					VID_KODE,
					VID_NAMA,
					VID_KET,
					VID_LINK,
					VID_USERINS,
					VID_USERUPDT,
					VID_DTINS,
					VID_DTUPDT,
					VID_KODEKANTOR
				)
				VALUES
				(
					(
						SELECT CONCAT('VID',FRMTGL,ORD) AS VID_ID
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
								COALESCE(MAX(CAST(RIGHT(VID_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_video
								-- WHERE DATE_FORMAT(VID_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(VID_DTINS) = DATE(NOW())
								AND VID_KODEKANTOR = '".$VID_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$KVID_ID."',
					'".$VID_GRUP."',
					'".$ID."',
					'".$VID_KODE."',
					'".$VID_NAMA."',
					'".$VID_KET."',
					'".$VID_LINK."',
					'".$VID_USERINS."',
					'".$VID_USERUPDT."',
					NOW(),
					NOW(),
					'".$VID_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit
		(
			$VID_ID,
			$KVID_ID,
			$VID_GRUP,
			$ID,
			$VID_KODE,
			$VID_NAMA,
			$VID_KET,
			$VID_LINK,
			$VID_USERUPDT,
			$VID_KODEKANTOR
		)
		{
			$query = "
					UPDATE tb_video SET
						KVID_ID = '".$KVID_ID."',
						VID_GRUP = '".$VID_GRUP."',
						ID = '".$ID."',
						VID_KODE = '".$VID_KODE."',
						VID_NAMA = '".$VID_NAMA."',
						VID_KET = '".$VID_KET."',
						VID_LINK = '".$VID_LINK."',
						VID_USERUPDT = '".$VID_USERUPDT."',
						VID_DTUPDT = NOW()
					WHERE VID_KODEKANTOR = '".$VID_KODEKANTOR."' AND VID_ID = '".$VID_ID."'
					";
			$query = $this->db->query($query);
		}
		
		
		function hapus($VID_ID)
		{
			$this->db->query("DELETE FROM tb_video WHERE VID_ID = '".$VID_ID."' AND VID_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' ;");
		}
		
		
		function get_video($cari)
		{
			$query = "SELECT * FROM tb_video ".$cari."";
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
		
		function get_video_byId($VID_ID,$VID_KODEKANTOR)
		{
			$query = "SELECT * FROM tb_video WHERE VID_ID = '".$VID_ID."' AND VID_KODEKANTOR = '".$VID_KODEKANTOR."'";
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
		
	}
?>