<?php
	class M_tematik extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_tematik_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT * FROM tb_tematik
							".$cari." ORDER BY TEMA_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_tematik_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(TEMA_ID) AS JUMLAH 
										FROM tb_tematik
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
			$TEMA_KODE,
			$TEMA_NAMA,
			$TEMA_KET,
			$TEMA_USERINS,
			$TEMA_USERUPDT,
			$TEMA_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_tematik
				(
					TEMA_ID,
					TEMA_KODE,
					TEMA_NAMA,
					TEMA_KET,
					TEMA_USERINS,
					TEMA_USERUPDT,
					TEMA_DTINS,
					TEMA_DTUPDT,
					TEMA_KODEKANTOR


				)
				VALUES
				(
					(
						SELECT CONCAT('TEMA',FRMTGL,ORD) AS TEMA_ID
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
								COALESCE(MAX(CAST(RIGHT(TEMA_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_tematik
								-- WHERE DATE_FORMAT(TEMA_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(TEMA_DTINS) = DATE(NOW())
								AND TEMA_KODEKANTOR = '".$TEMA_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$TEMA_KODE."',
					'".$TEMA_NAMA."',
					'".$TEMA_KET."',
					'".$TEMA_USERINS."',
					'".$TEMA_USERUPDT."',
					NOW(),
					NOW(),
					'".$TEMA_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$TEMA_ID,
			$TEMA_KODE,
			$TEMA_NAMA,
			$TEMA_KET,
			$TEMA_USERUPDT,
			$TEMA_KODEKANTOR
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
					UPDATE tb_tematik SET
						TEMA_KODE = '".$TEMA_KODE."',
						TEMA_NAMA = '".$TEMA_NAMA."',
						TEMA_KET = '".$TEMA_KET."',
						TEMA_USERUPDT = '".$TEMA_USERUPDT."',
						TEMA_DTUPDT = NOW()
					WHERE TEMA_KODEKANTOR = '".$TEMA_KODEKANTOR."' AND TEMA_ID = '".$TEMA_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($TEMA_KODEKANTOR,$TEMA_ID)
		{
			$this->db->query("
						DELETE FROM tb_tematik 
						WHERE TEMA_KODEKANTOR = '".$TEMA_KODEKANTOR."'
						AND TEMA_ID = '".$TEMA_ID."'
					");
		}
		
		
		function get_tematik($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_tematik', array($berdasarkan => $cari,'TEMA_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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