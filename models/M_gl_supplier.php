<?php
	class M_gl_supplier extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function get_no_supplier()
		{
			$query = 
			"
				SELECT CONCAT('SPL',ORD) AS NO_SPL
				FROM
				(
					SELECT
						CASE
						WHEN (ORD >= 10 AND ORD < 99) THEN CONCAT('000',CAST(ORD AS CHAR))
						WHEN (ORD >= 100 AND ORD < 999) THEN CONCAT('00',CAST(ORD AS CHAR))
						WHEN (ORD >= 1000 AND ORD < 9999) THEN CONCAT('0',CAST(ORD AS CHAR))
						WHEN ORD >= 10000 THEN CAST(ORD AS CHAR)
						ELSE CONCAT('0000',CAST(ORD AS CHAR))
						END As ORD
					From
					(
						-- SELECT COUNT(id_supplier)+1 AS ORD FROM tb_supplier
						SELECT
						CAST(LEFT(NOW(),4) AS CHAR) AS Y,
						CAST(MID(NOW(),6,2) AS CHAR) AS M,
						MID(NOW(),9,2) AS D,
						COALESCE(MAX(CAST(RIGHT(id_supplier,5) AS UNSIGNED)) + 1,1) AS ORD
						From tb_supplier
						WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
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
		
		function list_hanya_supplier_kode_akun_limit($cari,$limit,$offset)
		{
			$query = "
				SELECT 
					A.*
					,COALESCE(B.kode_akun,'') AS kode_akun
				FROM tb_supplier AS A
				LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_supplier = B.id_supplier
				".$cari." 
				ORDER BY A.nama_supplier ASC LIMIT ".$offset.",".$limit;
			
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
		
		function list_hanya_supplier_limit($cari,$limit,$offset)
		{
			/*
			$query = "
				SELECT 
					*
					,CASE 
						WHEN (YEAR(CURDATE()) - YEAR(tgl_lahir)) < 150 THEN 
							(YEAR(CURDATE()) - YEAR(tgl_lahir))
						ELSE
							0
						END
					AS USIA
				FROM
				tb_supplier ".$cari." 
				ORDER BY tgl_ins DESC LIMIT ".$offset.",".$limit
			;
			*/
			
			$query = "
				SELECT 
					A.*
				FROM tb_supplier AS A
				".$cari." 
				ORDER BY A.nama_supplier ASC LIMIT ".$offset.",".$limit;
			
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
		
		function list_supplier_limit($cari,$limit,$offset)
		{
			/*
			$query = "
				SELECT 
					*
					,CASE 
						WHEN (YEAR(CURDATE()) - YEAR(tgl_lahir)) < 150 THEN 
							(YEAR(CURDATE()) - YEAR(tgl_lahir))
						ELSE
							0
						END
					AS USIA
				FROM
				tb_supplier ".$cari." 
				ORDER BY tgl_ins DESC LIMIT ".$offset.",".$limit
			;
			*/
			
			$query = "
				SELECT 
					A.*
					,COALESCE(B.nama_kat_supplier,'') AS nama_kat_supplier
				FROM tb_supplier AS A
				LEFT JOIN tb_kat_supplier AS B ON A.id_kat_supplier = B.id_kat_supplier AND A.kode_kantor = B.kode_kantor
				".$cari." 
				ORDER BY A.nama_supplier ASC LIMIT ".$offset.",".$limit;
			
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
		
		function count_supplier_limit($cari)
		{
			$query = 
			"
				SELECT COUNT(A.id_supplier) AS JUMLAH
				FROM tb_supplier AS A
				LEFT JOIN tb_kat_supplier AS B ON A.id_kat_supplier = B.id_kat_supplier AND A.kode_kantor = B.kode_kantor
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
			$id_kat_supplier,
			$kode_supplier,
			$no_supplier,
			$nama_supplier,
			$pemilik_supplier,
			$situ,
			$siup,
			$bidang,
			$ket_supplier,
			$avatar,
			$avatar_url,
			$email,
			$tlp,
			$alamat,
			$limit_budget,
			$allow_budget,
			$bank,
			$norek,
			$hutang_awal,
			$type_supplier,
			$tgl_hutang_awal,
			$hari_tempo,
			$isPajak,
			$user_ins,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_supplier
				(
					id_supplier,
					id_kat_supplier,
					kode_supplier,
					no_supplier,
					nama_supplier,
					pemilik_supplier,
					situ,
					siup,
					bidang,
					ket_supplier,
					avatar,
					avatar_url,
					email,
					tlp,
					alamat,
					limit_budget,
					allow_budget,
					bank,
					norek,
					hutang_awal,
					type_supplier,
					tgl_hutang_awal,
					hari_tempo,
					isPajak,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_supplier
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
								COALESCE(MAX(CAST(RIGHT(id_supplier,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_supplier
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_kat_supplier."',
					/*
					(
						SELECT CONCAT('SPL',ORD) AS NO_SPL
						FROM
						(
							SELECT
								CASE
								WHEN (ORD >= 10 AND ORD < 99) THEN CONCAT('000',CAST(ORD AS CHAR))
								WHEN (ORD >= 100 AND ORD < 999) THEN CONCAT('00',CAST(ORD AS CHAR))
								WHEN (ORD >= 1000 AND ORD < 9999) THEN CONCAT('0',CAST(ORD AS CHAR))
								WHEN ORD >= 10000 THEN CAST(ORD AS CHAR)
								ELSE CONCAT('0000',CAST(ORD AS CHAR))
								END As ORD
							From
							(
								SELECT COUNT(id_supplier)+1 AS ORD FROM tb_supplier
							) AS A
						) AS AA
					),
					*/
					
					'".$kode_supplier."',
					'".$no_supplier."',
					'".$nama_supplier."',
					'".$pemilik_supplier."',
					'".$situ."',
					'".$siup."',
					'".$bidang."',
					'".$ket_supplier."',
					'".$avatar."',
					'".$avatar_url."',
					'".$email."',
					'".$tlp."',
					'".$alamat."',
					'".$limit_budget."',
					'".$allow_budget."',
					'".$bank."',
					'".$norek."',
					'".$hutang_awal."',
					'".$type_supplier."',
					'".$tgl_hutang_awal."',
					'".$hari_tempo."',
					'".$isPajak."',
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
			$id_supplier,
			$id_kat_supplier,
			$kode_supplier,
			$no_supplier,
			$nama_supplier,
			$pemilik_supplier,
			$situ,
			$siup,
			$bidang,
			$ket_supplier,
			$avatar,
			$avatar_url,
			$email,
			$tlp,
			$alamat,
			$limit_budget,
			$allow_budget,
			$bank,
			$norek,
			$hutang_awal,
			$type_supplier,
			$tgl_hutang_awal,
			$hari_tempo,
			$isPajak,
			$user_updt,
			$kode_kantor
		)
		{
			if($avatar != "")
			{
				$strquery = "
				UPDATE tb_supplier SET
				
					id_kat_supplier = '".$id_kat_supplier."',
					kode_supplier = '".$kode_supplier."',
					no_supplier = '".$no_supplier."',
					nama_supplier = '".$nama_supplier."',
					pemilik_supplier = '".$pemilik_supplier."',
					situ = '".$situ."',
					siup = '".$siup."',
					bidang = '".$bidang."',
					ket_supplier = '".$ket_supplier."',
					avatar = '".$avatar."',
					avatar_url = '".$avatar_url."',
					email = '".$email."',
					tlp = '".$tlp."',
					alamat = '".$alamat."',
					limit_budget = '".$limit_budget."',
					allow_budget = '".$allow_budget."',
					bank = '".$bank."',
					norek = '".$norek."',
					hutang_awal = '".$hutang_awal."',
					type_supplier = '".$type_supplier."',
					tgl_hutang_awal = '".$tgl_hutang_awal."',
					hari_tempo = '".$hari_tempo."',
					isPajak = '".$isPajak."',
					tgl_updt = NOW(),
					user_updt = '".$user_updt."'

				WHERE kode_kantor = '".$kode_kantor."' AND id_supplier = '".$id_supplier
				."'
				";
			}
			else
			{
				$strquery = "
				UPDATE tb_supplier SET
				
					id_kat_supplier = '".$id_kat_supplier."',
					kode_supplier = '".$kode_supplier."',
					no_supplier = '".$no_supplier."',
					nama_supplier = '".$nama_supplier."',
					pemilik_supplier = '".$pemilik_supplier."',
					situ = '".$situ."',
					siup = '".$siup."',
					bidang = '".$bidang."',
					ket_supplier = '".$ket_supplier."',
					email = '".$email."',
					tlp = '".$tlp."',
					alamat = '".$alamat."',
					limit_budget = '".$limit_budget."',
					allow_budget = '".$allow_budget."',
					bank = '".$bank."',
					norek = '".$norek."',
					hutang_awal = '".$hutang_awal."',
					type_supplier = '".$type_supplier."',
					tgl_hutang_awal = '".$tgl_hutang_awal."',
					hari_tempo = '".$hari_tempo."',
					isPajak = '".$isPajak."',
					tgl_updt = NOW(),
					user_updt = '".$user_updt."'
					
				WHERE kode_kantor = '".$kode_kantor."' AND id_supplier = '".$id_supplier
				."'
				";
			}
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		
		function hapus($berdasarkan,$id_supplier)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_supplier WHERE ".$berdasarkan." = '".$id_supplier."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_supplier($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_supplier', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_supplier_cari($cari)
        {
            $query = "
					SELECT * FROM tb_supplier ".$cari."
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
		
		function list_alias_produk($cari,$limit,$offset)
		{
			$query = "
				SELECT 
					A.*
					,COALESCE(B.kode_produk,'') AS kode_produk_asal
					,COALESCE(B.nama_produk,'') AS nama_produk_asal
				FROM tb_alias_produk AS A
				LEFT JOIN tb_produk AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor
				".$cari." 
				ORDER BY COALESCE(B.nama_produk,'') ASC LIMIT ".$offset.",".$limit;
			
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
		
		function list_produk_for_alias($id_supplier,$cari,$limit,$offset)
		{
			$query = "
				SELECT 
					A.*
					,COALESCE(B.id_alias,'') AS id_alias
				FROM tb_produk AS A
				LEFT JOIN tb_alias_produk AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk AND B.grup = 'PURCHASE' AND B.id_supplier = '".$id_supplier."'
				".$cari." 
				ORDER BY COALESCE(A.nama_produk,'') ASC LIMIT ".$offset.",".$limit;
			
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
	
		function simpan_alias_produk
		(
			$id_supplier,
			$id_produk,
			$kode_produk_alias,
			$nama_produk_alias,
			$grup,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_alias_produk
				(
					id_alias,
					id_supplier,
					id_produk,
					kode_produk,
					nama_produk,
					grup,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_alias
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
								COALESCE(MAX(CAST(RIGHT(id_alias,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_alias_produk
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_supplier."',
					'".$id_produk."',
					'".$kode_produk_alias."',
					'".$nama_produk_alias."',
					'".$grup."',
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
		
		function edit_alias_produk
		(
			$id_alias,
			$id_supplier,
			$id_produk,
			$kode_produk_alias,
			$nama_produk_alias,
			$grup,
			$user_updt,
			$kode_kantor
			
		)
		{
			$strquery = "
					UPDATE tb_alias_produk SET
						id_supplier = '".$id_supplier."',
						id_produk = '".$id_produk."',
						kode_produk = '".$kode_produk_alias."',
						nama_produk = '".$nama_produk_alias."',
						grup = '".$grup."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_alias = '".$id_alias
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_alias_produk($berdasarkan,$id_satuan)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_alias_produk WHERE ".$berdasarkan." = '".$id_satuan."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_alias($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_alias_produk', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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