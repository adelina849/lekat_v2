<?php
	class M_gl_stock_produk extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_stock_produk_per_produk_jelek($kode_kantor,$dari,$sampai,$cari)
		{
			$query = "
			
				SELECT
					A.kode_kantor,A.id_produk,A.kode_produk,A.nama_produk,COALESCE(B.kode_satuan,'') AS kode_satuan
					
					,COALESCE(D.RETUR_PENJUALAN_JELEK,0) - COALESCE(D.RETUR_PEMBELIAN,0) AS ST_AWAL
					-- ,(COALESCE(D.RETUR_PENJUALAN_JELEK,0) - COALESCE(D.RETUR_PEMBELIAN,0)) + COALESCE(D.RETUR_PENJUALAN_BAGUS,0) AS ST_AWAL
					-- ,COALESCE(D.RETUR_PENJUALAN_BAGUS,0)
					
					,COALESCE(C.RETUR_PEMBELIAN,0) AS RETUR_PEMBELIAN
					,COALESCE(C.RETUR_PENJUALAN_JELEK,0) AS RETUR_PENJUALAN_JELEK
					-- ,COALESCE(C.RETUR_PENJUALAN_BAGUS,0) AS RETUR_PENJUALAN_BAGUS
				FROM tb_produk AS A
				LEFT JOIN 
				(
					SELECT DISTINCT A.kode_kantor,A.id_produk,A.id_satuan,COALESCE(B.kode_satuan,'') AS kode_satuan
					FROM tb_satuan_konversi AS A
					LEFT JOIN tb_satuan AS B ON A.kode_kantor = B.kode_kantor AND A.id_satuan = B.id_satuan
					WHERE besar_konversi = 1
					GROUP BY A.kode_kantor,A.id_produk,A.id_satuan,COALESCE(B.kode_satuan,'')
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
				LEFT JOIN
				(
					SELECT
						AAA.kode_kantor
						,AAA.id_produk
						,SUM(AAA.RETUR_PEMBELIAN) AS RETUR_PEMBELIAN
						,SUM(AAA.RETUR_PENJUALAN_JELEK) AS RETUR_PENJUALAN_JELEK
						-- ,SUM(AAA.RETUR_PENJUALAN_BAGUS) AS RETUR_PENJUALAN_BAGUS
					FROM
					(
						SELECT
							AA.kode_kantor
							,AA.id_h_penjualan
							,AA.id_produk
							,AA.jumlah
							,AA.jumlah_konversi
							,AA.kondisi
							,AA.STS
							,CASE 
								WHEN AA.STS = 'RETUR-PEMBELIAN' THEN
									AA.jumlah_konversi
								ELSE
									0
								END AS RETUR_PEMBELIAN
							,CASE 
								WHEN (AA.STS = 'RETUR-PENJUALAN') AND (kondisi = 'JELEK') THEN
									AA.jumlah_konversi
								ELSE
									0
								END AS RETUR_PENJUALAN_JELEK
							,CASE 
								WHEN (AA.STS = 'RETUR-PENJUALAN') AND ((kondisi = 'BAGUS') OR (kondisi = '') ) THEN
									AA.jumlah_konversi
								ELSE
									0
								END AS RETUR_PENJUALAN_BAGUS
						FROM
						(
							SELECT
								A.kode_kantor
								,A.id_h_penjualan
								,A.id_produk
								,A.jumlah
								,CASE 
									WHEN A.status_konversi = '*' THEN
										A.jumlah * A.konversi
									ELSE
										A.jumlah / A.konversi
									END AS jumlah_konversi
								,A.kondisi
								,COALESCE(B.status_penjualan,'') AS STS
							FROM tb_d_retur AS A
							INNER JOIN tb_h_retur AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							WHERE B.sts_penjualan = 'SELESAI'
							AND CASE WHEN COALESCE(B.status_penjualan,'') = 'RETUR-PENJUALAN' THEN kondisi = 'JELEK' ELSE kondisi = '' END 
							AND DATE(COALESCE(B.tgl_h_penjualan,'')) BETWEEN '".$dari."' AND '".$sampai."'
						) AS AA
					) AS AAA
					GROUP BY AAA.kode_kantor,AAA.id_produk
				) AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
				LEFT JOIN
				(
					SELECT
						AAA.kode_kantor
						,AAA.id_produk
						,SUM(AAA.RETUR_PEMBELIAN) AS RETUR_PEMBELIAN
						,SUM(AAA.RETUR_PENJUALAN_JELEK) AS RETUR_PENJUALAN_JELEK
						-- ,SUM(AAA.RETUR_PENJUALAN_BAGUS) AS RETUR_PENJUALAN_BAGUS
					FROM
					(
						SELECT
							AA.kode_kantor
							,AA.id_h_penjualan
							,AA.id_produk
							,AA.jumlah
							,AA.jumlah_konversi
							,AA.kondisi
							,AA.STS
							,CASE 
								WHEN AA.STS = 'RETUR-PEMBELIAN' THEN
									AA.jumlah_konversi
								ELSE
									0
								END AS RETUR_PEMBELIAN
							,CASE 
								WHEN (AA.STS = 'RETUR-PENJUALAN') AND (kondisi = 'JELEK') THEN
									AA.jumlah_konversi
								ELSE
									0
								END AS RETUR_PENJUALAN_JELEK
							,CASE 
								WHEN (AA.STS = 'RETUR-PENJUALAN') AND ((kondisi = 'BAGUS') OR (kondisi = '') ) THEN
									AA.jumlah_konversi
								ELSE
									0
								END AS RETUR_PENJUALAN_BAGUS
						FROM
						(
							SELECT
								A.kode_kantor
								,A.id_h_penjualan
								,A.id_produk
								,A.jumlah
								,CASE 
									WHEN A.status_konversi = '*' THEN
										A.jumlah * A.konversi
									ELSE
										A.jumlah / A.konversi
									END AS jumlah_konversi
								,A.kondisi
								,COALESCE(B.status_penjualan,'') AS STS
							FROM tb_d_retur AS A
							INNER JOIN tb_h_retur AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							WHERE B.sts_penjualan = 'SELESAI' 
							AND CASE WHEN COALESCE(B.status_penjualan,'') = 'RETUR-PENJUALAN' THEN kondisi = 'JELEK' ELSE kondisi = '' END 
							AND DATE(COALESCE(B.tgl_h_penjualan,'')) < '".$dari."'
						) AS AA
					) AS AAA
					GROUP BY AAA.kode_kantor,AAA.id_produk
				) AS D ON A.kode_kantor = D.kode_kantor AND A.id_produk = D.id_produk
				-- WHERE A.id_produk = 'ONYG12021100301501'

				WHERE
					A.kode_kantor = '".$kode_kantor."'
					AND 
					(
						A.kode_produk LIKE '%".$cari."%'
						OR A.nama_produk LIKE '%".$cari."%'
					)
					AND
					(
						COALESCE(C.RETUR_PEMBELIAN,0) > 0
						OR COALESCE(C.RETUR_PENJUALAN_JELEK,0) > 0
						-- OR COALESCE(C.RETUR_PENJUALAN_BAGUS,0) > 0
						
						OR COALESCE(D.RETUR_PEMBELIAN,0) > 0
						OR COALESCE(D.RETUR_PENJUALAN_JELEK,0) > 0
						-- OR COALESCE(D.RETUR_PENJUALAN_BAGUS,0) > 0
					);
			
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
		
		function list_stock_produk_per_kategori($kode_kantor,$tgl_dari,$tgl_sampai,$offset,$limit,$cari)
		{
			$query = "CALL SP_VIEW_STOCK_PER_KATEGORI_2('".$kode_kantor."','".$tgl_dari."','".$tgl_sampai."',".$offset.",".$limit.",'".$cari."');";
			
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
		
		function list_stock_produk_per_supplier($kode_kantor,$tgl_dari,$tgl_sampai,$offset,$limit,$cari)
		{
			$query = "CALL SP_VIEW_STOCK_PER_SUPPLIER_2('".$kode_kantor."','".$tgl_dari."','".$tgl_sampai."',".$offset.",".$limit.",'".$cari."');";
			
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
		
		function list_stock_produk($kode_kantor,$cari,$order_by,$offset,$limit,$tgl,$jam,$menit,$v_status_min)
		{
			$query = $this->db->query("CALL PROC_STOCK_PRODUK_2('". $kode_kantor ."','". $cari ."','". $order_by ."','". $offset ."','". $limit ."','". $tgl ."','". $jam ."','". $menit ."','".$v_status_min."')");
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_analisa_order($kode_kantor,$cari,$order_by,$offset,$limit,$tgl,$jam,$menit,$v_status_min)
		{
			$query = $this->db->query("CALL PROC_ANALISA_ORDER_2('". $kode_kantor ."','". $cari ."','". $order_by ."','". $offset ."','". $limit ."','". $tgl ."','". $jam ."','". $menit ."','".$v_status_min."')");
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_analisa_order_new_by_avg_3_bulan($kode_kantor,$cari,$order_by,$offset,$limit,$tgl)
		{
			$query = "
					SELECT 
						A.id_produk
						,A.kode_produk
						,A.nama_produk
						,A.jenis_moving
						,A.late_time
						,COALESCE(D.kode_satuan,'') AS kode_satuan
						,TIMESTAMPDIFF(DAY,DATE_SUB( DATE(NOW()) ,INTERVAL 3 MONTH),DATE(NOW())) AS selisih_hari
						,
						ROUND
						(
							COALESCE(B.JML_TERJUAL,0)
								/
							TIMESTAMPDIFF(DAY,DATE_SUB( DATE(NOW()) ,INTERVAL 3 MONTH),DATE(NOW()))
						)
						AS RATA_PERHARI
						,COALESCE(B.JML_TERJUAL,0) AS JML_TERJUAL_3BULAN
						,ROUND(COALESCE(B.JML_TERJUAL,0)/3,0) AS AVG_TERJUAL_3BULAN
						,ROUND((COALESCE(B.JML_TERJUAL,0)/100) * 20,0) AS BUF_20_TERJUAL
						,
						ROUND
						(
							COALESCE(B.JML_TERJUAL,0)
								/
							TIMESTAMPDIFF(DAY,DATE_SUB( DATE(NOW()) ,INTERVAL 3 MONTH),DATE(NOW()))
						)
						*
						A.late_time
						AS LEAD_TIME
						,
							ROUND((COALESCE(B.JML_TERJUAL,0)/100) * 20,0)
							+
							(
								ROUND
								(
									COALESCE(B.JML_TERJUAL,0)
										/
									TIMESTAMPDIFF(DAY,DATE_SUB( DATE(NOW()) ,INTERVAL 3 MONTH),DATE(NOW()))
								)
								*
								A.late_time
							)
							AS MORDER
							
							,COALESCE(C.harga_jual,0) AS HARGA_MODAL
							,COALESCE(E.jumlah,0) AS TLH_ORDER
							,COALESCE(E.kode_satuan,'') AS kode_satuan_tlh_order
						
					FROM tb_produk AS A
					LEFT JOIN
					(
						SELECT 
							A.id_produk
							,CASE WHEN A.status_konversi = '*' THEN
								SUM(A.jumlah * A.konversi)
							ELSE
								SUM(A.jumlah / A.konversi)
							END AS JML_TERJUAL
							,A.kode_kantor
						FROM tb_d_penjualan AS A
						INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor 
						WHERE A.kode_kantor = '".$kode_kantor."'
						AND B.sts_penjualan NOT IN ('PENDING','DIBATALKAN')
						AND COALESCE(B.sts_penjualan,'') = 'SELESAI'
						AND LEFT(B.ket_penjualan,7) <> 'DIHAPUS'
						AND DATE(A.tgl_ins) <= DATE('".$tgl."') AND DATE(A.tgl_ins) >= DATE_SUB( DATE('".$tgl."') ,INTERVAL 3 MONTH)
						-- AND A.isProduk IN ('PRODUK')
						GROUP BY A.id_produk,A.status_konversi,A.kode_kantor
					) AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
					LEFT JOIN tb_satuan_konversi AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk AND C.besar_konversi = '1'
					LEFT JOIN tb_satuan AS D ON A.kode_kantor = D.kode_kantor AND C.id_satuan = D.id_satuan
					LEFT JOIN tb_temp_d_pembelian AS E ON A.id_produk = E.id_produk AND A.kode_kantor = E.kode_kantor
					WHERE A.kode_kantor = '".$kode_kantor."' AND (A.kode_produk LIKE '%".$cari."%' OR A.nama_produk LIKE '%".$cari."%')
					AND A.isProduk IN ('PRODUK')
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
		
		function list_analisa_order_new_by_avg_3_bulan_use_procedure($kode_kantor,$cari,$order_by,$offset,$limit,$tgl)
		{
			$query = "CALL SP_VIEW_STOCK_2_ANALISA_ORDER('".$kode_kantor."','".$tgl."','".$tgl."',".$offset.",".$limit.",'".$cari."');";
			
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
		
		
		function count_stock_produk($cari)
		{
			$query = $this->db->query("SELECT COUNT(id_produk) AS JUMLAH FROM tb_produk WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND isProduk IN ('PRODUK','CONSUMABLE') AND (kode_produk LIKE  '%".$cari."%' OR nama_produk LIKE  '%".$cari."%' OR produksi_oleh LIKE  '%".$cari."%')");
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function sum_stock_produk($kode_kantor,$cari,$tgl,$jam,$menit)
		{
			$query = $this->db->query("CALL PROC_STOCK_PRODUK_PERSEDIAAN_2('". $kode_kantor ."','". $cari ."','". $tgl ."','". $jam ."','". $menit ."')");
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_laporan_detail_penjualan($cari,$order_by,$limit,$offset)
		{
			$query = 
					"
						SELECT
							A.*
							,COALESCE(B.no_faktur,'') AS no_faktur
							,COALESCE(B.no_antrian,'') AS no_antrian
							,COALESCE(B.no_costmer,'') AS no_costumer
							,COALESCE(B.nama_costumer,'') AS nama_costumer
							,COALESCE(B.tgl_h_penjualan,'') AS tgl_h_penjualan
							,COALESCE(B.type_h_penjualan,'') AS type_h_penjualan
						FROM tb_d_penjualan AS A
						INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
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
		
		function count_laporan_detail_penjualan($cari)
		{
			$query = 
					"
						SELECT 
							COUNT(A.id_d_penjualan) AS JUMLAH
							,SUM(
									CASE WHEN A.status_konversi = '*' THEN
										(A.jumlah * A.konversi)
									ELSE
										(A.jumlah / A.konversi)
									END
								) AS CNT_KELUAR
							,MIN(A.satuan_jual) AS STN
						FROM tb_d_penjualan AS A
						INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
						".$cari."
						
						GROUP BY A.status_konversi
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
		
		function list_laporan_detail_penerimaan($cari,$order_by,$limit,$offset)
		{
			$query = 
					"
						SELECT 
							A.*
							,COALESCE(B.no_surat_jalan,'') AS no_surat_jalan
							,COALESCE(B.nama_pengirim,'') AS nama_pengirim
							,COALESCE(B.cara_pengiriman,'') AS cara_pengiriman
							,COALESCE(DATE(B.tgl_terima),'0000-00-00') AS tgl_terima
							,COALESCE(C.no_h_pembelian,'') AS NO_PO
							,COALESCE(D.nama_supplier,'') AS nama_supplier
						FROM tb_d_penerimaan AS A
						INNER JOIN tb_h_penerimaan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penerimaan = B.id_h_penerimaan
						LEFT JOIN tb_h_pembelian AS C ON A.kode_kantor = B.kode_kantor AND B.id_h_pembelian = C.id_h_pembelian
						LEFT JOIN tb_supplier AS D ON A.kode_kantor = D.kode_kantor AND C.id_supplier = D.id_supplier
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
		
		function count_laporan_detail_penerimaan($cari)
		{
			$query = 
					"
						SELECT 
							COUNT(A.id_d_penerimaan) AS JUMLAH
							,SUM(
									CASE WHEN A.status_konversi = '*' THEN
										(A.diterima * A.konversi)
									ELSE
										(A.diterima / A.konversi)
									END
								) AS CNT_TERIMA
							,MIN(A.kode_satuan) AS STN
						FROM tb_d_penerimaan AS A
						INNER JOIN tb_h_penerimaan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penerimaan = B.id_h_penerimaan
						LEFT JOIN tb_h_pembelian AS C ON A.kode_kantor = B.kode_kantor AND B.id_h_pembelian = C.id_h_pembelian
						LEFT JOIN tb_supplier AS D ON A.kode_kantor = D.kode_kantor AND C.id_supplier = D.id_supplier
						".$cari."
						
						GROUP BY A.status_konversi
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
		
		function list_laporan_detail_mutasi($cari,$order_by,$limit,$offset)
		{
			$query = 
					"
						SELECT
							A.*
							,COALESCE(B.no_faktur,'') AS no_faktur
							,COALESCE(B.no_faktur_penjualan,'') AS no_faktur_penjualan
							,COALESCE(DATE(B.tgl_h_penjualan),'') AS tgl_h_penjualan
							,COALESCE(B.status_penjualan,'') AS status_penjualan
							,COALESCE(B.sts_penjualan,'') AS sts_penjualan
							,
							CASE 
							WHEN A.status_konversi = '*' THEN
								A.jumlah * A.konversi
							ELSE
								A.jumlah / A.konversi
							END AS mutasi_fix
						FROM tb_d_mutasi AS A
						INNER JOIN tb_h_mutasi AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
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
		
		function count_laporan_detail_mutasi($cari)
		{
			$query = 
					"
						SELECT
							COUNT(A.id_d_penjualan) AS JUMLAH
						FROM tb_d_mutasi AS A
						INNER JOIN tb_h_mutasi AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
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
	
		function list_laporan_detail_retur($cari,$order_by,$limit,$offset)
		{
			$query = 
					"
						SELECT
							A.*
							,COALESCE(B.no_faktur,'') AS no_faktur
							,COALESCE(B.no_faktur_penjualan,'') AS no_faktur_penjualan
							,COALESCE(DATE(B.tgl_h_penjualan),'') AS tgl_h_penjualan
							,COALESCE(B.status_penjualan,'') AS status_penjualan
							,COALESCE(B.sts_penjualan,'') AS sts_penjualan
							,COALESCE(C.kode_supplier,'') AS kode_supplier
							,COALESCE(C.nama_supplier,'') AS nama_supplier
							
							,COALESCE(D.no_costumer,'') AS no_costumer
							,COALESCE(D.nama_lengkap,'') AS nama_lengkap
							
							,
							CASE 
								WHEN A.status_konversi = '*' THEN
									A.jumlah * A.konversi
								ELSE
									A.jumlah / A.konversi
								END AS retur_fix
						FROM tb_d_retur AS A
						INNER JOIN tb_h_retur AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
						LEFT JOIN tb_supplier AS C ON A.kode_kantor = C.kode_kantor AND B.id_supplier = C.id_supplier
						LEFT JOIN tb_costumer AS D ON A.kode_kantor = D.kode_kantor AND B.id_costumer = D.id_costumer
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
		
		function count_laporan_detail_retur($cari)
		{
			$query = 
					"
						SELECT
							COUNT(A.id_d_penjualan) AS JUMLAH
						FROM tb_d_retur AS A
						INNER JOIN tb_h_retur AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
						LEFT JOIN tb_supplier AS C ON A.kode_kantor = C.kode_kantor AND B.id_supplier = C.id_supplier
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
	
		function list_histori_produk($kode_kantor,$id_produk,$dari,$sampai,$waktu_st_awal,$st_awal)
		{
			$query =
				"
					SELECT
						A.DATE_LIST
						,COALESCE(B.BELI,0) AS BELI
						,COALESCE(C.TERJUAL,0) AS TERJUAL
						,COALESCE(D.MUTASI_IN,0) AS MUTASI_IN
						,COALESCE(D.MUTASI_OUT,0) AS MUTASI_OUT
						,COALESCE(D.BERTAMBAH_GUDANG,0) AS BERTAMBAH_GUDANG
						,COALESCE(D.BERKURANG_GUDANG,0) AS BERKURANG_GUDANG
						,COALESCE(D.BERTAMBAH_TOKO,0) AS BERTAMBAH_TOKO
						,COALESCE(D.BERKURANG_TOKO,0) AS BERKURANG_TOKO
						,COALESCE(E.RETUR_PEMBELIAN,0) AS RETUR_PEMBELIAN
						,COALESCE(E.RETUR_PENJUALAN,0) AS RETUR_PENJUALAN
					From
					(
						select * from
						(select adddate('1970-01-01',t4.i*10000 + t3.i*1000 + t2.i*100 + t1.i*10 + t0.i) AS DATE_LIST from
						 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
						 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
						 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
						 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3,
						 (select 0 i union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t4) v
						where DATE_LIST BETWEEN DATE('".$dari."') AND DATE('".$sampai."')
					) AS A
					Left Join
					(
						SELECT DT_TERIMA,id_produk,SUM(BELI) AS BELI
						FROM
						(
							SELECT 
								DATE(A.tgl_ins) As DT_TERIMA
								,A.id_produk
								,CASE WHEN (A.status_konversi = '*') THEN
									SUM(A.diterima_satuan_beli * A.konversi) 
								ELSE
									SUM(A.diterima_satuan_beli / A.konversi) 
								END AS BELI
								-- ,A.tgl_ins
								-- ,A.kode_kantor
							FROM tb_d_penerimaan AS A
							INNER JOIN tb_h_penerimaan AS B ON A.id_h_penerimaan = B.id_h_penerimaan AND A.kode_kantor = B.kode_kantor
							WHERE 
							A.kode_kantor = '".$kode_kantor."'
							AND A.tgl_ins > '".$waktu_st_awal."'
							-- AND (A.tgl_ins) <= CONCAT(V_tgl_stock_sampai,' 23:59:00')
							AND A.id_produk = '".$id_produk."'
							GROUP BY DATE(A.tgl_ins),A.id_produk,A.status_konversi,A.diterima_satuan_beli,A.konversi 
							-- ,A.tgl_ins,A.kode_kantor
						
							/*
							SELECT
								DATE(B.tgl_terima) As DT_TERIMA
								,A.id_produk
								,CASE WHEN (A.status_konversi = '*') THEN
									SUM(A.diterima_satuan_beli * A.konversi)
								Else
									SUM(A.diterima_satuan_beli / A.konversi)
								END As BELI
							FROM tb_d_penerimaan AS A
							INNER JOIN tb_h_penerimaan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penerimaan = B.id_h_penerimaan
							WHERE A.id_produk = '".$id_produk."' AND A.kode_kantor = '".$kode_kantor."'
							GROUP BY B.tgl_terima,A.id_produk,A.status_konversi
							*/
							
						) AS BELI
						GROUP BY DT_TERIMA,id_produk
					) AS B ON A.DATE_LIST = B.DT_TERIMA
					Left Join
					(
						
														
						
						
						SELECT DT_PENJUALAN,id_produk,SUM(TERJUAL) AS TERJUAL
						FROM
						(
						
							
							SELECT 
								COALESCE(DATE(A.tgl_ins),'') As DT_PENJUALAN
								,A.id_produk
								,CASE WHEN A.status_konversi = '*' THEN
									SUM(A.jumlah * A.konversi)
								ELSE
									SUM(A.jumlah / A.konversi)
								END AS TERJUAL
								-- ,A.tgl_ins
								-- ,A.kode_kantor
							FROM tb_d_penjualan AS A
							INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND B.sts_penjualan NOT IN ('PENDING','DIBATALKAN')
							AND COALESCE(B.sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
							AND LEFT(B.ket_penjualan,7) <> 'DIHAPUS'
							-- AND A.tgl_ins > @tgl_stock
							-- AND (A.tgl_ins) <= CONCAT(V_tgl_stock_sampai,' 23:59:00')
							AND A.tgl_ins > '".$waktu_st_awal."'
							AND A.id_produk = '".$id_produk."'
							GROUP BY COALESCE(DATE(A.tgl_ins),''),A.id_produk,A.status_konversi,A.jumlah,A.konversi
							-- ,A.tgl_ins,A.kode_kantor
							
							/*
							SELECT
								DATE(A.tgl_h_penjualan) As DT_PENJUALAN
								,B.id_produk
								,CASE WHEN (B.status_konversi = '*') THEN
									SUM(B.jumlah * B.konversi)
								Else
									SUM(B.jumlah / B.konversi)
								END As TERJUAL
							FROM tb_h_penjualan AS A
							INNER JOIN tb_d_penjualan AS B ON A.id_h_penjualan = B.id_h_penjualan
							AND A.kode_kantor = B.kode_kantor
							WHERE B.id_produk = '".$id_produk."' AND A.kode_kantor = '".$kode_kantor."' AND COALESCE(A.sts_penjualan,'') = 'SELESAI' 
							-- AND B.isReady = 1
							GROUP BY A.tgl_h_penjualan,B.id_produk,B.status_konversi
							*/
							
						) AS TERJUAL
						GROUP BY DT_PENJUALAN,id_produk
						
						
					) AS C ON A.DATE_LIST = C.DT_PENJUALAN
					Left Join
					(
						SELECT
							DATE(tgl_h_penjualan) As DT_MUTASI
							,id_produk
							,SUM(MUTASI_IN) AS MUTASI_IN
							,SUM(MUTASI_OUT) AS MUTASI_OUT
							,SUM(BERTAMBAH_GUDANG) AS BERTAMBAH_GUDANG
							,SUM(BERKURANG_GUDANG) AS BERKURANG_GUDANG
							,SUM(BERTAMBAH_TOKO) AS BERTAMBAH_TOKO
							,SUM(BERKURANG_TOKO) AS BERKURANG_TOKO
						From
						(
							SELECT
								DATE(B.tgl_ins) AS tgl_h_penjualan
								,B.id_produk
								,CASE WHEN A.status_penjualan = 'MUTASI-IN' THEN
									CASE WHEN (B.status_konversi = '*') THEN
										SUM(B.jumlah * B.konversi)
									Else
										SUM(B.jumlah / B.konversi)
									End
								Else
									0
								END As MUTASI_IN
								,CASE WHEN A.status_penjualan = 'MUTASI-OUT' THEN
									CASE WHEN (B.status_konversi = '*') THEN
										SUM(B.jumlah * B.konversi)
									Else
										SUM(B.jumlah / B.konversi)
									End
								Else
									0
								END As MUTASI_OUT
								,CASE WHEN A.status_penjualan = 'Mutasi-Toko-Gudang' THEN
									CASE WHEN (B.status_konversi = '*') THEN
										SUM(B.jumlah * B.konversi)
									Else
										SUM(B.jumlah / B.konversi)
									End
								Else
									0
								END As BERTAMBAH_GUDANG
								,CASE WHEN (A.status_penjualan = 'Mutasi-Toko') OR ( (A.status_penjualan <> 'Mutasi-Toko-Gudang' AND A.status_penjualan <> 'Mutasi-Toko' AND A.status_penjualan <> 'Mutasi-Toko-Toko' AND A.status_penjualan <> 'Mutasi-Toko Luar-Toko' AND A.status_penjualan <> 'MUTASI-IN' AND A.status_penjualan <> 'MUTASI-OUT' AND A.status_penjualan <> 'STOCK-OPNAME')) THEN
									CASE WHEN (A.status_penjualan = 'Mutasi-Toko') THEN
										CASE WHEN (B.status_konversi = '*') THEN
											SUM(B.jumlah_req * B.konversi)
										Else
											SUM(B.jumlah_req / B.konversi)
										End
									Else
										CASE WHEN (B.status_konversi = '*') THEN
											SUM(B.jumlah * B.konversi)
										Else
											SUM(B.jumlah / B.konversi)
										End
									End
								Else
									0
								END As BERKURANG_GUDANG
								,CASE WHEN (A.status_penjualan = 'Mutasi-Toko Luar-Toko') OR (A.status_penjualan = 'Mutasi-Toko') THEN
									CASE WHEN (A.status_penjualan = 'Mutasi-Toko') THEN
										CASE WHEN (B.status_konversi = '*') THEN
											SUM(B.jumlah_req * B.konversi)
										Else
											SUM(B.jumlah_req / B.konversi)
										End
									Else
										CASE WHEN (B.status_konversi = '*') THEN
											SUM(B.jumlah * B.konversi)
										Else
											SUM(B.jumlah / B.konversi)
										End
									End
								Else
									0
								END As BERTAMBAH_TOKO
								,CASE WHEN (A.status_penjualan = 'Mutasi-Toko-Toko') OR (A.status_penjualan = 'Mutasi-Toko-Gudang') THEN
									CASE WHEN (B.status_konversi = '*') THEN
										SUM(B.jumlah * B.konversi)
									Else
										SUM(B.jumlah / B.konversi)
									End
								Else
									0
								END As BERKURANG_TOKO
							FROM tb_h_mutasi AS A
							INNER JOIN tb_d_mutasi AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
							WHERE B.id_produk = '".$id_produk."' 
							AND A.kode_kantor = '".$kode_kantor."' 
							AND COALESCE(A.sts_penjualan,'') = 'SELESAI'
							AND B.tgl_ins > '".$waktu_st_awal."'
							GROUP BY DATE(B.tgl_ins),B.id_produk,A.status_penjualan
						) AS AA
						GROUP BY DATE(tgl_h_penjualan),id_produk
					) AS D ON A.DATE_LIST = D.DT_MUTASI
					Left Join
					(
						SELECT DATE(tgl_h_penjualan) AS DT_RETUR, id_produk, SUM(RETUR_PEMBELIAN) AS RETUR_PEMBELIAN, SUM(RETUR_PENJUALAN) AS RETUR_PENJUALAN
						From
						(
							SELECT
								COALESCE(DATE(B.tgl_ins),'') AS tgl_h_penjualan
								,B.id_produk
								,CASE WHEN A.status_penjualan = 'RETUR-PEMBELIAN' THEN
									CASE WHEN (B.status_konversi = '*') THEN
										SUM(B.jumlah * B.konversi)
									Else
										SUM(B.jumlah / B.konversi)
									End
								Else
									0
								END As RETUR_PEMBELIAN
								,CASE WHEN A.status_penjualan = 'RETUR-PENJUALAN' THEN
									CASE WHEN (B.status_konversi = '*') THEN
										SUM(B.jumlah * B.konversi)
									Else
										SUM(B.jumlah / B.konversi)
									End
								Else
									0
								END As RETUR_PENJUALAN
							FROM tb_h_retur AS A
							INNER JOIN tb_d_retur AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
							WHERE B.id_produk = '".$id_produk."' AND A.kode_kantor = '".$kode_kantor."' AND COALESCE(A.sts_penjualan,'') = 'SELESAI'
							AND B.tgl_ins > '".$waktu_st_awal."'
							GROUP BY COALESCE(DATE(B.tgl_ins),''),B.id_produk,A.status_penjualan
						) AS AA
						GROUP BY tgl_h_penjualan, id_produk
					) AS E ON A.DATE_LIST = E.DT_RETUR;
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
	
		function list_rata_produk_terjual($dari,$sampai,$cari,$order_by,$limit,$offset)
		{
			$query = 
					"
						SELECT id_produk
							-- ,no_faktur
							,kode_produk,nama_produk,satuan_jual,harga,harga_dasar_ori,SUM(jumlah_konversi_format) AS jumlah,(SUM(jumlah) / (DATEDIFF('".$sampai."','".$dari."')+1)) AS RATA
						FROM
						(
							SELECT
								A.*
								,
								CASE WHEN A.status_konversi = '*' THEN
									A.jumlah * A.konversi
								ELSE
									A.jumlah / A.konversi
								END AS jumlah_konversi_format
								,COALESCE(B.no_faktur,'') AS no_faktur
								,COALESCE(B.no_costmer,'') AS no_costumer
								,COALESCE(B.nama_costumer,'') AS nama_costumer
								,COALESCE(B.sts_penjualan,'') AS sts_penjualan
								,COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') AS tgl_h_penjualan
								,COALESCE(C.kode_produk,'') AS kode_produk
								,COALESCE(C.nama_produk,'') AS nama_produk
							FROM tb_d_penjualan AS A
							INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
							LEFT JOIN tb_produk AS C ON A.id_produk = C.id_produk AND A.kode_kantor = C.kode_kantor
							WHERE A.isProduk = 'PRODUK'
						) AS AA
						
						".$cari."
						
						GROUP BY id_produk
								-- ,no_faktur
								,kode_produk,nama_produk,satuan_jual,harga,harga_dasar_ori
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
		
		function count_rata_produk_terjual($cari)
		{
			$query = 
					"
						SELECT COUNT(id_produk) AS JUMLAH
						FROM
						(
							SELECT id_produk,kode_produk,nama_produk,satuan_jual,SUM(jumlah_konversi_format) AS jumlah,(SUM(jumlah_konversi_format) / DATEDIFF('2020-08-05','2020-08-03')) AS RATA
							FROM
							(
								SELECT
									A.*
									,
									CASE WHEN A.status_konversi = '*' THEN
										A.jumlah * A.konversi
									ELSE
										A.jumlah / A.konversi
									END AS jumlah_konversi_format
									,COALESCE(B.no_faktur,'') AS no_faktur
									,COALESCE(B.no_costmer,'') AS no_costumer
									,COALESCE(B.nama_costumer,'') AS nama_costumer
									,COALESCE(B.sts_penjualan,'') AS sts_penjualan
									,COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') AS tgl_h_penjualan
									,COALESCE(C.kode_produk,'') AS kode_produk
									,COALESCE(C.nama_produk,'') AS nama_produk
								FROM tb_d_penjualan AS A
								INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
								LEFT JOIN tb_produk AS C ON A.id_produk = C.id_produk AND A.kode_kantor = C.kode_kantor
							) AS AA
							".$cari."
							GROUP BY id_produk,kode_produk,nama_produk,satuan_jual
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
		
		
		function M_ajax_update_penerimaan($kode_kantor,$id_d_penerimaan,$id_produk,$konversi,$harga_beli)
		{
			$strquery = "
						UPDATE tb_d_penerimaan 
							SET
								diterima = 	CASE WHEN status_konversi = '*' THEN
												diterima_satuan_beli * ".$konversi."
											ELSE
												diterima_satuan_beli / ".$konversi."
											END
								,konversi = '".$konversi."'
								,harga_beli = '".$harga_beli."'
								,harga_konversi = 	
											CASE WHEN status_konversi = '*' THEN
												".$harga_beli." / ".$konversi."
											ELSE
												".$harga_beli." * ".$konversi."
											END
						WHERE kode_kantor = '".$kode_kantor."' AND id_d_penerimaan = '".$id_d_penerimaan."' AND id_produk = '".$id_produk."'
					";
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function M_ajax_update_penjualan($kode_kantor,$id_d_penjualan,$id_produk,$konversi,$harga)
		{
			$strquery = "
						UPDATE tb_d_penjualan
							SET
								jumlah_konversi = 	CASE WHEN status_konversi = '*' THEN
												jumlah * ".$konversi."
											ELSE
												jumlah / ".$konversi."
											END
								,konversi = '".$konversi."'
								,harga = '".$harga."'
								,harga_konversi = 	
											CASE WHEN status_konversi = '*' THEN
												".$harga." / ".$konversi."
											ELSE
												".$harga." * ".$konversi."
											END
						WHERE kode_kantor = '".$kode_kantor."' AND id_d_penjualan = '".$id_d_penjualan."' AND id_produk = '".$id_produk."'
					";
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	}
?>