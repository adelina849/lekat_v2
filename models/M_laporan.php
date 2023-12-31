<?php
	class M_laporan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function view_query_general($query)
		{
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
		
		
		function list_laporan_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query("
				SELECT A.*
					,COALESCE(B.KLAP_KODE,'') AS KLAP_KODE
					,COALESCE(B.KLAP_NAMA,'') AS KLAP_NAMA
					,COALESCE(C.JUMLAH,0) AS JUMLAH
					,COALESCE(D.nama_karyawan,'-') AS NAMA_PIC
				FROM tb_laporan AS A 
				LEFT JOIN tb_klaporan AS B ON A.KLAP_ID = B.KLAP_ID
				LEFT JOIN
				(
					SELECT LAP_ID,COUNT(ILAP_ID) AS JUMLAH FROM tb_item_laporan GROUP BY LAP_ID
				) AS C ON A.LAP_ID = C.LAP_ID
				LEFT JOIN tb_karyawan AS D ON A.LAP_PJ = D.id_karyawan
				
				".$cari." ORDER BY A.LAP_DTINS DESC,COALESCE(B.KLAP_KODE,'') ASC, A.LAP_KODE ASC, A.LAP_NAMA ASC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_laporan_limit($cari)
		{
			$query = $this->db->query("
				SELECT COUNT(A.LAP_ID) AS JUMLAH
				FROM tb_laporan AS A 
				LEFT JOIN tb_klaporan AS B ON A.KLAP_ID = B.KLAP_ID ".$cari);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function list_laporan_limit_perkecamatan($id_kecamatan,$cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query("
				SELECT
					A.LAP_ID
					,A.KLAP_ID
					,A.LAP_KODE
					,A.LAP_NAMA
					,A.LAP_PERIODE
					,A.LAP_KET
					,A.URL_LAP
					,A.LAP_DTINS
					,COUNT(B.BLAP_ID) AS JUMLAH
				FROM tb_laporan AS A 
				LEFT JOIN tb_buat_laporan AS B ON A.LAP_ID = B.LAP_ID AND B.BLAP_KKANTOR = '".$id_kecamatan."'
				".$cari."
				GROUP BY A.LAP_ID
					,A.KLAP_ID
					,A.LAP_KODE
					,A.LAP_NAMA
					,A.LAP_PERIODE
					,A.LAP_KET
					,A.LAP_DTINS
				ORDER BY A.LAP_KODE ASC, A.LAP_NAMA ASC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function simpan(
					$KLAP_ID
					,$LAP_KODE
					,$LAP_NAMA
					,$LAP_PERIODE
					,$LAP_DASAR_HUKUM
					,$LAP_JUMROW
					,$LAP_PJ
					,$LAP_KET
					,$LAP_ISPERDESA
					,$URL_LAP
					,$LAP_USER
					,$LAP_KKANTOR
					,$LAP_SKANTOR)
		{
			$query = "
				INSERT INTO tb_laporan
				(
					LAP_ID
					,KLAP_ID
					,LAP_KODE
					,LAP_NAMA
					,LAP_PERIODE
					,LAP_DASAR_HUKUM
					,LAP_JUMROW
					,LAP_PJ
					,LAP_KET
					,LAP_ISPERDESA
					,URL_LAP
					,LAP_DTINS
					,LAP_DTUPDT
					,LAP_USER
					,LAP_KKANTOR
					,LAP_SKANTOR
				)
				VALUES
				(
					(
						SELECT CONCAT('KL',FRMTGL,ORD) AS LAP_ID
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
								 COALESCE(MAX(CAST(RIGHT(LAP_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								 From tb_laporan
								 -- WHERE DATE_FORMAT(tgl_insert,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								 WHERE DATE(LAP_DTINS) = DATE(NOW())
								 -- AND KEC_KKANTOR = 'KABCJR'
							 ) AS A
						 ) AS AA
					)
					,'".$KLAP_ID."'
					,'".$LAP_KODE."'
					,'".$LAP_NAMA."'
					,'".$LAP_PERIODE."'
					,'".$LAP_DASAR_HUKUM."'
					,'".$LAP_JUMROW."'
					,'".$LAP_PJ."'
					,'".$LAP_KET."'
					,'".$LAP_ISPERDESA."'
					,'".$URL_LAP."'
					,NOW()
					,NOW()
					,'".$LAP_USER."'
					,'".$LAP_KKANTOR."'
					,'".$LAP_SKANTOR."'
				);
			";
			$this->db->query($query);
		}
		
		function edit(
					$LAP_ID
					,$KLAP_ID
					,$LAP_KODE
					,$LAP_NAMA
					,$LAP_PERIODE
					,$LAP_DASAR_HUKUM
					,$LAP_JUMROW
					,$LAP_PJ
					,$LAP_KET
					,$LAP_ISPERDESA
					,$URL_LAP
					,$LAP_USER
					)
		{
			$query = "
				UPDATE tb_laporan SET
					KLAP_ID = '".$KLAP_ID."'
					,LAP_KODE = '".$LAP_KODE."'
					,LAP_NAMA = '".$LAP_NAMA."'
					,LAP_PERIODE = '".$LAP_PERIODE."'
					,LAP_DASAR_HUKUM = '".$LAP_DASAR_HUKUM."'
					,LAP_JUMROW = '".$LAP_JUMROW."'
					,LAP_PJ = '".$LAP_PJ."'
					,LAP_KET = '".$LAP_KET."'
					,LAP_ISPERDESA = '".$LAP_ISPERDESA."'
					,URL_LAP = '".$URL_LAP."'
					,LAP_DTUPDT = NOW()
					,LAP_USER = '".$LAP_USER."'
					WHERE LAP_ID = '".$LAP_ID."'
			";
			$this->db->query($query);
		}
		
		function ubah_catatan_buat_laporan(
			$KEC_ID,
			$LAP_ID,
			$BLAP_PERIODE,
			$BLAP_CATATAN
		)
		{
			
			$query = "
					UPDATE tb_buat_laporan 
						SET 
							BLAP_CATATAN='".$BLAP_CATATAN."' 
							,BLAP_USER_CATATAN = '".$this->session->userdata('ses_nama_karyawan')."'
						WHERE KEC_ID = '".$KEC_ID."' AND LAP_ID = '".$LAP_ID."' AND BLAP_PERIODE = '".$BLAP_PERIODE."'";
			
			$this->db->query($query);
		}
		
		function hapus($id)
		{
			$this->db->query("DELETE FROM tb_laporan WHERE LAP_ID = '".$id."'");
		}
		
		
		function get_laporan($berdasarkan,$cari)
        {
            //$query = $this->db->get_where('tb_laporan', array($berdasarkan => $cari));
			
			$query = "
				SELECT A.*
					,COALESCE(B.KLAP_KODE,'') AS KLAP_KODE 
					,COALESCE(B.KLAP_NAMA,'') AS KLAP_NAMA
				FROM tb_laporan AS A LEFT JOIN tb_klaporan AS B 
				ON A.KLAP_ID = B.KLAP_ID 
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
		
		
		function get_laporan_perkecamatan($KEC_ID,$cari)
        {
            //$query = $this->db->get_where('tb_laporan', array($berdasarkan => $cari));
			
			$query = "
				SELECT A.*
					,COALESCE(B.KLAP_KODE,'') AS KLAP_KODE 
					,COALESCE(B.KLAP_NAMA,'') AS KLAP_NAMA
					,COALESCE(C.JUMLAH,0) AS JUMLAH
				FROM tb_laporan AS A 
				LEFT JOIN tb_klaporan AS B ON A.KLAP_ID = B.KLAP_ID
				LEFT JOIN
				(
					SELECT LAP_ID,COUNT(LAP_ID) AS JUMLAH FROM tb_buat_laporan WHERE BLAP_KKANTOR = '".$KEC_ID."' GROUP BY LAP_ID
				) AS C ON A.LAP_ID =  C.LAP_ID
				".$cari."
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
		
		function list_persen_laporan_perkecamatan($KEC_ID,$CARI)
		{
			$query = "
				SELECT *
				FROM
				(
					SELECT
						CONCAT(A.PER_KODE,COALESCE(B.LAP_ID) ) AS CONLAP
						,A.PER_ID,A.PER_KODE,A.PER_NAMA,A.PER_KATEGORI
						,B.LAP_KODE,B.LAP_NAMA
						,COALESCE(MAX(C.DBLAP_HITUNG),0) AS DBLAP_HITUNG
						,
						CASE WHEN C.KEC_ID IS NOT NULL THEN
							'SUDAH'
						ELSE
							'BELUM'
						END AS STATUS_LAPORAN
						,COALESCE(C.BLAP_CATATAN,'') AS BLAP_CATATAN
						,COALESCE(C.KEC_ID,'') AS KEC_ID
						,COALESCE(B.LAP_ID,'') AS LAP_ID
						,COALESCE(C.BLAP_PERIODE,'') AS BLAP_PERIODE
						,COALESCE(C.BLAP_USER_CATATAN,'') AS BLAP_USER_CATATAN
					FROM tb_periode AS A 
					LEFT JOIN tb_laporan AS B ON A.PER_KATEGORI = B.LAP_PERIODE AND B.URL_LAP = ''
					
					LEFT JOIN tb_buat_laporan AS C 
						ON CONCAT(A.PER_KODE,COALESCE(B.LAP_ID) ) = CONCAT(COALESCE(C.BLAP_PERIODE,''),COALESCE(C.LAP_ID,''))
						AND C.KEC_ID = '".$KEC_ID."'
					
					LEFT JOIN tb_klaporan AS D ON B.KLAP_ID = D.KLAP_ID
						
					WHERE D.KLAP_ISAKTIF = 0 AND  NOW() >= A.PER_DARI  AND NOW() <= A.PER_SAMPAI AND CONCAT(A.PER_KODE,COALESCE(B.LAP_ID) ) IS NOT NULL AND B.LAP_NAMA LIKE '%".$CARI."%'
					GROUP BY
						CONCAT(A.PER_KODE,COALESCE(B.LAP_ID) )
						,A.PER_ID,A.PER_KODE,A.PER_NAMA,A.PER_KATEGORI
						,B.LAP_KODE,B.LAP_NAMA,C.KEC_ID
						,COALESCE(C.BLAP_CATATAN,''),COALESCE(B.LAP_ID,'')
						,COALESCE(C.BLAP_PERIODE,'')
						,COALESCE(C.BLAP_USER_CATATAN,'')
						
				) AS A
				ORDER BY PER_KATEGORI,LAP_NAMA,PER_KODE,STATUS_LAPORAN ASC";
				
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
		
		function list_arsip_persen_laporan_perkecamatan($KEC_ID,$CARI,$KAT_PERIODE,$PERIODE)
		{
			$query = "
				SELECT *
				FROM
				(
					SELECT 
						CONCAT(A.PER_KODE,COALESCE(B.LAP_ID) ) AS CONLAP
						,A.PER_ID,A.PER_KODE,A.PER_NAMA,A.PER_KATEGORI
						,B.LAP_KODE,B.LAP_NAMA
						,COALESCE(MAX(C.DBLAP_HITUNG),0) AS DBLAP_HITUNG
						,
						CASE WHEN C.KEC_ID IS NOT NULL THEN
							'SUDAH'
						ELSE
							'BELUM'
						END AS STATUS_LAPORAN
						,COALESCE(B.LAP_ID,'') AS LAP_ID
						,COALESCE(B.LAP_PERIODE,'') AS LAP_PERIODE
						,COALESCE(C.KEC_ID,'') AS KEC_ID
						,COALESCE(C.BLAP_PERIODE,'') AS BLAP_PERIODE
						,COALESCE(C.BLAP_CATATAN,'') AS BLAP_CATATAN
					FROM tb_periode AS A 
					LEFT JOIN tb_laporan AS B ON A.PER_KATEGORI = B.LAP_PERIODE
					LEFT JOIN tb_buat_laporan AS C 
						ON CONCAT(A.PER_KODE,COALESCE(B.LAP_ID) ) = CONCAT(COALESCE(C.BLAP_PERIODE,''),COALESCE(C.LAP_ID,''))
						AND C.KEC_ID = '".$KEC_ID."'
					-- WHERE NOW() >= A.PER_DARI  AND NOW() <= A.PER_SAMPAI 
					WHERE A.PER_KATEGORI = '".$KAT_PERIODE."'  AND A.PER_KODE = '".$PERIODE."'
					AND CONCAT(A.PER_KODE,COALESCE(B.LAP_ID) ) IS NOT NULL 
					AND B.LAP_ISAKTIF = 0
					AND B.LAP_NAMA LIKE '%".$CARI."%'
					GROUP BY
						CONCAT(A.PER_KODE,COALESCE(B.LAP_ID) )
						,A.PER_ID,A.PER_KODE,A.PER_NAMA,A.PER_KATEGORI
						,B.LAP_KODE,B.LAP_NAMA
						,COALESCE(B.LAP_ID,'')
						,COALESCE(B.LAP_PERIODE,'')
						,COALESCE(C.KEC_ID,'')
						,COALESCE(C.BLAP_PERIODE,'')
						,COALESCE(C.BLAP_CATATAN,'')
				) AS A
				ORDER BY PER_KATEGORI,LAP_NAMA,PER_KODE,STATUS_LAPORAN ASC";
				
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