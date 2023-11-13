<?php
	class M_tr_masuk_muzaqi extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_tr_masuk_muzaqi_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT
								A.INMUZ_ID,
								A.MUZ_ID,
								A.BNK_ID,
								A.AML_ID,
								A.INMUZ_KAT,
								A.INMUZ_BANK,
								A.INMUZ_ATASNAMA,
								A.INMUZ_NOREK,
								A.INMUZ_NOMINAL,
								A.INMUZ_DTTRAN,
								A.INMUZ_VERIFIED,
								CASE WHEN A.INMUZ_VERIFIED = 1 THEN 'TERVERIFIKASI'
								ELSE 'BELUM TERVERIFIKASI' END AS STATUS_VERIFIKASI,
								A.INMUZ_IMG,
								A.INMUZ_IMGLINK,
								A.INMUZ_USERINS,
								A.INMUZ_USERUPDT,
								A.INMUZ_DTINS,
								A.INMUZ_DTUPDT,
								A.INMUZ_KODEKANTOR,
								COALESCE(B.MUZ_KODE,'') AS MUZ_KODE,
								COALESCE(B.MUZ_NAMA,'') AS MUZ_NAMA,
								COALESCE(B.MUZ_EMAIL,'') AS MUZ_EMAIL,
								COALESCE(B.MUZ_ALMTDETAIL,'') AS MUZ_ALMTDETAIL
								
								
							FROM tb_tr_masuk_muzaqi AS A
							LEFT JOIN tb_muzaqi AS B ON A.MUZ_ID = B.MUZ_ID AND A.INMUZ_KODEKANTOR = B.MUZ_KODEKANTOR
							".$cari." 
							ORDER BY A.INMUZ_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_tr_masuk_muzaqi_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.INMUZ_ID) AS JUMLAH
										FROM tb_tr_masuk_muzaqi AS A
										LEFT JOIN tb_muzaqi AS B ON A.MUZ_ID = B.MUZ_ID AND A.INMUZ_KODEKANTOR = B.MUZ_KODEKANTOR
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
			$MUZ_ID,
			$BNK_ID,
			$AML_ID,
			$INMUZ_KAT,
			$INMUZ_BANK,
			$INMUZ_ATASNAMA,
			$INMUZ_NOREK,
			$INMUZ_NOMINAL,
			$INMUZ_DTTRAN,
			$INMUZ_VERIFIED,
			$INMUZ_IMG,
			$INMUZ_IMGLINK,
			$INMUZ_USERINS,
			$INMUZ_USERUPDT,
			$INMUZ_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_tr_masuk_muzaqi
				(
					INMUZ_ID,
					MUZ_ID,
					BNK_ID,
					AML_ID,
					INMUZ_KAT,
					INMUZ_BANK,
					INMUZ_ATASNAMA,
					INMUZ_NOREK,
					INMUZ_NOMINAL,
					INMUZ_DTTRAN,
					INMUZ_VERIFIED,
					INMUZ_IMG,
					INMUZ_IMGLINK,
					INMUZ_USERINS,
					INMUZ_USERUPDT,
					INMUZ_DTINS,
					INMUZ_DTUPDT,
					INMUZ_KODEKANTOR

				)
				VALUES
				(
					(
						SELECT CONCAT('INMUZ',FRMTGL,ORD) AS INMUZ_ID
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
								COALESCE(MAX(CAST(RIGHT(INMUZ_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_tr_masuk_muzaqi
								WHERE DATE(INMUZ_DTINS) = DATE(NOW())
								AND INMUZ_KODEKANTOR = '".$INMUZ_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$MUZ_ID."',
					'".$BNK_ID."',
					'".$AML_ID."',
					'".$INMUZ_KAT."',
					'".$INMUZ_BANK."',
					'".$INMUZ_ATASNAMA."',
					'".$INMUZ_NOREK."',
					'".$INMUZ_NOMINAL."',
					'".$INMUZ_DTTRAN."',
					'".$INMUZ_VERIFIED."',
					'".$INMUZ_IMG."',
					'".$INMUZ_IMGLINK."',
					'".$INMUZ_USERINS."',
					'".$INMUZ_USERUPDT."',
					NOW(),
					NOW(),
					'".$INMUZ_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$INMUZ_ID,
			$MUZ_ID,
			$BNK_ID,
			$AML_ID,
			$INMUZ_KAT,
			$INMUZ_BANK,
			$INMUZ_ATASNAMA,
			$INMUZ_NOREK,
			$INMUZ_NOMINAL,
			$INMUZ_DTTRAN,
			$INMUZ_IMG,
			$INMUZ_IMGLINK,
			$INMUZ_USERUPDT,
			$INMUZ_KODEKANTOR
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
					UPDATE tb_tr_masuk_muzaqi SET
						
						MUZ_ID = '".$MUZ_ID."',
						BNK_ID = '".$BNK_ID."',
						AML_ID = '".$AML_ID."',
						INMUZ_KAT = '".$INMUZ_KAT."',
						INMUZ_BANK = '".$INMUZ_BANK."',
						INMUZ_ATASNAMA = '".$INMUZ_ATASNAMA."',
						INMUZ_NOREK = '".$INMUZ_NOREK."',
						INMUZ_NOMINAL = '".$INMUZ_NOMINAL."',
						INMUZ_DTTRAN = '".$INMUZ_DTTRAN."',
						INMUZ_IMG = '".$INMUZ_IMG."',
						INMUZ_IMGLINK = '".$INMUZ_IMGLINK."',
						INMUZ_USERUPDT = '".$INMUZ_USERUPDT."',
						INMUZ_DTUPDT = NOW()
					WHERE INMUZ_KODEKANTOR = '".$INMUZ_KODEKANTOR."' AND INMUZ_ID = '".$INMUZ_ID
					."'
					";
			$query = $this->db->query($query);
			
		}
		
		function verified(
			$INMUZ_ID,
			$INMUZ_KODEKANTOR
		)
		{
			
			$query = "
					UPDATE tb_tr_masuk_muzaqi SET
						INMUZ_VERIFIED = 	CASE WHEN INMUZ_VERIFIED = 0 THEN 1
											ELSE 0
											END
					WHERE INMUZ_KODEKANTOR = '".$INMUZ_KODEKANTOR."' AND INMUZ_ID = '".$INMUZ_ID
					."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($INMUZ_KODEKANTOR,$INMUZ_ID)
		{
			$this->db->query("
						DELETE FROM tb_tr_masuk_muzaqi 
						WHERE INMUZ_KODEKANTOR = '".$INMUZ_KODEKANTOR."'
						AND INMUZ_ID = '".$INMUZ_ID."'
					");
		}
		
		
		function get_tr_tb_masuk_muzaqi($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_tr_masuk_muzaqi', array($berdasarkan => $cari,'INMUZ_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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