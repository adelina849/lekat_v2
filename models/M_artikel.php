<?php
	class M_artikel extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_artikel_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
										SELECT 
											A.ART_ID
											,A.KART_ID
											,A.ART_KODE
											,A.ART_NAMA
											,A.ART_DTWRITE
											,A.ART_ISI
											,A.ART_KEYWORDS
											,A.ART_DESC
											,A.ART_LINKTAHUN
											,A.ART_LINKBULAN
											,A.ART_LINKTITLE
											,A.ART_CREATED
											,A.ART_DTINS
											,COALESCE(B.KART_NAMA,'') AS KART_NAMA
											,COALESCE(MAX(C.IMG_FILE),'') AS IMG_FILE
											,COALESCE(MAX(C.IMG_LINK),'') AS IMG_LINK
										FROM tb_artikel AS A
										LEFT JOIN tb_kat_artikel AS B ON A.KART_ID = B.KART_ID AND A.ART_KODEKANTOR = B.KART_KODEKANTOR
										LEFT JOIN tb_images AS C ON A.ART_ID = C.ID AND A.ART_KODEKANTOR = C.IMG_KODEKANTOR AND C.IMG_GRUP = 'artikel'
										".$cari." 
										GROUP BY
											A.ART_ID
											,A.KART_ID
											,A.ART_KODE
											,A.ART_NAMA
											,A.ART_DTWRITE
											,A.ART_ISI
											,A.ART_KEYWORDS
											,A.ART_DESC
											,A.ART_LINKTAHUN
											,A.ART_LINKBULAN
											,A.ART_LINKTITLE
											,A.ART_CREATED
											,A.ART_DTINS
											,COALESCE(B.KART_NAMA,'')
										ORDER BY A.ART_DTWRITE DESC, ART_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_artikel_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.ART_ID) AS JUMLAH 
										FROM tb_artikel AS A
										LEFT JOIN tb_kat_artikel AS B ON A.KART_ID = B.KART_ID AND A.ART_KODEKANTOR = B.KART_KODEKANTOR
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
			$KART_ID,
			$ART_KODE,
			$ART_NAMA,
			$ART_DTWRITE,
			$ART_ISI,
			$ART_KEYWORDS,
			$ART_DESC,
			$ART_LINKTITLE,
			$ART_CREATED,
			$ART_USERINS,
			$ART_USERUPDT,
			$ART_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_artikel
				(
					ART_ID,
					KART_ID,
					ART_KODE,
					ART_NAMA,
					ART_DTWRITE,
					ART_ISI,
					ART_KEYWORDS,
					ART_DESC,
					ART_LINKTAHUN,
					ART_LINKBULAN,
					ART_LINKTITLE,
					ART_CREATED,
					ART_USERINS,
					ART_USERUPDT,
					ART_DTINS,
					ART_DTUPDT,
					ART_KODEKANTOR



				)
				VALUES
				(
					(
						SELECT CONCAT('ART',FRMTGL,ORD) AS ART_ID
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
								COALESCE(MAX(CAST(RIGHT(ART_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_artikel
								-- WHERE DATE_FORMAT(ART_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(ART_DTINS) = DATE(NOW())
								AND ART_KODEKANTOR = '".$ART_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$KART_ID."',
					'".$ART_KODE."',
					'".$ART_NAMA."',
					'".$ART_DTWRITE."',
					'".$ART_ISI."',
					'".$ART_KEYWORDS."',
					'".$ART_DESC."',
					DATE_FORMAT(NOW(),'%Y'),
					DATE_FORMAT(NOW(),'%m'),
					'".$ART_LINKTITLE."',
					'".$ART_CREATED."',
					'".$ART_USERINS."',
					'".$ART_USERUPDT."',
					NOW(),
					NOW(),
					'".$ART_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$ART_ID,
			$KART_ID,
			$ART_KODE,
			$ART_NAMA,
			$ART_DTWRITE,
			$ART_ISI,
			$ART_KEYWORDS,
			$ART_DESC,
			$ART_LINKTITLE,
			$ART_USERUPDT,
			$ART_KODEKANTOR
		)
		{
			$query = "
					UPDATE tb_artikel SET
						KART_ID = '".$KART_ID."',
						ART_KODE = '".$ART_KODE."',
						ART_NAMA = '".$ART_NAMA."',
						ART_DTWRITE = '".$ART_DTWRITE."',
						ART_ISI = '".$ART_ISI."',
						ART_KEYWORDS = '".$ART_KEYWORDS."',
						ART_DESC = '".$ART_DESC."',
						ART_LINKTITLE = '".$ART_LINKTITLE."',
						ART_USERUPDT = '".$ART_USERUPDT."',
						ART_DTUPDT = NOW()
					WHERE ART_KODEKANTOR = '".$ART_KODEKANTOR."' AND ART_ID = '".$ART_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($ART_KODEKANTOR,$ART_ID)
		{
			$this->db->query("
						DELETE FROM tb_artikel 
						WHERE ART_KODEKANTOR = '".$ART_KODEKANTOR."'
						AND ART_ID = '".$ART_ID."'
					");
		}
		
		
		function get_artikel($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_artikel', array($berdasarkan => $cari,'ART_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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
