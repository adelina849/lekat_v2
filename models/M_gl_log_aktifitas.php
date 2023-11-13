<?php
	class M_gl_log_aktifitas extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		public function list_log_aktifitas_limit($cari,$order_by,$limit,$offset)
		{
			$query = 
				"
					SELECT 
						A.* 
						,COALESCE(B.no_karyawan,'') AS no_karyawan
						,COALESCE(B.nama_karyawan,'') AS nama_karyawan
						,COALESCE(B.avatar,'') AS avatar
						,COALESCE(B.avatar_url,'') AS avatar_url
					FROM tb_log AS A
					LEFT JOIN tb_karyawan AS B ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
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
		
		function count_log_aktifitas_limit($cari)
		{
			$query = 
				"
					SELECT 
						COUNT(A.waktu) AS JUMLAH
					FROM tb_log AS A
					LEFT JOIN tb_karyawan AS B ON A.id_karyawan = B.id_karyawan AND A.kode_kantor = B.kode_kantor
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
	
	
		function list_request_perubahan($cari,$order_by,$limit,$offset)
		{
			$query = 
				"
					SELECT A.*
					FROM tb_h_penjualan AS A
					-- WHERE A.sts_penjualan IN ('SELESAI','PEMBAYARAN') 
					-- AND DATE(A.tgl_h_penjualan) = DATE(NOW()) AND A.isApprove > 0
					".$cari."
					-- ORDER BY COUNT(A.isApprove) DESC
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
	
	
		function count_request_perubahan($cari)
		{
			$query = 
				"
					SELECT 
						COUNT(A.id_h_penjualan) AS JUMLAH
					FROM tb_h_penjualan AS A
					-- WHERE A.sts_penjualan IN ('SELESAI','PEMBAYARAN') 
					-- AND DATE(A.tgl_h_penjualan) = DATE(NOW()) AND A.isApprove > 0
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
	}
?>