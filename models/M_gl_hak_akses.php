<?php
	Class M_gl_hak_akses extends CI_Model
	{
		function __construct()
		{
			parent::__construct();
		}
		
		function beri_akses_masal($id_jabatan,$cari,$limit,$offset)
		{
			$query = "
						INSERT INTO tb_hak_akses(id_hak_akses,id_jabatan,id_fasilitas,tgl_ins,tgl_updt,user_updt,kode_kantor)
						SELECT
							CONCAT('".$id_jabatan."',A.id_fasilitas) AS id_hak_akses
							,'".$id_jabatan."' AS id_jabatan
							,A.id_fasilitas
							,NOW() AS tgl_ins
							,NOW() AS tgl_updt
							,'".$this->session->userdata('ses_id_karyawan')."' AS user_updt
							,'".$this->session->userdata('ses_kode_kantor')."' AS kode_kantor
						FROM tb_fasilitas AS A		
						LEFT JOIN
						(
							SELECT id_fasilitas,COUNT(id_hak_akses) AS JUMLAH 
							FROM tb_hak_akses
							WHERE (id_jabatan) = '".$id_jabatan."'
							AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							GROUP BY id_fasilitas
						) AS B
						ON A.id_fasilitas = B.id_fasilitas
						".$cari." ORDER BY  COALESCE(B.JUMLAH,0) DESC, A.group1 ASC, A.main_group ASC,id_fasilitas ASC,nama_fasilitas ASC LIMIT ".$offset.",".$limit."";
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($query);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function list_hak_akses_limit($id_jabatan,$cari,$limit,$offset)
		{
			if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
			{
				$query = $this->db->query("
									SELECT *
									FROM
									(
										SELECT 
										-- A.*
										A.id_fasilitas
										,REPLACE(
											REPLACE(  
												REPLACE(UCASE(A.group1),'PASIEN','PELANGGAN')
												,'DOKTER'
												,''
												)
											,'PEMERIKSAAN'
											,'TRANSAKSI'
											)
											AS group1
										,REPLACE(
											REPLACE(
												REPLACE(UCASE(A.main_group),'PASIEN','PELANGGAN') 
												,'DOKTER'
												,''
												)
											,'PEMERIKSAAN'
											,'TRANSAKSI'
											)
											AS main_group
										,REPLACE(
											REPLACE(
											REPLACE(A.sub_group,'Pasien','Pelanggan')
												,'Dokter'
												,''
												)
											,'PEMERIKSAAN'
											,'TRANSAKSI'
											)
											AS sub_group
										,REPLACE(
												REPLACE(LCASE(A.nama_fasilitas),'pasien','Pelanggan')
												,'dokter'
												,''
												)
												AS nama_fasilitas
										,A.level
										,REPLACE(
												REPLACE(LCASE(A.keterangan),'pasien','Pelanggan') 
												,'dokter'
												,''
												)
												AS keterangan
										,COALESCE(B.JUMLAH,0) AS JUMLAH
										FROM tb_fasilitas AS A
										LEFT JOIN
										(
											SELECT id_fasilitas,COUNT(id_hak_akses) AS JUMLAH 
											FROM tb_hak_akses
											WHERE id_jabatan = '".$id_jabatan."'
											AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
											GROUP BY id_fasilitas
										) AS B
										ON A.id_fasilitas = B.id_fasilitas
									) AS A
									".$cari." 
									ORDER BY  COALESCE(A.JUMLAH,0) DESC, A.group1 ASC, A.main_group ASC,id_fasilitas ASC,nama_fasilitas ASC LIMIT ".$offset.",".$limit);
			}
			else
			{
				$query = $this->db->query("
										SELECT 
										A.*
										,COALESCE(B.JUMLAH,0) AS JUMLAH
										FROM tb_fasilitas AS A
										LEFT JOIN
										(
											SELECT id_fasilitas,COUNT(id_hak_akses) AS JUMLAH 
											FROM tb_hak_akses
											WHERE id_jabatan = '".$id_jabatan."'
											AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
											GROUP BY id_fasilitas
										) AS B
										ON A.id_fasilitas = B.id_fasilitas
										".$cari." ORDER BY  COALESCE(B.JUMLAH,0) DESC, A.group1 ASC, A.main_group ASC,id_fasilitas ASC,nama_fasilitas ASC LIMIT ".$offset.",".$limit);
			}
			
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_hak_akses_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(A.id_fasilitas) AS JUMLAH
										FROM tb_fasilitas AS A
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
		
		function get_akses_fasilitas($id_jabatan,$id_fasilitas)
        {
            $query = $this->db->query("SELECT * FROM tb_hak_akses WHERE id_fasilitas = ".$id_fasilitas." AND id_jabatan = '".$id_jabatan."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."';");
            if($query->num_rows() > 0)
            {
                return $query->row();
            }
            else
            {
                return false;
            }
        }
		
		function simpan($id_jabatan,$id_fasilitas,$id_user,$kode_kantor)
		{
			
			$query = "INSERT INTO tb_hak_akses
			 (
				 id_hak_akses
				 ,id_jabatan
				 ,id_fasilitas
				 ,tgl_updt
				 ,tgl_ins
				 ,kode_kantor
				 ,user_updt
			 )
			 Values
			 (
				 (
					 SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_hak_akses
					 From
					 (
						 SELECT CONCAT(Y,M) AS FRMTGL
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
							 COALESCE(MAX(CAST(RIGHT(id_hak_akses,5) AS UNSIGNED)) + 1,1) AS ORD
							 From tb_hak_akses
							 WHERE DATE_FORMAT(tgl_ins,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
							 AND kode_kantor = '".$kode_kantor."'
						 ) AS A
					 ) AS AA
				 )
				 ,'".$id_jabatan."'
				 ,'".$id_fasilitas."'
				 ,NOW()
				 ,NOW()
				 ,'".$kode_kantor."'
				 ,'".$id_user."'
			 )";
			 
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($query);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		
		function hapus($id_jabatan,$id_fasilitas)
		{
			$query = "DELETE FROM tb_hak_akses WHERE id_jabatan = '".$id_jabatan."' AND id_fasilitas = ".$id_fasilitas." AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			
			//$this->db->query("DELETE FROM tb_hak_akses WHERE id_jabatan = ".$id_jabatan." AND id_fasilitas = ".$id_fasilitas." AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;");
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($query);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function cek_akses_jabatan_level1($id_jabatan,$kode_kantor)
		{
			$query = "
					SELECT
						SUM(AKSES1) AS AKSES1
						,SUM(AKSES2) AS AKSES2
						,SUM(AKSES3) AS AKSES3
						,SUM(AKSES4) AS AKSES4
						,SUM(AKSES5) AS AKSES5
						,SUM(AKSES6) AS AKSES6
						,SUM(AKSES7) AS AKSES7
						,SUM(AKSES8) AS AKSES8
						,SUM(AKSES9) AS AKSES9
						FROM
						(
							SELECT
								CASE WHEN INDEX_MENU = 1 THEN SUM(AKSES) ELSE 0 END AS AKSES1
								,CASE WHEN INDEX_MENU = 2 THEN SUM(AKSES) ELSE 0 END AS AKSES2
								,CASE WHEN INDEX_MENU = 3 THEN SUM(AKSES) ELSE 0 END AS AKSES3
								,CASE WHEN INDEX_MENU = 4 THEN SUM(AKSES) ELSE 0 END AS AKSES4
								,CASE WHEN INDEX_MENU = 5 THEN SUM(AKSES) ELSE 0 END AS AKSES5
								,CASE WHEN INDEX_MENU = 6 THEN SUM(AKSES) ELSE 0 END AS AKSES6
								,CASE WHEN INDEX_MENU = 7 THEN SUM(AKSES) ELSE 0 END AS AKSES7
								,CASE WHEN INDEX_MENU = 8 THEN SUM(AKSES) ELSE 0 END AS AKSES8
								,CASE WHEN INDEX_MENU = 9 THEN SUM(AKSES) ELSE 0 END AS AKSES9
							FROM
							(
								SELECT A.*
									,CASE 
									WHEN B.id_hak_akses IS NULL THEN
										0
									ELSE
										1
									END AS AKSES
									,LEFT(group1,1) AS INDEX_MENU
								FROM tb_fasilitas AS A 
								LEFT JOIN tb_hak_akses AS B 
										ON A.id_fasilitas = B.id_fasilitas 
										AND B.kode_kantor = '".$kode_kantor."' 
										AND B.id_jabatan = '".$id_jabatan."'
								WHERE A.level >= 1
							) AS AA
							GROUP BY INDEX_MENU
							-- ORDER BY A.id_fasilitas ASC;
						) AS AAA
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
		
		function cek_akses_jabatan_level2($id_jabatan,$kode_kantor)
		{
			$query = "
					SELECT
						-- AKSES 2
							SUM(AKSES21) AS AKSES21
							,SUM(AKSES22) AS AKSES22
							,SUM(AKSES23) AS AKSES23
							,SUM(AKSES24) AS AKSES24
							,SUM(AKSES25) AS AKSES25
							,SUM(AKSES26) AS AKSES26
							,SUM(AKSES27) AS AKSES27
							,SUM(AKSES28) AS AKSES28
							,SUM(AKSES29) AS AKSES29
							,SUM(AKSES210) AS AKSES210
						-- AKSES 2
						
						-- AKSES 4
							,SUM(AKSES41) AS AKSES41
							,SUM(AKSES42) AS AKSES42
							,SUM(AKSES43) AS AKSES43
							,SUM(AKSES44) AS AKSES44
							,SUM(AKSES45) AS AKSES45
						-- AKSES 4
						
						-- AKSES 5
							,SUM(AKSES51) AS AKSES51
							,SUM(AKSES52) AS AKSES52
							,SUM(AKSES53) AS AKSES53
							,SUM(AKSES54) AS AKSES54
							,SUM(AKSES55) AS AKSES55
							,SUM(AKSES56) AS AKSES56
							,SUM(AKSES57) AS AKSES57
							,SUM(AKSES58) AS AKSES58
							,SUM(AKSES59) AS AKSES59
							,SUM(AKSES510) AS AKSES510
							,SUM(AKSES511) AS AKSES511
							,SUM(AKSES513) AS AKSES513
							,SUM(AKSES514) AS AKSES514
							,SUM(AKSES515) AS AKSES515
							,SUM(AKSES516) AS AKSES516 -- Transaksi Assets dan BA
						-- AKSES 5
						
						-- AKSES 6
							,SUM(AKSES61) AS AKSES61
							,SUM(AKSES62) AS AKSES62
						-- AKSES 6
						
						-- AKSES 7
							,SUM(AKSES71) AS AKSES71
							,SUM(AKSES72) AS AKSES72
						-- AKSES 7
						
						-- AKSES 8
							,SUM(AKSES81) AS AKSES81
							,SUM(AKSES82) AS AKSES82
							,SUM(AKSES83) AS AKSES83
							,SUM(AKSES84) AS AKSES84
							,SUM(AKSES85) AS AKSES85
						-- AKSES 8
						
						-- AKSES 9
							,SUM(AKSES91) AS AKSES91
						-- AKSES 9
						
					FROM
					(
						SELECT
							-- AKSES 2
								CASE WHEN INDEX_MENU = 21 THEN SUM(AKSES) ELSE 0 END AS AKSES21
								,CASE WHEN INDEX_MENU = 22 THEN SUM(AKSES) ELSE 0 END AS AKSES22
								,CASE WHEN INDEX_MENU = 23 THEN SUM(AKSES) ELSE 0 END AS AKSES23
								,CASE WHEN INDEX_MENU = 24 THEN SUM(AKSES) ELSE 0 END AS AKSES24
								,CASE WHEN INDEX_MENU = 25 THEN SUM(AKSES) ELSE 0 END AS AKSES25
								,CASE WHEN INDEX_MENU = 26 THEN SUM(AKSES) ELSE 0 END AS AKSES26
								,CASE WHEN INDEX_MENU = 27 THEN SUM(AKSES) ELSE 0 END AS AKSES27
								,CASE WHEN INDEX_MENU = 28 THEN SUM(AKSES) ELSE 0 END AS AKSES28
								,CASE WHEN INDEX_MENU = 29 THEN SUM(AKSES) ELSE 0 END AS AKSES29
								,CASE WHEN INDEX_MENU = 210 THEN SUM(AKSES) ELSE 0 END AS AKSES210
							-- AKSES 2
							
							-- AKSES 4
								,CASE WHEN INDEX_MENU = 41 THEN SUM(AKSES) ELSE 0 END AS AKSES41
								,CASE WHEN INDEX_MENU = 42 THEN SUM(AKSES) ELSE 0 END AS AKSES42
								,CASE WHEN INDEX_MENU = 43 THEN SUM(AKSES) ELSE 0 END AS AKSES43
								,CASE WHEN INDEX_MENU = 44 THEN SUM(AKSES) ELSE 0 END AS AKSES44
								,CASE WHEN INDEX_MENU = 45 THEN SUM(AKSES) ELSE 0 END AS AKSES45
							-- AKSES 4
							
							-- AKSES 5
								,CASE WHEN INDEX_MENU = 51 THEN SUM(AKSES) ELSE 0 END AS AKSES51
								,CASE WHEN INDEX_MENU = 52 THEN SUM(AKSES) ELSE 0 END AS AKSES52
								,CASE WHEN INDEX_MENU = 53 THEN SUM(AKSES) ELSE 0 END AS AKSES53
								,CASE WHEN INDEX_MENU = 54 THEN SUM(AKSES) ELSE 0 END AS AKSES54
								,CASE WHEN INDEX_MENU = 55 THEN SUM(AKSES) ELSE 0 END AS AKSES55
								,CASE WHEN INDEX_MENU = 56 THEN SUM(AKSES) ELSE 0 END AS AKSES56
								,CASE WHEN INDEX_MENU = 57 THEN SUM(AKSES) ELSE 0 END AS AKSES57
								,CASE WHEN INDEX_MENU = 58 THEN SUM(AKSES) ELSE 0 END AS AKSES58
								,CASE WHEN INDEX_MENU = 59 THEN SUM(AKSES) ELSE 0 END AS AKSES59
								,CASE WHEN INDEX_MENU = 510 THEN SUM(AKSES) ELSE 0 END AS AKSES510
								,CASE WHEN INDEX_MENU = 511 THEN SUM(AKSES) ELSE 0 END AS AKSES511
								,CASE WHEN INDEX_MENU = 513 THEN SUM(AKSES) ELSE 0 END AS AKSES513
								,CASE WHEN INDEX_MENU = 514 THEN SUM(AKSES) ELSE 0 END AS AKSES514
								,CASE WHEN INDEX_MENU = 515 THEN SUM(AKSES) ELSE 0 END AS AKSES515
								,CASE WHEN INDEX_MENU = 516 THEN SUM(AKSES) ELSE 0 END AS AKSES516
							-- AKSES 5
							
							-- AKSES 6
								,CASE WHEN INDEX_MENU = 61 THEN SUM(AKSES) ELSE 0 END AS AKSES61
								,CASE WHEN INDEX_MENU = 62 THEN SUM(AKSES) ELSE 0 END AS AKSES62
							-- AKSES 6
							
							-- AKSES 7
								,CASE WHEN INDEX_MENU = 71 THEN SUM(AKSES) ELSE 0 END AS AKSES71
								,CASE WHEN INDEX_MENU = 72 THEN SUM(AKSES) ELSE 0 END AS AKSES72
							-- AKSES 7
							
							-- AKSES 8
								,CASE WHEN INDEX_MENU = 81 THEN SUM(AKSES) ELSE 0 END AS AKSES81
								,CASE WHEN INDEX_MENU = 82 THEN SUM(AKSES) ELSE 0 END AS AKSES82
								,CASE WHEN INDEX_MENU = 83 THEN SUM(AKSES) ELSE 0 END AS AKSES83
								,CASE WHEN INDEX_MENU = 84 THEN SUM(AKSES) ELSE 0 END AS AKSES84
								,CASE WHEN INDEX_MENU = 85 THEN SUM(AKSES) ELSE 0 END AS AKSES85
							-- AKSES 8
							
							-- AKSES 9
								,CASE WHEN INDEX_MENU = 91 THEN SUM(AKSES) ELSE 0 END AS AKSES91
							-- AKSES 9
						FROM
						(
							SELECT A.*
								,CASE 
								WHEN B.id_hak_akses IS NULL THEN
									0
								ELSE
									1
								END AS AKSES
								,CONCAT(LEFT(group1,1),REPLACE(LEFT(main_group,2),'.','')) AS INDEX_MENU
							FROM tb_fasilitas AS A 
							LEFT JOIN tb_hak_akses AS B 
									ON A.id_fasilitas = B.id_fasilitas 
									AND B.kode_kantor = '". $kode_kantor ."' 
									AND B.id_jabatan = '". $id_jabatan ."'
							WHERE A.level >= 2
						) AS AA
						GROUP BY INDEX_MENU
					) AS AAA
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
		
		
		function cek_akses_jabatan_level3($id_jabatan,$kode_kantor)
		{
			$query = "
					SELECT
						SUM(AKSES211) AS AKSES211
						,SUM(AKSES212) AS AKSES212
						,SUM(AKSES213) AS AKSES213
						,SUM(AKSES214) AS AKSES214
						,SUM(AKSES215) AS AKSES215
						,SUM(AKSES216) AS AKSES216
						,SUM(AKSES217) AS AKSES217
						,SUM(AKSES218) AS AKSES218
						,SUM(AKSES219) AS AKSES219
						
						,SUM(AKSES221) AS AKSES221
						,SUM(AKSES222) AS AKSES222
						,SUM(AKSES223) AS AKSES223
						
						,SUM(AKSES231) AS AKSES231
						,SUM(AKSES232) AS AKSES232
						
						,SUM(AKSES241) AS AKSES241
						,SUM(AKSES242) AS AKSES242
						
						,SUM(AKSES251) AS AKSES251
						,SUM(AKSES252) AS AKSES252
						
						,SUM(AKSES261) AS AKSES261
						,SUM(AKSES262) AS AKSES262
						,SUM(AKSES263) AS AKSES263
						
						,SUM(AKSES441) AS AKSES441
						,SUM(AKSES442) AS AKSES442
						
						,SUM(AKSES451) AS AKSES451
						,SUM(AKSES452) AS AKSES452
						,SUM(AKSES453) AS AKSES453
						,SUM(AKSES454) AS AKSES454
						,SUM(AKSES455) AS AKSES455
						
						,SUM(AKSES511) AS AKSES511
						,SUM(AKSES512) AS AKSES512
						
						,SUM(AKSES531) AS AKSES531
						,SUM(AKSES532) AS AKSES532
						,SUM(AKSES533) AS AKSES533
						,SUM(AKSES534) AS AKSES534
						,SUM(AKSES535) AS AKSES535
						
						,SUM(AKSES711) AS AKSES711
						,SUM(AKSES712) AS AKSES712
						,SUM(AKSES713) AS AKSES713
						,SUM(AKSES714) AS AKSES714
						,SUM(AKSES715) AS AKSES715
						,SUM(AKSES716) AS AKSES716
						,SUM(AKSES717) AS AKSES717
						,SUM(AKSES718) AS AKSES718
						,SUM(AKSES719) AS AKSES719
						,SUM(AKSES7110) AS AKSES7110
						,SUM(AKSES7111) AS AKSES7111
						,SUM(AKSES7112) AS AKSES7112
						
						,SUM(AKSES721) AS AKSES721
						,SUM(AKSES722) AS AKSES722
						,SUM(AKSES723) AS AKSES723
					FROM
					(
						SELECT
							CASE WHEN INDEX_MENU = 211 THEN SUM(AKSES) ELSE 0 END AS AKSES211
							,CASE WHEN INDEX_MENU = 212 THEN SUM(AKSES) ELSE 0 END AS AKSES212
							,CASE WHEN INDEX_MENU = 213 THEN SUM(AKSES) ELSE 0 END AS AKSES213
							,CASE WHEN INDEX_MENU = 214 THEN SUM(AKSES) ELSE 0 END AS AKSES214
							,CASE WHEN INDEX_MENU = 215 THEN SUM(AKSES) ELSE 0 END AS AKSES215
							,CASE WHEN INDEX_MENU = 216 THEN SUM(AKSES) ELSE 0 END AS AKSES216
							,CASE WHEN INDEX_MENU = 217 THEN SUM(AKSES) ELSE 0 END AS AKSES217
							,CASE WHEN INDEX_MENU = 218 THEN SUM(AKSES) ELSE 0 END AS AKSES218
							,CASE WHEN INDEX_MENU = 219 THEN SUM(AKSES) ELSE 0 END AS AKSES219
							
							,CASE WHEN INDEX_MENU = 221 THEN SUM(AKSES) ELSE 0 END AS AKSES221
							,CASE WHEN INDEX_MENU = 222 THEN SUM(AKSES) ELSE 0 END AS AKSES222
							,CASE WHEN INDEX_MENU = 223 THEN SUM(AKSES) ELSE 0 END AS AKSES223
							
							,CASE WHEN INDEX_MENU = 231 THEN SUM(AKSES) ELSE 0 END AS AKSES231
							,CASE WHEN INDEX_MENU = 232 THEN SUM(AKSES) ELSE 0 END AS AKSES232
							
							,CASE WHEN INDEX_MENU = 241 THEN SUM(AKSES) ELSE 0 END AS AKSES241
							,CASE WHEN INDEX_MENU = 242 THEN SUM(AKSES) ELSE 0 END AS AKSES242
							
							,CASE WHEN INDEX_MENU = 251 THEN SUM(AKSES) ELSE 0 END AS AKSES251
							,CASE WHEN INDEX_MENU = 252 THEN SUM(AKSES) ELSE 0 END AS AKSES252
							
							,CASE WHEN INDEX_MENU = 261 THEN SUM(AKSES) ELSE 0 END AS AKSES261
							,CASE WHEN INDEX_MENU = 262 THEN SUM(AKSES) ELSE 0 END AS AKSES262
							,CASE WHEN INDEX_MENU = 263 THEN SUM(AKSES) ELSE 0 END AS AKSES263
							
							,CASE WHEN INDEX_MENU = 441 THEN SUM(AKSES) ELSE 0 END AS AKSES441
							,CASE WHEN INDEX_MENU = 442 THEN SUM(AKSES) ELSE 0 END AS AKSES442
							
							,CASE WHEN INDEX_MENU = 451 THEN SUM(AKSES) ELSE 0 END AS AKSES451
							,CASE WHEN INDEX_MENU = 452 THEN SUM(AKSES) ELSE 0 END AS AKSES452
							,CASE WHEN INDEX_MENU = 453 THEN SUM(AKSES) ELSE 0 END AS AKSES453
							,CASE WHEN INDEX_MENU = 454 THEN SUM(AKSES) ELSE 0 END AS AKSES454
							,CASE WHEN INDEX_MENU = 455 THEN SUM(AKSES) ELSE 0 END AS AKSES455
							
							,CASE WHEN INDEX_MENU = 511 THEN SUM(AKSES) ELSE 0 END AS AKSES511
							,CASE WHEN INDEX_MENU = 512 THEN SUM(AKSES) ELSE 0 END AS AKSES512
							
							,CASE WHEN INDEX_MENU = 531 THEN SUM(AKSES) ELSE 0 END AS AKSES531
							,CASE WHEN INDEX_MENU = 532 THEN SUM(AKSES) ELSE 0 END AS AKSES532
							,CASE WHEN INDEX_MENU = 533 THEN SUM(AKSES) ELSE 0 END AS AKSES533
							,CASE WHEN INDEX_MENU = 534 THEN SUM(AKSES) ELSE 0 END AS AKSES534
							,CASE WHEN INDEX_MENU = 535 THEN SUM(AKSES) ELSE 0 END AS AKSES535
							
							,CASE WHEN INDEX_MENU = 711 THEN SUM(AKSES) ELSE 0 END AS AKSES711
							,CASE WHEN INDEX_MENU = 712 THEN SUM(AKSES) ELSE 0 END AS AKSES712
							,CASE WHEN INDEX_MENU = 713 THEN SUM(AKSES) ELSE 0 END AS AKSES713
							,CASE WHEN INDEX_MENU = 714 THEN SUM(AKSES) ELSE 0 END AS AKSES714
							,CASE WHEN INDEX_MENU = 715 THEN SUM(AKSES) ELSE 0 END AS AKSES715
							,CASE WHEN INDEX_MENU = 716 THEN SUM(AKSES) ELSE 0 END AS AKSES716
							,CASE WHEN INDEX_MENU = 717 THEN SUM(AKSES) ELSE 0 END AS AKSES717
							,CASE WHEN INDEX_MENU = 718 THEN SUM(AKSES) ELSE 0 END AS AKSES718
							,CASE WHEN INDEX_MENU = 719 THEN SUM(AKSES) ELSE 0 END AS AKSES719
							,CASE WHEN INDEX_MENU = 7110 THEN SUM(AKSES) ELSE 0 END AS AKSES7110
							,CASE WHEN INDEX_MENU = 7111 THEN SUM(AKSES) ELSE 0 END AS AKSES7111
							,CASE WHEN INDEX_MENU = 7112 THEN SUM(AKSES) ELSE 0 END AS AKSES7112
							,CASE WHEN INDEX_MENU = 7113 THEN SUM(AKSES) ELSE 0 END AS AKSES7113
							
							,CASE WHEN INDEX_MENU = 721 THEN SUM(AKSES) ELSE 0 END AS AKSES721
							,CASE WHEN INDEX_MENU = 722 THEN SUM(AKSES) ELSE 0 END AS AKSES722
							,CASE WHEN INDEX_MENU = 723 THEN SUM(AKSES) ELSE 0 END AS AKSES723
						FROM
						(
							SELECT
								CONCAT(LEFT(group1,1),REPLACE(LEFT(main_group,2),'.',''),REPLACE(LEFT(sub_group,2),'.','')) AS INDEX_MENU
								,A.*
								,CASE 
								WHEN B.id_hak_akses IS NULL THEN
									0
								ELSE
									1
								END AS AKSES
								
							FROM tb_fasilitas AS A 
							LEFT JOIN tb_hak_akses AS B 
									ON A.id_fasilitas = B.id_fasilitas 
									AND B.kode_kantor = '". $kode_kantor ."' 
									AND B.id_jabatan = '". $id_jabatan ."'
							WHERE A.level >= 3
							-- ORDER BY id_fasilitas ASC
							-- ORDER BY LEFT(group1,1) ASC , REPLACE(LEFT(main_group,2),'.','') ASC, REPLACE(LEFT(sub_group,2),'.','') ASC
						) AS AA
						GROUP BY INDEX_MENU
						-- ORDER BY INDEX_MENU
					) AS AAA
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
		
		function cek_akses_jabatan_level4($id_jabatan,$kode_kantor)
		{
			$query = "
					
					SELECT
						SUM(AKSES2182) AS AKSES2182
						,SUM(AKSES2183) AS AKSES2183
						,SUM(AKSES2184) AS AKSES2184
						
						,SUM(AKSES7164) AS AKSES7164
						
						,SUM(AKSES7212) AS AKSES7212
					FROM
					(
						SELECT
							CASE WHEN INDEX_MENU = 2182 THEN SUM(AKSES) ELSE 0 END AS AKSES2182
							,CASE WHEN INDEX_MENU = 2183 THEN SUM(AKSES) ELSE 0 END AS AKSES2183
							,CASE WHEN INDEX_MENU = 2184 THEN SUM(AKSES) ELSE 0 END AS AKSES2184
							
							,CASE WHEN INDEX_MENU = 7164 THEN SUM(AKSES) ELSE 0 END AS AKSES7164
							
							,CASE WHEN INDEX_MENU = 7212 THEN SUM(AKSES) ELSE 0 END AS AKSES7212
						FROM
						(
							SELECT
								CONCAT(LEFT(group1,1),REPLACE(LEFT(main_group,2),'.',''),REPLACE(LEFT(sub_group,2),'.',''),REPLACE(RIGHT(A.id_fasilitas,1),'.','')) AS INDEX_MENU
								,A.*
								,CASE 
								WHEN B.id_hak_akses IS NULL THEN
									0
								ELSE
									1
								END AS AKSES
								
							FROM tb_fasilitas AS A 
							LEFT JOIN tb_hak_akses AS B 
									ON A.id_fasilitas = B.id_fasilitas 
									AND B.kode_kantor = '". $kode_kantor ."' 
									AND B.id_jabatan = '". $id_jabatan ."'
							WHERE A.level >= 4
						) AS AA
						GROUP BY INDEX_MENU
					) AS AAA
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
	}
?>