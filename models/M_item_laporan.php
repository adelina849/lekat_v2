<?php
	class M_item_laporan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function getMaxOrder($LAP_ID)
		{
			$query = "SELECT COALESCE(MAX(ILAP_ORDR),0) + 1 AS MAX_ORDR FROM tb_item_laporan WHERE LAP_ID = '".$LAP_ID."';";
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
		
		
		function list_item_laporan_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query("
				SELECT A.*,COALESCE(B.LAP_NAMA,'') AS LAP_NAMA 
				FROM tb_item_laporan AS A
				LEFT JOIN tb_laporan AS B ON A.LAP_ID = B.LAP_ID
				".$cari." ORDER BY ILAP_ORDR ASC,ILAP_NAMA ASC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_item_laporan_limit($cari)
		{
			$query = $this->db->query("
				SELECT COUNT(A.ILAP_ID) AS JUMLAH
				FROM tb_item_laporan AS A
				LEFT JOIN tb_laporan AS B ON A.LAP_ID = B.LAP_ID ".$cari);
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
					$LAP_ID
					,$ILAP_NAMA
					,$ILAP_TYPE
					,$ILAP_KET
					,$ILAP_ORDR
					,$ILAP_USER
					,$ILAP_KKANTOR
					,$ILAP_SKANTOR)
		{
			$query = "
				INSERT INTO tb_item_laporan
				(
					ILAP_ID
					,LAP_ID
					,ILAP_NAMA
					,ILAP_TYPE
					,ILAP_KET
					,ILAP_ORDR
					,ILAP_DTINS
					,ILAP_DTUPDT
					,ILAP_USER
					,ILAP_KKANTOR
					,ILAP_SKANTOR
				)
				VALUES
				(
					(
						SELECT CONCAT('IL',FRMTGL,ORD) AS ILAP_ID
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
								 -- COALESCE(MAX(CAST(RIGHT(ILAP_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								 COALESCE(MAX(CAST(RIGHT(ILAP_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								 From tb_item_laporan
								 -- WHERE DATE_FORMAT(tgl_insert,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								 -- WHERE DATE(ILAP_DTINS) = DATE(NOW()) AND 
								 WHERE LAP_ID = '".$LAP_ID."'
								 -- AND KEC_KKANTOR = 'KABCJR'
							 ) AS A
						 ) AS AA
					)
					,'".$LAP_ID."'
					,'".$ILAP_NAMA."'
					,'".$ILAP_TYPE."'
					,'".$ILAP_KET."'
					,'".$ILAP_ORDR."'
					,NOW()
					,NOW()
					,'".$ILAP_USER."'
					,'".$ILAP_KKANTOR."'
					,'".$ILAP_SKANTOR."'
				)
			";
			$this->db->query($query);
		}
		
		function edit(
					$ILAP_ID
					,$LAP_ID
					,$ILAP_NAMA
					,$ILAP_TYPE
					,$ILAP_KET
					,$ILAP_ORDR
					,$ILAP_USER)
		{
			$query = "
				UPDATE tb_item_laporan SET
				LAP_ID = '".$LAP_ID."'
				,ILAP_NAMA = '".$ILAP_NAMA."'
				,ILAP_TYPE = '".$ILAP_TYPE."'
				,ILAP_KET = '".$ILAP_KET."'
				,ILAP_ORDR = '".$ILAP_ORDR."'
				,ILAP_DTUPDT = NOW()
				,ILAP_USER = '".$ILAP_USER."'
				WHERE ILAP_ID = '".$ILAP_ID."'
			";
			$this->db->query($query);
		}
		
		function editOrderNaik($ILAP_ID,$LAP_ID,$ILAP_ORDR_BFR, $ILAP_ORDR_CUR)
		{
			$query = "UPDATE tb_item_laporan SET ILAP_ORDR = (ILAP_ORDR + 1) WHERE LAP_ID = '".$LAP_ID."' AND ILAP_ID <> '".$ILAP_ID."' AND ILAP_ORDR >= ".$ILAP_ORDR_CUR." AND ILAP_ORDR < ".$ILAP_ORDR_BFR." ;";
			
			$this->db->query($query);
		}
		
		function editOrderTurun($ILAP_ID,$LAP_ID,$ILAP_ORDR_BFR, $ILAP_ORDR_CUR)
		{
			$query = "UPDATE tb_item_laporan SET ILAP_ORDR = (ILAP_ORDR - 1) WHERE LAP_ID = '".$LAP_ID."' AND ILAP_ID <> '".$ILAP_ID."' AND ILAP_ORDR <= ".$ILAP_ORDR_CUR." AND ILAP_ORDR > ".$ILAP_ORDR_BFR." ;";
			 
			$this->db->query($query);
		}
		
		function hapus($ILAP_ID)
		{
			$this->db->query("DELETE FROM tb_item_laporan WHERE ILAP_ID = '".$ILAP_ID."'");
		}
		
		function get_item_laporan($berdasarkan,$cari)
        {
            //$query = $this->db->get_where('tb_item_laporan', array($berdasarkan => $cari));
			//$query = $this->db->query("SELECT * FROM tb_item_laporan WHERE ".$berdasarkan." = '".$cari."' ORDER BY ILAP_ORDR");
			
			$query = "
						SELECT 
							A.* 
							,COALESCE(B.LAP_JUMROW,0) AS LAP_JUMROW
						FROM tb_item_laporan AS A 
						LEFT JOIN tb_laporan AS B ON A.LAP_ID = B.LAP_ID
						WHERE A.".$berdasarkan." = '".$cari."';";
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
		
		function get_no_order_laporan($LAP_ID,$ILAP_ORDR)
        {
            //$query = $this->db->get_where('tb_item_laporan', array($berdasarkan => $cari));
			$query = $this->db->query("SELECT * FROM tb_item_laporan WHERE LAP_ID = '".$LAP_ID."' AND ILAP_ORDR = ".$ILAP_ORDR." ORDER BY ILAP_ORDR");
			
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