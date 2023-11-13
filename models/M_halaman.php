<?php
	class M_halaman extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_halaman_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECt A.*,COALESCE(B.MENU_NAMA,'') AS MENU_NAMA,COALESCE(C.nama_karyawan,'') AS CREATED_BY
							FROM tb_halaman AS A
							LEFT JOIN tb_menu AS B ON A.MENU_ID = B.MENU_ID AND A.HAL_KODEKANTOR = B.MENU_KODEKANTOR
							LEFT JOIN tb_karyawan C ON A.HAL_USERINS = C.id_karyawan AND A.HAL_KODEKANTOR = C.kode_kantor
							".$cari." ORDER BY HAL_DTINS ASC LIMIT ".$offset.",".$limit
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
		
		function count_halaman_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.HAL_ID) AS JUMLAH 
										FROM tb_halaman AS A
										LEFT JOIN tb_menu AS B ON A.MENU_ID = B.MENU_ID AND A.HAL_KODEKANTOR = B.MENU_KODEKANTOR
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
			$MENU_ID,
			$HAL_KODE,
			$HAL_NAMA,
			$HAL_ISI,
			$MENU_KEYWORDS,
			$MENU_DESC,
			$HAL_LINKTITLE,
			$HAL_USERINS,
			$HAL_USERUPDT,
			$HAL_KODEKANTOR
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
				INSERT INTO tb_halaman
				(
					HAL_ID,
					MENU_ID,
					HAL_KODE,
					HAL_NAMA,
					HAL_ISI,
					MENU_KEYWORDS,
					MENU_DESC,
					HAL_LINKTAHUN,
					HAL_LINKBULAN,
					HAL_LINKTITLE,
					HAL_USERINS,
					HAL_USERUPDT,
					HAL_DTINS,
					HAL_DTUPDT,
					HAL_KODEKANTOR
				)
				VALUES
				(
					(
						SELECT CONCAT('HAL',FRMTGL,ORD) AS HAL_ID
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
								COALESCE(MAX(CAST(RIGHT(HAL_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_halaman
								-- WHERE DATE_FORMAT(HAL_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(HAL_DTINS) = DATE(NOW())
								AND HAL_KODEKANTOR = '".$HAL_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$MENU_ID."',
					'".$HAL_KODE."',
					'".$HAL_NAMA."',
					'".$HAL_ISI."',
					'".$MENU_KEYWORDS."',
					'".$MENU_DESC."',
					'',
					'',
					'".$HAL_LINKTITLE."',
					'".$HAL_USERINS."',
					'".$HAL_USERUPDT."',
					NOW(),
					NOW(),
					'".$HAL_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$HAL_ID,
			$MENU_ID,
			$HAL_KODE,
			$HAL_NAMA,
			$HAL_ISI,
			$MENU_KEYWORDS,
			$MENU_DESC,
			$HAL_LINKTITLE,
			$HAL_USERUPDT,
			$HAL_KODEKANTOR
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
					UPDATE tb_halaman SET
									MENU_ID = '".$MENU_ID."',
									HAL_KODE = '".$HAL_KODE."',
									HAL_NAMA = '".$HAL_NAMA."',
									HAL_ISI = '".$HAL_ISI."',
									MENU_KEYWORDS = '".$MENU_KEYWORDS."',
									MENU_DESC = '".$MENU_DESC."',
									HAL_LINKTITLE = '".$HAL_LINKTITLE."',
									HAL_DTUPDT = NOW(),
									HAL_USERUPDT = '".$HAL_USERUPDT."'
					WHERE HAL_KODEKANTOR = '".$HAL_KODEKANTOR."' AND HAL_ID = '".$HAL_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($HAL_KODEKANTOR,$HAL_ID)
		{
			$this->db->query("
						DELETE FROM tb_halaman 
						WHERE HAL_KODEKANTOR = '".$HAL_KODEKANTOR."'
						AND HAL_ID = '".$HAL_ID."'
					");
		}
		
		
		function get_halaman($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_halaman', array($berdasarkan => $cari,'HAL_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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