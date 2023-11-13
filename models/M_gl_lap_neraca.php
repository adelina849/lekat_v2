<?php
	class M_gl_lap_neraca extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function get_assets($kode_kantor,$tgl_sampai)
		{
			$query = "
					SELECT ROUND(SUM(SETELAH_PENYUSUTAN),1) AS SETELAH_PENYUSUTAN 
					FROM
					(
						SELECT
							nama_assets
							,tgl_beli
							,nominal
							,A.penyusutan
							,TIMESTAMPDIFF(MONTH, A.tgl_beli, NOW()) AS selisih_bulan
							,ROUND(((A.nominal/100) * A.penyusutan) * (TIMESTAMPDIFF(MONTH, A.tgl_beli, NOW())),0) AS NOMINAL_PENYUSUTAN
							,A.nominal - (ROUND(((A.nominal/100) * A.penyusutan) * (TIMESTAMPDIFF(MONTH, A.tgl_beli, NOW())),0)) AS SETELAH_PENYUSUTAN
						FROM tb_d_assets AS A
						WHERE kode_kantor = '".$kode_kantor."' 
						AND isUang = 0 
						AND tgl_beli <= '".$tgl_sampai."'
					) AS AA    
					; -- PRODUK/BARANG/ASSETS
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
		
		function get_kas
		(
			$kode_kantor
			,$id_bank
			,$ref
			,$cari
			,$tgl_sampai
		)
		{
			$query = "CALL PROC_SUM_JURNAL_2('".$kode_kantor."','".$id_bank."','".$ref."','".$cari."','".$tgl_sampai."');";
			
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
		
		function get_kas_per_target
		(
			$kode_kantor
			,$target
			,$tgl_dari
			,$tgl_sampai
		)
		{
			$query = "CALL SP_SALDO_PERAKUN_TARGET('".$kode_kantor."','".$target."','".$tgl_dari."','".$tgl_sampai."');";
			
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
		
		function get_persediaan
		(
			$kode_kantor
			,$cari
			,$tgl_sampai
			,$jam
			,$menit
		)
		{
			//$query = "CALL PROC_STOCK_PRODUK_PERSEDIAAN_2('".$kode_kantor."','".$cari."','".$tgl_sampai."','".$jam."','".$menit."');";
			
			//KARENA PROSEDUR DIEKSEKUSI OLEH JOB DENGAN INSERT KE TABLE
			//$query = "CALL SP_INSERT_STOCK_TO_TB_2('".$kode_kantor."','".$tgl_sampai."','".$tgl_sampai."','0','200000','');";
			
			$query = "SELECT ROUND(SUM(nominal_stock),1) AS NOMINAL_STOCK FROM tb_sum_persediaan WHERE waktu = 
						(
							SELECT waktu FROM tb_sum_persediaan WHERE waktu <= '".$tgl_sampai." 23:00:00' ORDER BY waktu DESC LIMIT 0,1
						)";
			
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
		
		function saldo_piutang_pelanggan_disamakan($cari,$tgl_sampai)
		{
			$query = 
			"
			
			SELECT 
				ROUND(SUM(COALESCE(B.SISA,0)),1) AS SISA_TR
				,ROUND(SUM(A.piutang_awal - COALESCE(C.bayar_piutang_awal,0)),1) AS SISA_AWAL
			
			FROM tb_costumer AS A
			LEFT JOIN
			(
				SELECT A.kode_kantor,A.id_costumer,MAX(A.DT_PENJUALAN) AS DT_PENJUALAN,MIN(A.TEMPO_FIX) AS TEMPO_FIX,COUNT(id_h_penjualan) AS CNT_TR,SUM(A.SISA) AS SISA
				FROM
				(
					SELECT 
						A.kode_kantor,A.id_h_penjualan, A.id_costumer,A.no_faktur,A.no_costmer,A.nama_costumer,A.tgl_tempo,COALESCE(C.TEMPO_NEXT,'') AS TEMPO_NEXT
						,
							CASE WHEN (COALESCE(C.TEMPO_NEXT,'') = '') THEN
								A.tgl_tempo
							ELSE
								COALESCE(C.TEMPO_NEXT,'')
							END AS TEMPO_FIX
						
						,DATE(A.tgl_h_penjualan) AS DT_PENJUALAN
						,CASE
						WHEN (A.waktu_mulai_pemeriksaan != '0000-00-00 00:00:00') AND (A.waktu_selesai_pemeriksaan != '0000-00-00 00:00:00') AND (A.type_h_penjualan <> 'PENJUALAN LANGSUNG') THEN
							TIMESTAMPDIFF
							(
								MINUTE,
								A.waktu_mulai_pemeriksaan,
								A.waktu_selesai_pemeriksaan
							) 
						ELSE
							0
						END
						AS LAMA_KONSUL
						,COALESCE(B.SUBTOTAL,0) AS SUBTOTAL
						,(COALESCE(B.SUBTOTAL,0) + A.pajak + A.biaya) - (A.diskon + A.nominal_retur) AS SUBTOTAL_ALL
						
						,COALESCE(C.BAYAR_CASH,0) AS BAYAR_CASH_ORI
						,CASE
							WHEN (COALESCE(C.TOTAL_BAYAR,0) + A.diskon + A.nominal_retur) > (COALESCE(B.SUBTOTAL,0) + A.pajak + A.biaya) THEN
								(COALESCE(B.SUBTOTAL,0) + A.pajak + A.biaya) - (COALESCE(C.BAYAR_BANK,0) + A.diskon + A.nominal_retur)
							ELSE
								COALESCE(C.BAYAR_CASH,0)
							END AS BAYAR_CASH_ALL
						,COALESCE(C.BAYAR_CASH,0) AS BAYAR_CASH
						,COALESCE(C.BAYAR_BANK,0) AS BAYAR_BANK
						
						,COALESCE(C.TOTAL_BAYAR,0) AS TOTAL_BAYAR_ORI
						,CASE
							WHEN (COALESCE(C.TOTAL_BAYAR,0) + A.diskon + A.nominal_retur) > (COALESCE(B.SUBTOTAL,0) + A.pajak + A.biaya) THEN
								(COALESCE(B.SUBTOTAL,0) + A.pajak + A.biaya)
							ELSE
								COALESCE(C.TOTAL_BAYAR,0)
							END AS TOTAL_BAYAR
						
						,COALESCE(C.BANK,'') AS BANK
						
						,(COALESCE(B.SUBTOTAL,0) + A.pajak + A.biaya) - (COALESCE(C.TOTAL_BAYAR,0) + A.diskon + A.nominal_retur) AS SISA
						
					FROM tb_h_penjualan AS A
					LEFT JOIN
					(
						SELECT 
							kode_kantor,id_h_penjualan 
							,SUM(jumlah * (harga - NOMINAL_DISKON)) AS SUBTOTAL
						FROM
						(
						SELECT
							kode_kantor,id_d_penjualan,id_h_penjualan,jumlah,harga,optr_diskon,diskon
							,CASE WHEN (optr_diskon = '%') OR (optr_diskon = '') THEN
								(harga/100) * diskon
							ELSE
								diskon
							END AS NOMINAL_DISKON
						FROM tb_d_penjualan
						WHERE isProduk <> 'CONSUMABLE'
						) AS AA
						GROUP BY kode_kantor,id_h_penjualan
						
					) AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
					LEFT JOIN
					(
						SELECT AA.kode_kantor, AA.id_h_penjualan
							,SUM(AA.BAYAR_CASH) AS BAYAR_CASH
							,SUM(AA.BAYAR_BANK) AS BAYAR_BANK
							,SUM(AA.BAYAR) AS TOTAL_BAYAR
							,GROUP_CONCAT(BANK) AS BANK
							,MAX(AA.tgl_tempo_next) AS TEMPO_NEXT
						FROM
						(
							SELECT A.kode_kantor,A.id_h_penjualan,A.tgl_tempo_next
								,SUM(A.nominal) AS BAYAR 
								,CASE WHEN A.id_bank = '' THEN SUM(A.nominal) ELSE 0 END AS BAYAR_CASH
								,CASE WHEN A.id_bank <> '' THEN SUM(A.nominal) ELSE 0 END AS BAYAR_BANK
								,COALESCE(CONCAT(B.nama_bank,' - ',B.norek),'') AS BANK
								
							FROM tb_d_penjualan_bayar AS A
							LEFT JOIN tb_bank AS B ON A.kode_kantor = B.kode_kantor AND A.id_bank = B.id_bank
							-- WHERE DATE(A.tgl_bayar) <= '".$tgl_sampai."'
							WHERE A.nominal > 0 AND DATE(A.tgl_pencairan) <= '".$tgl_sampai."'
							GROUP BY A.kode_kantor,A.id_h_penjualan,A.tgl_tempo_next,A.id_bank,COALESCE(CONCAT(B.nama_bank,' - ',B.norek),'')
						) AS AA
						GROUP BY AA.kode_kantor, AA.id_h_penjualan
						
						
					) AS C ON A.kode_kantor = C.kode_kantor AND A.id_h_penjualan = C.id_h_penjualan
					WHERE A.sts_penjualan IN ('SELESAI','PEMERIKSAAN','PEMBAYARAN','PENDING')
					AND ((COALESCE(B.SUBTOTAL,0) + A.pajak + A.biaya) - (COALESCE(C.TOTAL_BAYAR,0) + A.diskon + A.nominal_retur)) > 1
					AND DATE(A.tgl_h_penjualan) <= '".$tgl_sampai."'
				) AS A
				GROUP BY A.kode_kantor,A.id_costumer
				HAVING SUM(A.SISA) > 0
			) AS B ON A.kode_kantor = B.kode_kantor AND A.id_costumer = B.id_costumer AND B.DT_PENJUALAN > A.tgl_piutang_awal
			LEFT JOIN
			(
				SELECT
					A.kode_kantor,A.id_costumer,MIN(A.tgl_uang_masuk) AS tgl_uang_masuk,SUM(A.nominal) AS bayar_piutang_awal
				FROM tb_uang_masuk AS A
				WHERE A.id_induk_uang_masuk = A.id_uang_masuk AND A.isAwal <> 'YA'
				AND DATE(A.tgl_uang_masuk) <= '".$tgl_sampai."'
				GROUP BY A.kode_kantor,A.id_costumer
			) AS C ON A.kode_kantor = C.kode_kantor AND A.id_costumer = C.id_costumer AND C.tgl_uang_masuk > A.tgl_piutang_awal
			LEFT JOIN
			(
				SELECT DISTINCT kode_kantor,id_kat_costumer,nama_kat_costumer
				FROM tb_kat_costumer
				GROUP BY kode_kantor,id_kat_costumer,nama_kat_costumer
			) AS D ON A.kode_kantor = D.kode_kantor AND A.id_kat_costumer = D.id_kat_costumer
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
		
		
		function saldo_akumulasi_hutang_per_supplier_disamakan($cari,$tgl_sampai)
		{
			$query =
			"
				SELECT
					-- *
					ROUND(SUM(SISA_TR),1) AS SISA_TR
					,ROUND(SUM(SISA_AWAL),1) AS SISA_AWAL
				FROM
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
		
		function get_piutang_awal_tr
		(
			$kode_kantor
			,$tgl_sampai
			,$tgl_backup
		)
		{
			$query = "
			
				SELECT
					COALESCE(SUM(piutang_awal),0) As piutang_awal
					,COALESCE(SUM(bayar_piutang_awal),0) AS bayar_piutang_awal
					,COALESCE(SUM(SISA_PIUTANG_AWAL),0) AS SISA_PIUTANG_AWAL
					,COALESCE(SUM(SISA_PIUTANG_TR),0) AS SISA_PIUTANG_TR
					-- ,SUM(TABUNGAN) AS TABUNGAN
					-- ,SUM(SISA_TABUNGAN) AS SISA_TABUNGAN
					,0 AS TABUNGAN
					,0 AS SISA_TABUNGAN
				From
				(
					SELECT
						SUM(piutang_awal) As piutang_awal
						,SUM(bayar_piutang) AS bayar_piutang_awal
						,SUM(piutang_awal) - SUM(bayar_piutang) AS SISA_PIUTANG_AWAL
						,SUM(SISA_PIUTANG_TR) AS SISA_PIUTANG_TR
						-- ,SUM(TABUNGAN) AS TABUNGAN,SUM(SISA_TABUNGAN) AS SISA_TABUNGAN
						,0 AS TABUNGAN
						,0 AS SISA_TABUNGAN
					From
					(
						SELECT
							A.id_costumer , A.id_kat_costumer
							,A.no_costumer,A.nama_lengkap
							,CASE WHEN
								(
									CONCAT(left(COALESCE(A.tgl_piutang_awal,''),10),' ',right(A.tgl_ins,8)) <  CONCAT('".$tgl_sampai."',' ','23:59:59')
									AND
									CONCAT(left(COALESCE(A.tgl_piutang_awal,''),10),' ',right(A.tgl_ins,8)) >  '".$tgl_backup."'
								)
							THEN
								piutang_awal
							Else
								0
							END As piutang_awal
							,A.no_ktp , A.alamat_rumah_sekarang, A.hp
							, COALESCE(A.email_costumer,'') AS email_costumer
							, A.ket_costumer, A.kode_kantor
							,COALESCE(SUM(C.nominal),0)  AS BAYAR_PIUTANG
							,
							ABS(
								( (COALESCE(D.nominal_transaksi,0)) + (COALESCE(D.donasi,0)) + (COALESCE(D.biaya,0)) )
								-
								( (COALESCE(D.bayar_detail,0)) + (COALESCE(D.diskon,0)) + (COALESCE(D.nominal_retur,0)) )
							)
							AS SISA_PIUTANG_TR
							-- ,COALESCE(BB.TABUNGAN,0) AS TABUNGAN,COALESCE(BB.SISA_TABUNGAN,0) AS SISA_TABUNGAN
						FROM tb_costumer A
						Left Join
						(
							SELECT id_costumer,kode_kantor,nominal,isPiutang
							From tb_uang_masuk
							Where isPiutang = 1
							AND CONCAT(left(COALESCE(tgl_uang_masuk,''),10),' ',right(tgl_ins,8)) <  CONCAT('".$tgl_sampai."',' ','23:59:59')
							AND CONCAT(left(COALESCE(tgl_uang_masuk,''),10),' ',right(tgl_ins,8)) >  '".$tgl_backup."'
						) AS C ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor AND C.isPiutang = 1
						Left Join
						(
							SELECT A.id_costumer,A.kode_kantor,A.kode_kantor_costumer
								,SUM(COALESCE(G.BAYAR,0)) AS bayar_detail -- SUM(A.bayar_detail) AS bayar_detail
								,SUM(COALESCE(C.TR,0)) AS nominal_transaksi
								,SUM(A.donasi) AS donasi
								,SUM(A.biaya) AS biaya
								,SUM(A.diskon) AS diskon
								,SUM(A.nominal_retur) + SUM(COALESCE(F.NOMINAL_DET_RTR,0)) AS nominal_retur
							From tb_h_penjualan AS A
							Inner Join
							(
								SELECT id_h_penjualan, kode_kantor, SUM(jumlah * (harga-diskon)) AS TR, SUM(jumlah * harga_dasar_ori) AS MDL
								From tb_d_penjualan
								GROUP BY  id_h_penjualan, kode_kantor
							) AS C ON A.id_h_penjualan = C.id_h_penjualan AND A.kode_kantor = C.kode_kantor
							Left Join
							(
								SELECT no_faktur_penjualan,SUM(NOMINAL_DET_RTR) AS NOMINAL_DET_RTR, kode_kantor
								From
								(
									SELECT A.id_h_penjualan,A.no_faktur_penjualan,(harga * jumlah) AS NOMINAL_DET_RTR,A.kode_kantor
										,B.id_produk
										,CASE
											WHEN B.status_konversi = '*' THEN
												B.jumlah * B.konversi
											Else
												B.jumlah / B.konversi
											END As jumlah_konversi
									FROM tb_h_retur AS A
									LEFT JOIN tb_d_retur AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor WHERE A.status_penjualan = 'RETUR-PENJUALAN'
								) AS BB GROUP BY no_faktur_penjualan,kode_kantor
							) AS F ON A.no_faktur = F.no_faktur_penjualan AND A.kode_kantor = F.kode_kantor
							Left Join
							(
								SELECT id_h_penjualan,kode_kantor,SUM(nominal) AS BAYAR
								From tb_d_penjualan_bayar
								GROUP BY id_h_penjualan,kode_kantor
							) AS G ON A.id_h_penjualan = G.id_h_penjualan AND A.kode_kantor = G.kode_kantor
							-- WHERE A.sts_penjualan NOT IN ('PENDING','DIBATALKAN')
							WHERE A.sts_penjualan = 'SELESAI'
							AND LEFT(A.ket_penjualan,7) <>'DIHAPUS' 
							AND COALESCE(C.TR,0) > 0
							AND (COALESCE(G.BAYAR,0)) < ((COALESCE(C.TR,0) + A.donasi + A.biaya) - (A.diskon + A.nominal_retur))
							AND CONCAT(left(COALESCE(A.tgl_h_penjualan,''),10),' ',right(A.tgl_ins,8)) <  CONCAT('".$tgl_sampai."',' ','23:59:59')
							AND CONCAT(left(COALESCE(A.tgl_h_penjualan,''),10),' ',right(A.tgl_ins,8)) >  '".$tgl_backup."'
							GROUP BY A.id_costumer,A.kode_kantor,A.kode_kantor_costumer
						) AS D ON A.id_costumer = D.id_costumer AND A.kode_kantor = D.kode_kantor_costumer AND (D.bayar_detail) < ((D.nominal_transaksi + D.donasi + D.biaya) - (D.diskon + D.nominal_retur))
						
						/*
						Left Join
						(

							SELECT
								(A.id_costumer) As id_costumer
								,(A.kode_kantor) AS kode_kantor
								,SUM(A.nominal) AS TABUNGAN
								,(COALESCE(B.BAYAR_PIUTANG_TABUNGAN,0)) AS BAYAR_PIUTANG_TABUNGAN
								,(COALESCE(C.BAYAR_TR_DENG_TABUNGAN,0)) AS BAYAR_TR_DENG_TABUNGAN
								,(COALESCE(D.BAYAR_PIUTANG_TABUNGAN_KELUAR,0)) AS BAYAR_PIUTANG_TABUNGAN_KELUAR
								,SUM(A.nominal) - ((COALESCE(B.BAYAR_PIUTANG_TABUNGAN,0)) + (COALESCE(C.BAYAR_TR_DENG_TABUNGAN,0)) + (COALESCE(D.BAYAR_PIUTANG_TABUNGAN_KELUAR,0))) AS SISA_TABUNGAN
							FROM tb_uang_masuk AS A
							Left Join
							(
								-- PEMBAYARAN PIUTANG DARI TABUNGAN
								SELECT id_costumer,kode_kantor,SUM(nominal) AS BAYAR_PIUTANG_TABUNGAN
								From tb_uang_masuk
								Where isTabungan = 1 And isPiutang = 1
								AND CONCAT(left(COALESCE(tgl_uang_masuk,''),10),' ',right(tgl_ins,8)) <  CONCAT('".$tgl_sampai."',' ','23:59:59')
								AND CONCAT(left(COALESCE(tgl_uang_masuk,''),10),' ',right(tgl_ins,8)) >  '".$tgl_backup."'
								GROUP BY id_costumer,kode_kantor
								-- PEMBAYARAN PIUTANG DARI TABUNGAN
							) AS B ON A.id_costumer = B.id_costumer AND A.kode_kantor = B.kode_kantor
							Left Join
							(
								-- PEMBAYARAN PENJUALAN DENGAN TABUNGAN
								SELECT id_costumer,kode_kantor,SUM(nominal) AS BAYAR_TR_DENG_TABUNGAN
								From tb_d_penjualan_bayar
								Where isTabungan = 1
								AND CONCAT(left(COALESCE(tgl_bayar,''),10),' ',right(tgl_ins,8)) <  CONCAT('".$tgl_sampai."',' ','23:59:59')
								AND CONCAT(left(COALESCE(tgl_bayar,''),10),' ',right(tgl_ins,8)) >  '".$tgl_backup."'
								GROUP BY id_costumer,kode_kantor
								-- PEMBAYARAN PENJUALAN DENGAN TABUNGAN
							) AS C ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
							Left Join
							(
								-- PEMBAYARAN PIUTANG DARI TABUNGAN UANG KELUAR
								SELECT id_costumer,kode_kantor,SUM(nominal) AS BAYAR_PIUTANG_TABUNGAN_KELUAR
								From tb_uang_keluar
								Where isTabungan = 1
								AND CONCAT(left(COALESCE(tgl_dikeluarkan,''),10),' ',right(tgl_ins,8)) <  CONCAT('".$tgl_sampai."',' ','23:59:59')
								AND CONCAT(left(COALESCE(tgl_dikeluarkan,''),10),' ',right(tgl_ins,8)) >  '".$tgl_backup."'
								GROUP BY id_costumer,kode_kantor
								-- PEMBAYARAN PIUTANG DARI TABUNGAN UANG KELUAR
							) AS D ON A.id_costumer = D.id_costumer AND A.kode_kantor = D.kode_kantor
							Where A.isTabungan = 1 And A.isPiutang = 0
							AND CONCAT(left(COALESCE(A.tgl_uang_masuk,''),10),' ',right(A.tgl_ins,8)) <  CONCAT('".$tgl_sampai."',' ','23:59:59')
							AND CONCAT(left(COALESCE(A.tgl_uang_masuk,''),10),' ',right(A.tgl_ins,8)) >  '".$tgl_backup."'
							Group By
							(A.id_costumer)
							,(A.kode_kantor)
							,(COALESCE(B.BAYAR_PIUTANG_TABUNGAN,0))
							,(COALESCE(C.BAYAR_TR_DENG_TABUNGAN,0))
							,(COALESCE(D.BAYAR_PIUTANG_TABUNGAN_KELUAR,0))
						) AS BB ON A.id_costumer = BB.id_costumer AND A.kode_kantor = BB.kode_kantor
						*/
						WHERE (A.no_costumer LIKE '%%' OR  A.nama_lengkap LIKE '%%')
						AND A.kode_kantor = '".$kode_kantor."'
						-- AND CONCAT(left(COALESCE(A.tgl_uang_masuk,''),10),' ',right(A.tgl_ins,8)) <  CONCAT('".$tgl_sampai."',' ','23:59:59')
						AND A.tgl_ins <  CONCAT('".$tgl_sampai."',' ','23:59:59')
						AND A.tgl_ins >  '".$tgl_backup."'
						Group By
							A.id_costumer , A.id_kat_costumer
							-- ,COALESCE(B.nama_kat_costumer,'')
							,A.no_costumer,A.nama_lengkap,A.piutang_awal
							,A.no_ktp , A.alamat_rumah_sekarang, A.hp
							, COALESCE(A.email_costumer,'')
							, A.ket_costumer
							-- ,COALESCE(BB.SISA_TABUNGAN,0)
						-- ORDER BY A.nama_lengkap ASC LIMIT 0,50
					) AS AA
					Union All
					SELECT
						tr_omset
						,tr_modal
						,tr_laba_kotor
						,tr_pemasukan
						,tr_pengeluaran
						,tr_laba_bersih
					FROM tb_backup_all WHERE kode_kantor = '".$kode_kantor."' AND back_id_kat = 'NERACAPIUTANG' AND back_time = '".$tgl_backup."'
				) AS AAA
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
	
	
		function get_hutang_awal
		(
			$kode_kantor
			,$tgl_sampai
		)
		{
			//NGAMBIL DARI HUTANG AWAL SETTING SUPPLIER
			
			$query = "
			
				SELECT SUM(hutang_awal) - SUM(BAYAR_HUTANG) AS SISA_HUTANG_AWAL
				From
				(
					SELECT A.id_supplier,A.id_kat_supplier,A.kode_supplier,A.no_supplier,A.nama_supplier,A.pemilik_supplier,A.situ
					,A.siup,A.bidang,A.ket_supplier,A.avatar,A.avatar_url,A.email,A.tlp,A.alamat,A.limit_budget,A.allow_budget,A.bank,A.norek,A.hutang_awal
						, COALESCE(SUM(D.nominal),0) AS BAYAR_HUTANG

					FROM tb_supplier AS A
					Left Join
					(
						SELECT id_supplier,kode_kantor,SUM(nominal) AS nominal
						From tb_uang_keluar
						WHERE CONCAT(left(COALESCE(tgl_dikeluarkan,''),10),' ',right(tgl_ins,8)) <  CONCAT('".$tgl_sampai."',' ','23:59:59')
						GROUP BY id_supplier,kode_kantor
					) AS D ON A.id_supplier = D.id_supplier AND A.kode_kantor = D.kode_kantor

					WHERE (A.kode_supplier LIKE '%%' OR A.nama_supplier LIKE '%%') AND A.kode_kantor = '".$kode_kantor."'
					
					AND CONCAT(left(COALESCE(A.tgl_hutang_awal,''),10),' ',right(A.tgl_ins,8)) <  CONCAT('".$tgl_sampai."',' ','23:59:59')
					AND CONCAT(left(COALESCE(A.tgl_hutang_awal,''),10),' ',right(A.tgl_ins,8)) >  ''
					Group By
					A.id_supplier , A.id_kat_supplier, A.kode_supplier, A.no_supplier, A.nama_supplier, A.pemilik_supplier, A.situ
					,A.siup,A.bidang,A.ket_supplier,A.avatar,A.avatar_url,A.email,A.tlp,A.alamat,A.limit_budget,A.allow_budget,A.bank,A.norek,A.hutang_awal
				) AS AA
			";
			
			//NGAMBIL DARI HUTANG AWAL SETTING SUPPLIER
			
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
		
		
		function get_hutang_awal_2
		(
			$kode_kantor
			,$tgl_sampai
		)
		{
			
			$query = "	
						SELECT * 
						FROM tb_uang_masuk AS A LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun WHERE A.kode_kantor = '".$kode_kantor."' AND DATE(A.tgl_uang_masuk) <= '".$tgl_sampai."' AND A.isAwal = 'YA' AND B.target = 'HUTANG-AWAL' 
						ORDER BY A.tgl_uang_masuk DESC, A.tgl_ins DESC LIMIT 0,1
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
	
		
		
		function get_hutang_tr
		(
			$kode_kantor
			,$tgl_awal
			,$nominal_awal
			,$tgl_sampai
			,$tgl_backup
		)
		{
			/*
			$query = "
			
				SELECT SUM(HUTANG) AS HUTANG FROM
				(
				SELECT ABS(SUM(SISA)) AS HUTANG FROM
				(
				SELECT A.id_h_pembelian, A.no_h_pembelian,A.id_supplier,CASE WHEN A.tgl_jatuh_tempo = '0000-00-00' THEN '1900-01-01' ELSE COALESCE(A.tgl_jatuh_tempo,'1900-01-01') END AS tgl_jatuh_tempo_ori
				,TIMESTAMPDIFF(DAY, DATE(NOW()), CASE WHEN A.tgl_jatuh_tempo = '0000-00-00' THEN '1900-01-01' ELSE COALESCE(A.tgl_jatuh_tempo,'1900-01-01') END ) AS SISA_TEMPO
						,COALESCE(D.BAYAR,0) AS NOMINAL_BAYAR 
						,COALESCE(B.GRANDTOTAL,0) AS GRANDTOTAL
						,( (COALESCE(D.BAYAR,0) + A.pengurang +  (A.nominal_retur + COALESCE(E.NOMINAL_RET,0) ) ) -  (COALESCE(B.GRANDTOTAL,0) + A.biaya_tambahan)  ) AS SISA
						,CASE WHEN ( ( (COALESCE(D.BAYAR,0) + A.pengurang +  (A.nominal_retur + COALESCE(E.NOMINAL_RET,0) ) ) -  (COALESCE(B.GRANDTOTAL,0) + A.biaya_tambahan)  ) <= 1 ) OR (A.sts_pembelian = 'LUNAS') THEN
							'LUNAS'
						Else
							'BELUM LUNAS'
						END As STATUS_LUNAS
						,COALESCE(B.JUM,0) AS JUM
						,COALESCE(B.JUMACC,0) AS JUMACC
						,COALESCE(B.jumlah_beli,0) AS jumlah_beli
						,A.tgl_h_pembelian,A.tgl_ins
						,A.biaya_tambahan,A.pengurang,(A.nominal_retur + COALESCE(E.NOMINAL_RET,0) ) AS nominal_retur
					FROM tb_h_pembelian AS A
				Left Join
				(
					SELECT
						id_h_pembelian
						,SUM(jumlah_beli) AS jumlah_beli
						,SUM(GRANDTOTAL) AS GRANDTOTAL
						,SUM(JUM) AS JUM
						,SUM(JUMACC) AS JUMACC
					From
					(
						SELECT A.id_h_pembelian,A.id_produk
							,CASE
								WHEN A.status_konversi = '*' THEN
									SUM(A.jumlah * A.konversi)
								Else
									SUM(A.jumlah / A.konversi)
								END As jumlah_beli
							,CASE
							WHEN A.optr_diskon = '%' THEN
								SUM(A.jumlah * (A.harga - ((A.harga / 100) * A.diskon)))
							Else
								SUM(A.jumlah * (A.harga - A.diskon))
							End
							AS GRANDTOTAL
							,COUNT(A.id_h_pembelian) AS JUM
							,SUM(A.acc) AS JUMACC
						From tb_d_pembelian AS A
						WHERE A.kode_kantor = '".$kode_kantor."'
						GROUP BY A.id_h_pembelian,A.id_produk,A.status_konversi
						,A.optr_diskon
					) AS AA
					GROUP BY id_h_pembelian
				) AS B ON A.id_h_pembelian = B.id_h_pembelian
				Left Join
				(
					SELECT id_h_pembelian,kode_kantor,SUM(nominal) AS BAYAR
					From tb_d_pembelian_bayar
					WHERE kode_kantor = '".$kode_kantor."'
					GROUP BY id_h_pembelian,kode_kantor
				) AS D ON A.id_h_pembelian = D.id_h_pembelian AND A.kode_kantor = D.kode_kantor
				LEFT JOIN
				(
					SELECT A.id_h_penjualan,A.no_faktur,A.no_faktur_penjualan,A.kode_kantor,COALESCE(B.NOMINAL_RET,0) AS NOMINAL_RET
					FROM tb_h_retur AS A
					Left Join
					(
						SELECT id_h_penjualan,kode_kantor,SUM(jumlah * harga) AS NOMINAL_RET
						From tb_d_retur
						GROUP BY id_h_penjualan,kode_kantor
					) AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
					WHERE A.kode_kantor = '".$kode_kantor."'
				) AS E ON A.no_h_pembelian = E.no_faktur_penjualan AND A.kode_kantor = E.kode_kantor
					WHERE A.kode_kantor = '".$kode_kantor."' 
					AND CONCAT(left(COALESCE(A.tgl_h_pembelian,''),10),' ',right(A.tgl_ins,8)) <  CONCAT('".$tgl_sampai."',' ','23:59:59')
					AND CONCAT(left(COALESCE(A.tgl_h_pembelian,''),10),' ',right(A.tgl_ins,8)) >  '".$tgl_backup."'
					AND A.sts_pembelian = 'SELESAI'
				) AS AA
				Union All
				SELECT back_nominal AS HUTANG FROM tb_backup_all WHERE back_id_kat = 'NERACAHUTANG' AND back_time = '".$tgl_backup."' AND kode_kantor = '".$kode_kantor."'
				) AS AA


			";
			*/
			
			$query = 
			"
				SELECT ".$nominal_awal." + SUM(SISA) AS HUTANG
				FROM
				(
					SELECT
						A.kode_supplier
						,A.nama_supplier
						,A.hari_tempo
						,DATE_ADD(A.tgl_h_pembelian , INTERVAL A.hari_tempo DAY) AS tgl_tempo
						,SUM(A.SUBTOTAL - A.BAYAR) AS SISA
					FROM
					(
						SELECT 
							A.id_h_pembelian, A.id_supplier,A.no_h_pembelian,A.ket_h_pembelian,A.nominal_transaksi,A.tgl_h_pembelian,DATE_FORMAT(A.tgl_h_pembelian, '%Y-%m-%d') AS DFORMAT  
								,COALESCE(C.kode_supplier,'') AS kode_supplier
								,COALESCE(C.nama_supplier,'') AS nama_supplier
								,COALESCE(C.hari_tempo,0) AS hari_tempo
								,COALESCE(C.alamat,'') AS alamat
								,COALESCE(B.JUM,0) AS JUM
								,COALESCE(B.JUMITEM,0) AS JUMITEM
								,COALESCE(B.JUMACC,0) AS JUMACC
								,COALESCE(B.SUBTOTAL,0) AS SUBTOTAL
								,SUM(COALESCE(D.diterima_satuan_beli,0)) AS DITERIMA
								,SUM(COALESCE(E.nominal,0)) AS BAYAR
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
						LEFT JOIN tb_d_pembelian_bayar AS E ON A.kode_kantor = E.kode_kantor AND A.id_h_pembelian = E.id_h_pembelian
						LEFT JOIN tb_supplier AS C ON A.id_supplier = C.id_supplier AND C.kode_kantor = '".$kode_kantor."'
						WHERE 
						A.kode_kantor = '".$kode_kantor."'
						AND A.sts_pembelian = 'SELESAI'
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
					) AS A

					WHERE 
					A.PERSEN >= 100
					AND DATE_ADD(A.tgl_h_pembelian , INTERVAL A.hari_tempo DAY) > DATE('".$tgl_awal."') 
					AND DATE_ADD(A.tgl_h_pembelian , INTERVAL A.hari_tempo DAY) <= DATE('".$tgl_sampai."')
					GROUP BY 
						A.kode_supplier
						,A.nama_supplier
						,A.hari_tempo
						,DATE_ADD(A.tgl_h_pembelian , INTERVAL A.hari_tempo DAY)
					HAVING SUM(A.SUBTOTAL - A.BAYAR) > 0
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
		
		function get_hutang_tr_by_inv
		(
			$kode_kantor
			,$tgl_awal
			,$nominal_awal
			,$tgl_sampai
			,$tgl_backup
		)
		{
			
			$query = 
			"
				SELECT ".$nominal_awal." + SUM(SISA) AS HUTANG
				FROM
				(
					SELECT
						A.kode_supplier
						,A.nama_supplier
						,A.hari_tempo
						,DATE_ADD(A.tgl_h_pembelian , INTERVAL A.hari_tempo DAY) AS tgl_tempo
						,ROUND(SUM(A.SUBTOTAL - A.BAYAR),0) AS SISA
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
							A.tgl_terima AS tgl_h_pembelian,
							A.ket_h_penerimaan,
							A.tgl_ins,
							A.tgl_updt,
							A.user_updt,
							A.user_ins,
							A.kode_kantor,
							COALESCE(C.JUMLAH,0) AS JUMLAH,
							COALESCE(C.NOMINAL,0) AS SUBTOTAL,
							COALESCE(F.BAYAR,0) AS BAYAR,
							COALESCE(C.NOMINAL,0) - COALESCE(F.BAYAR,0) AS SISA,
							COALESCE(E.kode_supplier,'') AS kode_supplier,
							COALESCE(E.nama_supplier,'') AS nama_supplier,
							COALESCE(E.hari_tempo,'') AS hari_tempo
							
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
						
						WHERE A.kode_kantor = '".$kode_kantor."'
						/*
						AND (
								A.no_surat_jalan LIKE CONCAT('%', V_cari, '%')
								OR A.nama_pengirim LIKE CONCAT('%', V_cari, '%')
								OR A.diterima_oleh LIKE CONCAT('%', V_cari, '%')
								OR A.diterima_oleh LIKE CONCAT('%', V_cari, '%')
								OR COALESCE(E.kode_supplier,'') LIKE CONCAT('%', V_cari, '%')
								OR COALESCE(E.nama_supplier,'') LIKE CONCAT('%', V_cari, '%')
							)
						*/
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
							COALESCE(C.JUMLAH,0),
							COALESCE(C.NOMINAL,0),
							COALESCE(F.BAYAR,0),
							COALESCE(E.kode_supplier,''),
							COALESCE(E.nama_supplier,''),
							COALESCE(E.hari_tempo,'')
					) AS A

					WHERE 
					DATE_ADD(A.tgl_h_pembelian , INTERVAL A.hari_tempo DAY) > '2021-07-01' 
					
					-- AND 
					-- DATE_ADD(A.tgl_h_pembelian , INTERVAL A.hari_tempo DAY) > DATE('".$tgl_awal."') 
					AND DATE_ADD(A.tgl_h_pembelian , INTERVAL A.hari_tempo DAY) > (SELECT MAX(tgl_uang_masuk) AS tgl_uang_masuk FROM tb_uang_masuk WHERE kode_kantor = '".$kode_kantor."' AND isAwal = 'YA')
					AND DATE_ADD(A.tgl_h_pembelian , INTERVAL A.hari_tempo DAY) <= DATE('".$tgl_sampai."')
					GROUP BY 
						A.kode_supplier
						,A.nama_supplier
						,A.hari_tempo
						,DATE_ADD(A.tgl_h_pembelian , INTERVAL A.hari_tempo DAY)
					HAVING SUM(A.SUBTOTAL - A.BAYAR) > 0
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
		
		function sum_assets_by_aktegori($kode_kantor,$kategori)
		{
			$query = "
						SELECT COALESCE(SUM(nominal),0) AS ASSET
						FROM
						(
							SELECT
								A.*
								,COALESCE(B.nama_kat_assets,'') AS nama_kat_assets
								,COALESCE(C.nama_bank,'') AS nama_bank
								,COALESCE(C.norek,'') AS norek
								,TIMESTAMPDIFF(MONTH, A.tgl_beli, NOW()) AS selisih_bulan
								,ROUND(((A.nominal/100) * A.penyusutan) * (TIMESTAMPDIFF(MONTH, A.tgl_beli, NOW())),0) AS NOMINAL_PENYUSUTAN
								,A.nominal - (ROUND(((A.nominal/100) * A.penyusutan) * (TIMESTAMPDIFF(MONTH, A.tgl_beli, NOW())),0)) AS SETELAH_PENYUSUTAN
							FROM tb_d_assets AS A 
							LEFT JOIN tb_kat_assets AS B ON A.id_kat_assets = B.id_kat_assets AND A.kode_kantor = B.kode_kantor
							LEFT JOIN tb_bank AS C ON A.id_bank = C.id_bank AND A.kode_kantor = B.kode_kantor
							WHERE A.kode_kantor = '".$kode_kantor."' AND COALESCE(B.nama_kat_assets,'') = '".$kategori."'
							AND A.isUang = 0
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
	
	}
?>