<?php
	class M_bank extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_bank_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT * FROM tb_bank
							".$cari." ORDER BY BNK_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_bank_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(BNK_ID) AS JUMLAH 
										FROM tb_bank
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
			$BNK_NAMA,
			$BNK_ATASNAMA,
			$BNK_UNIT,
			$BNK_NOREK,
			$BNK_KET,
			$BNK_DTBUAT,
			$BNK_USERINS,
			$BNK_USERUPDT,
			$BNK_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_bank
				(
					BNK_ID,
					BNK_NAMA,
					BNK_ATASNAMA,
					BNK_UNIT,
					BNK_NOREK,
					BNK_KET,
					BNK_DTBUAT,
					BNK_USERINS,
					BNK_USERUPDT,
					BNK_DTINS,
					BNK_DTUPDT,
					BNK_KODEKANTOR


				)
				VALUES
				(
					(
						SELECT CONCAT('BNK',FRMTGL,ORD) AS BNK_ID
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
								COALESCE(MAX(CAST(RIGHT(BNK_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_bank
								-- WHERE DATE_FORMAT(BNK_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(BNK_DTINS) = DATE(NOW())
								AND BNK_KODEKANTOR = '".$BNK_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$BNK_NAMA."',
					'".$BNK_ATASNAMA."',
					'".$BNK_UNIT."',
					'".$BNK_NOREK."',
					'".$BNK_KET."',
					'".$BNK_DTBUAT."',
					'".$BNK_USERINS."',
					'".$BNK_USERUPDT."',
					NOW(),
					NOW(),
					'".$BNK_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$BNK_ID,
			$BNK_NAMA,
			$BNK_ATASNAMA,
			$BNK_UNIT,
			$BNK_NOREK,
			$BNK_KET,
			$BNK_DTBUAT,
			$BNK_USERUPDT,
			$BNK_KODEKANTOR
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
					UPDATE tb_bank SET
						BNK_NAMA = '".$BNK_NAMA."',
						BNK_ATASNAMA = '".$BNK_ATASNAMA."',
						BNK_UNIT = '".$BNK_UNIT."',
						BNK_NOREK = '".$BNK_NOREK."',
						BNK_KET = '".$BNK_KET."',
						BNK_DTBUAT = '".$BNK_DTBUAT."',
						BNK_USERUPDT = '".$BNK_USERUPDT."',
						BNK_DTUPDT = NOW()
					WHERE BNK_KODEKANTOR = '".$BNK_KODEKANTOR."' AND BNK_ID = '".$BNK_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($BNK_KODEKANTOR,$BNK_ID)
		{
			$this->db->query("
						DELETE FROM tb_bank 
						WHERE BNK_KODEKANTOR = '".$BNK_KODEKANTOR."'
						AND BNK_ID = '".$BNK_ID."'
					");
		}
		
		
		function get_bank($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_bank', array($berdasarkan => $cari,'BNK_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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