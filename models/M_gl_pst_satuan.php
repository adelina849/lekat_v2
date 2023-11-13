<?php
	class M_gl_pst_satuan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_satuan_limit($cari,$limit,$offset)
		{
			$query = 
				"
					SELECT * FROM tb_satuan ".$cari." ORDER BY kode_satuan ASC LIMIT ".$offset.",".$limit."
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
		
		function count_satuan_limit($cari)
		{
			$query = 
				"
					SELECT COUNT(id_satuan) AS JUMLAH FROM tb_satuan ".$cari."
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
		
		function get_id_satuan()
		{
			$query = "
				SELECT CONCAT('".$this->session->userdata('isLocal')."',(SELECT kode_kantor FROM tb_kantor WHERE isDefault = 1),FRMTGL,ORD) AS id_satuan
				FROM
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
						COALESCE(MAX(CAST(RIGHT(id_satuan,5) AS UNSIGNED)) + 1,1) AS ORD
						From tb_satuan
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
			$id_satuan,
			$kode_satuan,
			$nama_satuan,
			$ket_satuan,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_satuan
				(
					id_satuan,
					kode_satuan,
					nama_satuan,
					ket_satuan,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					'".$id_satuan."',
					'".$kode_satuan."',
					'".$nama_satuan."',
					'".$ket_satuan."',
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
			$id_satuan,
			$kode_satuan,
			$nama_satuan,
			$ket_satuan,
			$user_updt
		)
		{
			$strquery = "
					UPDATE tb_satuan SET
						kode_satuan = '".$kode_satuan."',
						nama_satuan = '".$nama_satuan."',
						ket_satuan = '".$ket_satuan."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE id_satuan = '".$id_satuan
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_satuan)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_satuan WHERE ".$berdasarkan." = '".$id_satuan."'  ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_satuan($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_satuan', array($berdasarkan => $cari));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
	
		function get_satuan_cari($cari)
        {
			$query = 
				"
					SELECT * FROM tb_satuan ".$cari."
				";
            //$query = $this->db->get_where('tb_satuan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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