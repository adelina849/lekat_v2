<?php
	class M_gl_pst_akuntansi extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_laba_rugi($kode_kantor,$dari,$sampai)
		{
			$query = $this->db->query("CALL PROC_LABA_RUGI_2('". $kode_kantor ."','". $dari ."','". $sampai ."')");
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_h_penjualan_cari($cari)
		{
			$query = "SELECT * FROM tb_h_penjualan ".$cari;
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