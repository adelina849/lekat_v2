<?php
	class M_perusahaan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_perusahaan_limit($cari,$limit,$offset)
		{
			$query = $this->db->query(" 
							SELECT 
								A.* 
								, COALESCE(C.name,'') AS PROVINSI
								, COALESCE(D.name,'') AS KABKOT
								, COALESCE(E.name,'') AS KECAMATAN
								, COALESCE(F.name,'') AS DESA
							FROM tb_perusahaan AS A
							LEFT JOIN provinces AS C ON A.PRSH_PROV = C.id
							LEFT JOIN tb_kabkot AS D ON A.PRSH_KAB = D.id
							LEFT JOIN tb_kecamatan AS E ON A.PRSH_KEC = E.id
							LEFT JOIN tb_desa AS F ON A.PRSH_DESA = F.id
							".$cari." ORDER BY PRSH_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_perusahaan_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.PRSH_ID) AS JUMLAH
										FROM tb_perusahaan AS A
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
			$PRSH_KODE,
			$PRSH_KAT,
			$PRSH_NAMA,
			$PRSH_BIDANG,
			$PRSH_KET,
			$PRSH_TLP,
			$PRSH_EMAIL,
			$PRSH_PROV,
			$PRSH_KAB,
			$PRSH_KEC,
			$PRSH_DESA,
			$PRSH_ALMTDETAIL,
			$PRSH_LONGI,
			$PRSH_LATI,
			$PRSH_USERINS,
			$PRSH_USERUPDT,
			$PRSH_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_perusahaan
				(
					PRSH_ID,
					PRSH_KODE,
					PRSH_KAT,
					PRSH_NAMA,
					PRSH_BIDANG,
					PRSH_KET,
					PRSH_TLP,
					PRSH_EMAIL,
					PRSH_PROV,
					PRSH_KAB,
					PRSH_KEC,
					PRSH_DESA,
					PRSH_ALMTDETAIL,
					PRSH_LONGI,
					PRSH_LATI,
					PRSH_USERINS,
					PRSH_USERUPDT,
					PRSH_DTINS,
					PRSH_DTUPDT,
					PRSH_KODEKANTOR

				)
				VALUES
				(
					(
						SELECT CONCAT('PRSH',FRMTGL,ORD) AS PRSH_ID
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
								COALESCE(MAX(CAST(RIGHT(PRSH_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_perusahaan
								WHERE DATE(PRSH_DTINS) = DATE(NOW())
								AND PRSH_KODEKANTOR = '".$PRSH_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$PRSH_KODE."',
					'".$PRSH_KAT."',
					'".$PRSH_NAMA."',
					'".$PRSH_BIDANG."',
					'".$PRSH_KET."',
					'".$PRSH_TLP."',
					'".$PRSH_EMAIL."',
					'".$PRSH_PROV."',
					'".$PRSH_KAB."',
					'".$PRSH_KEC."',
					'".$PRSH_DESA."',
					'".$PRSH_ALMTDETAIL."',
					'".$PRSH_LONGI."',
					'".$PRSH_LATI."',
					'".$PRSH_USERINS."',
					'".$PRSH_USERUPDT."'
					,NOW()
					,NOW()
					,'".$PRSH_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$PRSH_ID,
			$PRSH_KODE,
			$PRSH_KAT,
			$PRSH_NAMA,
			$PRSH_BIDANG,
			$PRSH_KET,
			$PRSH_TLP,
			$PRSH_EMAIL,
			$PRSH_PROV,
			$PRSH_KAB,
			$PRSH_KEC,
			$PRSH_DESA,
			$PRSH_ALMTDETAIL,
			$PRSH_LONGI,
			$PRSH_LATI,
			$PRSH_USERUPDT,
			$PRSH_KODEKANTOR
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
					UPDATE tb_perusahaan SET
							PRSH_KODE = '". $PRSH_KODE."',
							PRSH_KAT = '". $PRSH_KAT."',
							PRSH_NAMA = '". $PRSH_NAMA."',
							PRSH_BIDANG = '". $PRSH_BIDANG."',
							PRSH_KET = '". $PRSH_KET."',
							PRSH_TLP = '". $PRSH_TLP."',
							PRSH_EMAIL = '". $PRSH_EMAIL."',
							PRSH_PROV = '". $PRSH_PROV."',
							PRSH_KAB = '". $PRSH_KAB."',
							PRSH_KEC = '". $PRSH_KEC."',
							PRSH_DESA = '". $PRSH_DESA."',
							PRSH_ALMTDETAIL = '". $PRSH_ALMTDETAIL."',
							PRSH_LONGI = '". $PRSH_LONGI."',
							PRSH_LATI = '". $PRSH_LATI."',
							PRSH_USERUPDT = '".$PRSH_USERUPDT."',
							PRSH_DTUPDT = NOW()
					WHERE PRSH_KODEKANTOR = '".$PRSH_KODEKANTOR."' AND PRSH_ID = '".$PRSH_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($PRSH_KODEKANTOR,$PRSH_ID)
		{
			$this->db->query("
						DELETE FROM tb_perusahaan 
						WHERE PRSH_KODEKANTOR = '".$PRSH_KODEKANTOR."'
						AND PRSH_ID = '".$PRSH_ID."'
					");
		}
		
		
		function get_perusahaan($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_perusahaan', array($berdasarkan => $cari,'PRSH_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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