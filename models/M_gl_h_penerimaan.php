<?php
	class M_gl_h_penerimaan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_h_penerimaan_limit_for_hutang_by_uang_masuk($cari,$limit,$offset)
		{
			$query =
			"
				SELECT 
					A.id_h_penerimaan,
					A.id_h_pembelian,
					A.id_gedung,
					A.no_surat_jalan,
					A.nama_pengirim,
					A.cara_pengiriman,
					A.diterima_oleh,
					A.biaya_kirim,
					A.tgl_kirim,
					A.tgl_terima,
					A.ket_h_penerimaan,
					A.tgl_ins,
					A.tgl_updt,
					A.user_updt,
					A.user_ins,
					A.kode_kantor,
					COALESCE(D.no_h_pembelian,0) AS NO_PO,
					COALESCE(C.JUMLAH,0) AS JUMLAH,
					COALESCE(C.NOMINAL,0) AS NOMINAL,
					COALESCE(F.BAYAR,0) AS BAYAR,
					COALESCE(C.NOMINAL,0) - COALESCE(F.BAYAR,0) AS SISA,
					COALESCE(E.kode_supplier,'') AS kode_supplier,
					COALESCE(E.nama_supplier,'') AS nama_supplier
					
				FROM tb_h_penerimaan AS A
				LEFT JOIN
				(
					SELECT 
						id_h_penerimaan
						,kode_kantor
						,COUNT(id_produk) AS JUMLAH
						,SUM(diterima * harga_beli) AS NOMINAL
					FROM tb_d_penerimaan
					GROUP BY id_h_penerimaan,kode_kantor
				) AS C ON A.id_h_penerimaan = C.id_h_penerimaan AND A.kode_kantor = C.kode_kantor
				LEFT JOIN tb_h_pembelian AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_pembelian = D.id_h_pembelian
				LEFT JOIN tb_supplier AS E ON A.kode_kantor = E.kode_kantor AND D.id_supplier = E.id_supplier
				LEFT JOIN
				(
					SELECT 
						kode_kantor
						,id_h_penerimaan
						,SUM(nominal) AS BAYAR
					FROM tb_d_pembelian_bayar
					GROUP BY kode_kantor,id_h_penerimaan
				) AS F ON A.kode_kantor = F.kode_kantor AND A.id_h_penerimaan = F.id_h_penerimaan
				
				".$cari." 
				GROUP BY A.id_h_penerimaan,
					A.id_h_pembelian,
					A.id_gedung,
					A.no_surat_jalan,
					A.nama_pengirim,
					A.cara_pengiriman,
					A.diterima_oleh,
					A.biaya_kirim,
					A.tgl_kirim,
					A.tgl_terima,
					A.ket_h_penerimaan,
					A.tgl_ins,
					A.tgl_updt,
					A.user_updt,
					A.user_ins,
					A.kode_kantor,
					COALESCE(D.no_h_pembelian,''),
					COALESCE(C.JUMLAH,0),
					COALESCE(C.NOMINAL,0),
					COALESCE(F.BAYAR,0),
					COALESCE(E.kode_supplier,''),
					COALESCE(E.nama_supplier,'')
					
				ORDER BY A.tgl_ins DESC LIMIT ".$offset.",".$limit."
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
		
		function list_h_penerimaan_limit_for_hutang_by_inv_pay_by_ju($cari,$limit,$offset)
		{
			$query =
			"
				SELECT * FROM
				(
					SELECT 
						A.id_h_penerimaan,
						A.id_h_pembelian,
						A.id_gedung,
						A.no_surat_jalan,
						A.nama_pengirim,
						A.cara_pengiriman,
						A.diterima_oleh,
						A.biaya_kirim,
						A.tgl_kirim,
						A.tgl_terima,
						A.ket_h_penerimaan,
						A.tgl_ins,
						A.tgl_updt,
						A.user_updt,
						A.user_ins,
						A.kode_kantor,
						CONCAT( COALESCE(F.NO_BUKTI,''),',', COALESCE(G.NO_BUKTI,'')) AS NO_BUKTI,
						COALESCE(D.no_h_pembelian,0) AS NO_PO,
						COALESCE(D.id_supplier,0) AS id_h_supplier,
						COALESCE(C.JUMLAH,0) AS JUMLAH,
						COALESCE(C.NOMINAL,0) AS NOMINAL,
						( COALESCE(F.BAYAR,0) + COALESCE(G.BAYAR,0) ) AS BAYAR,
						(COALESCE(C.NOMINAL,0) + A.biaya_kirim) - ( COALESCE(F.BAYAR,0) + COALESCE(G.BAYAR,0) ) AS SISA,
						COALESCE(E.kode_supplier,'') AS kode_supplier,
						COALESCE(E.nama_supplier,'') AS nama_supplier
						
					FROM tb_h_penerimaan AS A
					LEFT JOIN
					(
						SELECT 
							id_h_penerimaan
							,kode_kantor
							,COUNT(id_produk) AS JUMLAH
							,SUM(diterima * harga_beli) AS NOMINAL
						FROM tb_d_penerimaan
						GROUP BY id_h_penerimaan,kode_kantor
					) AS C ON A.id_h_penerimaan = C.id_h_penerimaan AND A.kode_kantor = C.kode_kantor
					LEFT JOIN tb_h_pembelian AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_pembelian = D.id_h_pembelian
					LEFT JOIN tb_supplier AS E ON A.kode_kantor = E.kode_kantor AND D.id_supplier = E.id_supplier

					
					
					
					
					-- DARI JURNAL UMUM
					LEFT JOIN
					(
						
						SELECT 
							kode_kantor
							,id_h_penerimaan
							,GROUP_CONCAT(no_bukti SEPARATOR ', ') AS NO_BUKTI
							,SUM(debit) AS BAYAR
						FROM tb_jurnal_umum
						GROUP BY kode_kantor,id_h_penerimaan
						
					) AS F ON A.kode_kantor = F.kode_kantor AND A.id_h_penerimaan = F.id_h_penerimaan
					
					-- DARI UANG KELUAR
					LEFT JOIN
					(
						
						SELECT 
							kode_kantor
							,id_h_penerimaan
							,GROUP_CONCAT(no_uang_keluar SEPARATOR ', ') AS NO_BUKTI
							,SUM(nominal) + SUM(biaya) + SUM(pendapatan) AS BAYAR
						FROM tb_uang_keluar
						GROUP BY kode_kantor,id_h_penerimaan
						
					) AS G ON A.kode_kantor = G.kode_kantor AND A.id_h_penerimaan = G.id_h_penerimaan
					
					".$cari." 
					GROUP BY A.id_h_penerimaan,
						A.id_h_pembelian,
						A.id_gedung,
						A.no_surat_jalan,
						A.nama_pengirim,
						A.cara_pengiriman,
						A.diterima_oleh,
						A.biaya_kirim,
						A.tgl_kirim,
						A.tgl_terima,
						A.ket_h_penerimaan,
						A.tgl_ins,
						A.tgl_updt,
						A.user_updt,
						A.user_ins,
						A.kode_kantor,
						COALESCE(D.no_h_pembelian,''),
						COALESCE(C.JUMLAH,0),
						COALESCE(C.NOMINAL,0),
						COALESCE(F.BAYAR,0),
						COALESCE(F.NO_BUKTI,''),
						COALESCE(G.BAYAR,0),
						COALESCE(G.NO_BUKTI,''),
						COALESCE(E.kode_supplier,''),
						COALESCE(E.nama_supplier,'')
				) AS A
				WHERE A.SISA > 0
					
				ORDER BY A.tgl_ins DESC LIMIT ".$offset.",".$limit."
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
		
		function list_h_penerimaan_limit_for_hutang_by_inv($cari,$limit,$offset)
		{
			$query =
			"
				SELECT 
					A.id_h_penerimaan,
					A.id_h_pembelian,
					A.id_gedung,
					A.no_surat_jalan,
					A.nama_pengirim,
					A.cara_pengiriman,
					A.diterima_oleh,
					A.biaya_kirim,
					A.tgl_kirim,
					A.tgl_terima,
					
					CASE WHEN COALESCE(A.tgl_tempo_next,'') = '' THEN
						DATE_ADD(A.tgl_terima, INTERVAL (COALESCE(E.hari_tempo,0)) DAY)
					ELSE
						A.tgl_tempo_next
					END
					AS tgl_tempo_next
					,
					
					
					A.ket_h_penerimaan,
					A.tgl_ins,
					A.tgl_updt,
					A.user_updt,
					A.user_ins,
					A.kode_kantor,
					
					CONCAT( COALESCE(F.NO_BUKTI,''),',', COALESCE(G.NO_BUKTI,'')) AS NO_BUKTI,
					COALESCE(D.no_h_pembelian,0) AS NO_PO,
					COALESCE(C.JUMLAH,0) AS JUMLAH,
					COALESCE(C.NOMINAL,0) AS NOMINAL,
					( COALESCE(F.BAYAR,0) + COALESCE(G.BAYAR,0) ) AS BAYAR,
					(COALESCE(C.NOMINAL,0) + A.biaya_kirim) - ( COALESCE(F.BAYAR,0) + COALESCE(G.BAYAR,0) ) AS SISA,
					COALESCE(E.kode_supplier,'') AS kode_supplier,
					COALESCE(E.nama_supplier,'') AS nama_supplier
					
				FROM tb_h_penerimaan AS A
				LEFT JOIN
				(
					SELECT
						id_h_penerimaan,kode_kantor,COUNT(id_produk) AS JUMLAH,SUM(diterima * (harga_beli - DISKON)) AS NOMINAL
					FROM
					(
						SELECT 
							DISTINCT
							id_h_penerimaan
							,kode_kantor
							,id_produk
							-- ,COUNT(id_produk) AS JUMLAH
							,CASE WHEN (optr_diskon = '%') THEN
								(harga_beli/100)*diskon
							ELSE
								diskon
							END AS DISKON
							-- ,SUM(diterima * harga_beli) AS NOMINAL
							,diterima
							,harga_beli
						FROM tb_d_penerimaan
						-- GROUP BY id_h_penerimaan,kode_kantor
					) AS A
					GROUP BY id_h_penerimaan,kode_kantor
				) AS C ON A.id_h_penerimaan = C.id_h_penerimaan AND A.kode_kantor = C.kode_kantor
				LEFT JOIN tb_h_pembelian AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_pembelian = D.id_h_pembelian
				LEFT JOIN tb_supplier AS E ON A.kode_kantor = E.kode_kantor AND D.id_supplier = E.id_supplier
				
				
				
				-- DARI JURNAL UMUM
				LEFT JOIN
				(
					
					SELECT 
						kode_kantor
						,id_h_penerimaan
						,GROUP_CONCAT(no_bukti SEPARATOR ', ') AS NO_BUKTI
						,SUM(debit) AS BAYAR
					FROM tb_jurnal_umum
					GROUP BY kode_kantor,id_h_penerimaan
					
				) AS F ON A.kode_kantor = F.kode_kantor AND A.id_h_penerimaan = F.id_h_penerimaan
				
				-- DARI UANG KELUAR
					LEFT JOIN
					(
						
						SELECT 
							kode_kantor
							,id_h_penerimaan
							,GROUP_CONCAT(no_uang_keluar SEPARATOR ', ') AS NO_BUKTI
							,SUM(nominal) + SUM(biaya) + SUM(pendapatan) AS BAYAR
						FROM tb_uang_keluar
						GROUP BY kode_kantor,id_h_penerimaan
						
					) AS G ON A.kode_kantor = G.kode_kantor AND A.id_h_penerimaan = G.id_h_penerimaan
					
				".$cari." 
				GROUP BY A.id_h_penerimaan,
					A.id_h_pembelian,
					A.id_gedung,
					A.no_surat_jalan,
					A.nama_pengirim,
					A.cara_pengiriman,
					A.diterima_oleh,
					A.biaya_kirim,
					A.tgl_kirim,
					A.tgl_terima,
					A.tgl_tempo_next,
					A.ket_h_penerimaan,
					A.tgl_ins,
					A.tgl_updt,
					A.user_updt,
					A.user_ins,
					A.kode_kantor,
					COALESCE(D.no_h_pembelian,''),
					COALESCE(C.JUMLAH,0),
					COALESCE(C.NOMINAL,0),
					COALESCE(F.BAYAR,0),
					COALESCE(F.NO_BUKTI,''),
					COALESCE(G.BAYAR,0),
					COALESCE(G.NO_BUKTI,''),
					COALESCE(E.kode_supplier,''),
					COALESCE(E.nama_supplier,'')
					
				ORDER BY A.tgl_ins DESC LIMIT ".$offset.",".$limit."
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
		
		
		function list_akumulasi_hutang_per_supplier($cari,$tgl_sampai)
		{
			$query =
			"
				SELECT * FROM
				(
					SELECT
						A.id_supplier
						,A.kode_supplier
						,A.nama_supplier
						,A.alamat
						,A.hari_tempo
						,A.hutang_awal
						,A.tgl_hutang_awal
						
						
						,SUM(COALESCE(B.CNT_TERIMA,0)) AS CNT_TERIMA
						,SUM(COALESCE(B.SISA,0)) AS SISA_TR
						,MAX(COALESCE(B.tgl_tempo_next,0)) AS TEMPO_NEXT
						
						/*
						,(COALESCE(B.CNT_TERIMA,0)) AS CNT_TERIMA
						,(COALESCE(B.SISA,0)) AS SISA_TR
						,(COALESCE(B.tgl_tempo_next,0)) AS TEMPO_NEXT
						*/
						
						,
						CASE 
							WHEN ( MAX(COALESCE(B.tgl_tempo_next,0)) <> '') && ( DATE(NOW()) = MAX(COALESCE(B.tgl_tempo_next,0)) ) THEN
								0
							WHEN ( MAX(COALESCE(B.tgl_tempo_next,0)) <> '') && ( DATE(NOW()) > MAX(COALESCE(B.tgl_tempo_next,0)) ) THEN
								1
							ELSE
								2
							END AS STS_TEMPO
						
						/*
						,
						CASE 
							WHEN ( (COALESCE(B.tgl_tempo_next,0)) <> '') && ( DATE(NOW()) = (COALESCE(B.tgl_tempo_next,0)) ) THEN
								0
							WHEN ( (COALESCE(B.tgl_tempo_next,0)) <> '') && ( DATE(NOW()) > (COALESCE(B.tgl_tempo_next,0)) ) THEN
								1
							ELSE
								2
							END AS STS_TEMPO
						*/
						
						
						-- ,A.hutang_awal - SUM(COALESCE(C.bayar_hutang_awal,0)) AS SISA_AWAL
						,A.hutang_awal - (COALESCE(C.bayar_hutang_awal,0)) AS SISA_AWAL
						
						-- ,SUM(COALESCE(C.bayar_hutang_awal,0)) AS BAYAR_HUTANG_AWAL
						,(COALESCE(C.bayar_hutang_awal,0)) AS BAYAR_HUTANG_AWAL
						-- ,SUM(COALESCE(B.SISA,0)) + (A.hutang_awal - (SUM(COALESCE(C.bayar_hutang_awal,0)))) AS SISA_ALL
						,SUM(COALESCE(B.SISA,0)) + (A.hutang_awal - ((COALESCE(C.bayar_hutang_awal,0)))) AS SISA_ALL
						
						/*
						,A.hutang_awal - (COALESCE(C.bayar_hutang_awal,0)) AS SISA_AWAL
						,(COALESCE(C.bayar_hutang_awal,0)) AS BAYAR_HUTANG_AWAL
						,(COALESCE(B.SISA,0)) + (A.hutang_awal - ((COALESCE(C.bayar_hutang_awal,0)))) AS SISA_ALL
						*/
					FROM tb_supplier AS A
					LEFT JOIN
					(
						SELECT A.kode_kantor,A.id_supplier,A.tgl_terima,COUNT(A.id_h_penerimaan) AS CNT_TERIMA,SUM(A.SISA) AS SISA,MAX(tgl_tempo_next) AS tgl_tempo_next
						FROM
						(
							SELECT 
								A.id_h_penerimaan,
								A.id_h_pembelian,
								A.id_gedung,
								A.no_surat_jalan,
								A.nama_pengirim,
								A.cara_pengiriman,
								A.diterima_oleh,
								A.biaya_kirim,
								A.tgl_kirim,
								A.tgl_terima,
								
								CASE WHEN COALESCE(A.tgl_tempo_next,'') = '' THEN
									DATE(DATE_ADD(A.tgl_terima, INTERVAL (COALESCE(E.hari_tempo,0)) DAY))
								ELSE
									DATE(A.tgl_tempo_next)
								END
								AS tgl_tempo_next,
										
								A.ket_h_penerimaan,
								A.tgl_ins,
								A.tgl_updt,
								A.user_updt,
								A.user_ins,
								A.kode_kantor,
								COALESCE(F.NO_BUKTI,'') AS NO_BUKTI,
								COALESCE(D.id_supplier,0) AS id_supplier,
								COALESCE(D.no_h_pembelian,0) AS NO_PO,
								COALESCE(C.JUMLAH,0) AS JUMLAH,
								COALESCE(C.NOMINAL,0) AS NOMINAL,
								( COALESCE(F.BAYAR,0) + COALESCE(G.NOMINAL,0) ) AS BAYAR,
								(COALESCE(C.NOMINAL,0) + A.biaya_kirim) - ( COALESCE(F.BAYAR,0) + COALESCE(G.NOMINAL,0) ) AS SISA
							FROM tb_h_penerimaan AS A
							LEFT JOIN
							(
								/*
								SELECT 
									id_h_penerimaan
									,kode_kantor
									,COUNT(id_produk) AS JUMLAH
									,SUM(diterima * harga_beli) AS NOMINAL
								FROM tb_d_penerimaan
								GROUP BY id_h_penerimaan,kode_kantor
								*/
								
								SELECT
									id_h_penerimaan,kode_kantor,COUNT(id_produk) AS JUMLAH,SUM(diterima * (harga_beli - DISKON)) AS NOMINAL
								FROM
								(
									SELECT 
										DISTINCT
										id_h_penerimaan
										,kode_kantor
										,id_produk
										-- ,COUNT(id_produk) AS JUMLAH
										,CASE WHEN (optr_diskon = '%') THEN
											(harga_beli/100)*diskon
										ELSE
											diskon
										END AS DISKON
										-- ,SUM(diterima * harga_beli) AS NOMINAL
										,diterima
										,harga_beli
									FROM tb_d_penerimaan
									-- GROUP BY id_h_penerimaan,kode_kantor
								) AS A
								GROUP BY id_h_penerimaan,kode_kantor
								
							) AS C ON A.id_h_penerimaan = C.id_h_penerimaan AND A.kode_kantor = C.kode_kantor
							LEFT JOIN tb_h_pembelian AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_pembelian = D.id_h_pembelian
							LEFT JOIN tb_supplier AS E ON A.kode_kantor = E.kode_kantor AND D.id_supplier = E.id_supplier
							
							-- AMBIL DARI JURNAL UMUM
								LEFT JOIN
								(
									
									SELECT 
										kode_kantor
										,id_h_penerimaan
										,GROUP_CONCAT(no_bukti SEPARATOR ', ') AS NO_BUKTI
										,SUM(debit) AS BAYAR
									FROM tb_jurnal_umum
									WHERE DATE(tgl_transaksi) <= '".$tgl_sampai."'
									GROUP BY kode_kantor,id_h_penerimaan
									
								) AS F ON A.kode_kantor = F.kode_kantor AND A.id_h_penerimaan = F.id_h_penerimaan
							-- AMBIL DARI JURNAL UMUM
							
							-- AMBIL DARI UANG KELUAR
								LEFT JOIN
								(
									SELECT kode_kantor,id_h_penerimaan,SUM(nominal) AS NOMINAL, MAX(biaya) AS BIAYA, MAX(pendapatan) AS PENDAPATAN
									FROM tb_uang_keluar
									WHERE DATE(tgl_dikeluarkan) <= '".$tgl_sampai."'
									GROUP BY kode_kantor,id_h_penerimaan
								) AS G ON A.kode_kantor = G.kode_kantor AND A.id_h_penerimaan = G.id_h_penerimaan
							-- AMBIL DARI UANG KELUAR
							
							WHERE DATE(A.tgl_terima) <= '".$tgl_sampai."'
							
							GROUP BY A.id_h_penerimaan,
								A.id_h_pembelian,
								A.id_gedung,
								A.no_surat_jalan,
								A.nama_pengirim,
								A.cara_pengiriman,
								A.diterima_oleh,
								A.biaya_kirim,
								A.tgl_kirim,
								A.tgl_terima,
								A.tgl_tempo_next,
								A.ket_h_penerimaan,
								A.tgl_ins,
								A.tgl_updt,
								A.user_updt,
								A.user_ins,
								A.kode_kantor,
								COALESCE(D.no_h_pembelian,''),
								COALESCE(C.JUMLAH,0),
								COALESCE(C.NOMINAL,0),
								COALESCE(F.BAYAR,0)
						) AS A
						GROUP BY A.kode_kantor,A.id_supplier,A.tgl_terima
					) AS B ON A.kode_kantor = B.kode_kantor AND A.id_supplier = B.id_supplier AND B.tgl_terima > A.tgl_hutang_awal
					LEFT JOIN
					(
						-- UNTUK HUTANG AWAL
						SELECT
							A.kode_kantor,A.id_supplier,MIN(A.tgl_diterima) AS tgl_diterima,SUM(A.nominal) AS bayar_hutang_awal
						FROM tb_uang_keluar AS A
						WHERE id_h_penerimaan = '' AND DATE(tgl_dikeluarkan) <= '".$tgl_sampai."'
						GROUP BY A.kode_kantor,A.id_supplier
						
					) AS C ON A.kode_kantor = C.kode_kantor AND A.id_supplier = C.id_supplier AND C.tgl_diterima > A.tgl_hutang_awal
					-- WHERE A.kode_supplier = 'PST' AND A.kode_kantor = 'JKT'
					".$cari."
					
					GROUP BY A.id_supplier
						,A.kode_supplier
						,A.nama_supplier
						,A.alamat
						,A.hari_tempo
						,A.hutang_awal
						,A.tgl_hutang_awal
					-- HAVING SUM(COALESCE(B.SISA,0)) + (A.hutang_awal - (SUM(COALESCE(C.bayar_hutang_awal,0)))) > 0
					-- HAVING (SUM(COALESCE(B.SISA,0)) + (A.hutang_awal - ((COALESCE(C.bayar_hutang_awal,0))))) > 0
				) AS  AA
				WHERE AA.SISA_ALL <> 0
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
		
		
		
		function list_h_penerimaan_limit($cari,$limit,$offset)
		{
			$query =
			"
				SELECT 
					A.id_h_penerimaan,
					A.id_h_pembelian,
					A.id_gedung,
					A.no_surat_jalan,
					A.nama_pengirim,
					A.cara_pengiriman,
					A.diterima_oleh,
					A.biaya_kirim,
					DATE(A.tgl_kirim) AS tgl_kirim,
					DATE(A.tgl_terima) AS tgl_terima,
					A.tgl_tempo_next,
					A.ket_h_penerimaan,
					A.tgl_ins,
					A.tgl_updt,
					A.user_updt,
					A.user_ins,
					A.kode_kantor,

					B.id_gedung,
					B.kat_gedung,
					B.kode_gedung,
					B.nama_gedung,
					B.ket_gedung,
					B.id_karyawan
					,COALESCE(C.JUMLAH,0) AS JUMLAH
					
					,MAX(COALESCE(D.img_nama,'')) AS img_nama
					,MAX(COALESCE(D.img_file,'')) AS img_file
					,MAX(COALESCE(D.img_url,'')) AS img_url
					,MAX(COALESCE(D.ket_img,'')) AS ket_img
				FROM tb_h_penerimaan AS A 
				LEFT JOIN tb_gedung AS B ON A.id_gedung = B.id_gedung AND A.kode_kantor = B.kode_kantor
				LEFT JOIN
				(
					SELECT id_h_penerimaan,kode_kantor,COUNT(id_produk) AS JUMLAH
					FROM tb_d_penerimaan
					GROUP BY id_h_penerimaan,kode_kantor
				) AS C ON A.id_h_penerimaan = C.id_h_penerimaan AND A.kode_kantor = C.kode_kantor
				
				
				LEFT JOIN tb_images AS D ON A.id_h_penerimaan = D.id AND A.kode_kantor = D.kode_kantor AND D.group_by = 'penerimaan'
				".$cari." 
				GROUP BY A.id_h_penerimaan,
					A.id_h_pembelian,
					A.id_gedung,
					A.no_surat_jalan,
					A.nama_pengirim,
					A.cara_pengiriman,
					A.diterima_oleh,
					A.biaya_kirim,
					A.tgl_kirim,
					A.tgl_terima,
					A.tgl_tempo_next,
					A.ket_h_penerimaan,
					A.tgl_ins,
					A.tgl_updt,
					A.user_updt,
					A.user_ins,
					A.kode_kantor,

					B.id_gedung,
					B.kat_gedung,
					B.kode_gedung,
					B.nama_gedung,
					B.ket_gedung,
					B.id_karyawan
					,COALESCE(C.JUMLAH,0)
				ORDER BY A.tgl_ins DESC LIMIT ".$offset.",".$limit."
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
		
		function count_h_penerimaan_limit_by_inv($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(id_h_penerimaan) AS JUMLAH 
										FROM tb_h_penerimaan AS A 
										LEFT JOIN tb_gedung AS B ON A.id_gedung = B.id_gedung AND A.kode_kantor = B.kode_kantor 
										LEFT JOIN tb_h_pembelian AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_pembelian = D.id_h_pembelian
										LEFT JOIN tb_supplier AS E ON A.kode_kantor = E.kode_kantor AND D.id_supplier = E.id_supplier
										".$cari);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function list_penerimaan_produk($cari)
		{
			$query = "
				SELECT
					A.*
					,COALESCE(E.kode_produk,'') AS kode_produk
					,COALESCE(E.nama_produk,'') AS nama_produk
					,COALESCE(B.id_d_penerimaan,'') AS id_d_penerimaan
					,COALESCE(B.diterima_satuan_beli,0) AS DITERIMA_SAT_BELI
					,COALESCE(B.kode_satuan,0) AS KODE_SATUAN
					,COALESCE(B.harga_beli,0) AS HARGA_PENERIMAAN
					,COALESCE(CC.harga,0) AS BL_HARGA
					,COALESCE(C.no_h_pembelian,'') AS NO_PEMBELIAN
					,COALESCE(D.nama_supplier,'') AS NAMA_SUPPLIER
				FROM tb_h_penerimaan AS A
				LEFT JOIN tb_d_penerimaan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penerimaan = B.id_h_penerimaan
				INNER JOIN tb_h_pembelian AS C ON A.kode_kantor = C.kode_kantor AND A.id_h_pembelian = C.id_h_pembelian
				LEFT JOIN tb_d_pembelian AS CC ON A.kode_kantor = CC.kode_kantor AND A.id_h_pembelian = CC.id_h_pembelian AND B.id_produk = CC.id_produk AND C.id_h_pembelian = CC.id_h_pembelian
				LEFT JOIN tb_supplier AS D ON A.kode_kantor = D.kode_kantor AND C.id_supplier = D.id_supplier
				LEFT JOIN tb_produk AS E ON A.kode_kantor = E.kode_kantor AND B.id_produk = E.id_produk
				-- WHERE A.kode_kantor = 'PST' AND A.no_surat_jalan = 'P21328469';
				".$cari."
				
				ORDER BY A.tgl_terima DESC, A.no_surat_jalan ASC
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
		
		function count_h_penerimaan_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(id_h_penerimaan) AS JUMLAH FROM tb_h_penerimaan AS A 
				LEFT JOIN tb_gedung AS B ON A.id_gedung = B.id_gedung AND A.kode_kantor = B.kode_kantor ".$cari);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function list_d_penerimaan_for_print_bukti_penerimaan($id_h_pembelian,$id_h_penerimaan,$cari,$kode_kantor)
		{
			$query = 
			"
				 -- SELECT * FROM
				SELECT A.id_d_pembelian, A.id_h_pembelian, A.id_produk,A.kode_produk,A.nama_produk,A.kode_satuan
					 ,A.BL_JUMLAH,A.BL_HARGA,A.BL_STATUS_KONVERSI,A.BL_KONVERSI,A.STN_DFL
					-- ,COALESCE(B.id_d_penerimaan,'') AS id_d_penerimaan
					,SUM(COALESCE(B.TRM_DITERIMA,0)) AS TRM_DITERIMA
					,SUM(COALESCE(B.TRM_SATUAN_BELI,0)) AS TRM_SATUAN_BELI
					,SUM(COALESCE(C.TRM_SATUAN_BELI,0)) AS TLH_TRM_SATUAN_BELI
					,A.BL_JUMLAH - (SUM(COALESCE(B.TRM_SATUAN_BELI,0)) + SUM(COALESCE(C.TRM_SATUAN_BELI,0))) AS SISA
					,COALESCE(B.TRM_BESAR_KONVERSI,0) AS TRM_BESAR_KONVERSI
					,COALESCE(B.TRM_STATUS_KONVERSI,'*') AS TRM_STATUS_KONVERSI
					,COALESCE(B.tgl_exp,'') AS TGL_EXP
					,COALESCE(B.harga_terima,'') AS harga_terima
					
					
				From

				(
					SELECT A.id_d_pembelian,A.id_h_pembelian,A.id_produk,A.jumlah AS BL_JUMLAH,A.kode_satuan,A.harga AS BL_HARGA
						  ,A.status_konversi AS BL_STATUS_KONVERSI,A.konversi AS BL_KONVERSI,A.tgl_ins
						  ,COALESCE(B.kode_produk,'') AS kode_produk
						  ,COALESCE(B.nama_produk,'') AS nama_produk
						  ,COALESCE(B.STN_DFL,'') AS STN_DFL
					  FROM tb_d_pembelian AS A
					  Left Join
					  (
						  SELECT A.id_produk,A.kode_produk,A.nama_produk
							-- ,COALESCE(B.kode_satuan,'') AS STN_DFL
							,'' AS STN_DFL
						  From tb_produk AS A
						  -- LEFT JOIN tb_satuan AS B ON A.id_satuan = B.id_satuan AND A.kode_kantor = B.kode_kantor
						  WHERE A.kode_kantor = '".$kode_kantor."' AND (A.kode_produk LIKE '%".$cari."%' OR A.nama_produk LIKE '%".$cari."%')
						  GROUP BY A.id_produk,A.kode_produk,A.nama_produk
					  ) AS B ON A.id_produk = B.id_produk
					  WHERE A.kode_kantor = '".$kode_kantor."' AND id_h_pembelian = '".$id_h_pembelian."'
					  GROUP BY A.id_d_pembelian,A.id_h_pembelian,A.id_produk,A.jumlah,A.kode_satuan,A.harga,A.status_konversi,A.konversi
						  ,COALESCE(B.kode_produk,'')
						  ,COALESCE(B.nama_produk,'')
						  ,COALESCE(B.STN_DFL,'')
				) AS A
				Left Join
				(
					  SELECT B.id_d_penerimaan,B.id_d_pembelian,B.diterima AS TRM_DITERIMA,B.konversi AS TRM_BESAR_KONVERSI
						  ,B.diterima_satuan_beli AS TRM_SATUAN_BELI, B.status_konversi AS TRM_STATUS_KONVERSI, B.tgl_exp
						  ,COALESCE(B.harga_beli,'') AS harga_terima
					  FROM tb_h_penerimaan AS A
					  INNER JOIN tb_d_penerimaan AS B ON A.id_h_penerimaan = B.id_h_penerimaan AND B.kode_kantor = '".$kode_kantor."'
					  WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_h_penerimaan LIKE '%".$id_h_penerimaan."%'  AND A.id_h_pembelian = '".$id_h_pembelian."'
					  GROUP BY B.id_d_penerimaan,B.id_d_pembelian,B.diterima,B.konversi,B.diterima_satuan_beli, B.status_konversi,COALESCE(B.harga_beli,''), B.tgl_exp
				) AS B ON A.id_d_pembelian = B.id_d_pembelian
				 Left Join
				(
					  SELECT B.id_d_pembelian
						  ,SUM(COALESCE(B.diterima,0)) AS TRM_DITERIMA
						  ,(B.konversi) AS TRM_BESAR_KONVERSI
						  ,SUM(COALESCE(B.diterima_satuan_beli,0)) AS TRM_SATUAN_BELI
						  ,B.status_konversi AS TRM_STATUS_KONVERSI
						  ,B.tgl_exp
					  FROM tb_h_penerimaan AS A
					  INNER JOIN tb_d_penerimaan AS B ON A.id_h_penerimaan = B.id_h_penerimaan AND B.kode_kantor = '".$kode_kantor."'
					  WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_h_penerimaan <> '".$id_h_penerimaan."'  AND A.id_h_pembelian = '".$id_h_pembelian."'
					  GROUP BY B.id_d_pembelian,B.konversi, B.status_konversi
				) AS C ON A.id_d_pembelian = C.id_d_pembelian
				WHERE (A.kode_produk LIKE '%".$cari."%' OR A.nama_produk LIKE '%".$cari."%')
				GROUP BY A.id_d_pembelian, A.id_h_pembelian, A.id_produk,A.kode_produk,A.nama_produk,A.kode_satuan,A.BL_JUMLAH,A.BL_HARGA,A.BL_STATUS_KONVERSI,A.BL_KONVERSI,A.STN_DFL
				-- ,COALESCE(B.id_d_penerimaan,'')
				,COALESCE(B.TRM_BESAR_KONVERSI,0)
				,COALESCE(B.TRM_STATUS_KONVERSI,'*')
				,COALESCE(B.harga_terima,'')
				
				HAVING SUM(COALESCE(B.TRM_DITERIMA,0)) > 0
				ORDER BY A.nama_produk ASC
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
		
		function list_d_penerimaan($id_h_pembelian,$id_h_penerimaan,$cari,$kode_kantor)
		{
			$query = 
			"
				 -- SELECT * FROM
				SELECT A.id_d_pembelian, A.id_h_pembelian, A.id_produk,A.kode_produk,A.nama_produk,A.kode_satuan
					 ,A.BL_JUMLAH,A.BL_HARGA,A.BL_STATUS_KONVERSI,A.BL_KONVERSI,A.STN_DFL
					-- ,COALESCE(B.id_d_penerimaan,'') AS id_d_penerimaan
					,SUM(COALESCE(B.TRM_DITERIMA,0)) AS TRM_DITERIMA
					,SUM(COALESCE(B.TRM_SATUAN_BELI,0)) AS TRM_SATUAN_BELI
					,SUM(COALESCE(C.TRM_SATUAN_BELI,0)) AS TLH_TRM_SATUAN_BELI
					,A.BL_JUMLAH - (SUM(COALESCE(B.TRM_SATUAN_BELI,0)) + SUM(COALESCE(C.TRM_SATUAN_BELI,0))) AS SISA
					,COALESCE(B.TRM_BESAR_KONVERSI,0) AS TRM_BESAR_KONVERSI
					,COALESCE(B.TRM_STATUS_KONVERSI,'*') AS TRM_STATUS_KONVERSI
					,COALESCE(B.tgl_exp,'') AS TGL_EXP
				From

				(
					SELECT A.id_d_pembelian,A.id_h_pembelian,A.id_produk,A.jumlah AS BL_JUMLAH,A.kode_satuan,A.harga AS BL_HARGA
						  ,A.status_konversi AS BL_STATUS_KONVERSI,A.konversi AS BL_KONVERSI,A.tgl_ins
						  ,COALESCE(B.kode_produk,'') AS kode_produk
						  ,COALESCE(B.nama_produk,'') AS nama_produk
						  ,COALESCE(B.STN_DFL,'') AS STN_DFL
					  FROM tb_d_pembelian AS A
					  Left Join
					  (
						  SELECT A.id_produk,A.kode_produk,A.nama_produk
							-- ,COALESCE(B.kode_satuan,'') AS STN_DFL
							,'' AS STN_DFL
						  From tb_produk AS A
						  -- LEFT JOIN tb_satuan AS B ON A.id_satuan = B.id_satuan AND A.kode_kantor = B.kode_kantor
						  WHERE A.kode_kantor = '".$kode_kantor."' AND (A.kode_produk LIKE '%".$cari."%' OR A.nama_produk LIKE '%".$cari."%')
						  GROUP BY A.id_produk,A.kode_produk,A.nama_produk
					  ) AS B ON A.id_produk = B.id_produk
					  WHERE A.kode_kantor = '".$kode_kantor."' AND id_h_pembelian = '".$id_h_pembelian."'
					  GROUP BY A.id_d_pembelian,A.id_h_pembelian,A.id_produk,A.jumlah,A.kode_satuan,A.harga,A.status_konversi,A.konversi
						  ,COALESCE(B.kode_produk,'')
						  ,COALESCE(B.nama_produk,'')
						  ,COALESCE(B.STN_DFL,'')
				) AS A
				Left Join
				(
					  SELECT B.id_d_penerimaan,B.id_d_pembelian,B.diterima AS TRM_DITERIMA,B.konversi AS TRM_BESAR_KONVERSI
						  ,B.diterima_satuan_beli AS TRM_SATUAN_BELI, B.status_konversi AS TRM_STATUS_KONVERSI, B.tgl_exp
					  FROM tb_h_penerimaan AS A
					  INNER JOIN tb_d_penerimaan AS B ON A.id_h_penerimaan = B.id_h_penerimaan AND B.kode_kantor = '".$kode_kantor."'
					  WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_h_penerimaan LIKE '%".$id_h_penerimaan."%'  AND A.id_h_pembelian = '".$id_h_pembelian."'
					  GROUP BY B.id_d_penerimaan,B.id_d_pembelian,B.diterima,B.konversi,B.diterima_satuan_beli, B.status_konversi
				) AS B ON A.id_d_pembelian = B.id_d_pembelian
				 Left Join
				(
					  SELECT B.id_d_pembelian
						  ,SUM(COALESCE(B.diterima,0)) AS TRM_DITERIMA
						  ,(B.konversi) AS TRM_BESAR_KONVERSI
						  ,SUM(COALESCE(B.diterima_satuan_beli,0)) AS TRM_SATUAN_BELI
						  ,B.status_konversi AS TRM_STATUS_KONVERSI
						  ,B.tgl_exp
					  FROM tb_h_penerimaan AS A
					  INNER JOIN tb_d_penerimaan AS B ON A.id_h_penerimaan = B.id_h_penerimaan AND B.kode_kantor = '".$kode_kantor."'
					  WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_h_penerimaan <> '".$id_h_penerimaan."'  AND A.id_h_pembelian = '".$id_h_pembelian."'
					  GROUP BY B.id_d_pembelian,B.konversi, B.status_konversi
				) AS C ON A.id_d_pembelian = C.id_d_pembelian
				WHERE (A.kode_produk LIKE '%".$cari."%' OR A.nama_produk LIKE '%".$cari."%')
				GROUP BY A.id_d_pembelian, A.id_h_pembelian, A.id_produk,A.kode_produk,A.nama_produk,A.kode_satuan,A.BL_JUMLAH,A.BL_HARGA,A.BL_STATUS_KONVERSI,A.BL_KONVERSI,A.STN_DFL
				-- ,COALESCE(B.id_d_penerimaan,'')
				,COALESCE(B.TRM_BESAR_KONVERSI,0)
				,COALESCE(B.TRM_STATUS_KONVERSI,'*')
				ORDER BY A.nama_produk ASC
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
		
		function list_d_penerimaan_pakai_alias($id_supplier,$id_h_pembelian,$id_h_penerimaan,$cari,$kode_kantor)
		{
			$query = 
			"
				 -- SELECT * FROM
				SELECT A.id_d_pembelian, A.id_h_pembelian, A.id_produk
					
					,A.kode_produk,A.nama_produk
					,COALESCE(D.kode_produk,'') AS kode_produk_alias
					,COALESCE(D.nama_produk,'') AS nama_produk_alias
					
					,A.kode_satuan
					,A.BL_JUMLAH,A.BL_HARGA,A.BL_STATUS_KONVERSI,A.BL_KONVERSI,A.STN_DFL
					-- ,COALESCE(B.id_d_penerimaan,'') AS id_d_penerimaan
					
					
					-- ,COALESCE(B.HRG_TERIMA,0) AS HRG_TERIMA
					,B.HRG_TERIMA
					,SUM(COALESCE(B.TRM_DITERIMA,0)) AS TRM_DITERIMA
					,SUM(COALESCE(B.TRM_SATUAN_BELI,0)) AS TRM_SATUAN_BELI
					,SUM(COALESCE(C.TRM_SATUAN_BELI,0)) AS TLH_TRM_SATUAN_BELI
					,A.BL_JUMLAH - (SUM(COALESCE(B.TRM_SATUAN_BELI,0)) + SUM(COALESCE(C.TRM_SATUAN_BELI,0))) AS SISA
					,COALESCE(B.TRM_BESAR_KONVERSI,0) AS TRM_BESAR_KONVERSI
					,COALESCE(B.TRM_STATUS_KONVERSI,'*') AS TRM_STATUS_KONVERSI
					,COALESCE(B.tgl_exp,'') AS TGL_EXP
					
					,COALESCE(B.optr_diskon,'%') AS optr_diskon
					,COALESCE(B.diskon,'0') AS diskon
					
					,A.tgl_ins
				From

				(
					SELECT A.id_d_pembelian,A.id_h_pembelian,A.id_produk,A.jumlah AS BL_JUMLAH,A.kode_satuan
							,A.harga AS BL_HARGA
						  ,A.status_konversi AS BL_STATUS_KONVERSI
						  ,A.konversi AS BL_KONVERSI
						  ,A.tgl_ins
						  ,COALESCE(B.kode_produk,'') AS kode_produk
						  ,COALESCE(B.nama_produk,'') AS nama_produk
						  ,COALESCE(B.STN_DFL,'') AS STN_DFL
						  
					  FROM tb_d_pembelian AS A
					  Left Join
					  (
						  SELECT A.id_produk,A.kode_produk,A.nama_produk
							-- ,COALESCE(B.kode_satuan,'') AS STN_DFL
							,'' AS STN_DFL
						  From tb_produk AS A
						  -- LEFT JOIN tb_satuan AS B ON A.id_satuan = B.id_satuan AND A.kode_kantor = B.kode_kantor
						  WHERE A.kode_kantor = '".$kode_kantor."' AND (A.kode_produk LIKE '%".$cari."%' OR A.nama_produk LIKE '%".$cari."%')
						  GROUP BY A.id_produk,A.kode_produk,A.nama_produk
					  ) AS B ON A.id_produk = B.id_produk
					  WHERE A.kode_kantor = '".$kode_kantor."' AND id_h_pembelian = '".$id_h_pembelian."'
					  GROUP BY A.id_d_pembelian,A.id_h_pembelian,A.id_produk,A.jumlah,A.kode_satuan,A.harga,A.status_konversi,A.konversi
						  ,COALESCE(B.kode_produk,'')
						  ,COALESCE(B.nama_produk,'')
						  ,COALESCE(B.STN_DFL,'')
				) AS A
				Left Join
				(
					  SELECT 
						B.id_d_penerimaan
						,B.id_d_pembelian
						,B.harga_beli AS HRG_TERIMA
						,B.diterima AS TRM_DITERIMA
						,B.konversi AS TRM_BESAR_KONVERSI
						,B.diterima_satuan_beli AS TRM_SATUAN_BELI
						,B.status_konversi AS TRM_STATUS_KONVERSI
						,B.tgl_exp
						,COALESCE(B.optr_diskon,'%') AS optr_diskon
						,COALESCE(B.diskon,'0') AS diskon
					  FROM tb_h_penerimaan AS A
					  INNER JOIN tb_d_penerimaan AS B ON A.id_h_penerimaan = B.id_h_penerimaan AND B.kode_kantor = '".$kode_kantor."'
					  WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_h_penerimaan LIKE '%".$id_h_penerimaan."%'  AND A.id_h_pembelian = '".$id_h_pembelian."'
					  GROUP BY B.id_d_penerimaan,B.id_d_pembelian,B.harga_beli,B.diterima,B.konversi,B.diterima_satuan_beli, B.status_konversi
				) AS B ON A.id_d_pembelian = B.id_d_pembelian
				 Left Join
				(
					  SELECT B.id_d_pembelian
						  ,SUM(COALESCE(B.diterima,0)) AS TRM_DITERIMA
						  ,(B.konversi) AS TRM_BESAR_KONVERSI
						  ,SUM(COALESCE(B.diterima_satuan_beli,0)) AS TRM_SATUAN_BELI
						  ,B.status_konversi AS TRM_STATUS_KONVERSI
						  ,B.tgl_exp
					  FROM tb_h_penerimaan AS A
					  INNER JOIN tb_d_penerimaan AS B ON A.id_h_penerimaan = B.id_h_penerimaan AND B.kode_kantor = '".$kode_kantor."'
					  WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_h_penerimaan <> '".$id_h_penerimaan."'  AND A.id_h_pembelian = '".$id_h_pembelian."'
					  GROUP BY B.id_d_pembelian,B.konversi, B.status_konversi
				) AS C ON A.id_d_pembelian = C.id_d_pembelian
				
				LEFT JOIN tb_alias_produk AS D ON D.kode_kantor = '".$kode_kantor."' AND A.id_produk = D.id_produk AND D.grup = 'PURCHASE' AND D.id_supplier = '".$id_supplier."'
				
				WHERE (A.kode_produk LIKE '%".$cari."%' OR A.nama_produk LIKE '%".$cari."%')
				GROUP BY A.id_d_pembelian, A.id_h_pembelian, A.id_produk,A.kode_produk,A.nama_produk,A.kode_satuan
				,A.BL_JUMLAH
				,A.BL_HARGA
				,A.BL_STATUS_KONVERSI
				,A.BL_KONVERSI
				,A.STN_DFL
				-- ,COALESCE(B.id_d_penerimaan,'')
				
				,COALESCE(B.HRG_TERIMA,0)
				,COALESCE(B.TRM_BESAR_KONVERSI,0)
				,COALESCE(B.TRM_STATUS_KONVERSI,'*')
				ORDER BY A.nama_produk ASC
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
		
		function list_d_penerimaan_cek_dari_pusat($id_h_pembelian,$id_h_penerimaan,$cari,$kode_kantor)
		{
			$query = 
			"
				 -- SELECT * FROM
				SELECT A.id_d_pembelian, A.id_h_pembelian, A.id_produk,A.kode_produk,A.nama_produk,A.kode_satuan
					 ,A.BL_JUMLAH,A.BL_HARGA,A.BL_STATUS_KONVERSI,A.BL_KONVERSI,A.STN_DFL
					-- ,COALESCE(B.id_d_penerimaan,'') AS id_d_penerimaan
					,SUM(COALESCE(B.TRM_DITERIMA,0)) AS TRM_DITERIMA
					,SUM(COALESCE(B.TRM_SATUAN_BELI,0)) AS TRM_SATUAN_BELI
					,SUM(COALESCE(C.TRM_SATUAN_BELI,0)) AS TLH_TRM_SATUAN_BELI
					,A.BL_JUMLAH - (SUM(COALESCE(B.TRM_SATUAN_BELI,0)) + SUM(COALESCE(C.TRM_SATUAN_BELI,0))) AS SISA
					,COALESCE(B.TRM_BESAR_KONVERSI,0) AS TRM_BESAR_KONVERSI
					,COALESCE(B.TRM_STATUS_KONVERSI,'*') AS TRM_STATUS_KONVERSI
					,COALESCE(B.tgl_exp,'') AS TGL_EXP
				From

				(
					SELECT A.id_d_pembelian,A.id_h_pembelian,A.id_produk,A.jumlah AS BL_JUMLAH,A.kode_satuan,A.harga AS BL_HARGA
						  ,A.status_konversi AS BL_STATUS_KONVERSI,A.konversi AS BL_KONVERSI,A.tgl_ins
						  ,COALESCE(B.kode_produk,'') AS kode_produk
						  ,COALESCE(B.nama_produk,'') AS nama_produk
						  ,COALESCE(B.STN_DFL,'') AS STN_DFL
					  FROM tb_d_pembelian AS A
					  Left Join
					  (
						  SELECT A.id_produk,A.kode_produk,A.nama_produk
							-- ,COALESCE(B.kode_satuan,'') AS STN_DFL
							,'' AS STN_DFL
						  From tb_produk AS A
						  -- LEFT JOIN tb_satuan AS B ON A.id_satuan = B.id_satuan AND A.kode_kantor = B.kode_kantor
						  WHERE A.kode_kantor = '".$kode_kantor."' AND (A.kode_produk LIKE '%".$cari."%' OR A.nama_produk LIKE '%".$cari."%')
						  GROUP BY A.id_produk,A.kode_produk,A.nama_produk
					  ) AS B ON A.id_produk = B.id_produk
					  WHERE A.kode_kantor = '".$kode_kantor."' AND id_h_pembelian = '".$id_h_pembelian."'
					  GROUP BY A.id_d_pembelian,A.id_h_pembelian,A.id_produk,A.jumlah,A.kode_satuan,A.harga,A.status_konversi,A.konversi
						  ,COALESCE(B.kode_produk,'')
						  ,COALESCE(B.nama_produk,'')
						  ,COALESCE(B.STN_DFL,'')
				) AS A
				Left Join
				(
					  SELECT B.id_d_penerimaan,B.id_d_pembelian,B.diterima AS TRM_DITERIMA,B.konversi AS TRM_BESAR_KONVERSI
						  ,B.diterima_satuan_beli AS TRM_SATUAN_BELI, B.status_konversi AS TRM_STATUS_KONVERSI, B.tgl_exp
					  FROM tb_h_penerimaan AS A
					  INNER JOIN tb_d_penerimaan AS B ON A.id_h_penerimaan = B.id_h_penerimaan AND B.kode_kantor = '".$kode_kantor."'
					  WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_h_penerimaan LIKE '%".$id_h_penerimaan."%'  AND A.id_h_pembelian = '".$id_h_pembelian."'
					  GROUP BY B.id_d_penerimaan,B.id_d_pembelian,B.diterima,B.konversi,B.diterima_satuan_beli, B.status_konversi
				) AS B ON A.id_d_pembelian = B.id_d_pembelian
				 Left Join
				(
					  SELECT B.id_d_pembelian
						  ,SUM(COALESCE(B.diterima,0)) AS TRM_DITERIMA
						  ,(B.konversi) AS TRM_BESAR_KONVERSI
						  ,SUM(COALESCE(B.diterima_satuan_beli,0)) AS TRM_SATUAN_BELI
						  ,B.status_konversi AS TRM_STATUS_KONVERSI
						  ,B.tgl_exp
					  FROM tb_h_penerimaan AS A
					  INNER JOIN tb_d_penerimaan AS B ON A.id_h_penerimaan = B.id_h_penerimaan AND B.kode_kantor = '".$kode_kantor."'
					  WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_h_penerimaan <> '".$id_h_penerimaan."'  AND A.id_h_pembelian = '".$id_h_pembelian."'
					  GROUP BY B.id_d_pembelian,B.konversi, B.status_konversi
				) AS C ON A.id_d_pembelian = C.id_d_pembelian
				WHERE (A.kode_produk LIKE '%".$cari."%' OR A.nama_produk LIKE '%".$cari."%')
				GROUP BY A.id_d_pembelian, A.id_h_pembelian, A.id_produk,A.kode_produk,A.nama_produk,A.kode_satuan,A.BL_JUMLAH,A.BL_HARGA,A.BL_STATUS_KONVERSI,A.BL_KONVERSI,A.STN_DFL
				-- ,COALESCE(B.id_d_penerimaan,'')
				,COALESCE(B.TRM_BESAR_KONVERSI,0)
				,COALESCE(B.TRM_STATUS_KONVERSI,'*')
				ORDER BY A.nama_produk ASC
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
		
		function simpan
		(
			$id_h_pembelian,
			$id_gedung,
			$no_surat_jalan,
			$nama_pengirim,
			$cara_pengiriman,
			$diterima_oleh,
			$biaya_kirim,
			$tgl_kirim,
			$tgl_terima,
			$tgl_tempo_next,
			$ket_h_penerimaan,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_h_penerimaan
				(
					id_h_penerimaan,
					id_h_pembelian,
					id_gedung,
					no_surat_jalan,
					nama_pengirim,
					cara_pengiriman,
					diterima_oleh,
					biaya_kirim,
					tgl_kirim,
					tgl_terima,
					tgl_tempo_next,
					ket_h_penerimaan,
					tgl_ins,
					tgl_updt,
					user_updt,
					user_ins,
					kode_kantor

				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_h_penerimaan
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
								COALESCE(MAX(CAST(RIGHT(id_h_penerimaan,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_h_penerimaan
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_h_pembelian."',
					'".$id_gedung."',
					'".$no_surat_jalan."',
					'".$nama_pengirim."',
					'".$cara_pengiriman."',
					'".$diterima_oleh."',
					'".$biaya_kirim."',
					'".$tgl_kirim."',
					'".$tgl_terima."',
					'".$tgl_tempo_next."',
					'".$ket_h_penerimaan."',
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
			$id_h_penerimaan,
			$id_h_pembelian,
			$id_gedung,
			$no_surat_jalan,
			$nama_pengirim,
			$cara_pengiriman,
			$diterima_oleh,
			$biaya_kirim,
			$tgl_kirim,
			$tgl_terima,
			$tgl_tempo_next,
			$ket_h_penerimaan,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_h_penerimaan SET
					
						id_h_pembelian = '".$id_h_pembelian."',
						id_gedung = '".$id_gedung."',
						no_surat_jalan = '".$no_surat_jalan."',
						nama_pengirim = '".$nama_pengirim."',
						cara_pengiriman = '".$cara_pengiriman."',
						diterima_oleh = '".$diterima_oleh."',
						biaya_kirim = '".$biaya_kirim."',
						tgl_kirim = '".$tgl_kirim."',
						tgl_terima = '".$tgl_terima."',
						tgl_tempo_next = '".$tgl_tempo_next."',
						ket_h_penerimaan = '".$ket_h_penerimaan."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_h_penerimaan = '".$id_h_penerimaan
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_h_penerimaan)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_h_penerimaan WHERE ".$berdasarkan." = '".$id_h_penerimaan."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_d_penerimaan($berdasarkan,$id_h_penerimaan)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_d_penerimaan WHERE ".$berdasarkan." = '".$id_h_penerimaan."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_h_penerimaan($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_h_penerimaan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query;
            }
            else
            {
                return false;
            }
        }
		
		function get_h_penerimaan_cari($cari)
        {
            $query =
			"
				SELECT * FROM tb_h_penerimaan ".$cari."
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
		
		function get_h_penerimaan_cari_get_supplier($cari)
        {
            $query =
			"
				SELECT 
					A.*
					,COALESCE(B.id_supplier,'') AS id_supplier
				FROM tb_h_penerimaan AS A
				LEFT JOIN tb_h_pembelian AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_pembelian = B.id_h_pembelian
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
		
		function get_h_penerimaan_cari_with_id_supplier($cari)
        {
            $query =
			"
				SELECT 
					A.*
					,COALESCE(B.id_supplier,'') AS id_supplier
				FROM tb_h_penerimaan AS A
				LEFT JOIN tb_h_pembelian AS B ON A.id_h_pembelian = B.id_h_pembelian AND A.kode_kantor = B.kode_kantor ".$cari."
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
	
		function list_penerimaan_pembelian_sj($cari,$order_by)
		{
			$query =
			"
				SELECT
					A.*
					,COALESCE(B.no_h_pembelian,'') AS no_h_pembelian
					,COALESCE(C.kode_supplier,'') AS kode_supplier
					,COALESCE(C.nama_supplier,'') AS nama_supplier
					,COALESCE(C.tlp,'') AS tlp_supplier
					,COALESCE(C.alamat,'') AS alamat_supplier
				FROM tb_h_penerimaan AS A
				LEFT JOIN tb_h_pembelian AS B ON A.id_h_pembelian = B.id_h_pembelian AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_supplier AS C ON A.kode_kantor = C.kode_kantor AND B.id_supplier = C.id_supplier
				".$cari."
				".$order_by."
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
		
		function get_surat_jalan_atau_faktur_penjualan($cari)
        {
            //$query = $this->db->get_where('tb_satuan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
			
			$query =
			"
				SELECT * FROM tb_h_penjualan ".$cari."
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