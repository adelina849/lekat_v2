<?php
	class M_gl_d_pembelian extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_d_pembelian($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT *
					,CASE
						WHEN optr_diskon = '%' THEN
							(harga/100) * diskon
						ELSE
							diskon
						END AS besar_diskon
					,CASE
						WHEN optr_diskon = '%' THEN
							jumlah * (harga - ((harga/100) * diskon))
						ELSE
							jumlah * (harga-diskon)
						END AS subtotal
					
				FROM tb_d_pembelian AS A
				LEFT JOIN tb_produk AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor
				".$cari."
				".$order_by."
				LIMIT ".$offset.",".$limit."
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
		
		function count_d_pembelian($cari)
		{
			$query =
			"
				SELECT COUNT(A.id_d_pembelian) AS JUMLAH FROM tb_d_pembelian AS A
				LEFT JOIN tb_produk AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor
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
			$id_h_pembelian,
			$id_produk,
			$jumlah,
			$harga,
			$harga_dasar,
			$diskon,
			$optr_diskon,
			$kode_satuan,
			$nama_satuan,
			$status_konversi,
			$konversi,
			$acc,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_d_pembelian
				(
					id_d_pembelian,
					id_h_pembelian,
					id_produk,
					jumlah,
					harga,
					harga_dasar,
					diskon,
					optr_diskon,
					kode_satuan,
					nama_satuan,
					status_konversi,
					konversi,
					acc,
					tgl_ins,
					tgl_updt,
					user_updt,
					user_ins,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_d_pembelian
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
								COALESCE(MAX(CAST(RIGHT(id_d_pembelian,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_d_pembelian
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_h_pembelian."',
					'".$id_produk."',
					'".$jumlah."',
					'".$harga."',
					'".$harga_dasar."',
					'".$diskon."',
					'".$optr_diskon."',
					'".$kode_satuan."',
					'".$nama_satuan."',
					'".$status_konversi."',
					'".$konversi."',
					'".$acc."',
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
			$id_d_pembelian,
			$id_h_pembelian,
			$id_produk,
			$jumlah,
			$harga,
			$harga_dasar,
			$diskon,
			$optr_diskon,
			$kode_satuan,
			$nama_satuan,
			$status_konversi,
			$konversi,
			$acc,
			$user_updt,
			$kode_kantor
		)
		{
			
			/*HARGA DASAR JANGAN IKUT DIUBAH, KARENA HANYA BERUBAH HARGA ATAU JUMLAHTIDAK AKAN BERPENGARUJ KEPADA HARGRA DASAR*/
				$strquery = "
						UPDATE tb_d_pembelian SET
							id_h_pembelian = '".$id_h_pembelian."',
							id_produk = '".$id_produk."',
							jumlah = '".$jumlah."',
							harga = '".$harga."',
							harga_dasar = '".$harga_dasar."',
							diskon = '".$diskon."',
							optr_diskon = '".$optr_diskon."',
							kode_satuan = '".$kode_satuan."',
							nama_satuan = '".$nama_satuan."',
							status_konversi = '".$status_konversi."',
							konversi = '".$konversi."',
							acc = '".$acc."',
							tgl_updt = NOW(),
							user_updt = '".$user_updt."'
						WHERE kode_kantor = '".$kode_kantor."' AND id_d_pembelian = '".$id_d_pembelian
						."'
						";
			/*HARGA DASAR JANGAN IKUT DIUBAH, KARENA HANYA BERUBAH HARGA ATAU JUMLAHTIDAK AKAN BERPENGARUJ KEPADA HARGRA DASAR*/
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_with_ket_d_penjualan
		(
			$id_d_pembelian,
			$id_h_pembelian,
			$id_produk,
			$jumlah,
			$harga,
			$harga_dasar,
			$diskon,
			$optr_diskon,
			$kode_satuan,
			$nama_satuan,
			$status_konversi,
			$konversi,
			$acc,
			$ket_d_penjualan,
			$user_updt,
			$kode_kantor
		)
		{
			
			/*HARGA DASAR JANGAN IKUT DIUBAH, KARENA HANYA BERUBAH HARGA ATAU JUMLAHTIDAK AKAN BERPENGARUJ KEPADA HARGRA DASAR*/
				$strquery = "
						UPDATE tb_d_pembelian SET
							id_h_pembelian = '".$id_h_pembelian."',
							id_produk = '".$id_produk."',
							jumlah = '".$jumlah."',
							harga = '".$harga."',
							harga_dasar = '".$harga_dasar."',
							diskon = '".$diskon."',
							optr_diskon = '".$optr_diskon."',
							kode_satuan = '".$kode_satuan."',
							nama_satuan = '".$nama_satuan."',
							status_konversi = '".$status_konversi."',
							konversi = '".$konversi."',
							acc = '".$acc."',
							ket_d_penjualan = '".$ket_d_penjualan."',
							tgl_updt = NOW(),
							user_updt = '".$user_updt."'
						WHERE kode_kantor = '".$kode_kantor."' AND id_d_pembelian = '".$id_d_pembelian
						."'
						";
			/*HARGA DASAR JANGAN IKUT DIUBAH, KARENA HANYA BERUBAH HARGA ATAU JUMLAHTIDAK AKAN BERPENGARUJ KEPADA HARGRA DASAR*/
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($cari)
		{
			/*HAPUS JABATAN*/
				//$strquery = "DELETE FROM tb_d_pembelian WHERE ".$berdasarkan." = '".$id_d_pembelian."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
				$strquery = "DELETE FROM tb_d_pembelian ".$cari;
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_data_by_query($query)
        {
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
		
		function get_d_pembelian($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_d_pembelian', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_d_pembelian_by_query($cari)
        {
			$query =
			"
				SELECT * FROM tb_d_pembelian ".$cari."
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
		
		function get_d_pembelian_by_query_for_cetak_po($id_supplier,$cari)
		{
			/*
			$query =
			"
				SELECT
					A.*
					,COALESCE(B.kode_produk,'') AS kode_produk
					,COALESCE(B.nama_produk,'') AS nama_produk
					,CASE 
					WHEN optr_diskon = '%' THEN
						(A.harga/100) * A.diskon
					ELSE
						A.diskon
					END AS besar_diskon_ori
				FROM tb_d_pembelian AS A
				LEFT JOIN tb_produk AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor
				".$cari."
			";
			*/
			
			$query =
			"
				SELECT
					A.*
					, 
					CASE WHEN COALESCE(C.kode_produk,'') = '' THEN
						COALESCE(B.kode_produk,'')
					ELSE
						COALESCE(C.kode_produk,'')
					END AS kode_produk
					,
					CASE WHEN COALESCE(C.nama_produk,'') = '' THEN
						COALESCE(B.nama_produk,'')
					ELSE
						COALESCE(C.nama_produk,'')
					END AS nama_produk
					
					,CASE 
					WHEN optr_diskon = '%' THEN
						(A.harga/100) * A.diskon
					ELSE
						A.diskon
					END AS besar_diskon_ori
				FROM tb_d_pembelian AS A
				LEFT JOIN tb_produk AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_alias_produk AS C ON A.kode_kantor = C.kode_kantor AND B.id_produk = C.id_produk AND C.grup = 'PURCHASE' AND C.id_supplier = '".$id_supplier."'
				".$cari."
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
	}
?>