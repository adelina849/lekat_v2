<?php
	class M_kat_muzaqi extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_kat_muzaqi_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT * FROM tb_kat_muzaqi
							".$cari." ORDER BY KMUZ_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_kat_muzaqi_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(KMUZ_ID) AS JUMLAH 
										FROM tb_kat_muzaqi
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
			$KMUZ_KODE,
			$KMUZ_NAMA,
			$KMUZ_KET,
			$KMUZ_USERINS,
			$KMUZ_USERUPDT,
			$KMUZ_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_kat_muzaqi
				(
					KMUZ_ID,
					KMUZ_KODE,
					KMUZ_NAMA,
					KMUZ_KET,
					KMUZ_USERINS,
					KMUZ_USERUPDT,
					KMUZ_DTINS,
					KMUZ_DTUPDT,
					KMUZ_KODEKANTOR


				)
				VALUES
				(
					(
						SELECT CONCAT('KMUZ',FRMTGL,ORD) AS KMUZ_ID
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
								COALESCE(MAX(CAST(RIGHT(KMUZ_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_kat_muzaqi
								-- WHERE DATE_FORMAT(KMUZ_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(KMUZ_DTINS) = DATE(NOW())
								AND KMUZ_KODEKANTOR = '".$KMUZ_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$KMUZ_KODE."',
					'".$KMUZ_NAMA."',
					'".$KMUZ_KET."',
					'".$KMUZ_USERINS."',
					'".$KMUZ_USERUPDT."',
					NOW(),
					NOW(),
					'".$KMUZ_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$KMUZ_ID,
			$KMUZ_KODE,
			$KMUZ_NAMA,
			$KMUZ_KET,
			$KMUZ_USERUPDT,
			$KMUZ_KODEKANTOR
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
					UPDATE tb_kat_muzaqi SET
						KMUZ_KODE = '".$KMUZ_KODE."',
						KMUZ_NAMA = '".$KMUZ_NAMA."',
						KMUZ_KET = '".$KMUZ_KET."',
						KMUZ_USERUPDT = '".$KMUZ_USERUPDT."',
						KMUZ_DTUPDT = NOW()
					WHERE KMUZ_KODEKANTOR = '".$KMUZ_KODEKANTOR."' AND KMUZ_ID = '".$KMUZ_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($KMUZ_KODEKANTOR,$KMUZ_ID)
		{
			$this->db->query("
						DELETE FROM tb_kat_muzaqi 
						WHERE KMUZ_KODEKANTOR = '".$KMUZ_KODEKANTOR."'
						AND KMUZ_ID = '".$KMUZ_ID."'
					");
		}
		
		
		function get_kat_muzaqi($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_kat_muzaqi', array($berdasarkan => $cari,'KMUZ_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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