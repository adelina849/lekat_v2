<?php
	class M_gl_d_penerimaan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function simpan
		(
			$id_h_penerimaan,
			$id_h_pembelian,
			$id_d_pembelian,
			$id_produk,
			$diterima,
			$konversi,
			$diterima_satuan_beli,
			$status_konversi,
			$kode_satuan,
			$nama_satuan,
			$harga_beli,
			$harga_konversi,
			$optr_diskon,
			$diskon,
			$tgl_exp,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_d_penerimaan
				(
					id_d_penerimaan,
					id_h_penerimaan,
					id_h_pembelian,
					id_d_pembelian,
					id_produk,
					diterima,
					konversi,
					diterima_satuan_beli,
					status_konversi,
					kode_satuan,
					nama_satuan,
					harga_beli,
					harga_konversi,
					optr_diskon,
					diskon,
					tgl_exp,
					tgl_ins,
					tgl_updt,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_d_penerimaan
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
								COALESCE(MAX(CAST(RIGHT(id_d_penerimaan,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_d_penerimaan
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_h_penerimaan."',
					'".$id_h_pembelian."',
					'".$id_d_pembelian."',
					'".$id_produk."',
					'".$diterima."',
					'".$konversi."',
					'".$diterima_satuan_beli."',
					'".$status_konversi."',
					'".$kode_satuan."',
					'".$nama_satuan."',
					'".$harga_beli."',
					'".$harga_konversi."',
					'".$optr_diskon."',
					'".$diskon."',
					'".$tgl_exp."',
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
			$id_d_penerimaan,
			$id_h_penerimaan,
			$id_h_pembelian,
			$id_d_pembelian,
			$id_produk,
			$diterima,
			$konversi,
			$diterima_satuan_beli,
			$status_konversi,
			$kode_satuan,
			$nama_satuan,
			$harga_beli,
			$harga_konversi,
			$tgl_exp,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_d_penerimaan SET
					
						id_h_penerimaan = '".$id_h_penerimaan."',
						id_h_pembelian = '".$id_h_pembelian."',
						id_d_pembelian = '".$id_d_pembelian."',
						id_produk = '".$id_produk."',
						diterima = '".$diterima."',
						konversi = '".$konversi."',
						diterima_satuan_beli = '".$diterima_satuan_beli."',
						status_konversi = '".$status_konversi."',
						kode_satuan = '".$kode_satuan."',
						nama_satuan = '".$nama_satuan."',
						harga_beli = '".$harga_beli."',
						harga_konversi = '".$harga_konversi."',
						tgl_exp = '".$tgl_exp."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'

					WHERE kode_kantor = '".$kode_kantor."' AND id_d_penerimaan = '".$id_d_penerimaan
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($cari)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_d_penerimaan ".$cari."";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_harga_dasar_satuan_konversi($kode_kantor,$id_produk,$kode_satuan,$harga)
		{
			/*HAPUS JABATAN*/
				$strquery = "UPDATE tb_satuan_konversi SET harga_jual = ".$harga." WHERE kode_kantor = '".$kode_kantor."' AND id_produk = '".$id_produk."' AND id_satuan = (SELECT DISTINCT id_satuan FROM tb_satuan WHERE kode_kantor = '".$kode_kantor."' AND kode_satuan = '".$kode_satuan."' GROUP BY id_satuan LIMIT 0,1);";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_d_penerimaan($cari)
        {
			$query=
			"
				SELECT * FROM tb_d_penerimaan ".$cari.";
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