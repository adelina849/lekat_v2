<?php
	class M_gl_sekolah extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_sekolah_limit($cari,$limit,$offset)
		{
			$query = $this->db->query("
				SELECT *
				FROM tb_sekolah ".$cari." 
				ORDER BY tingkat DESC, hirarki DESC 
				LIMIT ".$offset.",".$limit);
				
				
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_sekolah_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(id_sekolah) AS JUMLAH FROM tb_sekolah ".$cari);
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
			$id_karyawan,
			$tingkat,
			$nama_sekolah,
			$nilai,
			$tahun_lulus,
			$alamat,
			$ket_sekolah,
			$hirarki,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_sekolah
				(
					id_sekolah,
					id_karyawan,
					tingkat,
					nama_sekolah,
					nilai,
					tahun_lulus,
					alamat,
					ket_sekolah,
					hirarki,
					user_ins,
					user_updt,
					tgl_ins,
					tgl_updt,
					kode_kantor

				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_sekolah
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
								COALESCE(MAX(CAST(RIGHT(id_sekolah,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_sekolah
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_karyawan."',
					'".$tingkat."',
					'".$nama_sekolah."',
					'".$nilai."',
					'".$tahun_lulus."',
					'".$alamat."',
					'".$ket_sekolah."',
					'".$hirarki."',
					'".$user_ins."',
					'',
					NOW(),
					NOW(),
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_sekolah,
			$id_karyawan,
			$tingkat,
			$nama_sekolah,
			$nilai,
			$tahun_lulus,
			$alamat,
			$ket_sekolah,
			$hirarki,
			$user_updt,
			$kode_kantor

		)
		{
			$strquery = "
					UPDATE tb_sekolah SET
						id_karyawan = '".$id_karyawan."',
						tingkat = '".$tingkat."',
						nama_sekolah = '".$nama_sekolah."',
						nilai = '".$nilai."',
						tahun_lulus = '".$tahun_lulus."',
						alamat = '".$alamat."',
						ket_sekolah = '".$ket_sekolah."',
						hirarki = '".$hirarki."',
						user_ins = '".$user_ins."',
						user_updt = '".$user_updt."',
						tgl_updt = NOW()
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_sekolah = '".$id_sekolah
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_sekolah WHERE ".$berdasarkan." = '".$id."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_sekolah($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_sekolah', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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