<?php
	class M_gl_jabatan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_jabatan($cari,$limit,$offset)
		{
			$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_jabatan_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query("SELECT A.id_jabatan,A.kode_jabatan,A.nama_jabatan,A.hirarki,A.ket_jabatan,A.tgl_insert,A.tgl_update,A.user,A.kode_kantor,COALESCE(B.JUMLAH,0) AS JUMLAH FROM tb_jabatan AS A
			LEFT JOIN
			(
				SELECT id_jabatan,COUNT(id_jabatan) AS JUMLAH FROM tb_hak_akses WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' GROUP BY id_jabatan
			) AS B
			ON A.id_jabatan = B.id_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_jabatan_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(id_jabatan) AS JUMLAH FROM tb_jabatan ".$cari);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function simpan($kode_jabatan,$nama_jabatan,$hirarki,$ket_jabatan,$user,$kode_kantor)
		{
			$strquery = "
				INSERT INTO tb_jabatan
				(
					id_jabatan,
					kode_jabatan,
					nama_jabatan,
					hirarki,
					ket_jabatan,
					tgl_insert,
					tgl_update,
					user,
					kode_kantor



				)
				VALUES
				(
					(
						-- SELECT CONCAT('INPOS',FRMTGL,ORD) AS id_jabatan
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_jabatan
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
								COALESCE(MAX(CAST(RIGHT(id_jabatan,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_jabatan
								WHERE DATE(tgl_insert) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$kode_jabatan."',
					'".$nama_jabatan."',
					'".$hirarki."',
					'".$ket_jabatan."',
					NOW(),
					NOW(),
					'".$user."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit($id_jabatan,$kode_jabatan,$nama_jabatan,$ket_jabatan,$hirarki,$user,$kode_kantor)
		{
			$strquery = "
					UPDATE tb_jabatan SET
						
						kode_jabatan = '".$kode_jabatan."',
						nama_jabatan = '".$nama_jabatan."',
						hirarki = '".$hirarki."',
						ket_jabatan = '".$ket_jabatan."',
						user_updt = '".$user."',
						tgl_update = NOW()
					WHERE kode_kantor = '".$kode_kantor."' AND id_jabatan = '".$id_jabatan
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($id)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_jabatan WHERE id_jabatan = '".$id."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
			
			
			/*HAPUS AKSES*/
			$strquery = "DELETE FROM tb_hak_akses WHERE id_jabatan = '".$id."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS AKSES*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_jabatan_num_rows($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_jabatan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query->num_rows();
            }
            else
            {
                return false;
            }
        }
		
		function get_jabatan($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_jabatan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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