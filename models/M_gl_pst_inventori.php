<?php
	class M_gl_pst_inventori extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_pergerakan_produk($kode_kantor,$dari,$sampai,$cari,$opcd)
		{
			$query = 
					"
					SELECT * FROM
					(
						-- PEMBELIAN
							SELECT
								A.kode_kantor
								,'PEMBELIAN' AS OPCD
								,(A.tgl_ins) As DT_TERIMA
								,C.no_h_pembelian
								,A.id_produk
								,A.diterima_satuan_beli
								,A.kode_satuan
							FROM tb_d_penerimaan AS A
							INNER JOIN tb_h_penerimaan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penerimaan = B.id_h_penerimaan
							INNER JOIN tb_h_pembelian AS C ON A.kode_kantor = C.kode_kantor AND B.id_h_pembelian = C.id_h_pembelian
							-- GROUP BY B.tgl_terima,A.id_produk,A.status_konversi
							WHERE C.sts_pembelian = 'SELESAI' AND (B.tgl_ins)  >= '".$dari." 00:00:00' AND (B.tgl_ins) <= '".$sampai." 23:59:00'
							
						-- PEMBELIAN
						UNION ALL
						-- PENJUALAN
							SELECT
								A.kode_kantor
								-- ,DATE(A.tgl_h_penjualan) As DT_PENJUALAN
								,'PENJUALAN' AS OPCD
								,COALESCE(B.tgl_ins,'') As DT_PENJUALAN
								,A.no_faktur
								,B.id_produk
								,B.jumlah
								,B.satuan_jual
							FROM tb_h_penjualan AS A
							INNER JOIN tb_d_penjualan AS B ON A.id_h_penjualan = B.id_h_penjualan
							AND A.kode_kantor = B.kode_kantor
							WHERE COALESCE(A.sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
							AND (B.tgl_ins)  >= '".$dari." 00:00:00' AND (B.tgl_ins) <= '".$sampai." 23:59:00'
							-- GROUP BY A.tgl_h_penjualan,B.id_produk,B.status_konversi
							
						-- PENJUALAN
						UNION ALL
						-- MUTASI OUT
							SELECT
								A.kode_kantor
								-- ,A.sts_penjualan
								,CASE 
									WHEN A.status_penjualan = 'MUTASI-OUT' THEN
										'MUTASI OUT'
									WHEN A.status_penjualan = 'MUTASI-IN' THEN
										'MUTASI IN'
									ELSE
										'MUTASI'
									END AS OPCD
								,COALESCE(B.tgl_ins,'') AS DT_MUTASI
								,A.no_faktur
								,B.id_produk
								,B.jumlah
								,B.satuan_jual
									
							FROM tb_h_mutasi AS A
							INNER JOIN tb_d_mutasi AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
							WHERE COALESCE(A.sts_penjualan,'') = 'SELESAI'
							AND (B.tgl_ins)  >= '".$dari." 00:00:00' AND (B.tgl_ins) <= '".$sampai." 23:59:00'
							
						-- MUTASI OUT
						UNION ALL
						-- RETUR
							SELECT
								A.kode_kantor
								,CASE 
									WHEN A.status_penjualan = 'RETUR-PEMBELIAN' THEN
										'RETUR PEMBELIAN'
									WHEN A.status_penjualan = 'RETUR-PENJUALAN' THEN
										'RETUR PENJUALAN'
									ELSE
										'RETUR'
									END AS OPCD
								,B.tgl_ins AS DT_RETUR
								,A.no_faktur
								,B.id_produk
								,B.jumlah
								,B.satuan_jual
							FROM tb_h_retur AS A
							INNER JOIN tb_d_retur AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
							WHERE COALESCE(A.sts_penjualan,'') = 'SELESAI'
							AND (B.tgl_ins)  >= '".$dari." 00:00:00' AND (B.tgl_ins) <= '".$sampai." 23:59:00'
						-- RETUR
					) AS AA
					LEFT JOIN tb_produk AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_produk = BB.id_produk
					WHERE BB.isProduk IN ('PRODUK','CONSUMABLE') 
					AND (BB.kode_produk LIKE '%".$cari."%' OR BB.nama_produk LIKE '%".$cari."%') 
					AND (AA.kode_kantor LIKE '%".$kode_kantor."%') 
					AND (AA.OPCD LIKE '%".$opcd."%')
					
					AND CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
						AA.kode_kantor LIKE '%".$kode_kantor."%'
					ELSE
						BB.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
					END
						
						
					ORDER BY DT_TERIMA DESC;
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
		
		
		function list_produk_terlaris($kode_kantor,$dari,$sampai,$cari,$order_by)
		{
			$query = 
					"
					SELECT * FROM
					(
						SELECT
							AA.kode_kantor
							,AA.id_produk
							,COALESCE(BB.kode_produk,'') AS kode_produk
							,COALESCE(BB.nama_produk,'') AS nama_produk
							,SUM(AA.jumlah_konversi) AS jumlah_konversi
							,AA.kode_satuan_default
							,SUM(AA.SUBTOTAL) AS SUBTOTAL
							,COUNT(AA.id_produk) AS CNT
						FROM
						(
							SELECT
								A.kode_kantor
								-- ,DATE(A.tgl_h_penjualan) As DT_PENJUALAN
								-- ,'PENJUALAN' AS OPCD
								,COALESCE(B.tgl_ins,'') As DT_PENJUALAN
								,B.id_produk
								,B.jumlah
								,B.satuan_jual
								,CASE
									WHEN status_konversi = '%' THEN
										jumlah / konversi
									ELSE
										jumlah * konversi
									END AS jumlah_konversi
								,CASE
									WHEN status_konversi = '%' THEN
										harga * konversi
									ELSE
										harga / konversi
									END AS harga_konversi
								,COALESCE(D.kode_satuan,'') AS kode_satuan_default
								,jumlah * harga AS SUBTOTAL
							FROM tb_h_penjualan AS A
							INNER JOIN tb_d_penjualan AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
							LEFT JOIN tb_satuan_konversi AS C ON A.kode_kantor = C.kode_kantor AND B.id_produk = C.id_produk AND C.besar_konversi = '1'
							LEFT JOIN tb_satuan AS D ON A.kode_kantor = D.kode_kantor AND C.id_satuan = D.id_satuan
							WHERE COALESCE(A.sts_penjualan,'') = 'SELESAI'
							AND (B.tgl_ins)  >= '".$dari." 00:00:00' AND (B.tgl_ins) <= '".$sampai." 23:59:00'
						) AS AA
						LEFT JOIN tb_produk AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_produk = BB.id_produk
						WHERE BB.isProduk IN ('PRODUK','CONSUMABLE')
						
						AND CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
							AA.kode_kantor LIKE '%%'
						ELSE
							BB.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
						END
						
						GROUP BY
						AA.kode_kantor
						,AA.id_produk
						,COALESCE(BB.kode_produk,'')
						,COALESCE(BB.nama_produk,'')
						,AA.kode_satuan_default
					) AS AAA
					WHERE (AAA.kode_produk LIKE '%".$cari."%' OR AAA.nama_produk LIKE '%".$cari."%') AND (AAA.kode_kantor LIKE '%".$kode_kantor."%')
					".$order_by."
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
		
		function list_pemesanan_produk_belum_full($kode_kantor,$dari,$sampai,$cari,$order_by)
		{
			//BERDASARKAN YANG BELUM DI TERIMA
			$query =
			"
				SELECT
					A.*
					,
						CASE 
						WHEN optr_diskon = '%' THEN
							harga + ((harga/100) * diskon)
						ELSE
							harga + diskon
						END AS HARGA_FIX
					,COALESCE(B.no_h_pembelian,'') AS no_h_pembelian
					,DATE(COALESCE(B.tgl_h_pembelian,'')) AS tgl_h_pembelian
					,COALESCE(C.kode_produk,'') AS kode_produk
					,COALESCE(C.nama_produk,'') AS nama_produk
					,COALESCE(D.TERIMA,0) AS TERIMA
					,COALESCE(E.kode_supplier,'') AS kode_supplier
					,COALESCE(E.nama_supplier,'') AS nama_supplier
				FROM tb_d_pembelian AS A
				INNER JOIN tb_h_pembelian AS B ON A.id_h_pembelian = B.id_h_pembelian AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
				LEFT JOIN
				(
					SELECT 
						A.kode_kantor
						,COALESCE(B.id_h_pembelian,'') AS id_h_pembelian
						,id_produk
						,SUM(diterima_satuan_beli) AS TERIMA
					FROM tb_d_penerimaan AS A 
					INNER JOIN tb_h_penerimaan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penerimaan = B.id_h_penerimaan
					GROUP BY A.kode_kantor,id_produk,COALESCE(B.id_h_pembelian,'')
				) AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_pembelian = D.id_h_pembelian AND A.id_produk = D.id_produk
				LEFT JOIN tb_supplier AS E ON A.kode_kantor = E.kode_kantor AND B.id_supplier = E.id_supplier
				
				WHERE 
				A.kode_kantor LIKE '%".$kode_kantor."%' 
				AND COALESCE(B.sts_pembelian,'') = 'SELESAI'
				AND COALESCE(DATE(B.tgl_h_pembelian),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
				AND A.jumlah > COALESCE(D.TERIMA,0)
				AND ( COALESCE(C.kode_produk,'') LIKE '%".$cari."%' OR COALESCE(C.nama_produk,'') LIKE '%".$cari."%')
				
				AND CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
					A.kode_kantor LIKE '%'
				ELSE
					B.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
				END
				
			";
			//BERDASARKAN YANG BELUM DI TERIMA
			
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
		
		//function list_stock_produk($kode_kantor,$cari,$order_by,$offset,$limit,$tgl,$jam,$menit,$v_status_min)
		function list_stock_produk($kode_kantor,$dari,$sampai,$offset,$limit,$cari)
		{
			//$query = $this->db->query("CALL PROC_STOCK_PRODUK_2('". $kode_kantor ."','". $cari ."','". $order_by ."','". $offset ."','". $limit ."','". $tgl ."','". $jam ."','". $menit ."','".$v_status_min."')");
			
			$query = $this->db->query("CALL SP_VIEW_STOCK_2('". $kode_kantor ."','".$dari."','".$sampai."',".$offset.",".$limit.",'".$cari."');");
			
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_stock_produk_v3_with_order($kode_kantor,$dari,$sampai,$offset,$limit,$cari,$order_by)
		{
			//$query = $this->db->query("CALL PROC_STOCK_PRODUK_2('". $kode_kantor ."','". $cari ."','". $order_by ."','". $offset ."','". $limit ."','". $tgl ."','". $jam ."','". $menit ."','".$v_status_min."')");
			
			$query = $this->db->query("CALL SP_VIEW_STOCK_3('". $kode_kantor ."','".$dari."','".$sampai."',".$offset.",".$limit.",'".$cari."','".$order_by."');");
			
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_stock_produk_harga_jual($kode_kantor,$dari,$sampai,$offset,$limit,$cari)
		{
			//$query = $this->db->query("CALL PROC_STOCK_PRODUK_2('". $kode_kantor ."','". $cari ."','". $order_by ."','". $offset ."','". $limit ."','". $tgl ."','". $jam ."','". $menit ."','".$v_status_min."')");
			
			$query = $this->db->query("CALL SP_VIEW_STOCK_2_FOR_PUSAT_HARGA_JUAL('". $kode_kantor ."','".$dari."','".$sampai."',".$offset.",".$limit.",'".$cari."');");
			
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
			
		function count_stock_produk($kode_kantor,$cari)
		{
			$query = $this->db->query("SELECT COUNT(id_produk) AS JUMLAH FROM tb_produk WHERE kode_kantor = '".$kode_kantor."' AND (kode_produk LIKE  '%".$cari."%' OR nama_produk LIKE  '%".$cari."%' OR produksi_oleh LIKE  '%".$cari."%')");
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
	}
?>