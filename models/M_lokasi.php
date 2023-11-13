<?php
	class M_lokasi extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_prov($cari,$limit,$offset)
		{
			$query = $this->db->query(" 
								SELECT id,name FROM provinces
								".$cari." 
								GROUP BY id,name
								ORDER BY name ASC
								LIMIT ".$offset.",".$limit
							);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_kabkot($cari,$limit,$offset)
		{
			$query = $this->db->query(" 
								SELECT id,province_id,name FROM tb_kabkot
								".$cari." 
								GROUP BY id,province_id,name
								ORDER BY name ASC
								LIMIT ".$offset.",".$limit
							);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_kecamatan($cari,$limit,$offset)
		{
			$query = $this->db->query(" 
								SELECT id,kabkot_id,name FROM tb_kecamatan
								".$cari." 
								GROUP BY id,kabkot_id,name
								ORDER BY name ASC
								LIMIT ".$offset.",".$limit
							);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function list_desa($cari,$limit,$offset)
		{
			$query = $this->db->query(" 
								SELECT id,kec_id,name FROM tb_desa
								".$cari." 
								GROUP BY id,kec_id,name
								ORDER BY name ASC
								LIMIT ".$offset.",".$limit
							);
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