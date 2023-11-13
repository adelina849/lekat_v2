<?php
	class M_d_buat_laporan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_d_buat_laporan_proc($KEC_ID,$LAP_ID,$BLAP_ID)
		{
			if($LAP_ID == "KL2018112400002")
			{
				$query = $this->db->query("CALL SP_SHOLAT_SUBUH ('".$BLAP_ID."','".$KEC_ID."');");
			}
			else
			{
				$query = $this->db->query("CALL SP_LISTDETAILBUATLAPORAN ('".$KEC_ID."','".$LAP_ID."','".$BLAP_ID."');");
			}
			mysqli_next_result( $this->db->conn_id );
			
			if($query->num_rows() > 0)
			{ 
				//$query->next_result();
				//	$query->free_result();

				return $query;
			}
			else
			{
				return false;
			}
		}
		
		
		function list_d_buat_laporan_limit($cari,$limit,$offset)
		{
			
			$query = $this->db->query("
					SELECT
						A.DBLAP_VALUE
						,COALESCE(B.ILAP_NAMA,'') AS ILAP_NAMA
					FROM tb_d_buat_laporan AS A 
					-- LEFT JOIN (SELECT LAP_ID,ILAP_NAMA FROM tb_item_laporan GROUP BY ILAP_NAMA,LAP_ID) AS B ON A.LAP_ID = B.LAP_ID
					LEFT JOIN tb_item_laporan AS B ON A.LAP_ID = B.LAP_ID AND A.ILAP_ID = B.ILAP_ID
					-- WHERE A.BLAP_ID = 'BL2017101500001'
					".$cari."
					ORDER BY B.ILAP_ORDR
					LIMIT ".$offset.",".$limit."
				");
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_d_buat_laporan_limit_for_table($cari,$limit,$offset)
		{
			
			$query = $this->db->query("
					SELECT
						A.DBLAP_VALUE
						,A.DBLAP_SEQN
						,COALESCE(B.ILAP_NAMA,'') AS ILAP_NAMA
					FROM tb_d_buat_laporan AS A 
					-- LEFT JOIN (SELECT LAP_ID,ILAP_NAMA FROM tb_item_laporan GROUP BY ILAP_NAMA,LAP_ID) AS B ON A.LAP_ID = B.LAP_ID
					LEFT JOIN tb_item_laporan AS B ON A.LAP_ID = B.LAP_ID AND A.ILAP_ID = B.ILAP_ID
					-- WHERE A.BLAP_ID = 'BL2017101500001'
					".$cari."
					ORDER BY A.DBLAP_ID
					LIMIT ".$offset.",".$limit."
				");
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_d_buat_laporan_isi_edit($BLAP_ID,$SEQN)
		{
			
			$query = $this->db->query("
					SELECT 
						A.BLAP_ID,A.DBLAP_ID,A.ILAP_ID,A.DBLAP_SEQN,A.DBLAP_VALUE,A.KEC_ID 
						,COALESCE(B.ILAP_NAMA,'') AS ILAP_NAMA
						,COALESCE(B.ILAP_TYPE,'') AS ILAP_TYPE
					FROM tb_d_buat_laporan AS A
					INNER JOIN tb_item_laporan AS B ON A.ILAP_ID = B.ILAP_ID
					WHERE A.BLAP_ID = '".$BLAP_ID."' AND A.DBLAP_SEQN = '".$SEQN."'
					;
				");
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function get_Seqn_d_laporan($BLAP_ID)
		{
			$query = $this->db->query("
					SELECT (MAX(DBLAP_SEQN)+1) AS SEQN FROM tb_d_buat_laporan AS A WHERE BLAP_ID = '".$BLAP_ID."'
				");
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		// function count_kecamatan_limit($cari)
		// {
			// $query = $this->db->query("SELECT COUNT(A.KEC_ID) AS JUMLAH FROM tb_kec AS A ".$cari);
			// if($query->num_rows() > 0)
			// {
				// return $query->row();
			// }
			// else
			// {
				// return false;
			// }
		// }
		
		function simpan(
					$BLAP_ID
					,$LAP_ID
					,$KEC_ID
					,$ILAP_ID
					,$DBLAP_SEQN
					,$DBLAP_VALUE
					,$DBLAP_USER
					,$DBLAP_KKANTOR
					,$DBLAP_SKANTOR)
		{
			
			$query = "
				INSERT INTO tb_d_buat_laporan
				(
					DBLAP_ID
					,BLAP_ID
					,LAP_ID
					,KEC_ID
					,ILAP_ID
					,DBLAP_SEQN
					,DBLAP_VALUE
					,DBLAP_DTINS
					,DBLAP_DTUPDT
					,DBLAP_USER
					,DBLAP_KKANTOR
					,DBLAP_SKANTOR
				)
				VALUES
				(
					(
						SELECT CONCAT('DBL',FRMTGL,ORD) AS DBLAP_ID
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
								 COALESCE(MAX(CAST(RIGHT(DBLAP_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								 From tb_d_buat_laporan
								 -- WHERE DATE_FORMAT(tgl_insert,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								 WHERE DATE(DBLAP_DTINS) = DATE(NOW())
							 ) AS A
						 ) AS AA
					)
					,'".$BLAP_ID."'
					,'".$LAP_ID."'
					,'".$KEC_ID."'
					,'".$ILAP_ID."'
					,'".$DBLAP_SEQN."'
					,'".$DBLAP_VALUE."'
					,NOW()
					,NOW()
					,'".$DBLAP_USER."'
					,'".$DBLAP_KKANTOR."'
					,'".$DBLAP_SKANTOR."'
				)
			";
			
			$this->db->query($query);
		}
		
		
		// function edit(
					// $KEC_ID
					// ,$KEC_KODE
					// ,$KEC_NAMA
					// ,$KEC_CAMAT
					// ,$KEC_SEKWILMAT
					// ,$KEC_MP
					// ,$KEC_TLP
					// ,$KEC_EMAIL
					// ,$KEC_ALAMAT
					// ,$KEC_KET
					// ,$KEC_LONGI
					// ,$KEC_LATI
					// ,$KEC_IMG
					// ,$KEC_CAMIMG
					// ,$KEC_USER)
		// {
			
			// $query = "
				// UPDATE tb_kec SET
					// KEC_KODE = '".$KEC_KODE."'
					// ,KEC_NAMA = '".$KEC_NAMA."'
					// ,KEC_CAMAT = '".$KEC_CAMAT."'
					// ,KEC_SEKWILMAT = '".$KEC_SEKWILMAT."'
					// ,KEC_MP = '".$KEC_MP."'
					// ,KEC_TLP = '".$KEC_TLP."'
					// ,KEC_EMAIL = '".$KEC_EMAIL."'
					// ,KEC_ALAMAT = '".$KEC_ALAMAT."'
					// ,KEC_KET = '".$KEC_KET."'
					// ,KEC_LONGI = '".$KEC_LONGI."'
					// ,KEC_LATI = '".$KEC_LATI."'
			// ";
			// IF($KEC_IMG <> "")
			// {
				// $query = $query .",KEC_IMG = '".$KEC_IMG."'";
			// }
			
			// IF($KEC_CAMIMG <> "")
			// {
				// $query = $query .",KEC_CAMIMG = '".$KEC_CAMIMG."'";
			// }
			
			// $query = $query ."
					// ,KEC_DTUPDT = NOW()
					// ,KEC_USER = '".$KEC_USER."'
					// WHERE KEC_ID = '".$KEC_ID."'
				// ;
			// ";
			
			// $this->db->query($query);
		// }
		
		function hapus($BLAP_ID,$SEQN)
		{
			$this->db->query("DELETE FROM tb_d_buat_laporan WHERE BLAP_ID = '".$BLAP_ID."' AND DBLAP_SEQN = '".$SEQN."'");
		}
		
		function hapus_all($BLAP_ID)
		{
			$this->db->query("DELETE FROM tb_d_buat_laporan WHERE BLAP_ID = '".$BLAP_ID."'");
		}
		
		function copy_laporan($BLAP_ID_CUR,$BLAP_ID_BFR,$DBLAP_USER)
		{
			$query = "
				INSERT INTO tb_d_buat_laporan 
				(
					DBLAP_ID
					,BLAP_ID
					,LAP_ID
					,KEC_ID
					,ILAP_ID
					,DBLAP_SEQN
					,DBLAP_VALUE
					,DBLAP_DTINS
					,DBLAP_DTUPDT
					,DBLAP_USER
					,DBLAP_KKANTOR
					,DBLAP_SKANTOR
				)
				SELECT
					CONCAT(NO_ROW,ORD) AS DBLAP_ID
					,BLAP_ID
					,LAP_ID
					,KEC_ID
					,ILAP_ID
					,DBLAP_SEQN
					,DBLAP_VALUE
					,DBLAP_DTINS
					,DBLAP_DTUPDT
					,DBLAP_USER
					,DBLAP_KKANTOR
					,DBLAP_SKANTOR
					
				FROM
				(
					SELECT
						*
						,CASE
							 WHEN (MAX_NO + row_number >= 10 AND MAX_NO + row_number < 99) THEN CONCAT('000',CAST(MAX_NO + row_number AS CHAR))
							 WHEN (MAX_NO + row_number >= 100 AND MAX_NO + row_number < 999) THEN CONCAT('00',CAST(MAX_NO + row_number AS CHAR))
							 WHEN (MAX_NO + row_number >= 1000 AND MAX_NO + row_number < 9999) THEN CONCAT('0',CAST(MAX_NO + row_number AS CHAR))
							 WHEN MAX_NO + row_number >= 10000 THEN CAST(MAX_NO + row_number AS CHAR)
							 ELSE CONCAT('0000',CAST(MAX_NO + row_number AS CHAR))
							 END As ORD
					FROM
					(
						SELECT
							-- @curRow := @curRow + 1 AS row_number
							@curRow :=  CAST(@curRow + 1 AS UNSIGNED) AS row_number
							,
							(
								SELECT COALESCE(MAX(CAST(RIGHT(DBLAP_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_d_buat_laporan
								-- WHERE DATE_FORMAT(tgl_insert,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(DBLAP_DTINS) = DATE(NOW() )
							) AS MAX_NO
							,
							(
								-- SELECT CONCAT('DBL',FRMTGL,ORD) AS DBLAP_ID
								SELECT CONCAT('DBL',FRMTGL) AS DBLAP_ID
								 From
								 (
									 SELECT CONCAT(Y,M,D) AS FRMTGL
									 From
									 (
										 SELECT
										 CAST(LEFT(NOW(),4) AS CHAR) AS Y,
										 CAST(MID(NOW(),6,2) AS CHAR) AS M,
										 MID(NOW(),9,2) AS D
									 ) AS A
								 ) AS AA
							) AS NO_ROW
							,'".$BLAP_ID_CUR."' AS BLAP_ID
							,LAP_ID
							,KEC_ID
							,ILAP_ID
							,DBLAP_SEQN
							,DBLAP_VALUE,NOW() AS DBLAP_DTINS,NOW() AS DBLAP_DTUPDT
							,'".$DBLAP_USER."' AS DBLAP_USER
							,DBLAP_KKANTOR
							,DBLAP_SKANTOR
						FROM tb_d_buat_laporan 
						JOIN    (SELECT @curRow := 0) r
						WHERE BLAP_ID = '".$BLAP_ID_BFR."'
						ORDER BY DBLAP_ID ASC
					) AS AA
				) AS AAA
				;
				
			";
			
			$this->db->query($query);
		}
		
		
		// function get_Kecamatan($berdasarkan,$cari)
        // {
            // $query = $this->db->get_where('tb_kec', array($berdasarkan => $cari));
            // if($query->num_rows() > 0)
            // {
                // return $query;
            // }
            // else
            // {
                // return false;
            // }
        // }
	}
?>