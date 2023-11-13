<?php
	class M_log extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function simpan(
			$LOG_TYPE,
			$LOG_NAMA,
			$LOG_ISI,
			$LOG_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_log
				(
					LOG_ID,
					LOG_TYPE,
					LOG_NAMA,
					LOG_ISI,
					LOG_DTINS,
					LOG_KODEKANTOR
				)
				VALUES
				(
					(
						SELECT CONCAT('LOG',FRMTGL,ORD) AS LOG_ID
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
								COALESCE(MAX(CAST(RIGHT(LOG_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_log
								WHERE DATE(LOG_DTINS) = DATE(NOW())
								AND LOG_KODEKANTOR = '".$LOG_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$LOG_TYPE."',
					'".$LOG_NAMA."',
					'".$LOG_ISI."',
					NOW(),
					'".$LOG_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		
	}
?>