<?php
	class M_gl_kode_akun extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_kode_akun_limit_order_by($cari,$order_by,$limit,$offset)
		{
			$query = $this->db->query("SELECT * FROM tb_kode_akun ".$cari." ".$order_by." LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_kode_akun_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_kode_akun ".$cari." ORDER BY tgl_ins DESC LIMIT ".$offset.",".$limit);
			
			$query = "
						SELECT
							A.*
							,COALESCE(B.norek,'') AS NOREK
							,COALESCE(B.nama_bank,'') AS NAMABANK
							,COALESCE(B.atas_nama,'') AS ATASNAMA
							,COALESCE(C.nama_supplier,'') AS nama_supplier
						FROM tb_kode_akun AS A
						LEFT JOIN tb_bank AS B
							ON A.kode_kantor = B.kode_kantor
							AND A.id_bank = B.id_bank
						LEFT JOIN tb_supplier AS C
							ON A.kode_kantor = C.kode_kantor
							AND A.id_supplier = C.id_supplier
						".$cari."
						ORDER BY A.tgl_ins DESC
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
		
		function count_kode_akun_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(A.id_kode_akun) AS JUMLAH FROM tb_kode_akun AS A ".$cari);
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
			$id_bank,
			$id_supplier,
			$kat_akun_jurnal,
			$kode_akun_induk,
			$target,
			$kode_akun,
			$nama_kode_akun,
			$ket_kode_akun,
			$isLabaRugi,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_kode_akun
				(
					id_kode_akun,
					id_bank,
					id_supplier,
					kat_akun_jurnal,
					kode_akun_induk,
					target,
					kode_akun,
					nama_kode_akun,
					ket_kode_akun,
					isLabaRugi,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor

				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_kode_akun
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
								COALESCE(MAX(CAST(RIGHT(id_kode_akun,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_kode_akun
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_bank."',
					'".$id_supplier."',
					'".$kat_akun_jurnal."',
					'".$kode_akun_induk."',
					'".$target."',
					'".$kode_akun."',
					'".$nama_kode_akun."',
					'".$ket_kode_akun."',
					'".$isLabaRugi."',
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
			$id_kode_akun,
			$id_bank,
			$id_supplier,
			$kat_akun_jurnal,
			$kode_akun_induk,
			$target,
			$kode_akun,
			$nama_kode_akun,
			$ket_kode_akun,
			$isLabaRugi,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_kode_akun SET
						id_bank = '".$id_bank."',
						id_supplier = '".$id_supplier."',
						kat_akun_jurnal = '".$kat_akun_jurnal."',
						kode_akun_induk = '".$kode_akun_induk."',
						target = '".$target."',
						kode_akun = '".$kode_akun."',
						nama_kode_akun = '".$nama_kode_akun."',
						ket_kode_akun = '".$ket_kode_akun."',
						isLabaRugi = '".$isLabaRugi."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_kode_akun = '".$id_kode_akun
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_kode_akun)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_kode_akun WHERE ".$berdasarkan." = '".$id_kode_akun."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_pusat($cari)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_kode_akun ".$cari.";";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_kode_akun($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_kode_akun', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
			
			//$query = $this->db->query($query);
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_kode_akun_by_cari($cari)
        {
            //$query = $this->db->get_where('tb_kode_akun', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
			
			$query = "SELECT  
						distinct
						id_kode_akun
						,kode_kantor
						,CASE WHEN id_bank = '' THEN 'TUNAI' ELSE id_bank END AS id_bank
						,target
						,kode_akun
						,nama_kode_akun AS nama_akun
					FROM tb_kode_akun
					".$cari."
					GROUP BY 
					id_kode_akun
					,kode_kantor
					,id_bank
					,target
					,kode_akun
					,nama_kode_akun 
					ORDER BY tgl_ins DESC 
					-- LIMIT 0,1 ";
			
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