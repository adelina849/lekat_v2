<?php
	class M_barcode extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_barcode_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT * FROM tb_barcode
							".$cari." ORDER BY BAR_INDX DESC LIMIT ".$offset.",".$limit
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
		
		function count_barcode_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(BAR_INDX) AS JUMLAH 
										FROM tb_barcode
										".$cari
									);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		
		
		function simpan($BAR_TYPE,$BAR_KODEKANTOR)
		{
			
			$query = "
				INSERT INTO tb_barcode
				(
					BAR_INDX,
					BAR_KODE,
					BAR_TYPE,
					BAR_KODEKANTOR


				)
				VALUES
				(
					(
						SELECT CONCAT(ORD) AS BAR_INDX
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
								COALESCE(MAX(CAST(RIGHT(BAR_INDX,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_barcode
								-- WHERE DATE_FORMAT(BAR_INDX,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								-- WHERE DATE(BAR_INDX) = DATE(NOW())
								
							) AS A
						) AS AA
					),
					(
						SELECT FRMTGL AS BAR_KODE
						FROM
						(
							-- SELECT CONCAT(Y,M,D,H,MM,DD) AS FRMTGL
							SELECT CONCAT(DD,ORD,D,M,Y,H,MM) AS FRMTGL
							 ,CASE
								WHEN ORD >= 10 THEN CONCAT('00',CAST(ORD AS CHAR))
								WHEN ORD >= 100 THEN CONCAT('0',CAST(ORD AS CHAR))
								WHEN ORD >= 1000 THEN CAST(ORD AS CHAR)
								ELSE CONCAT('000',CAST(ORD AS CHAR))
								END AS ORD
							FROM
							(
								SELECT 
								CAST(MID(NOW(),3,2) AS CHAR) AS Y,
								CAST(MID(NOW(),6,2) AS CHAR) AS M,
								MID(NOW(),9,2) AS D,
								CAST(MID(NOW(),12,2) AS CHAR) AS H,
								CAST(MID(NOW(),15,2) AS CHAR) AS MM,
								CAST(MID(NOW(),18,2) AS CHAR) AS DD,
								( COALESCE(MAX(CAST(RIGHT(BAR_INDX,4) AS UNSIGNED)),0) + 1) AS ORD FROM tb_barcode
								-- WHERE DATE(tgl_ins) = DATE(NOW())
							) AS A
						) AS AA
					),
					'".$BAR_TYPE."',
					'".$BAR_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		
		function hapus($BAR_KODEKANTOR,$BAR_KODE)
		{
			$this->db->query("
						DELETE FROM tb_barcode 
						WHERE BAR_KODEKANTOR = '".$BAR_KODEKANTOR."'
						AND BAR_KODE = '".$BAR_KODE."'
					");
		}
		
		
		function get_barcode($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_barcode', array($berdasarkan => $cari,'BAR_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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