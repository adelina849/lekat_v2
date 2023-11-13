<?php
	class M_gl_lap_apoteker extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_apoteker_today($cari,$order_by)
		{
			$query = "
					SELECT
						A.id_d_penjualan,
						A.id_h_penjualan,
						A.id_d_penerimaan,
						A.id_produk,
						A.id_h_diskon,
						A.id_d_diskon,
						A.jumlah,
						A.status_konversi,
						A.konversi,
						A.satuan_jual,
						A.jumlah_konversi,
						A.diskon,
						CASE WHEN A.optr_diskon = '' THEN
							'%'
						ELSE
							A.optr_diskon
						END AS optr_diskon,
						
						-- A.besar_diskon_ori,
						CASE WHEN (A.optr_diskon = '%') OR (A.optr_diskon = '') THEN
							((A.harga/100) * A.diskon)
						ELSE
							A.diskon
						END AS besar_diskon_ori,
						
						A.harga,
						/*
						CASE WHEN (A.optr_diskon = '%') OR (A.optr_diskon = '') THEN
							((A.harga/100) * A.diskon) + A.harga
						ELSE
							A.harga + A.diskon
						END AS harga,
						*/
						A.harga_konversi,
						A.harga_ori,
						A.harga_dasar_ori,
						A.stock,
						A.ket_d_penjualan,
						A.aturan_minum,
						A.isProduk,
						A.isReady,
						A.ket_ready,
						A.isTerima,
						A.id_ass,
						A.nama_ass,
						A.tgl_ins,
						A.tgl_updt,
						A.user_updt,
						A.user_ins,
						A.kode_kantor
						,COALESCE(B.no_faktur,'') AS no_faktur
						,COALESCE(B.tgl_h_penjualan,'') AS tgl_h_penjualan
						,COALESCE(B.tgl_ins,'') AS waktu_input
						,COALESCE(B.no_costmer,'') AS no_costumer
						,COALESCE(B.nama_costumer,'') AS nama_costumer
						,COALESCE(B.id_dokter,'') AS id_dokter
						,COALESCE(D.nama_karyawan,'') AS nama_dokter_konsul
						,COALESCE(B.id_dokter2,'') AS id_dokter2
						,COALESCE(E.nama_karyawan,'') AS nama_dokter_tindakan
						,COALESCE(C.kode_produk,'') AS kode_produk
						,COALESCE(C.nama_produk,'') AS nama_produk
						,COALESCE(C.isProduk,'') AS isProduk

					FROM tb_d_penjualan AS A
					INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
					LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
					LEFT JOIN tb_karyawan AS D ON A.kode_kantor = D.kode_kantor AND COALESCE(B.id_dokter,'') = D.id_karyawan
					LEFT JOIN tb_karyawan AS E ON A.kode_kantor = E.kode_kantor AND COALESCE(B.id_dokter2,'') = E.id_karyawan
					LEFT JOIN
					(
						SELECT kode_kantor,id_h_penjualan,SUM(nominal) AS NOMINAL
						FROM tb_d_penjualan_bayar
						GROUP BY kode_kantor,id_h_penjualan
					) AS F ON A.id_h_penjualan = F.id_h_penjualan AND A.kode_kantor = F.kode_kantor
					
					
					".$cari."
					".$order_by."
					
					/*
					WHERE A.kode_kantor = 'CJRT' 
					AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
					AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) = DATE(NOW())
					*/
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
		
		function ubah_status_ready($kode_kantor, $id_d_penjualan, $id_produk, $isReady, $ket_ready)
		{
			$strquery = "
					UPDATE tb_d_penjualan SET
						isReady = '".$isReady."',
						ket_ready = '".$ket_ready."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_d_penjualan = '".$id_d_penjualan."' AND id_produk = '".$id_produk."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_status_terima($kode_kantor, $id_d_penjualan, $id_produk, $isTerima)
		{
			$strquery = "
					UPDATE tb_d_penjualan SET
						isTerima = '".$isTerima."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_d_penjualan = '".$id_d_penjualan."' AND id_produk = '".$id_produk."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_d_penjualan_cari($cari)
		{
			$strquery = "
					DELETE FROM tb_d_penjualan ".$cari."
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	}
?>