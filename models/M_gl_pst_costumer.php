<?php
	class M_gl_pst_costumer extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
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
		
	}
?>