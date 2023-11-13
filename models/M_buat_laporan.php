<?php
	class M_buat_laporan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_buat_laporan_limit($cari,$limit,$offset,$KEC_ID)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query("
				SELECT A.*
					,COALESCE(B.LAP_KODE,'') AS LAP_KODE 
					,COALESCE(B.LAP_NAMA,'') AS LAP_NAMA
					,COALESCE(C.JUMLAH,0) AS JUMLAH
					,COALESCE(D.DES_NAMA,'') AS DES_NAMA
					,COALESCE(E.img_url,'') AS img_url
					,COALESCE(E.img_file,'') AS img_file
				FROM tb_buat_laporan AS A
				LEFT JOIN tb_laporan AS B ON A.LAP_ID = B.LAP_ID
				LEFT JOIN
				(
					SELECT BLAP_ID,COUNT(INIT) AS JUMLAH
					FROM
					(
						SELECT BLAP_ID,DBLAP_SEQN AS DBLAP_SEQN,1 AS INIT FROM tb_d_buat_laporan 
						WHERE KEC_ID = '".$KEC_ID."'
						GROUP BY BLAP_ID,DBLAP_SEQN
					) AS AA GROUP BY BLAP_ID
				) AS C ON A.BLAP_ID = C.BLAP_ID
				LEFT JOIN tb_desa AS D ON A.DES_ID = D.DES_ID
				LEFT JOIN
				(
					SELECT 
						id
						,group_by
						,img_url
						,MAX(img_file) AS img_file
					FROM tb_images
					GROUP BY
						id
						,group_by
						,img_url
				) AS E ON A.BLAP_ID = E.id AND E.group_by = 'laporan'
				
				".$cari." 
				ORDER BY A.BLAP_DTDARI DESC
				LIMIT ".$offset.",".$limit
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
		
		function count_buat_laporan_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(A.BLAP_ID) AS JUMLAH 
				FROM tb_buat_laporan AS A
				LEFT JOIN tb_laporan AS B ON A.LAP_ID = B.LAP_ID ".$cari
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
			$KEC_ID
			,$DES_ID
			,$LAP_ID
			,$BLAP_JUDUL
			,$BLAP_PERIODE
			,$BLAP_DTDARI
			,$BLAP_DTSAMPAI
			,$BLAP_KET
			,$BLAP_USER
			,$BLAP_KKANTOR
			,$BLAP_SKANTOR
		)
		{
			$query = "
				INSERT INTO tb_buat_laporan
				(
					BLAP_ID
					,KEC_ID
					,DES_ID
					,LAP_ID
					,BLAP_JUDUL
					,BLAP_PERIODE
					,BLAP_DTDARI
					,BLAP_DTSAMPAI
					,BLAP_KET
					,BLAP_DTINS
					,BLAP_DTUPDT
					,BLAP_USER
					,BLAP_KKANTOR
					,BLAP_SKANTOR
				)
				VALUES
				(
					(
						SELECT CONCAT('BL',FRMTGL,ORD) AS BLAP_ID
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
								 COALESCE(MAX(CAST(RIGHT(BLAP_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								 From tb_buat_laporan
								 WHERE DATE(BLAP_DTINS) = DATE(NOW())
							 ) AS A
						 ) AS AA
					)
					,'".$KEC_ID."'
					,'".$DES_ID."'
					,'".$LAP_ID."'
					,'".$BLAP_JUDUL."'
					,'".$BLAP_PERIODE."'
					,'".$BLAP_DTDARI."'
					,'".$BLAP_DTSAMPAI."'
					,'".$BLAP_KET."'
					,NOW()
					,NOW()
					,'".$BLAP_USER."'
					,'".$BLAP_KKANTOR."'
					,'".$BLAP_SKANTOR."'
				);
			";
			
			$this->db->query($query);
		}
		
		function edit(
			$BLAP_ID
			,$KEC_ID
			,$DES_ID
			,$LAP_ID
			,$BLAP_JUDUL
			,$BLAP_PERIODE
			,$BLAP_DTDARI
			,$BLAP_DTSAMPAI
			,$BLAP_KET
			,$BLAP_USER
		)
		{
			$query = "
				UPDATE tb_buat_laporan SET
					KEC_ID = '". $KEC_ID ."'
					,DES_ID = '". $DES_ID ."'
					,LAP_ID = '". $LAP_ID ."'
					,BLAP_JUDUL = '". $BLAP_JUDUL ."'
					,BLAP_PERIODE = '". $BLAP_PERIODE ."'
					,BLAP_DTDARI = '". $BLAP_DTDARI ."'
					,BLAP_DTSAMPAI = '". $BLAP_DTSAMPAI ."'
					,BLAP_KET = '". $BLAP_KET ."'
					,BLAP_USER = '". $BLAP_USER ."'
					,BLAP_DTUPDT = NOW()
					WHERE BLAP_ID = '".$BLAP_ID."'
			";
			$this->db->query($query);
		}
		
		function update_hitung_d_laporan(
			$BLAP_ID
			,$HITUNG
		)
		{
			$query = "
				UPDATE tb_buat_laporan SET
					DBLAP_HITUNG = '". $HITUNG ."'
					WHERE BLAP_ID = '".$BLAP_ID."'
			";
			$this->db->query($query);
		}
		
		function hapus($BLAP_ID)
		{
			$this->db->query("DELETE FROM tb_buat_laporan WHERE BLAP_ID = '".$BLAP_ID."'");
		}
		
		function get_laporan_yang_sudah_buat($LAP_ID,$KEC_ID,$BLAP_ID)
		{
			$query = "SELECT * FROM tb_buat_laporan WHERE LAP_ID = '".$LAP_ID."' AND KEC_ID = '".$KEC_ID."' AND BLAP_ID <> '".$BLAP_ID."'";
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
		
		function get_laporan_yang_sudah_buat_by_periode($LAP_ID,$KEC_ID,$PERIODE)
		{
			$query = "SELECT * FROM tb_buat_laporan WHERE LAP_ID = '".$LAP_ID."' AND KEC_ID = '".$KEC_ID."' AND BLAP_PERIODE = '".$PERIODE."'";
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
		
		function get_buat_laporan_num_rows($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_buat_laporan', array($berdasarkan => $cari));
            if($query->num_rows() > 0)
            {
                return $query->num_rows();
            }
            else
            {
                return false;
            }
        }
		
		function get_buat_laporan($berdasarkan,$cari)
        {
            //$query = $this->db->get_where('tb_buat_laporan', array($berdasarkan => $cari));
			$query = "
					SELECT 
						A.* 
						,COALESCE(B.KEC_NAMA,'') AS KEC_NAMA
						,COALESCE(B.KEC_CAMAT,'') AS KEC_CAMAT
						,COALESCE(C.LAP_KODE,'') AS LAP_KODE
						,COALESCE(C.LAP_NAMA,'') AS LAP_NAMA
					FROM tb_buat_laporan AS A 
					LEFT JOIN tb_kec AS B ON A.KEC_ID = B.KEC_ID 
					LEFT JOIN tb_laporan AS C ON A.LAP_ID = C.LAP_ID
					WHERE A.".$berdasarkan." = '".$cari."'
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
		
		function get_buat_laporan_join_dengan_kecamatan($berdasarkan,$cari)
        {
			//$query = $this->db->get_where('tb_buat_laporan', array($berdasarkan => $cari));
			$query = "
					SELECT A.*
						,COALESCE(B.KEC_NAMA,'') AS NAMAKECAMATAN
						,COALESCE(B.KEC_CAMAT,'') AS CAMAT
					FROM tb_buat_laporan AS A
					LEFT JOIN tb_kec AS B ON A.KEC_ID = B.KEC_ID
					WHERE ".$berdasarkan." = '".$cari."';
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