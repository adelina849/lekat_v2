<?php
	class M_klaporan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_klaporan_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query("
				SELECT * FROM tb_klaporan
				".$cari." ORDER BY KLAP_KODE ASC, KLAP_NAMA LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_klaporan_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(KLAP_ID) AS JUMLAH FROM tb_klaporan ".$cari);
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
					$KLAP_KODE
					,$KLAP_NAMA
					,$KLAP_KET
					,$KLAP_USER
					,$KLAP_KKANTOR
					,$KLAP_SKANTOR)
		{
			$query = "
				INSERT INTO tb_klaporan
				(
					KLAP_ID
					,KLAP_KODE
					,KLAP_NAMA
					,KLAP_KET
					,KLAP_DTINS
					,KLAP_DTUPDT
					,KLAP_USER
					,KLAP_KKANTOR
					,KLAP_SKANTOR
				)
				VALUES
				(
					(
						SELECT CONCAT('KL',FRMTGL,ORD) AS KLAP_ID
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
								 COALESCE(MAX(CAST(RIGHT(KLAP_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								 From tb_klaporan
								 -- WHERE DATE_FORMAT(tgl_insert,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								 WHERE DATE(KLAP_DTINS) = DATE(NOW())
								 -- AND KEC_KKANTOR = 'KABCJR'
							 ) AS A
						 ) AS AA
					)
					,'".$KLAP_KODE."'
					,'".$KLAP_NAMA."'
					,'".$KLAP_KET."'
					,NOW()
					,NOW()
					,'".$KLAP_USER."'
					,'".$KLAP_KKANTOR."'
					,'".$KLAP_SKANTOR."'
				);
			";
			$this->db->query($query);
		}
		
		function edit(
					$KLAP_ID
					,$KLAP_KODE
					,$KLAP_NAMA
					,$KLAP_KET
					,$KLAP_USER
					)
		{
			$query = "
				UPDATE tb_klaporan SET
					KLAP_KODE = '".$KLAP_KODE."'
					,KLAP_NAMA = '".$KLAP_NAMA."'
					,KLAP_KET = '".$KLAP_KET."'
					,KLAP_DTUPDT = NOW()
					,KLAP_USER = '".$KLAP_USER."'
					WHERE KLAP_ID = '".$KLAP_ID."'
			";
			$this->db->query($query);
		}
		
		function hapus($id)
		{
			$this->db->query("DELETE FROM tb_klaporan WHERE KLAP_ID = '".$id."'");
		}
		
		
		function get_klaporan($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_klaporan', array($berdasarkan => $cari));
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