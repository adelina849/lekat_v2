<?php
	class M_gl_acc_buku_besar extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_acc_buku_besar_old_20210310($kode_kantor,$dari,$sampai)
		{
			$query = 
				"
				SELECT
					AA.kode_kantor
					,AA.kode_akun
					,AA.nama_kode_akun
					,AA.id_bank
					,COALESCE(BB.nama_bank,'') AS nama_bank
					,COALESCE(BB.norek,'') AS norek
					,AA.tgl_uang_masuk
					,AA.tgl_ins
					,AA.no_ref
					,AA.nama_ref
					,AA.nominal
				FROM
				(
					SELECT
						A.kode_kantor
						,COALESCE(B.kode_akun,'') AS kode_akun
						,COALESCE(B.nama_kode_akun,'') AS nama_kode_akun
						,A.id_bank
						,A.tgl_uang_masuk
						,A.tgl_ins
						,A.no_bukti AS no_ref
						,A.nama_uang_masuk AS nama_ref
						,A.nominal
					FROM tb_uang_masuk AS A
					LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_uang_masuk BETWEEN '".$dari."' AND '".$sampai."'
					-- ORDER BY A.tgl_ins DESC;
					UNION ALL
					SELECT
						A.kode_kantor
						,COALESCE(B.kode_akun,'') AS kode_akun
						,COALESCE(B.nama_kode_akun,'') AS nama_kode_akun
						,A.id_bank
						,A.tgl_diterima
						,A.tgl_ins
						,A.no_uang_keluar AS no_ref
						,A.nama_uang_keluar AS nama_ref
						,A.nominal
					FROM tb_uang_keluar AS A
					LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_keluar = B.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_diterima BETWEEN '".$dari."' AND '".$sampai."'
					-- ORDER BY A.tgl_ins DESC;
					UNION ALL
					
					
					SELECT
						A.kode_kantor
						,COALESCE(C.kode_akun,'') AS kode_akun
						,COALESCE(C.nama_kode_akun,'') AS nama_kode_akun
						,A.id_bank
						,COALESCE(DATE(B.tgl_h_penjualan),'1900-01-01') AS nama_kode_akun
						,A.tgl_ins
						,COALESCE(B.no_faktur,'') AS no_ref
						,CONCAT('Penjualan dari ', COALESCE(B.nama_costumer,'')) AS nama_ref
						,A.nominal
					FROM tb_d_penjualan_bayar AS A
					INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
					LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND C.target = 'PENJUALAN'
					WHERE B.sts_penjualan IN ('SELESAI','PEMBAYARAN')
					AND A.kode_kantor = '".$kode_kantor."' AND COALESCE(DATE(B.tgl_h_penjualan),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
					
					
					
					UNION ALL
					SELECT
						A.kode_kantor
						,COALESCE(C.kode_akun,'') AS kode_akun
						,COALESCE(C.nama_kode_akun,'') AS nama_kode_akun
						,A.id_bank
						,COALESCE(DATE(B.tgl_h_pembelian),'1900-01-01') AS nama_kode_akun
						,A.tgl_ins
						,COALESCE(B.no_h_pembelian,'') AS no_ref
						,CONCAT('Pembayaran PO Ke ', COALESCE(D.nama_supplier,'')) AS nama_ref
						,A.nominal
					FROM tb_d_pembelian_bayar AS A
					INNER JOIN tb_h_pembelian AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_pembelian = B.id_h_pembelian
					LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND C.target = 'PEMBELIAN'
					LEFT JOIN tb_supplier AS D ON A.kode_kantor = D.kode_kantor AND B.id_supplier = D.id_supplier
					WHERE B.sts_pembelian = 'SELESAI'
					AND A.kode_kantor = '".$kode_kantor."' AND COALESCE(DATE(B.tgl_h_pembelian),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
				) AS AA
				LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
				WHERE AA.kode_akun <> ''
				ORDER BY kode_akun ASC,tgl_ins ASC
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
		
		function list_acc_buku_besar_2021_09_27($kode_kantor,$dari,$sampai,$cari)
		{
			
			$query="
				SELECT
					AA.kode_kantor
					,AA.id_kategori
					,COALESCE(CC.kode_akun,'') AS kode_akun
					,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
					,AA.id_bank
					,COALESCE(BB.nama_bank,'') AS nama_bank
					,COALESCE(BB.norek,'') AS norek
					,AA.tgl_uang_masuk
					,AA.tgl_ins
					,AA.no_ref
					,AA.nama_ref
					
					/*
					,AA.DEBET AS DEBET_ORI
					,AA.KREDIT AS KREDIT_ORI
					,CASE WHEN AA.DEBET = 0 THEN AA.KREDIT ELSE AA.DEBET END AS NOMINAL
					*/
					
					,AA.DEBET
					,AA.KREDIT
					/*
					,
						CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
							AA.DEBET
						ELSE
							AA.KREDIT
						END AS DEBET
					,
						CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
							AA.KREDIT
						ELSE
							AA.DEBET
						END AS KREDIT
					*/
					,CC.kat_akun_jurnal
				FROM
				(
					SELECT
						A.kode_kantor
						,A.id_kat_uang_masuk AS id_kategori
						,A.id_bank
						,A.tgl_uang_masuk
						,A.tgl_ins
						,A.no_bukti AS no_ref
						,A.ket_uang_masuk AS nama_ref
						,
						CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_masuk AS A
					WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_uang_masuk BETWEEN '".$dari."' AND '".$sampai."'

					UNION ALL
					
					
						SELECT  
							kode_kantor
							,id_kode_akun
							,'' AS id_bank
							,tgl_transaksi
							,tgl_ins
							,no_bukti
							,nama_akun
							,debit
							,kredit
					FROM tb_jurnal_umum
					WHERE kode_kantor = '".$kode_kantor."'
					AND DATE(tgl_transaksi) BETWEEN  DATE('".$dari."') AND DATE('".$sampai."')

					UNION ALL
					
					SELECT
						A.kode_kantor
						,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
						,A.id_bank
						,A.tgl_dikeluarkan
						,A.tgl_ins
						,A.no_uang_keluar AS no_ref
						,A.ket_uang_keluar AS nama_ref
						,
						CASE WHEN A.id_uang_keluar = A.id_induk_uang_keluar THEN
							0
						ELSE
							A.nominal
						END AS DEBET
						,
						CASE WHEN A.id_uang_keluar <> A.id_induk_uang_keluar THEN
							0
						ELSE
							A.nominal
						END AS KREDIT
					FROM tb_uang_keluar AS A
					WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_dikeluarkan BETWEEN '".$dari."' AND '".$sampai."'
					
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_in
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						,A.nominal AS DEBET
						, 0 AS KREDIT
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_out
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						, 0 AS DEBET
						,A.nominal AS KREDIT
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						
				) AS AA
				LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
				LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
				WHERE ( COALESCE(CC.kode_akun,'') <> '' AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
				ORDER BY COALESCE(CC.kode_akun,'') ASC,AA.tgl_uang_masuk ASC,tgl_ins ASC;
			
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
		
		function list_acc_buku_besar_20211011_No_penjualan($kode_kantor,$dari,$sampai,$cari)
		{
			
				
			$query="
				
			
				-- CURRENT TRANSAKSI
				SELECT
					AA.kode_kantor
					,AA.id_kategori
					
					,COALESCE(CC.kode_akun,'') AS kode_akun
					,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
					
					,AA.id_bank
					,COALESCE(BB.nama_bank,'') AS nama_bank
					,COALESCE(BB.norek,'') AS norek
					,AA.tgl_uang_masuk
					,AA.tgl_ins
					,AA.no_ref
					,AA.nama_ref
					-- ,AA.DEBET
					-- ,AA.KREDIT
					,
						-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
						
							CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								(AA.DEBET)
							ELSE
								(AA.KREDIT)
							END AS DEBET
					,	-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
							
							CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								(AA.KREDIT)
							ELSE
								(AA.DEBET)
							END AS KREDIT
				FROM
				(
					SELECT
						A.kode_kantor
						,A.id_kat_uang_masuk AS id_kategori
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						,A.id_bank
						,A.tgl_uang_masuk
						,A.tgl_ins
						,A.no_bukti AS no_ref
						,A.ket_uang_masuk AS nama_ref
						,
						CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_masuk AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_masuk2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."' 
					-- AND A.tgl_uang_masuk BETWEEN '".$dari."' AND '".$sampai."'  AND A.isAwal = 'TIDAK'
					AND A.tgl_uang_masuk >= '".$dari."' AND A.tgl_uang_masuk <= '".$sampai."'  
					AND A.isAwal = 'TIDAK'

					UNION ALL

					SELECT
						A.kode_kantor
						,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
						
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						
						,A.id_bank
						,A.tgl_dikeluarkan
						,A.tgl_ins
						,A.no_uang_keluar AS no_ref
						,A.ket_uang_keluar AS nama_ref
						-- ,A.nominal
						,
						-- CASE WHEN A.id_uang_keluar = A.id_induk_uang_keluar THEN
						CASE WHEN A.id_kat_uang_keluar = '' THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						-- CASE WHEN A.id_uang_keluar <> A.id_induk_uang_keluar THEN
						CASE WHEN A.id_kat_uang_keluar <> '' THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_keluar AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_keluar = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_keluar2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."'
					-- AND A.tgl_diterima BETWEEN '".$dari."' AND '".$sampai."'
					AND A.tgl_dikeluarkan >= '".$dari."' AND A.tgl_dikeluarkan <= '".$sampai."'
					
					UNION ALL
					
					
						SELECT  
								kode_kantor
								,id_kode_akun
								,'' AS id_bank
								,tgl_transaksi
								,tgl_ins
								,no_bukti
								,nama_akun
								,debit
								,kredit
						FROM tb_jurnal_umum
						WHERE kode_kantor = '".$kode_kantor."'
						AND DATE(tgl_transaksi) >=  DATE('".$dari."') AND DATE(tgl_transaksi) <= DATE('".$sampai."')
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_in
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						,A.nominal AS DEBET
						, 0 AS KREDIT
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' 
						-- AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <= '".$sampai."'
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_out
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						, 0 AS DEBET
						,A.nominal AS KREDIT
						
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' 
						-- AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <='".$sampai."'
						
				) AS AA
				LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
				LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
				WHERE ( COALESCE(CC.kode_akun,'') <> '' 
				AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
				-- WHERE COALESCE(CC.kode_akun,'') = '".$cari."'
				ORDER BY tgl_uang_masuk ASC,tgl_ins ASC,kode_akun DESC;
			
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
		
		function list_acc_buku_besar_kas($kode_kantor,$dari,$sampai,$cari)
		{
			
				
			$query="
				
			
				-- CURRENT TRANSAKSI
				SELECT
					AA.kode_kantor
					,AA.id_kategori
					
					,COALESCE(CC.kode_akun,'') AS kode_akun
					,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
					
					,AA.id_bank
					,COALESCE(BB.nama_bank,'') AS nama_bank
					,COALESCE(BB.norek,'') AS norek
					,AA.tgl_uang_masuk
					,AA.tgl_ins
					,AA.no_ref
					,AA.nama_ref
					,
						
						
							-- CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
							
							CASE 
							WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN') ) THEN
								(AA.DEBET)
							WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
								(AA.DEBET)
							ELSE
								(AA.KREDIT)
							END AS DEBET
					,	
							
							-- CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
							
							CASE 
							WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN') ) THEN
								(AA.KREDIT)
							WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
								(AA.KREDIT)
							ELSE
								(AA.DEBET)
							END AS KREDIT
				FROM
				(
					
				
					SELECT
						A.kode_kantor
						,A.id_kat_uang_masuk AS id_kategori
						,A.id_bank
						,A.tgl_uang_masuk
						,A.tgl_ins
						,A.no_bukti AS no_ref
						,A.ket_uang_masuk AS nama_ref
						,
						CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_masuk AS A
					WHERE A.kode_kantor = '".$kode_kantor."' 
					AND A.tgl_uang_masuk >= '".$dari."' AND A.tgl_uang_masuk <= '".$sampai."'  
					AND A.isAwal = 'TIDAK'

					UNION ALL

					SELECT
						A.kode_kantor
						,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
						,A.id_bank
						,A.tgl_dikeluarkan
						,A.tgl_ins
						,A.no_uang_keluar AS no_ref
						,A.ket_uang_keluar AS nama_ref
						,
						CASE WHEN A.id_kat_uang_keluar = '' THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						CASE WHEN A.id_kat_uang_keluar <> '' THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_keluar AS A
					WHERE A.kode_kantor = '".$kode_kantor."'
					AND A.tgl_dikeluarkan >= '".$dari."' AND A.tgl_dikeluarkan <= '".$sampai."'
					
					UNION ALL
					
					SELECT
						A.kode_kantor
						,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'KAS' GROUP BY id_kode_akun LIMIT 0,1) AS kategori
						,'' AS id_bank
						,A.tgl_pencairan
						,A.tgl_ins
						,COALESCE(B.no_faktur,'') AS no_faktur
						,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
						,A.nominal AS DEBET
						,0 AS KREDIT
					FROM tb_d_penjualan_bayar AS A
					INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
					WHERE A.kode_kantor = '".$kode_kantor."'
					AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
					AND A.nominal > 0
					AND A.id_bank = ''
					AND A.tgl_pencairan >= '".$dari."' AND A.tgl_pencairan <= '".$sampai."'
					
					UNION ALL
					
					SELECT
						A.kode_kantor
						,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'BANK' AND id_bank = C.id_bank GROUP BY id_kode_akun LIMIT 0,1) AS kategori
						,'' AS id_bank
						,A.tgl_pencairan
						,A.tgl_ins
						,COALESCE(B.no_faktur,'') AS no_faktur
						,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
						,A.nominal AS DEBET
						,0 AS KREDIT
					FROM tb_d_penjualan_bayar AS A
					INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
					LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
					WHERE A.kode_kantor = '".$kode_kantor."'
					AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
					AND A.nominal > 0
					AND A.id_bank <> ''
					AND A.tgl_pencairan >= '".$dari."' AND A.tgl_pencairan <= '".$sampai."'
					
					UNION ALL
					
					
						SELECT  
								kode_kantor
								,id_kode_akun
								,'' AS id_bank
								,tgl_transaksi
								,tgl_ins
								,no_bukti
								-- ,nama_akun
								,ket
								,debit
								,kredit
						FROM tb_jurnal_umum
						WHERE kode_kantor = '".$kode_kantor."'
						AND DATE(tgl_transaksi) >=  DATE('".$dari."') AND DATE(tgl_transaksi) <= DATE('".$sampai."')
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_in
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						,A.nominal AS DEBET
						, 0 AS KREDIT
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <= '".$sampai."'
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_out
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						, 0 AS DEBET
						,A.nominal AS KREDIT
						
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <='".$sampai."'
						
					UNION ALL
						SELECT 
							A.kode_kantor,
							(
								SELECT distinct id_kode_akun 
								FROM tb_h_pembelian AS AA
								LEFT JOIN tb_kode_akun AS BB 
									ON AA.kode_kantor = BB.kode_kantor 
									AND AA.id_supplier = BB.id_supplier
									AND BB.kat_akun_jurnal = 'UTANG'
								WHERE AA.id_h_pembelian = A.id_h_pembelian
								GROUP BY id_kode_akun
								LIMIT 0,1
							)
							AS id_kode_out,
							'' AS id_bank,
							DATE(A.tgl_terima) AS tgl_terima,
							A.tgl_ins,
							A.no_surat_jalan,
							CONCAT('Hutang Produk Invoice ',A.no_surat_jalan) AS keterangan,
							0 AS DEBIT,
							(COALESCE(C.NOMINAL,0) + A.biaya_kirim) AS KREDIT
							
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
						WHERE A.kode_kantor = '".$kode_kantor."'
						AND A.tgl_terima >= '".$dari."' AND A.tgl_terima <='".$sampai."'
						
				) AS AA
				LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
				LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
				WHERE ( COALESCE(CC.kode_akun,'') <> '' 
				AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
				ORDER BY kode_akun ASC,tgl_uang_masuk ASC,tgl_ins ASC
			
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
		
		function list_acc_buku_besar_kas_tambah_saldo_awal($kode_kantor,$dari,$sampai,$cari)
		{
			
				
			$query="
				SELECT * FROM
				(
					-- SALDO AWAL
					SELECT
						AA.kode_kantor,AA.id_kategori,AA.kode_akun,AA.nama_kode_akun
						-- ,AA.id_bank,AA.nama_bank,AA.norek
						,'' AS id_bank
						,'' AS nama_bank
						,'' AS norek
						,'' AS tgl_uang_masuk
						,'' AS tgl_ins
						,'SALDO AWAL' AS no_ref
						,'SALDO AWAL' AS nama_ref
						,SUM(DEBET) AS DEBET
						,SUM(KREDIT) AS KREDIT
					FROM
					(
						SELECT
							AA.kode_kantor
							,AA.id_kategori
							,COALESCE(CC.kode_akun,'') AS kode_akun
							,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
							,AA.id_bank
							,COALESCE(BB.nama_bank,'') AS nama_bank
							,COALESCE(BB.norek,'') AS norek
							,AA.tgl_uang_masuk
							,AA.tgl_ins
							,AA.no_ref
							,AA.nama_ref
							,
								
								
									-- CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
									
									CASE 
									WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN') ) THEN
										(AA.DEBET)
									WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
										(AA.DEBET)
									ELSE
										(AA.KREDIT)
									END AS DEBET
							,	
									
									-- CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
									
									CASE 
									WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN') ) THEN
										(AA.KREDIT)
									WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
										(AA.KREDIT)
									ELSE
										(AA.DEBET)
									END AS KREDIT
						FROM
						(
							
							-- UANG MASUK BUKAN SALDO AWAL SEBELUM CURRENT
								SELECT
									A.kode_kantor
									,A.id_kat_uang_masuk AS id_kategori
									,A.id_bank
									,A.tgl_uang_masuk
									,A.tgl_ins
									,A.no_bukti AS no_ref
									,A.ket_uang_masuk AS nama_ref
									,
									CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
										A.nominal
									ELSE
										0
									END AS DEBET
									,
									CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
										A.nominal
									ELSE
										0
									END AS KREDIT
								FROM tb_uang_masuk AS A
								WHERE A.kode_kantor = '".$kode_kantor."'
								AND A.tgl_uang_masuk > (
															SELECT '1900-01-01' AS tgl_uang_masuk
															UNION ALL
															SELECT distinct tgl_uang_masuk 
															FROM tb_uang_masuk 
																WHERE kode_kantor = A.kode_kantor 
																AND isAwal = 'YA' 
																AND tgl_uang_masuk < '".$dari."' 
																AND id_kat_uang_masuk = A.id_kat_uang_masuk
															GROUP BY tgl_uang_masuk 
															ORDER BY tgl_uang_masuk 
															DESC LIMIT 0,1
														)
								AND A.tgl_uang_masuk < '".$dari."'
								AND A.isAwal <> 'YA'
							-- UANG MASUK BUKAN SALDO AWAL SEBELUM CURRENT
							
							UNION ALL

							SELECT
								A.kode_kantor
								,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
								,A.id_bank
								,A.tgl_dikeluarkan
								,A.tgl_ins
								,A.no_uang_keluar AS no_ref
								,A.ket_uang_keluar AS nama_ref
								,
								CASE WHEN A.id_kat_uang_keluar = '' THEN
									A.nominal
								ELSE
									0
								END AS DEBET
								,
								CASE WHEN A.id_kat_uang_keluar <> '' THEN
									A.nominal
								ELSE
									0
								END AS KREDIT
							FROM tb_uang_keluar AS A
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND A.tgl_dikeluarkan > 
												(
													SELECT '1900-01-01' AS tgl_uang_masuk
													UNION ALL
													SELECT distinct tgl_uang_masuk 
													FROM tb_uang_masuk 
														WHERE kode_kantor = A.kode_kantor 
														AND isAwal = 'YA' 
														AND tgl_uang_masuk < '".$dari."' 
														AND id_kat_uang_masuk = (CASE WHEN A.id_kat_uang_keluar = '' THEN A.id_kat_uang_keluar2 ELSE A.id_kat_uang_keluar END)
													GROUP BY tgl_uang_masuk 
													ORDER BY tgl_uang_masuk 
													DESC LIMIT 0,1
												) 
								AND A.tgl_dikeluarkan < '".$dari."'
							
							UNION ALL
							
							SELECT
								A.kode_kantor
								,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'KAS' GROUP BY id_kode_akun LIMIT 0,1) AS kategori
								,'' AS id_bank
								,A.tgl_pencairan
								,A.tgl_ins
								,COALESCE(B.no_faktur,'') AS no_faktur
								,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
								,A.nominal AS DEBET
								,0 AS KREDIT
							FROM tb_d_penjualan_bayar AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
							AND A.nominal > 0
							AND A.id_bank = ''
							AND A.tgl_pencairan > 
												(
													SELECT '1900-01-01' AS tgl_uang_masuk
													UNION ALL
													SELECT distinct tgl_uang_masuk 
													FROM tb_uang_masuk 
														WHERE kode_kantor = A.kode_kantor 
														AND isAwal = 'YA' 
														AND tgl_uang_masuk < '".$dari."' 
														AND id_kat_uang_masuk = (SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'KAS' GROUP BY id_kode_akun LIMIT 0,1)
													GROUP BY tgl_uang_masuk 
													ORDER BY tgl_uang_masuk 
													DESC LIMIT 0,1
												) 
								AND A.tgl_pencairan < '".$dari."'
							
							UNION ALL
							
							SELECT
								A.kode_kantor
								,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'BANK' AND id_bank = C.id_bank GROUP BY id_kode_akun LIMIT 0,1) AS kategori
								,'' AS id_bank
								,A.tgl_pencairan
								,A.tgl_ins
								,COALESCE(B.no_faktur,'') AS no_faktur
								,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
								,A.nominal AS DEBET
								,0 AS KREDIT
							FROM tb_d_penjualan_bayar AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
							AND A.nominal > 0
							AND A.id_bank <> ''
							AND A.tgl_pencairan > 
													(
														SELECT '1900-01-01' AS tgl_uang_masuk
														UNION ALL
														SELECT distinct tgl_uang_masuk 
														FROM tb_uang_masuk 
															WHERE kode_kantor = A.kode_kantor 
															AND isAwal = 'YA' 
															AND tgl_uang_masuk < '".$dari."' 
															AND id_kat_uang_masuk = (SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'BANK' AND id_bank = C.id_bank GROUP BY id_kode_akun LIMIT 0,1) 
														GROUP BY tgl_uang_masuk 
														ORDER BY tgl_uang_masuk 
														DESC LIMIT 0,1
													) 
								AND A.tgl_pencairan < '".$dari."'
							
							UNION ALL
							
							
								SELECT  
										kode_kantor
										,id_kode_akun
										,'' AS id_bank
										,tgl_transaksi
										,tgl_ins
										,no_bukti
										-- ,nama_akun
										,ket
										,debit
										,kredit
								FROM tb_jurnal_umum AS A
								WHERE kode_kantor = '".$kode_kantor."'
								AND DATE(tgl_transaksi) > 
													(
														SELECT '1900-01-01' AS tgl_uang_masuk
														UNION ALL
														SELECT distinct tgl_uang_masuk 
														FROM tb_uang_masuk 
															WHERE kode_kantor = A.kode_kantor 
															AND isAwal = 'YA' 
															AND tgl_uang_masuk < '".$dari."' 
															AND id_kat_uang_masuk = A.id_kode_akun 
														GROUP BY tgl_uang_masuk 
														ORDER BY tgl_uang_masuk 
														DESC LIMIT 0,1
													) 
									AND DATE(tgl_transaksi) <  DATE('".$dari."')
								
							UNION ALL
							
							SELECT
								A.kode_kantor
								,A.id_kode_in
								,'' AS id_bank
								,A.tgl_transaksi
								,A.tgl_ins
								,A.no_bukti
								,A.ket_mutasi
								,A.nominal AS DEBET
								, 0 AS KREDIT
								FROM tb_mutasi_kas AS A
								WHERE A.kode_kantor = '".$kode_kantor."'
								AND A.tgl_transaksi > 
														(
															SELECT '1900-01-01' AS tgl_uang_masuk
															UNION ALL
															SELECT distinct tgl_uang_masuk 
															FROM tb_uang_masuk 
																WHERE kode_kantor = A.kode_kantor 
																AND isAwal = 'YA' 
																AND tgl_uang_masuk < '".$dari."' 
																AND id_kat_uang_masuk = A.id_kode_in 
															GROUP BY tgl_uang_masuk 
															ORDER BY tgl_uang_masuk 
															DESC LIMIT 0,1
														) 
									AND A.tgl_transaksi < '".$dari."'
								
							UNION ALL
							
							SELECT
								A.kode_kantor
								,A.id_kode_out
								,'' AS id_bank
								,A.tgl_transaksi
								,A.tgl_ins
								,A.no_bukti
								,A.ket_mutasi
								, 0 AS DEBET
								,A.nominal AS KREDIT
								
								FROM tb_mutasi_kas AS A
								WHERE A.kode_kantor = '".$kode_kantor."'
								AND A.tgl_transaksi > (
															SELECT '1900-01-01' AS tgl_uang_masuk
															UNION ALL
															SELECT distinct tgl_uang_masuk 
															FROM tb_uang_masuk 
																WHERE kode_kantor = A.kode_kantor 
																AND isAwal = 'YA' 
																AND tgl_uang_masuk < '".$dari."' 
																AND id_kat_uang_masuk = A.id_kode_out 
															GROUP BY tgl_uang_masuk 
															ORDER BY tgl_uang_masuk 
															DESC LIMIT 0,1
														)
									AND A.tgl_transaksi < '".$dari."'
								
							UNION ALL
								SELECT 
									A.kode_kantor,
									(
										SELECT distinct id_kode_akun 
										FROM tb_h_pembelian AS AA
										LEFT JOIN tb_kode_akun AS BB 
											ON AA.kode_kantor = BB.kode_kantor 
											AND AA.id_supplier = BB.id_supplier
											AND BB.kat_akun_jurnal = 'UTANG'
										WHERE AA.id_h_pembelian = A.id_h_pembelian
										GROUP BY id_kode_akun
										LIMIT 0,1
									)
									AS id_kode_out,
									'' AS id_bank,
									A.tgl_terima,
									A.tgl_ins,
									A.no_surat_jalan,
									CONCAT('Hutang Produk Invoice ',A.no_surat_jalan) AS keterangan,
									0 AS DEBIT,
									(COALESCE(C.NOMINAL,0) + A.biaya_kirim) AS KREDIT
									
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
								WHERE A.kode_kantor = '".$kode_kantor."'
								AND A.tgl_terima > (
														SELECT '1900-01-01' AS tgl_uang_masuk
														UNION ALL
														SELECT distinct tgl_uang_masuk 
														FROM tb_uang_masuk 
															WHERE kode_kantor = A.kode_kantor 
															AND isAwal = 'YA' 
															AND tgl_uang_masuk < '".$dari."' 
															AND id_kat_uang_masuk = 
																					(
																						SELECT distinct id_kode_akun 
																						FROM tb_h_pembelian AS AA
																						LEFT JOIN tb_kode_akun AS BB 
																							ON AA.kode_kantor = BB.kode_kantor 
																							AND AA.id_supplier = BB.id_supplier
																							AND BB.kat_akun_jurnal = 'UTANG'
																						WHERE AA.id_h_pembelian = A.id_h_pembelian
																						GROUP BY id_kode_akun
																						LIMIT 0,1
																					)
														GROUP BY tgl_uang_masuk 
														ORDER BY tgl_uang_masuk 
														DESC LIMIT 0,1
													) 
									AND A.tgl_terima < '".$dari."'
								
							UNION ALL
							-- SALDO AWAL INPUT UANG MASUK
								SELECT
									A.kode_kantor
									,A.id_kat_uang_masuk
									,'' AS id_bank
									,'' AS tgl_masuk
									,'' AS tgl_ins
									
									,COALESCE(B.kode_akun,'') AS kode_akun
									,COALESCE(B.nama_kode_akun,'') AS nama_kode_akun
									
									,CASE 
										WHEN ( (COALESCE(B.kat_akun_jurnal,'') = 'HARTA') OR (COALESCE(B.kat_akun_jurnal,'') = 'BEBAN') ) THEN
											nominal
										ELSE
											0
										END AS DEBET
									,CASE 
										WHEN ( (COALESCE(B.kat_akun_jurnal,'') = 'HARTA') OR (COALESCE(B.kat_akun_jurnal,'') = 'BEBAN') ) THEN
											0
										ELSE
											nominal
										END AS KREDIT
								FROM tb_uang_masuk AS A
								LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
								WHERE COALESCE(A.isAwal,'') = 'YA' 
								AND A.kode_kantor = '".$kode_kantor."'
								-- AND A.tgl_uang_masuk < '".$dari."'
								AND A.tgl_uang_masuk = (
													SELECT distinct tgl_uang_masuk 
													FROM tb_uang_masuk 
														WHERE kode_kantor = A.kode_kantor 
														AND isAwal = 'YA' 
														AND tgl_uang_masuk < '".$dari."' 
														AND id_kat_uang_masuk = A.id_kat_uang_masuk 
													GROUP BY tgl_uang_masuk 
													ORDER BY tgl_uang_masuk 
													DESC LIMIT 0,1
												)
							-- SALDO AWAL INPUT UANG MASUK
						) AS AA
						LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
						LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
						WHERE ( COALESCE(CC.kode_akun,'') <> '' AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
					) AS AA
					GROUP BY AA.kode_kantor,AA.id_kategori,AA.kode_akun,AA.nama_kode_akun
					-- SALSO AWAL
					
					UNION ALL
				
					-- CURRENT TRANSAKSI
					SELECT
						AA.kode_kantor
						,AA.id_kategori
						
						,COALESCE(CC.kode_akun,'') AS kode_akun
						,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
						
						,AA.id_bank
						,COALESCE(BB.nama_bank,'') AS nama_bank
						,COALESCE(BB.norek,'') AS norek
						,AA.tgl_uang_masuk
						,AA.tgl_ins
						,AA.no_ref
						,AA.nama_ref
						,
							
							
								-- CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								
								CASE 
								WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN') ) THEN
									(AA.DEBET)
								WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
									(AA.DEBET)
								ELSE
									(AA.KREDIT)
								END AS DEBET
						,	
								
								-- CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								
								CASE 
								WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN') ) THEN
									(AA.KREDIT)
								WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
									(AA.KREDIT)
								ELSE
									(AA.DEBET)
								END AS KREDIT
					FROM
					(
						
					
						SELECT
							A.kode_kantor
							,A.id_kat_uang_masuk AS id_kategori
							,A.id_bank
							,A.tgl_uang_masuk
							,A.tgl_ins
							,A.no_bukti AS no_ref
							,A.ket_uang_masuk AS nama_ref
							,
							CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
								A.nominal
							ELSE
								0
							END AS DEBET
							,
							CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
								A.nominal
							ELSE
								0
							END AS KREDIT
						FROM tb_uang_masuk AS A
						WHERE A.kode_kantor = '".$kode_kantor."' 
						AND A.tgl_uang_masuk >= '".$dari."' AND A.tgl_uang_masuk <= '".$sampai."'  
						-- AND A.isAwal = 'TIDAK'
						AND A.isAwal <> 'YA'

						UNION ALL

						SELECT
							A.kode_kantor
							,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
							,A.id_bank
							,A.tgl_dikeluarkan
							,A.tgl_ins
							,A.no_uang_keluar AS no_ref
							,A.ket_uang_keluar AS nama_ref
							,
							CASE WHEN A.id_kat_uang_keluar = '' THEN
								A.nominal
							ELSE
								0
							END AS DEBET
							,
							CASE WHEN A.id_kat_uang_keluar <> '' THEN
								A.nominal
							ELSE
								0
							END AS KREDIT
						FROM tb_uang_keluar AS A
						WHERE A.kode_kantor = '".$kode_kantor."'
						AND A.tgl_dikeluarkan >= '".$dari."' AND A.tgl_dikeluarkan <= '".$sampai."'
						
						UNION ALL
						
						SELECT
							A.kode_kantor
							,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'KAS' GROUP BY id_kode_akun LIMIT 0,1) AS kategori
							,'' AS id_bank
							,A.tgl_pencairan
							,A.tgl_ins
							,COALESCE(B.no_faktur,'') AS no_faktur
							,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
							,A.nominal AS DEBET
							,0 AS KREDIT
						FROM tb_d_penjualan_bayar AS A
						INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
						WHERE A.kode_kantor = '".$kode_kantor."'
						AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
						AND A.nominal > 0
						AND A.id_bank = ''
						AND A.tgl_pencairan >= '".$dari."' AND A.tgl_pencairan <= '".$sampai."'
						
						UNION ALL
						
						SELECT
							A.kode_kantor
							,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'BANK' AND id_bank = C.id_bank GROUP BY id_kode_akun LIMIT 0,1) AS kategori
							,'' AS id_bank
							,A.tgl_pencairan
							,A.tgl_ins
							,COALESCE(B.no_faktur,'') AS no_faktur
							,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
							,A.nominal AS DEBET
							,0 AS KREDIT
						FROM tb_d_penjualan_bayar AS A
						INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
						LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
						WHERE A.kode_kantor = '".$kode_kantor."'
						AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
						AND A.nominal > 0
						AND A.id_bank <> ''
						AND A.tgl_pencairan >= '".$dari."' AND A.tgl_pencairan <= '".$sampai."'
						
						UNION ALL
						
						
							SELECT  
									kode_kantor
									,id_kode_akun
									,'' AS id_bank
									,tgl_transaksi
									,tgl_ins
									,no_bukti
									-- ,nama_akun
									,ket
									,debit
									,kredit
							FROM tb_jurnal_umum
							WHERE kode_kantor = '".$kode_kantor."'
							AND DATE(tgl_transaksi) >=  DATE('".$dari."') AND DATE(tgl_transaksi) <= DATE('".$sampai."')
							
						UNION ALL
						
						SELECT
							A.kode_kantor
							,A.id_kode_in
							,'' AS id_bank
							,A.tgl_transaksi
							,A.tgl_ins
							,A.no_bukti
							,A.ket_mutasi
							,A.nominal AS DEBET
							, 0 AS KREDIT
							FROM tb_mutasi_kas AS A
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <= '".$sampai."'
							
						UNION ALL
						
						SELECT
							A.kode_kantor
							,A.id_kode_out
							,'' AS id_bank
							,A.tgl_transaksi
							,A.tgl_ins
							,A.no_bukti
							,A.ket_mutasi
							, 0 AS DEBET
							,A.nominal AS KREDIT
							
							FROM tb_mutasi_kas AS A
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <='".$sampai."'
							
						UNION ALL
							SELECT 
								A.kode_kantor,
								(
									SELECT distinct id_kode_akun 
									FROM tb_h_pembelian AS AA
									LEFT JOIN tb_kode_akun AS BB 
										ON AA.kode_kantor = BB.kode_kantor 
										AND AA.id_supplier = BB.id_supplier
										AND BB.kat_akun_jurnal = 'UTANG'
									WHERE AA.id_h_pembelian = A.id_h_pembelian
									GROUP BY id_kode_akun
									LIMIT 0,1
								)
								AS id_kode_out,
								'' AS id_bank,
								DATE(A.tgl_terima) AS tgl_terima,
								A.tgl_ins,
								A.no_surat_jalan,
								CONCAT('Hutang Produk Invoice ',A.no_surat_jalan) AS keterangan,
								0 AS DEBIT,
								(COALESCE(C.NOMINAL,0) + A.biaya_kirim) AS KREDIT
								
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
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND A.tgl_terima >= '".$dari."' AND A.tgl_terima <='".$sampai."'
							
					) AS AA
					LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
					LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
					WHERE ( COALESCE(CC.kode_akun,'') <> ''  AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
				) AS AA
				WHERE 
					CASE WHEN COALESCE(AA.nama_ref,'') = 'SALDO AWAL' THEN
						(AA.DEBET + AA.KREDIT)
					ELSE
						1
					END > 0
					
				ORDER BY AA.kode_akun ASC,AA.tgl_uang_masuk ASC,AA.tgl_ins ASC
			
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
		
		
		function saldo_acc_buku_besar_kas_tambah_saldo_awal_per_pencarian($kode_kantor,$sampai,$cari)
		{
			//AWAS ADA PEERBEDAAN ANTAR SALDO AWAL INI DENGAN FUNGSI DIATAS, KALAU YANG INI <= KARENA SUM
				
			$query="
				SELECT 
					-- * 
					ROUND(SUM(DEBET) - SUM(KREDIT),1) AS SALDO
				FROM
				(
					-- SALDO AWAL
					SELECT
						AA.kode_kantor,AA.id_kategori,AA.kode_akun,AA.nama_kode_akun
						-- ,AA.id_bank,AA.nama_bank,AA.norek
						,'' AS id_bank
						,'' AS nama_bank
						,'' AS norek
						,'' AS tgl_uang_masuk
						,'' AS tgl_ins
						,'SALDO AWAL' AS no_ref
						,'SALDO AWAL' AS nama_ref
						,SUM(DEBET) AS DEBET
						,SUM(KREDIT) AS KREDIT
					FROM
					(
						SELECT
							AA.kode_kantor
							,AA.id_kategori
							,COALESCE(CC.kode_akun,'') AS kode_akun
							,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
							,AA.id_bank
							,COALESCE(BB.nama_bank,'') AS nama_bank
							,COALESCE(BB.norek,'') AS norek
							,AA.tgl_uang_masuk
							,AA.tgl_ins
							,AA.no_ref
							,AA.nama_ref
							,
								
								
									-- CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
									
									CASE 
									WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN') ) THEN
										(AA.DEBET)
									WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
										(AA.DEBET)
									ELSE
										(AA.KREDIT)
									END AS DEBET
							,	
									
									-- CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
									
									CASE 
									WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN') ) THEN
										(AA.KREDIT)
									WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
										(AA.KREDIT)
									ELSE
										(AA.DEBET)
									END AS KREDIT
						FROM
						(
							
							
							-- UANG MASUK BUKAN SALDO AWAL SEBELUM CURRENT
								SELECT
									A.kode_kantor
									,A.id_kat_uang_masuk AS id_kategori
									,A.id_bank
									,A.tgl_uang_masuk
									,A.tgl_ins
									,A.no_bukti AS no_ref
									,A.ket_uang_masuk AS nama_ref
									,
									CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
										A.nominal
									ELSE
										0
									END AS DEBET
									,
									CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
										A.nominal
									ELSE
										0
									END AS KREDIT
								FROM tb_uang_masuk AS A
								WHERE A.kode_kantor = '".$kode_kantor."'
								AND A.tgl_uang_masuk > (
															SELECT '1900-01-01' AS tgl_uang_masuk
															UNION ALL
															SELECT distinct tgl_uang_masuk 
															FROM tb_uang_masuk 
																WHERE kode_kantor = A.kode_kantor 
																AND isAwal = 'YA' 
																AND tgl_uang_masuk < '".$sampai."' 
																AND id_kat_uang_masuk = A.id_kat_uang_masuk
															GROUP BY tgl_uang_masuk 
															ORDER BY tgl_uang_masuk 
															DESC LIMIT 0,1
														)
								AND A.tgl_uang_masuk <= '".$sampai."'
								AND A.isAwal <> 'YA'
							-- UANG MASUK BUKAN SALDO AWAL SEBELUM CURRENT

							UNION ALL

							SELECT
								A.kode_kantor
								,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
								,A.id_bank
								,A.tgl_dikeluarkan
								,A.tgl_ins
								,A.no_uang_keluar AS no_ref
								,A.ket_uang_keluar AS nama_ref
								,
								CASE WHEN A.id_kat_uang_keluar = '' THEN
									A.nominal
								ELSE
									0
								END AS DEBET
								,
								CASE WHEN A.id_kat_uang_keluar <> '' THEN
									A.nominal
								ELSE
									0
								END AS KREDIT
							FROM tb_uang_keluar AS A
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND A.tgl_dikeluarkan > 
												(
													SELECT '1900-01-01' AS tgl_uang_masuk
													UNION ALL
													SELECT distinct tgl_uang_masuk 
													FROM tb_uang_masuk 
														WHERE kode_kantor = A.kode_kantor 
														AND isAwal = 'YA' 
														AND tgl_uang_masuk < '".$sampai."' 
														AND id_kat_uang_masuk = (CASE WHEN A.id_kat_uang_keluar = '' THEN A.id_kat_uang_keluar2 ELSE A.id_kat_uang_keluar END)
													GROUP BY tgl_uang_masuk 
													ORDER BY tgl_uang_masuk 
													DESC LIMIT 0,1
												) 
								AND A.tgl_dikeluarkan <= '".$sampai."'
							
							UNION ALL
							
							SELECT
								A.kode_kantor
								,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'KAS' GROUP BY id_kode_akun LIMIT 0,1) AS kategori
								,'' AS id_bank
								,A.tgl_pencairan
								,A.tgl_ins
								,COALESCE(B.no_faktur,'') AS no_faktur
								,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
								,A.nominal AS DEBET
								,0 AS KREDIT
							FROM tb_d_penjualan_bayar AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
							AND A.nominal > 0
							AND A.id_bank = ''
							AND A.tgl_pencairan > 
												(
													SELECT '1900-01-01' AS tgl_uang_masuk
													UNION ALL
													SELECT distinct tgl_uang_masuk 
													FROM tb_uang_masuk 
														WHERE kode_kantor = A.kode_kantor 
														AND isAwal = 'YA' 
														AND tgl_uang_masuk < '".$sampai."' 
														AND id_kat_uang_masuk = (SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'KAS' GROUP BY id_kode_akun LIMIT 0,1)
													GROUP BY tgl_uang_masuk 
													ORDER BY tgl_uang_masuk 
													DESC LIMIT 0,1
												) 
								AND A.tgl_pencairan <= '".$sampai."'
							
							UNION ALL
							
							SELECT
								A.kode_kantor
								,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'BANK' AND id_bank = C.id_bank GROUP BY id_kode_akun LIMIT 0,1) AS kategori
								,'' AS id_bank
								,A.tgl_pencairan
								,A.tgl_ins
								,COALESCE(B.no_faktur,'') AS no_faktur
								,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
								,A.nominal AS DEBET
								,0 AS KREDIT
							FROM tb_d_penjualan_bayar AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
							AND A.nominal > 0
							AND A.id_bank <> ''
							AND A.tgl_pencairan > 
													(
														SELECT '1900-01-01' AS tgl_uang_masuk
														UNION ALL
														SELECT distinct tgl_uang_masuk 
														FROM tb_uang_masuk 
															WHERE kode_kantor = A.kode_kantor 
															AND isAwal = 'YA' 
															AND tgl_uang_masuk < '".$sampai."' 
															AND id_kat_uang_masuk = (SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'BANK' AND id_bank = C.id_bank GROUP BY id_kode_akun LIMIT 0,1) 
														GROUP BY tgl_uang_masuk 
														ORDER BY tgl_uang_masuk 
														DESC LIMIT 0,1
													) 
								AND A.tgl_pencairan <= '".$sampai."'
							
							UNION ALL
							
							
								SELECT  
										kode_kantor
										,id_kode_akun
										,'' AS id_bank
										,tgl_transaksi
										,tgl_ins
										,no_bukti
										-- ,nama_akun
										,ket
										,debit
										,kredit
								FROM tb_jurnal_umum AS A
								WHERE kode_kantor = '".$kode_kantor."'
								AND DATE(tgl_transaksi) > 
													(
														SELECT '1900-01-01' AS tgl_uang_masuk
														UNION ALL
														SELECT distinct tgl_uang_masuk 
														FROM tb_uang_masuk 
															WHERE kode_kantor = A.kode_kantor 
															AND isAwal = 'YA' 
															AND tgl_uang_masuk < '".$sampai."' 
															AND id_kat_uang_masuk = A.id_kode_akun 
														GROUP BY tgl_uang_masuk 
														ORDER BY tgl_uang_masuk 
														DESC LIMIT 0,1
													) 
									AND DATE(tgl_transaksi) <=  DATE('".$sampai."')
								
							UNION ALL
							
							SELECT
								A.kode_kantor
								,A.id_kode_in
								,'' AS id_bank
								,A.tgl_transaksi
								,A.tgl_ins
								,A.no_bukti
								,A.ket_mutasi
								,A.nominal AS DEBET
								, 0 AS KREDIT
								FROM tb_mutasi_kas AS A
								WHERE A.kode_kantor = '".$kode_kantor."'
								AND A.tgl_transaksi > 
														(
															SELECT '1900-01-01' AS tgl_uang_masuk
															UNION ALL
															SELECT distinct tgl_uang_masuk 
															FROM tb_uang_masuk 
																WHERE kode_kantor = A.kode_kantor 
																AND isAwal = 'YA' 
																AND tgl_uang_masuk < '".$sampai."' 
																AND id_kat_uang_masuk = A.id_kode_in 
															GROUP BY tgl_uang_masuk 
															ORDER BY tgl_uang_masuk 
															DESC LIMIT 0,1
														) 
									AND A.tgl_transaksi <= '".$sampai."'
								
							UNION ALL
							
							SELECT
								A.kode_kantor
								,A.id_kode_out
								,'' AS id_bank
								,A.tgl_transaksi
								,A.tgl_ins
								,A.no_bukti
								,A.ket_mutasi
								, 0 AS DEBET
								,A.nominal AS KREDIT
								
								FROM tb_mutasi_kas AS A
								WHERE A.kode_kantor = '".$kode_kantor."'
								AND A.tgl_transaksi > (
															SELECT '1900-01-01' AS tgl_uang_masuk
															UNION ALL
															SELECT distinct tgl_uang_masuk 
															FROM tb_uang_masuk 
																WHERE kode_kantor = A.kode_kantor 
																AND isAwal = 'YA' 
																AND tgl_uang_masuk < '".$sampai."' 
																AND id_kat_uang_masuk = A.id_kode_out 
															GROUP BY tgl_uang_masuk 
															ORDER BY tgl_uang_masuk 
															DESC LIMIT 0,1
														)
									AND A.tgl_transaksi <= '".$sampai."'
								
							UNION ALL
								SELECT 
									A.kode_kantor,
									(
										SELECT distinct id_kode_akun 
										FROM tb_h_pembelian AS AA
										LEFT JOIN tb_kode_akun AS BB 
											ON AA.kode_kantor = BB.kode_kantor 
											AND AA.id_supplier = BB.id_supplier
											AND BB.kat_akun_jurnal = 'UTANG'
										WHERE AA.id_h_pembelian = A.id_h_pembelian
										GROUP BY id_kode_akun
										LIMIT 0,1
									)
									AS id_kode_out,
									'' AS id_bank,
									A.tgl_terima,
									A.tgl_ins,
									A.no_surat_jalan,
									CONCAT('Hutang Produk Invoice ',A.no_surat_jalan) AS keterangan,
									0 AS DEBIT,
									(COALESCE(C.NOMINAL,0) + A.biaya_kirim) AS KREDIT
									
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
								WHERE A.kode_kantor = '".$kode_kantor."'
								AND A.tgl_terima > (
														SELECT '1900-01-01' AS tgl_uang_masuk
														UNION ALL
														SELECT distinct tgl_uang_masuk 
														FROM tb_uang_masuk 
															WHERE kode_kantor = A.kode_kantor 
															AND isAwal = 'YA' 
															AND tgl_uang_masuk < '".$sampai."' 
															AND id_kat_uang_masuk = 
																					(
																						SELECT distinct id_kode_akun 
																						FROM tb_h_pembelian AS AA
																						LEFT JOIN tb_kode_akun AS BB 
																							ON AA.kode_kantor = BB.kode_kantor 
																							AND AA.id_supplier = BB.id_supplier
																							AND BB.kat_akun_jurnal = 'UTANG'
																						WHERE AA.id_h_pembelian = A.id_h_pembelian
																						GROUP BY id_kode_akun
																						LIMIT 0,1
																					)
														GROUP BY tgl_uang_masuk 
														ORDER BY tgl_uang_masuk 
														DESC LIMIT 0,1
													) 
									AND A.tgl_terima <= '".$sampai."'
								
							UNION ALL
							-- SALDO AWAL INPUT UANG MASUK
								SELECT
									A.kode_kantor
									,A.id_kat_uang_masuk
									,'' AS id_bank
									,'' AS tgl_masuk
									,'' AS tgl_ins
									
									,COALESCE(B.kode_akun,'') AS kode_akun
									,COALESCE(B.nama_kode_akun,'') AS nama_kode_akun
									
									,CASE 
										WHEN ( (COALESCE(B.kat_akun_jurnal,'') = 'HARTA') OR (COALESCE(B.kat_akun_jurnal,'') = 'BEBAN') ) THEN
											nominal
										ELSE
											0
										END AS DEBET
									,CASE 
										WHEN ( (COALESCE(B.kat_akun_jurnal,'') = 'HARTA') OR (COALESCE(B.kat_akun_jurnal,'') = 'BEBAN') ) THEN
											0
										ELSE
											nominal
										END AS KREDIT
								FROM tb_uang_masuk AS A
								LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
								WHERE COALESCE(A.isAwal,'') = 'YA' 
								AND A.kode_kantor = '".$kode_kantor."'
								-- AND A.tgl_uang_masuk < '".$sampai."'
								AND A.tgl_uang_masuk = (
													SELECT distinct tgl_uang_masuk 
													FROM tb_uang_masuk 
														WHERE kode_kantor = A.kode_kantor 
														AND isAwal = 'YA' 
														AND tgl_uang_masuk < '".$sampai."' 
														AND id_kat_uang_masuk = A.id_kat_uang_masuk 
													GROUP BY tgl_uang_masuk 
													ORDER BY tgl_uang_masuk 
													DESC LIMIT 0,1
												)
							-- SALDO AWAL INPUT UANG MASUK
						) AS AA
						LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
						LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
						-- WHERE ( COALESCE(CC.kode_akun,'') <> '' AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
						".$cari."
					) AS AA
					GROUP BY AA.kode_kantor,AA.id_kategori,AA.kode_akun,AA.nama_kode_akun
					-- SALSO AWAL
					
				) AS AA
				WHERE 
					CASE WHEN COALESCE(AA.nama_ref,'') = 'SALDO AWAL' THEN
						(AA.DEBET + AA.KREDIT)
					ELSE
						1
					END > 0
					
				ORDER BY AA.kode_akun ASC,AA.tgl_uang_masuk ASC,AA.tgl_ins ASC
			
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
		
		
		function list_acc_buku_besar($kode_kantor,$dari,$sampai,$cari)
		{
			
				
			$query="
				
			
				-- CURRENT TRANSAKSI
				SELECT
					AA.kode_kantor
					,AA.id_kategori
					
					,COALESCE(CC.kode_akun,'') AS kode_akun
					,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
					
					,AA.id_bank
					,COALESCE(BB.nama_bank,'') AS nama_bank
					,COALESCE(BB.norek,'') AS norek
					,AA.tgl_uang_masuk
					,AA.tgl_ins
					,AA.no_ref
					,AA.nama_ref
					-- ,AA.DEBET
					-- ,AA.KREDIT
					,
						-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
						
							CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								(AA.DEBET)
							ELSE
								(AA.KREDIT)
							END AS DEBET
					,	-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
							
							CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								(AA.KREDIT)
							ELSE
								(AA.DEBET)
							END AS KREDIT
				FROM
				(
					SELECT
						A.kode_kantor
						,A.id_kat_uang_masuk AS id_kategori
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						,A.id_bank
						,A.tgl_uang_masuk
						,A.tgl_ins
						,A.no_bukti AS no_ref
						,A.ket_uang_masuk AS nama_ref
						,
						CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_masuk AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_masuk2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."' 
					-- AND A.tgl_uang_masuk BETWEEN '".$dari."' AND '".$sampai."'  AND A.isAwal = 'TIDAK'
					AND A.tgl_uang_masuk >= '".$dari."' AND A.tgl_uang_masuk <= '".$sampai."'  
					AND A.isAwal = 'TIDAK'

					UNION ALL

					SELECT
						A.kode_kantor
						,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
						
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						
						,A.id_bank
						,A.tgl_dikeluarkan
						,A.tgl_ins
						,A.no_uang_keluar AS no_ref
						,A.ket_uang_keluar AS nama_ref
						-- ,A.nominal
						,
						-- CASE WHEN A.id_uang_keluar = A.id_induk_uang_keluar THEN
						CASE WHEN A.id_kat_uang_keluar = '' THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						-- CASE WHEN A.id_uang_keluar <> A.id_induk_uang_keluar THEN
						CASE WHEN A.id_kat_uang_keluar <> '' THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_keluar AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_keluar = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_keluar2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."'
					-- AND A.tgl_diterima BETWEEN '".$dari."' AND '".$sampai."'
					AND A.tgl_dikeluarkan >= '".$dari."' AND A.tgl_dikeluarkan <= '".$sampai."'
					
					UNION ALL
					
					
						SELECT  
								kode_kantor
								,id_kode_akun
								,'' AS id_bank
								,tgl_transaksi
								,tgl_ins
								,no_bukti
								,nama_akun
								,debit
								,kredit
						FROM tb_jurnal_umum
						WHERE kode_kantor = '".$kode_kantor."'
						AND DATE(tgl_transaksi) >=  DATE('".$dari."') AND DATE(tgl_transaksi) <= DATE('".$sampai."')
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_in
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						,A.nominal AS DEBET
						, 0 AS KREDIT
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' 
						-- AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <= '".$sampai."'
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_out
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						, 0 AS DEBET
						,A.nominal AS KREDIT
						
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' 
						-- AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <='".$sampai."'
						
				) AS AA
				LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
				LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
				WHERE ( COALESCE(CC.kode_akun,'') <> '' 
				AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
				-- WHERE COALESCE(CC.kode_akun,'') = '".$cari."'
				ORDER BY tgl_uang_masuk ASC,tgl_ins ASC,kode_akun DESC;
			
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
		
		function list_acc_buku_besar_laporan_keuangan_saldo_20211012($kode_kantor,$dari,$sampai,$cari,$last_saldo_awal,$saldo_awal)
		{
			
				
			$query="
				-- GABUNGAKN SALDO AWAL DAN TRANSAKSI SEBELUM CURRENT
				SELECT 
					kode_kantor,kategori,kode_akun,nama_kode_akun,id_bank,nama_bank,norek,
					tgl_uang_masuk,tgl_ins,no_ref,nama_ref
					,SUM(DEBET) AS DEBET
					,SUM(KREDIT) AS KREDIT
				FROM
				(
					-- SALDO AWAL
					
					SELECT
						AA.kode_kantor
						,'SALDO AWAL' AS kategori
						,'SALDO AWAL' AS kode_akun
						,'SALDO AWAL' AS nama_kode_akun
						,'' AS id_bank
						,'' AS nama_bank
						,'' AS norek
						,'' AS tgl_uang_masuk
						,'' AS tgl_ins
						,'' AS no_ref
						,'SALDO AWAL' AS nama_ref
						,SUM(AA.DEBET) AS DEBET
						,SUM(AA.KREDIT) AS KREDIT
					FROM
					(
						
						SELECT
							'".$kode_kantor."' AS kode_kantor
							,'SALDO AWAL' AS id_kategori
							,'' AS id_bank
							,'".$last_saldo_awal."' AS tgl_uang_masuk
							,'".$last_saldo_awal." 01:00:00' AS tgl_ins
							,'' AS no_ref
							,'SALDO AWAL' AS nama_ref
							,
							CASE WHEN ".$saldo_awal." >= 0 THEN
								".$saldo_awal."
							ELSE
								0
							END AS DEBET
							,
							CASE WHEN ".$saldo_awal." < 0 THEN
								".$saldo_awal."
							ELSE
								0
							END AS KREDIT
							
					) AS AA
					-- LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
					-- LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
					GROUP BY AA.kode_kantor
					
					-- SALDO AWAL

					UNION ALL
					
					
					-- SETELAH SALDO AWAL SEBELUM CURENT
					SELECT
						AA.kode_kantor
						,'SALDO AWAL' AS kategori -- ,AA.id_kategori
						,'SALDO AWAL' AS kode_akun -- ,COALESCE(CC.kode_akun,'') AS kode_akun
						,'SALDO AWAL' AS nama_kode_akun -- ,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
						,'' AS id_bank -- ,AA.id_bank
						,'' AS nama_bank -- ,COALESCE(BB.nama_bank,'') AS nama_bank
						,'' AS norek -- ,COALESCE(BB.norek,'') AS norek
						,'' AS tgl_uang_masuk -- ,AA.tgl_uang_masuk
						,'' AS tgl_ins -- ,AA.tgl_ins
						,'' AS no_ref -- ,AA.no_ref
						,'SALDO AWAL' AS nama_ref -- ,AA.nama_ref
						
						-- ,SUM(AA.DEBET) AS DEBET
						-- ,SUM(AA.KREDIT) AS KREDIT
						
						
						,
							-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
							
							CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								SUM(AA.DEBET)
							ELSE
								SUM(AA.KREDIT)
							END AS DEBET
						,
							-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
							
							CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								SUM(AA.KREDIT)
							ELSE
								SUM(AA.DEBET)
							END AS KREDIT
						
					FROM
					(
						SELECT
							A.kode_kantor
							,A.id_kat_uang_masuk AS id_kategori
							,A.id_bank
							,A.tgl_uang_masuk
							,A.tgl_ins
							,A.no_bukti AS no_ref
							,A.ket_uang_masuk AS nama_ref
							,
							CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
								A.nominal
							ELSE
								0
							END AS DEBET
							,
							CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
								A.nominal
							ELSE
								0
							END AS KREDIT
						FROM tb_uang_masuk AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_uang_masuk > '".$last_saldo_awal."' AND A.tgl_uang_masuk < '".$dari."'
						AND A.isAwal = 'TIDAK'
						
						UNION ALL
						
					
								SELECT  
									kode_kantor
									,id_kode_akun
									,'' AS id_bank
									,tgl_transaksi
									,tgl_ins
									,no_bukti
									,nama_akun
									,debit
									,kredit
							FROM tb_jurnal_umum
							WHERE kode_kantor = '".$kode_kantor."'
							AND DATE(tgl_transaksi) > '".$last_saldo_awal."'
							AND DATE(tgl_transaksi) <  DATE('".$dari."')
					
						UNION ALL

						SELECT
							A.kode_kantor
							,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
							,A.id_bank
							,A.tgl_dikeluarkan
							,A.tgl_ins
							,A.no_uang_keluar AS no_ref
							,A.ket_uang_keluar AS nama_ref
							,
							-- CASE WHEN A.id_uang_keluar = A.id_induk_uang_keluar THEN
							CASE WHEN A.id_kat_uang_keluar = '' THEN
								A.nominal
							ELSE
								0
							END AS DEBET
							,
							-- CASE WHEN A.id_uang_keluar <> A.id_induk_uang_keluar THEN
							CASE WHEN A.id_kat_uang_keluar <> '' THEN
								A.nominal
							ELSE
								0
							END AS KREDIT
						FROM tb_uang_keluar AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_dikeluarkan > '".$last_saldo_awal."' AND A.tgl_dikeluarkan < '".$dari."'
						
						UNION ALL
						
						SELECT
							A.kode_kantor
							,A.id_kode_in
							,'' AS id_bank
							,A.tgl_transaksi
							,A.tgl_ins
							,A.no_bukti
							,A.ket_mutasi
							,A.nominal AS DEBET
							, 0 AS KREDIT
							FROM tb_mutasi_kas AS A
							WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi > '".$last_saldo_awal."' AND A.tgl_transaksi < '".$dari."'
							
						UNION ALL
						
						SELECT
							A.kode_kantor
							,A.id_kode_out
							,'' AS id_bank
							,A.tgl_transaksi
							,A.tgl_ins
							,A.no_bukti
							,A.ket_mutasi
							, 0 AS DEBET
							,A.nominal AS KREDIT
							
							FROM tb_mutasi_kas AS A
							WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi > '".$last_saldo_awal."' AND A.tgl_transaksi < '".$dari."'
							
					) AS AA
					LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
					LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
					WHERE COALESCE(CC.kode_akun,'') = '".$cari."'
					GROUP BY AA.kode_kantor,CC.kat_akun_jurnal
					
					-- SETELAH SALDO AWAL SEBELUM CURRNT
				) AS AAA
				GROUP BY 
					kode_kantor,kategori,kode_akun,nama_kode_akun,id_bank,nama_bank,norek,
					tgl_uang_masuk,tgl_ins,no_ref,nama_ref
				-- GABUNGAKN SALDO AWAL DAN TRANSAKSI SEBELUM CURRENT
				
				UNION ALL
			
				-- CURRENT TRANSAKSI
				SELECT
					AA.kode_kantor
					,AA.id_kategori
					
					,COALESCE(CC.kode_akun,'') AS kode_akun
					,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
					
					,AA.id_bank
					,COALESCE(BB.nama_bank,'') AS nama_bank
					,COALESCE(BB.norek,'') AS norek
					,AA.tgl_uang_masuk
					,AA.tgl_ins
					,AA.no_ref
					,AA.nama_ref
					-- ,AA.DEBET
					-- ,AA.KREDIT
					,
						-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
						
							CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								(AA.DEBET)
							ELSE
								(AA.KREDIT)
							END AS DEBET
					,	-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
							
							CASE WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								(AA.KREDIT)
							ELSE
								(AA.DEBET)
							END AS KREDIT
				FROM
				(
					SELECT
						A.kode_kantor
						,A.id_kat_uang_masuk AS id_kategori
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						,A.id_bank
						,A.tgl_uang_masuk
						,A.tgl_ins
						,A.no_bukti AS no_ref
						,A.ket_uang_masuk AS nama_ref
						,
						CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_masuk AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_masuk2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."' 
					-- AND A.tgl_uang_masuk BETWEEN '".$dari."' AND '".$sampai."'  AND A.isAwal = 'TIDAK'
					AND A.tgl_uang_masuk >= '".$dari."' AND A.tgl_uang_masuk <= '".$sampai."'  
					AND A.isAwal = 'TIDAK'

					UNION ALL

					SELECT
						A.kode_kantor
						,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
						
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						
						,A.id_bank
						,A.tgl_dikeluarkan
						,A.tgl_ins
						,A.no_uang_keluar AS no_ref
						,A.ket_uang_keluar AS nama_ref
						-- ,A.nominal
						,
						-- CASE WHEN A.id_uang_keluar = A.id_induk_uang_keluar THEN
						CASE WHEN A.id_kat_uang_keluar = '' THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						-- CASE WHEN A.id_uang_keluar <> A.id_induk_uang_keluar THEN
						CASE WHEN A.id_kat_uang_keluar <> '' THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_keluar AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_keluar = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_keluar2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."'
					-- AND A.tgl_diterima BETWEEN '".$dari."' AND '".$sampai."'
					AND A.tgl_dikeluarkan >= '".$dari."' AND A.tgl_dikeluarkan <= '".$sampai."'
					
					UNION ALL
					
					
						SELECT  
								kode_kantor
								,id_kode_akun
								,'' AS id_bank
								,tgl_transaksi
								,tgl_ins
								,no_bukti
								,nama_akun
								,debit
								,kredit
						FROM tb_jurnal_umum
						WHERE kode_kantor = '".$kode_kantor."'
						AND DATE(tgl_transaksi) >=  DATE('".$dari."') AND DATE(tgl_transaksi) <= DATE('".$sampai."')
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_in
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						,A.nominal AS DEBET
						, 0 AS KREDIT
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' 
						-- AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <= '".$sampai."'
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_out
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						, 0 AS DEBET
						,A.nominal AS KREDIT
						
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' 
						-- AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <='".$sampai."'
						
				) AS AA
				LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
				LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
				-- WHERE ( COALESCE(CC.kode_akun,'') <> '' 
				-- AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
				WHERE COALESCE(CC.kode_akun,'') = '".$cari."'
				ORDER BY tgl_uang_masuk ASC,tgl_ins ASC,kode_akun DESC;
			
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
		
		
		function list_acc_buku_besar_laporan_keuangan_saldo($kode_kantor,$dari,$sampai,$cari,$last_saldo_awal,$saldo_awal)
		{
			
				
			$query="
				-- GABUNGAKN SALDO AWAL DAN TRANSAKSI SEBELUM CURRENT
				SELECT 
					kode_kantor,kategori,kode_akun,nama_kode_akun,id_bank,nama_bank,norek,
					tgl_uang_masuk,tgl_ins,no_ref,nama_ref
					,SUM(DEBET) AS DEBET
					,SUM(KREDIT) AS KREDIT
				FROM
				(
					-- SALDO AWAL
					
					SELECT
						AA.kode_kantor
						,'SALDO AWAL' AS kategori
						,'SALDO AWAL' AS kode_akun
						,'SALDO AWAL' AS nama_kode_akun
						,'' AS id_bank
						,'' AS nama_bank
						,'' AS norek
						,'' AS tgl_uang_masuk
						,'' AS tgl_ins
						,'' AS no_ref
						,'SALDO AWAL' AS nama_ref
						,SUM(AA.DEBET) AS DEBET
						,SUM(AA.KREDIT) AS KREDIT
					FROM
					(
						
						SELECT
							'".$kode_kantor."' AS kode_kantor
							,'SALDO AWAL' AS id_kategori
							,'' AS id_bank
							,'".$last_saldo_awal."' AS tgl_uang_masuk
							,'".$last_saldo_awal." 01:00:00' AS tgl_ins
							,'' AS no_ref
							,'SALDO AWAL' AS nama_ref
							,
							CASE WHEN ".$saldo_awal." >= 0 THEN
								".$saldo_awal."
							ELSE
								0
							END AS DEBET
							,
							CASE WHEN ".$saldo_awal." < 0 THEN
								".$saldo_awal."
							ELSE
								0
							END AS KREDIT
							
					) AS AA
					-- LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
					-- LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
					GROUP BY AA.kode_kantor
					
					-- SALDO AWAL

					UNION ALL
					
					
					-- SETELAH SALDO AWAL SEBELUM CURENT
					SELECT
						AA.kode_kantor
						,'SALDO AWAL' AS kategori -- ,AA.id_kategori
						,'SALDO AWAL' AS kode_akun -- ,COALESCE(CC.kode_akun,'') AS kode_akun
						,'SALDO AWAL' AS nama_kode_akun -- ,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
						,'' AS id_bank -- ,AA.id_bank
						,'' AS nama_bank -- ,COALESCE(BB.nama_bank,'') AS nama_bank
						,'' AS norek -- ,COALESCE(BB.norek,'') AS norek
						,'' AS tgl_uang_masuk -- ,AA.tgl_uang_masuk
						,'' AS tgl_ins -- ,AA.tgl_ins
						,'' AS no_ref -- ,AA.no_ref
						,'SALDO AWAL' AS nama_ref -- ,AA.nama_ref
						
						-- ,SUM(AA.DEBET) AS DEBET
						-- ,SUM(AA.KREDIT) AS KREDIT
						
						
						,
							-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
							
							CASE 
							WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								SUM(AA.DEBET)
							WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
								SUM(AA.DEBET)
							ELSE
								SUM(AA.KREDIT)
							END AS DEBET
						,
							-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
							
							CASE 
							WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								SUM(AA.KREDIT)
							WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
								SUM(AA.KREDIT)
							ELSE
								SUM(AA.DEBET)
							END AS KREDIT
						
					FROM
					(
						SELECT
							A.kode_kantor
							,A.id_kat_uang_masuk AS id_kategori
							,A.id_bank
							,A.tgl_uang_masuk
							,A.tgl_ins
							,A.no_bukti AS no_ref
							,A.ket_uang_masuk AS nama_ref
							,
							CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
								A.nominal
							ELSE
								0
							END AS DEBET
							,
							CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
								A.nominal
							ELSE
								0
							END AS KREDIT
						FROM tb_uang_masuk AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_uang_masuk > '".$last_saldo_awal."' AND A.tgl_uang_masuk < '".$dari."'
						AND A.isAwal = 'TIDAK'
						
						UNION ALL
						
					
								SELECT  
									kode_kantor
									,id_kode_akun
									,'' AS id_bank
									,tgl_transaksi
									,tgl_ins
									,no_bukti
									-- ,nama_akun
									,ket
									,debit
									,kredit
							FROM tb_jurnal_umum
							WHERE kode_kantor = '".$kode_kantor."'
							AND DATE(tgl_transaksi) > '".$last_saldo_awal."'
							AND DATE(tgl_transaksi) <  DATE('".$dari."')
					
						UNION ALL

						SELECT
							A.kode_kantor
							,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
							,A.id_bank
							,A.tgl_dikeluarkan
							,A.tgl_ins
							,A.no_uang_keluar AS no_ref
							,A.ket_uang_keluar AS nama_ref
							,
							-- CASE WHEN A.id_uang_keluar = A.id_induk_uang_keluar THEN
							CASE WHEN A.id_kat_uang_keluar = '' THEN
								A.nominal
							ELSE
								0
							END AS DEBET
							,
							-- CASE WHEN A.id_uang_keluar <> A.id_induk_uang_keluar THEN
							CASE WHEN A.id_kat_uang_keluar <> '' THEN
								A.nominal
							ELSE
								0
							END AS KREDIT
						FROM tb_uang_keluar AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_dikeluarkan > '".$last_saldo_awal."' AND A.tgl_dikeluarkan < '".$dari."'
						
						-- PENJUALAN
						UNION ALL
						
						SELECT
								A.kode_kantor
								,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'KAS' GROUP BY id_kode_akun LIMIT 0,1) AS kategori
								,'' AS id_bank
								,A.tgl_pencairan
								,A.tgl_ins
								,COALESCE(B.no_faktur,'') AS no_faktur
								,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
								,A.nominal AS DEBET
								,0 AS KREDIT
							FROM tb_d_penjualan_bayar AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
							AND A.nominal > 0
							AND A.id_bank = ''
							AND A.tgl_pencairan > '".$last_saldo_awal."' AND A.tgl_pencairan < '".$dari."'
							
							UNION ALL
							
							SELECT
								A.kode_kantor
								,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'BANK' AND id_bank = C.id_bank GROUP BY id_kode_akun LIMIT 0,1) AS kategori
								,'' AS id_bank
								,A.tgl_pencairan
								,A.tgl_ins
								,COALESCE(B.no_faktur,'') AS no_faktur
								,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
								,A.nominal AS DEBET
								,0 AS KREDIT
							FROM tb_d_penjualan_bayar AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
							AND A.nominal > 0
							AND A.id_bank <> ''
							AND A.tgl_pencairan > '".$last_saldo_awal."' AND A.tgl_pencairan < '".$dari."'
					
					-- PENJUALAN
					
						
						UNION ALL
						
						SELECT
							A.kode_kantor
							,A.id_kode_in
							,'' AS id_bank
							,A.tgl_transaksi
							,A.tgl_ins
							,A.no_bukti
							,A.ket_mutasi
							,A.nominal AS DEBET
							, 0 AS KREDIT
							FROM tb_mutasi_kas AS A
							WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi > '".$last_saldo_awal."' AND A.tgl_transaksi < '".$dari."'
							
						UNION ALL
						
						SELECT
							A.kode_kantor
							,A.id_kode_out
							,'' AS id_bank
							,A.tgl_transaksi
							,A.tgl_ins
							,A.no_bukti
							,A.ket_mutasi
							, 0 AS DEBET
							,A.nominal AS KREDIT
							
							FROM tb_mutasi_kas AS A
							WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi > '".$last_saldo_awal."' AND A.tgl_transaksi < '".$dari."'
							
					) AS AA
					LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
					LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
					WHERE COALESCE(CC.kode_akun,'') = '".$cari."'
					GROUP BY AA.kode_kantor,CC.kat_akun_jurnal
					
					-- SETELAH SALDO AWAL SEBELUM CURRNT
				) AS AAA
				GROUP BY 
					kode_kantor,kategori,kode_akun,nama_kode_akun,id_bank,nama_bank,norek,
					tgl_uang_masuk,tgl_ins,no_ref,nama_ref
				-- GABUNGAKN SALDO AWAL DAN TRANSAKSI SEBELUM CURRENT
				
				UNION ALL
			
				-- CURRENT TRANSAKSI
				SELECT
					AA.kode_kantor
					,AA.id_kategori
					
					,COALESCE(CC.kode_akun,'') AS kode_akun
					,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
					
					,AA.id_bank
					,COALESCE(BB.nama_bank,'') AS nama_bank
					,COALESCE(BB.norek,'') AS norek
					,AA.tgl_uang_masuk
					,AA.tgl_ins
					,AA.no_ref
					,AA.nama_ref
					-- ,AA.DEBET
					-- ,AA.KREDIT
					,
						-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
						
							CASE 
							WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								(AA.DEBET)
							WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
								(AA.DEBET)
							ELSE
								(AA.KREDIT)
							END AS DEBET
					,	-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
							
							CASE 
							WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								(AA.KREDIT)
							WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
								(AA.KREDIT)
							ELSE
								(AA.DEBET)
							END AS KREDIT
				FROM
				(
					SELECT
						A.kode_kantor
						,A.id_kat_uang_masuk AS id_kategori
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						,A.id_bank
						,A.tgl_uang_masuk
						,A.tgl_ins
						,A.no_bukti AS no_ref
						,A.ket_uang_masuk AS nama_ref
						,
						CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_masuk AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_masuk2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."' 
					-- AND A.tgl_uang_masuk BETWEEN '".$dari."' AND '".$sampai."'  AND A.isAwal = 'TIDAK'
					AND A.tgl_uang_masuk >= '".$dari."' AND A.tgl_uang_masuk <= '".$sampai."'  
					AND A.isAwal = 'TIDAK'

					UNION ALL

					SELECT
						A.kode_kantor
						,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
						
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						
						,A.id_bank
						,A.tgl_dikeluarkan
						,A.tgl_ins
						,A.no_uang_keluar AS no_ref
						,A.ket_uang_keluar AS nama_ref
						-- ,A.nominal
						,
						-- CASE WHEN A.id_uang_keluar = A.id_induk_uang_keluar THEN
						CASE WHEN A.id_kat_uang_keluar = '' THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						-- CASE WHEN A.id_uang_keluar <> A.id_induk_uang_keluar THEN
						CASE WHEN A.id_kat_uang_keluar <> '' THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_keluar AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_keluar = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_keluar2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."'
					-- AND A.tgl_diterima BETWEEN '".$dari."' AND '".$sampai."'
					AND A.tgl_dikeluarkan >= '".$dari."' AND A.tgl_dikeluarkan <= '".$sampai."'
					
					
					-- PENJUALAN
					UNION ALL
						
						SELECT
								A.kode_kantor
								,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'KAS' GROUP BY id_kode_akun LIMIT 0,1) AS kategori
								,'' AS id_bank
								,A.tgl_pencairan
								,A.tgl_ins
								,COALESCE(B.no_faktur,'') AS no_faktur
								,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
								,A.nominal AS DEBET
								,0 AS KREDIT
							FROM tb_d_penjualan_bayar AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
							AND A.nominal > 0
							AND A.id_bank = ''
							AND A.tgl_pencairan >= '".$dari."' AND A.tgl_pencairan <= '".$sampai."'
							
							UNION ALL
							
							SELECT
								A.kode_kantor
								,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'BANK' AND id_bank = C.id_bank GROUP BY id_kode_akun LIMIT 0,1) AS kategori
								,'' AS id_bank
								,A.tgl_pencairan
								,A.tgl_ins
								,COALESCE(B.no_faktur,'') AS no_faktur
								,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
								,A.nominal AS DEBET
								,0 AS KREDIT
							FROM tb_d_penjualan_bayar AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
							AND A.nominal > 0
							AND A.id_bank <> ''
							AND A.tgl_pencairan >= '".$dari."' AND A.tgl_pencairan <= '".$sampai."'
					
					-- PENJUALAN
					
					UNION ALL
					
					
						SELECT  
								kode_kantor
								,id_kode_akun
								,'' AS id_bank
								,tgl_transaksi
								,tgl_ins
								,no_bukti
								-- ,nama_akun
								,ket
								,debit
								,kredit
						FROM tb_jurnal_umum
						WHERE kode_kantor = '".$kode_kantor."'
						AND DATE(tgl_transaksi) >=  DATE('".$dari."') AND DATE(tgl_transaksi) <= DATE('".$sampai."')
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_in
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						,A.nominal AS DEBET
						, 0 AS KREDIT
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' 
						-- AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <= '".$sampai."'
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_out
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						, 0 AS DEBET
						,A.nominal AS KREDIT
						
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' 
						-- AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <='".$sampai."'
						
				) AS AA
				LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
				LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
				-- WHERE ( COALESCE(CC.kode_akun,'') <> '' 
				-- AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
				WHERE COALESCE(CC.kode_akun,'') = '".$cari."'
				ORDER BY tgl_uang_masuk ASC,tgl_ins ASC,kode_akun DESC;
			
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
		
		function sum_acc_buku_besar_laporan_keuangan_saldo_per_akun($kode_kantor,$dari,$sampai,$cari,$last_saldo_awal,$saldo_awal)
		{
			
				
			$query="
				-- GABUNGAKN SALDO AWAL DAN TRANSAKSI SEBELUM CURRENT
				SELECT 
					kode_kantor,kategori,kode_akun,nama_kode_akun,id_bank,nama_bank,norek,
					tgl_uang_masuk,tgl_ins,no_ref,nama_ref
					,SUM(DEBET) AS DEBET
					,SUM(KREDIT) AS KREDIT
				FROM
				(
					-- SALDO AWAL
					
					SELECT
						AA.kode_kantor
						,'SALDO AWAL' AS kategori
						,'SALDO AWAL' AS kode_akun
						,'SALDO AWAL' AS nama_kode_akun
						,'' AS id_bank
						,'' AS nama_bank
						,'' AS norek
						,'' AS tgl_uang_masuk
						,'' AS tgl_ins
						,'' AS no_ref
						,'SALDO AWAL' AS nama_ref
						,SUM(AA.DEBET) AS DEBET
						,SUM(AA.KREDIT) AS KREDIT
					FROM
					(
						
						SELECT
							'".$kode_kantor."' AS kode_kantor
							,'SALDO AWAL' AS id_kategori
							,'' AS id_bank
							,'".$last_saldo_awal."' AS tgl_uang_masuk
							,'".$last_saldo_awal." 01:00:00' AS tgl_ins
							,'' AS no_ref
							,'SALDO AWAL' AS nama_ref
							,
							CASE WHEN ".$saldo_awal." >= 0 THEN
								".$saldo_awal."
							ELSE
								0
							END AS DEBET
							,
							CASE WHEN ".$saldo_awal." < 0 THEN
								".$saldo_awal."
							ELSE
								0
							END AS KREDIT
							
					) AS AA
					-- LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
					-- LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
					GROUP BY AA.kode_kantor
					
					-- SALDO AWAL

					UNION ALL
					
					
					-- SETELAH SALDO AWAL SEBELUM CURENT
					SELECT
						AA.kode_kantor
						,'SALDO AWAL' AS kategori -- ,AA.id_kategori
						,'SALDO AWAL' AS kode_akun -- ,COALESCE(CC.kode_akun,'') AS kode_akun
						,'SALDO AWAL' AS nama_kode_akun -- ,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
						,'' AS id_bank -- ,AA.id_bank
						,'' AS nama_bank -- ,COALESCE(BB.nama_bank,'') AS nama_bank
						,'' AS norek -- ,COALESCE(BB.norek,'') AS norek
						,'' AS tgl_uang_masuk -- ,AA.tgl_uang_masuk
						,'' AS tgl_ins -- ,AA.tgl_ins
						,'' AS no_ref -- ,AA.no_ref
						,'SALDO AWAL' AS nama_ref -- ,AA.nama_ref
						
						-- ,SUM(AA.DEBET) AS DEBET
						-- ,SUM(AA.KREDIT) AS KREDIT
						
						
						,
							-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
							
							CASE 
							WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								SUM(AA.DEBET)
							WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
								SUM(AA.DEBET)
							ELSE
								SUM(AA.KREDIT)
							END AS DEBET
						,
							-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
							
							CASE 
							WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								SUM(AA.KREDIT)
							WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
								SUM(AA.KREDIT)
							ELSE
								SUM(AA.DEBET)
							END AS KREDIT
						
					FROM
					(
						SELECT
							A.kode_kantor
							,A.id_kat_uang_masuk AS id_kategori
							,A.id_bank
							,A.tgl_uang_masuk
							,A.tgl_ins
							,A.no_bukti AS no_ref
							,A.ket_uang_masuk AS nama_ref
							,
							CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
								A.nominal
							ELSE
								0
							END AS DEBET
							,
							CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
								A.nominal
							ELSE
								0
							END AS KREDIT
						FROM tb_uang_masuk AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_uang_masuk > '".$last_saldo_awal."' AND A.tgl_uang_masuk < '".$dari."'
						AND A.isAwal = 'TIDAK'
						
						UNION ALL
						
					
								SELECT  
									kode_kantor
									,id_kode_akun
									,'' AS id_bank
									,tgl_transaksi
									,tgl_ins
									,no_bukti
									,nama_akun
									,debit
									,kredit
							FROM tb_jurnal_umum
							WHERE kode_kantor = '".$kode_kantor."'
							AND DATE(tgl_transaksi) > '".$last_saldo_awal."'
							AND DATE(tgl_transaksi) <  DATE('".$dari."')
					
						UNION ALL

						SELECT
							A.kode_kantor
							,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
							,A.id_bank
							,A.tgl_dikeluarkan
							,A.tgl_ins
							,A.no_uang_keluar AS no_ref
							,A.ket_uang_keluar AS nama_ref
							,
							-- CASE WHEN A.id_uang_keluar = A.id_induk_uang_keluar THEN
							CASE WHEN A.id_kat_uang_keluar = '' THEN
								A.nominal
							ELSE
								0
							END AS DEBET
							,
							-- CASE WHEN A.id_uang_keluar <> A.id_induk_uang_keluar THEN
							CASE WHEN A.id_kat_uang_keluar <> '' THEN
								A.nominal
							ELSE
								0
							END AS KREDIT
						FROM tb_uang_keluar AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_dikeluarkan > '".$last_saldo_awal."' AND A.tgl_dikeluarkan < '".$dari."'
						
						-- PENJUALAN
						UNION ALL
						
						SELECT
								A.kode_kantor
								,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'KAS' GROUP BY id_kode_akun LIMIT 0,1) AS kategori
								,'' AS id_bank
								,A.tgl_pencairan
								,A.tgl_ins
								,COALESCE(B.no_faktur,'') AS no_faktur
								,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
								,A.nominal AS DEBET
								,0 AS KREDIT
							FROM tb_d_penjualan_bayar AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
							AND A.nominal > 0
							AND A.id_bank = ''
							AND A.tgl_pencairan > '".$last_saldo_awal."' AND A.tgl_pencairan < '".$dari."'
							
							UNION ALL
							
							SELECT
								A.kode_kantor
								,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'BANK' AND id_bank = C.id_bank GROUP BY id_kode_akun LIMIT 0,1) AS kategori
								,'' AS id_bank
								,A.tgl_pencairan
								,A.tgl_ins
								,COALESCE(B.no_faktur,'') AS no_faktur
								,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
								,A.nominal AS DEBET
								,0 AS KREDIT
							FROM tb_d_penjualan_bayar AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
							AND A.nominal > 0
							AND A.id_bank <> ''
							AND A.tgl_pencairan > '".$last_saldo_awal."' AND A.tgl_pencairan < '".$dari."'
					
					-- PENJUALAN
					
						
						UNION ALL
						
						SELECT
							A.kode_kantor
							,A.id_kode_in
							,'' AS id_bank
							,A.tgl_transaksi
							,A.tgl_ins
							,A.no_bukti
							,A.ket_mutasi
							,A.nominal AS DEBET
							, 0 AS KREDIT
							FROM tb_mutasi_kas AS A
							WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi > '".$last_saldo_awal."' AND A.tgl_transaksi < '".$dari."'
							
						UNION ALL
						
						SELECT
							A.kode_kantor
							,A.id_kode_out
							,'' AS id_bank
							,A.tgl_transaksi
							,A.tgl_ins
							,A.no_bukti
							,A.ket_mutasi
							, 0 AS DEBET
							,A.nominal AS KREDIT
							
							FROM tb_mutasi_kas AS A
							WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi > '".$last_saldo_awal."' AND A.tgl_transaksi < '".$dari."'
							
					) AS AA
					LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
					LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
					WHERE COALESCE(CC.kode_akun,'') = '".$cari."'
					GROUP BY AA.kode_kantor,CC.kat_akun_jurnal
					
					-- SETELAH SALDO AWAL SEBELUM CURRNT
				) AS AAA
				GROUP BY 
					kode_kantor,kategori,kode_akun,nama_kode_akun,id_bank,nama_bank,norek,
					tgl_uang_masuk,tgl_ins,no_ref,nama_ref
				-- GABUNGAKN SALDO AWAL DAN TRANSAKSI SEBELUM CURRENT
				
				UNION ALL
			
				-- CURRENT TRANSAKSI
				SELECT
					AA.kode_kantor
					,AA.id_kategori
					
					,COALESCE(CC.kode_akun,'') AS kode_akun
					,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
					
					,AA.id_bank
					,COALESCE(BB.nama_bank,'') AS nama_bank
					,COALESCE(BB.norek,'') AS norek
					,AA.tgl_uang_masuk
					,AA.tgl_ins
					,AA.no_ref
					,AA.nama_ref
					-- ,AA.DEBET
					-- ,AA.KREDIT
					,
						-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
						
							CASE 
							WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								(AA.DEBET)
							WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
								(AA.DEBET)
							ELSE
								(AA.KREDIT)
							END AS DEBET
					,	-- CASE WHEN ((CC.kat_akun_jurnal = 'UTANG') OR (CC.kat_akun_jurnal = 'MODAL') OR (CC.kat_akun_jurnal = 'PENDAPATAN')) THEN
							
							CASE 
							WHEN ((CC.kat_akun_jurnal = 'HARTA') OR (CC.kat_akun_jurnal = 'BEBAN')) THEN
								(AA.KREDIT)
							WHEN (CC.kat_akun_jurnal = 'UTANG') THEN
								(AA.KREDIT)
							ELSE
								(AA.DEBET)
							END AS KREDIT
				FROM
				(
					SELECT
						A.kode_kantor
						,A.id_kat_uang_masuk AS id_kategori
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						,A.id_bank
						,A.tgl_uang_masuk
						,A.tgl_ins
						,A.no_bukti AS no_ref
						,A.ket_uang_masuk AS nama_ref
						,
						CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_masuk AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_masuk2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."' 
					-- AND A.tgl_uang_masuk BETWEEN '".$dari."' AND '".$sampai."'  AND A.isAwal = 'TIDAK'
					AND A.tgl_uang_masuk >= '".$dari."' AND A.tgl_uang_masuk <= '".$sampai."'  
					AND A.isAwal = 'TIDAK'

					UNION ALL

					SELECT
						A.kode_kantor
						,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
						
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						
						,A.id_bank
						,A.tgl_dikeluarkan
						,A.tgl_ins
						,A.no_uang_keluar AS no_ref
						,A.ket_uang_keluar AS nama_ref
						-- ,A.nominal
						,
						-- CASE WHEN A.id_uang_keluar = A.id_induk_uang_keluar THEN
						CASE WHEN A.id_kat_uang_keluar = '' THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						-- CASE WHEN A.id_uang_keluar <> A.id_induk_uang_keluar THEN
						CASE WHEN A.id_kat_uang_keluar <> '' THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_keluar AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_keluar = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_keluar2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."'
					-- AND A.tgl_diterima BETWEEN '".$dari."' AND '".$sampai."'
					AND A.tgl_dikeluarkan >= '".$dari."' AND A.tgl_dikeluarkan <= '".$sampai."'
					
					
					-- PENJUALAN
					UNION ALL
						
						SELECT
								A.kode_kantor
								,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'KAS' GROUP BY id_kode_akun LIMIT 0,1) AS kategori
								,'' AS id_bank
								,A.tgl_pencairan
								,A.tgl_ins
								,COALESCE(B.no_faktur,'') AS no_faktur
								,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
								,A.nominal AS DEBET
								,0 AS KREDIT
							FROM tb_d_penjualan_bayar AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
							AND A.nominal > 0
							AND A.id_bank = ''
							AND A.tgl_pencairan >= '".$dari."' AND A.tgl_pencairan <= '".$sampai."'
							
							UNION ALL
							
							SELECT
								A.kode_kantor
								,(SELECT id_kode_akun FROM tb_kode_akun WHERE kode_kantor = '".$kode_kantor."' AND target = 'BANK' AND id_bank = C.id_bank GROUP BY id_kode_akun LIMIT 0,1) AS kategori
								,'' AS id_bank
								,A.tgl_pencairan
								,A.tgl_ins
								,COALESCE(B.no_faktur,'') AS no_faktur
								,CONCAT('Pembayaran Tunai Faktur ',COALESCE(B.no_faktur,'')) AS nama_ref
								,A.nominal AS DEBET
								,0 AS KREDIT
							FROM tb_d_penjualan_bayar AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
							LEFT JOIN tb_bank AS C ON A.kode_kantor = C.kode_kantor AND A.id_bank = C.id_bank
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
							AND A.nominal > 0
							AND A.id_bank <> ''
							AND A.tgl_pencairan >= '".$dari."' AND A.tgl_pencairan <= '".$sampai."'
					
					-- PENJUALAN
					
					UNION ALL
					
					
						SELECT  
								kode_kantor
								,id_kode_akun
								,'' AS id_bank
								,tgl_transaksi
								,tgl_ins
								,no_bukti
								,nama_akun
								,debit
								,kredit
						FROM tb_jurnal_umum
						WHERE kode_kantor = '".$kode_kantor."'
						AND DATE(tgl_transaksi) >=  DATE('".$dari."') AND DATE(tgl_transaksi) <= DATE('".$sampai."')
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_in
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						,A.nominal AS DEBET
						, 0 AS KREDIT
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' 
						-- AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <= '".$sampai."'
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_out
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						, 0 AS DEBET
						,A.nominal AS KREDIT
						
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' 
						-- AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <='".$sampai."'
						
				) AS AA
				LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
				LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
				-- WHERE ( COALESCE(CC.kode_akun,'') <> '' 
				-- AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
				WHERE COALESCE(CC.kode_akun,'') = '".$cari."'
				ORDER BY tgl_uang_masuk ASC,tgl_ins ASC,kode_akun DESC;
			
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
		
		
		function list_acc_buku_besar_laporan_keuangan_saldo_error($kode_kantor,$dari,$sampai,$cari,$last_saldo_awal,$saldo_awal)
		{
			
				
			$query="
				-- SALDO AWAL
				
				SELECT
					AA.kode_kantor
					,'SALDO AWAL' AS kategori -- ,AA.id_kategori
					,'SALDO AWAL' AS kode_akun -- ,COALESCE(CC.kode_akun,'') AS kode_akun
					,'SALDO AWAL' AS nama_kode_akun -- ,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
					,'' AS id_bank -- ,AA.id_bank
					,'' AS nama_bank -- ,COALESCE(BB.nama_bank,'') AS nama_bank
					,'' AS norek -- ,COALESCE(BB.norek,'') AS norek
					,'' AS tgl_uang_masuk -- ,AA.tgl_uang_masuk
					,'' AS tgl_ins -- ,AA.tgl_ins
					,'' AS no_ref -- ,AA.no_ref
					,'SALDO AWAL' AS nama_ref -- ,AA.nama_ref
					,SUM(AA.DEBET) AS DEBET
					,SUM(AA.KREDIT) AS KREDIT
				FROM
				(
					-- AMBIL DARI SALDO AWAL INPUTAN
					
					SELECT
						'".$kode_kantor."' AS kode_kantor
						,'SALDO AWAL' AS id_kategori
						,'' AS id_bank
						,'".$last_saldo_awal."' AS tgl_uang_masuk
						,'".$last_saldo_awal." 01:00:00' AS tgl_ins
						,'' AS no_ref
						,'SALDO AWAL' AS nama_ref
						,
						CASE WHEN ".$saldo_awal." >= 0 THEN
							".$saldo_awal."
						ELSE
							0
						END AS DEBET
						,
						CASE WHEN ".$saldo_awal." < 0 THEN
							".$saldo_awal."
						ELSE
							0
						END AS KREDIT
					
					
					-- AMBIL DARI SALDO AWAL INPUTAN
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kat_uang_masuk AS id_kategori
						,A.id_bank
						,A.tgl_uang_masuk
						,A.tgl_ins
						,A.no_bukti AS no_ref
						,A.ket_uang_masuk AS nama_ref
						,
						CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_masuk AS A
					WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_uang_masuk >= '".$last_saldo_awal."'AND A.tgl_uang_masuk < '".$dari."' AND A.isAwal = 'TIDAK'
					
					UNION ALL

					SELECT
						A.kode_kantor
						,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
						,A.id_bank
						,A.tgl_diterima
						,A.tgl_ins
						,A.no_uang_keluar AS no_ref
						,A.ket_uang_keluar AS nama_ref
						,
						CASE WHEN A.id_uang_keluar = A.id_induk_uang_keluar THEN
							0
						ELSE
							A.nominal
						END AS DEBET
						,
						CASE WHEN A.id_uang_keluar <> A.id_induk_uang_keluar THEN
							0
						ELSE
							A.nominal
						END AS KREDIT
					FROM tb_uang_keluar AS A
					WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_diterima >= '".$last_saldo_awal."' AND A.tgl_diterima < '".$dari."'
					-- WHERE A.kode_kantor = 'PST' AND A.tgl_diterima BETWEEN '2021-03-01' AND '2021-03-22'
					-- WHERE A.kode_kantor = 'PST' AND A.tgl_diterima < '2021-03-20'
					
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_in
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						,A.nominal AS DEBET
						, 0 AS KREDIT
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi >= '".$last_saldo_awal."' AND A.tgl_transaksi < '".$dari."'
						-- WHERE A.kode_kantor = 'PST' AND A.tgl_transaksi BETWEEN '2021-03-01' AND '2021-03-22'
						-- WHERE A.kode_kantor = 'PST' AND A.tgl_transaksi < '2021-03-20'
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_out
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						, 0 AS DEBET
						,A.nominal AS KREDIT
						
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi >= '".$last_saldo_awal."' AND A.tgl_transaksi < '".$dari."'
						-- WHERE A.kode_kantor = 'PST' AND A.tgl_transaksi BETWEEN '2021-03-01' AND '2021-03-22'
						-- WHERE A.kode_kantor = 'PST' AND A.tgl_transaksi < '2021-03-20'
						
				) AS AA
				LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
				LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
				WHERE ( COALESCE(CC.kode_akun,'') <> '' AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
				-- ORDER BY COALESCE(CC.kode_akun,'') ASC,tgl_ins ASC;
				GROUP BY AA.kode_kantor
				
				-- SALDO AWAL
			
				UNION ALL
			
				-- CURRENT TRANSAKSI
				SELECT
					AA.kode_kantor
					,AA.id_kategori
					
					,COALESCE(CC.kode_akun,'') AS kode_akun
					,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
					
					,AA.id_bank
					,COALESCE(BB.nama_bank,'') AS nama_bank
					,COALESCE(BB.norek,'') AS norek
					,AA.tgl_uang_masuk
					,AA.tgl_ins
					,AA.no_ref
					,AA.nama_ref
					,AA.DEBET
					,AA.KREDIT
				FROM
				(
					SELECT
						A.kode_kantor
						,A.id_kat_uang_masuk AS id_kategori
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						,A.id_bank
						,A.tgl_uang_masuk
						,A.tgl_ins
						,A.no_bukti AS no_ref
						,A.ket_uang_masuk AS nama_ref
						,
						CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_masuk AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_masuk2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."' 
					-- AND A.tgl_uang_masuk BETWEEN '".$dari."' AND '".$sampai."'  AND A.isAwal = 'TIDAK'
					AND A.tgl_uang_masuk >= '".$dari."' AND A.tgl_uang_masuk <= '".$sampai."'  
					AND A.isAwal = 'TIDAK'

					UNION ALL

					SELECT
						A.kode_kantor
						,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
						
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						
						,A.id_bank
						,A.tgl_diterima
						,A.tgl_ins
						,A.no_uang_keluar AS no_ref
						,A.ket_uang_keluar AS nama_ref
						-- ,A.nominal
						,
						CASE WHEN A.id_uang_keluar = A.id_induk_uang_keluar THEN
							0
						ELSE
							A.nominal
						END AS DEBET
						,
						CASE WHEN A.id_uang_keluar <> A.id_induk_uang_keluar THEN
							0
						ELSE
							A.nominal
						END AS KREDIT
					FROM tb_uang_keluar AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_keluar = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_keluar2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."'
					-- AND A.tgl_diterima BETWEEN '".$dari."' AND '".$sampai."'
					AND A.tgl_diterima >= '".$dari."' AND A.tgl_diterima <= '".$sampai."'
					
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_in
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						,A.nominal AS DEBET
						, 0 AS KREDIT
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' 
						-- AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <= '".$sampai."'
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_out
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						, 0 AS DEBET
						,A.nominal AS KREDIT
						
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' 
						-- AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_transaksi >= '".$dari."' AND A.tgl_transaksi <='".$sampai."'
						
				) AS AA
				LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
				LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
				-- WHERE ( COALESCE(CC.kode_akun,'') <> '' 
				-- AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
				
				WHERE COALESCE(CC.kode_akun,'') LIKE '%".$cari."%'
				ORDER BY tgl_ins ASC,kode_akun DESC;
			
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
		
		function list_acc_buku_besar_laporan_keuangan_saldo_20210402($kode_kantor,$dari,$sampai,$cari)
		{
			
				
			$query="
				-- SALDO AWAL
				
				SELECT
					AA.kode_kantor
					,'SALDO AWAL' AS kategori -- ,AA.id_kategori
					,'SALDO AWAL' AS kode_akun -- ,COALESCE(CC.kode_akun,'') AS kode_akun
					,'SALDO AWAL' AS nama_kode_akun -- ,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
					,'' AS id_bank -- ,AA.id_bank
					,'' AS nama_bank -- ,COALESCE(BB.nama_bank,'') AS nama_bank
					,'' AS norek -- ,COALESCE(BB.norek,'') AS norek
					,'' AS tgl_uang_masuk -- ,AA.tgl_uang_masuk
					,'' AS tgl_ins -- ,AA.tgl_ins
					,'' AS no_ref -- ,AA.no_ref
					,'SALDO AWAL' AS nama_ref -- ,AA.nama_ref
					,SUM(AA.DEBET) AS DEBET
					,SUM(AA.KREDIT) AS KREDIT
				FROM
				(
					SELECT
						A.kode_kantor
						,A.id_kat_uang_masuk AS id_kategori
						,A.id_bank
						,A.tgl_uang_masuk
						,A.tgl_ins
						,A.no_bukti AS no_ref
						,A.ket_uang_masuk AS nama_ref
						,
						CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_masuk AS A
					WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_uang_masuk < '".$dari."'
					-- WHERE A.kode_kantor = 'PST' AND A.tgl_uang_masuk BETWEEN '2021-03-01' AND '2021-03-22'
					-- WHERE A.kode_kantor = 'PST' AND A.tgl_uang_masuk < '2021-03-20'
					
					UNION ALL

					SELECT
						A.kode_kantor
						,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
						,A.id_bank
						,A.tgl_diterima
						,A.tgl_ins
						,A.no_uang_keluar AS no_ref
						,A.ket_uang_keluar AS nama_ref
						,
						CASE WHEN A.id_uang_keluar = A.id_induk_uang_keluar THEN
							0
						ELSE
							A.nominal
						END AS DEBET
						,
						CASE WHEN A.id_uang_keluar <> A.id_induk_uang_keluar THEN
							0
						ELSE
							A.nominal
						END AS KREDIT
					FROM tb_uang_keluar AS A
					WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_diterima < '".$dari."'
					-- WHERE A.kode_kantor = 'PST' AND A.tgl_diterima BETWEEN '2021-03-01' AND '2021-03-22'
					-- WHERE A.kode_kantor = 'PST' AND A.tgl_diterima < '2021-03-20'
					
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_in
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						,A.nominal AS DEBET
						, 0 AS KREDIT
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi < '".$dari."'
						-- WHERE A.kode_kantor = 'PST' AND A.tgl_transaksi BETWEEN '2021-03-01' AND '2021-03-22'
						-- WHERE A.kode_kantor = 'PST' AND A.tgl_transaksi < '2021-03-20'
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_out
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						, 0 AS DEBET
						,A.nominal AS KREDIT
						
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi < '".$dari."'
						-- WHERE A.kode_kantor = 'PST' AND A.tgl_transaksi BETWEEN '2021-03-01' AND '2021-03-22'
						-- WHERE A.kode_kantor = 'PST' AND A.tgl_transaksi < '2021-03-20'
						
				) AS AA
				LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
				LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
				WHERE ( COALESCE(CC.kode_akun,'') <> '' AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
				-- ORDER BY COALESCE(CC.kode_akun,'') ASC,tgl_ins ASC;
				GROUP BY AA.kode_kantor
				
				-- SALDO AWAL
			
				UNION ALL
			
				-- CURRENT TRANSAKSI
				SELECT
					AA.kode_kantor
					,AA.id_kategori
					
					,COALESCE(CC.kode_akun,'') AS kode_akun
					,COALESCE(CC.nama_kode_akun,'') AS nama_kode_akun
					
					,AA.id_bank
					,COALESCE(BB.nama_bank,'') AS nama_bank
					,COALESCE(BB.norek,'') AS norek
					,AA.tgl_uang_masuk
					,AA.tgl_ins
					,AA.no_ref
					,AA.nama_ref
					,AA.DEBET
					,AA.KREDIT
				FROM
				(
					SELECT
						A.kode_kantor
						,A.id_kat_uang_masuk AS id_kategori
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						,A.id_bank
						,A.tgl_uang_masuk
						,A.tgl_ins
						,A.no_bukti AS no_ref
						,A.ket_uang_masuk AS nama_ref
						,
						CASE WHEN A.id_uang_masuk = A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS DEBET
						,
						CASE WHEN A.id_uang_masuk <> A.id_induk_uang_masuk THEN
							A.nominal
						ELSE
							0
						END AS KREDIT
					FROM tb_uang_masuk AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_masuk = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_masuk2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_uang_masuk BETWEEN '".$dari."' AND '".$sampai."'

					UNION ALL

					SELECT
						A.kode_kantor
						,CASE WHEN id_kat_uang_keluar = '' THEN id_kat_uang_keluar2 ELSE id_kat_uang_keluar END AS id_kat_uang_keluar
						
						-- ,COALESCE(B.kode_akun,COALESCE(C.kode_akun,'')) AS kode_akun
						-- ,COALESCE(B.nama_kode_akun,COALESCE(C.nama_kode_akun,'')) AS nama_kode_akun
						
						,A.id_bank
						,A.tgl_diterima
						,A.tgl_ins
						,A.no_uang_keluar AS no_ref
						,A.ket_uang_keluar AS nama_ref
						-- ,A.nominal
						,
						CASE WHEN A.id_uang_keluar = A.id_induk_uang_keluar THEN
							0
						ELSE
							A.nominal
						END AS DEBET
						,
						CASE WHEN A.id_uang_keluar <> A.id_induk_uang_keluar THEN
							0
						ELSE
							A.nominal
						END AS KREDIT
					FROM tb_uang_keluar AS A
					-- LEFT JOIN tb_kode_akun AS B ON A.kode_kantor = B.kode_kantor AND A.id_kat_uang_keluar = B.id_kode_akun
					-- LEFT JOIN tb_kode_akun AS C ON A.kode_kantor = C.kode_kantor AND A.id_kat_uang_keluar2 = C.id_kode_akun
					WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_diterima BETWEEN '".$dari."' AND '".$sampai."'
					
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_in
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						,A.nominal AS DEBET
						, 0 AS KREDIT
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						
					UNION ALL
					
					SELECT
						A.kode_kantor
						,A.id_kode_out
						,'' AS id_bank
						,A.tgl_transaksi
						,A.tgl_ins
						,A.no_bukti
						,A.ket_mutasi
						, 0 AS DEBET
						,A.nominal AS KREDIT
						
						FROM tb_mutasi_kas AS A
						WHERE A.kode_kantor = '".$kode_kantor."' AND A.tgl_transaksi BETWEEN '".$dari."' AND '".$sampai."'
						
				) AS AA
				LEFT JOIN tb_bank AS BB ON AA.kode_kantor = BB.kode_kantor AND AA.id_bank = BB.id_bank
				LEFT JOIN tb_kode_akun AS CC ON AA.kode_kantor = CC.kode_kantor AND AA.id_kategori = CC.id_kode_akun
				WHERE ( COALESCE(CC.kode_akun,'') <> '' AND COALESCE(CC.kode_akun,'') LIKE '%".$cari."%')
				-- ORDER BY kode_akun ASC,tgl_ins ASC;
				ORDER BY tgl_ins ASC,kode_akun DESC;
			
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
		
		function row_last_saldo_awal_uang_masuk($kode_kantor,$kode_akun,$tgl_dari)
		{
				
			
			$query="
				SELECT MAX(A.tgl_uang_masuk) AS last_saldo_uang_masuk ,(A.nominal*1) AS nominal
				-- SELECT A.nominal,(A.tgl_uang_masuk) AS last_saldo_uang_masuk 
				FROM tb_uang_masuk AS A
				LEFT JOIN tb_kode_akun AS B ON A.id_kat_uang_masuk = B.id_kode_akun AND A.kode_kantor = B.kode_kantor
				WHERE A.kode_kantor = '".$kode_kantor."'
				AND A.isAwal = 'YA'
				AND B.kode_akun = '".$kode_akun."'
				AND DATE(A.tgl_uang_masuk) < '".$tgl_dari.".'
				GROUP BY A.nominal
				ORDER BY A.tgl_ins DESC LIMIT 0,1;
			
			";
			
			
			//$query = "SELECT *, tgl_uang_masuk AS last_saldo_uang_masuk  FROM tb_uang_masuk ORDER BY tgl_ins DESC LIMIT 0,1";
			
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
		
		function get_id_kode_akun($kode_kantor,$kode_akun)
		{
				
			
			$query="
				SELECT * FROM tb_kode_akun AS A
				WHERE A.kode_kantor = '".$kode_kantor."'
				AND A.kode_akun = '".$kode_akun."';
			
			";
			
			
			//$query = "SELECT *, tgl_uang_masuk AS last_saldo_uang_masuk  FROM tb_uang_masuk ORDER BY tgl_ins DESC LIMIT 0,1";
			
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