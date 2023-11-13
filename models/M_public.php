<?php
	class M_public extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_main_menu($cari)
		{
			$query = "
					SELECT 
						A.*
						,COALESCE(B.HAL_LINKTITLE,'') AS HAL_LINKTITLE
						,COALESCE(B.MENU_KEYWORDS,'') AS MENU_KEYWORDS
						,COALESCE(B.MENU_DESC,'') AS MENU_DESC
						,
						CASE 
						WHEN A.MENU_ISPUNYAHAL = 0 THEN
							COALESCE(B.HAL_LINKTITLE,'')
						ELSE
							COALESCE(A.MENU_LINK,'')
						END AS LINK
					FROM tb_menu AS A 
					LEFT JOIN tb_halaman AS B ON A.MENU_ID = B.MENU_ID AND A.MENU_KODEKANTOR = B.HAL_KODEKANTOR 
					LEFT JOIN tb_menu AS C ON A.MENU_MAINID = C.MENU_KODE AND A.MENU_KODEKANTOR = C.MENU_KODEKANTOR
					".$cari."
					ORDER BY A.MENU_ORDER ASC;"
					;
			
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
		
		function list_images($cari,$offset,$limit)
		{
			$query = "	
						SELECT * FROM tb_images AS A
						LEFT JOIn tb_kat_images AS B 
						ON A.KIMG_ID = B.KIMG_ID 
						AND A.IMG_KODEKANTOR = B.KIMG_KODEKANTOR
						".$cari."
						LIMIT ".$offset.",".$limit."
						;
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
		
		function detail_halaman($cari)
		{
			$query = "	
						SELECT * FROM tb_halaman ".$cari.";
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
		
		function list_artikel($cari,$orderby,$offset,$limit)
		{
			$query = "
						SELECT 
							A.ART_ID
							,A.KART_ID
							,A.ART_KODE
							,A.ART_NAMA
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
							,A.ART_ISI
							,A.ART_KEYWORDS
							,A.ART_DESC
							,A.ART_LINKTAHUN
							,A.ART_LINKBULAN
							,A.ART_LINKTITLE
							,A.ART_CREATED
							,A.ART_DTINS
							,COALESCE(B.KART_NAMA,'')
						ORDER BY ".$orderby." LIMIT ".$offset.",".$limit.";
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
		
		function list_berita($cari,$orderby,$offset,$limit)
		{
			$query = "
						SELECT 
							A.BRT_ID
							,A.KBRT_ID
							,A.BRT_KODE
							,A.BRT_NAMA
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
							,A.BRT_ISI
							,A.BRT_KEYWORDS
							,A.BRT_DESC
							,A.BRT_LINKTAHUN
							,A.BRT_LINKBULAN
							,A.BRT_LINKTITLE
							,A.BRT_CREATED
							,A.BRT_DTINS
							,COALESCE(B.KBRT_NAMA,'')
						ORDER BY ".$orderby." LIMIT ".$offset.",".$limit.";
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
		
	}
?>