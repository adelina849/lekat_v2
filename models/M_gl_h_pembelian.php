<?php
	class M_gl_h_pembelian extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_tgl_expired($kode_kantor,$cari)
		{
			$query =
			"
				SELECT 
					AA.* 
					,TIMESTAMPDIFF(DAY,NOW(),AA.tgl_exp) AS SLSH
				FROM
				(
					SELECT
						A.kode_kantor
						,A.kode_produk
						,A.nama_produk
						,MIN(A.tgl_exp) AS tgl_exp
					FROM
					(
						SELECT 
							A.*
							,COALESCE(C.kode_produk,'') AS kode_produk
							,COALESCE(C.nama_produk,'') AS nama_produk
							-- ,TIMESTAMPDIFF(DAY,DATE_ADD(A.tgl_exp, INTERVAL -60 DAY) , NOW()) AS SLSH
							,TIMESTAMPDIFF(DAY,NOW(),A.tgl_exp) AS SLSH
						FROM tb_d_penerimaan AS A
						INNER JOIN tb_h_penerimaan AS B ON A.id_h_penerimaan = B.id_h_penerimaan AND A.kode_kantor = B.kode_kantor
						LEFT JOIN tb_produk AS C ON A.id_produk = C.id_produk AND A.kode_kantor = C.kode_kantor
						WHERE A.kode_kantor LIKE '%".$kode_kantor."%' AND A.tgl_exp <> '0000-00-00' AND A.tgl_exp > DATE(NOW()) AND (COALESCE(C.kode_produk,'') LIKE '%".$cari."%' OR COALESCE(C.nama_produk,'') LIKE '%".$cari."%')
					) AS A
					GROUP BY A.kode_kantor,A.kode_produk,A.nama_produk
				) AS AA
				-- WHERE TIMESTAMPDIFF(DAY,NOW(),AA.tgl_exp) < 450
				ORDER BY AA.tgl_exp ASC
				-- AND TIMESTAMPDIFF(DAY,NOW(),A.tgl_exp) < 420
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
		
		function list_tgl_expired_with_stock($kode_kantor,$tgl_dtStock_produk,$cari)
		{
			$query =
			"
				SELECT
					*
					,datediff(tgl_exp,NOW()) AS SLSH
				FROM
				(
					SELECT
						A.kode_kantor,A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh,A.isProduk,A.stock,A.dtstock
						,
						CASE WHEN ( (COALESCE(A.pencipta,'') <> '') AND (LENGTH(A.pencipta) > 2 ) ) THEN
							A.pencipta
						ELSE
							A.produksi_oleh
						END AS supplier
					
						,(A.stock + COALESCE(G.DITERIMA,0) + SUM(COALESCE(H.RETUR_PENJUALAN,0)) + SUM(COALESCE(I.MUTASI_IN,0)) ) - ( COALESCE(F.TERJUAL,0) + SUM(COALESCE(H.RETUR_PEMBELIAN,0)) + SUM(COALESCE(I.MUTASI_OUT,0))) AS SISA_STOCK
						,COALESCE(B.id_satuan,'') AS id_satuan
						,COALESCE(B.kode_satuan,'') AS satuan
						,COALESCE(B.status,'') AS status
						,COALESCE(B.besar_konversi,'') AS besar_konversi
						,COALESCE(B.harga_jual,0) AS harga_modal
						-- ,COALESCE(F.satuan_jual,'') AS satuan
						-- ,F.satuan_jual AS satuan
						,COALESCE((SELECT MIN(tgl_exp) AS tgl_exp FROM tb_d_penerimaan WHERE kode_kantor = '".$kode_kantor."' AND id_produk = A.id_produk AND DATE(tgl_exp) >= DATE(NOW())),'') AS tgl_exp
					FROM tb_produk AS A
					LEFT JOIN 	
					(
						SELECT A.kode_kantor,A.id_produk,A.id_satuan,A.status,A.besar_konversi,A.harga_jual,COALESCE(B.kode_satuan,'') AS kode_satuan
						FROM tb_satuan_konversi AS A
						LEFT JOIN tb_satuan AS B ON A.kode_kantor = B.kode_kantor AND A.id_satuan = B.id_satuan
						WHERE A.besar_konversi = '1' AND A.kode_kantor = '".$kode_kantor."'
						GROUP BY A.kode_kantor,A.id_produk,A.id_satuan,A.status,A.besar_konversi,A.harga_jual,COALESCE(B.kode_satuan,'')
						
						/*
						SELECT DISTINCT A.kode_kantor,A.id_produk,A.id_satuan,A.status,A.besar_konversi,A.harga_jual,COALESCE(B.kode_satuan,'') AS kode_satuan
						FROM tb_satuan_konversi AS A
						LEFT JOIN tb_satuan AS B ON A.kode_kantor AND B.kode_kantor AND A.id_satuan = B.id_satuan
						WHERE A.besar_konversi = '1' AND A.kode_kantor = '".$kode_kantor."'
						GROUP BY A.kode_kantor,A.id_produk,A.id_satuan,A.status,A.besar_konversi,A.harga_jual,COALESCE(B.kode_satuan,'')
						*/
						
					)AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor 
					AND B.besar_konversi = '1'

					-- AMBIL DATA PENJUALAN
					LEFT JOIN
					(
						SELECT DISTINCT A.id_produk,A.kode_kantor,A.tgl_ins
							-- ,A.satuan_jual
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
							WHERE tgl_ins > '".$tgl_dtStock_produk."' AND kode_kantor = '".$kode_kantor."'
							GROUP BY kode_kantor,id_h_penjualan
						)  AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
						WHERE A.tgl_ins > '".$tgl_dtStock_produk."' AND A.kode_kantor = '".$kode_kantor."'
						GROUP BY A.id_produk,A.kode_kantor,A.tgl_ins,A.status_konversi,A.konversi,A.jumlah
						-- ,A.satuan_jual
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
								WHERE tgl_ins > '".$tgl_dtStock_produk."' AND kode_kantor = '".$kode_kantor."'
								GROUP BY kode_kantor,id_h_pembelian
							) AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_pembelian = B.id_h_pembelian
							WHERE A.tgl_ins > '".$tgl_dtStock_produk."' AND A.kode_kantor = '".$kode_kantor."'
							GROUP BY A.kode_kantor,A.id_h_penerimaan
						)  AS B ON A.id_h_penerimaan = B.id_h_penerimaan AND A.kode_kantor = B.kode_kantor
						WHERE A.tgl_ins > '".$tgl_dtStock_produk."'
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
									WHERE tgl_ins > '".$tgl_dtStock_produk."' AND kode_kantor = '".$kode_kantor."'
									GROUP BY kode_kantor,id_h_penjualan,status_penjualan
								)  AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
								WHERE A.tgl_ins > '".$tgl_dtStock_produk."' AND A.kode_kantor = '".$kode_kantor."'
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
									WHERE tgl_ins > '".$tgl_dtStock_produk."' AND kode_kantor = '".$kode_kantor."'
									GROUP BY kode_kantor,id_h_penjualan,status_penjualan
								)  AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
								WHERE A.tgl_ins > '".$tgl_dtStock_produk."' AND A.kode_kantor = '".$kode_kantor."'
								GROUP BY A.id_produk,A.kode_kantor,A.tgl_ins,A.status_konversi,A.konversi,A.jumlah,COALESCE(B.status_penjualan,'')
							) AS A
							GROUP BY A.kode_kantor,A.id_produk,A.tgl_ins
						) AS A
						GROUP BY kode_kantor,id_produk,tgl_ins
					) AS I ON A.id_produk = I.id_produk AND A.kode_kantor = I.kode_kantor AND I.tgl_ins > A.dtstock		
					WHERE A.kode_kantor = '".$kode_kantor."'
					GROUP BY A.kode_kantor,A.id_produk,A.kode_produk,A.nama_produk,A.produksi_oleh,A.isProduk,A.stock,A.dtstock
							-- ,COALESCE(G.DITERIMA,0)
							-- ,COALESCE(F.TERJUAL,0)
							,COALESCE(B.id_satuan,'')
							-- ,COALESCE(C.kode_satuan,'')
							,COALESCE(B.status,'')
							,COALESCE(B.besar_konversi,'')
							,COALESCE(B.harga_jual,'')
				) AS A
				WHERE tgl_exp <> ''
				AND
				(
					kode_produk LIKE '%".$cari."%'
					OR nama_produk LIKE '%".$cari."%'
					OR supplier LIKE '%".$cari."%'
				)
				AND A.SISA_STOCK > 0
				ORDER BY datediff(tgl_exp,NOW()) ASC
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
		
		function jumlah_pembelian_pending()
		{
			$query =
			"
				SELECT COUNT(id_h_pembelian) AS JUMLAH FROM tb_h_pembelian WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND user_ins = '".$this->session->userdata('ses_id_karyawan')."' AND DATE(tgl_ins) = DATE(NOW())
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
		
		function list_h_pembelian_penerimaan($kode_kantor,$id_supplier,$cari,$dari,$sampai,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT 
					A.id_h_pembelian, A.id_supplier,A.no_h_pembelian,A.ket_h_pembelian,A.nominal_transaksi,A.tgl_h_pembelian,DATE_FORMAT(A.tgl_h_pembelian, '%Y-%m-%d') AS DFORMAT  
						,COALESCE(C.kode_supplier,'') AS kode_supplier
						,COALESCE(C.nama_supplier,'') AS nama_supplier
						,COALESCE(C.alamat,'') AS alamat
						,COALESCE(B.JUM,0) AS JUM
						,COALESCE(B.JUMITEM,0) AS JUMITEM
						,COALESCE(B.JUMACC,0) AS JUMACC
						,COALESCE(B.SUBTOTAL,0) AS SUBTOTAL
						,SUM(COALESCE(D.diterima_satuan_beli,0)) AS DITERIMA
						,ROUND(SUM(COALESCE(D.diterima_satuan_beli,0)) / (COALESCE(B.JUMITEM,0) / 100)) AS PERSEN
				FROM tb_h_pembelian AS A
				Left Join
				(
					SELECT id_h_pembelian,COUNT(id_h_pembelian) AS JUM, SUM(acc) AS JUMACC,SUM(jumlah) AS JUMITEM, SUM(jumlah * (harga_fix - diskon_fix)) AS SUBTOTAL
					FROM
					(
						SELECT
							*
							,CASE WHEN optr_diskon = '*' THEN
								(harga/100) * diskon
							ELSE
								diskon
							END AS diskon_fix
							,ROUND(harga,0) AS harga_fix
						FROM tb_d_pembelian 
						-- WHERE id_h_pembelian = 'ONPST2020072200003';
						WHERE kode_kantor = '".$kode_kantor."'
					) AS A
					GROUP BY id_h_pembelian
					/*
					SELECT id_h_pembelian,COUNT(id_h_pembelian) AS JUM, SUM(acc) AS JUMACC,SUM(jumlah) AS JUMITEM, SUM(jumlah * harga) AS SUBTOTAL
					From tb_d_pembelian
					WHERE kode_kantor = '".$kode_kantor."'
					GROUP BY id_h_pembelian
					*/
				) AS B ON A.id_h_pembelian = B.id_h_pembelian
				Left Join
				(
					SELECT A.id_h_pembelian,A.id_h_penerimaan,A.id_d_penerimaan,SUM(A.diterima_satuan_beli) AS diterima_satuan_beli
					From
					(
						SELECT id_d_penerimaan,id_h_pembelian, id_h_penerimaan, id_d_pembelian, id_produk,diterima_satuan_beli
						From tb_d_penerimaan
						WHERE kode_kantor = '".$kode_kantor."'
					) AS A
					INNER JOIN tb_h_penerimaan AS B ON A.id_h_penerimaan = B.id_h_penerimaan AND B.kode_kantor = '".$kode_kantor."'
					GROUP BY A.id_h_pembelian,A.id_h_penerimaan,A.id_d_penerimaan
				) AS D ON A.id_h_pembelian = D.id_h_pembelian
				LEFT JOIN tb_supplier AS C ON A.id_supplier = C.id_supplier AND C.kode_kantor = '".$kode_kantor."'
				WHERE 
				A.kode_kantor = '".$kode_kantor."'
				AND A.id_supplier LIKE '%".$id_supplier."%'
				AND A.sts_pembelian = 'SELESAI'
				AND (A.no_h_pembelian LIKE '%".$cari."%' OR C.kode_supplier LIKE '%".$cari."%' OR C.nama_supplier LIKE '%".$cari."%')
				-- AND DATE_FORMAT(A.tgl_h_pembelian, '%Y-%m-%d') BETWEEN '".$dari."' AND '".$sampai."' 
				AND COALESCE(B.JUMACC,0) = COALESCE(B.JUM,0)
				GROUP BY  A.id_h_pembelian,
				A.id_supplier,A.no_h_pembelian,A.ket_h_pembelian,A.nominal_transaksi,A.tgl_h_pembelian,DATE_FORMAT(A.tgl_h_pembelian, '%Y-%m-%d')
				,COALESCE(C.kode_supplier,'')
				,COALESCE(C.nama_supplier,'')
				,COALESCE(C.alamat,'')
				,COALESCE(B.JUM,0)
				,COALESCE(B.JUMITEM,0)
				,COALESCE(B.JUMACC,0)
				,COALESCE(B.SUBTOTAL,0)
				
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
		
		function count_h_pembelian_penerimaan($kode_kantor,$cari,$dari,$sampai)
		{
			$query =
			"
				SELECT COUNT(A.id_h_pembelian) AS JUMLAH
				FROM tb_h_pembelian AS A
				Left Join
				(
					SELECT id_h_pembelian,COUNT(id_h_pembelian) AS JUM, SUM(acc) AS JUMACC,SUM(jumlah) AS JUMITEM, SUM(jumlah * harga) AS SUBTOTAL
					From tb_d_pembelian
					WHERE kode_kantor = '".$kode_kantor."'
					GROUP BY id_h_pembelian
				) AS B ON A.id_h_pembelian = B.id_h_pembelian
				LEFT JOIN tb_supplier AS C ON A.id_supplier = C.id_supplier AND C.kode_kantor = '".$kode_kantor."'
				WHERE 
				A.kode_kantor = '".$kode_kantor."'
				AND A.sts_pembelian = 'SELESAI'
				AND (A.no_h_pembelian LIKE '%".$cari."%' OR C.kode_supplier LIKE '%".$cari."%' OR C.nama_supplier LIKE '%".$cari."%')
				AND DATE_FORMAT(A.tgl_h_pembelian, '%Y-%m-%d') BETWEEN '".$dari."' AND '".$sampai."' 
				AND COALESCE(B.JUMACC,0) = COALESCE(B.JUM,0)
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
		
		function list_h_pembelian($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT A.* 
					,COALESCE(B.kode_supplier,'') AS kode_supplier
					,COALESCE(B.nama_supplier,'') AS nama_supplier
					,COALESCE(B.tlp,'') AS tlp
					,COALESCE(B.email,'') AS email
					,COALESCE(B.alamat,'') AS alamat_supplier
				FROM tb_h_pembelian AS A
				LEFT JOIN tb_supplier AS B ON A.id_supplier = B.id_supplier AND A.kode_kantor = B.kode_kantor
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
		
		
		function count_h_pembelian($cari)
		{
			$query = "SELECT COUNT(A.id_h_pembelian) AS JUMLAH 
				FROM tb_h_pembelian AS A
				LEFT JOIN tb_supplier AS B ON A.id_supplier = B.id_supplier AND A.kode_kantor = B.kode_kantor 
				".$cari;
			
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
		
		
		function get_id_h_pembelian_dan_faktur($kode_kantor)
		{
			$query =
			"
				SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_h_pembelian,CONCAT('".$kode_kantor."/','PO/',FRMTGL,'/',ORD) AS NO_FAKTUR
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
						COALESCE(MAX(CAST(RIGHT(id_h_pembelian,5) AS UNSIGNED)) + 1,1) AS ORD
						From tb_h_pembelian
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
		
		
		function simpan
		(
			$id_h_pembelian,
			$id_supplier,
			$id_h_retur,
			$no_h_pembelian,
			$nama_h_pembelian,
			$tgl_h_pembelian,
			$tgl_jatuh_tempo,
			$nominal_transaksi,
			$nominal_retur,
			$bayar_detail,
			$biaya_tambahan,
			$pengurang,
			$ket_h_pembelian,
			$sts_pembelian,
			$user_ins,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_h_pembelian
				(
					id_h_pembelian,
					id_supplier,
					id_h_retur,
					no_h_pembelian,
					nama_h_pembelian,
					tgl_h_pembelian,
					tgl_jatuh_tempo,
					nominal_transaksi,
					nominal_retur,
					bayar_detail,
					biaya_tambahan,
					pengurang,
					ket_h_pembelian,
					sts_pembelian,
					tgl_ins,
					tgl_updt,
					user_updt,
					user_ins,
					kode_kantor
				)
				VALUES
				(
					'".$id_h_pembelian."',
					'".$id_supplier."',
					'".$id_h_retur."',
					'".$no_h_pembelian."',
					'".$nama_h_pembelian."',
					'".$tgl_h_pembelian."',
					'".$tgl_jatuh_tempo."',
					'".$nominal_transaksi."',
					'".$nominal_retur."',
					'".$bayar_detail."',
					'".$biaya_tambahan."',
					'".$pengurang."',
					'".$ket_h_pembelian."',
					'".$sts_pembelian."',
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
			$id_h_pembelian,
			$id_supplier,
			$id_h_retur,
			$no_h_pembelian,
			$nama_h_pembelian,
			$tgl_h_pembelian,
			$tgl_jatuh_tempo,
			$nominal_transaksi,
			$nominal_retur,
			$bayar_detail,
			$biaya_tambahan,
			$pengurang,
			$ket_h_pembelian,
			$sts_pembelian,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_h_pembelian SET
						id_supplier = '".$id_supplier."',
						id_h_retur = '".$id_h_retur."',
						no_h_pembelian = '".$no_h_pembelian."',
						nama_h_pembelian = '".$nama_h_pembelian."',
						tgl_h_pembelian = '".$tgl_h_pembelian."',
						tgl_jatuh_tempo = '".$tgl_jatuh_tempo."',
						nominal_transaksi = '".$nominal_transaksi."',
						nominal_retur = '".$nominal_retur."',
						bayar_detail = '".$bayar_detail."',
						biaya_tambahan = '".$biaya_tambahan."',
						pengurang = '".$pengurang."',
						ket_h_pembelian = '".$ket_h_pembelian."',
						sts_pembelian = '".$sts_pembelian."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_h_pembelian = '".$id_h_pembelian
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_h_pembelian)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_h_pembelian WHERE ".$berdasarkan." = '".$id_h_pembelian."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function proses_looping_analisa_order($kode_kantor,$id_h_pembelian,$id_karyawan)
		{
			/*HAPUS JABATAN*/
				$strquery = "
						CALL SP_LOOP_PROSES_ANALISA_ORDER('".$kode_kantor."','".$id_h_pembelian."','".$id_karyawan."');
						";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function closing_po($kode_kantor,$id_h_pembelian)
		{
			/*HAPUS JABATAN*/
				$strquery = "
						UPDATE tb_d_pembelian AS A
						INNER JOIN
						(
							SELECT
								A.kode_kantor
								,A.id_d_pembelian
								,A.id_h_pembelian
								,A.id_produk
								,A.jumlah
								,COALESCE(C.diterima_satuan_beli,0) AS diterima
								,A.kode_satuan
								,COALESCE(B.no_h_pembelian,'') AS no_h_pembelian
							FROM tb_d_pembelian AS A
							INNER JOIN tb_h_pembelian AS B 
								ON A.kode_kantor = B.kode_kantor 
								AND A.id_h_pembelian = B.id_h_pembelian
							LEFT JOIN tb_d_penerimaan AS C 
								ON A.kode_kantor = C.kode_kantor 
								AND A.id_h_pembelian = C.id_h_pembelian 
								AND A.id_d_pembelian = C.id_d_pembelian
								AND A.id_produk = C.id_produk
							WHERE 
							A.kode_kantor = '".$kode_kantor."'
							AND B.sts_pembelian = 'SELESAI'
							-- AND B.no_h_pembelian = 'JKT/PO/20210305/00003'
							AND A.id_h_pembelian = '".$id_h_pembelian."'
						) AS B
						ON A.kode_kantor = B.kode_kantor 
						AND A.id_h_pembelian = B.id_h_pembelian 
						AND A.id_d_pembelian = B.id_d_pembelian
						AND A.id_produk = B.id_produk
						SET A.jumlah = B.diterima
						WHERE A.kode_kantor = '".$kode_kantor."'
						AND A.id_h_pembelian = '".$id_h_pembelian."';";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_h_pembelian($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_h_pembelian', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		
		function get_h_pembelian_cari($cari)
		{
			$query =
			"
				SELECT
					A.*
					,COALESCE(B.kode_supplier,'') AS kode_supplier
					,COALESCE(B.nama_supplier,'') AS nama_supplier
					,COALESCE(B.tlp,'') AS tlp_supplier
					,COALESCE(B.alamat,'') AS alamat_supplier
					,COALESCE(B.hari_tempo,0) AS hari_tempo
					,DATE_ADD( DATE(NOW()) , INTERVAL (COALESCE(B.hari_tempo,0)) DAY) AS TEMPO_FIX
				FROM tb_h_pembelian AS A
				LEFT JOIN tb_supplier AS B ON A.kode_kantor = B.kode_kantor AND A.id_supplier = B.id_supplier
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
	}
?>