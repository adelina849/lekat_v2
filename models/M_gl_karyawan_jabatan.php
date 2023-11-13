<?php
	class M_gl_karyawan_jabatan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_karyawan_jabatan_limit($cari,$limit,$offset)
		{
			$query = "
				SELECT
					A.*
					,DATE_ADD(tgl_dari, INTERVAL masa_percobaan DAY) AS Akhir_percobaan
					,CASE 
						WHEN DATE(CURDATE()) <= DATE_ADD(tgl_dari, INTERVAL masa_percobaan DAY) THEN
							0
						ELSE
							1
						END AS STATUS_MASA_PERCOBAAN
					,COALESCE(B.no_karyawan,'') AS no_karyawan
					,COALESCE(B.nik_karyawan,'') AS nik_karyawan
					,COALESCE(B.nama_karyawan,'') AS nama_karyawan
					,COALESCE(B.pnd,'') AS pnd
					,COALESCE(B.tlp,'') AS tlp
					,COALESCE(B.email,'') AS email
					,COALESCE(B.avatar,'') AS avatar
					,COALESCE(B.avatar_url,'') AS avatar_url
					,COALESCE(B.alamat,'') AS alamat
					,COALESCE(B.kelamin,'') AS kelamin
					,COALESCE(B.tmp_lahir,'') AS tmp_lahir
					,COALESCE(B.tgl_lahir,'') AS tgl_lahir
					
					,CASE 
						WHEN (YEAR(CURDATE()) - YEAR(COALESCE(B.tgl_lahir,''))) < 150 THEN 
							(YEAR(CURDATE()) - YEAR(COALESCE(B.tgl_lahir,'')))
						ELSE
							0
						END
					AS USIA
					
					,COALESCE(B.ket_karyawan,'') AS ket_karyawan
					,COALESCE(B.isAktif,'') AS isAktif
					,COALESCE(B.isDokter,'') AS isDokter
					,COALESCE(B.lamar_via,'') AS lamar_via
					,COALESCE(B.nilai_ujian,'') AS nilai_ujian
					,COALESCE(B.ket_hasil_ujian,'') AS ket_hasil_ujian
					,COALESCE(B.user,'') AS user
					,COALESCE(B.pass,'') AS pass
					,COALESCE(B.tgl_updt_pass ,'') AS tgl_updt_pass 
					,COALESCE(C.kode_jabatan,'') AS kode_jabatan
					,COALESCE(C.nama_jabatan,'') AS nama_jabatan
					,COALESCE(C.hirarki,'') AS hirarki_jabatan
					,COALESCE(D.kode_dept,'') AS kode_dept
					,COALESCE(D.nama_dept,'') AS nama_dept
					,COALESCE(D.hirarki,'') AS hirarki_dept
                    
                    ,COALESCE(E.kode_jabatan_old,'') AS kode_jabatan_old
					,COALESCE(E.nama_jabatan_old,'') AS nama_jabatan_old
					,COALESCE(E.hirarki_jabatan_old,'') AS hirarki_jabatan_old
					,COALESCE(E.kode_dept_old,'') AS kode_dept_old
					,COALESCE(E.nama_dept_old,'') AS nama_dept_old
					,COALESCE(E.hirarki_dept_old,'') AS hirarki_dept_old
                    
				FROM tb_karyawan_jabatan AS A
				LEFT JOIN tb_karyawan AS B ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_jabatan AS C ON A.id_jabatan = C.id_jabatan AND A.kode_kantor = C.kode_kantor
				LEFT JOIN tb_dept AS D ON A.id_dept = D.id_dept AND A.kode_kantor = D.kode_kantor
                LEFT JOIN
                (
					SELECT 
						AA.kode_kantor
                        ,AA.id_karyawan AS id_karyawan_old
                        ,AA.id_jabatan AS id_jabatan_old
                        ,AA.id_dept AS id_dept_old
                        ,AA.tgl_ins AS tgl_ins_old 
                        
                        ,COALESCE(BB.kode_jabatan,'') AS kode_jabatan_old
						,COALESCE(BB.nama_jabatan,'') AS nama_jabatan_old
						,COALESCE(BB.hirarki,'') AS hirarki_jabatan_old
						,COALESCE(CC.kode_dept,'') AS kode_dept_old
						,COALESCE(CC.nama_dept,'') AS nama_dept_old
						,COALESCE(CC.hirarki,'') AS hirarki_dept_old
                        
                    FROM tb_karyawan_jabatan AS AA
                    LEFT JOIN tb_jabatan AS BB ON AA.id_jabatan = BB.id_jabatan AND AA.kode_kantor = BB.kode_kantor
					LEFT JOIN tb_dept AS CC ON AA.id_dept = CC.id_dept AND AA.kode_kantor = CC.kode_kantor
                    HAVING MAX(AA.tgl_ins) -- KUNCINYA INI
                    ORDER BY AA.tgl_ins DESC
                    -- LIMIT 0,1
                ) AS E ON A.kode_kantor = E.kode_kantor AND A.id_karyawan = E.id_karyawan_old AND E.tgl_ins_old < A.tgl_ins
				".$cari." ORDER BY A.tgl_ins DESC LIMIT ".$offset.",".$limit;
			
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
		
		function count_karyawan_jabatan_limit($cari)
		{
			$query = "
				SELECT
					COUNT(A.id_kj) AS JUMLAH
				FROM tb_karyawan_jabatan AS A
				LEFT JOIN tb_karyawan AS B ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_jabatan AS C ON A.id_jabatan = C.id_jabatan AND A.kode_kantor = C.kode_kantor
				LEFT JOIN tb_dept AS D ON A.id_dept = D.id_dept AND A.kode_kantor = D.kode_kantor
				".$cari."";
			
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
		
		function list_riwayat_jabatan_karyawan($kode_kantor,$id_karyawan)
		{
			$query = "
				SELECT A.*
					,COALESCE(B.kode_jabatan,'') AS kode_jabatan
					,COALESCE(B.nama_jabatan,'') AS nama_jabatan
					,COALESCE(C.kode_dept,'') AS kode_dept
					,COALESCE(C.nama_dept,'') AS nama_dept
					,DATE_ADD(A.tgl_dari, INTERVAL A.masa_percobaan DAY) AS Akhir_percobaan
				FROM tb_karyawan_jabatan AS A
				LEFT JOIN tb_jabatan AS B ON A.id_jabatan = B.id_jabatan AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_dept AS C ON A.id_dept = C.id_dept AND A.kode_kantor = B.kode_kantor
				WHERE A.kode_kantor = '".$kode_kantor."' AND A.id_karyawan ='".$id_karyawan."'
				ORDER BY A.tgl_ins DESC
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
		
		function list_karyawan_belum_terdaftar_promo($cari,$limit,$offset)
		{
			$query = "
				SELECT A.* FROM tb_karyawan AS A
				LEFT JOIN tb_karyawan_jabatan AS B 
					ON A.kode_kantor = B.kode_kantor 
					AND A.id_karyawan = B.id_karyawan 
					AND DATE(CURDATE()) <= DATE_ADD(tgl_dari, INTERVAL masa_percobaan DAY)
				WHERE B.id_karyawan IS NULL
				".$cari." ORDER BY A.no_karyawan ASC, A.nama_karyawan ASC LIMIT ".$offset.",".$limit;
			
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
		
		function simpan(
		
			$id_karyawan,
			$id_jabatan,
			$id_dept,
			$kode_promosi,
			$no_surat,
			$periode,
			$rekomendasi,
			$tgl_dari,
			$tgl_sampai,
			$keterangan,
			$tipe,
			$masa_percobaan,
			$status,
			$isAktif,
			$user_ins,
			$user_updt,
			$kode_kantor

		)
		{
			$strquery = "
				INSERT INTO tb_karyawan_jabatan
				(
					id_kj,
					id_karyawan,
					id_jabatan,
					id_dept,
					kode_promosi,
					no_surat,
					periode,
					rekomendasi,
					tgl_dari,
					tgl_sampai,
					keterangan,
					tipe,
					masa_percobaan,
					status,
					isAktif,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
					
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_karyawan_jabatan
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
								COALESCE(MAX(CAST(RIGHT(id_kj,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_karyawan_jabatan
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_karyawan."',
					'".$id_jabatan."',
					'".$id_dept."',
					'".$kode_promosi."',
					'".$no_surat."',
					'".$periode."',
					'".$rekomendasi."',
					'".$tgl_dari."',
					'".$tgl_sampai."',
					'".$keterangan."',
					'".$tipe."',
					'".$masa_percobaan."',
					'".$status."',
					'".$isAktif."',
					NOW(),
					NOW(),
					'".$user_ins."',
					'".$user_updt."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
		
			$id_kj,
			$id_karyawan,
			$id_jabatan,
			$id_dept,
			$kode_promosi,
			$no_surat,
			$periode,
			$rekomendasi,
			$tgl_dari,
			$tgl_sampai,
			$keterangan,
			$tipe,
			$masa_percobaan,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_karyawan_jabatan SET
					
						id_karyawan= '".$id_karyawan."',
						id_jabatan= '".$id_jabatan."',
						id_dept= '".$id_dept."',
						kode_promosi= '".$kode_promosi."',
						no_surat= '".$no_surat."',
						periode= '".$periode."',
						rekomendasi= '".$rekomendasi."',
						tgl_dari= '".$tgl_dari."',
						tgl_sampai= '".$tgl_sampai."',
						keterangan= '".$keterangan."',
						tipe= '".$tipe."',
						masa_percobaan= '".$masa_percobaan."',
						tgl_updt= NOW(),
						user_updt= '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_kj = '".$id_kj
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan, $id)
		{
			/*HAPUS karyawan_jabatan*/
				$strquery = "DELETE FROM tb_karyawan_jabatan WHERE ".$berdasarkan." = '".$id."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS karyawan_jabatan*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit_ubah_nilai($kode_kantor,$id_kj,$nilai,$status,$ket_nilai)
		{
			$strquery = "
					UPDATE tb_karyawan_jabatan SET
					
						nilai = '".$nilai."',
						status = '".$status."',
						ket_nilai = '".$ket_nilai."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_kj = '".$id_kj
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
			
			return "BERHASIL";
		}
		
		function get_karyawan_jabatan($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_karyawan_jabatan', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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