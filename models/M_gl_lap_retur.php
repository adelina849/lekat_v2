<?php
	class M_gl_lap_retur extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_laporan_retur_limit($cari,$limit,$offset)
		{
			$query = "
					SELECT
						A.kode_kantor,A.id_h_penjualan, A.no_faktur,A.no_faktur_penjualan,A.status_penjualan,A.tgl_h_penjualan,A.tgl_ins
						,COALESCE(B.id_produk,'') AS id_produk
						,COALESCE(B.jumlah,0) AS jumlah
						,CASE WHEN COALESCE(B.status_konversi,'') = '*' THEN
							ROUND(COALESCE(B.jumlah,0) * COALESCE(B.konversi,0),2)
						ELSE
							ROUND(COALESCE(B.jumlah,0) / COALESCE(B.konversi,0),2)
						END AS jumlah_konversi
						,ROUND(COALESCE(B.harga,0),2) AS harga
						,CASE WHEN COALESCE(B.status_konversi,'') = '*' THEN
							ROUND(COALESCE(B.harga,0) / COALESCE(B.konversi,0),2)
						ELSE
							ROUND(COALESCE(B.harga,0) * COALESCE(B.konversi,0),2)
						END AS harga_konversi
						,COALESCE(B.satuan_jual,'') AS satuan_jual
						,COALESCE(C.kode_produk,'') AS kode_produk
						,COALESCE(C.nama_produk,'') AS nama_produk
						,COALESCE(D.no_costumer,'') AS no_costumer
						,COALESCE(D.nama_lengkap,'') AS nama_costumer
						,COALESCE(E.kode_supplier,'') AS kode_supplier
						,COALESCE(E.nama_supplier,'') AS nama_supplier
					FROM tb_h_retur AS A
					INNER JOIN tb_d_retur AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
					LEFT JOIN tb_produk AS C ON A.kode_kantor  = C.kode_kantor AND B.id_produk = C.id_produk
					LEFT JOIN tb_costumer AS D ON A.kode_kantor = D.kode_kantor AND A.id_costumer = D.id_costumer
					LEFT JOIN tb_supplier AS E ON A.kode_kantor = E.kode_kantor AND A.id_supplier = E.id_supplier
					
					".$cari." ORDER BY A.tgl_h_penjualan DESC, A.tgl_ins DESC LIMIT ".$offset.",".$limit."
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