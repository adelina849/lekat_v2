<?php
	class M_tr_keluar_operasional extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_tr_keluar_operasional_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT
								A.OTOPRS_ID
								,A.OTOPRS_KAT
								,A.OTOPRS_NOMOR
								,A.OTOPRS_NAMA
								,A.OTOPRS_TAHUN
								,A.OTOPRS_BULAN
								,A.OTOPRS_DTTRAN
								,A.OTOPRS_DISERAHKAN
								,A.OTOPRS_DITERIMA
								,A.OTOPRS_NOMINAL
								,A.OTOPRS_PERIHAL
								,A.OTOPRS_KET
								,A.OTOPRS_DTINS
								,A.OTOPRS_DTUPDT
								,A.OTOPRS_USERINS
								,A.OTOPRS_USERUPDT
								,A.OTOPRS_KODEKANTOR
								,COALESCE(MAX(C.IMG_FILE),'') AS IMG_FILE
								,COALESCE(MAX(C.IMG_LINK),'') AS IMG_LINK
							FROM tb_tr_keluar_operasional AS A
							LEFT JOIN tb_images AS C ON A.OTOPRS_ID = C.ID AND A.OTOPRS_KODEKANTOR = C.IMG_KODEKANTOR AND C.IMG_GRUP = 'TROTOPRS'
							".$cari." 
							GROUP BY
								A.OTOPRS_ID
								,A.OTOPRS_KAT
								,A.OTOPRS_NOMOR
								,A.OTOPRS_NAMA
								,A.OTOPRS_TAHUN
								,A.OTOPRS_BULAN
								,A.OTOPRS_DTTRAN
								,A.OTOPRS_DISERAHKAN
								,A.OTOPRS_DITERIMA
								,A.OTOPRS_NOMINAL
								,A.OTOPRS_PERIHAL
								,A.OTOPRS_KET
								,A.OTOPRS_DTINS
								,A.OTOPRS_DTUPDT
								,A.OTOPRS_USERINS
								,A.OTOPRS_USERUPDT
								,A.OTOPRS_KODEKANTOR
							ORDER BY A.OTOPRS_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_tr_keluar_operasional_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.OTOPRS_ID) AS JUMLAH
										FROM tb_tr_keluar_operasional AS A
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
			$OTOPRS_KAT,
			$OTOPRS_NOMOR,
			$OTOPRS_NAMA,
			$OTOPRS_TAHUN,
			$OTOPRS_BULAN,
			$OTOPRS_DTTRAN,
			$OTOPRS_DISERAHKAN,
			$OTOPRS_DITERIMA,
			$OTOPRS_NOMINAL,
			$OTOPRS_PERIHAL,
			$OTOPRS_KET,
			$OTOPRS_USERINS,
			$OTOPRS_USERUPDT,
			$OTOPRS_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_tr_keluar_operasional
				(
					OTOPRS_ID,
					OTOPRS_KAT,
					OTOPRS_NOMOR,
					OTOPRS_NAMA,
					OTOPRS_TAHUN,
					OTOPRS_BULAN,
					OTOPRS_DTTRAN,
					OTOPRS_DISERAHKAN,
					OTOPRS_DITERIMA,
					OTOPRS_NOMINAL,
					OTOPRS_PERIHAL,
					OTOPRS_KET,
					OTOPRS_DTINS,
					OTOPRS_DTUPDT,
					OTOPRS_USERINS,
					OTOPRS_USERUPDT,
					OTOPRS_KODEKANTOR

				)
				VALUES
				(
					(
						SELECT CONCAT('OTOPRS',FRMTGL,ORD) AS OTOPRS_ID
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
								COALESCE(MAX(CAST(RIGHT(OTOPRS_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_tr_keluar_operasional
								WHERE DATE(OTOPRS_DTINS) = DATE(NOW())
								AND OTOPRS_KODEKANTOR = '".$OTOPRS_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$OTOPRS_KAT."',
					'".$OTOPRS_NOMOR."',
					'".$OTOPRS_NAMA."',
					'".$OTOPRS_TAHUN."',
					'".$OTOPRS_BULAN."',
					'".$OTOPRS_DTTRAN."',
					'".$OTOPRS_DISERAHKAN."',
					'".$OTOPRS_DITERIMA."',
					'".$OTOPRS_NOMINAL."',
					'".$OTOPRS_PERIHAL."',
					'".$OTOPRS_KET."',
					NOW(),
					NOW(),
					'".$OTOPRS_USERINS."',
					'".$OTOPRS_USERUPDT."',
					'".$OTOPRS_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$OTOPRS_ID,
			$OTOPRS_KAT,
			$OTOPRS_NOMOR,
			$OTOPRS_NAMA,
			$OTOPRS_TAHUN,
			$OTOPRS_BULAN,
			$OTOPRS_DTTRAN,
			$OTOPRS_DISERAHKAN,
			$OTOPRS_DITERIMA,
			$OTOPRS_NOMINAL,
			$OTOPRS_PERIHAL,
			$OTOPRS_KET,
			$OTOPRS_USERUPDT,
			$OTOPRS_KODEKANTOR
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
					UPDATE tb_tr_keluar_operasional SET
						
						OTOPRS_KAT = '".$OTOPRS_KAT."',
						OTOPRS_NOMOR = '".$OTOPRS_NOMOR."',
						OTOPRS_NAMA = '".$OTOPRS_NAMA."',
						OTOPRS_TAHUN = '".$OTOPRS_TAHUN."',
						OTOPRS_BULAN = '".$OTOPRS_BULAN."',
						OTOPRS_DTTRAN = '".$OTOPRS_DTTRAN."',
						OTOPRS_DISERAHKAN = '".$OTOPRS_DISERAHKAN."',
						OTOPRS_DITERIMA = '".$OTOPRS_DITERIMA."',
						OTOPRS_NOMINAL = '".$OTOPRS_NOMINAL."',
						OTOPRS_PERIHAL = '".$OTOPRS_PERIHAL."',
						OTOPRS_KET = '".$OTOPRS_KET."',
						OTOPRS_USERUPDT = '".$OTOPRS_USERUPDT."',
						OTOPRS_DTUPDT = NOW()
					WHERE OTOPRS_KODEKANTOR = '".$OTOPRS_KODEKANTOR."' AND OTOPRS_ID = '".$OTOPRS_ID
					."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($OTOPRS_KODEKANTOR,$OTOPRS_ID)
		{
			$this->db->query("
						DELETE FROM tb_tr_keluar_operasional 
						WHERE OTOPRS_KODEKANTOR = '".$OTOPRS_KODEKANTOR."'
						AND OTOPRS_ID = '".$OTOPRS_ID."'
					");
		}
		
		
		function get_tr_keluar_operasional($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_tr_keluar_operasional', array($berdasarkan => $cari,'OTOPRS_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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