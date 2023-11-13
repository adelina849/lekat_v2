<?php
	class M_gl_harga_pasien extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_harga_pasien($kode_kantor,$cari,$offset,$limit)
		{
			$query = $this->db->query("CALL SP_PRODUKHARGASATUANCOSTUMER_2('". $kode_kantor ."','". $cari ."',". $offset .",". $limit .")");
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_harga_pasien($cari)
		{
			$query = $this->db->query("SELECT COUNT(id_produk) AS JUMLAH FROM tb_produk WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (kode_produk LIKE  '%".$cari."%' OR nama_produk LIKE  '%".$cari."%')");
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function count_harga_pasien_pusat($kode_kantor,$cari)
		{
			$query = $this->db->query("SELECT COUNT(id_produk) AS JUMLAH FROM tb_produk WHERE kode_kantor = '".$kode_kantor."' AND (kode_produk LIKE  '%".$cari."%' OR nama_produk LIKE  '%".$cari."%')");
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function cek_harga_satuan_costumer($cari)
		{
			$query = 
			"
				SELECT * FROM tb_produk_harga_satuan_costumer ".$cari."
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
		
		function get_id_phsc()
		{
			$query =
			"
				SELECT CONCAT('".$this->session->userdata('isLocal')."',(SELECT kode_kantor FROM tb_kantor WHERE isDefault = 1),FRMTGL,ORD) AS id_phsc
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
						COALESCE(MAX(CAST(RIGHT(id_phsc,5) AS UNSIGNED)) + 1,1) AS ORD
						From tb_produk_harga_satuan_costumer
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
			$id_produk,
			$id_satuan,
			$id_costumer,
			$harga,
			$besar_prsn,
			$optr_prsn,
			$ket,
			$media,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_produk_harga_satuan_costumer
				(
					id_phsc,
					id_produk,
					id_satuan,
					id_costumer,
					harga,
					besar_prsn,
					optr_prsn,
					ket,
					media,
					tgl_ins,
					tgl_updt,
					user_updt,
					kode_kantor

				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_phsc
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
								COALESCE(MAX(CAST(RIGHT(id_phsc,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_produk_harga_satuan_costumer
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_produk."',
					'".$id_satuan."',
					'".$id_costumer."',
					'".$harga."',
					'".$besar_prsn."',
					'".$optr_prsn."',
					'".$ket."',
					'".$media."',
					NOW(),
					NOW(),
					'".$user_updt."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_pusat
		(
			$id_phsc,
			$id_produk,
			$id_satuan,
			$id_costumer,
			$harga,
			$besar_prsn,
			$optr_prsn,
			$ket,
			$media,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_produk_harga_satuan_costumer
				(
					id_phsc,
					id_produk,
					id_satuan,
					id_costumer,
					harga,
					besar_prsn,
					optr_prsn,
					ket,
					media,
					tgl_ins,
					tgl_updt,
					user_updt,
					kode_kantor

				)
				VALUES
				(
					'".$id_phsc."',
					'".$id_produk."',
					'".$id_satuan."',
					'".$id_costumer."',
					'".$harga."',
					'".$besar_prsn."',
					'".$optr_prsn."',
					'".$ket."',
					'".$media."',
					NOW(),
					NOW(),
					'".$user_updt."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_phsc,
			$harga,
			$media,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_produk_harga_satuan_costumer SET
						harga = '".$harga."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND media = '".$media."' AND id_phsc = '".$id_phsc
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_pusat
		(
			$id_phsc,
			$harga,
			$media,
			$user_updt
		)
		{
			$strquery = "
					UPDATE tb_produk_harga_satuan_costumer SET
						harga = '".$harga."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE media = '".$media."' AND id_phsc = '".$id_phsc
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($cari)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_produk_harga_satuan_costumer ".$cari." ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function simpan_all_dari_pusat
		(
			$kode_kantor_sumber_pusat,$kode_kantor_target
		)
		{
			$strquery = 
			"
				INSERT INTO 
				tb_produk_harga_satuan_costumer (id_phsc,id_produk,id_satuan,id_costumer,media,harga,besar_prsn,optr_prsn,ket,tgl_ins,tgl_updt,user_updt,kode_kantor)
				SELECT A.id_phsc,A.id_produk,A.id_satuan,A.id_costumer,A.media,A.harga,A.besar_prsn,A.optr_prsn,A.ket,A.tgl_ins,A.tgl_updt,A.user_updt,'".$kode_kantor_target."'
				FROM tb_produk_harga_satuan_costumer AS A
				LEFT JOIN 
				(
					SELECT * 
					FROM tb_produk_harga_satuan_costumer 
					WHERE kode_kantor = '".$kode_kantor_target."' 
					-- AND status <> '' AND besar_konversi > 0 AND harga_jual >= 0
				) AS B ON A.id_produk = B.id_produk AND A.id_satuan = B.id_satuan AND A.id_costumer = B.id_costumer AND A.media = B.media
				WHERE A.kode_kantor = '".$kode_kantor_sumber_pusat."' AND B.id_produk IS NULL AND B.id_produk IS NULL AND B.id_costumer IS NULL AND B.media IS NULL;
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function update_all_dari_pusat
		(
			$kode_kantor_sumber_pusat
		)
		{
			$strquery = 
			"
				UPDATE tb_produk_harga_satuan_costumer AS A
				LEFT JOIN 
				(
					SELECT * 
					FROM tb_produk_harga_satuan_costumer 
					WHERE kode_kantor = '".$kode_kantor_sumber_pusat."' 
					-- AND status <> '' AND besar_konversi > 0 AND harga_jual >= 0
				) AS B ON A.id_produk = B.id_produk AND A.id_satuan = B.id_satuan AND A.id_costumer = B.id_costumer AND A.media = B.media
				SET 
					A.harga = B.harga
					,A.besar_prsn = B.besar_prsn
					,A.optr_prsn = B.optr_prsn
				WHERE A.kode_kantor <> '".$kode_kantor_sumber_pusat."';
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	}
?>