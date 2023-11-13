<?php
	class M_gl_punish extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_punish_limit($cari,$limit,$offset)
		{
			$query =
			"
				SELECT
					A.*
					,COALESCE(B.no_karyawan,'') AS no_karyawan
					,COALESCE(B.nama_karyawan,'') AS nama_karyawan
					,COALESCE(C.kode_per,'') AS kode_per
					,COALESCE(C.nama_per,'') AS nama_per
					,COALESCE(C.bobot_per,'') AS bobot_per
					,COALESCE(C.ket_per,'') AS ket_per
				FROM tb_punish AS A
				LEFT JOIN tb_karyawan AS B ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_peraturan AS C ON A.id_peraturan = C.id_per AND A.kode_kantor = C.kode_kantor
				".$cari." ORDER BY A.tgl_ins DESC LIMIT ".$offset.",".$limit."
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
		
		function count_punish_limit($cari)
		{
			$query =
			"
				SELECT
					COUNT(id_punish) AS JUMLAH
				FROM tb_punish AS A
				LEFT JOIN tb_karyawan AS B ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_peraturan AS C ON A.id_peraturan = C.id_per AND A.kode_kantor = C.kode_kantor
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
			$id_karyawan,
			$id_peraturan,
			$no_pelanggaran,
			$nama_pelanggaran,
			$hukuman,
			$tgl_mulai,
			$tgl_selesai,
			$kronologi,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_punish
				(
					id_punish,
					id_karyawan,
					id_peraturan,
					no_pelanggaran,
					nama_pelanggaran,
					hukuman,
					tgl_mulai,
					tgl_selesai,
					kronologi,
					user_ins,
					user_updt,
					tgl_ins,
					tgl_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_punish
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
								COALESCE(MAX(CAST(RIGHT(id_punish,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_punish
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_karyawan."',
					'".$id_peraturan."',
					'".$no_pelanggaran."',
					'".$nama_pelanggaran."',
					'".$hukuman."',
					'".$tgl_mulai."',
					'".$tgl_selesai."',
					'".$kronologi."',
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
			$id_punish,
			$id_karyawan,
			$id_peraturan,
			$no_pelanggaran,
			$nama_pelanggaran,
			$hukuman,
			$tgl_mulai,
			$tgl_selesai,
			$kronologi,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_punish SET
						
						id_karyawan = '".$id_karyawan."',
						id_peraturan = '".$id_peraturan."',
						no_pelanggaran = '".$no_pelanggaran."',
						nama_pelanggaran = '".$nama_pelanggaran."',
						hukuman = '".$hukuman."',
						tgl_mulai = '".$tgl_mulai."',
						tgl_selesai = '".$tgl_selesai."',
						kronologi = '".$kronologi."',
						user_updt = '".$user_updt."',
						tgl_updt = NOW()
					WHERE kode_kantor = '".$kode_kantor."' AND id_punish = '".$id_punish
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_punish)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_punish WHERE ".$berdasarkan." = '".$id_punish."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_punish($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_punish', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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