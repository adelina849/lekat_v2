<?php
	class M_pos extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_pos_limit($cari,$limit,$offset)
		{
			$query = $this->db->query("
										SELECT
											A.POS_ID,
											A.KPOS_ID,
											A.POS_KODE,
											A.POS_NAMA,
											A.POS_PIC,
											A.POS_PICTLP,
											A.POS_NAMALOK,
											A.POS_PICLOK,
											A.POS_PICLOKTLP,
											A.POS_KET,
											A.POS_PROV,
											A.POS_KAB,
											A.POS_KEC,
											A.POS_DESA,
											A.POS_ALMTDETAIL,
											A.POS_LONGI,
											A.POS_LATI,
											A.POS_START,
											A.POS_END,
											A.POS_USERINS,
											A.POS_USERUPDT,
											A.POS_DTINS,
											A.POS_DTUPDT,
											A.POS_KODEKANTOR
											, COALESCE(B.KPOS_NAMA,'') AS KPOS_NAMA
											, COALESCE(C.name,'') AS PROVINSI
											, COALESCE(D.name,'') AS KABKOT
											, COALESCE(E.name,'') AS KECAMATAN
											, COALESCE(F.name,'') AS DESA
											, COALESCE(MAX(G.IMG_FILE),'') AS IMG_FILE
											, COALESCE(MAX(G.IMG_LINK),'') AS IMG_LINK
										FROM tb_pos AS A
										LEFT JOIN tb_kat_pos AS B ON A.KPOS_ID = B.KPOS_ID AND A.POS_KODEKANTOR = B.KPOS_KODEKANTOR
										LEFT JOIN provinces AS C ON A.POS_PROV = C.id
										LEFT JOIN tb_kabkot AS D ON A.POS_KAB = D.id
										LEFT JOIN tb_kecamatan AS E ON A.POS_KEC = E.id
										LEFT JOIN tb_desa AS F ON A.POS_DESA = F.id
										LEFT JOIN tb_images AS G ON A.POS_ID = G.ID AND A.POS_KODEKANTOR = G.IMG_KODEKANTOR AND G.IMG_GRUP = 'pos'
										".$cari." 
										GROUP BY
											A.POS_ID,
											A.KPOS_ID,
											A.POS_KODE,
											A.POS_NAMA,
											A.POS_PIC,
											A.POS_PICTLP,
											A.POS_NAMALOK,
											A.POS_PICLOK,
											A.POS_PICLOKTLP,
											A.POS_KET,
											A.POS_PROV,
											A.POS_KAB,
											A.POS_KEC,
											A.POS_DESA,
											A.POS_ALMTDETAIL,
											A.POS_LONGI,
											A.POS_LATI,
											A.POS_START,
											A.POS_END,
											A.POS_USERINS,
											A.POS_USERUPDT,
											A.POS_DTINS,
											A.POS_DTUPDT,
											A.POS_KODEKANTOR
											, COALESCE(B.KPOS_NAMA,'')
											, COALESCE(C.name,'')
											, COALESCE(D.name,'')
											, COALESCE(E.name,'')
											, COALESCE(F.name,'')
										ORDER BY A.POS_DTINS DESC 
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
		
		function count_pos_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.POS_ID) AS JUMLAH  
										FROM tb_pos AS A
										LEFT JOIN tb_kat_pos AS B ON A.KPOS_ID = B.KPOS_ID AND A.POS_KODEKANTOR = B.KPOS_KODEKANTOR
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
			$KPOS_ID,
			$POS_KODE,
			$POS_NAMA,
			$POS_PIC,
			$POS_PICTLP,
			$POS_NAMALOK,
			$POS_PICLOK,
			$POS_PICLOKTLP,
			$POS_KET,
			$POS_PROV,
			$POS_KAB,
			$POS_KEC,
			$POS_DESA,
			$POS_ALMTDETAIL,
			$POS_LONGI,
			$POS_LATI,
			$POS_START,
			$POS_END,
			$POS_USERINS,
			$POS_USERUPDT,
			$POS_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_pos
				(
					POS_ID,
					KPOS_ID,
					POS_KODE,
					POS_NAMA,
					POS_PIC,
					POS_PICTLP,
					POS_NAMALOK,
					POS_PICLOK,
					POS_PICLOKTLP,
					POS_KET,
					POS_PROV,
					POS_KAB,
					POS_KEC,
					POS_DESA,
					POS_ALMTDETAIL,
					POS_LONGI,
					POS_LATI,
					POS_START,
					POS_END,
					POS_USERINS,
					POS_USERUPDT,
					POS_DTINS,
					POS_DTUPDT,
					POS_KODEKANTOR

				)
				VALUES
				(
					(
						SELECT CONCAT('POS',FRMTGL,ORD) AS POS_ID
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
								COALESCE(MAX(CAST(RIGHT(POS_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_pos
								-- WHERE DATE_FORMAT(POS_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(POS_DTINS) = DATE(NOW())
								AND POS_KODEKANTOR = '".$POS_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$KPOS_ID."',
					'".$POS_KODE."',
					'".$POS_NAMA."',
					'".$POS_PIC."',
					'".$POS_PICTLP."',
					'".$POS_NAMALOK."',
					'".$POS_PICLOK."',
					'".$POS_PICLOKTLP."',
					'".$POS_KET."',
					'".$POS_PROV."',
					'".$POS_KAB."',
					'".$POS_KEC."',
					'".$POS_DESA."',
					'".$POS_ALMTDETAIL."',
					'".$POS_LONGI."',
					'".$POS_LATI."',
					'".$POS_START."',
					'".$POS_END."',
					'".$POS_USERINS."',
					'".$POS_USERUPDT."',
					NOW(),
					NOW(),
					'".$POS_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit
		(
			$POS_ID,
			$KPOS_ID,
			$POS_KODE,
			$POS_NAMA,
			$POS_PIC,
			$POS_PICTLP,
			$POS_NAMALOK,
			$POS_PICLOK,
			$POS_PICLOKTLP,
			$POS_KET,
			$POS_PROV,
			$POS_KAB,
			$POS_KEC,
			$POS_DESA,
			$POS_ALMTDETAIL,
			$POS_LONGI,
			$POS_LATI,
			$POS_START,
			$POS_END,
			$POS_USERUPDT,
			$POS_KODEKANTOR
		)
		{
			$query = "
					UPDATE tb_pos SET
						KPOS_ID = '".$KPOS_ID."',
						POS_KODE = '".$POS_KODE."',
						POS_NAMA = '".$POS_NAMA."',
						POS_PIC = '".$POS_PIC."',
						POS_PICTLP = '".$POS_PICTLP."',
						POS_NAMALOK = '".$POS_NAMALOK."',
						POS_PICLOK = '".$POS_PICLOK."',
						POS_PICLOKTLP = '".$POS_PICLOKTLP."',
						POS_KET = '".$POS_KET."',
						POS_PROV = '".$POS_PROV."',
						POS_KAB = '".$POS_KAB."',
						POS_KEC = '".$POS_KEC."',
						POS_DESA = '".$POS_DESA."',
						POS_ALMTDETAIL = '".$POS_ALMTDETAIL."',
						POS_LONGI = '".$POS_LONGI."',
						POS_LATI = '".$POS_LATI."',
						POS_START = '".$POS_START."',
						POS_END = '".$POS_END."',
						POS_USERUPDT = '".$POS_USERUPDT."',
						POS_DTUPDT = NOW()
					WHERE POS_KODEKANTOR = '".$POS_KODEKANTOR."' AND POS_ID = '".$POS_ID."'
					";
			$query = $this->db->query($query);
		}
		
		
		function hapus($POS_KODEKANTOR,$POS_ID)
		{
			$this->db->query("DELETE FROM tb_pos WHERE POS_ID = '".$POS_ID."' AND POS_KODEKANTOR = '".$POS_KODEKANTOR."' ;");
		}
		
		
		function get_pos($berdasarkan,$cari)
		{
			$query = "SELECT * FROM tb_pos WHERE ".$berdasarkan." = '".$cari."'";
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
		
		function get_pos_byId($POS_ID,$POS_KODEKANTOR)
		{
			$query = "SELECT * FROM tb_pos WHERE POS_ID = '".$POS_ID."' AND POS_KODEKANTOR = '".$POS_KODEKANTOR."'";
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