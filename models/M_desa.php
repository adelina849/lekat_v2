<?php
	class M_desa extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_desa_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query("SELECT A.*
									,COALESCE(B.KEC_NAMA,'') AS KEC_NAMA
									,COALESCE(B.KEC_CAMAT,'') AS KEC_CAMAT 
									,COALESCE(B.KEC_ALAMAT,'') AS KEC_ALAMAT 
									FROM tb_desa AS A LEFT JOIN tb_kec AS B ON A.KEC_ID = B.KEC_ID ".$cari." ORDER BY DES_NAMA ASC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_desa_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(DES_ID) AS JUMLAH FROM tb_desa AS A LEFT JOIN tb_kec AS B ON A.KEC_ID = B.KEC_ID ".$cari);
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
					$KEC_ID
					,$DES_NAMA
					,$DES_KADES
					,$DES_TLP
					,$DES_KET
					,$DES_ALAMAT
					,$DES_LONGI
					,$DES_LATI
					,$DES_USER
					,$DES_KKANTOR
					,$DES_SKANTOR)
		{
			$query = "
				INSERT INTO tb_desa
				(
					DES_ID
					,KEC_ID
					,DES_NAMA
					,DES_KADES
					,DES_TLP
					,DES_KET
					,DES_ALAMAT
					,DES_LONGI
					,DES_LATI
					,DES_DTINS
					,DES_DTUPDT
					,DES_USER
					,DES_KKANTOR
					,DES_SKANTOR
				)
				VALUES
				(
					(
						SELECT CONCAT('DES',FRMTGL,ORD) AS DES_ID
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
								 COALESCE(MAX(CAST(RIGHT(DES_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								 From tb_desa
								 -- WHERE DATE_FORMAT(tgl_insert,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								 WHERE DATE(DES_DTINS) = DATE(NOW())
								 -- AND KEC_KKANTOR = 'KABCJR'
							 ) AS A
						 ) AS AA
					)
					,'".$KEC_ID."'
					,'".$DES_NAMA."'
					,'".$DES_KADES."'
					,'".$DES_TLP."'
					,'".$DES_KET."'
					,'".$DES_ALAMAT."'
					,'".$DES_LONGI."'
					,'".$DES_LATI."'
					,NOW()
					,NOW()
					,'".$DES_USER."'
					,'".$DES_KKANTOR."'
					,'".$DES_SKANTOR."'
				)
			";
			$this->db->query($query);
		}
		
		function edit(
					$DES_ID
					,$DES_NAMA
					,$DES_KADES
					,$DES_TLP
					,$DES_KET
					,$DES_ALAMAT
					,$DES_LONGI
					,$DES_LATI
					,$DES_USER)
		{
			$query = "
				UPDATE tb_desa SET
				DES_NAMA = '".$DES_NAMA."'
				,DES_KADES = '".$DES_KADES."'
				,DES_TLP = '".$DES_TLP."'
				,DES_KET = '".$DES_KET."'
				,DES_ALAMAT = '".$DES_ALAMAT."'
				,DES_LONGI = '".$DES_LONGI."'
				,DES_LATI = '".$DES_LATI."'
				,DES_DTUPDT = NOW()
				,DES_USER = '".$DES_USER."'
				WHERE DES_ID = '".$DES_ID."'
			";
			$this->db->query($query);
		}
		
		function hapus($id)
		{
			$this->db->query("DELETE FROM tb_desa WHERE DES_ID = '".$id."'");
		}
		
		function get_desa($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_desa', array($berdasarkan => $cari));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_desa2($KEC_ID,$DES_NAMA)
        {
            $query = $this->db->get_where('tb_desa', array('KEC_ID' => $KEC_ID, 'DES_NAMA' => $DES_NAMA));
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