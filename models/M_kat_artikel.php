<?php
	class M_kat_artikel extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_kat_artikel_limit($cari,$limit,$offset)
		{
			$query = $this->db->query(" 
							SELECT * FROM tb_kat_artikel
							".$cari." ORDER BY KART_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_kat_artikel_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(KART_ID) AS JUMLAH 
										FROM tb_kat_artikel
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
			$KART_KODE,
			$KART_NAMA,
			$KART_KET,
			$KART_USERINS,
			$KART_USERUPDT,
			$KART_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_kat_artikel
				(
					KART_ID,
					KART_KODE,
					KART_NAMA,
					KART_KET,
					KART_USERINS,
					KART_USERUPDT,
					KART_DTINS,
					KART_DTUPDT,
					KART_KODEKANTOR


				)
				VALUES
				(
					(
						SELECT CONCAT('KART',FRMTGL,ORD) AS KART_ID
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
								COALESCE(MAX(CAST(RIGHT(KART_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_kat_artikel
								-- WHERE DATE_FORMAT(KART_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(KART_DTINS) = DATE(NOW())
								AND KART_KODEKANTOR = '".$KART_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$KART_KODE."',
					'".$KART_NAMA."',
					'".$KART_KET."',
					'".$KART_USERINS."',
					'".$KART_USERUPDT."',
					NOW(),
					NOW(),
					'".$KART_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$KART_ID,
			$KART_KODE,
			$KART_NAMA,
			$KART_KET,
			$KART_USERUPDT,
			$KART_KODEKANTOR
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
					UPDATE tb_kat_artikel SET
						KART_KODE = '".$KART_KODE."',
						KART_NAMA = '".$KART_NAMA."',
						KART_KET = '".$KART_KET."',
						KART_USERUPDT = '".$KART_USERUPDT."',
						KART_DTUPDT = NOW()
					WHERE KART_KODEKANTOR = '".$KART_KODEKANTOR."' AND KART_ID = '".$KART_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($KART_KODEKANTOR,$KART_ID)
		{
			$this->db->query("
						DELETE FROM tb_kat_artikel 
						WHERE KART_KODEKANTOR = '".$KART_KODEKANTOR."'
						AND KART_ID = '".$KART_ID."'
					");
		}
		
		
		function get_kat_artikel($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_kat_artikel', array($berdasarkan => $cari,'KART_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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