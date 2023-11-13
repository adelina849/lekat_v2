<?php
	class M_menu extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_menu_not_halaman($cari)
		{
			$query = "
					SELECT A.* 
					FROM tb_menu AS A 
					LEFT JOIN tb_halaman AS B ON A.MENU_ID = B.MENU_ID AND A.MENU_KODEKANTOR = B.HAL_KODEKANTOR 
					WHERE A.MENU_ISPUNYAHAL = 0 AND B.HAL_ID IS NULL ".$cari.";
					";
			
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		
		function list_menu_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT A.*,COALESCE(B.MENU_NAMA,'') AS MENU_UTAMA
							FROM 
							tb_menu AS A
							LEFT JOIN tb_menu AS B ON A.MENU_MAINID = B.MENU_KODE AND A.MENU_KODEKANTOR = B.MENU_KODEKANTOR
							".$cari." ORDER BY MENU_ORDER ASC, MENU_KODE ASC LIMIT ".$offset.",".$limit
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
		
		function count_menu_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(A.MENU_ID) AS JUMLAH 
										FROM tb_menu AS A
										LEFT JOIN tb_menu AS B ON A.MENU_MAINID = B.MENU_ID  AND A.MENU_KODEKANTOR = B.MENU_KODEKANTOR
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
			$MENU_MAINID,
			$MENU_KAT,
			$MENU_ISMAINMENU,
			$MENU_KODE,
			$MENU_NAMA,
			$MENU_LEVEL,
			$MENU_ORDER,
			$MENU_ISPUNYAHAL,
			$MENU_LINK,
			$MENU_KET,
			$MENU_USERINS,
			$MENU_USERUPDT,
			$MENU_KODEKANTOR
		)
		{
			/*$data = array
			(
			   'nama_jabatan' => $nama,
			   'ket_jabatan' => $ket,
			   'user' => $id_user,
			   'kode_kantor' => $kode_kantor
			);

			$this->db->insert('tb_jabatan', $data);*/
			
			$query = "
				INSERT INTO tb_menu
				(
					MENU_ID,
					MENU_MAINID,
					MENU_KAT,
					MENU_ISMAINMENU,
					MENU_KODE,
					MENU_NAMA,
					MENU_LEVEL,
					MENU_ORDER,
					MENU_ISPUNYAHAL,
					MENU_LINK,
					MENU_KET,
					MENU_USERINS,
					MENU_USERUPDT,
					MENU_DTINS,
					MENU_DTUPDT,
					MENU_KODEKANTOR

				)
				VALUES
				(
					(
						SELECT CONCAT('MNU',FRMTGL,ORD) AS MENU_ID
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
								COALESCE(MAX(CAST(RIGHT(MENU_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_menu
								-- WHERE DATE_FORMAT(MENU_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(MENU_DTINS) = DATE(NOW())
								AND MENU_KODEKANTOR = '".$MENU_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$MENU_MAINID."',
					'".$MENU_KAT."',
					'".$MENU_ISMAINMENU."',
					'".$MENU_KODE."',
					'".$MENU_NAMA."',
					'".$MENU_LEVEL."',
					'".$MENU_ORDER."',
					'".$MENU_ISPUNYAHAL."',
					'".$MENU_LINK."',
					'".$MENU_KET."',
					'".$MENU_USERINS."',
					'".$MENU_USERUPDT."',
					NOW(),
					NOW(),
					'".$MENU_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$MENU_ID,
			$MENU_MAINID,
			$MENU_KAT,
			$MENU_ISMAINMENU,
			$MENU_KODE,
			$MENU_NAMA,
			$MENU_LEVEL,
			$MENU_ORDER,
			$MENU_ISPUNYAHAL,
			$MENU_LINK,
			$MENU_KET,
			$MENU_USERUPDT,
			$MENU_KODEKANTOR
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
					UPDATE tb_menu SET
						MENU_MAINID = '".$MENU_MAINID."',
						MENU_KAT = '".$MENU_KAT."',
						MENU_ISMAINMENU = '".$MENU_ISMAINMENU."',
						MENU_KODE = '".$MENU_KODE."',
						MENU_NAMA = '".$MENU_NAMA."',
						MENU_LEVEL = '".$MENU_LEVEL."',
						MENU_ORDER = '".$MENU_ORDER."',
						MENU_ISPUNYAHAL = '".$MENU_ISPUNYAHAL."',
						MENU_LINK = '".$MENU_LINK."',
						MENU_KET = '".$MENU_KET."',
						MENU_USERUPDT = '".$MENU_USERUPDT."',
						MENU_DTUPDT = NOW()
					WHERE MENU_KODEKANTOR = '".$MENU_KODEKANTOR."' AND MENU_ID = '".$MENU_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($MENU_KODEKANTOR,$MENU_ID)
		{
			$this->db->query("
						DELETE FROM tb_menu 
						WHERE MENU_KODEKANTOR = '".$MENU_KODEKANTOR."'
						AND MENU_ID = '".$MENU_ID."'
					");
		}
		
		
		function get_menu($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_menu', array($berdasarkan => $cari,'MENU_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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