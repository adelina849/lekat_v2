<?php
	class M_gl_costumer extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function get_prov($cari)
		{
			$query = "
				SELECT * FROM wilayah_2020 WHERE LENGTH(kode) = '2' ".$cari.";
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
		
		function get_kabkot($cari)
		{
			$query = "
				SELECT * FROM wilayah_2020 WHERE LENGTH(kode) = '5' ".$cari.";
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
		
		function get_kec($cari)
		{
			$query = "
				SELECT * FROM wilayah_2020 WHERE LENGTH(kode) = '8' ".$cari.";
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
		
		function get_desa($cari)
		{
			$query = "
				SELECT * FROM wilayah_2020 WHERE LENGTH(kode) = '13' ".$cari.";
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
		
		function get_costumer_mulai_akhir_rekam_medik($kode_kantor,$id_costumer)
        {
			$query = "
					SELECT
						MIN(tgl_h_penjualan) AS MIN_REKAM
						,MAX(tgl_h_penjualan) AS MAX_REKAM
					FROM tb_h_penjualan 
					WHERE kode_kantor = '".$kode_kantor."' 
					AND id_costumer = '".$id_costumer."' 
					AND sts_penjualan IN ('SELESAI','PEMBAYARAN');
			";
            //$query = $this->db->get_where('tb_costumer', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
			
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
		
		function get_no_costumer()
		{
			$query = 
			"
				SELECT CONCAT('".$this->session->userdata('ses_kode_kantor')."',Y,M,D,ORD) AS NO_COS
				FROM
				(
					SELECT
						Y,M,D,
						/*
						CASE
						WHEN (ORD >= 10 AND ORD < 99) THEN CONCAT('000',CAST(ORD AS CHAR))
						WHEN (ORD >= 100 AND ORD < 999) THEN CONCAT('00',CAST(ORD AS CHAR))
						WHEN (ORD >= 1000 AND ORD < 9999) THEN CONCAT('0',CAST(ORD AS CHAR))
						WHEN ORD >= 10000 THEN CAST(ORD AS CHAR)
						ELSE CONCAT('0000',CAST(ORD AS CHAR))
						END As ORD
						*/
						CASE
						WHEN (ORD >= 10 AND ORD < 99) THEN CONCAT('0',CAST(ORD AS CHAR))
						WHEN ORD >= 100 THEN CAST(ORD AS CHAR)
						ELSE CONCAT('00',CAST(ORD AS CHAR))
						END As ORD
					From
					(
						-- SELECT COUNT(no_costume)+1 AS ORD FROM tb_costumer
						SELECT
						-- CAST(LEFT(NOW(),4) AS CHAR) AS Y,
						CAST(MID(NOW(),3,2) AS CHAR) AS Y,
						CAST(MID(NOW(),6,2) AS CHAR) AS M,
						MID(NOW(),9,2) AS D,
						COALESCE(MAX(CAST(RIGHT(no_costumer,3) AS UNSIGNED)) + 1,1) AS ORD
						From tb_costumer
						-- WHERE DATE(tgl_ins) = DATE(NOW())
						WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
						AND DATE(tgl_ins) = DATE(NOW())
					) AS A
				) AS AA;

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
		
		function get_id_costumer($kode_kantor)
		{
			$query = 
			"
				SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_costumer
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
						COALESCE(MAX(CAST(RIGHT(id_costumer,5) AS UNSIGNED)) + 1,1) AS ORD
						From tb_costumer
						WHERE DATE(tgl_ins) = DATE(NOW())
						AND kode_kantor = '".$kode_kantor."'
					) AS A
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
		
		
		function list_costumer_biasa($cari,$limit,$offset)
		{
			$query =
			"
				SELECT
					A.id_costumer,
					A.id_karyawan,
					
					
					-- A.id_kat_costumer,
					CASE WHEN A.id_kat_costumer = '' THEN
						COALESCE(C.id_kat_costumer,'')
					ELSE
						A.id_kat_costumer
					END
					AS id_kat_costumer,
					
					
					A.id_kat_costumer2,
					A.tgl_pengajuan,
					A.no_costumer,
					A.nama_lengkap,
					A.panggilan,
					A.nik,
					A.no_ktp,
					A.tmp_lahir,
					A.tgl_lahir,
					A.jenis_kelamin,
					A.status,
					A.alamat_rumah_sekarang,
					
					SUBSTRING_INDEX(A.wil_prov,'|',1) AS kode_prov,
					SUBSTRING_INDEX(A.wil_prov,'|',-1) AS nama_prov,
					
					SUBSTRING_INDEX(A.wil_kabkot,'|',1) AS kode_kabkot,
					SUBSTRING_INDEX(A.wil_kabkot,'|',-1) AS nama_kabkot,
					
					SUBSTRING_INDEX(A.wil_kec,'|',1) AS kode_kec,
					SUBSTRING_INDEX(A.wil_kec,'|',-1) AS nama_kec,
					
					SUBSTRING_INDEX(A.wil_des,'|',1) AS kode_des,
					SUBSTRING_INDEX(A.wil_des,'|',-1) AS nama_des,
					
					
					A.hp,
					A.status_rumah,
					A.lama_menempati,
					A.pendidikan,
					A.ibu_kandung,
					A.nama_perusahaan,
					A.alamat_perusahaan,
					A.bidang_usaha,
					A.jabatan,
					A.penghasilan_perbulan,
					A.nama_lengkap_pnd,
					A.alamat_rumah_pnd,
					A.hp_pnd,
					A.pekerjaan,
					A.hubungan,
					A.username,
					A.pass,
					A.avatar,
					A.avatar_url,
					A.piutang_awal,
					A.tgl_piutang_awal,
					A.isDefault,
					A.point_awal,
					A.tgl_registrasi,
					A.jenis_kunjungan,
					A.tgl_ins,
					A.tgl_updt,
					A.user_ins,
					A.user_updt,
					A.kode_kantor,
					A.email_costumer,
					A.ket_costumer,
					COALESCE(D.POINT,0) AS POINT,

					
						CASE WHEN COALESCE(B.nama_kat_costumer,'') = '' THEN
							COALESCE(C.nama_kat_costumer,'')
						ELSE
							COALESCE(B.nama_kat_costumer,'')
						END
						AS nama_kat_costumer
					,DATEDIFF(DATE(NOW()),COALESCE(A.tgl_registrasi,'')) AS lama_terdaftar
					,DATEDIFF(DATE(NOW()),COALESCE(A.tgl_registrasi,'')) AS BEDA
					,CASE 
							WHEN (YEAR(CURDATE()) - YEAR(A.tgl_lahir)) < 150 THEN 
								(YEAR(CURDATE()) - YEAR(A.tgl_lahir))
							ELSE
								0
							END
						AS USIA
				FROM tb_costumer AS A
				LEFT JOIN tb_kat_costumer AS B ON A.id_kat_costumer = B.id_kat_costumer AND A.kode_kantor = B.kode_kantor
				LEFT JOIN
				(
					SELECT 
						(kode_kantor) AS kode_kantor
						,(id_kat_costumer) AS id_kat_costumer
						,(nama_kat_costumer) AS nama_kat_costumer
					FROM tb_kat_costumer
					WHERE set_default = 1
					
				) AS C ON A.kode_kantor = C.kode_kantor
				LEFT JOIN
				(
					SELECT id_costumer,POINT
					FROM
					(
						SELECT id_costumer,SUM(point) AS POINT 
						FROM tb_h_penjualan
						WHERE sts_penjualan = 'SELESAI'
						-- AND isKirim = '1'
						GROUP BY id_costumer
					) AS AA
				) AS D ON A.id_costumer = D.id_costumer
				".$cari."
				ORDER BY  A.nama_lengkap ASC LIMIT ".$offset.",".$limit."
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
		
		function count_costumer_biasa($cari)
		{
			$query = "
						SELECT COUNT(A.id_costumer) AS JUMLAH
						FROM tb_costumer AS A
						LEFT JOIN tb_kat_costumer AS B ON A.id_kat_costumer = B.id_kat_costumer AND A.kode_kantor = B.kode_kantor
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
		
		
		function list_costumer_limit($cari,$limit,$offset)
		{
			
			$query = "
				SELECT
					A.id_costumer , A.id_kat_costumer
					,COALESCE(B.nama_kat_costumer,'') AS nama_kat_costumer
					-- ,COALESCE(B2.nama_kat_costumer,'') AS nama_kat_costumer2
					,A.no_costumer,A.nama_lengkap,A.piutang_awal
					,A.no_ktp , A.alamat_rumah_sekarang, A.hp
					, COALESCE(A.email_costumer,'') AS email_costumer
					
					,A.wil_prov
					,A.wil_kabkot
					,A.wil_kec
					,A.wil_des
					
					, A.ket_costumer, A.kode_kantor
					,COALESCE(SUM(C.nominal),0)  AS BAYAR_PIUTANG
					,COALESCE(PJM_KELUAR.nominal,0) AS PJM_KELUAR
					,COALESCE(PJM_MASUK.nominal,0) AS PJM_MASUk
					,
					ABS(
						( (COALESCE(D.nominal_transaksi,0)) + (COALESCE(D.donasi,0)) + (COALESCE(D.biaya,0)) )
						-
						( (COALESCE(D.bayar_detail,0)) + (COALESCE(D.diskon,0)) + (COALESCE(D.nominal_retur,0)) )
					)
					AS SISA_PIUTANG_TR
					,COALESCE(BB.SISA_TABUNGAN,0) AS SISA_TABUNGAN, COALESCE(A.tgl_piutang_awal,'') AS tgl_piutang_awal,A.isDefault,A.point_awal, COALESCE(A.tgl_registrasi,'') AS tgl_registrasi
					,DATEDIFF(DATE(NOW()),COALESCE(A.tgl_registrasi,'')) AS BEDA
					
					,A.avatar
					,A.nik
					,A.panggilan
					,A.pendidikan
					,A.pekerjaan
					,A.jabatan
					,A.avatar_url
					,A.nama_perusahaan
					,A.jenis_kelamin
					,A.tmp_lahir
					,A.tgl_lahir
					,CASE 
							WHEN (YEAR(CURDATE()) - YEAR(A.tgl_lahir)) < 150 THEN 
								(YEAR(CURDATE()) - YEAR(A.tgl_lahir))
							ELSE
								0
							END
						AS USIA
					,A.status
				FROM tb_costumer A
				LEFT JOIN tb_kat_costumer B ON A.id_kat_costumer = B.id_kat_costumer AND A.kode_kantor = B.kode_kantor
-- LEFT JOIN tb_kat_costumer B2 ON A.id_kat_costumer2 = B2.id_kat_costumer AND A.kode_kantor = B2.kode_kantor
				LEFT JOIN tb_uang_masuk AS C ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor AND C.isPiutang = 1 AND C.noPinjamanCos = ''
				LEFT JOIN
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
						WHERE A.sts_penjualan NOT IN ('PENDING','DIBATALKAN')
						AND LEFT(A.ket_penjualan,7) <>'DIHAPUS' AND nominal_transaksi > 0
						AND (COALESCE(G.BAYAR,0)) < ((COALESCE(C.TR,0) + A.donasi + A.biaya) - (A.diskon + (A.nominal_retur + COALESCE(F.NOMINAL_DET_RTR,0)) ))
						GROUP BY A.id_costumer,A.kode_kantor,A.kode_kantor_costumer
				) AS D ON A.id_costumer = D.id_costumer AND A.kode_kantor = D.kode_kantor_costumer AND (D.bayar_detail) < ((D.nominal_transaksi + D.donasi + D.biaya) - (D.diskon + D.nominal_retur))
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
						Where isTabungan = 1 And isPiutang = 1 AND noPinjamanCos = ''
						GROUP BY id_costumer,kode_kantor
						-- PEMBAYARAN PIUTANG DARI TABUNGAN
					) AS B ON A.id_costumer = B.id_costumer AND A.kode_kantor = B.kode_kantor
					Left Join
					(
						-- PEMBAYARAN PENJUALAN DENGAN TABUNGAN
						SELECT id_costumer,kode_kantor,SUM(nominal) AS BAYAR_TR_DENG_TABUNGAN
						From tb_d_penjualan_bayar
						Where isTabungan = 1
						GROUP BY id_costumer,kode_kantor
						-- PEMBAYARAN PENJUALAN DENGAN TABUNGAN
					) AS C ON A.id_costumer = C.id_costumer AND A.kode_kantor = C.kode_kantor
					Left Join
					(
						-- PEMBAYARAN PIUTANG DARI TABUNGAN UANG KELUAR
						SELECT id_costumer,kode_kantor,SUM(nominal) AS BAYAR_PIUTANG_TABUNGAN_KELUAR
						From tb_uang_keluar
						Where isTabungan = 1
						GROUP BY id_costumer,kode_kantor
						-- PEMBAYARAN PIUTANG DARI TABUNGAN UANG KELUAR
					) AS D ON A.id_costumer = D.id_costumer AND A.kode_kantor = D.kode_kantor
					Where A.isTabungan = 1 And A.isPiutang = 0
					GROUP BY 
					(A.id_costumer)
					,(A.kode_kantor)
					,(COALESCE(B.BAYAR_PIUTANG_TABUNGAN,0))
					,(COALESCE(C.BAYAR_TR_DENG_TABUNGAN,0))
					,(COALESCE(D.BAYAR_PIUTANG_TABUNGAN_KELUAR,0))
				) AS BB ON A.id_costumer = BB.id_costumer AND A.kode_kantor = BB.kode_kantor
				Left Join
				(
					SELECT id_costumer,kode_kantor,SUM(nominal) AS nominal
					From tb_uang_keluar
					Where isTabungan = 0 And isPinjamanCos = 1
					GROUP BY id_costumer,kode_kantor
				) AS PJM_KELUAR ON A.id_costumer = PJM_KELUAR.id_costumer AND A.kode_kantor = PJM_KELUAR.kode_kantor
				Left Join
				(
					SELECT id_costumer, SUM(nominal) AS nominal, kode_kantor
					From tb_uang_masuk
					WHERE isPiutang = 1 AND noPinjamanCos <> ''
					GROUP BY id_costumer,kode_kantor
				) AS PJM_MASUK ON A.id_costumer = PJM_MASUK.id_costumer AND A.kode_kantor = PJM_MASUK.kode_kantor
				-- WHERE (A.no_costumer LIKE '%%' OR  A.nama_lengkap LIKE '%%') AND A.kode_kantor = 'CJRT'
				".$cari."
				Group By
					A.id_costumer , A.id_kat_costumer
					,COALESCE(B.nama_kat_costumer,'')
					-- ,COALESCE(B2.nama_kat_costumer,'')
					,A.no_costumer,A.nama_lengkap,A.piutang_awal
					,A.no_ktp , A.alamat_rumah_sekarang, A.hp
					, COALESCE(A.email_costumer,'')
					, A.ket_costumer,COALESCE(BB.SISA_TABUNGAN,0)
					,COALESCE(PJM_KELUAR.nominal)
					,COALESCE(PJM_MASUK.nominal)
					,D.nominal_transaksi,D.donasi,D.biaya
					,D.bayar_detail,D.diskon,D.nominal_retur,COALESCE(A.tgl_piutang_awal,''),A.isDefault,A.point_awal
					,A.avatar
					,A.nik
					,A.panggilan
					,A.pendidikan
					,A.pekerjaan
					,A.jabatan
					,A.avatar_url
					,A.nama_perusahaan
					,A.jenis_kelamin
					,A.tmp_lahir
					,A.tgl_lahir
					
				ORDER BY A.tgl_ins DESC, A.nama_lengkap ASC LIMIT ".$offset.",".$limit;
			
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
		
		function count_costumer_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(A.id_costumer) AS JUMLAH FROM tb_costumer AS A ".$cari);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function simpan_with_no_costumer
		(
			$id_costumer,
			$id_karyawan,
			$id_kat_costumer,
			$id_kat_costumer2,
			$tgl_pengajuan,
			$no_costumer,
			$nama_lengkap,
			$panggilan,
			$nik,
			$no_ktp,
			$tmp_lahir,
			$tgl_lahir,
			$jenis_kelamin,
			$status,
			$alamat_rumah_sekarang,
			$hp,
			$status_rumah,
			$lama_menempati,
			$pendidikan,
			$ibu_kandung,
			$nama_perusahaan,
			$alamat_perusahaan,
			$bidang_usaha,
			$jabatan,
			$penghasilan_perbulan,
			$nama_lengkap_pnd,
			$alamat_rumah_pnd,
			$hp_pnd,
			$pekerjaan,
			$hubungan,
			$username,
			$pass,
			$avatar,
			$avatar_url,
			$piutang_awal,
			$tgl_piutang_awal,
			$isDefault,
			$point_awal,
			$tgl_registrasi,
			$jenis_kunjungan,
			$user_ins,
			$kode_kantor,
			$email_costumer,
			$ket_costumer,
			
			$wil_prov,
			$wil_kabkot,
			$wil_kec,
			$wil_des
		)
		{
			$strquery = "
				INSERT INTO tb_costumer
				(
					id_costumer,
					id_karyawan,
					id_kat_costumer,
					id_kat_costumer2,
					tgl_pengajuan,
					no_costumer,
					nama_lengkap,
					panggilan,
					nik,
					no_ktp,
					tmp_lahir,
					tgl_lahir,
					jenis_kelamin,
					status,
					alamat_rumah_sekarang,
					hp,
					status_rumah,
					lama_menempati,
					pendidikan,
					ibu_kandung,
					nama_perusahaan,
					alamat_perusahaan,
					bidang_usaha,
					jabatan,
					penghasilan_perbulan,
					nama_lengkap_pnd,
					alamat_rumah_pnd,
					hp_pnd,
					pekerjaan,
					hubungan,
					username,
					pass,
					avatar,
					avatar_url,
					piutang_awal,
					tgl_piutang_awal,
					isDefault,
					point_awal,
					tgl_registrasi,
					jenis_kunjungan,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor,
					email_costumer,
					ket_costumer,
					
					wil_prov,
					wil_kabkot,
					wil_kec,
					wil_des
				)
				VALUES
				(
					'".$id_costumer."',
					'".$id_karyawan."',
					'".$id_kat_costumer."',
					'".$id_kat_costumer2."',
					'".$tgl_pengajuan."',
					'".$no_costumer."',
					'".$nama_lengkap."',
					'".$panggilan."',
					'".$nik."',
					'".$no_ktp."',
					'".$tmp_lahir."',
					'".$tgl_lahir."',
					'".$jenis_kelamin."',
					'".$status."',
					'".$alamat_rumah_sekarang."',
					'".$hp."',
					'".$status_rumah."',
					'".$lama_menempati."',
					'".$pendidikan."',
					'".$ibu_kandung."',
					'".$nama_perusahaan."',
					'".$alamat_perusahaan."',
					'".$bidang_usaha."',
					'".$jabatan."',
					'".$penghasilan_perbulan."',
					'".$nama_lengkap_pnd."',
					'".$alamat_rumah_pnd."',
					'".$hp_pnd."',
					'".$pekerjaan."',
					'".$hubungan."',
					'".$username."',
					'".$pass."',
					'".$avatar."',
					'".$avatar_url."',
					'".$piutang_awal."',
					'".$tgl_piutang_awal."',
					'".$isDefault."',
					'".$point_awal."',
					'".$tgl_registrasi."',
					'".$jenis_kunjungan."',
					NOW(),
					NOW(),
					'".$user_ins."',
					'',
					'".$kode_kantor."',
					'".$email_costumer."',
					'".$ket_costumer."',
					
					'".$wil_prov."',
					'".$wil_kabkot."',
					'".$wil_kec."',
					'".$wil_des."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan
		(	
			$id_costumer,
			$id_karyawan,
			$id_kat_costumer,
			$id_kat_costumer2,
			$tgl_pengajuan,
			$no_costumer,
			$nama_lengkap,
			$panggilan,
			$nik,
			$no_ktp,
			$tmp_lahir,
			$tgl_lahir,
			$jenis_kelamin,
			$status,
			$alamat_rumah_sekarang,
			$hp,
			$status_rumah,
			$lama_menempati,
			$pendidikan,
			$ibu_kandung,
			$nama_perusahaan,
			$alamat_perusahaan,
			$bidang_usaha,
			$jabatan,
			$penghasilan_perbulan,
			$nama_lengkap_pnd,
			$alamat_rumah_pnd,
			$hp_pnd,
			$pekerjaan,
			$hubungan,
			$username,
			$pass,
			$avatar,
			$avatar_url,
			$piutang_awal,
			$tgl_piutang_awal,
			$isDefault,
			$point_awal,
			$tgl_registrasi,
			$jenis_kunjungan,
			$user_ins,
			$kode_kantor,
			$email_costumer,
			$ket_costumer,
			
			$wil_prov,
			$wil_kabkot,
			$wil_kec,
			$wil_des
			
		)
		{
			
			/*
			(
						SELECT CONCAT('".$this->session->userdata('ses_kode_kantor')."',Y,M,D,ORD) AS NO_COS
						FROM
						(
							SELECT
								Y,M,D,
								CASE
								WHEN (ORD >= 10 AND ORD < 99) THEN CONCAT('0',CAST(ORD AS CHAR))
								WHEN ORD >= 100 THEN CAST(ORD AS CHAR)
								ELSE CONCAT('00',CAST(ORD AS CHAR))
								END As ORD
							From
							(
								-- SELECT COUNT(no_costume)+1 AS ORD FROM tb_costumer
								SELECT
								-- CAST(LEFT(NOW(),4) AS CHAR) AS Y,
								CAST(MID(NOW(),3,2) AS CHAR) AS Y,
								CAST(MID(NOW(),6,2) AS CHAR) AS M,
								MID(NOW(),9,2) AS D,
								COALESCE(MAX(CAST(RIGHT(no_costumer,3) AS UNSIGNED)) + 1,1) AS ORD
								From tb_costumer
								-- WHERE DATE(tgl_ins) = DATE(NOW())
								WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
								AND DATE(tgl_ins) = DATE(NOW())
							) AS A
						) AS AA
					),
			*/
			
			$strquery = "
				INSERT INTO tb_costumer
				(
					id_costumer,
					id_karyawan,
					id_kat_costumer,
					id_kat_costumer2,
					tgl_pengajuan,
					no_costumer,
					nama_lengkap,
					panggilan,
					nik,
					no_ktp,
					tmp_lahir,
					tgl_lahir,
					jenis_kelamin,
					status,
					alamat_rumah_sekarang,
					hp,
					status_rumah,
					lama_menempati,
					pendidikan,
					ibu_kandung,
					nama_perusahaan,
					alamat_perusahaan,
					bidang_usaha,
					jabatan,
					penghasilan_perbulan,
					nama_lengkap_pnd,
					alamat_rumah_pnd,
					hp_pnd,
					pekerjaan,
					hubungan,
					username,
					pass,
					avatar,
					avatar_url,
					piutang_awal,
					tgl_piutang_awal,
					isDefault,
					point_awal,
					tgl_registrasi,
					jenis_kunjungan,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor,
					email_costumer,
					ket_costumer,
					
					wil_prov,
					wil_kabkot,
					wil_kec,
					wil_des
				)
				VALUES
				(
					'".$id_costumer."',
					'".$id_karyawan."',
					'".$id_kat_costumer."',
					'".$id_kat_costumer2."',
					'".$tgl_pengajuan."',
					
					CASE WHEN '".$no_costumer."' <> '' THEN
						'".$no_costumer."'
					ELSE
						(
							SELECT CONCAT('".$this->session->userdata('ses_kode_kantor')."',Y,M,D,ORD) AS NO_COS
							FROM
							(
								SELECT
									Y,M,D,
									CASE
									WHEN (ORD >= 10 AND ORD < 99) THEN CONCAT('0',CAST(ORD AS CHAR))
									WHEN ORD >= 100 THEN CAST(ORD AS CHAR)
									ELSE CONCAT('00',CAST(ORD AS CHAR))
									END As ORD
								From
								(
									-- SELECT COUNT(no_costume)+1 AS ORD FROM tb_costumer
									SELECT
									-- CAST(LEFT(NOW(),4) AS CHAR) AS Y,
									CAST(MID(NOW(),3,2) AS CHAR) AS Y,
									CAST(MID(NOW(),6,2) AS CHAR) AS M,
									MID(NOW(),9,2) AS D,
									COALESCE(MAX(CAST(RIGHT(no_costumer,3) AS UNSIGNED)) + 1,1) AS ORD
									From tb_costumer
									-- WHERE DATE(tgl_ins) = DATE(NOW())
									WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
									AND DATE(tgl_ins) = DATE(NOW())
								) AS A
							) AS AA
						)
					END,
					-- '".$no_costumer."',
					'".$nama_lengkap."',
					'".$panggilan."',
					'".$nik."',
					'".$no_ktp."',
					'".$tmp_lahir."',
					'".$tgl_lahir."',
					'".$jenis_kelamin."',
					'".$status."',
					'".$alamat_rumah_sekarang."',
					'".$hp."',
					'".$status_rumah."',
					'".$lama_menempati."',
					'".$pendidikan."',
					'".$ibu_kandung."',
					'".$nama_perusahaan."',
					'".$alamat_perusahaan."',
					'".$bidang_usaha."',
					'".$jabatan."',
					'".$penghasilan_perbulan."',
					'".$nama_lengkap_pnd."',
					'".$alamat_rumah_pnd."',
					'".$hp_pnd."',
					'".$pekerjaan."',
					'".$hubungan."',
					'".$username."',
					'".$pass."',
					'".$avatar."',
					'".$avatar_url."',
					'".$piutang_awal."',
					'".$tgl_piutang_awal."',
					'".$isDefault."',
					'".$point_awal."',
					'".$tgl_registrasi."',
					'".$jenis_kunjungan."',
					NOW(),
					NOW(),
					'".$user_ins."',
					'',
					'".$kode_kantor."',
					'".$email_costumer."',
					'".$ket_costumer."',
					
					'".$wil_prov."',
					'".$wil_kabkot."',
					'".$wil_kec."',
					'".$wil_des."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_costumer,
			$id_karyawan,
			$id_kat_costumer,
			$id_kat_costumer2,
			$tgl_pengajuan,
			$no_costumer,
			$nama_lengkap,
			$panggilan,
			$nik,
			$no_ktp,
			$tmp_lahir,
			$tgl_lahir,
			$jenis_kelamin,
			$status,
			$alamat_rumah_sekarang,
			$hp,
			$status_rumah,
			$lama_menempati,
			$pendidikan,
			$ibu_kandung,
			$nama_perusahaan,
			$alamat_perusahaan,
			$bidang_usaha,
			$jabatan,
			$penghasilan_perbulan,
			$nama_lengkap_pnd,
			$alamat_rumah_pnd,
			$hp_pnd,
			$pekerjaan,
			$hubungan,
			$username,
			$pass,
			$avatar,
			$avatar_url,
			$piutang_awal,
			$tgl_piutang_awal,
			$isDefault,
			$point_awal,
			$tgl_registrasi,
			$jenis_kunjungan,
			$user_updt,
			$kode_kantor,
			$email_costumer,
			$ket_costumer,
			
			$wil_prov,
			$wil_kabkot,
			$wil_kec,
			$wil_des
		)
		{
			/*
			if($avatar != "")
			{
			*/
				$strquery = "
				UPDATE tb_costumer SET
					id_karyawan = '".$id_karyawan."',
					id_kat_costumer = '".$id_kat_costumer."',
					id_kat_costumer2 = '".$id_kat_costumer2."',
					tgl_pengajuan = '".$tgl_pengajuan."',
					no_costumer = '".$no_costumer."',
					nama_lengkap = '".$nama_lengkap."',
					panggilan = '".$panggilan."',
					nik = '".$nik."',
					no_ktp = '".$no_ktp."',
					tmp_lahir = '".$tmp_lahir."',
					tgl_lahir = '".$tgl_lahir."',
					jenis_kelamin = '".$jenis_kelamin."',
					status = '".$status."',
					alamat_rumah_sekarang = '".$alamat_rumah_sekarang."',
					hp = '".$hp."',
					status_rumah = '".$status_rumah."',
					lama_menempati = '".$lama_menempati."',
					pendidikan = '".$pendidikan."',
					ibu_kandung = '".$ibu_kandung."',
					nama_perusahaan = '".$nama_perusahaan."',
					alamat_perusahaan = '".$alamat_perusahaan."',
					bidang_usaha = '".$bidang_usaha."',
					jabatan = '".$jabatan."',
					penghasilan_perbulan = '".$penghasilan_perbulan."',
					nama_lengkap_pnd = '".$nama_lengkap_pnd."',
					alamat_rumah_pnd = '".$alamat_rumah_pnd."',
					hp_pnd = '".$hp_pnd."',
					pekerjaan = '".$pekerjaan."',
					hubungan = '".$hubungan."',
					username = '".$username."',
					pass = '".$pass."',
					avatar = '".$avatar."',
					avatar_url = '".$avatar_url."',
					piutang_awal = '".$piutang_awal."',
					tgl_piutang_awal = '".$tgl_piutang_awal."',
					isDefault = '".$isDefault."',
					point_awal = '".$point_awal."',
					tgl_registrasi = '".$tgl_registrasi."',
					jenis_kunjungan = '".$jenis_kunjungan."',
					tgl_updt = NOW(),
					user_updt = '".$user_updt."',
					email_costumer = '".$email_costumer."',
					ket_costumer = '".$ket_costumer."',
					
					wil_prov = '".$wil_prov."',
					wil_kabkot = '".$wil_kabkot."',
					wil_kec = '".$wil_kec."',
					wil_des = '".$wil_des."'
			
				WHERE kode_kantor = '".$kode_kantor."' AND id_costumer = '".$id_costumer
				."'
				";
			/*
			}
			else
			{
				$strquery = "
				UPDATE tb_costumer SET
					id_karyawan = '".$id_karyawan."',
					id_kat_costumer = '".$id_kat_costumer."',
					id_kat_costumer2 = '".$id_kat_costumer2."',
					tgl_pengajuan = '".$tgl_pengajuan."',
					no_costumer = '".$no_costumer."',
					nama_lengkap = '".$nama_lengkap."',
					panggilan = '".$panggilan."',
					nik = '".$nik."',
					no_ktp = '".$no_ktp."',
					tgl_lahir = '".$tgl_lahir."',
					jenis_kelamin = '".$jenis_kelamin."',
					status = '".$status."',
					alamat_rumah_sekarang = '".$alamat_rumah_sekarang."',
					hp = '".$hp."',
					status_rumah = '".$status_rumah."',
					lama_menempati = '".$lama_menempati."',
					pendidikan = '".$pendidikan."',
					ibu_kandung = '".$ibu_kandung."',
					nama_perusahaan = '".$nama_perusahaan."',
					alamat_perusahaan = '".$alamat_perusahaan."',
					bidang_usaha = '".$bidang_usaha."',
					jabatan = '".$jabatan."',
					penghasilan_perbulan = '".$penghasilan_perbulan."',
					nama_lengkap_pnd = '".$nama_lengkap_pnd."',
					alamat_rumah_pnd = '".$alamat_rumah_pnd."',
					hp_pnd = '".$hp_pnd."',
					pekerjaan = '".$pekerjaan."',
					hubungan = '".$hubungan."',
					username = '".$username."',
					pass = '".$pass."',
					piutang_awal = '".$piutang_awal."',
					tgl_piutang_awal = '".$tgl_piutang_awal."',
					isDefault = '".$isDefault."',
					point_awal = '".$point_awal."',
					tgl_registrasi = '".$tgl_registrasi."',
					tgl_updt = NOW(),
					user_updt = '".$user_updt."',
					email_costumer = '".$email_costumer."',
					ket_costumer = '".$ket_costumer."'
				WHERE kode_kantor = '".$kode_kantor."' AND id_costumer = '".$id_costumer
				."'
				";
			}
			*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}

		function set_default($id_costumer,$kode_kantor)
		{
				/*UPDATE DEFAULT*/
					$strquery = 
					"
						UPDATE tb_costumer SET 
							isDefault = 
								CASE 
								WHEN isDefault = 0 THEN
									1
								ELSE
									0
								END
						
						WHERE kode_kantor = '".$kode_kantor."' AND id_costumer = '".$id_costumer."' ;
					";
				/*UPDATE DEFAULT*/
				
				/*SIMPAN DAN CATAT QUERY*/
					$this->M_gl_log->simpan_query($strquery);
				/*SIMPAN DAN CATAT QUERY*/
				
				
				
				$strquery = 
				"
					UPDATE tb_costumer SET 
						isDefault = 0
					WHERE kode_kantor = '".$kode_kantor."' AND id_costumer <> '".$id_costumer."' ;
				";
					
				/*SIMPAN DAN CATAT QUERY*/
					$this->M_gl_log->simpan_query($strquery);
				/*SIMPAN DAN CATAT QUERY*/
				
		}
		
		function hapus($berdasarkan, $id_costumer)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_costumer WHERE ".$berdasarkan." = '".$id_costumer."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_costumer($berdasarkan,$cari)
        {
			$query = "SELECT * FROM tb_costumer WHERE ".$berdasarkan." = '".$cari."' ;";
            //$query = $this->db->get_where('tb_costumer', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
			
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
		
		function get_costumer_cari($cari)
        {
			$query = "SELECT * FROM tb_costumer ".$cari." ;";
			
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
		
		function get_costumer_with_kategori($cari)
        {
			$query = "
				SELECT
					A.*
					,COALESCE(B.nama_kat_costumer,'') AS nama_kat_costumer
				FROM tb_costumer AS A 
				LEFT JOIN tb_kat_costumer AS B ON A.id_kat_costumer = B.id_kat_costumer AND A.kode_kantor = B.kode_kantor 
				".$cari." 
				;";
            //$query = $this->db->get_where('tb_costumer', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
			
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
		
		function simpan_outbox($no_hp,$pesan)
		{
			$query =
					"
						INSERT INTO outbox(DestinationNumber, TextDecoded, CreatorID) VALUES ('".$no_hp."', '".$pesan."', 'Gammu');
					";
			$this->db->query($query);
		}
		
		function simpan_upgrade_member
		(
			$id_kat_costumer,
			$id_costumer,
			$tgl_aktif,
			$ket_upgrade,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_upgrade_member
				(
					id_upgrade,
					id_kat_costumer,
					id_costumer,
					tgl_aktif,
					ket_upgrade,
					tgl_ins,
					tgl_updt,
					user_ins,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_upgrade
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
								COALESCE(MAX(CAST(RIGHT(id_upgrade,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_upgrade_member
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_kat_costumer."',
					'".$id_costumer."',
					'".$tgl_aktif."',
					'".$ket_upgrade."',
					NOW(),
					NOW(),
					'".$user_ins."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_upgrade_member
		(
			$id_upgrade,
			$id_kat_costumer,
			$id_costumer,
			$tgl_aktif,
			$ket_upgrade,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_upgrade_member SET
						id_kat_costumer = '".$id_kat_costumer."',
						tgl_aktif = '".$tgl_aktif."',
						ket_upgrade = '".$ket_upgrade."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_upgrade = '".$id_upgrade
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_akun
		(
			$id_costumer,
			$username,
			$pass,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_costumer SET
						username = '".$username."',
						pass = '".$pass."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_costumer = '".$id_costumer
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus_upgrade_member($berdasarkan,$id_upgrade)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_upgrade_member WHERE ".$berdasarkan." = '".$id_upgrade."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function update_member($kode_kantor)
		{
			/*HAPUS JABATAN*/
				$strquery = "
						UPDATE tb_costumer
						INNER JOIN tb_upgrade_member AS B
						ON tb_costumer.id_costumer = B.id_costumer AND tb_costumer.kode_kantor = B.kode_kantor
						SET tb_costumer.id_kat_costumer = B.id_kat_costumer,B.isAktif = '1'
						WHERE tb_costumer.kode_kantor = '".$kode_kantor."' 
						-- AND tb_costumer.id_costumer = '' 
						AND B.isAktif = '0'  AND DATE(B.tgl_aktif) <= DATE(NOW());
				";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function list_costumer_upgrade($cari)
		{
			$query =
			"
				SELECT
					A.*
					,COALESCE(B.nama_kat_costumer,'') AS nama_kat_costumer
				FROM tb_upgrade_member AS A
				LEFT JOIN tb_kat_costumer AS B ON A.id_kat_costumer = B.id_kat_costumer  AND A.kode_kantor = B.kode_kantor
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
		
		function list_karyawan_cari($cari,$order_by)
		{
			$query =
			"
				SELECT * FROM tb_karyawan ".$cari." ".$order_by."
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