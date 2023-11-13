<?php
	class M_gl_pst_lap_penjualan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_laporan_detail_for_toko($cari,$order_by,$limit,$offset)
		{
			$query = "
					SELECT
						A.id_d_penjualan,
						A.id_h_penjualan,
						A.id_d_penerimaan,
						A.id_produk,
						A.id_h_diskon,
						A.id_d_diskon,
						A.isProduk,
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
						
						CASE WHEN (A.optr_diskon = '%') OR (A.optr_diskon = '') THEN
							((A.harga/100) * A.diskon)
						ELSE
							A.diskon
						END AS besar_diskon_ori,
						
						A.harga,
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
						
						A.id_dok,
						CASE
							WHEN (A.nama_dok <> '') THEN
								A.nama_dok
							WHEN ((A.nama_dok = '') AND (COALESCE(E.nama_karyawan,'') <> '')) THEN
								COALESCE(E.nama_karyawan,'')
							ELSE
								'-'
							END AS nama_dok,
						A.id_ass,
						A.nama_ass,
						
						A.tgl_ins,
						A.tgl_updt,
						A.user_updt,
						A.user_ins,
						A.kode_kantor
						,COALESCE(B.type_h_penjualan,'') AS type_h_penjualan
						,COALESCE(B.no_faktur,'') AS no_faktur
						,COALESCE(B.tgl_h_penjualan,'') AS tgl_h_penjualan
						,COALESCE(B.tgl_ins,'') AS waktu_input
						,COALESCE(B.no_costmer,'') AS no_costumer
						,COALESCE(B.nama_costumer,'') AS nama_costumer
						,COALESCE(B.diskon,'') AS diskon_lain
						,COALESCE(B.pajak,'') AS pajak
						,COALESCE(B.biaya,'') AS biaya
						,COALESCE(B.id_dokter,'') AS id_dokter
						,COALESCE(B.isApprove,'') AS isApprove
						
						,COALESCE(B.ba_file,'') AS ba_file
						,COALESCE(B.ba_url,'') AS ba_url
						,COALESCE(B.petugas_salah,'') AS petugas_salah
						,COALESCE(B.tgl_salah,'') AS tgl_salah
						,COALESCE(B.jam_salah,'') AS jam_salah
						,COALESCE(B.ket_salah,'') AS ket_salah
						,COALESCE(B.isApprove,'') AS isApprove
						
						,COALESCE(B.sts_penjualan,'') AS sts_penjualan
						,COALESCE(B.jenis_costumer,'') AS jenis_costumer
						
						,COALESCE(TIME(B.waktu_mulai_tindakan),'') AS waktu_mulai_tindakan
						,CASE WHEN ( COALESCE(TIME(B.tgl_updt),'') > COALESCE(TIME(B.waktu_mulai_tindakan),'') ) THEN
							COALESCE(TIME(B.tgl_updt),'') 
						ELSE
							''
						END
						AS waktu_selesai_tindakan
						
						,
						
						( 
							TIME_FORMAT
							(
								(timediff(
											COALESCE(B.tgl_updt,'')
											,COALESCE(B.waktu_mulai_tindakan,'')
										)
								)
								,
								'%H Jam %i Menit'
							)
						) 
						AS selisih_waktu_tindakan_menit
						
						/*
						
						-- %H %i %s
						( (TIMESTAMPDIFF(MINUTE
											,COALESCE(B.waktu_mulai_tindakan,'')
											,COALESCE(B.tgl_updt,'')
										)
							)%60 
						) 
						AS selisih_waktu_tindakan_menit
						*/
						
						
						,COALESCE(D.nama_karyawan,'') AS nama_dokter_konsul
						,COALESCE(B.id_dokter2,'') AS id_dokter2
						,COALESCE(E.nama_karyawan,'') AS nama_dokter_tindakan
						,COALESCE(C.kode_produk,'') AS kode_produk
						,COALESCE(C.nama_produk,'') AS nama_produk
						,COALESCE(C.isProduk,'') AS isProduk
						,COALESCE(C.isNPKP,'') AS isNPKP
						,COALESCE(F.nama_bank,'') AS nama_bank
						,COALESCE(F.TUNAI,0) AS TUNAI
						,COALESCE(F.NONTUNAI,0) AS NONTUNAI
						,COALESCE(F.tgl_ins,0) AS WAKTU_BAYAR
						
						,COALESCE(G.nama_diskon,'') AS nama_diskon
						,COALESCE(H.nama_diskon,'') AS nama_detail_diskon

					FROM tb_d_penjualan AS A
					INNER JOIN
					(
						SELECT
							A.*
							,COALESCE(B.alamat_rumah_sekarang,'') AS alamat_costumer
							,COALESCE(C.nama_kat_costumer,'') AS jenis_costumer
						FROM tb_h_penjualan AS A
						LEFT JOIN 
						(
							SELECT DISTINCT kode_kantor,id_kat_costumer,id_costumer,alamat_rumah_sekarang
							FROM tb_costumer
							-- WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							GROUP BY kode_kantor,id_kat_costumer,id_costumer,alamat_rumah_sekarang
						) AS B ON A.kode_kantor = B.kode_kantor AND A.id_costumer = B.id_costumer
						LEFT JOIN 
						(
							SELECT DISTINCT kode_kantor,id_kat_costumer,nama_kat_costumer
							FROM tb_kat_costumer
							-- WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							GROUP BY kode_kantor,id_kat_costumer,nama_kat_costumer
						) AS C ON A.kode_kantor = C.kode_kantor AND B.id_kat_costumer = C.id_kat_costumer
					)
					AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
					LEFT JOIN 
					(
						SELECT DISTINCT kode_kantor,id_produk,kode_produk,nama_produk,isProduk,isNPKP
						FROM tb_produk
						-- WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						GROUP BY kode_kantor,id_produk,kode_produk,nama_produk,isProduk,isNPKP
					) AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
					
					-- KARENA TOKO, AMBIL DARI id_sales
					LEFT JOIN 
					(
						SELECT DISTINCT kode_kantor,id_karyawan,nama_karyawan
						FROM tb_karyawan
						-- WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						GROUP BY kode_kantor,id_karyawan,nama_karyawan
					) AS D ON A.kode_kantor = D.kode_kantor AND COALESCE(B.id_sales,'') = D.id_karyawan
					
					LEFT JOIN 
					(
						SELECT DISTINCT kode_kantor,id_karyawan,nama_karyawan
						FROM tb_karyawan
						-- WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						GROUP BY kode_kantor,id_karyawan,nama_karyawan
					) AS E ON A.kode_kantor = E.kode_kantor AND COALESCE(B.id_dokter2,'') = E.id_karyawan
					LEFT JOIN
					(
						SELECT A.kode_kantor,A.id_h_penjualan,MAX(A.tgl_ins) AS tgl_ins, GROUP_CONCAT(A.nama_bank SEPARATOR ',') as nama_bank,SUM(A.TUNAI) AS TUNAI, SUM(A.NONTUNAI) AS NONTUNAI
						FROM
						(
							SELECT A.kode_kantor,A.id_h_penjualan,A.tgl_ins,GROUP_CONCAT(A.nama_bank SEPARATOR ',') as nama_bank
								,
									CASE WHEN A.nama_bank = '' THEN
										SUM(nominal)
									ELSE
										0
									END AS TUNAI
								,
									CASE WHEN A.nama_bank <> '' THEN
										SUM(nominal)
									ELSE
										0
									END AS NONTUNAI
							FROM
							(
								SELECT
									A.id_d_bayar
									,A.id_h_penjualan
									,A.id_bank
									,
										CASE WHEN A.id_bank <> '' THEN
											CONCAT(COALESCE(B.nama_bank,''),' (',COALESCE(B.norek,''),')')
										ELSE
											''
										END AS nama_bank
									,A.cara
									,A.nominal
									,A.kode_kantor
									,MAX(A.tgl_ins) AS tgl_ins
								FROM tb_d_penjualan_bayar AS A
								LEFT JOIN tb_bank AS B ON A.kode_kantor = B.kode_kantor AND A.id_bank = B.id_bank
								-- WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								GROUP BY 
								    A.id_d_bayar
									,A.id_h_penjualan
									,A.id_bank
									,CONCAT(COALESCE(B.nama_bank,''),' (',COALESCE(B.norek,''),')')
									,A.cara
									,A.nominal
									,A.kode_kantor
							) AS A
							GROUP BY A.kode_kantor,A.id_h_penjualan,A.tgl_ins,A.nama_bank
						) AS A
						GROUP BY A.kode_kantor,A.id_h_penjualan
					) AS F ON A.id_h_penjualan = F.id_h_penjualan AND A.kode_kantor = F.kode_kantor
					LEFT JOIN 
					(
						SELECT DISTINCT kode_kantor,id_h_diskon,nama_diskon
						FROM tb_h_diskon
						-- WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						GROUP BY kode_kantor,id_h_diskon,nama_diskon
					) AS G ON A.kode_kantor = G.kode_kantor AND B.id_h_diskon = G.id_h_diskon
					
					LEFT JOIN 
					(
						SELECT DISTINCT kode_kantor,id_h_diskon,nama_diskon
						FROM tb_h_diskon
						-- WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						GROUP BY kode_kantor,id_h_diskon,nama_diskon
					)  AS H ON A.kode_kantor = H.kode_kantor AND A.id_d_diskon2 = H.id_h_diskon

					
					
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
		
		function list_laporan_detail($cari,$order_by,$limit,$offset)
		{
			$query = "
					SELECT
						A.id_d_penjualan,
						A.id_h_penjualan,
						A.id_d_penerimaan,
						A.id_produk,
						A.id_h_diskon,
						A.id_d_diskon,
						A.isProduk,
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
						
						CASE WHEN (A.optr_diskon = '%') OR (A.optr_diskon = '') THEN
							((A.harga/100) * A.diskon)
						ELSE
							A.diskon
						END AS besar_diskon_ori,
						
						A.harga,
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
						
						A.id_dok,
						CASE
							WHEN (A.nama_dok <> '') THEN
								A.nama_dok
							WHEN ((A.nama_dok = '') AND (COALESCE(E.nama_karyawan,'') <> '')) THEN
								COALESCE(E.nama_karyawan,'')
							ELSE
								'-'
							END AS nama_dok,
						A.id_ass,
						A.nama_ass,
						
						A.tgl_ins,
						A.tgl_updt,
						A.user_updt,
						A.user_ins,
						A.kode_kantor
						,COALESCE(B.type_h_penjualan,'') AS type_h_penjualan
						,COALESCE(B.no_faktur,'') AS no_faktur
						,COALESCE(B.tgl_h_penjualan,'') AS tgl_h_penjualan
						,COALESCE(B.tgl_ins,'') AS waktu_input
						,COALESCE(B.no_costmer,'') AS no_costumer
						,COALESCE(B.nama_costumer,'') AS nama_costumer
						,COALESCE(B.diskon,'') AS diskon_lain
						,COALESCE(B.pajak,'') AS pajak
						,COALESCE(B.biaya,'') AS biaya
						,COALESCE(B.id_dokter,'') AS id_dokter
						,COALESCE(B.isApprove,'') AS isApprove
						
						,COALESCE(B.ba_file,'') AS ba_file
						,COALESCE(B.ba_url,'') AS ba_url
						,COALESCE(B.petugas_salah,'') AS petugas_salah
						,COALESCE(B.tgl_salah,'') AS tgl_salah
						,COALESCE(B.jam_salah,'') AS jam_salah
						,COALESCE(B.ket_salah,'') AS ket_salah
						-- ,COALESCE(B.isApprove,'') AS isApprove
						
						,COALESCE(B.sts_penjualan,'') AS sts_penjualan
						
						,COALESCE(TIME(B.waktu_mulai_tindakan),'') AS waktu_mulai_tindakan
						,CASE WHEN ( COALESCE(TIME(B.tgl_updt),'') > COALESCE(TIME(B.waktu_mulai_tindakan),'') ) THEN
							COALESCE(TIME(B.tgl_updt),'') 
						ELSE
							''
						END
						AS waktu_selesai_tindakan
						
						,
						
						( 
							TIME_FORMAT
							(
								(timediff(
											COALESCE(B.tgl_updt,'')
											,COALESCE(B.waktu_mulai_tindakan,'')
										)
								)
								,
								'%H Jam %i Menit'
							)
						) 
						AS selisih_waktu_tindakan_menit
						
						/*
						
						-- %H %i %s
						( (TIMESTAMPDIFF(MINUTE
											,COALESCE(B.waktu_mulai_tindakan,'')
											,COALESCE(B.tgl_updt,'')
										)
							)%60 
						) 
						AS selisih_waktu_tindakan_menit
						*/
						
						
						,COALESCE(D.nama_karyawan,'') AS nama_dokter_konsul
						,COALESCE(B.id_dokter2,'') AS id_dokter2
						,COALESCE(E.nama_karyawan,'') AS nama_dokter_tindakan
						,COALESCE(C.kode_produk,'') AS kode_produk
						,COALESCE(C.nama_produk,'') AS nama_produk
						,COALESCE(C.isProduk,'') AS isProduk
						,COALESCE(C.isNPKP,'') AS isNPKP
						
						,COALESCE(F.nama_bank,'') AS nama_bank
						,COALESCE(F.TUNAI,0) AS TUNAI
						,COALESCE(F.NONTUNAI,0) AS NONTUNAI
						,COALESCE(F.tgl_ins,0) AS WAKTU_BAYAR
						
						,COALESCE(G.nama_diskon,'') AS nama_diskon
						,COALESCE(H.nama_diskon,'') AS nama_detail_diskon

					FROM tb_d_penjualan AS A
					INNER JOIN 
					(
						SELECT 
						DISTINCT
							kode_kantor,
							id_h_penjualan,
							id_h_diskon,
							type_h_penjualan,
							no_faktur,
							tgl_h_penjualan,
							tgl_ins,
							no_costmer,
							nama_costumer,
							diskon,
							pajak,
							biaya,
							id_dokter,
							isApprove,
							ba_file,
							ba_url,
							petugas_salah,
							tgl_salah,
							jam_salah,
							ket_salah,
							sts_penjualan,
							waktu_mulai_tindakan,
							tgl_updt,
							id_dokter2
						FROM tb_h_penjualan
						-- WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						GROUP BY
							kode_kantor,
							id_h_penjualan,
							id_h_diskon,
							type_h_penjualan,
							no_faktur,
							tgl_h_penjualan,
							tgl_ins,
							no_costmer,
							nama_costumer,
							diskon,
							pajak,
							biaya,
							id_dokter,
							isApprove,
							ba_file,
							ba_url,
							petugas_salah,
							tgl_salah,
							jam_salah,
							ket_salah,
							isApprove,
							sts_penjualan,
							waktu_mulai_tindakan,
							tgl_updt,
							id_dokter2
					) AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
					LEFT JOIN 
					(
						SELECT DISTINCT kode_kantor,id_produk,kode_produk,nama_produk,isProduk,isNPKP
						FROM tb_produk
						-- WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						GROUP BY kode_kantor,id_produk,kode_produk,nama_produk,isProduk,isNPKP
					) AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
					LEFT JOIN 
					(
						SELECT DISTINCT kode_kantor,id_karyawan,nama_karyawan
						FROM tb_karyawan
						-- WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						GROUP BY kode_kantor,id_karyawan,nama_karyawan
					) AS D ON A.kode_kantor = D.kode_kantor AND COALESCE(B.id_dokter,'') = D.id_karyawan
					LEFT JOIN 
					(
						SELECT DISTINCT kode_kantor,id_karyawan,nama_karyawan
						FROM tb_karyawan
						-- WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						GROUP BY kode_kantor,id_karyawan,nama_karyawan
					) AS E ON A.kode_kantor = E.kode_kantor AND COALESCE(B.id_dokter2,'') = E.id_karyawan
					LEFT JOIN
					(
						SELECT A.kode_kantor,A.id_h_penjualan,MAX(A.tgl_ins) AS tgl_ins, GROUP_CONCAT(A.nama_bank SEPARATOR ',') as nama_bank,SUM(A.TUNAI) AS TUNAI, SUM(A.NONTUNAI) AS NONTUNAI
						FROM
						(
							SELECT A.kode_kantor,A.id_h_penjualan,A.tgl_ins,GROUP_CONCAT(A.nama_bank SEPARATOR ',') as nama_bank
								,
									CASE WHEN A.nama_bank = '' THEN
										SUM(nominal)
									ELSE
										0
									END AS TUNAI
								,
									CASE WHEN A.nama_bank <> '' THEN
										SUM(nominal)
									ELSE
										0
									END AS NONTUNAI
							FROM
							(
								SELECT
									A.id_d_bayar
									,A.id_h_penjualan
									,A.id_bank
									,
										CASE WHEN A.id_bank <> '' THEN
											CONCAT(COALESCE(B.nama_bank,''),' (',COALESCE(B.norek,''),')')
										ELSE
											''
										END AS nama_bank
									,A.cara
									,A.nominal
									,A.kode_kantor
									,MAX(A.tgl_ins) AS tgl_ins
								FROM tb_d_penjualan_bayar AS A
								LEFT JOIN tb_bank AS B ON A.kode_kantor = B.kode_kantor AND A.id_bank = B.id_bank
								-- WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								GROUP BY 
								    A.id_d_bayar
									,A.id_h_penjualan
									,A.id_bank
									,CONCAT(COALESCE(B.nama_bank,''),' (',COALESCE(B.norek,''),')')
									,A.cara
									,A.nominal
									,A.kode_kantor
							) AS A
							GROUP BY A.kode_kantor,A.id_h_penjualan,A.tgl_ins,A.nama_bank
						) AS A
						GROUP BY A.kode_kantor,A.id_h_penjualan
					) AS F ON A.id_h_penjualan = F.id_h_penjualan AND A.kode_kantor = F.kode_kantor
					LEFT JOIN 
					(
						SELECT DISTINCT kode_kantor,id_h_diskon,nama_diskon
						FROM tb_h_diskon
						-- WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						GROUP BY kode_kantor,id_h_diskon,nama_diskon
					) AS G ON A.kode_kantor = G.kode_kantor AND B.id_h_diskon = G.id_h_diskon
					
					LEFT JOIN 
					(
						SELECT DISTINCT kode_kantor,id_h_diskon,nama_diskon
						FROM tb_h_diskon
						-- WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						GROUP BY kode_kantor,id_h_diskon,nama_diskon
					)  AS H ON A.kode_kantor = H.kode_kantor AND A.id_d_diskon2 = H.id_h_diskon

					
					
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
		
		
		function list_laporan_h_penjualan($cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT 
					A.*
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
					
					,COALESCE(D.no_costumer,A.no_costmer) AS NO_PASIEN
					,COALESCE(D.nama_lengkap,A.nama_costumer) AS NAMA_PASIEN
					,COALESCE(D.avatar,'') AS AVATAR_PASIEN
					,COALESCE(D.avatar_url,'') AS AVATAR_URL_PASIEN
					
					
					,COALESCE(E.no_karyawan,'') AS NO_KARYAWAN
					,COALESCE(E.nama_karyawan,'') AS NAMA_KARYAWAN
					,COALESCE(E.avatar,'') AS AVATAR_DOKTER
					,COALESCE(E.avatar_url,'') AS AVATAR_URL_DOKTER
				FROM tb_h_penjualan AS A
				-- INNER JOIN
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
					-- WHERE id_h_penjualan = 'OFCJRT2020060900003'
					-- WHERE isProduk <> 'CONSUMABLE'
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
					FROM
					(
						SELECT A.kode_kantor,A.id_h_penjualan
							,SUM(A.nominal) AS BAYAR 
							,CASE WHEN A.id_bank = '' THEN SUM(A.nominal) ELSE 0 END AS BAYAR_CASH
							,CASE WHEN A.id_bank <> '' THEN SUM(A.nominal) ELSE 0 END AS BAYAR_BANK
							,COALESCE(CONCAT(B.nama_bank,' - ',B.norek),'') AS BANK
						FROM tb_d_penjualan_bayar AS A
						LEFT JOIN tb_bank AS B ON A.kode_kantor = B.kode_kantor AND A.id_bank = B.id_bank
						GROUP BY A.kode_kantor,A.id_h_penjualan,A.id_bank,COALESCE(CONCAT(B.nama_bank,' - ',B.norek),'')
					) AS AA
					GROUP BY AA.kode_kantor, AA.id_h_penjualan
				) AS C ON A.kode_kantor = C.kode_kantor AND A.id_h_penjualan = C.id_h_penjualan
				LEFT JOIN tb_costumer AS D ON A.kode_kantor = D.kode_kantor AND A.id_costumer = D.id_costumer
				LEFT JOIN tb_karyawan AS E ON A.kode_kantor = E.kode_kantor AND A.id_dokter = E.id_karyawan
				
				".$cari."
				-- WHERE A.kode_kantor = 'CJRT' AND A.sts_penjualan = 'SELESAI' AND DATE(A.tgl_h_penjualan) BETWEEN '2020-03-17' AND '2020-03-19'
				
				-- ORDER BY A.tgl_ins DESC;
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
		
		function list_laporan_detail_20211020($cari,$order_by,$limit,$offset)
		{
			$query = "
					SELECT
						A.id_d_penjualan,
						A.id_h_penjualan,
						A.id_d_penerimaan,
						A.id_produk,
						A.id_h_diskon,
						A.id_d_diskon,
						A.isProduk,
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
						
						CASE WHEN (A.optr_diskon = '%') OR (A.optr_diskon = '') THEN
							((A.harga/100) * A.diskon)
						ELSE
							A.diskon
						END AS besar_diskon_ori,
						
						A.harga,
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
						,COALESCE(B.type_h_penjualan,'') AS type_h_penjualan
						,COALESCE(B.no_faktur,'') AS no_faktur
						,COALESCE(B.tgl_h_penjualan,'') AS tgl_h_penjualan
						,COALESCE(B.tgl_ins,'') AS waktu_input
						,COALESCE(B.no_costmer,'') AS no_costumer
						,COALESCE(B.nama_costumer,'') AS nama_costumer
						,COALESCE(B.diskon,'') AS diskon_lain
						,COALESCE(B.pajak,'') AS pajak
						,COALESCE(B.biaya,'') AS biaya
						,COALESCE(B.id_dokter,'') AS id_dokter
						,COALESCE(B.isApprove,'') AS isApprove
						
						,COALESCE(B.ba_file,'') AS ba_file
						,COALESCE(B.ba_url,'') AS ba_url
						,COALESCE(B.petugas_salah,'') AS petugas_salah
						,COALESCE(B.tgl_salah,'') AS tgl_salah
						,COALESCE(B.jam_salah,'') AS jam_salah
						,COALESCE(B.ket_salah,'') AS ket_salah
						,COALESCE(B.isApprove,'') AS isApprove
						,COALESCE(TIME(B.waktu_mulai_tindakan),'') AS waktu_mulai_tindakan
						,CASE WHEN ( COALESCE(TIME(B.tgl_updt),'') > COALESCE(TIME(B.waktu_mulai_tindakan),'') ) THEN
							COALESCE(TIME(B.tgl_updt),'') 
						ELSE
							''
						END
						AS waktu_selesai_tindakan
						
						,
						( (TIMESTAMPDIFF(MINUTE
											,COALESCE(B.waktu_mulai_tindakan,'')
											,COALESCE(B.tgl_updt,'')
										)
							)%60 
						) 
						AS selisih_waktu_tindakan_menit
						
						
						,COALESCE(D.nama_karyawan,'') AS nama_dokter_konsul
						,COALESCE(B.id_dokter2,'') AS id_dokter2
						,COALESCE(E.nama_karyawan,'') AS nama_dokter_tindakan
						,COALESCE(C.kode_produk,'') AS kode_produk
						,COALESCE(C.nama_produk,'') AS nama_produk
						,COALESCE(C.isProduk,'') AS isProduk
						,COALESCE(F.nama_bank,'') AS nama_bank
						,COALESCE(F.TUNAI,0) AS TUNAI
						,COALESCE(F.NONTUNAI,0) AS NONTUNAI
						,COALESCE(F.tgl_ins,0) AS WAKTU_BAYAR
						
						,COALESCE(G.nama_diskon,'') AS nama_diskon

					FROM tb_d_penjualan AS A
					INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
					LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
					LEFT JOIN tb_karyawan AS D ON A.kode_kantor = D.kode_kantor AND COALESCE(B.id_dokter,'') = D.id_karyawan
					LEFT JOIN tb_karyawan AS E ON A.kode_kantor = E.kode_kantor AND COALESCE(B.id_dokter2,'') = E.id_karyawan
					LEFT JOIN
					(
						SELECT A.kode_kantor,A.id_h_penjualan,MAX(A.tgl_ins) AS tgl_ins, GROUP_CONCAT(A.nama_bank SEPARATOR ',') as nama_bank,SUM(A.TUNAI) AS TUNAI, SUM(A.NONTUNAI) AS NONTUNAI
						FROM
						(
							SELECT A.kode_kantor,A.id_h_penjualan,A.tgl_ins,GROUP_CONCAT(A.nama_bank SEPARATOR ',') as nama_bank
								,
									CASE WHEN A.nama_bank = '' THEN
										SUM(nominal)
									ELSE
										0
									END AS TUNAI
								,
									CASE WHEN A.nama_bank <> '' THEN
										SUM(nominal)
									ELSE
										0
									END AS NONTUNAI
							FROM
							(
								SELECT
									A.id_d_bayar
									,A.id_h_penjualan
									,A.id_bank
									,
										CASE WHEN A.id_bank <> '' THEN
											CONCAT(COALESCE(B.nama_bank,''),' (',COALESCE(B.norek,''),')')
										ELSE
											''
										END AS nama_bank
									,A.cara
									,A.nominal
									,A.kode_kantor
									,MAX(A.tgl_ins) AS tgl_ins
								FROM tb_d_penjualan_bayar AS A
								LEFT JOIN tb_bank AS B ON A.kode_kantor = B.kode_kantor AND A.id_bank = B.id_bank
								GROUP BY 
								    A.id_d_bayar
									,A.id_h_penjualan
									,A.id_bank
									,CONCAT(COALESCE(B.nama_bank,''),' (',COALESCE(B.norek,''),')')
									,A.cara
									,A.nominal
									,A.kode_kantor
							) AS A
							GROUP BY A.kode_kantor,A.id_h_penjualan,A.tgl_ins,A.nama_bank
						) AS A
						GROUP BY A.kode_kantor,A.id_h_penjualan
					) AS F ON A.id_h_penjualan = F.id_h_penjualan AND A.kode_kantor = F.kode_kantor
					LEFT JOIN tb_h_diskon AS G ON A.kode_kantor = G.kode_kantor AND B.id_h_diskon = G.id_h_diskon

					
					
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
		
		function list_laporan_produk_jasa_terjual($cari)
		{
			$query = "
					SELECT
						kode_kantor
						,kode_produk
						,nama_produk
						,SUM(jumlah) AS jumlah
						,satuan_jual
						,harga_setelah_diskon
						,SUM(jumlah) * harga_setelah_diskon AS SUBTOTAL
					FROM
					(
						SELECT 
							AA.* 
							,COALESCE(BB.kode_produk,'') AS kode_produk
							,COALESCE(BB.nama_produk,'') AS nama_produk
						FROM
						(
							SELECT
								kode_kantor,
								id_produk,
								SUM(jumlah) AS jumlah,
								satuan_jual,
								harga,
								harga_setelah_diskon,
								SUM(jumlah) * harga_setelah_diskon AS SUBTOTAL,
								COALESCE(AA.tgl_h_penjualan,'') AS tgl_h_penjualan
								,AA.sts_penjualan
							FROM
							(
								SELECT
									A.kode_kantor,
									A.id_produk,
									A.jumlah,
									A.satuan_jual,
									A.harga,
									CASE WHEN A.optr_diskon = '%' THEN
										A.harga - ((A.harga/100) * A.diskon)
									ELSE
										A.harga - A.diskon
									END AS harga_setelah_diskon
									,COALESCE(B.tgl_h_penjualan,'') AS tgl_h_penjualan
									,B.sts_penjualan
								FROM tb_d_penjualan AS A
								INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan  = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
								
								-- WHERE B.sts_penjualan IN ('SELESAI','PEMBAYARAN')
								-- AND DATE(COALESCE(B.tgl_h_penjualan,'')) = DATE(NOW())
								-- AND A.isProduk IN ('JASA','PRODUK')
							) AS AA
							GROUP BY 
								kode_kantor,
								id_produk,
								satuan_jual,
								harga,
								harga_setelah_diskon,
								COALESCE(AA.tgl_h_penjualan,'')
								,AA.sts_penjualan
						) AS AA
						LEFT JOIN tb_produk AS BB ON AA.id_produk = BB.id_produk AND AA.kode_kantor = BB.kode_kantor
						".$cari."
					) AS AAA
					GROUP BY 
						kode_kantor
						,kode_produk
						,nama_produk
						,satuan_jual
						,harga_setelah_diskon
					ORDER BY (SUM(jumlah) * harga_setelah_diskon) DESC
					-- LIMIT 0,20

					
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
		
		function list_laporan_produk_jasa_terjual_hanya_kode_nama($cari)
		{
			$query = "
					SELECT
						-- kode_kantor,
						kode_produk
						,nama_produk
						,SUM(jumlah) AS jumlah
						/*
						,satuan_jual
						,harga_setelah_diskon
						*/
						,SUM(jumlah * harga_setelah_diskon) AS SUBTOTAL
					FROM
					(
						SELECT 
							AA.* 
							,COALESCE(BB.kode_produk,'') AS kode_produk
							,COALESCE(BB.nama_produk,'') AS nama_produk
						FROM
						(
							SELECT
								kode_kantor,
								id_produk,
								SUM(jumlah) AS jumlah,
								satuan_jual,
								harga,
								harga_setelah_diskon,
								SUM(jumlah) * harga_setelah_diskon AS SUBTOTAL,
								COALESCE(AA.tgl_h_penjualan,'') AS tgl_h_penjualan
								,AA.sts_penjualan
							FROM
							(
								SELECT
									A.kode_kantor,
									A.id_produk,
									A.jumlah,
									A.satuan_jual,
									A.harga,
									CASE WHEN A.optr_diskon = '%' THEN
										A.harga - ((A.harga/100) * A.diskon)
									ELSE
										A.harga - A.diskon
									END AS harga_setelah_diskon
									,COALESCE(B.tgl_h_penjualan,'') AS tgl_h_penjualan
									,B.sts_penjualan
								FROM tb_d_penjualan AS A
								INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan  = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
								
								-- WHERE B.sts_penjualan IN ('SELESAI','PEMBAYARAN')
								-- AND DATE(COALESCE(B.tgl_h_penjualan,'')) = DATE(NOW())
								-- AND A.isProduk IN ('JASA','PRODUK')
							) AS AA
							GROUP BY 
								kode_kantor,
								id_produk,
								satuan_jual,
								harga,
								harga_setelah_diskon,
								COALESCE(AA.tgl_h_penjualan,'')
								,AA.sts_penjualan
						) AS AA
						LEFT JOIN tb_produk AS BB ON AA.id_produk = BB.id_produk AND AA.kode_kantor = BB.kode_kantor
						".$cari."
					) AS AAA
					GROUP BY 
						-- kode_kantor,
						kode_produk
						,nama_produk
						-- ,satuan_jual
						-- ,harga_setelah_diskon
					ORDER BY SUM(jumlah * harga_setelah_diskon) DESC;

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
		
		
		function list_laporan_produk_jasa_terjual_by_kategori($cari,$isian_cari)
		{
			$query = "
					SELECT id_kat_produk,kode_kat_produk,nama_kat_produk,isProduk,COUNT(id_d_penjualan) AS CNT,SUM(jumlah * harga_setelah_diskon) AS NOMINAL
					FROM
					(
						SELECT
							A.*
							,COALESCE(B.id_kat_produk,'') AS id_kat_produk
							,COALESCE(C.kode_kat_produk,'') AS kode_kat_produk
							,COALESCE(C.nama_kat_produk,'') AS nama_kat_produk
							,COALESCE(C.isProduk,'') AS isProduk
						FROM
						(
							SELECT
								A.id_d_penjualan,
								A.kode_kantor,
								A.id_produk,
								A.jumlah,
								A.satuan_jual,
								A.harga,
								CASE WHEN A.optr_diskon = '%' THEN
									A.harga - ((A.harga/100) * A.diskon)
								ELSE
									A.harga - A.diskon
								END AS harga_setelah_diskon
								,COALESCE(B.tgl_h_penjualan,'') AS tgl_h_penjualan
								,B.sts_penjualan
							FROM tb_d_penjualan AS A
							INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan  = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
							/*
							WHERE B.sts_penjualan IN ('SELESAI','PEMBAYARAN') AND DATE(B.tgl_h_penjualan) = '2020-11-04' AND A.kode_kantor <> 'PST'
							*/
							".$cari."
						) AS A
						INNER JOIN tb_prod_to_kat AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
						LEFT JOIN tb_kat_produk AS C ON A.kode_kantor = C.kode_kantor AND B.id_kat_produk = C.id_kat_produk
						WHERE (COALESCE(C.kode_kat_produk,'') LIKE '%".$isian_cari."%' OR COALESCE(C.nama_kat_produk,'') LIKE '%".$isian_cari."%') 
					) AS AA
					GROUP BY id_kat_produk,kode_kat_produk,nama_kat_produk,isProduk
					ORDER BY isProduk ASC,nama_kat_produk ASC,SUM(jumlah * harga_setelah_diskon) DESC
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
		
		function list_laporan_produk_jasa_terjual_by_kategori_by_akum_produk($cari,$id_kat_produk)
		{
			$query = "
					SELECT
						COALESCE(AA.id_produk,'') AS id_produk
						,COALESCE(AA.kode_produk,'') AS kode_produk
						,COALESCE(AA.nama_produk,'') AS nama_produk
						,COUNT(AA.id_d_penjualan) AS CNT
						,SUM(AA.jumlah * AA.harga_setelah_diskon) AS NOMINAL
					FROM
					(
						SELECT
							A.*
							,COALESCE(B.id_kat_produk,'') AS id_kat_produk
							,COALESCE(C.kode_produk,'') AS kode_produk
							,COALESCE(C.nama_produk,'') AS nama_produk
						FROM
						(
							SELECT
								A.id_d_penjualan,
								A.kode_kantor,
								A.id_produk,
								A.jumlah,
								A.satuan_jual,
								A.harga,
								CASE WHEN A.optr_diskon = '%' THEN
									A.harga - ((A.harga/100) * A.diskon)
								ELSE
									A.harga - A.diskon
								END AS harga_setelah_diskon
								,COALESCE(B.tgl_h_penjualan,'') AS tgl_h_penjualan
								,B.sts_penjualan
								,B.no_faktur
								,B.nama_costumer
							FROM tb_d_penjualan AS A
							INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan  = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
							/*
							WHERE B.sts_penjualan IN ('SELESAI','PEMBAYARAN') 
							AND DATE(B.tgl_h_penjualan) BETWEEN '2020-11-01' AND '2020-11-04'
							AND A.kode_kantor <> 'PST'
							AND A.kode_kantor LIKE '%CJR%'
							*/
							".$cari."
						) AS A
						INNER JOIN tb_prod_to_kat AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
						-- LEFT JOIN tb_kat_produk AS C ON A.kode_kantor = C.kode_kantor AND B.id_kat_produk = C.id_kat_produk
						LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
						WHERE B.id_kat_produk = '".$id_kat_produk."'
					) AS AA
					GROUP BY COALESCE(AA.id_produk,''),COALESCE(AA.kode_produk,'') ,COALESCE(AA.nama_produk,'') 

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
		
		function list_laporan_produk_jasa_terjual_by_kategori_by_akum_faktur($cari,$id_produk)
		{
			$query = "
					
					SELECT
						A.*
						,COALESCE(C.kode_produk,'') AS kode_produk
						,COALESCE(C.nama_produk,'') AS nama_produk
					FROM
					(
						SELECT
							A.id_d_penjualan,
							A.kode_kantor,
							A.id_produk,
							A.jumlah,
							A.satuan_jual,
							A.harga,
							CASE WHEN A.optr_diskon = '%' THEN
								A.harga - ((A.harga/100) * A.diskon)
							ELSE
								A.harga - A.diskon
							END AS harga_setelah_diskon
							,COALESCE(B.tgl_h_penjualan,'') AS tgl_h_penjualan
							,B.sts_penjualan
							,B.no_faktur
							,B.nama_costumer
							,B.type_h_penjualan
							
							
							,COALESCE(D.no_karyawan,'') AS NO_DOKTER_KONSUL
							,COALESCE(D.nama_karyawan,'') AS NAMA_DOKTER_KONSUL
							,COALESCE(E.no_karyawan,'') AS NO_DOKTER_TINDAKAN
							,COALESCE(E.nama_karyawan,'') AS NAMA_DOKTER_TINDAKAN
							
							/*
							,'' AS NO_DOKTER_KONSUL
							,'' AS NAMA_DOKTER_KONSUL
							,'' AS NO_DOKTER_TINDAKAN
							,'' AS NAMA_DOKTER_TINDAKAN
							*/
							
						FROM tb_d_penjualan AS A
						INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan  = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
						LEFT JOIN tb_karyawan AS D ON A.kode_kantor = D.kode_kantor AND B.id_dokter = D.id_karyawan
						LEFT JOIN tb_karyawan AS E ON A.kode_kantor = E.kode_kantor AND B.id_dokter2 = E.id_karyawan
						".$cari."
					) AS A
					LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
					WHERE A.id_produk = '".$id_produk."'
					

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
	
		function list_uang_masuk_perakun_bank($dari,$sampai,$cari,$kode_kantor)
		{
			$query = 
			"
				SELECT COUNT(A.id_h_penjualan) AS CNT, A.nama_bank,SUM(A.TUNAI) AS TUNAI, SUM(A.NONTUNAI) AS NONTUNAI
				FROM
				(
					SELECT A.kode_kantor,A.id_h_penjualan,A.nama_bank
						,
							CASE WHEN A.nama_bank = '' THEN
								SUM(nominal)
							ELSE
								0
							END AS TUNAI
						,
							CASE WHEN A.nama_bank <> '' THEN
								SUM(nominal)
							ELSE
								0
							END AS NONTUNAI
					FROM
					(
						SELECT
							COALESCE(C.id_d_bayar,'') AS id_d_bayar
							,A.id_h_penjualan
							,COALESCE(C.id_bank,'') AS id_bank
							,
								CASE WHEN COALESCE(C.id_bank,'') <> '' THEN
									CONCAT(COALESCE(B.nama_bank,''),' (',COALESCE(B.norek,''),')')
								ELSE
									'TUNAI'
								END AS nama_bank
							,COALESCE(C.cara,'') AS cara
							,COALESCE(C.nominal,0) AS nominal
							,A.kode_kantor
						FROM 
						tb_h_penjualan AS A
						LEFT JOIN tb_d_penjualan_bayar AS C ON A.id_h_penjualan = C.id_h_penjualan AND A.kode_kantor = C.kode_kantor
						LEFT JOIN tb_bank AS B ON A.kode_kantor = B.kode_kantor AND C.id_bank = B.id_bank
						WHERE 
						A.kode_kantor LIKE '%".$kode_kantor."%'
						
						
						
						AND 
						CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
							A.kode_kantor LIKE '%%'
						ELSE
							A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
						END
						
						
						AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
						AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						AND (COALESCE(B.nama_bank,'') LIKE '%".$cari."%')
						AND C.nominal > 0
					) AS A
					GROUP BY A.kode_kantor,A.id_h_penjualan,A.nama_bank
				) AS A
				GROUP BY A.nama_bank;
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
		
		function sum_laporan_d_penjualan_produk_isRport($kode_kantor,$dari,$sampai,$cari)
		{
			$query = 
			"
				SELECT
					A.kode_kantor,
					A.id_produk,
					A.kode_produk,
					A.nama_produk,
					COALESCE(B.jumlah,0) AS jumlah,
					COALESCE(B.satuan_jual,0) AS satuan_jual,
					COALESCE(B.harga,0) AS harga,
					COALESCE(B.harga_setelah_diskon,0) AS harga_setelah_diskon
				FROM tb_produk AS A
				LEFT JOIN
				(
					SELECT
						kode_kantor,
						id_produk,
						SUM(jumlah) AS jumlah,
						satuan_jual,
						harga,
						harga_setelah_diskon
					FROM
					(
						SELECT
							A.kode_kantor,
							A.id_produk,
							A.jumlah,
							A.satuan_jual,
							A.harga,
							CASE WHEN A.optr_diskon = '%' THEN
								A.harga - ((A.harga/100) * A.diskon)
							ELSE
								A.harga - A.diskon
							END AS harga_setelah_diskon
						FROM tb_d_penjualan AS A
						INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan  = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
						WHERE A.kode_kantor = '".$kode_kantor."' AND B.sts_penjualan IN ('SELESAI','PEMBAYARAN')
						AND DATE(COALESCE(B.tgl_h_penjualan,'')) BETWEEN '".$dari."' AND '".$sampai."'
						-- AND B.no_faktur ='JKT2020081800002'
					) AS AA
					GROUP BY 
						kode_kantor,
						id_produk,
						satuan_jual,
						harga,
						harga_setelah_diskon
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
				WHERE A.kode_kantor = '".$kode_kantor."' AND A.isReport = '1' ".$cari."
				ORDER BY A.nama_produk ASC;
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
		
		
		function list_h_penjualan_images_saja($cari,$order_by)
		{
			$query = 
			"
				SELECT * FROM tb_h_penjualan AS A 
				LEFT JOIN tb_costumer AS B ON A.id_costumer = B.id_costumer AND A.kode_kantor = B.kode_kantor
				".$cari." ".$order_by."
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