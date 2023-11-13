<?php
	class M_tr_masuk_pos extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_tr_masuk_pos_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT
								A.INPOS_ID
								,A.POS_ID
								,A.TEMA_NAMA
								,A.INPOS_PERIODETHN
								,A.INPOS_PERIODEMNTH
								,A.INPOS_PETUGAS
								,A.INPOS_DITERIMA
								,A.INPOS_DTTRAN
								,A.INPOS_LOKASI
								,A.INPOS_NOMINAL
								,A.INPOS_KET
								,A.INPOS_DTINS
								,A.INPOS_DTUPDT
								,A.INPOS_USERINS
								,A.INPOS_USERUPDT
								,A.INPOS_KODEKANTOR
								,COALESCE(B.POS_KODE,'') AS POS_KODE
								,COALESCE(B.POS_NAMA,'') AS POS_NAMA
								,COALESCE(B.POS_PICLOK,'') AS POS_PICLOK
								,COALESCE(B.POS_ALMTDETAIL,'') AS POS_ALMTDETAIL
								,COALESCE(MAX(C.IMG_FILE),'') AS IMG_FILE
								,COALESCE(MAX(C.IMG_LINK),'') AS IMG_LINK
							FROM tb_tr_masuk_pos AS A
							LEFT JOIN tb_pos AS B 
							ON A.POS_ID = B.POS_ID AND A.INPOS_KODEKANTOR = B.POS_KODEKANTOR
							LEFT JOIN tb_images AS C ON A.INPOS_ID = C.ID AND A.INPOS_KODEKANTOR = C.IMG_KODEKANTOR AND C.IMG_GRUP = 'TRINPOS'
							".$cari." 
							GROUP BY
								A.INPOS_ID
								,A.POS_ID
								,A.TEMA_NAMA
								,A.INPOS_PERIODETHN
								,A.INPOS_PERIODEMNTH
								,A.INPOS_PETUGAS
								,A.INPOS_DITERIMA
								,A.INPOS_DTTRAN
								,A.INPOS_LOKASI
								,A.INPOS_NOMINAL
								,A.INPOS_KET
								,A.INPOS_DTINS
								,A.INPOS_DTUPDT
								,A.INPOS_USERINS
								,A.INPOS_USERUPDT
								,A.INPOS_KODEKANTOR
								,COALESCE(B.POS_KODE,'')
								,COALESCE(B.POS_NAMA,'')
								,COALESCE(B.POS_PICLOK,'')
								,COALESCE(B.POS_ALMTDETAIL,'')
							ORDER BY A.INPOS_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_tr_masuk_pos_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.INPOS_ID) AS JUMLAH
										FROM tb_tr_masuk_pos AS A
										LEFT JOIN tb_pos AS B 
										ON A.POS_ID = B.POS_ID AND A.INPOS_KODEKANTOR = B.POS_KODEKANTOR
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
			$POS_ID,
			$TEMA_NAMA,
			$INPOS_PERIODETHN,
			$INPOS_PERIODEMNTH,
			$INPOS_PETUGAS,
			$INPOS_DITERIMA,
			$INPOS_DTTRAN,
			$INPOS_LOKASI,
			$INPOS_NOMINAL,
			$INPOS_KET,
			$INPOS_USERINS,
			$INPOS_USERUPDT,
			$INPOS_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_tr_masuk_pos
				(
					INPOS_ID,
					POS_ID,
					TEMA_NAMA,
					INPOS_PERIODETHN,
					INPOS_PERIODEMNTH,
					INPOS_PETUGAS,
					INPOS_DITERIMA,
					INPOS_DTTRAN,
					INPOS_LOKASI,
					INPOS_NOMINAL,
					INPOS_KET,
					INPOS_DTINS,
					INPOS_DTUPDT,
					INPOS_USERINS,
					INPOS_USERUPDT,
					INPOS_KODEKANTOR



				)
				VALUES
				(
					(
						SELECT CONCAT('INPOS',FRMTGL,ORD) AS INPOS_ID
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
								COALESCE(MAX(CAST(RIGHT(INPOS_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_tr_masuk_pos
								WHERE DATE(INPOS_DTINS) = DATE(NOW())
								AND INPOS_KODEKANTOR = '".$INPOS_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$POS_ID."',
					'".$TEMA_NAMA."',
					'".$INPOS_PERIODETHN."',
					'".$INPOS_PERIODEMNTH."',
					'".$INPOS_PETUGAS."',
					'".$INPOS_DITERIMA."',
					'".$INPOS_DTTRAN."',
					'".$INPOS_LOKASI."',
					'".$INPOS_NOMINAL."',
					'".$INPOS_KET."',
					NOW(),
					NOW(),
					'".$INPOS_USERINS."',
					'".$INPOS_USERUPDT."',
					'".$INPOS_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$INPOS_ID,
			$POS_ID,
			$TEMA_NAMA,
			$INPOS_PERIODETHN,
			$INPOS_PERIODEMNTH,
			$INPOS_PETUGAS,
			$INPOS_DITERIMA,
			$INPOS_DTTRAN,
			$INPOS_LOKASI,
			$INPOS_NOMINAL,
			$INPOS_KET,
			$INPOS_USERUPDT,
			$INPOS_KODEKANTOR
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
					UPDATE tb_tr_masuk_pos SET
						
						POS_ID = '".$POS_ID."',
						TEMA_NAMA = '".$TEMA_NAMA."',
						INPOS_PERIODETHN = '".$INPOS_PERIODETHN."',
						INPOS_PERIODEMNTH = '".$INPOS_PERIODEMNTH."',
						INPOS_PETUGAS = '".$INPOS_PETUGAS."',
						INPOS_DITERIMA = '".$INPOS_DITERIMA."',
						INPOS_DTTRAN = '".$INPOS_DTTRAN."',
						INPOS_LOKASI = '".$INPOS_LOKASI."',
						INPOS_NOMINAL = '".$INPOS_NOMINAL."',
						INPOS_KET = '".$INPOS_KET."',
						INPOS_USERUPDT = '".$INPOS_USERUPDT."',
						INPOS_DTUPDT = NOW()
					WHERE INPOS_KODEKANTOR = '".$INPOS_KODEKANTOR."' AND INPOS_ID = '".$INPOS_ID
					."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($INPOS_KODEKANTOR,$INPOS_ID)
		{
			$this->db->query("
						DELETE FROM tb_tr_masuk_pos 
						WHERE INPOS_KODEKANTOR = '".$INPOS_KODEKANTOR."'
						AND INPOS_ID = '".$INPOS_ID."'
					");
		}
		
		
		function get_tr_tb_masuk_pos($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_tr_masuk_pos', array($berdasarkan => $cari,'INPOS_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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