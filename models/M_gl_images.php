<?php
	class M_gl_images extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_images_limit($cari,$limit,$offset)
		{
			$query = "
				SELECT A.*
				-- , COALESCE(B.KIMG_NAMA,'') AS KIMG_NAMA
				FROM tb_images AS A
				-- LEFT JOIN tb_kat_images AS B ON A.KIMG_ID = B.KIMG_ID AND A.IMG_KODEKANTOR = B.KIMG_KODEKANTOR
				".$cari." ORDER BY A.tgl_ins DESC 
				LIMIT ".$offset.",".$limit;
			
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
		
		function count_images_limit($cari)
		{
			$query = "
				SELECT COUNT(A.id_images) AS JUMLAH
				FROM tb_images AS A
				-- LEFT JOIN tb_kat_images AS B ON A.KIMG_ID = B.KIMG_ID AND A.IMG_KODEKANTOR = B.KIMG_KODEKANTOR
				".$cari."
				";
				
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
		
		function simpan
		(	
			$id,
			$group_by,
			$img_nama,
			$img_file,
			$img_url,
			$ket_img,
			$user_ins,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_images
				(
					id_images,
					id,
					group_by,
					img_nama,
					img_file,
					img_url,
					ket_img,
					tgl_ins,
					tgl_updt,
					user_updt,
					user_ins,
					kode_kantor

				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_images
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
								COALESCE(MAX(CAST(RIGHT(id_images,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_images
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id."',
					'".$group_by."',
					'".$img_nama."',
					'".$img_file."',
					'".$img_url."',
					'".$ket_img."',
					NOW(),
					NOW(),
					'',
					'".$user_ins."',
					'".$kode_kantor."'

				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				//$this->M_gl_log->simpan_query($strquery);
				$this->db->query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_info_saja
		(
			$id_images,
			$id,
			$group_by,
			$img_nama,
			$ket_img,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
			UPDATE tb_images SET
			
				id = '".$id."',
				group_by = '".$group_by."',
				img_nama = '".$img_nama."',
				ket_img = '".$ket_img."',
				tgl_updt = NOW(),
				user_updt = '".$user_updt."'
			WHERE kode_kantor = '".$kode_kantor."' AND id_images = '".$id_images
			."'
			";
		
			
			/*SIMPAN DAN CATAT QUERY*/
				//$this->M_gl_log->simpan_query($strquery);
				$this->db->query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_with_images
		(
			$id_images,
			$id,
			$group_by,
			$img_nama,
			$ket_img,
			$img_file,
			$img_url,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
			UPDATE tb_images SET
			
				id = '".$id."',
				group_by = '".$group_by."',
				img_nama = '".$img_nama."',
				ket_img = '".$ket_img."',
				img_file = '".$img_file."',
				img_url = '".$img_url."',
				tgl_updt = NOW(),
				user_updt = '".$user_updt."'
			WHERE kode_kantor = '".$kode_kantor."' AND id_images = '".$id_images
			."'
			";
		
			
			/*SIMPAN DAN CATAT QUERY*/
				//$this->M_gl_log->simpan_query($strquery);
				$this->db->query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_images)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_images WHERE ".$berdasarkan." = '".$id_images."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				//$this->M_gl_log->simpan_query($strquery);
				$this->db->query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_id_images($kode_kantor)
		{
			$query = "
					SELECT CONCAT('".$kode_kantor."',FRMTGL,ORD) AS id_images
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
							COALESCE(MAX(CAST(RIGHT(id_images,5) AS UNSIGNED)) + 1,1) AS ORD
							From tb_images
							WHERE DATE(tgl_ins) = DATE(NOW())
							AND kode_kantor = '".$kode_kantor."'
						) AS A
					) AS AA
				";
				
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
		
		function get_images_cari($cari)
		{
			$query = "SELECT * FROM tb_images ".$cari."";
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
	}
?>