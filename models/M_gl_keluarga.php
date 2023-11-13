<?php
	class M_gl_keluarga extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_keluarga_limit($cari,$limit,$offset)
		{
			$query = $this->db->query("
				SELECT *
					,CASE 
						WHEN (YEAR(CURDATE()) - YEAR(tgl_lahir)) < 150 THEN 
							(YEAR(CURDATE()) - YEAR(tgl_lahir))
						ELSE
							0
						END
					AS USIA
				FROM tb_keluarga ".$cari." 
				ORDER BY hirarki DESC 
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
		
		function count_keluarga_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(id_kel) AS JUMLAH FROM tb_keluarga ".$cari);
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
			$nama_hub,
			$nik_kel,
			$nama,
			$tempat_lahir,
			$tgl_lahir,
			$kelamin,
			$tlp,
			$email,
			$pnd,
			$alamat,
			$ket_kel,
			$hirarki,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_keluarga
				(
					id_kel,
					id_karyawan,
					nama_hub,
					nik_kel,
					nama,
					tempat_lahir,
					tgl_lahir,
					kelamin,
					tlp,
					email,
					pnd,
					alamat,
					ket_kel,
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
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_kel
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
								COALESCE(MAX(CAST(RIGHT(id_kel,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_keluarga
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_karyawan."',
					'".$nama_hub."',
					'".$nik_kel."',
					'".$nama."',
					'".$tempat_lahir."',
					'".$tgl_lahir."',
					'".$kelamin."',
					'".$tlp."',
					'".$email."',
					'".$pnd."',
					'".$alamat."',
					'".$ket_kel."',
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
		
			$id_kel,
			$id_karyawan,
			$nama_hub,
			$nik_kel,
			$nama,
			$tempat_lahir,
			$tgl_lahir,
			$kelamin,
			$tlp,
			$email,
			$pnd,
			$alamat,
			$ket_kel,
			$hirarki,
			$user_updt,
			$kode_kantor

		)
		{
			$strquery = "
					UPDATE tb_keluarga SET
						id_karyawan = '".$id_karyawan."',
						nama_hub = '".$nama_hub."',
						nik_kel = '".$nik_kel."',
						nama = '".$nama."',
						tempat_lahir = '".$tempat_lahir."',
						tgl_lahir = '".$tgl_lahir."',
						kelamin = '".$kelamin."',
						tlp = '".$tlp."',
						email = '".$email."',
						pnd = '".$pnd."',
						alamat = '".$alamat."',
						ket_kel = '".$ket_kel."',
						hirarki = '".$hirarki."',
						user_updt = '".$user_updt."',
						tgl_updt = NOW()
					WHERE kode_kantor = '".$kode_kantor."' AND id_kel = '".$id_kel
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_keluarga WHERE ".$berdasarkan." = '".$id."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_keluarga($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_keluarga', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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