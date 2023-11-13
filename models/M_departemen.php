<?php
	class M_departemen extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_departemen_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT * FROM tb_departemen
							".$cari." ORDER BY DEPT_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_departemen_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(DEPT_ID) AS JUMLAH 
										FROM tb_departemen
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
			$DEPT_KODE,
			$DEPT_NAMA,
			$DEPT_KEPALA,
			$DEPT_KET,
			$DEPT_USERINS,
			$DEPT_USERUPDT,
			$DEPT_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_departemen
				(
					DEPT_ID,
					DEPT_KODE,
					DEPT_NAMA,
					DEPT_KEPALA,
					DEPT_KET,
					DEPT_USERINS,
					DEPT_USERUPDT,
					DEPT_DTINS,
					DEPT_DTUPDT,
					DEPT_KODEKANTOR


				)
				VALUES
				(
					(
						SELECT CONCAT('DEPT',FRMTGL,ORD) AS DEPT_ID
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
								COALESCE(MAX(CAST(RIGHT(DEPT_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_departemen
								-- WHERE DATE_FORMAT(DEPT_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(DEPT_DTINS) = DATE(NOW())
								AND DEPT_KODEKANTOR = '".$DEPT_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$DEPT_KODE."',
					'".$DEPT_NAMA."',
					'".$DEPT_KEPALA."',
					'".$DEPT_KET."',
					'".$DEPT_USERINS."',
					'".$DEPT_USERUPDT."',
					NOW(),
					NOW(),
					'".$DEPT_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$DEPT_ID,
			$DEPT_KODE,
			$DEPT_NAMA,
			$DEPT_KEPALA,
			$DEPT_KET,
			$DEPT_USERUPDT,
			$DEPT_KODEKANTOR
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
					UPDATE tb_departemen SET
						DEPT_KODE = '".$DEPT_KODE."',
						DEPT_NAMA = '".$DEPT_NAMA."',
						DEPT_KEPALA = '".$DEPT_KEPALA."',
						DEPT_KET = '".$DEPT_KET."',
						DEPT_USERUPDT = '".$DEPT_USERUPDT."',
						DEPT_DTUPDT = NOW()
					WHERE DEPT_KODEKANTOR = '".$DEPT_KODEKANTOR."' AND DEPT_ID = '".$DEPT_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($DEPT_KODEKANTOR,$DEPT_ID)
		{
			$this->db->query("
						DELETE FROM tb_departemen 
						WHERE DEPT_KODEKANTOR = '".$DEPT_KODEKANTOR."'
						AND DEPT_ID = '".$DEPT_ID."'
					");
		}
		
		
		function get_departemen($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_departemen', array($berdasarkan => $cari,'DEPT_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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