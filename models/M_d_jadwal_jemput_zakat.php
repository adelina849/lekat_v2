<?php
	class M_d_jadwal_jemput_zakat extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_d_jadwal_jemput_zakat_limit($cari,$limit,$offset)
		{
			$query = $this->db->query(" 
							SELECT A.*
								, COALESCE(C.name,'') AS PROVINSI
								, COALESCE(D.name,'') AS KABKOT
								, COALESCE(E.name,'') AS KECAMATAN
								, COALESCE(F.name,'') AS DESA
							FROM tb_d_jadwal_jemput_zakat AS A
							LEFT JOIN provinces AS C ON A.DJMPT_PROV = C.id
							LEFT JOIN tb_kabkot AS D ON A.DJMPT_KABKOT = D.id
							LEFT JOIN tb_kecamatan AS E ON A.DJMPT_KEC = E.id
							LEFT JOIN tb_desa AS F ON A.DJMPT_DESA = F.id
							
							".$cari." ORDER BY A.DJMPT_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_d_jadwal_jemput_zakat_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.DJMPT_ID) AS JUMLAH 
										FROM tb_d_jadwal_jemput_zakat AS A
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
			$JMPT_ID,
			$DJMPT_NAMALOK,
			$DJMPT_KET,
			$DJMPT_PROV,
			$DJMPT_KABKOT,
			$DJMPT_KEC,
			$DJMPT_DESA,
			$DJMPT_LATI,
			$DJMPT_LONGI,
			$DJMPT_USERINS,
			$DJMPT_USERUPDT,
			$DJMPT_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_d_jadwal_jemput_zakat
				(
					DJMPT_ID,
					JMPT_ID,
					DJMPT_NAMALOK,
					DJMPT_KET,
					DJMPT_PROV,
					DJMPT_KABKOT,
					DJMPT_KEC,
					DJMPT_DESA,
					DJMPT_LATI,
					DJMPT_LONGI,
					DJMPT_DTINS,
					DJMPT_DTUPDT,
					DJMPT_USERINS,
					DJMPT_USERUPDT,
					DJMPT_KODEKANTOR



				)
				VALUES
				(
					(
						SELECT CONCAT('DJMPT',FRMTGL,ORD) AS DJMPT_ID
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
								COALESCE(MAX(CAST(RIGHT(DJMPT_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_d_jadwal_jemput_zakat
								-- WHERE DATE_FORMAT(DJMPT_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(DJMPT_DTINS) = DATE(NOW())
								AND DJMPT_KODEKANTOR = '".$DJMPT_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$JMPT_ID."',
					'".$DJMPT_NAMALOK."',
					'".$DJMPT_KET."',
					'".$DJMPT_PROV."',
					'".$DJMPT_KABKOT."',
					'".$DJMPT_KEC."',
					'".$DJMPT_DESA."',
					'".$DJMPT_LATI."',
					'".$DJMPT_LONGI."',
					NOW(),
					NOW(),
					'".$DJMPT_USERINS."',
					'".$DJMPT_USERUPDT."',
					'".$DJMPT_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$DJMPT_ID,
			$JMPT_ID,
			$DJMPT_NAMALOK,
			$DJMPT_KET,
			$DJMPT_PROV,
			$DJMPT_KABKOT,
			$DJMPT_KEC,
			$DJMPT_DESA,
			$DJMPT_LATI,
			$DJMPT_LONGI,
			$DJMPT_USERUPDT,
			$DJMPT_KODEKANTOR
		)
		{
			$query = "
					UPDATE tb_d_jadwal_jemput_zakat SET
						JMPT_ID = '".$JMPT_ID."',
						DJMPT_NAMALOK = '".$DJMPT_NAMALOK."',
						DJMPT_KET = '".$DJMPT_KET."',
						DJMPT_PROV = '".$DJMPT_PROV."',
						DJMPT_KABKOT = '".$DJMPT_KABKOT."',
						DJMPT_KEC = '".$DJMPT_KEC."',
						DJMPT_DESA = '".$DJMPT_DESA."',
						DJMPT_LATI = '".$DJMPT_LATI."',
						DJMPT_LONGI = '".$DJMPT_LONGI."',
						DJMPT_USERUPDT = '".$DJMPT_USERUPDT."',
						DJMPT_DTUPDT = NOW()
					WHERE DJMPT_KODEKANTOR = '".$DJMPT_KODEKANTOR."' AND DJMPT_ID = '".$DJMPT_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($DJMPT_KODEKANTOR,$DJMPT_ID)
		{
			$this->db->query("
						DELETE FROM tb_d_jadwal_jemput_zakat 
						WHERE DJMPT_KODEKANTOR = '".$DJMPT_KODEKANTOR."'
						AND DJMPT_ID = '".$DJMPT_ID."'
					");
		}
		
		
		function get_d_jadwal_jemput_zakat($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_d_jadwal_jemput_zakat', array($berdasarkan => $cari,'DJMPT_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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