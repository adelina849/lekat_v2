<?php
	class M_kat_amal extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_kat_amal_limit($cari,$limit,$offset)
		{
			$query = $this->db->query(" 
							SELECT * FROM tb_kat_amal
							".$cari." ORDER BY KAML_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_kat_amal_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(KAML_ID) AS JUMLAH 
										FROM tb_kat_amal
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
			$KAML_KODE,
			$KAML_NAMA,
			$KAML_KET,
			$KAML_USERINS,
			$KAML_USERUPDT,
			$KAML_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_kat_amal
				(
					KAML_ID,
					KAML_KODE,
					KAML_NAMA,
					KAML_KET,
					KAML_USERINS,
					KAML_USERUPDT,
					KAML_DTINS,
					KAML_DTUPDT,
					KAML_KODEKANTOR


				)
				VALUES
				(
					(
						SELECT CONCAT('KAML',FRMTGL,ORD) AS KAML_ID
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
								COALESCE(MAX(CAST(RIGHT(KAML_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_kat_amal
								-- WHERE DATE_FORMAT(KAML_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(KAML_DTINS) = DATE(NOW())
								AND KAML_KODEKANTOR = '".$KAML_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$KAML_KODE."',
					'".$KAML_NAMA."',
					'".$KAML_KET."',
					'".$KAML_USERINS."',
					'".$KAML_USERUPDT."',
					NOW(),
					NOW(),
					'".$KAML_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$KAML_ID,
			$KAML_KODE,
			$KAML_NAMA,
			$KAML_KET,
			$KAML_USERUPDT,
			$KAML_KODEKANTOR
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
					UPDATE tb_kat_amal SET
						KAML_KODE = '".$KAML_KODE."',
						KAML_NAMA = '".$KAML_NAMA."',
						KAML_KET = '".$KAML_KET."',
						KAML_USERUPDT = '".$KAML_USERUPDT."',
						KAML_DTUPDT = NOW()
					WHERE KAML_KODEKANTOR = '".$KAML_KODEKANTOR."' AND KAML_ID = '".$KAML_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($KAML_KODEKANTOR,$KAML_ID)
		{
			$this->db->query("
						DELETE FROM tb_kat_amal 
						WHERE KAML_KODEKANTOR = '".$KAML_KODEKANTOR."'
						AND KAML_ID = '".$KAML_ID."'
					");
		}
		
		
		function get_kat_amal($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_kat_amal', array($berdasarkan => $cari,'KAML_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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