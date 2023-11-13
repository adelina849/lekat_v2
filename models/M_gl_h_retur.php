<?php
	class M_gl_h_retur extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_h_penjualan($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT A.*, DATE(A.tgl_h_penjualan) AS DT_PENJUALAN
				FROM tb_h_penjualan AS A
				".$cari."
				".$order_by."
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
		
		function list_h_pembelian($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT
					A.*
					,DATE(A.tgl_h_pembelian) AS DT_PEMBELIAN
					,COALESCE(B.kode_supplier,'') AS kode_supplier
					,COALESCE(B.nama_supplier,'') AS nama_supplier
					,COALESCE(B.tlp,'') AS tlp
					,COALESCE(B.alamat,'') AS alamat
				FROM tb_h_pembelian AS A
				LEFT JOIN tb_supplier AS B ON A.id_supplier = B.id_supplier AND A.kode_kantor = B.kode_kantor
				".$cari."
				".$order_by."
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
		
		
		function list_supplier($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT * FROM tb_supplier AS A
				".$cari."
				".$order_by."
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
		
		function list_h_retur_supplier($cari,$order_by,$limit,$offset)
		{
			
			$query =
			"
				SELECT 
					A.* 
					,COALESCE(B.no_supplier,'') AS no_supplier
					,COALESCE(B.nama_supplier,'') AS NAMA_SUPPLIER
					,COALESCE(B.tlp,'') AS TLP_SUPPLIER
					,COALESCE(B.alamat,'') AS ALAMAT_SUPPLIER
					
					,COALESCE(C.no_costumer,'') AS no_costumer
					,COALESCE(C.nama_lengkap,'') AS nama_lengkap
					,COALESCE(C.alamat_rumah_sekarang,'') AS alamat_rumah_sekarang
					
					,DATE(A.tgl_h_penjualan) AS DT_PENJUALAN
				FROM tb_h_retur AS A
				LEFT JOIN tb_supplier AS B ON A.id_supplier = B.id_supplier AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_costumer AS C ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
				".$cari."
				".$order_by."
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
	
		function get_id_h_penjualan_retur($kode_kantor)
		{
			
			$query =
			"
				SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_h_penjualan
				From
				(
					SELECT CONCAT(Y,M,D) AS FRMTGL,Y,M,D
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
						COALESCE(MAX(CAST(RIGHT(id_h_penjualan,5) AS UNSIGNED)) + 1,1) AS ORD
						From tb_h_retur
						WHERE DATE(tgl_ins) = DATE(NOW())
						AND kode_kantor = '".$kode_kantor."'
					) AS A
				) AS AA
				;
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
		
		function get_no_faktur_retur($jenis,$kode_kantor)
		{
			if($jenis == 'RETUR-PENJUALAN')
			{
				$jenis_fix = 'RC';
			}
			elseif($jenis == 'RETUR-PEMBELIAN')
			{
				$jenis_fix = 'RS';
			}
			else
			{
				$jenis_fix = 'RB';
			}
			$query =
			"
				SELECT CONCAT(FRMTGL,'/".$jenis_fix."/',ORD) AS NO_FAKTUR
				From
				(
					SELECT CONCAT(Y,M,D) AS FRMTGL,Y,M,D
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
						COALESCE(MAX(CAST(RIGHT(no_faktur,5) AS UNSIGNED)) + 1,1) AS ORD
						From tb_h_retur
						WHERE DATE(tgl_ins) = DATE(NOW())
						AND status_penjualan = '".$jenis."'
						AND kode_kantor = '".$kode_kantor."'
					) AS A
				) AS AA
				;
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
	
		function list_d_retur_produk($cari,$order_by,$limit,$offset)
		{
			$query =
				"
					SELECT * FROM tb_d_retur AS A
					LEFT JOIN tb_produk AS B ON A.id_produk = B.id_produk 
					AND A.kode_kantor = B.kode_kantor 
					".$cari." ".$order_by." 
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
		
		
		function get_h_retur_by_query($cari)
		{
			$query = "SELECT * FROM tb_h_retur ".$cari."";
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
		
		function get_d_retur_by_query($cari)
		{
			$query = "SELECT * FROM tb_d_retur ".$cari."";
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
		
		
		function simpan_h_retur
		(
			$id_h_penjualan,
			$id_h_pemesanan,
			$id_supplier,
			$id_costumer,
			$id_karyawan,
			$id_penerimaan,
			$no_faktur,
			$no_faktur_penjualan,
			$biaya,
			$nominal_retur,
			$tgl_pengajuan,
			$tgl_h_penjualan,
			$tgl_tempo,
			$status_penjualan,
			$ket_penjualan,
			$type_h_penjualan,
			$user_updt,
			$kode_kantor,
			$sts_penjualan,
			$alasan_retur

		)
		{
			$strquery = "
				INSERT INTO tb_h_retur
				(
				
					id_h_penjualan,
					id_h_pemesanan,
					id_supplier,
					id_costumer,
					id_karyawan,
					id_penerimaan,
					no_faktur,
					no_faktur_penjualan,
					biaya,
					nominal_retur,
					tgl_pengajuan,
					tgl_h_penjualan,
					tgl_tempo,
					status_penjualan,
					ket_penjualan,
					type_h_penjualan,
					tgl_ins,
					tgl_updt,
					user_updt,
					kode_kantor,
					sts_penjualan,
					alasan_retur
				)
				VALUES
				(
					'".$id_h_penjualan."',
					'".$id_h_pemesanan."',
					'".$id_supplier."',
					'".$id_costumer."',
					'".$id_karyawan."',
					'".$id_penerimaan."',
					'".$no_faktur."',
					'".$no_faktur_penjualan."',
					'".$biaya."',
					'".$nominal_retur."',
					'".$tgl_pengajuan."',
					'".$tgl_h_penjualan."',
					'".$tgl_tempo."',
					'".$status_penjualan."',
					'".$ket_penjualan."',
					'".$type_h_penjualan."',
					NOW(),
					NOW(),
					'".$user_updt."',
					'".$kode_kantor."',
					'".$sts_penjualan."',
					'".$alasan_retur."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function simpan_d_retur
		(
			$id_d_penjualan_retur,
			$id_h_penjualan,
			$id_h_penjualan_retur,
			$id_d_penerimaan,
			$id_produk,
			$jumlah,
			$status_konversi,
			$konversi,
			$satuan_jual,
			$jumlah_konversi,
			$harga,
			$harga_konversi,
			$harga_ori,
			$ket_d_penjualan,
			$user_updt,
			$kode_kantor,
			$optr_charge,
			$charge,
			$diskon,
			$kondisi
		)
		{
			$strquery = "
				INSERT INTO tb_d_retur
				(
					id_d_penjualan,
					id_d_penjualan_retur,
					id_h_penjualan,
					id_h_penjualan_retur,
					id_d_penerimaan,
					id_produk,
					jumlah,
					status_konversi,
					konversi,
					satuan_jual,
					jumlah_konversi,
					harga,
					harga_konversi,
					harga_ori,
					ket_d_penjualan,
					tgl_ins,
					tgl_updt,
					user_updt,
					kode_kantor,
					optr_charge,
					charge,
					diskon,
					kondisi
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_d_penjualan
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
								COALESCE(MAX(CAST(RIGHT(id_d_penjualan,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_d_retur
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_d_penjualan_retur."',
					'".$id_h_penjualan."',
					'".$id_h_penjualan_retur."',
					'".$id_d_penerimaan."',
					'".$id_produk."',
					'".$jumlah."',
					'".$status_konversi."',
					'".$konversi."',
					'".$satuan_jual."',
					'".$jumlah_konversi."',
					'".$harga."',
					'".$harga_konversi."',
					'".$harga_ori."',
					'".$ket_d_penjualan."',
					NOW(),
					NOW(),
					'".$user_updt."',
					'".$kode_kantor."',
					'".$optr_charge."',
					'".$charge."',
					'".$diskon."',
					'".$kondisi."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function ubah_d_retur
		(
			$id_d_penjualan_retur,
			$id_h_penjualan,
			$id_h_penjualan_retur,
			$id_d_penerimaan,
			$id_produk,
			$jumlah,
			$status_konversi,
			$konversi,
			$satuan_jual,
			$jumlah_konversi,
			$harga,
			$harga_konversi,
			$harga_ori,
			$ket_d_penjualan,
			$user_updt,
			$kode_kantor,
			$optr_charge,
			$charge,
			$diskon,
			$kondisi
		)
		{
			$strquery = "
					UPDATE tb_d_retur SET
					
						id_d_penjualan_retur = '".$id_d_penjualan_retur."',
						id_h_penjualan_retur = '".$id_h_penjualan_retur."',
						id_d_penerimaan = '".$id_d_penerimaan."',
						id_produk = '".$id_produk."',
						jumlah = '".$jumlah."',
						status_konversi = '".$status_konversi."',
						konversi = '".$konversi."',
						satuan_jual = '".$satuan_jual."',
						jumlah_konversi = '".$jumlah_konversi."',
						harga = '".$harga."',
						harga_konversi = '".$harga_konversi."',
						harga_ori = '".$harga_ori."',
						ket_d_penjualan = '".$ket_d_penjualan."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."',
						optr_charge = '".$optr_charge."',
						charge = '".$charge."',
						diskon = '".$diskon."',
						kondisi = '".$kondisi."'
						
						WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."' AND id_produk = '".$id_produk."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function ubah_h_retur
		(
			$id_h_penjualan,
			$id_h_pemesanan,
			$id_supplier,
			$id_costumer,
			$id_karyawan,
			$id_penerimaan,
			$no_faktur,
			$no_faktur_penjualan,
			$biaya,
			$nominal_retur,
			$tgl_pengajuan,
			$tgl_h_penjualan,
			$tgl_tempo,
			$status_penjualan,
			$ket_penjualan,
			$type_h_penjualan,
			$user_updt,
			$kode_kantor,
			$sts_penjualan,
			$alasan_retur
		)
		{
			$strquery = "
					UPDATE tb_h_retur SET
					
						id_h_pemesanan = '".$id_h_pemesanan."',
						id_supplier = '".$id_supplier."',
						id_costumer = '".$id_costumer."',
						id_karyawan = '".$id_karyawan."',
						id_penerimaan = '".$id_penerimaan."',
						no_faktur = '".$no_faktur."',
						no_faktur_penjualan = '".$no_faktur_penjualan."',
						biaya = '".$biaya."',
						nominal_retur = '".$nominal_retur."',
						tgl_pengajuan = '".$tgl_pengajuan."',
						tgl_h_penjualan = '".$tgl_h_penjualan."',
						tgl_tempo = '".$tgl_tempo."',
						status_penjualan = '".$status_penjualan."',
						ket_penjualan = '".$ket_penjualan."',
						type_h_penjualan = '".$type_h_penjualan."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."',
						kode_kantor = '".$kode_kantor."',
						sts_penjualan = '".$sts_penjualan."',
						alasan_retur = '".$alasan_retur."'
						
						WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function hapus_h_retur($cari)
		{
			$strquery = "DELETE FROM tb_h_retur ".$cari."";
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_d_retur($cari)
		{
			$strquery = "DELETE FROM tb_d_retur ".$cari."";
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	}
?>