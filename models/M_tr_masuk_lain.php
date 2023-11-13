<?php
	class M_tr_masuk_lain extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_tr_masuk_lain_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT
								A.INLAIN_ID
								,A.INLAIN_KAT
								,A.INLAIN_NAMA
								,A.INLAIN_TAHUN
								,A.INLAIN_BULAN
								,A.INLAIN_DIBERIKAN
								,A.INLAIN_DITERIMA
								,A.INLAIN_DTTRAN
								,A.INLAIN_LOKASI
								,A.INLAIN_NOMINAL
								,A.INLAIN_KET
								,A.INLAIN_DTINS
								,A.INLAIN_DTUPDT
								,A.INLAIN_USERINS
								,A.INLAIN_USERUPDT
								,A.INLAIN_KODEKANTOR
								,COALESCE(MAX(C.IMG_FILE),'') AS IMG_FILE
								,COALESCE(MAX(C.IMG_LINK),'') AS IMG_LINK
							FROM tb_tr_masuk_lain AS A
							LEFT JOIN tb_images AS C ON A.INLAIN_ID = C.ID AND A.INLAIN_KODEKANTOR = C.IMG_KODEKANTOR AND C.IMG_GRUP = 'TRINLAIN'
							".$cari." 
							GROUP BY
								A.INLAIN_ID
								,A.INLAIN_KAT
								,A.INLAIN_NAMA
								,A.INLAIN_TAHUN
								,A.INLAIN_BULAN
								,A.INLAIN_DIBERIKAN
								,A.INLAIN_DITERIMA
								,A.INLAIN_DTTRAN
								,A.INLAIN_LOKASI
								,A.INLAIN_NOMINAL
								,A.INLAIN_KET
								,A.INLAIN_DTINS
								,A.INLAIN_DTUPDT
								,A.INLAIN_USERINS
								,A.INLAIN_USERUPDT
								,A.INLAIN_KODEKANTOR
							ORDER BY A.INLAIN_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_tr_masuk_lain_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.INLAIN_ID) AS JUMLAH
										FROM tb_tr_masuk_lain AS A
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
		
		
		
		function simpan(
			$INLAIN_KAT,
			$INLAIN_TAHUN,
			$INLAIN_BULAN,
			$INLAIN_DIBERIKAN,
			$INLAIN_DITERIMA,
			$INLAIN_DTTRAN,
			$INLAIN_LOKASI,
			$INLAIN_NOMINAL,
			$INLAIN_KET,
			$INLAIN_USERINS,
			$INLAIN_USERUPDT,
			$INLAIN_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_tr_masuk_lain
				(
					INLAIN_ID,
					INLAIN_KAT,
					INLAIN_TAHUN,
					INLAIN_BULAN,
					INLAIN_DIBERIKAN,
					INLAIN_DITERIMA,
					INLAIN_DTTRAN,
					INLAIN_LOKASI,
					INLAIN_NOMINAL,
					INLAIN_KET,
					INLAIN_DTINS,
					INLAIN_DTUPDT,
					INLAIN_USERINS,
					INLAIN_USERUPDT,
					INLAIN_KODEKANTOR



				)
				VALUES
				(
					(
						SELECT CONCAT('INLAIN',FRMTGL,ORD) AS INLAIN_ID
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
								COALESCE(MAX(CAST(RIGHT(INLAIN_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_tr_masuk_lain
								WHERE DATE(INLAIN_DTINS) = DATE(NOW())
								AND INLAIN_KODEKANTOR = '".$INLAIN_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$INLAIN_KAT."',
					'".$INLAIN_TAHUN."',
					'".$INLAIN_BULAN."',
					'".$INLAIN_DIBERIKAN."',
					'".$INLAIN_DITERIMA."',
					'".$INLAIN_DTTRAN."',
					'".$INLAIN_LOKASI."',
					'".$INLAIN_NOMINAL."',
					'".$INLAIN_KET."',
					NOW(),
					NOW(),
					'".$INLAIN_USERINS."',
					'".$INLAIN_USERUPDT."',
					'".$INLAIN_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$INLAIN_ID,
			$INLAIN_KAT,
			$INLAIN_TAHUN,
			$INLAIN_BULAN,
			$INLAIN_DIBERIKAN,
			$INLAIN_DITERIMA,
			$INLAIN_DTTRAN,
			$INLAIN_LOKASI,
			$INLAIN_NOMINAL,
			$INLAIN_KET,
			$INLAIN_USERUPDT,
			$INLAIN_KODEKANTOR
		)
		{
			/*$data = array
			(
			   'nama_jabatan' => $nama,
			   'ket_jabatan' => $ket,
			   'user' => $id_user
			);
			
			//$this->db->where('id_jabatan', $id);
			$this->db->update('tb_jabatan', $data,array('id_jabatan' => $id,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));*/
			
			$query = "
					UPDATE tb_tr_masuk_lain SET
						
						INLAIN_KAT = '".$INLAIN_KAT."',
						INLAIN_TAHUN = '".$INLAIN_TAHUN."',
						INLAIN_BULAN = '".$INLAIN_BULAN."',
						INLAIN_DIBERIKAN = '".$INLAIN_DIBERIKAN."',
						INLAIN_DITERIMA = '".$INLAIN_DITERIMA."',
						INLAIN_DTTRAN = '".$INLAIN_DTTRAN."',
						INLAIN_LOKASI = '".$INLAIN_LOKASI."',
						INLAIN_NOMINAL = '".$INLAIN_NOMINAL."',
						INLAIN_KET = '".$INLAIN_KET."',
						INLAIN_USERUPDT = '".$INLAIN_USERUPDT."',
						INLAIN_DTUPDT = NOW()
					WHERE INLAIN_KODEKANTOR = '".$INLAIN_KODEKANTOR."' AND INLAIN_ID = '".$INLAIN_ID
					."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($INLAIN_KODEKANTOR,$INLAIN_ID)
		{
			$this->db->query("
						DELETE FROM tb_tr_masuk_lain 
						WHERE INLAIN_KODEKANTOR = '".$INLAIN_KODEKANTOR."'
						AND INLAIN_ID = '".$INLAIN_ID."'
					");
		}
		
		
		function get_tr_tb_masuk_lain($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_tr_masuk_lain', array($berdasarkan => $cari,'INLAIN_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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