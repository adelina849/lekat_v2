<?php
	class M_gl_pst_satuan_konversi extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_satuan_konversi($kode_kantor,$cari,$offset,$limit)
		{
			$query = $this->db->query("CALL SP_PRODUKSATUANKONVERSI('". $kode_kantor ."','". $cari ."',". $offset .",". $limit .")");
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_harga_dasar_satuan($kode_kantor,$cari,$offset,$limit)
		{
			$query = $this->db->query("CALL SP_PRODUKHARGASATUAN('". $kode_kantor ."','". $cari ."',". $offset .",". $limit .")");
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_satuan_konversi($cari,$kode_kantor)
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
		
		function list_data_satuan_konversi($cari,$order_by,$offset,$limit)
		{
			$query = 
			"
				SELECT 
					A.*
					,COALESCE(B.kode_satuan,'') AS kode_satuan
					,COALESCE(B.nama_satuan,'') AS nama_satuan
				FROM tb_satuan_konversi AS A
				LEFT JOIN tb_satuan AS B ON A.id_satuan = B.id_satuan AND A.kode_kantor = B.kode_kantor
				".$cari." ".$order_by." LIMIT ".$offset.",".$limit."
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
		
		function cek_satuan_konversi($cari)
		{
			$query = 
			"
				SELECT * FROM tb_satuan_konversi ".$cari."
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
		
		function simpan
		(
			$id_produk,
			$id_satuan,
			$status,
			$besar_konversi,
			$harga_jual,
			$ket_satuan_konversi,
			$user_updt,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_satuan_konversi
				(
					id_satuan_konversi,
					id_produk,
					id_satuan,
					status,
					besar_konversi,
					harga_jual,
					ket_satuan_konversi,
					tgl_ins,
					tgl_updt,
					user_updt,
					kode_kantor

				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_satuan_konversi
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
								COALESCE(MAX(CAST(RIGHT(id_satuan_konversi,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_satuan_konversi
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_produk."',
					'".$id_satuan."',
					'".$status."',
					'".$besar_konversi."',
					'".$harga_jual."',
					'".$ket_satuan_konversi."',
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
			$id_satuan_konversi,
			$konversi,
			$harga_jual,
			$user_updt
		)
		{
			$strquery = "
					UPDATE tb_satuan_konversi SET
						besar_konversi = '".$konversi."',
						harga_jual = '".$harga_jual."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE id_satuan_konversi = '".$id_satuan_konversi
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_by_idProduk_idSatuan
		(
			$id_produk,
			$id_satuan,
			$konversi,
			$harga_jual,
			$user_updt
		)
		{
			$strquery = "
					UPDATE tb_satuan_konversi SET
						besar_konversi = '".$konversi."',
						harga_jual = '".$harga_jual."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE id_produk = '".$id_produk."'
					AND id_satuan = '".$id_satuan."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($cari)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_satuan_konversi ".$cari." ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_tb_penampungan($cari_so,$cari_ims)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_tmp_stock_so ".$cari_so." ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
			
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_tmp_stock_ims ".$cari_ims." ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function simpan_tmp_ims($kode_kantor)
		{
			/*HAPUS JABATAN*/
				$strquery = "CALL PROC_BACKUPSTOCKIMS_FORSO_2('','".$kode_kantor."','')";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_tmp_so
		(
			$kode_produk,
			$nama_produk,
			$nama_supplier,
			$satuan_def,
			$stock_gudang,
			$stock_toko,
			$stock_all,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_tmp_stock_so
				(
					kode_produk,
					nama_produk,
					nama_supplier,
					satuan_def,
					stock_gudang,
					stock_toko,
					stock_all,
					kode_kantor

				)
				VALUES
				(
					'".$kode_produk."',
					'".$nama_produk."',
					'".$nama_supplier."',
					'".$satuan_def."',
					'".$stock_gudang."',
					'".$stock_toko."',
					'".$stock_all."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function list_stock_upload($cari)
		{
			$query = "
					SELECT A.*,COALESCE(B.stock_all,0) AS STOCK_IMS FROM tb_tmp_stock_so AS A
					LEFT JOIN tb_tmp_stock_ims AS B ON A.kode_kantor = B.kode_kantor AND A.kode_produk = B.kode_produk ".$cari."";
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