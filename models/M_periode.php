<?php

	Class M_periode Extends CI_Model
	{
		function __construct()
		{
			parent::__construct();
		}
		
		function list_laporan_periode($cari,$KAT_PERIODE,$PERIODE)
		{
			$query = $this->db->query("CALL SP_LAP_ARSIPPERIODE('".$cari."','".$KAT_PERIODE."','".$PERIODE."');");
			mysqli_next_result( $this->db->conn_id );
			
			if($query->num_rows() > 0)
			{ 
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_periode_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query("
				SELECT * FROM tb_periode
				".$cari." 
				ORDER BY PER_KATEGORI DESC,COALESCE(PER_DTUPDT,PER_DTINS) DESC  LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_periode_limit($cari)
		{
			$query = $this->db->query("
				SELECT COUNT(PER_ID) AS JUMLAH
				FROM tb_periode
				".$cari);
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
					$PER_KODE
					,$PER_NAMA
					,$PER_KATEGORI
					,$PER_DARI
					,$PER_SAMPAI
					,$PER_KET
					,$PER_USER
					,$PER_KKANTOR
					,$PER_SKANTOR)
		{
			$query = "
				INSERT INTO tb_periode
				(
					PER_ID
					,PER_KODE
					,PER_NAMA
					,PER_KATEGORI
					,PER_DARI
					,PER_SAMPAI
					,PER_KET
					,PER_DTINS
					,PER_DTUPDT
					,PER_USER
					,PER_KKANTOR
					,PER_SKANTOR
				)
				VALUES
				(
					(
						SELECT CONCAT('PER',FRMTGL,ORD) AS PER_ID
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
								 COALESCE(MAX(CAST(RIGHT(PER_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								 From tb_periode
								 -- WHERE DATE_FORMAT(tgl_insert,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								 WHERE DATE(PER_DTINS) = DATE(NOW())
								 -- AND KEC_KKANTOR = 'KABCJR'
							 ) AS A
						 ) AS AA
					)
					
					,'".$PER_KODE."'
					,'".$PER_NAMA."'
					,'".$PER_KATEGORI."'
					,'".$PER_DARI."'
					,'".$PER_SAMPAI."'
					,'".$PER_KET."'
					,NOW()
					,NOW()
					,'".$PER_USER."'
					,'".$PER_KKANTOR."'
					,'".$PER_SKANTOR."'
				);
			";
			$this->db->query($query);
		}
		
		function edit(
					$PER_ID
					,$PER_NAMA
					,$PER_KATEGORI
					,$PER_DARI
					,$PER_SAMPAI
					,$PER_KET
					,$PER_USER
					)
		{
			$query = "
				UPDATE tb_periode SET
					PER_NAMA = '".$PER_NAMA."'
					,PER_KATEGORI = '".$PER_KATEGORI."'
					,PER_DARI = '".$PER_DARI."'
					,PER_SAMPAI = '".$PER_SAMPAI."'
					,PER_KET = '".$PER_KET."'
					,PER_DTUPDT = NOW()
					,PER_USER = '".$PER_USER."'
					WHERE PER_ID = '".$PER_ID."'
			";
			$this->db->query($query);
		}
		
		function hapus($id)
		{
			$this->db->query("DELETE FROM tb_periode WHERE PER_ID = '".$id."'");
		}
		
		
		function get_periode($berdasarkan,$cari)
        {
            //$query = $this->db->get_where('tb_laporan', array($berdasarkan => $cari));
			
			$query = "
				SELECT * FROM tb_periode
				WHERE ".$berdasarkan." = '".$cari."'
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