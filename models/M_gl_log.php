<?php
	class M_gl_log extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function simpan_log(
			$id_karyawan,
			$aktifitas,
			$ket_aktifitas,
			$ipkom,
			$namakom,
			$kode_kantor
		)
		{
			
			$strquery = "
				INSERT INTO tb_log
				(
					id_karyawan,
					aktifitas,
					ket_aktifitas,
					waktu,
					ipkom,
					namakom,
					kode_kantor
					
				)
				VALUES
				(
					
					'".$id_karyawan."',
					'".$aktifitas."',
					'".$ket_aktifitas."',
					NOW(),
					'".$ipkom."',
					'".$namakom."',
					'".$kode_kantor."'
				)
			";
			
			$this->db->query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
			//$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function simpan_query($strquery)
		{
			/*EKSEKUSI DULU QUERYNYA*/
				$this->db->query($strquery);
			/*EKSEKUSI DULU QUERYNYA*/
			
			/*SIMPAN DI TB QUERYNYA DENGAN CEK STATUS PENGATURAN APAKAH AKAN DI CATAT*/
				if($this->session->userdata('catat_query') == 'Y')
				{
					$sts_query = strpos(base_url(),".com");
					if($sts_query > 0)
					{
						$hasil_sts_query = 'SERVER';
					}
					else
					{
						$hasil_sts_query = 'LOCAL';
					}
					
					$query = "
					INSERT INTO tb_update_server
					(
						id_table
						,id_karyawan
						,str_query
						,sts_query
						,waktu
						,ipkom
						,namekom
						,kode_kantor
					)
					Values
					(
						(SELECT COALESCE(MAX(id_table),0) AS TEST FROM tb_update_server AS A WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."') + 1
						,'".$this->session->userdata('ses_id_karyawan')."'
						,'".str_replace("'","|",$strquery)."'
						,'".$hasil_sts_query."'
						,NOW()
						,'".$this->M_gl_pengaturan->getUserIpAddr()."'
						,'".gethostname()."'
						,'".$this->session->userdata('ses_kode_kantor')."'
					)
					";
					
					$this->db->query($query);
				}
			/*SIMPAN DI TB QUERYNYA DENGAN CEK STATUS PENGATURAN APAKAH AKAN DI CATAT*/
		}
		
		function notif_count_pasien_dokter($kode_kantor,$id_dokter,$jabatan)
		{
			if($jabatan == "Admin Aplikasi")
			{
				$query = 
				"
					SELECT COUNT(id_h_penjualan) AS JUMLAH FROM tb_h_penjualan WHERE kode_kantor = '".$kode_kantor."' AND DATE(tgl_h_penjualan) = DATE(NOW()) AND sts_penjualan = 'PENDING' ;
				";
			}
			else
			{
				$query = 
				"
					SELECT COUNT(id_h_penjualan) AS JUMLAH FROM tb_h_penjualan WHERE kode_kantor = '".$kode_kantor."' AND DATE(tgl_h_penjualan) = DATE(NOW()) AND sts_penjualan = 'PENDING' AND id_dokter = '".$id_dokter."';
				";
			}
			
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query->row()->JUMLAH;
			}
			else
			{
				return 0;
			}
		}
	
		function notif_list_pasien($kode_kantor,$id_dokter,$jabatan)
		{
			if($jabatan == "Admin Aplikasi")
			{
				$query =
				"
					SELECT A.* 
						,COALESCE(B.no_costumer,'') AS no_costumer
						,COALESCE(B.nama_lengkap,'') AS nama_costumer
						,COALESCE(B.avatar,'') AS avatar
						,COALESCE(B.avatar_url,'') AS avatar_url
						,TIMESTAMPDIFF
						(
							MINUTE,
							A.tgl_ins,
							NOW()
						) AS MENIT_TUNGGU
					FROM tb_h_penjualan AS A
					LEFT JOIN tb_costumer AS B ON A.id_costumer = B.id_costumer AND A.kode_kantor = B.kode_kantor
					WHERE A.kode_kantor = '".$kode_kantor."'  AND DATE(A.tgl_h_penjualan) = DATE(NOW()) AND A.sts_penjualan = 'PENDING' ORDER BY A.tgl_ins ASC;
				";
			}
			else
			{
				$query =
				"
					SELECT A.* 
						,COALESCE(B.no_costumer,'') AS no_costumer
						,COALESCE(B.nama_lengkap,'') AS nama_costumer
						,COALESCE(B.avatar,'') AS avatar
						,COALESCE(B.avatar_url,'') AS avatar_url
						,TIMESTAMPDIFF
						(
							MINUTE,
							A.tgl_ins,
							NOW()
						) AS MENIT_TUNGGU
					FROM tb_h_penjualan AS A
					LEFT JOIN tb_costumer AS B ON A.id_costumer = B.id_costumer AND A.kode_kantor = B.kode_kantor
					WHERE A.kode_kantor = '".$kode_kantor."'  AND DATE(A.tgl_h_penjualan) = DATE(NOW()) AND A.id_dokter = '".$id_dokter."' AND A.sts_penjualan = 'PENDING' ORDER BY A.tgl_ins ASC;
				";
			}
			
			
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