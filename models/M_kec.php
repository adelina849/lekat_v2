<?php
	class M_kec extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_kecamatan_limit($cari,$limit,$offset)
		{
			
			$query = $this->db->query("
				SELECT A.*,COALESCE(B.JUMLAH,0) AS JUMLAH FROM tb_kec AS A
				LEFT JOIN
				(
					SELECT KEC_ID,COUNT(DES_ID) AS JUMLAH FROM tb_desa GROUP BY KEC_ID
				) AS B ON A.KEC_ID= B.KEC_ID
				".$cari." ORDER BY A.KEC_NAMA ASC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_kecamatan_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(A.KEC_ID) AS JUMLAH FROM tb_kec AS A ".$cari);
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
					$KEC_KODE
					,$KEC_NAMA
					,$KEC_CAMAT
					,$KEC_BUCAMAT
					,$KEC_SEKWILMAT
					,$KEC_MP
					,$KEC_TLP
					,$KEC_ALAMAT
					,$KEC_KET
					,$KEC_LONGI
					,$KEC_LATI
					,$KEC_IMG
					,$KEC_CAMIMG
					,$KEC_USER
					,$KEC_KKANTOR
					,$KEC_SKANTOR)
		{
			
			$query = "
				INSERT INTO tb_kec
				(
					KEC_ID
					,KEC_KODE
					,KEC_NAMA
					,KEC_CAMAT
					,KEC_BUCAMAT
					,KEC_SEKWILMAT
					,KEC_MP
					,KEC_TLP
					,KEC_ALAMAT
					,KEC_KET
					,KEC_LONGI
					,KEC_LATI
			";
			IF($KEC_IMG <> "")
			{
				$query = $query .",KEC_IMG";
			}
			
			IF($KEC_CAMIMG <> "")
			{
				$query = $query .",KEC_CAMIMG";
			}
			$query = $query ."
					,KEC_DTINS
					,KEC_DTUPDT
					,KEC_USER
					,KEC_KKANTOR
					,KEC_SKANTOR
				)
				VALUES
				(
					(
						SELECT CONCAT('KEC',FRMTGL,ORD) AS id_jabatan
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
								 COALESCE(MAX(CAST(RIGHT(KEC_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								 From tb_kec
								 -- WHERE DATE_FORMAT(tgl_insert,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								 WHERE DATE(KEC_DTINS) = DATE(NOW())
								 -- AND KEC_KKANTOR = 'KABCJR'
							 ) AS A
						 ) AS AA
					)
					,'".$KEC_KODE."'
					,'".$KEC_NAMA."'
					,'".$KEC_CAMAT."'
					,'".$KEC_BUCAMAT."'
					,'".$KEC_SEKWILMAT."'
					,'".$KEC_MP."'
					,'".$KEC_TLP."'
					,'".$KEC_ALAMAT."'
					,'".$KEC_KET."'
					,'".$KEC_LONGI."'
					,'".$KEC_LATI."'
				";
				
				IF($KEC_IMG <> "")
				{
					$query = $query .",'".$KEC_IMG."'";
				}
				
				IF($KEC_CAMIMG <> "")
				{
					$query = $query .",'".$KEC_CAMIMG."'";
				}
				
				$query = $query ."
					,NOW()
					,NOW()
					,'".$KEC_USER."'
					,'".$KEC_KKANTOR."'
					,'".$KEC_SKANTOR."'
				);
			";
			
			$this->db->query($query);
		}
		
		
		function edit(
					$KEC_ID
					,$KEC_KODE
					,$KEC_NAMA
					,$KEC_CAMAT
					,$KEC_BUCAMAT
					,$KEC_SEKWILMAT
					,$KEC_MP
					,$KEC_TLP
					,$KEC_EMAIL
					,$KEC_ALAMAT
					,$KEC_KET
					,$KEC_LONGI
					,$KEC_LATI
					,$KEC_IMG
					,$KEC_CAMIMG
					,$KEC_USER)
		{
			
			$query = "
				UPDATE tb_kec SET
					KEC_KODE = '".$KEC_KODE."'
					,KEC_NAMA = '".$KEC_NAMA."'
					,KEC_CAMAT = '".$KEC_CAMAT."'
					,KEC_BUCAMAT = '".$KEC_BUCAMAT."'
					,KEC_SEKWILMAT = '".$KEC_SEKWILMAT."'
					,KEC_MP = '".$KEC_MP."'
					,KEC_TLP = '".$KEC_TLP."'
					,KEC_EMAIL = '".$KEC_EMAIL."'
					,KEC_ALAMAT = '".$KEC_ALAMAT."'
					,KEC_KET = '".$KEC_KET."'
					,KEC_LONGI = '".$KEC_LONGI."'
					,KEC_LATI = '".$KEC_LATI."'
			";
			IF($KEC_IMG <> "")
			{
				$query = $query .",KEC_IMG = '".$KEC_IMG."'";
			}
			
			IF($KEC_CAMIMG <> "")
			{
				$query = $query .",KEC_CAMIMG = '".$KEC_CAMIMG."'";
			}
			
			$query = $query ."
					,KEC_DTUPDT = NOW()
					,KEC_USER = '".$KEC_USER."'
					WHERE KEC_ID = '".$KEC_ID."'
				;
			";
			
			$this->db->query($query);
		}
		
		function hapus($id)
		{
			$this->db->query("DELETE FROM tb_kec WHERE KEC_ID = '".$id."'");
		}
		
		
		function get_Kecamatan($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_kec', array($berdasarkan => $cari));
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