<?php
	class M_lap_pemasukan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function lap_per_periode($tahun,$bulan,$cari,$order)
		{
			$query = "
						SELECT OPCD,PERIODE,ID,NAMA,DT,NOMINAL,PETUGAS,KANTOR,KABKOT
						FROM
						(
							SELECT 
								'KOTAK AMAL' AS OPCD
								, COALESCE(D.name,'') KABKOT
								, CONCAT(A.INPOS_PERIODETHN,'-',A.INPOS_PERIODEMNTH) AS PERIODE
								, A.POS_ID AS ID
								,COALESCE(B.POS_NAMA,'') AS NAMA
								-- ,month(str_to_date(LEFT(A.INPOS_PERIODEMNTH,3),'%b')) AS PERIODE_ORICONCAT		
								,A.INPOS_DTTRAN AS DT
								,A.INPOS_DTINS
								,SUM(A.INPOS_NOMINAL) AS NOMINAL
								,A.INPOS_PETUGAS AS PETUGAS
								,A.INPOS_KODEKANTOR AS KANTOR
								-- ,COUNT(A.INPOS_ID) AS DARI
							FROM tb_tr_masuk_pos AS A
							LEFT JOIN tb_pos AS B ON A.POS_ID = B.POS_ID AND A.INPOS_KODEKANTOR = B.POS_KODEKANTOR
							LEFT JOIN tb_kabkot AS D ON B.POS_KAB = D.id
							-- WHERE (A.INPOS_PERIODETHN) = YEAR(NOW()) AND A.INPOS_PERIODEMNTH = MONTHNAME(DATE_ADD(NOW(), INTERVAL -1 MONTH) )
							WHERE (A.INPOS_PERIODETHN) = '".$tahun."' AND A.INPOS_PERIODEMNTH = '".$bulan."'
							GROUP BY A.POS_ID, COALESCE(D.name,''),COALESCE(B.POS_NAMA,''),CONCAT(A.INPOS_PERIODETHN,'-',A.INPOS_PERIODEMNTH)
							,A.INPOS_DTTRAN,A.INPOS_DTINS,A.INPOS_PETUGAS,A.INPOS_KODEKANTOR
						) AS AA
						".$cari."
						".$order."
						-- ORDER BY AA.DT ASC, AA.INPOS_DTINS ASC,AA.NAMA ASC
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