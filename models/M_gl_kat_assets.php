<?php
	class M_gl_kat_assets extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_kat_assets_limit($cari,$limit,$offset)
		{
			$query = $this->db->query("SELECT * FROM tb_kat_assets ".$cari." ORDER BY nama_kat_assets ASC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_kat_assets_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(id_kat_assets) AS JUMLAH FROM tb_kat_assets ".$cari);
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
			$nama_kat_assets,
			$ket_kat_assets,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_kat_assets
				(
					id_kat_assets,
					nama_kat_assets,
					ket_kat_assets,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_kat_assets
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
								COALESCE(MAX(CAST(RIGHT(id_kat_assets,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_kat_assets
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$nama_kat_assets."',
					'".$ket_kat_assets."',
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
			$id_kat_assets,
			$nama_kat_assets,
			$ket_kat_assets,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_kat_assets SET
						nama_kat_assets = '".$nama_kat_assets."',
						ket_kat_assets = '".$ket_kat_assets."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_kat_assets = '".$id_kat_assets
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_kat_assets)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_kat_assets WHERE ".$berdasarkan." = '".$id_kat_assets."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_kat_assets($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_kat_assets', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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