<?php
	class M_kat_pos extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_kat_pos_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT * FROM tb_kat_pos
							".$cari." ORDER BY KPOS_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_kat_pos_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(KPOS_ID) AS JUMLAH 
										FROM tb_kat_pos
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
			$KPOS_KODE,
			$KPOS_NAMA,
			$KPOS_KET,
			$KPOS_USERINS,
			$KPOS_USERUPDT,
			$KPOS_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_kat_pos
				(
					KPOS_ID,
					KPOS_KODE,
					KPOS_NAMA,
					KPOS_KET,
					KPOS_USERINS,
					KPOS_USERUPDT,
					KPOS_DTINS,
					KPOS_DTUPDT,
					KPOS_KODEKANTOR


				)
				VALUES
				(
					(
						SELECT CONCAT('KPOS',FRMTGL,ORD) AS KPOS_ID
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
								COALESCE(MAX(CAST(RIGHT(KPOS_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_kat_pos
								-- WHERE DATE_FORMAT(KPOS_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(KPOS_DTINS) = DATE(NOW())
								AND KPOS_KODEKANTOR = '".$KPOS_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$KPOS_KODE."',
					'".$KPOS_NAMA."',
					'".$KPOS_KET."',
					'".$KPOS_USERINS."',
					'".$KPOS_USERUPDT."',
					NOW(),
					NOW(),
					'".$KPOS_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$KPOS_ID,
			$KPOS_KODE,
			$KPOS_NAMA,
			$KPOS_KET,
			$KPOS_USERUPDT,
			$KPOS_KODEKANTOR
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
					UPDATE tb_kat_pos SET
						KPOS_KODE = '".$KPOS_KODE."',
						KPOS_NAMA = '".$KPOS_NAMA."',
						KPOS_KET = '".$KPOS_KET."',
						KPOS_USERUPDT = '".$KPOS_USERUPDT."',
						KPOS_DTUPDT = NOW()
					WHERE KPOS_KODEKANTOR = '".$KPOS_KODEKANTOR."' AND KPOS_ID = '".$KPOS_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($KPOS_KODEKANTOR,$KPOS_ID)
		{
			$this->db->query("
						DELETE FROM tb_kat_pos 
						WHERE KPOS_KODEKANTOR = '".$KPOS_KODEKANTOR."'
						AND KPOS_ID = '".$KPOS_ID."'
					");
		}
		
		
		function get_kat_pos($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_kat_pos', array($berdasarkan => $cari,'KPOS_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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