<?php
	class M_jadwal_jemput_zakat extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_jemput_zakat_limit($cari,$limit,$offset)
		{
			$query = $this->db->query(" 
								SELECT
									*
								FROM 
								tb_jadwal_jemput_zakat AS A
								LEFT JOIN tb_karyawan AS B ON A.JMPT_IDPEG = B.id_karyawan AND A.JMPT_KODEKANTOR = B.kode_kantor
								".$cari." 
								ORDER BY JMPT_DTDARI DESC,JMPT_DTINS DESC 
								LIMIT ".$offset.",".$limit
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
		
		function count_jemput_zakat_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.JMPT_ID) AS JUMLAH
										FROM										
										tb_jadwal_jemput_zakat AS A
										LEFT JOIN tb_karyawan AS B ON A.JMPT_IDPEG = B.id_karyawan AND A.JMPT_KODEKANTOR = B.kode_kantor
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
			$JMPT_IDPEG,
			$JMPT_PEGNAMA, 	
			$JMPT_DTDARI,	
			$JMPT_SAMPAI,	
			$JMPT_KET, 	
			$JMPT_USERINS, 	
			$JMPT_USERUPDT, 	
			$JMPT_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_jadwal_jemput_zakat
				(
					JMPT_ID,
					JMPT_IDPEG,					
					JMPT_PEGNAMA, 	
					JMPT_DTDARI,	
					JMPT_SAMPAI,	
					JMPT_KET,
					JMPT_DTINS, 	
					JMPT_DTUPDT, 	
					JMPT_USERINS, 	
					JMPT_USERUPDT, 	
					JMPT_KODEKANTOR


				)
				VALUES
				(
					(
						SELECT CONCAT('JMPT',FRMTGL,ORD) AS JMPT_ID
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
								COALESCE(MAX(CAST(RIGHT(JMPT_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_jadwal_jemput_zakat
								-- WHERE DATE_FORMAT(JMPT_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(JMPT_DTINS) = DATE(NOW())
								AND JMPT_KODEKANTOR = '".$JMPT_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$JMPT_IDPEG."',
					'".$JMPT_PEGNAMA."', 	
					'".$JMPT_DTDARI."',	
					'".$JMPT_SAMPAI."',	
					'".$JMPT_KET."',
					NOW(), 	
					NOW(), 	
					'".$JMPT_USERINS."', 	
					'".$JMPT_USERUPDT."', 	
					'".$JMPT_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$JMPT_ID,
			$JMPT_IDPEG,
			$JMPT_PEGNAMA, 	
			$JMPT_DTDARI,	
			$JMPT_SAMPAI,	
			$JMPT_KET,
			$JMPT_USERUPDT, 	
			$JMPT_KODEKANTOR
		)
		{
			$query = "
					UPDATE tb_jadwal_jemput_zakat SET
						JMPT_IDPEG = '".$JMPT_IDPEG."',
						JMPT_PEGNAMA = '".$JMPT_PEGNAMA."',
						JMPT_DTDARI = '".$JMPT_DTDARI."',
						JMPT_SAMPAI = '".$JMPT_SAMPAI."',
						JMPT_KET = '".$JMPT_KET."',
						JMPT_USERUPDT = '".$JMPT_USERUPDT."',
						JMPT_DTUPDT = NOW()
					WHERE JMPT_KODEKANTOR = '".$JMPT_KODEKANTOR."' AND JMPT_ID = '".$JMPT_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($JMPT_KODEKANTOR,$JMPT_ID)
		{
			$this->db->query("
						DELETE FROM tb_jadwal_jemput_zakat 
						WHERE JMPT_KODEKANTOR = '".$JMPT_KODEKANTOR."'
						AND JMPT_ID = '".$JMPT_ID."'
					");
		}
		
		
		function get_jemput_zakat($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_jadwal_jemput_zakat', array($berdasarkan => $cari,'JMPT_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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