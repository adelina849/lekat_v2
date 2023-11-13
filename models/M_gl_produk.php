<?php
	class M_gl_produk extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function exec_gen_query($strquery)
		{
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		
		}
		
		function exec_update_jenis_moving_produk($kode_kantor)
		{
			$strquery = "
				UPDATE tb_produk AS Z
						INNER JOIN
						(
							SELECT
								AA.*
								,CASE 
									WHEN (AA.jumlah > AA.ROW_FAST_START)THEN
										'FAST'
									WHEN (AA.jumlah > AA.ROW_MID_START) AND (AA.jumlah <= AA.ROW_MID_END) THEN
										'MID'
									-- WHEN (AA.jumlah > AA.ROW_SLOW_START) AND (AA.jumlah <= AA.ROW_SLOW_END) THEN
									ELSE
										'SLOW'
									END AS MOVING
							FROM
							(
								SELECT
									A.*
									,SUBSTRING_INDEX(A.ROL_SLOW,'-',1) AS ROW_SLOW_START
									,SUBSTRING_INDEX(A.ROL_SLOW,'-',-1) AS ROW_SLOW_END
									
									,SUBSTRING_INDEX(A.ROL_MID,'-',1) AS ROW_MID_START
									,SUBSTRING_INDEX(A.ROL_MID,'-',-1) AS ROW_MID_END
									
									,SUBSTRING_INDEX(A.ROL_FAST,'-',1) AS ROW_FAST_START
									,SUBSTRING_INDEX(A.ROL_FAST,'-',-1) AS ROW_FAST_END
								FROM
								(
									SELECT
										A.*
										,COALESCE(B.jumlah,0) AS jumlah
										,(SELECT nilai FROM tb_pengaturan WHERE id_pengaturan='10') AS ROL_SLOW
										,(SELECT nilai FROM tb_pengaturan WHERE id_pengaturan='11') AS ROL_MID
										,(SELECT nilai FROM tb_pengaturan WHERE id_pengaturan='12') AS ROL_FAST
									FROM tb_produk AS A
									LEFT JOIN(
										SELECT
											A.kode_kantor,A.id_produk,SUM(A.jumlah_konversi) AS jumlah
										FROM tb_d_penjualan AS A
										INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan  = B.id_h_penjualan
										WHERE COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
										AND A.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE
										AND MONTH(DATE(A.tgl_ins)) < MONTH(DATE_SUB(DATE(NOW()), INTERVAL 1 MONTH))
										AND YEAR(DATE(A.tgl_ins)) < YEAR(DATE_SUB(DATE(NOW()), INTERVAL 1 MONTH))
										GROUP BY A.kode_kantor,A.id_produk
									) AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
									WHERE A.kode_kantor = '".$kode_kantor."' 
									-- AND A.kode_produk = '307011'
								) AS A
							) AS AA
						) AS X ON Z.kode_kantor = X.kode_kantor AND Z.id_produk = X.id_produk
						SET Z.jenis_moving = X.MOVING
						WHERE Z.kode_kantor = '".$kode_kantor."'
						;
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		
		}
		
		
		function cek_dt_stock($kode_kantor)
		{
			$query =
			"
				SELECT MAX(dtstock) AS DT_STOCK,CASE WHEN DATE(MAX(dtstock)) = DATE(NOW()) THEN 1 ELSE 0 END AS STS_DTSTOCK
				FROM tb_produk
				WHERE kode_kantor = '".$kode_kantor."'
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
		
		function update_stock_tb_produk($kode_kantor,$tgl_dari,$tgl_sampai,$waktu_backup)
		{
			$strquery = "CALL SP_INSERT_STOCK_TO_TB_2('".$kode_kantor."','".$tgl_dari."','".$tgl_sampai."',0,99999,'','".$waktu_backup."');";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function list_kantor_with_produk_active($id_produk,$kode_kantor)
		{
			$query =
			"
				SELECT
					A.id_produk
					,A.kode_produk
					,A.nama_produk
					,A.isNotActive
					,A.kode_kantor
					,COALESCE(B.nama_kantor,'') AS nama_kantor
				FROM tb_produk AS A 
				LEFT JOIN tb_kantor AS B ON A.kode_kantor = B.kode_kantor
				WHERE A.id_produk = '".$id_produk."' AND A.kode_kantor <> '".$kode_kantor."'
				ORDER BY COALESCE(B.nama_kantor,'') ASC
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
		
		function list_produk_dan_kategori($kode_kantor,$id_kat_produk,$cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT * FROM
				(
					SELECT
						AA.id_produk
						,AA.kode_produk
						,AA.nama_produk
						,AA.isNotActive
						,AA.isProduk
						,AA.kode_kantor
						,GROUP_CONCAT(AA.id_kat_produk SEPARATOR ',') as id_kat_produk
						,COALESCE(AA.id_satuan,'') AS id_satuan
						,COALESCE(AA.besar_konversi,'') AS besar_konversi
						,COALESCE(AA.status_konversi,'') AS status_konversi
						,COALESCE(AA.kode_satuan,'') AS kode_satuan
						,COALESCE(AA.harga_jual,'') AS harga_jual
						
					FROM
					(
						SELECT 
							A.* 
							,COALESCE(B.id_kat_produk,'') AS id_kat_produk
							,COALESCE(C.id_satuan,'') AS id_satuan
							,COALESCE(C.besar_konversi,'') AS besar_konversi
							,COALESCE(C.status,'') AS status_konversi
							,COALESCE(D.kode_satuan,'') AS kode_satuan
							,COALESCE(C.harga_jual,'') AS harga_jual
						
						FROM tb_produk AS A
						LEFT JOIN tb_prod_to_kat AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
						LEFT JOIN tb_satuan_konversi AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk AND C.besar_konversi = 1
						LEFT JOIN tb_satuan AS D ON A.kode_kantor = D.kode_kantor AND C.id_satuan = D.id_satuan

					) AS AA
					GROUp BY 
						AA.id_produk
						,AA.kode_produk
						,AA.nama_produk
						,AA.isNotActive
						,AA.isProduk
						,AA.kode_kantor
						,COALESCE(AA.id_satuan,'')
						,COALESCE(AA.besar_konversi,'')
						,COALESCE(AA.status_konversi,'')
						,COALESCE(AA.kode_satuan,'')
						,COALESCE(AA.harga_jual,'')
				) AS AAA
				WHERE AAA.kode_kantor = '".$kode_kantor."' 
				AND (AAA.kode_produk LIKE '%".$cari."%' OR AAA.nama_produk LIKE '%".$cari."%') 
				AND AAA.id_kat_produk LIKE '%".$id_kat_produk."%'
				AND AAA.isNotActive = 0
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
		
		function list_produk_saja($cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT 
					A.*
					,COALESCE(B.id_satuan,'') AS id_satuan
					,COALESCE(B.besar_konversi,'') AS besar_konversi
					,COALESCE(B.status,'') AS status_konversi
					,COALESCE(C.kode_satuan,'') AS kode_satuan
				FROM tb_produk AS A
				LEFT JOIN tb_satuan_konversi AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk AND B.besar_konversi = 1
				LEFT JOIN tb_satuan AS C ON A.kode_kantor = C.kode_kantor AND B.id_satuan = C.id_satuan
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
		
		function list_produk_saja_dengan_hpp_untuk_excel($cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT
					A.*
					,COALESCE(B.id_satuan,'') AS id_satuan_1
					,COALESCE(B.status,'') AS optr_konversi_1
					,COALESCE(B.besar_konversi,'') AS besar_konversi_1
					,COALESCE(B.harga_jual,'') AS harga_jual_1
					,COALESCE(C.kode_satuan,'') AS kode_satuan_1
				FROM tb_produk AS A
				LEFT JOIN tb_satuan_konversi AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk AND B.besar_konversi = 1
				LEFT JOIN tb_satuan AS C ON A.kode_kantor = C.kode_kantor AND B.id_satuan = C.id_satuan
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
		
		function list_produk_saja_dengan_harga_jual_costumer_untuk_excel($cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT
					A.*
					,
					CASE WHEN ( (COALESCE(A.pencipta,'') <> '') AND (LENGTH(A.pencipta) > 2 ) ) THEN
						A.pencipta
					ELSE
						A.produksi_oleh
					END AS supplier
					
					,COALESCE(B.id_satuan,'') AS id_satuan_1
					,COALESCE(B.status,'') AS optr_konversi_hpp_1
					,COALESCE(B.besar_konversi,'') AS besar_konversi_hpp_1
					,COALESCE(B.harga_jual,'') AS harga_jual_hpp_1
					,COALESCE(C.kode_satuan,'') AS kode_satuan_hpp_1
					,COALESCE(E.nama_kat_costumer,'') AS nama_kat_costumer_harga
					,COALESCE(F.media,'') AS media_harga
					,COALESCE(F.harga,'') AS harga_jual
				FROM tb_produk AS A
				LEFT JOIN tb_satuan_konversi AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk AND B.besar_konversi = 1
				LEFT JOIN tb_satuan AS C ON A.kode_kantor = C.kode_kantor AND B.id_satuan = C.id_satuan
				LEFT JOIN tb_media_transaksi AS D ON A.kode_kantor = D.kode_kantor AND D.isDefault = 1
				LEFT JOIN tb_kat_costumer AS E ON A.kode_kantor = E.kode_kantor AND E.set_default = 1

				LEFT JOIN tb_produk_harga_satuan_costumer AS F 
					ON A.kode_kantor = F.kode_kantor 
					AND A.id_produk = F.id_produk 
					AND B.id_satuan = F.id_satuan 
					AND E.id_kat_costumer = F.id_costumer
					AND D.nama_media = F.media
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
		
		function list_produk_limit_harga_dasar_untuk_retur_pembelian($id_h_retur,$cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT
					A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh
					,
					CASE WHEN ( (COALESCE(A.pencipta,'') <> '') AND (LENGTH(A.pencipta) > 2 ) ) THEN
						A.pencipta
					ELSE
						A.produksi_oleh
					END AS supplier
					
					,COALESCE(B.id_satuan,'') AS id_satuan
					,COALESCE(C.kode_satuan,'') AS kode_satuan
					,COALESCE(B.status,'') AS status
					,COALESCE(B.besar_konversi,'') AS besar_konversi
					,COALESCE(B.harga_jual,'') AS harga_jual
					
				FROM tb_produk AS A
				LEFT JOIN tb_satuan_konversi AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor AND B.besar_konversi = 1
				LEFT JOIN tb_satuan AS C ON B.id_satuan = C.id_satuan AND A.kode_kantor = C.kode_kantor
				LEFT JOIN
				(
					SELECT A.id_produk,A.kode_kantor
					FROM tb_d_retur AS A
					INNER JOIN tb_h_retur AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
					WHERE A.id_h_penjualan = '".$id_h_retur."'
				) AS E ON A.id_produk = E.id_produk AND A.kode_kantor = E.kode_kantor
				".$cari."
				GROUP BY A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh
						,COALESCE(B.id_satuan,'')
						,COALESCE(C.kode_satuan,'')
						,COALESCE(B.status,'')
						,COALESCE(B.besar_konversi,'')
						,COALESCE(B.harga_jual,'')
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
		
		function list_produk_limit_harga_dasar_untuk_retur_penjualan($id_h_retur,$cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT
					A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh
					,
					CASE WHEN ( (COALESCE(A.pencipta,'') <> '') AND (LENGTH(A.pencipta) > 2 ) ) THEN
						A.pencipta
					ELSE
						A.produksi_oleh
					END AS supplier
					
					,COALESCE(B.id_satuan,'') AS id_satuan
					,COALESCE(C.kode_satuan,'') AS kode_satuan
					,COALESCE(B.status,'') AS status
					,COALESCE(B.besar_konversi,'') AS besar_konversi
					,COALESCE(B.harga_jual,'') AS harga_jual
					
				FROM tb_produk AS A
				LEFT JOIN tb_satuan_konversi AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor AND B.besar_konversi = 1
				LEFT JOIN tb_satuan AS C ON B.id_satuan = C.id_satuan AND A.kode_kantor = C.kode_kantor
				LEFT JOIN
				(
					SELECT A.id_produk,A.kode_kantor
					FROM tb_d_retur AS A
					INNER JOIN tb_h_retur AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
					WHERE A.id_h_penjualan = '".$id_h_retur."'
				) AS E ON A.id_produk = E.id_produk AND A.kode_kantor = E.kode_kantor
				".$cari."
				GROUP BY A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh
						,COALESCE(B.id_satuan,'')
						,COALESCE(C.kode_satuan,'')
						,COALESCE(B.status,'')
						,COALESCE(B.besar_konversi,'')
						,COALESCE(B.harga_jual,'')
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
		
		function list_produk_limit_harga_dasar_untuk_Mutasi_out($id_h_mutasi_out,$cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT
					A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh
					,
					CASE WHEN ( (COALESCE(A.pencipta,'') <> '') AND (LENGTH(A.pencipta) > 2 ) ) THEN
						A.pencipta
					ELSE
						A.produksi_oleh
					END AS supplier
					
					,COALESCE(B.id_satuan,'') AS id_satuan
					,COALESCE(C.kode_satuan,'') AS kode_satuan
					,COALESCE(B.status,'') AS status
					,COALESCE(B.besar_konversi,'') AS besar_konversi
					,COALESCE(B.harga_jual,'') AS harga_jual
					
				FROM tb_produk AS A
				LEFT JOIN tb_satuan_konversi AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor AND B.besar_konversi = 1
				LEFT JOIN tb_satuan AS C ON B.id_satuan = C.id_satuan AND A.kode_kantor = C.kode_kantor
				LEFT JOIN
				(
					SELECT A.id_produk,A.kode_kantor
					FROM tb_d_mutasi AS A
					INNER JOIN tb_h_mutasi AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
					WHERE A.id_h_penjualan = '".$id_h_mutasi_out."'
				) AS E ON A.id_produk = E.id_produk AND A.kode_kantor = E.kode_kantor
				".$cari."
				GROUP BY A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh
						,COALESCE(B.id_satuan,'')
						,COALESCE(C.kode_satuan,'')
						,COALESCE(B.status,'')
						,COALESCE(B.besar_konversi,'')
						,COALESCE(B.harga_jual,'')
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
		
		function list_produk_limit_harga_dasar_untuk_PO_ori_harga_dasar($id_h_pembelian,$cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT
					A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh,A.isProduk
					,
					CASE WHEN ( (COALESCE(A.pencipta,'') <> '') AND (LENGTH(A.pencipta) > 2 ) ) THEN
						A.pencipta
					ELSE
						A.produksi_oleh
					END AS supplier
						
					,COALESCE(B.id_satuan,'') AS id_satuan
					,COALESCE(C.kode_satuan,'') AS kode_satuan
					,COALESCE(B.status,'') AS status
					,COALESCE(B.besar_konversi,'') AS besar_konversi
					,COALESCE(B.harga_jual,'') AS harga_jual
					,COALESCE(B.harga_jual,'') AS harga_dasar
					,MAX(COALESCE(D.img_nama,'')) AS img_nama
					,MAX(COALESCE(D.img_file,'')) AS img_file
					,MAX(COALESCE(D.img_url,'')) AS img_url
					,MAX(COALESCE(D.ket_img,'')) AS ket_img
					,GROUP_CONCAT(COALESCE(F.id_kat_produk,'') SEPARATOR ',') AS id_kat_produk
				FROM tb_produk AS A
				LEFT JOIN tb_satuan_konversi AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor AND B.besar_konversi = 1
				LEFT JOIN tb_satuan AS C ON B.id_satuan = C.id_satuan AND A.kode_kantor = C.kode_kantor
				LEFT JOIN tb_images AS D ON A.id_produk = D.id AND A.kode_kantor = D.kode_kantor AND D.group_by = 'produks'
				LEFT JOIN
				(
					SELECT A.id_produk,A.kode_kantor
					FROM tb_d_pembelian AS A
					INNER JOIN tb_h_pembelian AS B ON A.id_h_pembelian = B.id_h_pembelian AND A.kode_kantor = B.kode_kantor
					WHERE A.id_h_pembelian = '".$id_h_pembelian."'
				) AS E ON A.id_produk = E.id_produk AND A.kode_kantor = E.kode_kantor
				LEFT JOIN tb_prod_to_kat AS F ON A.kode_kantor = F.kode_kantor AND A.id_produk = F.id_produk
				".$cari."
				GROUP BY A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh
						,COALESCE(B.id_satuan,'')
						,COALESCE(C.kode_satuan,'')
						,COALESCE(B.status,'')
						,COALESCE(B.besar_konversi,'')
						,COALESCE(B.harga_jual,'')
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
		
		function list_produk_limit_harga_dasar_untuk_PO($id_h_pembelian,$cari,$order_by,$limit,$offset)
		{
			//,COALESCE(B.harga_jual,'') AS harga_jual
			//,COALESCE(BB.harga,B.harga_jual) AS harga_jual
			$query = 
			"
				SELECT
					A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh,A.isProduk
					,
					CASE WHEN ( (COALESCE(A.pencipta,'') <> '') AND (LENGTH(A.pencipta) > 2 ) ) THEN
						A.pencipta
					ELSE
						A.produksi_oleh
					END AS supplier
					
					,COALESCE(B.id_satuan,'') AS id_satuan
					,COALESCE(C.kode_satuan,'') AS kode_satuan
					,COALESCE(B.status,'') AS status
					,COALESCE(B.besar_konversi,'') AS besar_konversi
					,COALESCE(B.harga_jual,0) AS harga_dasar
					,COALESCE(BB.harga,0) AS harga_jual
					,MAX(COALESCE(D.img_nama,'')) AS img_nama
					,MAX(COALESCE(D.img_file,'')) AS img_file
					,MAX(COALESCE(D.img_url,'')) AS img_url
					,MAX(COALESCE(D.ket_img,'')) AS ket_img
					,GROUP_CONCAT(COALESCE(F.id_kat_produk,'') SEPARATOR ',') AS id_kat_produk
					
				FROM tb_produk AS A
				LEFT JOIN tb_satuan_konversi AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor AND B.besar_konversi = 1
				
				LEFT JOIN tb_produk_harga_satuan_costumer  AS BB 
				ON A.kode_kantor = BB.kode_kantor AND A.id_produk = BB.id_produk AND B.id_satuan = BB.id_satuan
				AND BB.id_costumer = (SELECT id_kat_costumer FROM tb_kat_costumer WHERE set_default = '1' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' GROUP BY id_kat_costumer) 
				AND BB.media = (SELECT nama_media FROM tb_media_transaksi WHERE isDefault = '1' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' GROUP BY nama_media)
				
				LEFT JOIN tb_satuan AS C ON B.id_satuan = C.id_satuan AND A.kode_kantor = C.kode_kantor
				LEFT JOIN tb_images AS D ON A.id_produk = D.id AND A.kode_kantor = D.kode_kantor AND D.group_by = 'produks'
				LEFT JOIN
				(
					SELECT A.id_produk,A.kode_kantor
					FROM tb_d_pembelian AS A
					INNER JOIN tb_h_pembelian AS B ON A.id_h_pembelian = B.id_h_pembelian AND A.kode_kantor = B.kode_kantor
					WHERE A.id_h_pembelian = '".$id_h_pembelian."'
				) AS E ON A.id_produk = E.id_produk AND A.kode_kantor = E.kode_kantor
				LEFT JOIN tb_prod_to_kat AS F ON A.kode_kantor = F.kode_kantor AND A.id_produk = F.id_produk


				".$cari."
				GROUP BY A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh
						,COALESCE(B.id_satuan,'')
						,COALESCE(C.kode_satuan,'')
						,COALESCE(B.status,'')
						,COALESCE(B.besar_konversi,'')
						,COALESCE(B.harga_jual,'')
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
		
		function list_produk_limit_harga_dasar_untuk_PENJUALAN($id_h_penjualan,$id_kat_costumer,$media_transaksi,$cari,$order_by,$limit,$offset)
		{
			if($this->session->userdata('ses_gnl_isJualStock0') == 'N')
			{
				$isJualStock0 = "WHERE CASE WHEN A.isProduk <> 'JASA' THEN
										(A.SISA_STOCK > 0 )
									ELSE
										A.nama_produk LIKE '%%'
									END
							";
			}
			else
			{
				$isJualStock0 = "";
			}
		
			$query = 
			"
				SELECT * FROM
				(
					SELECT
						A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh,A.isProduk,A.stock,A.dtstock
						,
						CASE WHEN ( (COALESCE(A.pencipta,'') <> '') AND (LENGTH(A.pencipta) > 2 ) ) THEN
							A.pencipta
						ELSE
							A.produksi_oleh
						END AS supplier
					
						,(A.stock + COALESCE(G.DITERIMA,0) + SUM(COALESCE(H.RETUR_PENJUALAN,0)) + SUM(COALESCE(I.MUTASI_IN,0)) ) - ( COALESCE(F.TERJUAL,0) + SUM(COALESCE(H.RETUR_PEMBELIAN,0)) + SUM(COALESCE(I.MUTASI_OUT,0))) AS SISA_STOCK
						,COALESCE(B.id_satuan,'') AS id_satuan
						,COALESCE(C.kode_satuan,'') AS kode_satuan
						,COALESCE(B.status,'') AS status
						,COALESCE(B.besar_konversi,'') AS besar_konversi
						,COALESCE(B.harga_jual,0) AS harga_modal
						,COALESCE(BB.harga,B.harga_jual) AS harga_jual
						
					FROM tb_produk AS A
					LEFT JOIN 
					(
						SELECT DISTINCT kode_kantor,id_produk,id_satuan,status,besar_konversi,harga_jual
						FROM tb_satuan_konversi
						WHERE besar_konversi = '1' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
						GROUP BY kode_kantor,id_produk,id_satuan,status,besar_konversi,harga_jual
					)AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor 
					AND B.besar_konversi = '1'
					LEFT JOIN 
					(
						SELECT DISTINCT kode_kantor,id_costumer,id_produk,id_satuan,media,harga
						FROM tb_produk_harga_satuan_costumer
						WHERE id_costumer = '".$id_kat_costumer."' AND media = '".$media_transaksi."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
						GROUP BY kode_kantor,id_costumer,id_produk,id_satuan,media,harga
					) AS BB ON A.kode_kantor = BB.kode_kantor AND A.id_produk = BB.id_produk AND B.id_satuan = BB.id_satuan AND BB.id_costumer = '".$id_kat_costumer."' AND BB.media = '".$media_transaksi."'
					LEFT JOIN tb_satuan AS C ON B.id_satuan = C.id_satuan AND A.kode_kantor = C.kode_kantor

					-- AMBIL DARI DATA PENJUALAN, APAKAH PRODUK SUDAH MASUK ATAU BELUM
					LEFT JOIN
					(
						SELECT DISTINCT A.id_produk,A.kode_kantor
						FROM tb_d_penjualan AS A
						INNER JOIN
						(				
							SELECT DISTINCT kode_kantor,id_h_penjualan
							FROM tb_h_penjualan
							WHERE id_h_penjualan = '".$id_h_penjualan."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							GROUP BY kode_kantor,id_h_penjualan
						)  AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
						WHERE A.id_h_penjualan = '".$id_h_penjualan."' AND A.ket_d_penjualan = '' AND A.konversi = 1
						GROUP BY A.id_produk,A.kode_kantor
					) AS E ON A.id_produk = E.id_produk AND A.kode_kantor = E.kode_kantor
					-- AMBIL DARI DATA PENJUALAN, APAKAH PRODUK SUDAH MASUK ATAU BELUM


					-- AMBIL DATA PENJUALAN
					LEFT JOIN
					(
						SELECT DISTINCT A.id_produk,A.kode_kantor,A.tgl_ins
							,CASE WHEN (A.status_konversi = '*') THEN
								A.jumlah * A.konversi
							ELSE
								A.jumlah / A.konversi
							END AS TERJUAL
						FROM tb_d_penjualan AS A
						INNER JOIN
						(				
							SELECT DISTINCT kode_kantor,id_h_penjualan
							FROM tb_h_penjualan
							WHERE tgl_ins > '".$this->session->userdata('ses_gnl_tglStockProduk')."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							GROUP BY kode_kantor,id_h_penjualan
						)  AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
						WHERE A.tgl_ins > '".$this->session->userdata('ses_gnl_tglStockProduk')."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
						GROUP BY A.id_produk,A.kode_kantor,A.tgl_ins,A.status_konversi,A.konversi,A.jumlah
					) AS F ON A.id_produk = F.id_produk AND A.kode_kantor = F.kode_kantor AND F.tgl_ins > A.dtstock
					-- AMBIL DATA PENJUALAN

					-- AMBIL DATA PEMBELIAN
					LEFT JOIN
					(
						SELECT DISTINCT A.id_produk,A.kode_kantor,A.tgl_ins
							,CASE WHEN (A.status_konversi = '*') THEN
								A.diterima * A.konversi
							ELSE
								A.diterima / A.konversi
							END AS DITERIMA
						FROM tb_d_penerimaan AS A
						INNER JOIN
						(				
							SELECT DISTINCT A.kode_kantor,A.id_h_penerimaan
							FROM tb_h_penerimaan AS A
							INNER JOIN 
							(
								SELECT DISTINCT kode_kantor,id_h_pembelian
								FROM tb_h_pembelian
								WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
								GROUP BY kode_kantor,id_h_pembelian
							) AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_pembelian = B.id_h_pembelian
							WHERE A.tgl_ins > '".$this->session->userdata('ses_gnl_tglStockProduk')."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							GROUP BY A.kode_kantor,A.id_h_penerimaan
						)  AS B ON A.id_h_penerimaan = B.id_h_penerimaan AND A.kode_kantor = B.kode_kantor
						WHERE A.tgl_ins > '".$this->session->userdata('ses_gnl_tglStockProduk')."'
						GROUP BY A.id_produk,A.kode_kantor,A.tgl_ins,A.status_konversi,A.konversi,A.diterima
					) AS G ON A.id_produk = G.id_produk AND A.kode_kantor = G.kode_kantor AND G.tgl_ins > A.dtstock
					-- AMBIL DATA PEMBELIAN

					-- AMBIL RETUR
					LEFT JOIN
					(
						SELECT kode_kantor,id_produk,tgl_ins,SUM(RETUR_PENJUALAN) AS RETUR_PENJUALAN,SUM(RETUR_PEMBELIAN) AS RETUR_PEMBELIAN
						FROM
						(
							SELECT A.kode_kantor,A.id_produk,A.tgl_ins
								,CASE WHEN STS_RETUR = 'RETUR-PENJUALAN' THEN
									SUM(RETUR)
								ELSE
									0
								END As RETUR_PENJUALAN
								,CASE WHEN STS_RETUR = 'RETUR-PEMBELIAN' THEN
									SUM(RETUR)
								ELSE
									0
								END As RETUR_PEMBELIAN
							FROM
							(
								SELECT DISTINCT A.id_produk,A.kode_kantor,A.tgl_ins,COALESCE(B.status_penjualan,'') AS STS_RETUR
									,CASE WHEN (A.status_konversi = '*') THEN
										A.jumlah * A.konversi
									ELSE
										A.jumlah / A.konversi
									END AS RETUR
								FROM tb_d_retur AS A
								INNER JOIN
								(				
									SELECT DISTINCT kode_kantor,id_h_penjualan,status_penjualan
									FROM tb_h_retur
									WHERE tgl_ins > '".$this->session->userdata('ses_gnl_tglStockProduk')."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
									GROUP BY kode_kantor,id_h_penjualan,status_penjualan
								)  AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
								WHERE A.tgl_ins > '".$this->session->userdata('ses_gnl_tglStockProduk')."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
								GROUP BY A.id_produk,A.kode_kantor,A.tgl_ins,A.status_konversi,A.konversi,A.jumlah,COALESCE(B.status_penjualan,'')
							) AS A
							GROUP BY A.kode_kantor,A.id_produk,A.tgl_ins
						) AS A
						GROUP BY kode_kantor,id_produk,tgl_ins
					) AS H ON A.id_produk = H.id_produk AND A.kode_kantor = H.kode_kantor AND H.tgl_ins > A.dtstock
					-- AMBIL RETUR


					-- AMBIL MUTASI

					LEFT JOIN
					(
						SELECT kode_kantor,id_produk,tgl_ins,SUM(RETUR_PENJUALAN) AS MUTASI_IN,SUM(RETUR_PEMBELIAN) AS MUTASI_OUT
						FROM
						(
							SELECT A.kode_kantor,A.id_produk,A.tgl_ins
								,CASE WHEN STS_RETUR = 'MUTASI-IN' THEN
									SUM(RETUR)
								ELSE
									0
								END As RETUR_PENJUALAN
								,CASE WHEN STS_RETUR = 'MUTASI-OUT' THEN
									SUM(RETUR)
								ELSE
									0
								END As RETUR_PEMBELIAN
							FROM
							(
								SELECT DISTINCT A.id_produk,A.kode_kantor,A.tgl_ins,COALESCE(B.status_penjualan,'') AS STS_RETUR
									,CASE WHEN (A.status_konversi = '*') THEN
										A.jumlah * A.konversi
									ELSE
										A.jumlah / A.konversi
									END AS RETUR
								FROM tb_d_mutasi AS A
								INNER JOIN
								(				
									SELECT DISTINCT kode_kantor,id_h_penjualan,status_penjualan
									FROM tb_h_mutasi
									WHERE tgl_ins > '".$this->session->userdata('ses_gnl_tglStockProduk')."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
									GROUP BY kode_kantor,id_h_penjualan,status_penjualan
								)  AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
								WHERE A.tgl_ins > '".$this->session->userdata('ses_gnl_tglStockProduk')."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
								GROUP BY A.id_produk,A.kode_kantor,A.tgl_ins,A.status_konversi,A.konversi,A.jumlah,COALESCE(B.status_penjualan,'')
							) AS A
							GROUP BY A.kode_kantor,A.id_produk,A.tgl_ins
						) AS A
						GROUP BY kode_kantor,id_produk,tgl_ins
					) AS I ON A.id_produk = I.id_produk AND A.kode_kantor = I.kode_kantor AND I.tgl_ins > A.dtstock

					-- AMBIL MUTASI

					".$cari."			
					GROUP BY A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh,A.isProduk,A.stock,A.dtstock
							-- ,COALESCE(G.DITERIMA,0)
							-- ,COALESCE(F.TERJUAL,0)
							,COALESCE(B.id_satuan,'')
							,COALESCE(C.kode_satuan,'')
							,COALESCE(B.status,'')
							,COALESCE(B.besar_konversi,'')
							,COALESCE(B.harga_jual,'')
				) AS A
				".$isJualStock0."
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
		
		function list_produk_limit_harga_dasar_untuk_PENJUALAN_ori_20211017_no_stock($id_h_penjualan,$id_kat_costumer,$media_transaksi,$cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT
					A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh,A.isProduk
					,COALESCE(B.id_satuan,'') AS id_satuan
					,COALESCE(C.kode_satuan,'') AS kode_satuan
					,COALESCE(B.status,'') AS status
					,COALESCE(B.besar_konversi,'') AS besar_konversi
					,COALESCE(B.harga_jual,0) AS harga_modal
					,COALESCE(BB.harga,B.harga_jual) AS harga_jual
					
					/*
					,MAX(COALESCE(D.img_nama,'')) AS img_nama
					,MAX(COALESCE(D.img_file,'')) AS img_file
					,MAX(COALESCE(D.img_url,'')) AS img_url
					,MAX(COALESCE(D.ket_img,'')) AS ket_img
					*/
					
				FROM tb_produk AS A
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_produk,id_satuan,status,besar_konversi,harga_jual
					FROM tb_satuan_konversi
					WHERE besar_konversi = '1'
					GROUP BY kode_kantor,id_produk,id_satuan,status,besar_konversi,harga_jual
				)AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor 
				AND B.besar_konversi = '1'
				LEFT JOIN 
				(
					SELECT DISTINCT kode_kantor,id_costumer,id_produk,id_satuan,media,harga
					FROM tb_produk_harga_satuan_costumer
					WHERE id_costumer = '".$id_kat_costumer."' AND media = '".$media_transaksi."'
					GROUP BY kode_kantor,id_costumer,id_produk,id_satuan,media,harga
				) AS BB ON A.kode_kantor = BB.kode_kantor AND A.id_produk = BB.id_produk AND B.id_satuan = BB.id_satuan AND BB.id_costumer = '".$id_kat_costumer."' AND BB.media = '".$media_transaksi."'
				LEFT JOIN tb_satuan AS C ON B.id_satuan = C.id_satuan AND A.kode_kantor = C.kode_kantor
				
				/*
				LEFT JOIN tb_images AS D ON A.id_produk = D.id AND A.kode_kantor = D.kode_kantor AND D.group_by = 'produks'
				*/
				
				
				-- AMBIL DARI DATA PENJUALAN, APAKAH PRODUK SUDAH MASUK ATAU BELUM
					LEFT JOIN
					(
						SELECT DISTINCT A.id_produk,A.kode_kantor
						FROM tb_d_penjualan AS A
						
						
						-- INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
						
						
						WHERE A.id_h_penjualan = '".$id_h_penjualan."' AND A.ket_d_penjualan = '' AND A.konversi = 1
						GROUP BY A.id_produk,A.kode_kantor
					) AS E ON A.id_produk = E.id_produk AND A.kode_kantor = E.kode_kantor
				-- AMBIL DARI DATA PENJUALAN, APAKAH PRODUK SUDAH MASUK ATAU BELUM
				
				".$cari."
				GROUP BY A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh,A.isProduk
						,COALESCE(B.id_satuan,'')
						,COALESCE(C.kode_satuan,'')
						,COALESCE(B.status,'')
						,COALESCE(B.besar_konversi,'')
						,COALESCE(B.harga_jual,'')
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
		
		function list_produk_limit_harga_dasar_untuk_PENJUALAN_ori_20211016($id_h_penjualan,$id_kat_costumer,$media_transaksi,$cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT
					A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh,A.isProduk
					,COALESCE(B.id_satuan,'') AS id_satuan
					,COALESCE(C.kode_satuan,'') AS kode_satuan
					,COALESCE(B.status,'') AS status
					,COALESCE(B.besar_konversi,'') AS besar_konversi
					,COALESCE(B.harga_jual,0) AS harga_modal
					,COALESCE(BB.harga,B.harga_jual) AS harga_jual
					
					/*
					,MAX(COALESCE(D.img_nama,'')) AS img_nama
					,MAX(COALESCE(D.img_file,'')) AS img_file
					,MAX(COALESCE(D.img_url,'')) AS img_url
					,MAX(COALESCE(D.ket_img,'')) AS ket_img
					*/
					
				FROM tb_produk AS A
				LEFT JOIN tb_satuan_konversi AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor 
				AND B.besar_konversi = '1'
				LEFT JOIN tb_produk_harga_satuan_costumer  AS BB ON A.kode_kantor = BB.kode_kantor AND A.id_produk = BB.id_produk AND B.id_satuan = BB.id_satuan AND BB.id_costumer = '".$id_kat_costumer."' AND BB.media = '".$media_transaksi."'
				LEFT JOIN tb_satuan AS C ON B.id_satuan = C.id_satuan AND A.kode_kantor = C.kode_kantor
				
				/*
				LEFT JOIN tb_images AS D ON A.id_produk = D.id AND A.kode_kantor = D.kode_kantor AND D.group_by = 'produks'
				*/
				
				
				-- AMBIL DARI DATA PENJUALAN, APAKAH PRODUK SUDAH MASUK ATAU BELUM
					LEFT JOIN
					(
						SELECT DISTINCT A.id_produk,A.kode_kantor
						FROM tb_d_penjualan AS A
						
						
						-- INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
						
						
						WHERE A.id_h_penjualan = '".$id_h_penjualan."' AND A.ket_d_penjualan = '' AND A.konversi = 1
						GROUP BY A.id_produk,A.kode_kantor
					) AS E ON A.id_produk = E.id_produk AND A.kode_kantor = E.kode_kantor
				-- AMBIL DARI DATA PENJUALAN, APAKAH PRODUK SUDAH MASUK ATAU BELUM
				
				".$cari."
				GROUP BY A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh,A.isProduk
						,COALESCE(B.id_satuan,'')
						,COALESCE(C.kode_satuan,'')
						,COALESCE(B.status,'')
						,COALESCE(B.besar_konversi,'')
						,COALESCE(B.harga_jual,'')
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
					A.isNPKP,
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
					A.isNPKP,
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
		
		function simpan
		(
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
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_produk
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
								COALESCE(MAX(CAST(RIGHT(id_produk,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_produk
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
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
		
		function simpan_upload_excel
		(
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
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_produk
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
								COALESCE(MAX(CAST(RIGHT(id_produk,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_produk
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
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
			$user_updt,
			$kode_kantor
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
					WHERE kode_kantor = '".$kode_kantor."' AND id_produk = '".$id_produk
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
			$isNotActive,
			$user_updt,
			$kode_kantor
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
						isNotActive = '".$isNotActive."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_produk = '".$id_produk
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_produk_with_hpp_jasa_for_upload_excel
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
			$isNotActive,
			$isNPKP,
			$user_updt,
			$kode_kantor
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
						isNotActive = '".$isNotActive."',
						isNPKP = '".$isNPKP."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_produk = '".$id_produk
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_with_hpp_jasa_all_cabang
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
		
		
		function ubah_aktif($id_produk,$kode_kantor,$kriteria)
		{
			$strquery = "
					UPDATE tb_produk SET
						".$kriteria." = CASE 
											WHEN ".$kriteria." = 1 THEN
												0
											ELSE
												1
											END
					WHERE kode_kantor = '".$kode_kantor."' AND id_produk = '".$id_produk
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_produk)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_produk WHERE ".$berdasarkan." = '".$id_produk."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_produk_with_kode_kantor($kode_kantor,$berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_produk', array($berdasarkan => $cari,'kode_kantor' => $kode_kantor));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_produk($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_produk', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_produk_cari($cari)
        {
			$query = "SELECT * FROM tb_produk ".$cari."";
			
            //$query = $this->db->get_where('tb_produk', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
			
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
		
		function get_produk_cari_with_stock_produk($kode_kantor,$tgl_stock,$cari)
        {
			$query = "
					SELECT
						A.*
						,COALESCE(B.id_stock_produk,'KOSONG') AS cek_stock_produk
					FROM tb_produk AS A
					LEFT JOIN tb_stock_produk AS B 
						ON A.id_produk = B.id_produk 
						AND A.kode_kantor = B.kode_kantor 
						AND B.tgl_stock = '".$tgl_stock."'
					WHERE A.kode_kantor = '".$kode_kantor."'
					".$cari."
					; 
					";
			
            //$query = $this->db->get_where('tb_produk', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
			
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
		
		function list_kategori_produk($id_produk,$kode_kantor)
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
				WHERE A.kode_kantor = '".$kode_kantor."'
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
		
		function hapus_kat_to_prod($id_produk,$id_kat_produk,$kode_kantor)
		{
			$strquery = 
			"
				DELETE FROM tb_prod_to_kat WHERE id_produk = '".$id_produk."' AND id_kat_produk = '".$id_kat_produk."' AND kode_kantor = '".$kode_kantor."';
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
		
		function cek_produk_kat_produk_upload_excel($kode_kantor,$id_produk,$kode_kat_produk)
		{
			$query = "
					SELECT * FROM tb_prod_to_kat AS A
					-- LEFT JOIN tb_produk AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
					LEFT JOIN tb_kat_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_produk = C.id_kat_produk
					WHERE A.id_produk = '".$id_produk."' AND A.kode_kantor = '".$kode_kantor."' AND COALESCE(C.kode_kat_produk,'') = '".$kode_kat_produk."'
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
		
		function cek_get_kategori_upload_excel($cari)
		{
			$query = "
					SELECT * FROM tb_kat_produk ".$cari."
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
		
		function simpan_prod_to_kat_upload_excel($kode_kantor,$id_produk,$kode_kat_produk,$user)
		{
			$strquery = 
			"
				INSERT INTO tb_prod_to_kat (id_produk,id_kat_produk,tgl_ins,user_ins,kode_kantor)
				VALUES
				(
					'".$id_produk."'
					,COALESCE(
							(SELECT DISTINCT id_kat_produk FROM tb_kat_produk WHERE kode_kantor = '".$kode_kantor."' AND kode_kat_produk = '".$kode_kat_produk."' GROUP BY id_kat_produk)
							,
							''
							)
					,NOW()
					,'".$user."'
					,'".$kode_kantor."'
					)
				
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_produk_all_cabang($kode_kantor_sumber_pusat,$kode_kantor_target)
		{
			$strquery = 
			"
				INSERT INTO 
				tb_produk 
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
					hpp,
					buf_stock,
					late_time,
					jenis_moving,
					isReport,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
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
					A.hpp,
					A.buf_stock,
					A.late_time,
					A.jenis_moving,
					A.isReport,
					A.tgl_ins,
					A.tgl_updt,
					A.user_ins,
					A.user_updt,
					'".$kode_kantor_target."'
				FROM tb_produk AS A
				LEFT JOIN 
				(
					SELECT * 
					FROM tb_produk 
					WHERE kode_kantor = '".$kode_kantor_target."' 
				) AS B ON A.id_produk = B.id_produk
				WHERE A.kode_kantor = '".$kode_kantor_sumber_pusat."' AND B.id_produk IS NULL;
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	}
?>