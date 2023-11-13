<?php
	class M_gl_penyedia_asuransi extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_penyedia_asuransi_limit($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT *
				FROM tb_penyedia_asuransi ".$cari." 
				".$order_by."
				LIMIT ".$offset.",".$limit."
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
		
		function count_penyedia_asuransi_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(id_p_asuransi) AS JUMLAH FROM tb_penyedia_asuransi ".$cari);
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
			$kode_penyedia,
			$nama_penyedia,
			$no_tlp,
			$email,
			$alamat,
			$ket_penyedia,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_penyedia_asuransi
				(
					id_p_asuransi,
					kode_penyedia,
					nama_penyedia,
					no_tlp,
					email,
					alamat,
					ket_penyedia,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_p_asuransi
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
								COALESCE(MAX(CAST(RIGHT(id_p_asuransi,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_penyedia_asuransi
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$kode_penyedia."',
					'".$nama_penyedia."',
					'".$no_tlp."',
					'".$email."',
					'".$alamat."',
					'".$ket_penyedia."',
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
			$id_p_asuransi,
			$kode_penyedia,
			$nama_penyedia,
			$no_tlp,
			$email,
			$alamat,
			$ket_penyedia,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_penyedia_asuransi SET
						kode_penyedia = '".$kode_penyedia."',
						nama_penyedia = '".$nama_penyedia."',
						no_tlp = '".$no_tlp."',
						email = '".$email."',
						alamat = '".$alamat."',
						ket_penyedia = '".$ket_penyedia."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_p_asuransi = '".$id_p_asuransi
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_penyedia_asuransi)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_penyedia_asuransi WHERE ".$berdasarkan." = '".$id_penyedia_asuransi."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_penyedia_asuransi($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_penyedia_asuransi', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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