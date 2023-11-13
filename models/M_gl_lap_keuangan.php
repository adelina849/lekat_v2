<?php
	class M_gl_lap_keuangan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_lap_keuangan
		(
			$kode_kantor
			,$id_bank
			,$ref
			,$cari
			,$tgl_dari
			,$tgl_sampai
		)
		{
			$query = "CALL PROC_JURNAL_2('".$kode_kantor."','".$tgl_dari."','".$tgl_sampai."',0,900000,'".$cari."');";
			
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