<?php
	class M_zportal_dash extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function ST_PENERIMAAN_PERPERIODE($MUZ_ID,$cari)
		{
			//month(str_to_date(LEFT(A.INPOS_PERIODEMNTH,3),'%b'))
			$query = "
						SELECT
							INMUZ_KAT
							,CONCAT(YEAR_DTTRAN,'-',MONTH_DTTRAN,' | ',INMUZ_KAT) AS PERIODE
							,SUM(AA.INMUZ_NOMINAL) AS INMUZ_NOMINAL
						FROM
						(
							SELECT  
								*
								,MONTHNAME(INMUZ_DTTRAN) AS MONTH_DTTRAN
								,YEAR(INMUZ_DTTRAN) AS YEAR_DTTRAN
								
							FROM tb_tr_masuk_muzaqi WHERE MUZ_ID = '".$MUZ_ID."'
						) AS AA
						".$cari."
						GROUP BY INMUZ_KAT,CONCAT(YEAR_DTTRAN,'-',MONTH_DTTRAN);
			
						
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