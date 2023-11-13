<?php
	class M_gl_lap_penjualan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_laporan_untuk_fee($kode_kantor,$dari,$sampai,$cari)
		{
			$query = "
					SELECT 
						A.*
						,CASE WHEN A.nama_dokter_konsul = '' THEN
							COALESCE(B.nama_karyawan,'') 
						ELSE
							A.nama_dokter_konsul
						END
						AS nama_karyawan
						,COALESCE(C.kode_produk,'') AS kode_produk
						,COALESCE(C.nama_produk,'') AS nama_produk
						,COALESCE(D.nama_diskon,'') AS nama_diskon
					FROM
					(
						SELECT kode_kantor,sts_penjualan,id_h_penjualan,id_h_diskon,id_dokter,no_faktur,type_h_penjualan,'' AS nama_dokter_konsul,'' AS id_produk,'DOKTER KONSULTASI' AS sebagai,'' AS isProduk,1 AS jumlah,'' AS satuan,0 as harga,tgl_h_penjualan AS tgl FROM tb_h_penjualan AS A WHERE id_dokter <> ''
						UNION ALL
						SELECT kode_kantor,sts_penjualan,id_h_penjualan,id_h_diskon,id_dokter2,no_faktur,type_h_penjualan,'' AS nama_dokter_tindakan,'' AS id_produk,'DOKTER TINDAKAN' AS sebagai, '' AS isProduk,1 AS jumlah,'' AS satuan,0 as harga,tgl_h_penjualan AS tgl FROM tb_h_penjualan AS A WHERE id_dokter2 <> ''
						UNION ALL
						SELECT A.kode_kantor,B.sts_penjualan,A.id_h_penjualan,B.id_h_diskon,A.id_ass,B.no_faktur AS no_faktur,'' AS type_h_penjualan,CONCAT(COALESCE(C.nama_karyawan),' <br/>( ',A.nama_ass,' ) ') AS nama_ass,A.id_produk,'THERAPIST' AS sebagai,A.isProduk,A.jumlah,A.satuan_jual,A.harga,DATE(A.tgl_ins) AS tgl 
						FROM tb_d_penjualan AS A 
						INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
						LEFT JOIN tb_karyawan AS C ON A.kode_kantor = C.kode_kantor AND B.id_dokter2 = C.id_karyawan
						WHERE A.isProduk = 'JASA'
					) AS A
					LEFT JOIN tb_karyawan AS B ON A.kode_kantor = B.kode_kantor AND A.id_dokter = B.id_karyawan AND B.isAktif = 'DITERIMA'
					LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
					LEFT JOIN tb_h_diskon AS D ON A.kode_kantor = D.kode_kantor AND A.id_h_diskon = D.id_h_diskon
					WHERE A.kode_kantor = '".$kode_kantor."' AND sts_penjualan IN ('SELESAI','PEMBAYARAN') 
					AND A.tgl BETWEEN '".$dari."' AND '".$sampai."'
					AND 
					(
						COALESCE(B.nama_karyawan,'') LIKE '%".$cari."%'
						OR A.nama_dokter_konsul LIKE '%".$cari."%'
						OR A.no_faktur LIKE '%".$cari."%'
						OR COALESCE(D.nama_diskon,'') LIKE '%".$cari."%'
						OR COALESCE(C.nama_produk,'') LIKE '%".$cari."%'
					)
					
					-- AND id_h_penjualan = 'OFBDG2020081500001'
					ORDER BY A.tgl DESC, A.id_h_penjualan DESC, A.isProduk ASC;
					
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
		
		function count_laporan_detail($cari)
		{
			$query = "
					SELECT
						COUNT(A.id_d_penjualan) AS JUMLAH
					FROM tb_d_penjualan AS A
					INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
					LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
					LEFT JOIN tb_karyawan AS D ON A.kode_kantor = D.kode_kantor AND COALESCE(B.id_dokter,'') = D.id_karyawan
					LEFT JOIN tb_karyawan AS E ON A.kode_kantor = E.kode_kantor AND COALESCE(B.id_dokter2,'') = E.id_karyawan
					
					".$cari."
					
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
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function count_h_penjualan($cari)
		{
			$query = "
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
							-- WHERE A.kode_kantor = 'CJRT' 
							-- AND A.id_h_penjualan = 'OFCJRT2020061300001'
							".$cari."
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
		
		
		function list_rekam_medis_lengkap($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT
					A.*
					,COALESCE(B.no_karyawan,'') AS no_dokter
					,COALESCE(B.nama_karyawan,'') AS nama_dokter
					,COALESCE(B.pnd,'') AS pnd
					,COALESCE(B.avatar_url,'') AS avatar_url_dokter
					,COALESCE(B.avatar,'') AS avatar_dokter
					
					,COALESCE(C.nama_karyawan,'') AS nama_karyawan
					,DATE_FORMAT(A.tgl_h_penjualan, '%d %M %Y') AS format_tgl_h_penjualan
					,TIMESTAMPDIFF(DAY,DATE(A.tgl_h_penjualan),DATE(NOW())) AS selisih_hari
					
					
					,COALESCE(E.no_karyawan,'') AS NO_DOKTER_TINDAKAN
					,COALESCE(E.nama_karyawan,'') AS NAMA_DOKTER_TINDAKAN
					
					,COALESCE(F.no_karyawan,'') AS NO_ASS_1
					,COALESCE(F.nama_karyawan,'') AS NAMA_ASS_1
					
					,COALESCE(G.no_karyawan,'') AS NO_ASS_2
					,COALESCE(G.nama_karyawan,'') AS NAMA_ASS_2
					
					,
						
						CASE
						WHEN (A.waktu_mulai_pemeriksaan != '0000-00-00 00:00:00') AND (A.waktu_selesai_pemeriksaan != '0000-00-00 00:00:00') THEN
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
					, 
						CASE 
						WHEN (A.type_h_penjualan <> 'PENJUALAN LANGSUNG') AND (A.sts_penjualan = 'SELESAI') THEN
							TIMESTAMPDIFF
							(
								MINUTE,
								A.waktu_mulai_tindakan,
								A.tgl_updt
							) 
						ELSE
							0
						END
						AS LAMA_TINDAKAN
					
					
				FROM tb_h_penjualan AS A
				LEFT JOIN tb_karyawan AS B ON A.kode_kantor = B.kode_kantor AND A.id_dokter = B.id_karyawan
				LEFT JOIN tb_karyawan AS C ON A.kode_kantor = C.kode_kantor AND A.id_karyawan = C.id_karyawan

				LEFT JOIN tb_karyawan AS E ON A.kode_kantor = E.kode_kantor AND A.id_dokter2 = E.id_karyawan
				LEFT JOIN tb_karyawan AS F ON A.kode_kantor = F.kode_kantor AND A.id_ass_dok = F.id_karyawan
				LEFT JOIN tb_karyawan AS G ON A.kode_kantor = F.kode_kantor AND A.id_ass_dok2 = G.id_karyawan


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
		
		function list_rekam_medis($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT
					A.*
					,COALESCE(B.no_karyawan,'') AS no_dokter
					,COALESCE(B.nama_karyawan,'') AS nama_dokter
					,COALESCE(B.pnd,'') AS pnd
					,COALESCE(B.avatar_url,'') AS avatar_url_dokter
					,COALESCE(B.avatar,'') AS avatar_dokter
					,COALESCE(C.nama_karyawan,'') AS nama_karyawan
					,DATE_FORMAT(A.tgl_h_penjualan, '%d %M %Y') AS format_tgl_h_penjualan
					,TIMESTAMPDIFF(DAY,DATE(A.tgl_h_penjualan),DATE(NOW())) AS selisih_hari
				FROM tb_h_penjualan AS A
				LEFT JOIN tb_karyawan AS B ON A.kode_kantor = B.kode_kantor AND A.id_dokter = B.id_karyawan
				LEFT JOIN tb_karyawan AS C ON A.kode_kantor = C.kode_kantor AND A.id_karyawan = C.id_karyawan
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
		
		function count_rekam_medis($cari)
		{
			$query =
			"
				SELECT
					COUNT(A.id_h_penjualan) AS JUMLAH
				FROM tb_h_penjualan AS A
				LEFT JOIN tb_karyawan AS B ON A.kode_kantor = B.kode_kantor AND A.id_dokter = B.id_karyawan
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
		
		function list_status_karyawan_therapist_tersedia($cari,$limit,$offset)
		{
			$query = 
			"
				SELECT A.id_karyawan,A.no_karyawan,A.nik_karyawan,A.nama_karyawan,A.avatar, A.avatar_url
					,DATEDIFF(DATE(NOW()),COALESCE(A.tgl_diterima,'')) AS lama_kerja
					
					,COALESCE( COALESCE(B.id_h_penjualan,C.id_h_penjualan),'') AS id_h_penjualan
					,COALESCE( COALESCE(B.id_ass_dok,C.id_ass_dok2),'') AS id_ass_dok
					,COALESCE( COALESCE(B.no_faktur,C.no_faktur),'') AS no_faktur_ass
					,COALESCE( COALESCE(B.no_costmer,C.no_costmer),'') AS no_costumer
					,COALESCE( COALESCE(B.nama_costumer,C.nama_costumer),'') AS nama_costumer
					,COALESCE( COALESCE(B.sts_penjualan,C.sts_penjualan),'') AS sts_penjualan
					,COALESCE( COALESCE(B.id_dokter2,C.id_dokter2),'') AS id_dokter
					,COALESCE( COALESCE(B.type_h_penjualan,C.type_h_penjualan),'') AS type_h_penjualan
					,
					(
						SELECT DATEDIFF(DATE(NOW()),COALESCE(tgl_diterima,'')) AS lama_dokter2 FROM tb_karyawan WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_karyawan = COALESCE( COALESCE(B.id_dokter2,C.id_dokter2),'')
					) AS lama_dokter2
					
					,COALESCE(B.id_h_penjualan,'') AS id_h_penjualan1
					,COALESCE(B.id_ass_dok,'') AS id_ass_dok1
					,COALESCE(B.no_faktur,'') AS no_faktur_ass1
					,COALESCE(B.no_costmer,'') AS no_costumer1
					,COALESCE(B.nama_costumer,'') AS nama_costumer1
					,COALESCE(B.id_dokter2,'') AS id_dokter1
					
					,COALESCE(C.id_h_penjualan,'') AS id_h_penjualan2
					,COALESCE(C.id_ass_dok2,'') AS id_ass_dok2
					,COALESCE(C.no_faktur,'') AS no_faktur_ass2
					,COALESCE(C.no_costmer,'') AS no_costumer2
					,COALESCE(C.nama_costumer,'') AS nama_costumer2
					,COALESCE(C.id_dokter2,'') AS id_dokter2
				FROM tb_karyawan AS A
				LEFT JOIN
				(
					SELECT id_h_penjualan, kode_kantor, no_faktur, no_costmer, nama_costumer,id_dokter2 , id_ass_dok , sts_penjualan, type_h_penjualan
					FROM tb_h_penjualan
					WHERE sts_penjualan = 'PEMBAYARAN' AND (no_faktur LIKE '%%' OR nama_costumer LIKE '%%')
					ANd DATE(tgl_h_penjualan) = DATE(NOW())
					-- HAVING MAX(tgl_ins)
					ORDER BY tgl_ins DESC
					-- LIMIT 0,1
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_karyawan = B.id_ass_dok
				LEFT JOIN
				(
					SELECT id_h_penjualan, kode_kantor, no_faktur, no_costmer, nama_costumer,id_dokter2 , id_ass_dok2 , sts_penjualan, type_h_penjualan
					FROM tb_h_penjualan
					WHERE sts_penjualan = 'PEMBAYARAN' AND (no_faktur LIKE '%%' OR nama_costumer LIKE '%%')
					ANd DATE(tgl_h_penjualan) = DATE(NOW())
					-- HAVING MAX(tgl_ins)
					ORDER BY tgl_ins DESC
					-- LIMIT 0,1
				) AS C ON A.kode_kantor = C.kode_kantor AND A.id_karyawan = C.id_ass_dok2
				
				".$cari."
				
				-- WHERE A.id_karyawan = 'KRY2020011000002' 
				-- AND A.isDokter = 'THERAPIST'
				ORDER BY A.nama_karyawan DESC LIMIT ".$offset.",".$limit."
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
		
		function list_status_karyawan_dokter_tersedia($cari,$limit,$offset)
		{
			$query = 
			"
				SELECT A.id_karyawan,A.no_karyawan,A.nik_karyawan,A.nama_karyawan,A.avatar, A.avatar_url
					,DATEDIFF(DATE(NOW()),COALESCE(A.tgl_diterima,'')) AS lama_kerja
					
					,COALESCE( COALESCE(B.id_h_penjualan,''),'') AS id_h_penjualan
					,COALESCE( COALESCE(B.id_ass_dok,''),'') AS id_ass_dok
					,COALESCE( COALESCE(B.no_faktur,''),'') AS no_faktur_ass
					,COALESCE( COALESCE(B.no_costmer,''),'') AS no_costumer
					,COALESCE( COALESCE(B.nama_costumer,''),'') AS nama_costumer
					,COALESCE( COALESCE(B.sts_penjualan,''),'') AS sts_penjualan
					,COALESCE( COALESCE(B.id_dokter2,''),'') AS id_dokter
					,COALESCE( COALESCE(B.type_h_penjualan,''),'') AS type_h_penjualan
					
					
				FROM tb_karyawan AS A
				LEFT JOIN
				(
					SELECT id_h_penjualan, kode_kantor, no_faktur, no_costmer, nama_costumer,id_dokter2 , id_ass_dok , sts_penjualan, type_h_penjualan
					FROM tb_h_penjualan
					WHERE sts_penjualan = 'PEMBAYARAN' AND (no_faktur LIKE '%%' OR nama_costumer LIKE '%%')
					ANd DATE(tgl_h_penjualan) = DATE(NOW())
					-- HAVING MAX(tgl_ins)
					ORDER BY tgl_ins DESC
					-- LIMIT 0,1
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_karyawan = B.id_dokter2
				
				
				".$cari."
				
				ORDER BY A.nama_karyawan DESC LIMIT ".$offset.",".$limit."
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
		
		function count_status_karyawan_tersedia_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(A.id_karyawan) AS JUMLAH FROM tb_karyawan AS A ".$cari);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
	
		
		function sum_laporan_h_penjualan($cari)
		{
			$query = 
			"
				SELECT
					SUM(A3.SUBTOTAL) AS SUBTOTAL
					,SUM(A3.SUBTOTAL_ALL) AS SUBTOTAL_ALL
					,SUM(A3.BAYAR_CASH) AS BAYAR_CASH
					,SUM(A3.BAYAR_BANK) AS BAYAR_BANK
				FROM
				(
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
							END AS BAYAR_CASH
						
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
						
						,COALESCE(D.no_costumer,'') AS NO_PASIEN
						,COALESCE(D.nama_lengkap,'') AS NAMA_PASIEN
						,COALESCE(D.avatar,'') AS AVATAR_PASIEN
						,COALESCE(D.avatar_url,'') AS AVATAR_URL_PASIEN
						
						
						,COALESCE(E.no_karyawan,'') AS NO_KARYAWAN
						,COALESCE(E.nama_karyawan,'') AS NAMA_KARYAWAN
						,COALESCE(E.avatar,'') AS AVATAR_DOKTER
						,COALESCE(E.avatar_url,'') AS AVATAR_URL_DOKTER
					FROM tb_h_penjualan AS A
					INNER JOIN
					(
						SELECT kode_kantor, id_h_penjualan, SUM(jumlah * (harga + diskon)) AS SUBTOTAL 
						FROM tb_d_penjualan
						GROUP BY kode_kantor, id_h_penjualan
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
							GROUP BY A.kode_kantor,A.id_h_penjualan,A.id_bank
						) AS AA
						GROUP BY AA.kode_kantor, AA.id_h_penjualan
					) AS C ON A.kode_kantor = C.kode_kantor AND A.id_h_penjualan = C.id_h_penjualan
					LEFT JOIN tb_costumer AS D ON A.kode_kantor = D.kode_kantor AND A.id_costumer = D.id_costumer
					LEFT JOIN tb_karyawan AS E ON A.kode_kantor = E.kode_kantor AND A.id_dokter = E.id_karyawan
					
					".$cari."
					-- WHERE A.kode_kantor = 'CJRT' AND A.sts_penjualan = 'SELESAI' AND DATE(A.tgl_h_penjualan) BETWEEN '2020-03-17' AND '2020-03-19'
				) AS A3
				
				
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
	
		function sum_laporan_d_penjualan_produk_isRport($kode_kantor,$dari,$sampai)
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
				WHERE A.kode_kantor = '".$kode_kantor."' AND A.isReport = '1'
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
					,COALESCE(D.alamat_rumah_sekarang,'') AS alamat_costumer
					,COALESCE(D.avatar,'') AS AVATAR_PASIEN
					,COALESCE(D.avatar_url,'') AS AVATAR_URL_PASIEN
					
					
					,COALESCE(E.no_karyawan,'') AS NO_KARYAWAN
					,COALESCE(E.nama_karyawan,'') AS NAMA_KARYAWAN
					,COALESCE(E.avatar,'') AS AVATAR_DOKTER
					,COALESCE(E.avatar_url,'') AS AVATAR_URL_DOKTER
					,CONCAT
					(
						COALESCE(F.provinsi,''),' ',
						COALESCE(F.kabkota,''),' ',
						COALESCE(F.kecamatan,''),' ',
						COALESCE(F.desa,''),' (',
						COALESCE(F.kodepos,''),') ',
						COALESCE(F.detail_alamat,''),' /(',
						COALESCE(F.telepon,''),') '
					) AS ALAMAT_KIRIM
					,COALESCE(F.provinsi,'') AS cek_provinsi
					,COALESCE(G.tgl_terima,'') AS tgl_terima
					,COALESCE(G.diterima_oleh,'') AS diterima_oleh
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
				LEFT JOIN tb_alamat AS F ON A.id_gedung = F.id_alamat
				LEFT JOIN 
				(
				 SELECT no_surat_jalan,diterima_oleh,MAX(tgl_ins) AS tgl_terima FROM tb_h_penerimaan GROUP BY no_surat_jalan,diterima_oleh
				) AS G ON A.no_faktur = G.no_surat_jalan
				
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
		
		function count_laporan_h_penjualan($cari)
		{
			$query = 
			"
				SELECT 
					COUNT(A.id_h_penjualan) AS JUMLAH
				FROM tb_h_penjualan AS A
				-- INNER JOIN
				LEFT JOIN
				(
					SELECT kode_kantor, id_h_penjualan, SUM(jumlah * (harga + diskon)) AS SUBTOTAL 
					FROM tb_d_penjualan
					GROUP BY kode_kantor, id_h_penjualan
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
				LEFT JOIN tb_d_penjualan_bayar AS C ON A.kode_kantor = C.kode_kantor AND A.id_h_penjualan = C.id_h_penjualan
				LEFT JOIN tb_costumer AS D ON A.kode_kantor = D.kode_kantor AND A.id_costumer = D.id_costumer
				LEFT JOIN tb_karyawan AS E ON A.kode_kantor = E.kode_kantor AND A.id_dokter = E.id_karyawan
				
				".$cari."
				-- WHERE A.kode_kantor = 'CJRT' AND A.sts_penjualan = 'SELESAI' AND DATE(A.tgl_h_penjualan) BETWEEN '2020-03-17' AND '2020-03-19'
				
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
	
		
		function list_d_penjualan($cari_deft,$cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT
					AAA.id_produk
					,COALESCE(BBB.kode_produk,'') AS KODE_PRODUK
					,COALESCE(BBB.nama_produk,'') AS NAMA_PRODUK
					,COALESCE(BBB.isProduk,'') AS ISPRODUK
					,AAA.CNT
					,AAA.JUMLAH
					,AAA.SATUAN
					,AAA.HARGA
					,(AAA.jumlah * AAA.harga) AS SUBTOTAL
				FROM
				(
					SELECT 
						AA.kode_kantor
						,AA.id_produk
						,COUNT(AA.id_d_penjualan) AS CNT
						,SUM(AA.jumlah_konversi) AS JUMLAH
						,AA.kode_satuan_deft AS SATUAN
						,AA.harga_konversi AS HARGA
					FROM
					(
						SELECT 
							A.id_d_penjualan
							,A.id_produk
							,CASE
								WHEN A.status_konversi = '*' THEN
									jumlah * konversi
								ELSE
									jumlah / konversi
								END AS jumlah_konversi
							,CASE
								WHEN A.status_konversi = '*' THEN
									harga / konversi
								ELSE
									harga * konversi
								END AS harga_konversi
							,COALESCE(C.id_satuan,'') AS id_satuan_deft
							,COALESCE(D.kode_satuan,'') AS kode_satuan_deft
							,A.kode_kantor
						FROM tb_d_penjualan AS A
						INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan 
						AND B.sts_penjualan IN ('SELESAI','PEMBAYARAN')
						-- AND B.sts_penjualan = 'SELESAI'
						LEFT JOIN tb_satuan_konversi AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk AND C.besar_konversi = 1 AND C.status = '*'
						LEFT JOIN tb_satuan AS D ON A.kode_kantor = D.kode_kantor AND C.id_satuan = D.id_satuan
						-- WHERE A.kode_kantor = 'CJRT' AND A.id_h_penjualan IN ('OFCJRT2020032600001','OFCJRT2020032700001')
						".$cari_deft."
					) AS AA
					GROUP BY 
						AA.kode_kantor
						,AA.id_produk
						,AA.kode_satuan_deft
						,AA.harga_konversi
				) AS AAA
				LEFT JOIN tb_produk AS BBB ON AAA.kode_kantor = BBB.kode_kantor AND AAA.id_produk = BBB.id_produk
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
		
		function count_d_penjualan($cari_deft,$cari)
		{
			$query =
			"
				SELECT
					COUNT(AAA.id_produk) AS JUMLAH
				FROM
				(
					SELECT 
						AA.kode_kantor
						,AA.id_produk
						,COUNT(AA.id_d_penjualan) AS CNT
						,SUM(AA.jumlah_konversi) AS JUMLAH
						,AA.kode_satuan_deft AS SATUAN
						,AA.harga_konversi AS HARGA
					FROM
					(
						SELECT 
							A.id_d_penjualan
							,A.id_produk
							,CASE
								WHEN A.status_konversi = '*' THEN
									jumlah * konversi
								ELSE
									jumlah / konversi
								END AS jumlah_konversi
							,CASE
								WHEN A.status_konversi = '*' THEN
									harga / konversi
								ELSE
									harga * konversi
								END AS harga_konversi
							,COALESCE(C.id_satuan,'') AS id_satuan_deft
							,COALESCE(D.kode_satuan,'') AS kode_satuan_deft
							,A.kode_kantor
						FROM tb_d_penjualan AS A
						INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan 
						-- AND B.sts_penjualan IN ('SELESAI','PEMBAYARAN')
						AND B.sts_penjualan = 'SELESAI'
						LEFT JOIN tb_satuan_konversi AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk AND C.besar_konversi = 1 AND C.status = '*'
						LEFT JOIN tb_satuan AS D ON A.kode_kantor = D.kode_kantor AND C.id_satuan = D.id_satuan
						-- WHERE A.kode_kantor = 'CJRT' AND A.id_h_penjualan IN ('OFCJRT2020032600001','OFCJRT2020032700001')
						".$cari_deft."
					) AS AA
					GROUP BY 
						AA.kode_kantor
						,AA.id_produk
						,AA.kode_satuan_deft
						,AA.harga_konversi
				) AS AAA
				LEFT JOIN tb_produk AS BBB ON AAA.kode_kantor = BBB.kode_kantor AND AAA.id_produk = BBB.id_produk
				".$cari."
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
		
		function sum_d_penjualan_OLD($cari_deft,$cari)
		{
			$query =
			"
				SELECT
					COUNT(AAAA.id_produk) AS CNT
					,SUM(AAAA.SUBTOTAL) AS TOTAL
					,CASE WHEN AAAA.ISPRODUK = '' THEN '-' ELSE AAAA.ISPRODUK END AS ISPRODUK
				FROM
				(
					SELECT
						AAA.id_produk
						,COALESCE(BBB.kode_produk,'') AS KODE_PRODUK
						,COALESCE(BBB.nama_produk,'') AS NAMA_PRODUK
						,COALESCE(BBB.isProduk,'') AS ISPRODUK
						,AAA.CNT
						,AAA.JUMLAH
						,AAA.SATUAN
						,AAA.HARGA
						,(AAA.jumlah * AAA.harga) AS SUBTOTAL
					FROM
					(
						SELECT 
							AA.kode_kantor
							,AA.id_produk
							,COUNT(AA.id_d_penjualan) AS CNT
							,SUM(AA.jumlah_konversi) AS JUMLAH
							,AA.kode_satuan_deft AS SATUAN
							,(AA.harga_konversi - AA.DISKON_FIX) AS HARGA
						FROM
						(
							SELECT 
								A.id_d_penjualan
								,A.id_produk
								,
									COALESCE(
									CASE
									WHEN A.status_konversi = '*' THEN
										jumlah * konversi
									ELSE
										jumlah / konversi
									END 
									,1)
									AS jumlah_konversi
								,CASE
									WHEN A.status_konversi = '*' THEN
										CASE WHEN konversi = 0 THEN
											harga / 1
										ELSE
											harga / konversi
										END
									ELSE
										CASE WHEN konversi = 0 THEN
											harga * 1
										ELSE
											harga * konversi
										END
										
									END AS harga_konversi
								,
								CASE WHEN A.optr_diskon = '%' THEN
									(A.harga/100) * A.diskon
								ELSE
									A.diskon
								END AS DISKON_FIX
								,COALESCE(C.id_satuan,'') AS id_satuan_deft
								,COALESCE(D.kode_satuan,'') AS kode_satuan_deft
								,A.kode_kantor
							FROM tb_d_penjualan AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan 
							AND B.sts_penjualan IN ('SELESAI','PEMBAYARAN')
							-- AND B.sts_penjualan = 'SELESAI'
							LEFT JOIN tb_satuan_konversi AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk AND C.besar_konversi = 1 AND C.status = '*'
							LEFT JOIN tb_satuan AS D ON A.kode_kantor = D.kode_kantor AND C.id_satuan = D.id_satuan
							-- WHERE A.kode_kantor = 'CJRT' AND A.id_h_penjualan IN ('OFCJRT2020032600001','OFCJRT2020032700001')
							".$cari_deft."
						) AS AA
						GROUP BY 
							AA.kode_kantor
							,AA.id_produk
							,AA.kode_satuan_deft
							,AA.harga_konversi
							,AA.DISKON_FIX
					) AS AAA
					LEFT JOIN tb_produk AS BBB ON AAA.kode_kantor = BBB.kode_kantor AND AAA.id_produk = BBB.id_produk
					".$cari."
				) AS AAAA
				GROUP BY AAAA.ISPRODUK
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
		
		function sum_d_penjualan($cari_deft,$cari)
		{
			$query =
			"
				SELECT
					COUNT(AAAA.id_produk) AS CNT
					,SUM(AAAA.SUBTOTAL) AS TOTAL
					,CASE WHEN AAAA.ISPRODUK = '' THEN '-' ELSE AAAA.ISPRODUK END AS ISPRODUK
				FROM
				(
					SELECT
						AAA.id_produk
						,COALESCE(BBB.kode_produk,'') AS KODE_PRODUK
						,COALESCE(BBB.nama_produk,'') AS NAMA_PRODUK
						,COALESCE(BBB.isProduk,'') AS ISPRODUK
						,AAA.CNT
						,AAA.JUMLAH
						,AAA.SATUAN
						,AAA.HARGA
						,(AAA.jumlah * AAA.harga) AS SUBTOTAL
					FROM
					(
						SELECT 
							AA.kode_kantor
							,AA.id_produk
							,COUNT(AA.id_d_penjualan) AS CNT
							,SUM(AA.jumlah_konversi) AS JUMLAH
							,AA.kode_satuan_deft AS SATUAN
							,(AA.harga_konversi - AA.DISKON_FIX) AS HARGA
						FROM
						(
							SELECT 
								A.id_d_penjualan
								,A.id_produk
								,
									COALESCE(
									CASE
									WHEN A.status_konversi = '*' THEN
										jumlah * konversi
									ELSE
										jumlah / konversi
									END 
									,1)
									AS jumlah_konversi
								,CASE
									WHEN A.status_konversi = '*' THEN
										CASE WHEN konversi = 0 THEN
											harga / 1
										ELSE
											harga / konversi
										END
									ELSE
										CASE WHEN konversi = 0 THEN
											harga * 1
										ELSE
											harga * konversi
										END
										
									END AS harga_konversi
								,
								CASE WHEN A.optr_diskon = '%' THEN
									(A.harga/100) * A.diskon
								ELSE
									A.diskon
								END AS DISKON_FIX
								,COALESCE(C.id_satuan,'') AS id_satuan_deft
								,COALESCE(D.kode_satuan,'') AS kode_satuan_deft
								,A.kode_kantor
							FROM tb_d_penjualan AS A
							INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan 
							AND B.sts_penjualan IN ('SELESAI','PEMBAYARAN')
							LEFT JOIN tb_satuan_konversi AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk AND C.besar_konversi = 1 AND C.status = '*'
							LEFT JOIN tb_satuan AS D ON A.kode_kantor = D.kode_kantor AND C.id_satuan = D.id_satuan
							".$cari_deft."
						) AS AA
						GROUP BY 
							AA.kode_kantor
							,AA.id_produk
							,AA.kode_satuan_deft
							,AA.harga_konversi
							,AA.DISKON_FIX
					) AS AAA
					LEFT JOIN tb_produk AS BBB ON AAA.kode_kantor = BBB.kode_kantor AND AAA.id_produk = BBB.id_produk
					".$cari."
				) AS AAAA
				GROUP BY AAAA.ISPRODUK
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
		
		
		function sum_d_penjualan_pusat_lap_pendapatan($cari_deft,$cari)
		{
			$query =
			"
				SELECT
					COUNT(no_faktur) AS CNT,
					ROUND(SUM(jumlah * harga),0) AS TOTAL,
					isProduk AS ISPRODUK
				FROM
				(
					SELECT COALESCE(B.no_faktur,'') AS no_faktur,A.*,(A.harga_konversi - A.DISKON_FIX) AS harga,COALESCE(B.pajak,0) AS biaya
					FROM
					(
						SELECT 
							A.kode_kantor
							,A.isProduk
							,A.id_h_penjualan
							,A.id_produk
							,A.id_d_penerimaan
							,A.jumlah
						,CASE
							WHEN A.status_konversi = '*' THEN
								CASE WHEN A.konversi = 0 THEN
									A.harga / 1
								ELSE
									A.harga / A.konversi
								END
							ELSE
								CASE WHEN A.konversi = 0 THEN
									A.harga * 1
								ELSE
									A.harga * A.konversi
								END
								
							END AS harga_konversi
						,CASE 
							WHEN A.optr_diskon = '%' THEN
								(A.harga/100) * A.diskon
							ELSE
								A.diskon
							END AS DISKON_FIX
						FROM tb_d_penjualan AS A 
						-- WHERE id_h_penjualan = 'ONOLN2020091400009';
					) AS A
					INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor
					".$cari_deft."

				) AS AA
				GROUP BY isProduk
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
	
		
		function list_d_pengeluaran_produk($cari,$order_by)
		{
			$query = 
			"
				SELECT
					A.*
					,COALESCE(B.no_faktur,'') AS no_faktur
					,COALESCE(B.type_h_penjualan,'') AS type_h_penjualan
					,COALESCE(B.ket_penjualan,'') AS ket_penjualan
					,COALESCE(B.no_costmer,'') AS no_costumer
					,COALESCE(B.nama_costumer,'') AS nama_costumer
					,COALESCE(B.nama_kat_costumer,'') AS nama_kat_costumer
					,COALESCE(DATE(B.tgl_h_penjualan),'') AS tgl_h_penjualan
					,COALESCE(C.kode_produk,'') AS kode_produk
					,COALESCE(C.nama_produk,'') AS nama_produk
					,COALESCE(D.no_karyawan,'') AS NO_DOKTER_KONSUL
					,COALESCE(D.nama_karyawan,'') AS NAMA_DOKTER_KONSUL
					,COALESCE(E.no_karyawan,'') AS NO_DOKTER_TINDAKAN
					,COALESCE(E.nama_karyawan,'') AS NAMA_DOKTER_TINDAKAN
				FROM tb_d_penjualan AS A
				INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan AND B.sts_penjualan = 'SELESAI'
				LEFT JOIN tb_produk AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk
				LEFT JOIN tb_karyawan AS D ON A.kode_kantor = D.kode_kantor AND B.id_dokter = D.id_karyawan
				LEFT JOIN tb_karyawan AS E ON A.kode_kantor = E.kode_kantor AND B.id_dokter2 = E.id_karyawan
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
		
		
		function list_d_laporan_fee($cari,$order_by)
		{
			$query = 
			"
				SELECT
					A.id_d_penjualan_fee
					,A.id_h_penjualan
					,A.id_h_fee
					,A.id_karyawan
					,A.group_by
					,A.nama_fee
					,A.nominal_fee
					,A.tgl_ins
					,A.kode_kantor
					,COALESCE(B.no_faktur,'') AS NO_FAKTUR
					,COALESCE(B.no_costmer,'') AS NO_COST
					,COALESCE(B.nama_costumer,'') AS NAMA_COST
					,DATE(COALESCE(B.tgl_h_penjualan,'')) AS TGL_H_PENJUALAN
					,COALESCE(C.nama_h_fee,'') AS NAMA_H_FEE
					,COALESCE(C.jenis_poli,'') AS POLI
					,COALESCE(C.isPasienBaru,'') AS PASIEN_BARU
					,COALESCE(C.isMembuatResep,'') AS IS_MEMBUAT_RESEP
					
					,COALESCE(E.jasa,'') AS JASA
					,COALESCE(E.produk,'') AS PRODUK
					,COALESCE(E.nominal_transaksi,'0') AS NOMINAL_TR
					
					,COALESCE(D.no_karyawan,'') AS NO_KARYAWAN
					,COALESCE(D.nama_karyawan,'') AS NAMA_KARYAWAN
					,COALESCE(D.isDokter,'') AS ISDOKTER
					,COALESCE(D.avatar,'') AS AVATAR
					,COALESCE(D.avatar_url,'') AS AVATAR_URL

				FROM tb_d_penjualan_fee AS A
				INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor AND B.sts_penjualan = 'SELESAI'
				LEFT JOIN tb_h_fee AS C ON A.id_h_fee = C.id_h_fee AND A.kode_kantor = C.kode_kantor
				LEFT JOIN tb_karyawan AS D ON A.id_karyawan = D.id_karyawan AND A.kode_kantor = D.kode_kantor
				LEFT JOIN tb_h_penjualan_catat_untuk_fee AS E ON A.id_h_penjualan = E.id_h_penjualan AND A.kode_kantor = E.kode_kantor
				-- WHERE A.kode_kantor = '' AND COALESCE(DATE(D.tgl_h_penjualan,'')) BETWEEN '' AND ''
				".$cari."
				
				-- ORDER BY COALESCE(B.no_faktur,'') DESC;
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
		
		
		function list_akumulasi_laporan_fee($cari,$order_by)
		{
			$query = 
			"
				SELECT
					A3.ID_KARYAWAN
					,A3.NAMA_KARYAWAN
					,A3.ISDOKTER
					,SUM(A3.PASIEN_LAMA) AS PASIEN_LAMA
					,SUM(A3.PASIEN_BARU) AS PASIEN_BARU
					,SUM(A3.IS_MEMBUAT_RESEP) AS RESEP
					,COUNT(A3.nominal_fee) AS CNT
					,SUM(A3.nominal_fee) AS TOTAL
				FROM
				(
					SELECT
						A2.id_karyawan AS ID_KARYAWAN
						,A2.NAMA_KARYAWAN
						,CASE
							WHEN A2.ISDOKTER = '0' THEN
								'KARYAWAN UMUM'
							WHEN A2.ISDOKTER = '' THEN
								'KARYAWAN UMUM'
							ELSE
								A2.ISDOKTER
							END AS ISDOKTER
						,CASE 
							WHEN A2.PASIEN_BARU = 'YA' THEN
								1
							ELSE
								0
							END AS PASIEN_BARU
						,CASE 
							WHEN A2.PASIEN_BARU = 'TIDAK' THEN
								1
							ELSE
								0
							END AS PASIEN_LAMA
						,CASE
							WHEN A2.IS_MEMBUAT_RESEP = 'YA' THEN
								1
							ELSE
								0
							END AS IS_MEMBUAT_RESEP
						,A2.nominal_fee
					FROM
					(
						
						SELECT
							A.id_d_penjualan_fee
							,A.id_h_penjualan
							,A.id_h_fee
							,A.id_karyawan
							,A.group_by
							,A.nama_fee
							,A.nominal_fee
							,A.tgl_ins
							,A.kode_kantor
							,COALESCE(B.no_faktur,'') AS NO_FAKTUR
							,COALESCE(B.no_costmer,'') AS NO_COST
							,COALESCE(B.nama_costumer,'') AS NAMA_COST
							,COALESCE(C.nama_h_fee,'') AS NAMA_H_FEE
							,COALESCE(C.jenis_poli,'') AS POLI
							,COALESCE(C.isPasienBaru,'') AS PASIEN_BARU
							,COALESCE(C.isMembuatResep,'') AS IS_MEMBUAT_RESEP
							,COALESCE(D.no_karyawan,'') AS NO_KARYAWAN
							,COALESCE(D.nama_karyawan,'') AS NAMA_KARYAWAN
							,COALESCE(D.isDokter,'') AS ISDOKTER
							,COALESCE(D.avatar,'') AS AVATAR
							,COALESCE(D.avatar_url,'') AS AVATAR_URL

						FROM tb_d_penjualan_fee AS A
						INNER JOIN tb_h_penjualan AS B ON A.id_h_penjualan = B.id_h_penjualan AND A.kode_kantor = B.kode_kantor AND B.sts_penjualan = 'SELESAI'
						LEFT JOIN tb_h_fee AS C ON A.id_h_fee = C.id_h_fee AND A.kode_kantor = C.kode_kantor
						LEFT JOIN tb_karyawan AS D ON A.id_karyawan = D.id_karyawan AND A.kode_kantor = D.kode_kantor
						".$cari."
					) AS A2
				) AS A3
				GROUP BY A3.ID_KARYAWAN, A3.NAMA_KARYAWAN,A3.ISDOKTER
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
		
		function ubah_h_penjualan_sts_saja($kode_kantor,$id_h_penjualan,$status)
		{
			$strquery=
			"
				UPDATE tb_h_penjualan SET sts_penjualan = '".$status."' WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_h_penjualan_sts_isApprove_ubah($kode_kantor,$id_h_penjualan,$status)
		{
			$strquery=
			"
				UPDATE tb_h_penjualan SET isApprove = ".$status." WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_berita_acara_with_img($kode_kantor,$id_h_penjualan,$ba_file,$ba_url,$petugas_salah,$tgl_salah,$jam_salah,$ket_salah)
		{
			$strquery=
			"
				UPDATE tb_h_penjualan SET 
					ba_file = '".$ba_file."'
					,ba_url = '".$ba_url."'
					,petugas_salah = '".$petugas_salah."'
					,tgl_salah = '".$tgl_salah."'
					,jam_salah = '".$jam_salah."'
					,ket_salah = '".$ket_salah."'
					,tgl_tempo = DATE(NOW())
				WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function ubah_berita_acara_no_img($kode_kantor,$id_h_penjualan,$petugas_salah,$tgl_salah,$jam_salah,$ket_salah)
		{
			$strquery=
			"
				UPDATE tb_h_penjualan SET 
					petugas_salah = '".$petugas_salah."'
					,tgl_salah = '".$tgl_salah."'
					,jam_salah = '".$jam_salah."'
					,ket_salah = '".$ket_salah."'
					,tgl_tempo = DATE(NOW())
				WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function cek_apakah_perubahan_pertama($kode_kantor,$id_h_penjualan)
		{
			$query = 
			"
				SELECT * FROM tb_d_penjualan_backup WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
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
		
		function simpan_backup_penjualan($kode_kantor,$id_h_penjualan)
		{
			$strquery=
			"
				INSERT INTO tb_d_penjualan_backup (id_d_penjualan,id_h_penjualan,id_produk,isProduk,satuan,jumlah,diskon,optr_diskon,harga,kode_kantor)
				SELECT id_d_penjualan,id_h_penjualan,id_produk,isProduk,satuan_jual,jumlah,diskon,optr_diskon,harga,kode_kantor FROM tb_d_penjualan WHERE kode_kantor = '".$kode_kantor."' AND id_h_penjualan = '".$id_h_penjualan."'
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
	
		function list_ba_penjualan_bck($kode_kantor,$id_h_penjualan)
		{
			$query = 
			"
				SELECT
					'ORI' AS STS
					,A.id_h_penjualan
					,A.id_d_penjualan
					,COALESCE(B.kode_produk,'') AS kode_produk 
					,COALESCE(B.nama_produk,'') AS nama_produk
					,A.jumlah
					,A.satuan AS satuan_jual
					,CONCAT(A.diskon,' ',A.optr_diskon) AS diskon
					,A.harga
					,A.kode_kantor
				FROM tb_d_penjualan_backup AS A LEFT JOIN tb_produk AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor
				WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_h_penjualan = '".$id_h_penjualan."'
				ORDER BY A.isProduk ASC, COALESCE(B.nama_produk,'') ASC
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
		
		function list_ba_penjualan($kode_kantor,$id_h_penjualan)
		{
			$query = 
			"
				SELECT
					'ORI' AS STS
					,A.id_h_penjualan
					,A.id_d_penjualan
					,COALESCE(B.kode_produk,'') AS kode_produk 
					,COALESCE(B.nama_produk,'') AS nama_produk
					,A.jumlah
					,A.satuan_jual
					,CONCAT(A.diskon,' ',A.optr_diskon) AS diskon
					,A.harga
					,A.kode_kantor
				FROM tb_d_penjualan AS A LEFT JOIN tb_produk AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor
				WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_h_penjualan = '".$id_h_penjualan."'
				ORDER BY A.isProduk ASC, COALESCE(B.nama_produk,'') ASC
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