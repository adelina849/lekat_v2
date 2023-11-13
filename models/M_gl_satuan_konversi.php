<?php
	class M_gl_satuan_konversi extends CI_Model 
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
			//$query = $this->db->query("CALL SP_PRODUKHARGASATUAN('". $kode_kantor ."','". $cari ."',". $offset .",". $limit .")");
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
		
		function count_satuan_konversi($cari)
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
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_satuan_konversi SET
						besar_konversi = '".$konversi."',
						harga_jual = '".$harga_jual."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_satuan_konversi = '".$id_satuan_konversi
					."'
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
		
		function getDateTimeMysql()
		{
			$query = "SELECT NOW() AS curDate;";
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
		
		function hapus_stock_produk_cur_month
		(
			$kode_kantor,
			$tglUpdate
		)
		{
			$strquery = "
				DELETE FROM tb_stock_produk 
				WHERE kode_Kantor = '".$kode_kantor."' 
				AND YEAR(tgl_stock) = YEAR('".$tglUpdate."') 
				AND MONTH(tgl_stock) = MONTH('".$tglUpdate."');
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function backup_stock_ke_tb_stock_produk_for_parsial
		(
			$kode_kantor,
			$tglUpload,
			$waktuUpload,
			$limit
		)
		{
			$strquery = "
				CALL SP_VIEW_STOCK_2_UPDATE_STOCK_PARSIAL('".$kode_kantor."','".$tglUpload."','".$tglUpload."',0,".$limit.",'','".$waktuUpload."');
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function update_tb_stock_produk_parsial
		(
			$kode_kantor,
			$id_produk,
			$tglUpdate,
			$stock
		)
		{
			$strquery = "
				UPDATE tb_stock_produk
				SET
					jumlah_stock = ".$stock."
				WHERE kode_kantor = '".$kode_kantor."' AND id_produk = '".$id_produk."' AND tgl_stock = '".$tglUpdate."';
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_stock_produk
		(
			$id_produk,
			$jumlah_stock,
			$tgl_stock,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_stock_produk
				(
					id_stock_produk,
					id_produk,
					jumlah_stock,
					tgl_stock,
					tgl_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_stock_produk
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
								COALESCE(MAX(CAST(RIGHT(id_stock_produk,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_stock_produk
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_produk."',
					'".$jumlah_stock."',
					'".$tgl_stock."',
					NOW(),
					'".$user_updt."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		
		
		function update_tb_produk_by_stock
		(
			$kode_kantor,
			$tglUpdate
		)
		{
			$strquery = "
				UPDATE tb_produk AS A
				INNER JOIN tb_stock_produk AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
				AND B.tgl_stock = '".$tglUpdate."'
				SET A.stock = B.jumlah_stock, A.dtstock = B.tgl_stock
				WHERE A.kode_kantor = '".$kode_kantor."';
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function update_tb_stock_produk_yg_tdk_ada_di_excel_upload
		(
			$kode_kantor,
			$tglUpdate
		)
		{
			$strquery = "
				INSERT INTO tb_stock_produk (id_produk,jumlah_stock,stock_toko,stock_gudang,tgl_stock,tgl_ins,user_updt,kode_kantor)
				SELECT
					DISTINCT
					A.id_produk
					,0 AS jumlah_stock
					,0 AS stock_toko
					,0 AS stock_gudang
					,'".$tglUpdate."' AS tgl_stock
					,NOW() AS tgl_ins
					,'SYSTEM' AS user_updt
					,'".$kode_kantor."' AS kode_kantor
				FROM tb_produk AS A
				LEFT JOIN tb_stock_produk AS B 
					ON A.id_produk = B.id_produk 
					AND A.kode_kantor = B.kode_kantor 
					AND B.tgl_stock = '".$tglUpdate."'
				WHERE A.kode_kantor = '".$kode_kantor."' AND B.id_stock_produk IS NULL
				GROUP BY A.id_produk;
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
	
		function simpan_all_dari_pusat
		(
			$kode_kantor_sumber_pusat,$kode_kantor_target
		)
		{
			$strquery = 
			"
				INSERT INTO 
				tb_satuan_konversi (id_satuan_konversi, id_produk, id_satuan,status,besar_konversi,harga_jual,ket_satuan_konversi,tgl_ins,tgl_updt,user_updt,kode_kantor)
				SELECT A.id_satuan_konversi,A.id_produk,A.id_satuan,A.status,A.besar_konversi,A.harga_jual,A.ket_satuan_konversi,A.tgl_ins,A.tgl_updt,A.user_updt,'".$kode_kantor_target."'
				FROM tb_satuan_konversi AS A
				LEFT JOIN 
				(
					SELECT * 
					FROM tb_satuan_konversi 
					WHERE kode_kantor = '".$kode_kantor_target."' 
					-- AND status <> '' AND besar_konversi > 0 AND harga_jual >= 0
				) AS B ON A.id_produk = B.id_produk AND A.id_satuan = B.id_satuan
				WHERE A.kode_kantor = '".$kode_kantor_sumber_pusat."' AND B.id_satuan IS NULL AND B.id_produk IS NULL;
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
				UPDATE tb_satuan_konversi AS A
				LEFT JOIN 
				(
					SELECT * 
					FROM tb_satuan_konversi 
					WHERE kode_kantor = '".$kode_kantor_sumber_pusat."' AND status <> '' AND besar_konversi > 0 AND harga_jual >= 0
				) AS B ON A.id_produk = B.id_produk AND A.id_satuan = B.id_satuan
				SET 
					A.status = B.status
					,A.besar_konversi = B.besar_konversi
					,A.harga_jual = B.harga_jual
				WHERE A.kode_kantor <> '".$kode_kantor_sumber_pusat."';
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	}
?>