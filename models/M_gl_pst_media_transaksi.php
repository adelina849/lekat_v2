<?php
	class M_gl_pst_media_transaksi extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_media_transaksi_limit($cari,$order_by,$limit,$offset)
		{
			$query = "SELECT * FROM tb_media_transaksi ".$cari." ".$order_by." LIMIT ".$offset.",".$limit." ";
			
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
		
		function count_media_transaksi_limit($cari)
		{
			$query = "SELECT COUNT(id_satuan) AS JUMLAH FROM tb_satuan ".$cari." ";
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
		
		function get_id_tr_media()
		{
			$query = "
				SELECT CONCAT('".$this->session->userdata('isLocal')."',(SELECT kode_kantor FROM tb_kantor WHERE isDefault = 1),FRMTGL,ORD) AS id_tr_media
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
						COALESCE(MAX(CAST(RIGHT(id_tr_media,5) AS UNSIGNED)) + 1,1) AS ORD
						From tb_media_transaksi
						WHERE DATE(tgl_ins) = DATE(NOW())
						AND kode_kantor = (SELECT kode_kantor FROM tb_kantor WHERE isDefault = 1)
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
		
		function simpan
		(
			$id_tr_media,
			$nama_media,
			$nama_akun,
			$ket_media,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_media_transaksi
				(
					id_tr_media,
					nama_media,
					nama_akun,
					ket_media,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					'".$id_tr_media."',
					'".$nama_media."',
					'".$nama_akun."',
					'".$ket_media."',
					NOW(),
					NOW(),
					'".$user_ins."',
					'',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_tr_media,
			$nama_media,
			$nama_akun,
			$ket_media,
			$user_updt
		)
		{
			$strquery = "
					UPDATE tb_media_transaksi SET
						
						nama_media = '".$nama_media."',
						nama_akun = '".$nama_akun."',
						ket_media = '".$ket_media."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
						
					WHERE id_tr_media = '".$id_tr_media
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_tr_media)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_media_transaksi WHERE ".$berdasarkan." = '".$id_tr_media."'  ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_media_transaksi($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_media_transaksi', array($berdasarkan => $cari));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function set_default($id_tr_media)
		{
			/*HAPUS JABATAN*/
				$strquery = 
				"
					UPDATE tb_media_transaksi SET 
						isDefault = 0
					WHERE id_tr_media <> '".$id_tr_media."' ;
				";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
			
			/*HAPUS JABATAN*/
				$strquery = 
				"
					UPDATE tb_media_transaksi SET 
						isDefault = 
							CASE 
							WHEN isDefault = 0 THEN
								1
							ELSE
								0
							END
					
					WHERE id_tr_media = '".$id_tr_media."' ;
				";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
				
		}
	}
?>