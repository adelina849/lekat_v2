<?php
	class M_muzaqi extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_muzaqi_limit($cari,$limit,$offset)
		{
			$query = $this->db->query("
										SELECT
											A.MUZ_ID,
											A.KMUZ_ID,
											A.PRSH_NAMA,
											A.PROF_NAMA,
											A.MUZ_KODE,
											A.MUZ_NAMA,
											A.MUZ_TLP,
											A.MUZ_EMAIL,
											A.MUZ_GAJI,
											A.MUZ_TLAHIR,
											A.MUZ_DTLAHIR,
											A.MUZ_PROV,
											A.MUZ_KAB,
											A.MUZ_KEC,
											A.MUZ_DESA,
											A.MUZ_ALMTDETAIL,
											A.MUZ_LONGI,
											A.MUZ_LATI,
											A.MUZ_KET,
											A.MUZ_AVATAR,
											A.MUZ_AVATARLINK,
											A.MUZ_USER,
											A.MUZ_PASS,
											A.MUZ_PASSORI,
											A.MUZ_AKTIFASI,
											A.MUZ_LINKAKTIFASI,
											A.MUZ_USERINS,
											A.MUZ_USERUPDT,
											A.MUZ_DTINS,
											A.MUZ_DTUPDT,
											A.MUZ_KODEKANTOR

											, COALESCE(B.KMUZ_NAMA,'') AS KMUZ_NAMA
											, COALESCE(C.name,'') AS PROVINSI
											, COALESCE(D.name,'') AS KABKOT
											, COALESCE(E.name,'') AS KECAMATAN
											, COALESCE(F.name,'') AS DESA
											
										FROM tb_muzaqi AS A
										LEFT JOIN tb_kat_muzaqi AS B ON A.KMUZ_ID = B.KMUZ_ID AND A.MUZ_KODEKANTOR = B.KMUZ_KODEKANTOR
										LEFT JOIN provinces AS C ON A.MUZ_PROV = C.id
										LEFT JOIN tb_kabkot AS D ON A.MUZ_KAB = D.id
										LEFT JOIN tb_kecamatan AS E ON A.MUZ_KEC = E.id
										LEFT JOIN tb_desa AS F ON A.MUZ_DESA = F.id
										
										".$cari." 
										GROUP BY
											A.MUZ_ID,
											A.KMUZ_ID,
											A.PRSH_NAMA,
											A.PROF_NAMA,
											A.MUZ_KODE,
											A.MUZ_NAMA,
											A.MUZ_TLP,
											A.MUZ_EMAIL,
											A.MUZ_GAJI,
											A.MUZ_TLAHIR,
											A.MUZ_DTLAHIR,
											A.MUZ_PROV,
											A.MUZ_KAB,
											A.MUZ_KEC,
											A.MUZ_DESA,
											A.MUZ_ALMTDETAIL,
											A.MUZ_LONGI,
											A.MUZ_LATI,
											A.MUZ_KET,
											A.MUZ_AVATAR,
											A.MUZ_AVATARLINK,
											A.MUZ_USER,
											A.MUZ_PASS,
											A.MUZ_PASSORI,
											A.MUZ_AKTIFASI,
											A.MUZ_LINKAKTIFASI,
											A.MUZ_USERINS,
											A.MUZ_USERUPDT,
											A.MUZ_DTINS,
											A.MUZ_DTUPDT,
											A.MUZ_KODEKANTOR
											, COALESCE(B.KMUZ_NAMA,'')
											, COALESCE(C.name,'')
											, COALESCE(D.name,'')
											, COALESCE(E.name,'')
											, COALESCE(F.name,'')
										ORDER BY A.MUZ_DTINS DESC 
										LIMIT ".$offset.",".$limit);
			if($query->num_rows() > 0)
			{
				return $query;
			}
			else
			{
				return false;
			}
		}
		
		function count_muzaqi_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(A.MUZ_ID) AS JUMLAH  
										FROM tb_muzaqi AS A
										LEFT JOIN tb_kat_muzaqi AS B ON A.KMUZ_ID = B.KMUZ_ID AND A.MUZ_KODEKANTOR = B.KMUZ_KODEKANTOR
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
			$KMUZ_ID,
			$PRSH_NAMA,
			$PROF_NAMA,
			$MUZ_KODE,
			$MUZ_NAMA,
			$MUZ_TLP,
			$MUZ_EMAIL,
			$MUZ_GAJI,
			$MUZ_TLAHIR,
			$MUZ_DTLAHIR,
			$MUZ_PROV,
			$MUZ_KAB,
			$MUZ_KEC,
			$MUZ_DESA,
			$MUZ_ALMTDETAIL,
			$MUZ_LONGI,
			$MUZ_LATI,
			$MUZ_KET,
			//$MUZ_AVATAR,
			//$MUZ_AVATARLINK,
			$MUZ_USER,
			$MUZ_PASS,
			$MUZ_PASSORI,
			$MUZ_AKTIFASI,
			$MUZ_USERINS,
			$MUZ_USERUPDT,
			$MUZ_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_muzaqi
				(
					MUZ_ID,
					KMUZ_ID,
					PRSH_NAMA,
					PROF_NAMA,
					MUZ_KODE,
					MUZ_NAMA,
					MUZ_TLP,
					MUZ_EMAIL,
					MUZ_GAJI,
					MUZ_TLAHIR,
					MUZ_DTLAHIR,
					MUZ_PROV,
					MUZ_KAB,
					MUZ_KEC,
					MUZ_DESA,
					MUZ_ALMTDETAIL,
					MUZ_LONGI,
					MUZ_LATI,
					MUZ_KET,
					MUZ_AVATAR,
					MUZ_AVATARLINK,
					MUZ_USER,
					MUZ_PASS,
					MUZ_PASSORI,
					MUZ_AKTIFASI,
					MUZ_LINKAKTIFASI,
					MUZ_USERINS,
					MUZ_USERUPDT,
					MUZ_DTINS,
					MUZ_DTUPDT,
					MUZ_KODEKANTOR


				)
				VALUES
				(
					(
						SELECT CONCAT('MUZ',FRMTGL,ORD) AS MUZ_ID
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
								COALESCE(MAX(CAST(RIGHT(MUZ_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_muzaqi
								-- WHERE DATE_FORMAT(MUZ_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(MUZ_DTINS) = DATE(NOW())
								AND MUZ_KODEKANTOR = '".$MUZ_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$KMUZ_ID."',
					'".$PRSH_NAMA."',
					'".$PROF_NAMA."',
					'".$MUZ_KODE."',
					'".$MUZ_NAMA."',
					'".$MUZ_TLP."',
					'".$MUZ_EMAIL."',
					'".$MUZ_GAJI."',
					'".$MUZ_TLAHIR."',
					'".$MUZ_DTLAHIR."',
					'".$MUZ_PROV."',
					'".$MUZ_KAB."',
					'".$MUZ_KEC."',
					'".$MUZ_DESA."',
					'".$MUZ_ALMTDETAIL."',
					'".$MUZ_LONGI."',
					'".$MUZ_LATI."',
					'".$MUZ_KET."',
					'',
					'',
					'".$MUZ_USER."',
					'".$MUZ_PASS."',
					'".$MUZ_PASSORI."',
					'".$MUZ_AKTIFASI."',
					
					(
						-- SELECT CONCAT('MUZ',FRMTGL,ORD) AS MUZ_ID
						SELECT MD5(CONCAT('MUZ',FRMTGL,ORD)) AS MUZ_ID
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
								COALESCE(MAX(CAST(RIGHT(MUZ_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_muzaqi
								-- WHERE DATE_FORMAT(MUZ_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(MUZ_DTINS) = DATE(NOW())
								AND MUZ_KODEKANTOR = '".$MUZ_KODEKANTOR."'
							) AS A
						) AS AA
					)
					,
					'".$MUZ_USERINS."',
					'".$MUZ_USERUPDT."',
					NOW(),
					NOW(),
					'".$MUZ_KODEKANTOR."'

				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit
		(
			$MUZ_ID,
			$KMUZ_ID,
			$PRSH_NAMA,
			$PROF_NAMA,
			$MUZ_KODE,
			$MUZ_NAMA,
			$MUZ_TLP,
			$MUZ_EMAIL,
			$MUZ_GAJI,
			$MUZ_TLAHIR,
			$MUZ_DTLAHIR,
			$MUZ_PROV,
			$MUZ_KAB,
			$MUZ_KEC,
			$MUZ_DESA,
			$MUZ_ALMTDETAIL,
			$MUZ_LONGI,
			$MUZ_LATI,
			$MUZ_KET,
			/*$MUZ_AVATAR,
			$MUZ_AVATARLINK,
			/*$MUZ_USER,*/
			$MUZ_PASS,
			$MUZ_PASSORI,
			$MUZ_USERUPDT,
			$MUZ_KODEKANTOR
		)
		{
			$query = "
					UPDATE tb_muzaqi SET
						KMUZ_ID = '".$KMUZ_ID."',
						PRSH_NAMA = '".$PRSH_NAMA."',
						PROF_NAMA = '".$PROF_NAMA."',
						MUZ_KODE = '".$MUZ_KODE."',
						MUZ_NAMA = '".$MUZ_NAMA."',
						MUZ_TLP = '".$MUZ_TLP."',
						MUZ_EMAIL = '".$MUZ_EMAIL."',
						MUZ_GAJI = '".$MUZ_GAJI."',
						MUZ_TLAHIR = '".$MUZ_TLAHIR."',
						MUZ_DTLAHIR = '".$MUZ_DTLAHIR."',
						MUZ_PROV = '".$MUZ_PROV."',
						MUZ_KAB = '".$MUZ_KAB."',
						MUZ_KEC = '".$MUZ_KEC."',
						MUZ_DESA = '".$MUZ_DESA."',
						MUZ_ALMTDETAIL = '".$MUZ_ALMTDETAIL."',
						MUZ_LONGI = '".$MUZ_LONGI."',
						MUZ_LATI = '".$MUZ_LATI."',
						MUZ_KET = '".$MUZ_KET."',
						MUZ_USERUPDT = '".$MUZ_USERUPDT."',
						MUZ_DTUPDT = NOW()

					WHERE MUZ_KODEKANTOR = '".$MUZ_KODEKANTOR."' AND MUZ_ID = '".$MUZ_ID."'
					";
			$query = $this->db->query($query);
		}
		
		function edit_aktifasi
		(
			$MUZ_LINKAKTIFASI,
			$MUZ_AKTIFASI
		)
		{
			$query = "
					UPDATE tb_muzaqi SET
						MUZ_AKTIFASI = '".$MUZ_AKTIFASI."'
						,MUZ_DTUPDT = NOW()

					WHERE MUZ_LINKAKTIFASI = '".$MUZ_LINKAKTIFASI."'
					";
			$query = $this->db->query($query);
		}
		
		function edit_avatar
		(
			$MUZ_ID,
			$MUZ_AVATAR,
			$MUZ_AVATARLINK
		)
		{
			$query = "
					UPDATE tb_muzaqi SET
						MUZ_AVATAR = '".$MUZ_AVATAR."'
						,MUZ_AVATARLINK = '".$MUZ_AVATARLINK."'
						,MUZ_DTUPDT = NOW()

					WHERE MUZ_ID = '".$MUZ_ID."'
					";
			$query = $this->db->query($query);
		}
		
		function edit_password
		(
			$MUZ_ID,
			$MUZ_PASSORI
		)
		{
			$query = "
					UPDATE tb_muzaqi SET
						MUZ_PASS = '".md5($MUZ_PASSORI)."'
						,MUZ_PASSORI = '".$MUZ_PASSORI."'
						,MUZ_DTUPDT = NOW()

					WHERE MUZ_ID = '".$MUZ_ID."'
					";
			$query = $this->db->query($query);
		}
		
		function hapus($MUZ_KODEKANTOR,$MUZ_ID)
		{
			$this->db->query("DELETE FROM tb_muzaqi WHERE MUZ_ID = '".$MUZ_ID."' AND MUZ_KODEKANTOR = '".$MUZ_KODEKANTOR."' ;");
		}
		
		
		function get_muzaqi($berdasarkan,$cari)
		{
			$query = "SELECT * FROM tb_muzaqi WHERE ".$berdasarkan." = '".$cari."'";
			$query = $this->db->query($query);
			if($query->num_rows() > 0)
			{
				return $query->row();
			}
			else
			{
				return false;
			}
		}
		
		function get_muzaqi_byId($MUZ_ID,$MUZ_KODEKANTOR)
		{
			$query = "SELECT * FROM tb_muzaqi WHERE MUZ_ID = '".$MUZ_ID."' AND MUZ_KODEKANTOR = '".$MUZ_KODEKANTOR."'";
			$query = $this->db->query($query);
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