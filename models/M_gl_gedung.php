<?php
	class M_gl_gedung extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_gedung_limit($cari,$limit,$offset)
		{
			$query = 
			"
				SELECT
					A.*
					,COALESCE(B.no_karyawan,'') AS no_karyawan
					,COALESCE(B.nama_karyawan,'') AS nama_karyawan
				FROM tb_gedung AS A
				LEFT JOIN tb_karyawan AS B ON A.kode_kantor = B.kode_kantor AND A.id_karyawan = B.id_karyawan
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
		
		function count_gedung_limit($cari)
		{
			$query = 
			"
				SELECT COUNT(A.id_gedung) AS JUMLAH FROM tb_gedung AS A ".$cari."
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
			$kat_gedung,
			$kode_gedung,
			$nama_gedung,
			$ket_gedung,
			$user_ins,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_gedung
				(
					id_gedung,
					id_karyawan,
					kat_gedung,
					kode_gedung,
					nama_gedung,
					ket_gedung,
					tgl_ins,
					tgl_updt,
					user_updt,
					user_ins,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_gedung
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
								COALESCE(MAX(CAST(RIGHT(id_gedung,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_gedung
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_karyawan."',
					'".$kat_gedung."',
					'".$kode_gedung."',
					'".$nama_gedung."',
					'".$ket_gedung."',
					NOW(),
					NOW(),
					'',
					'".$user_ins."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_gedung,
			$id_karyawan,
			$kat_gedung,
			$kode_gedung,
			$nama_gedung,
			$ket_gedung,
			$user_updt,
			$kode_kantor

		
		)
		{
			$strquery = "
					UPDATE tb_gedung SET
						
						id_karyawan = '".$id_karyawan."',
						kat_gedung = '".$kat_gedung."',
						kode_gedung = '".$kode_gedung."',
						nama_gedung = '".$nama_gedung."',
						ket_gedung = '".$ket_gedung."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'

					WHERE kode_kantor = '".$kode_kantor."' AND id_gedung = '".$id_gedung
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_gedung)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_gedung WHERE ".$berdasarkan." = '".$id_gedung."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_gedung($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_gedung', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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