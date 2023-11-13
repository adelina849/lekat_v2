<?php
	class M_amal extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_amal_limit($cari,$limit,$offset)
		{
			$query = $this->db->query("
										SELECT
											A.AML_ID,
											A.KAML_ID,
											A.AML_KODE,
											A.AML_NAMA,
											A.AML_KEYWORDS,
											A.AML_DESC,
											A.AML_PIC,
											A.AML_PICTLP,
											A.AML_NAMALOK,
											A.AML_PICLOK,
											A.AML_PICLOKTLP,
											A.AML_NOMINAL,
											A.AML_KET,
											A.AML_PROV,
											A.AML_KAB,
											A.AML_KEC,
											A.AML_DESA,
											A.AML_ALMTDETAIL,
											A.AML_LONGI,
											A.AML_LATI,
											A.AML_START,
											A.AML_END,
											A.AML_LINKTAHUN,
											A.AML_LINKBULAN,
											A.AML_LINKTITLE,
											A.AML_USERINS,
											A.AML_USERUPDT,
											A.AML_DTINS,
											A.AML_DTUPDT,
											A.AML_KODEKANTOR
											, COALESCE(B.KAML_NAMA,'') AS KAML_NAMA
											, COALESCE(C.name,'') AS PROVINSI
											, COALESCE(D.name,'') AS KABKOT
											, COALESCE(E.name,'') AS KECAMATAN
											, COALESCE(F.name,'') AS DESA
											, COALESCE(MAX(G.IMG_FILE),'') AS IMG_FILE
											, COALESCE(MAX(G.IMG_LINK),'') AS IMG_LINK
										FROM tb_amal AS A
										LEFT JOIN tb_kat_amal AS B ON A.KAML_ID = B.KAML_ID AND A.AML_KODEKANTOR = B.KAML_KODEKANTOR
										LEFT JOIN provinces AS C ON A.AML_PROV = C.id
										LEFT JOIN tb_kabkot AS D ON A.AML_KAB = D.id
										LEFT JOIN tb_kecamatan AS E ON A.AML_KEC = E.id
										LEFT JOIN tb_desa AS F ON A.AML_DESA = F.id
										LEFT JOIN tb_images AS G ON A.AML_ID = G.ID AND A.AML_KODEKANTOR = G.IMG_KODEKANTOR AND G.IMG_GRUP = 'amal'
										".$cari." 
										GROUP BY
											A.AML_ID,
											A.KAML_ID,
											A.AML_KODE,
											A.AML_NAMA,
											A.AML_KEYWORDS,
											A.AML_DESC,
											A.AML_PIC,
											A.AML_PICTLP,
											A.AML_NAMALOK,
											A.AML_PICLOK,
											A.AML_PICLOKTLP,
											A.AML_NOMINAL,
											A.AML_KET,
											A.AML_PROV,
											A.AML_KAB,
											A.AML_KEC,
											A.AML_DESA,
											A.AML_ALMTDETAIL,
											A.AML_LONGI,
											A.AML_LATI,
											A.AML_START,
											A.AML_END,
											A.AML_LINKTAHUN,
											A.AML_LINKBULAN,
											A.AML_LINKTITLE,
											A.AML_USERINS,
											A.AML_USERUPDT,
											A.AML_DTINS,
											A.AML_DTUPDT,
											A.AML_KODEKANTOR
											, COALESCE(B.KAML_NAMA,'')
											, COALESCE(C.name,'')
											, COALESCE(D.name,'')
											, COALESCE(E.name,'')
											, COALESCE(F.name,'')
										ORDER BY A.AML_DTINS DESC 
										LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_amal_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.AML_ID) AS JUMLAH  
										FROM tb_amal AS A
										LEFT JOIN tb_kat_amal AS B ON A.KAML_ID = B.KAML_ID AND A.AML_KODEKANTOR = B.KAML_KODEKANTOR
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
			$KAML_ID,
			$AML_KODE,
			$AML_NAMA,
			
			$AML_LINKTITLE,
			
			$AML_KEYWORDS,
			$AML_DESC,
			
			$AML_PIC,
			$AML_PICTLP,
			$AML_NAMALOK,
			$AML_PICLOK,
			$AML_PICLOKTLP,
			$AML_NOMINAL,
			$AML_KET,
			$AML_PROV,
			$AML_KAB,
			$AML_KEC,
			$AML_DESA,
			$AML_ALMTDETAIL,
			$AML_LONGI,
			$AML_LATI,
			$AML_START,
			$AML_END,
			$AML_USERINS,
			$AML_USERUPDT,
			$AML_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_amal
				(
					AML_ID,
					KAML_ID,
					AML_KODE,
					AML_NAMA,
					AML_LINKTITLE,
					AML_LINKTAHUN,
					AML_LINKBULAN,
					AML_KEYWORDS,
					AML_DESC,
					AML_PIC,
					AML_PICTLP,
					AML_NAMALOK,
					AML_PICLOK,
					AML_PICLOKTLP,
					AML_NOMINAL,
					
					AML_KET,
					AML_PROV,
					AML_KAB,
					AML_KEC,
					AML_DESA,
					AML_ALMTDETAIL,
					AML_LONGI,
					AML_LATI,
					AML_START,
					AML_END,
					AML_USERINS,
					AML_USERUPDT,
					AML_DTINS,
					AML_DTUPDT,
					AML_KODEKANTOR

				)
				VALUES
				(
					(
						SELECT CONCAT('AML',FRMTGL,ORD) AS AML_ID
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
								COALESCE(MAX(CAST(RIGHT(AML_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_amal
								-- WHERE DATE_FORMAT(AML_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(AML_DTINS) = DATE(NOW())
								AND AML_KODEKANTOR = '".$AML_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$KAML_ID."',
					'".$AML_KODE."',
					'".$AML_NAMA."',
					'".$AML_LINKTITLE."',
					DATE_FORMAT(NOW(),'%Y'),
					DATE_FORMAT(NOW(),'%m'),
					'".$AML_KEYWORDS."',
					'".$AML_DESC."',
					'".$AML_PIC."',
					'".$AML_PICTLP."',
					'".$AML_NAMALOK."',
					'".$AML_PICLOK."',
					'".$AML_PICLOKTLP."',
					'".$AML_NOMINAL."',
					'".$AML_KET."',
					'".$AML_PROV."',
					'".$AML_KAB."',
					'".$AML_KEC."',
					'".$AML_DESA."',
					'".$AML_ALMTDETAIL."',
					'".$AML_LONGI."',
					'".$AML_LATI."',
					'".$AML_START."',
					'".$AML_END."',
					'".$AML_USERINS."',
					'".$AML_USERUPDT."',
					NOW(),
					NOW(),
					'".$AML_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit
		(
			$AML_ID,
			$KAML_ID,
			$AML_KODE,
			$AML_NAMA,
			
			$AML_LINKTITLE,
			
			$AML_KEYWORDS,
			$AML_DESC,
			
			$AML_PIC,
			$AML_PICTLP,
			$AML_NAMALOK,
			$AML_PICLOK,
			$AML_PICLOKTLP,
			$AML_NOMINAL,
			$AML_KET,
			$AML_PROV,
			$AML_KAB,
			$AML_KEC,
			$AML_DESA,
			$AML_ALMTDETAIL,
			$AML_LONGI,
			$AML_LATI,
			$AML_START,
			$AML_END,
			$AML_USERUPDT,
			$AML_KODEKANTOR
		)
		{
			$query = "
					UPDATE tb_amal SET
						KAML_ID = '".$KAML_ID."',
						AML_KODE = '".$AML_KODE."',
						AML_NAMA = '".$AML_NAMA."',
						AML_LINKTITLE = '".$AML_LINKTITLE."',
						AML_KEYWORDS = '".$AML_KEYWORDS."',
						AML_DESC = '".$AML_DESC."',
						AML_PIC = '".$AML_PIC."',
						AML_PICTLP = '".$AML_PICTLP."',
						AML_NAMALOK = '".$AML_NAMALOK."',
						AML_PICLOK = '".$AML_PICLOK."',
						AML_PICLOKTLP = '".$AML_PICLOKTLP."',
						AML_NOMINAL = '".$AML_NOMINAL."',
						AML_KET = '".$AML_KET."',
						AML_PROV = '".$AML_PROV."',
						AML_KAB = '".$AML_KAB."',
						AML_KEC = '".$AML_KEC."',
						AML_DESA = '".$AML_DESA."',
						AML_ALMTDETAIL = '".$AML_ALMTDETAIL."',
						AML_LONGI = '".$AML_LONGI."',
						AML_LATI = '".$AML_LATI."',
						AML_START = '".$AML_START."',
						AML_END = '".$AML_END."',
						AML_USERUPDT = '".$AML_USERUPDT."',
						AML_DTUPDT = NOW()
					WHERE AML_KODEKANTOR = '".$AML_KODEKANTOR."' AND AML_ID = '".$AML_ID."'
					";
			$query = $this->db->query($query);
		}
		
		
		function hapus($AML_KODEKANTOR,$AML_ID)
		{
			$this->db->query("DELETE FROM tb_amal WHERE AML_ID = '".$AML_ID."' AND AML_KODEKANTOR = '".$AML_KODEKANTOR."' ;");
		}
		
		
		function get_amal($berdasarkan,$cari)
		{
			$query = "SELECT * FROM tb_amal WHERE ".$berdasarkan." = '".$cari."'";
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function get_amal_byId($AML_ID,$AML_KODEKANTOR)
		{
			$query = "SELECT * FROM tb_amal WHERE AML_ID = '".$AML_ID."' AND AML_KODEKANTOR = '".$AML_KODEKANTOR."'";
			$query = $this->db->query($query);
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