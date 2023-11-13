<?php
	class M_dash extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_persen_laporan_kecamatan($KEC_NAMA)
		{
			$query = $this->db->query("CALL SP_PERSEN_LAPORAN('".$KEC_NAMA."');");
			mysqli_next_result( $this->db->conn_id );
			
			if($query->num_rows() > 0)
			{ 
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function exec_export_rangking_kecamatan($KEC_NAMA,$PERIODE)
		{
			$query = $this->db->query("CALL SP_PERSEN_LAPORAN_INSERT_TO_TB_RANGKING('".$KEC_NAMA."','".$PERIODE."');");
			mysqli_next_result( $this->db->conn_id );
			
			if($query->num_rows() > 0)
			{ 
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_rangking_from_table($cari)
		{
			$query = "SELECT * FROM tb_rangking ".$cari." ORDER BY NO_RANGKING ASC";
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
		
		function ubah_rangking($rangking,$kec_id,$periode)
		{
			$query = "
						UPDATE tb_rangking SET NO_RANGKING = '".$rangking."' WHERE KEC_ID = '".$kec_id."' AND PERIODE = '".$periode."';
					";
						
			$query = $this->db->query($query);
		}
		
		function akumulasi_rangking($dari,$sampai)
		{
			$query = "
						SELECT
							KEC_NAMA,SUM(NO_RANGKING) AS RANG_TERKECIL,COUNT(NO_RANGKING) AS CNT
						FROM tb_rangking
						WHERE tgl_ins BETWEEN '".$dari."' AND '".$sampai."'
							GROUP BY KEC_NAMA
						ORDER BY CNT DESC,RANG_TERKECIL ASC
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
		
		function list_arsip_persen_laporan_kecamatan($cari,$KAT_PERIODE,$PERIODE)
		{
			$query = $this->db->query("CALL SP_LAP_ARSIPPERIODE('".$cari."','".$KAT_PERIODE."','".$PERIODE."');");
			mysqli_next_result( $this->db->conn_id );
			
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