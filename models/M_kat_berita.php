<?php
	class M_kat_berita extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_kat_berita_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT * FROM tb_kat_berita
							".$cari." ORDER BY KBRT_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_kat_berita_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(KBRT_ID) AS JUMLAH 
										FROM tb_kat_berita
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
			$KBRT_KODE,
			$KBRT_NAMA,
			$KBRT_KET,
			$KBRT_USERINS,
			$KBRT_USERUPDT,
			$KBRT_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_kat_berita
				(
					KBRT_ID,
					KBRT_KODE,
					KBRT_NAMA,
					KBRT_KET,
					KBRT_USERINS,
					KBRT_USERUPDT,
					KBRT_DTINS,
					KBRT_DTUPDT,
					KBRT_KODEKANTOR


				)
				VALUES
				(
					(
						SELECT CONCAT('KBRT',FRMTGL,ORD) AS KBRT_ID
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
								COALESCE(MAX(CAST(RIGHT(KBRT_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_kat_berita
								-- WHERE DATE_FORMAT(KBRT_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(KBRT_DTINS) = DATE(NOW())
								AND KBRT_KODEKANTOR = '".$KBRT_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$KBRT_KODE."',
					'".$KBRT_NAMA."',
					'".$KBRT_KET."',
					'".$KBRT_USERINS."',
					'".$KBRT_USERUPDT."',
					NOW(),
					NOW(),
					'".$KBRT_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$KBRT_ID,
			$KBRT_KODE,
			$KBRT_NAMA,
			$KBRT_KET,
			$KBRT_USERUPDT,
			$KBRT_KODEKANTOR
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
					UPDATE tb_kat_berita SET
						KBRT_KODE = '".$KBRT_KODE."',
						KBRT_NAMA = '".$KBRT_NAMA."',
						KBRT_KET = '".$KBRT_KET."',
						KBRT_USERUPDT = '".$KBRT_USERUPDT."',
						KBRT_DTUPDT = NOW()
					WHERE KBRT_KODEKANTOR = '".$KBRT_KODEKANTOR."' AND KBRT_ID = '".$KBRT_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($KBRT_KODEKANTOR,$KBRT_ID)
		{
			$this->db->query("
						DELETE FROM tb_kat_berita 
						WHERE KBRT_KODEKANTOR = '".$KBRT_KODEKANTOR."'
						AND KBRT_ID = '".$KBRT_ID."'
					");
		}
		
		
		function get_kat_berita($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_kat_berita', array($berdasarkan => $cari,'KBRT_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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