<?php
	class M_berita extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_berita_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
										SELECT 
											A.BRT_ID
											,A.KBRT_ID
											,A.BRT_KODE
											,A.BRT_NAMA
											,A.BRT_DTWRITE
											,A.BRT_ISI
											,A.BRT_KEYWORDS
											,A.BRT_DESC
											,A.BRT_LINKTAHUN
											,A.BRT_LINKBULAN
											,A.BRT_LINKTITLE
											,A.BRT_CREATED
											,A.BRT_DTINS
											,COALESCE(B.KBRT_NAMA,'') AS KBRT_NAMA
											,COALESCE(MAX(C.IMG_FILE),'') AS IMG_FILE
											,COALESCE(MAX(C.IMG_LINK),'') AS IMG_LINK
										FROM tb_berita AS A
										LEFT JOIN tb_kat_berita AS B ON A.KBRT_ID = B.KBRT_ID AND A.BRT_KODEKANTOR = B.KBRT_KODEKANTOR
										LEFT JOIN tb_images AS C ON A.BRT_ID = C.ID AND A.BRT_KODEKANTOR = C.IMG_KODEKANTOR AND C.IMG_GRUP = 'berita'
										".$cari." 
										GROUP BY
											A.BRT_ID
											,A.KBRT_ID
											,A.BRT_KODE
											,A.BRT_NAMA
											,A.BRT_DTWRITE
											,A.BRT_ISI
											,A.BRT_KEYWORDS
											,A.BRT_DESC
											,A.BRT_LINKTAHUN
											,A.BRT_LINKBULAN
											,A.BRT_LINKTITLE
											,A.BRT_CREATED
											,A.BRT_DTINS
											,COALESCE(B.KBRT_NAMA,'')
										ORDER BY A.BRT_DTWRITE DESC,BRT_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_berita_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.BRT_ID) AS JUMLAH 
										FROM tb_berita AS A
										LEFT JOIN tb_kat_berita AS B ON A.KBRT_ID = B.KBRT_ID AND A.BRT_KODEKANTOR = B.KBRT_KODEKANTOR
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
			$KBRT_ID,
			$BRT_KODE,
			$BRT_NAMA,
			$BRT_DTWRITE,
			$BRT_ISI,
			$BRT_KEYWORDS,
			$BRT_DESC,
			$BRT_LINKTITLE,
			$BRT_CREATED,
			$BRT_USERINS,
			$BRT_USERUPDT,
			$BRT_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_berita
				(
					BRT_ID,
					KBRT_ID,
					BRT_KODE,
					BRT_NAMA,
					BRT_DTWRITE,
					BRT_ISI,
					BRT_KEYWORDS,
					BRT_DESC,
					BRT_LINKTAHUN,
					BRT_LINKBULAN,
					BRT_LINKTITLE,
					BRT_CREATED,
					BRT_USERINS,
					BRT_USERUPDT,
					BRT_DTINS,
					BRT_DTUPDT,
					BRT_KODEKANTOR



				)
				VALUES
				(
					(
						SELECT CONCAT('BRT',FRMTGL,ORD) AS BRT_ID
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
								COALESCE(MAX(CAST(RIGHT(BRT_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_berita
								-- WHERE DATE_FORMAT(BRT_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(BRT_DTINS) = DATE(NOW())
								AND BRT_KODEKANTOR = '".$BRT_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$KBRT_ID."',
					'".$BRT_KODE."',
					'".$BRT_NAMA."',
					'".$BRT_DTWRITE."',
					'".$BRT_ISI."',
					'".$BRT_KEYWORDS."',
					'".$BRT_DESC."',
					DATE_FORMAT(NOW(),'%Y'),
					DATE_FORMAT(NOW(),'%m'),
					'".$BRT_LINKTITLE."',
					'".$BRT_CREATED."',
					'".$BRT_USERINS."',
					'".$BRT_USERUPDT."',
					NOW(),
					NOW(),
					'".$BRT_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$BRT_ID,
			$KBRT_ID,
			$BRT_KODE,
			$BRT_NAMA,
			$BRT_DTWRITE,
			$BRT_ISI,
			$BRT_KEYWORDS,
			$BRT_DESC,
			$BRT_LINKTITLE,
			$BRT_USERUPDT,
			$BRT_KODEKANTOR
		)
		{
			$query = "
					UPDATE tb_berita SET
						KBRT_ID = '".$KBRT_ID."',
						BRT_KODE = '".$BRT_KODE."',
						BRT_NAMA = '".$BRT_NAMA."',
						BRT_DTWRITE = '".$BRT_DTWRITE."',
						BRT_ISI = '".$BRT_ISI."',
						BRT_KEYWORDS = '".$BRT_KEYWORDS."',
						BRT_DESC = '".$BRT_DESC."',
						BRT_LINKTITLE = '".$BRT_LINKTITLE."',
						BRT_USERUPDT = '".$BRT_USERUPDT."',
						BRT_DTUPDT = NOW()
					WHERE BRT_KODEKANTOR = '".$BRT_KODEKANTOR."' AND BRT_ID = '".$BRT_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($BRT_KODEKANTOR,$BRT_ID)
		{
			$this->db->query("
						DELETE FROM tb_berita 
						WHERE BRT_KODEKANTOR = '".$BRT_KODEKANTOR."'
						AND BRT_ID = '".$BRT_ID."'
					");
		}
		
		
		function get_berita($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_berita', array($berdasarkan => $cari,'BRT_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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