<?php
	class M_gl_pst_produk extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_produk_limit($cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT
					A.id_produk,
					A.id_supplier,
					A.kode_produk,
					A.nama_produk,
					A.nama_umum,
					A.produksi_oleh,
					A.pencipta,
					A.charge,
					A.optr_charge,
					A.charge_beli,
					A.optr_charge_beli,
					A.min_stock,
					A.max_stock,
					A.harga_minimum,
					A.spek_produk,
					A.ket_produk,
					A.isNotActive,
					A.isNotRetur,
					A.stock,
					A.dtstock,
					A.isProduk,
					A.kat_costumer_fee,
					A.optr_kondisi_fee,
					A.besar_penjualan_fee,
					A.satuan_jual_fee,
					A.optr_fee,
					A.besar_fee,
					A.isSattle,
					A.buf_stock,
					A.late_time,
					A.jenis_moving,
					A.isReport,
					A.hpp,
					A.tgl_ins,
					A.tgl_updt,
					A.user_ins,
					A.user_updt,
					A.kode_kantor
					,COALESCE(B.kode_supplier,'') AS kode_supplier
					,COALESCE(B.nama_supplier,'') AS nama_supplier
					,MAX(COALESCE(C.img_nama,'')) AS img_nama
					,MAX(COALESCE(C.img_file,'')) AS img_file
					,MAX(COALESCE(C.img_url,'')) AS img_url
					,MAX(COALESCE(C.ket_img,'')) AS ket_img
					,COALESCE(GROUP_CONCAT(DISTINCT NAMA_KATEGORI ORDER BY NAMA_KATEGORI ASC SEPARATOR ', '),'') AS NAMA_KATEGORI
				FROM tb_produk AS A
				LEFT JOIN tb_supplier AS B ON A.id_supplier = B.id_supplier AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_images AS C ON A.id_produk = C.id AND A.kode_kantor = C.kode_kantor AND C.group_by = 'produks'
				LEFT JOIN
				(
					SELECT
						A.id_produk,A.id_kat_produk,A.kode_kantor,COALESCE(B.kode_kat_produk,'') AS KODE_KATEGORI,COALESCE(B.nama_kat_produk,'') AS NAMA_KATEGORI
					FROM tb_prod_to_kat AS A
					LEFT JOIN tb_kat_produk AS B ON A.id_kat_produk = B.id_kat_produk AND A.kode_kantor = B.kode_kantor
					
				) AS D ON A.id_produk = D.id_produk AND A.kode_kantor = D.kode_kantor
				
				".$cari."
				
				GROUP BY A.id_produk,
					A.id_supplier,
					A.kode_produk,
					A.nama_produk,
					A.nama_umum,
					A.produksi_oleh,
					A.pencipta,
					A.charge,
					A.optr_charge,
					A.charge_beli,
					A.optr_charge_beli,
					A.min_stock,
					A.max_stock,
					A.harga_minimum,
					A.spek_produk,
					A.ket_produk,
					A.isNotActive,
					A.isNotRetur,
					A.stock,
					A.dtstock,
					A.isProduk,
					A.kat_costumer_fee,
					A.optr_kondisi_fee,
					A.besar_penjualan_fee,
					A.satuan_jual_fee,
					A.optr_fee,
					A.besar_fee,
					A.isSattle,
					A.buf_stock,
					A.late_time,
					A.jenis_moving,
					A.hpp,
					A.tgl_ins,
					A.tgl_updt,
					A.user_ins,
					A.user_updt,
					A.kode_kantor,COALESCE(B.kode_supplier,''),COALESCE(B.nama_supplier,'')
	
				".$order_by."
				-- ORDER BY A.kode_produk ASC 
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
		
		function count_produk_limit($cari)
		{
			$query = 
			"
				SELECT COUNT(id_produk) AS JUMLAH
				FROM tb_produk AS A
				LEFT JOIN tb_supplier AS B ON A.id_supplier = B.id_supplier AND A.kode_kantor = B.kode_kantor
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
		
		function get_produk($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_produk', array($berdasarkan => $cari));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_id_produk()
		{
			$query = "
				SELECT CONCAT('".$this->session->userdata('isLocal')."',(SELECT kode_kantor FROM tb_kantor WHERE isDefault = 1),FRMTGL,ORD) AS id_produk
				FROM
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
						COALESCE(MAX(CAST(RIGHT(id_produk,5) AS UNSIGNED)) + 1,1) AS ORD
						From tb_produk
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
			$id_supplier,
			$kode_produk,
			$nama_produk,
			$nama_umum,
			$produksi_oleh,
			$pencipta,
			$charge,
			$optr_charge,
			$charge_beli,
			$optr_charge_beli,
			$min_stock,
			$max_stock,
			$harga_minimum,
			$spek_produk,
			$ket_produk,
			$isNotActive,
			$isNotRetur,
			$stock,
			$dtstock,
			$isProduk,
			$kat_costumer_fee,
			$optr_kondisi_fee,
			$besar_penjualan_fee,
			$satuan_jual_fee,
			$optr_fee,
			$besar_fee,
			$isSattle,
			$buf_stock,
			$late_time,
			$jenis_moving,
			$hpp,
			$isNPKP,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_produk
				(
					id_produk,
					id_supplier,
					kode_produk,
					nama_produk,
					nama_umum,
					produksi_oleh,
					pencipta,
					charge,
					optr_charge,
					charge_beli,
					optr_charge_beli,
					min_stock,
					max_stock,
					harga_minimum,
					spek_produk,
					ket_produk,
					isNotActive,
					isNotRetur,
					stock,
					dtstock,
					isProduk,
					kat_costumer_fee,
					optr_kondisi_fee,
					besar_penjualan_fee,
					satuan_jual_fee,
					optr_fee,
					besar_fee,
					isSattle,
					buf_stock,
					late_time,
					jenis_moving,
					hpp,
					isNPKP,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					'".$id_produk."',
					'".$id_supplier."',
					'".$kode_produk."',
					'".$nama_produk."',
					'".$nama_umum."',
					'".$produksi_oleh."',
					'".$pencipta."',
					'".$charge."',
					'".$optr_charge."',
					'".$charge_beli."',
					'".$optr_charge_beli."',
					'".$min_stock."',
					'".$max_stock."',
					'".$harga_minimum."',
					'".$spek_produk."',
					'".$ket_produk."',
					'".$isNotActive."',
					'".$isNotRetur."',
					'".$stock."',
					'".$dtstock."',
					'".$isProduk."',
					'".$kat_costumer_fee."',
					'".$optr_kondisi_fee."',
					'".$besar_penjualan_fee."',
					'".$satuan_jual_fee."',
					'".$optr_fee."',
					'".$besar_fee."',
					'".$isSattle."',
					'".$buf_stock."',
					'".$late_time."',
					'".$jenis_moving."',
					'".$hpp."',
					'".$isNPKP."',
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
			$id_produk,
			$id_supplier,
			$kode_produk,
			$nama_produk,
			$nama_umum,
			$produksi_oleh,
			$pencipta,
			$charge,
			$optr_charge,
			$charge_beli,
			$optr_charge_beli,
			$min_stock,
			$max_stock,
			$harga_minimum,
			$spek_produk,
			$ket_produk,
			$isProduk,
			$kat_costumer_fee,
			$optr_kondisi_fee,
			$besar_penjualan_fee,
			$satuan_jual_fee,
			$optr_fee,
			$besar_fee,
			$isSattle,
			$buf_stock,
			$late_time,
			$jenis_moving,
			$isNPKP,
			$user_updt
		)
		{
			$strquery = "
					UPDATE tb_produk SET
						
						id_supplier = '".$id_supplier."',
						kode_produk = '".$kode_produk."',
						nama_produk = '".$nama_produk."',
						nama_umum = '".$nama_umum."',
						produksi_oleh = '".$produksi_oleh."',
						pencipta = '".$pencipta."',
						charge = '".$charge."',
						optr_charge = '".$optr_charge."',
						charge_beli = '".$charge_beli."',
						optr_charge_beli = '".$optr_charge_beli."',
						min_stock = '".$min_stock."',
						max_stock = '".$max_stock."',
						harga_minimum = '".$harga_minimum."',
						spek_produk = '".$spek_produk."',
						ket_produk = '".$ket_produk."',
						isProduk = '".$isProduk."',
						kat_costumer_fee = '".$kat_costumer_fee."',
						optr_kondisi_fee = '".$optr_kondisi_fee."',
						besar_penjualan_fee = '".$besar_penjualan_fee."',
						satuan_jual_fee = '".$satuan_jual_fee."',
						optr_fee = '".$optr_fee."',
						besar_fee = '".$besar_fee."',
						isSattle = '".$isSattle."',
						buf_stock = '".$buf_stock."',
						late_time = '".$late_time."',
						jenis_moving = '".$jenis_moving."',
						isNPKP = '".$isNPKP."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE id_produk = '".$id_produk
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_with_hpp_jasa
		(
		
			$id_produk,
			$id_supplier,
			$kode_produk,
			$nama_produk,
			$nama_umum,
			$produksi_oleh,
			$pencipta,
			$charge,
			$optr_charge,
			$charge_beli,
			$optr_charge_beli,
			$min_stock,
			$max_stock,
			$harga_minimum,
			$spek_produk,
			$ket_produk,
			$isProduk,
			$kat_costumer_fee,
			$optr_kondisi_fee,
			$besar_penjualan_fee,
			$satuan_jual_fee,
			$optr_fee,
			$besar_fee,
			$isSattle,
			$buf_stock,
			$late_time,
			$jenis_moving,
			$hpp,
			$user_updt
		)
		{
			$strquery = "
					UPDATE tb_produk SET
						
						id_supplier = '".$id_supplier."',
						kode_produk = '".$kode_produk."',
						nama_produk = '".$nama_produk."',
						nama_umum = '".$nama_umum."',
						produksi_oleh = '".$produksi_oleh."',
						pencipta = '".$pencipta."',
						charge = '".$charge."',
						optr_charge = '".$optr_charge."',
						charge_beli = '".$charge_beli."',
						optr_charge_beli = '".$optr_charge_beli."',
						min_stock = '".$min_stock."',
						max_stock = '".$max_stock."',
						harga_minimum = '".$harga_minimum."',
						spek_produk = '".$spek_produk."',
						ket_produk = '".$ket_produk."',
						isProduk = '".$isProduk."',
						kat_costumer_fee = '".$kat_costumer_fee."',
						optr_kondisi_fee = '".$optr_kondisi_fee."',
						besar_penjualan_fee = '".$besar_penjualan_fee."',
						satuan_jual_fee = '".$satuan_jual_fee."',
						optr_fee = '".$optr_fee."',
						besar_fee = '".$besar_fee."',
						isSattle = '".$isSattle."',
						buf_stock = '".$buf_stock."',
						late_time = '".$late_time."',
						jenis_moving = '".$jenis_moving."',
						hpp = '".$hpp."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE id_produk = '".$id_produk
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		
		function get_kode_produk($kode_kantor)
		{
			$query = 
				"
				SELECT CONCAT('".$kode_kantor."',FRMTGL,ORD) AS kode_produk
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
						COALESCE(MAX(CAST(RIGHT(kode_produk,5) AS UNSIGNED)) + 1,1) AS ORD
						From tb_produk
						WHERE DATE(tgl_ins) = DATE(NOW())
						AND kode_kantor = '".$kode_kantor."'
					) AS A
				) AS AA
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
	
		function list_kategori_produk($id_produk)
		{
			$query = 
			"
				SELECT 
					A.*
					,CASE 
						WHEN B.id_produk IS NULL THEN
							0
						ELSE
							1
						END AS AKTIF
				FROM tb_kat_produk AS A
				LEFT JOIN tb_prod_to_kat AS B ON A.id_kat_produk = B.id_kat_produk AND A.kode_kantor = B.kode_kantor AND B.id_produk = '".$id_produk."'
				WHERE A.kode_kantor = (SELECT kode_kantor FROM tb_kantor WHERE isDefault = 1)
				;
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
		
		function simpan_kat_to_prod($id_produk,$id_kat_produk,$kode_kantor)
		{
			$strquery = 
			"
				INSERT INTO tb_prod_to_kat
				(
					id_produk,
					id_kat_produk,
					tgl_ins,
					user_ins,
					kode_kantor
				)
				VALUES
				(
					'".$id_produk."',
					'".$id_kat_produk."',
					NOW(),
					'',
					'".$kode_kantor."'
				);
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_kat_to_prod($id_produk,$id_kat_produk)
		{
			$strquery = 
			"
				DELETE FROM tb_prod_to_kat WHERE id_produk = '".$id_produk."' AND id_kat_produk = '".$id_kat_produk."';
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function cek_kategori_produk($cari)
		{
			$query = 
			"
				SELECT
					A.*,COALESCE(B.kode_kat_produk,'') AS KODE_KATEGORI,COALESCE(B.nama_kat_produk,'') AS NAMA_KATEGORI
				FROM tb_prod_to_kat AS A
				LEFT JOIN tb_kat_produk AS B ON A.id_kat_produk = B.id_kat_produk AND A.kode_kantor = B.kode_kantor
				".$cari.";
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
	
		function hapus($berdasarkan,$id_produk)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_produk WHERE ".$berdasarkan." = '".$id_produk."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function ubah_aktif($id_produk,$kriteria)
		{
			$strquery = "
					UPDATE tb_produk SET
						".$kriteria." = CASE 
											WHEN ".$kriteria." = 1 THEN
												0
											ELSE
												1
											END
					WHERE id_produk = '".$id_produk
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
	}
?>