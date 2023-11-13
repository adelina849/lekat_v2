<?php
	class M_jadwal_kegiatan extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_jadwal_kegiatan_limit($cari,$order,$limit,$offset)
		{
			$query = $this->db->query("
										SELECT
											A.JKEG_ID,
											A.DEPT_KODE,
											A.JKEG_NAMA,
											A.JKEG_DASAR,
											A.JKEG_KETUA,
											A.JKEG_SUMBERDANA,
											A.JKEG_PERIODE,
											A.JKEG_NOMINAL,
											A.JKEG_PROV,
											A.JKEG_KABKOT,
											A.JKEG_KEC,
											A.JKEG_DESA,
											A.JKEG_ALAMATDETAIL,
											A.JKEG_DTDARI,
											A.JKEG_DTSAMPAI,
											A.JKEG_KET,
											A.JKEG_LATI,
											A.JKEG_LONGI,
											A.JKEG_DTINS,
											A.JKEG_DTUPDT,
											A.JKEG_USERINS,
											A.JKEG_USERUPDT,
											A.JKEG_KODEKANTOR,
											A.JKEG_ISPUBLISH
											,
											CASE
											WHEN A.JKEG_ISPUBLISH = 1 THEN
												'YA'
											ELSE
												'TIDAK'
											END AS PUBLISH
											,COALESCE(B.DEPT_KODE,'') As DEPT_KODE
											,COALESCE(B.DEPT_NAMA,'') As DEPT_NAMA
											, COALESCE(C.name,'') AS PROVINSI
											, COALESCE(D.name,'') AS KABKOT
											, COALESCE(E.name,'') AS KECAMATAN
											, COALESCE(F.name,'') AS DESA
											, COALESCE(MAX(G.IMG_FILE),'') AS IMG_FILE
											, COALESCE(MAX(G.IMG_LINK),'') AS IMG_LINK
										FROM tb_jadwal_kegiatan AS A 
										LEFT JOIN tb_departemen AS B ON A.DEPT_KODE = B.DEPT_KODE AND A.JKEG_KODEKANTOR = B.DEPT_KODEKANTOR
										LEFT JOIN provinces AS C ON A.JKEG_PROV = C.id
										LEFT JOIN tb_kabkot AS D ON A.JKEG_KABKOT = D.id
										LEFT JOIN tb_kecamatan AS E ON A.JKEG_KEC = E.id
										LEFT JOIN tb_desa AS F ON A.JKEG_DESA = F.id
										LEFT JOIN tb_images AS G ON A.JKEG_ID = G.ID AND A.JKEG_KODEKANTOR = G.IMG_KODEKANTOR AND G.IMG_GRUP = 'kegiatan'
										".$cari."
										GROUP BY
											A.JKEG_ID,
											A.DEPT_KODE,
											A.JKEG_NAMA,
											A.JKEG_DASAR,
											A.JKEG_KETUA,
											A.JKEG_SUMBERDANA,
											A.JKEG_PERIODE,
											A.JKEG_NOMINAL,
											A.JKEG_PROV,
											A.JKEG_KABKOT,
											A.JKEG_KEC,
											A.JKEG_DESA,
											A.JKEG_ALAMATDETAIL,
											A.JKEG_DTDARI,
											A.JKEG_DTSAMPAI,
											A.JKEG_KET,
											A.JKEG_LATI,
											A.JKEG_LONGI,
											A.JKEG_DTINS,
											A.JKEG_DTUPDT,
											A.JKEG_USERINS,
											A.JKEG_USERUPDT,
											A.JKEG_KODEKANTOR
											,COALESCE(B.DEPT_KODE,'')
											,COALESCE(B.DEPT_NAMA,'')
											, COALESCE(C.name,'')
											, COALESCE(D.name,'')
											, COALESCE(E.name,'')
											, COALESCE(F.name,'')
										".$order."
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
		
		function count_jadwal_kegiatan_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(JKEG_ID) AS JUMLAH 
										FROM tb_jadwal_kegiatan AS A 
										LEFT JOIN tb_departemen AS B 
										ON A.DEPT_KODE = B.DEPT_KODE 
										AND A.JKEG_KODEKANTOR = B.DEPT_KODEKANTOR
										".$cari);
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
			$DEPT_KODE,
			$JKEG_NAMA,
			$JKEG_DASAR,
			$JKEG_KETUA,
			$JKEG_SUMBERDANA,
			$JKEG_PERIODE,
			$JKEG_NOMINAL,
			$JKEG_PROV,
			$JKEG_KABKOT,
			$JKEG_KEC,
			$JKEG_DESA,
			$JKEG_ALAMATDETAIL,
			$JKEG_DTDARI,
			$JKEG_DTSAMPAI,
			$JKEG_KET,
			$JKEG_ISPUBLISH,
			$JKEG_LATI,
			$JKEG_LONGI,
			$JKEG_USERINS,
			$JKEG_USERUPDT,
			$JKEG_KODEKANTOR

		)
		{
			
			$query = "
				INSERT INTO tb_jadwal_kegiatan
				(
					JKEG_ID,
					DEPT_KODE,
					JKEG_NAMA,
					JKEG_DASAR,
					JKEG_KETUA,
					JKEG_SUMBERDANA,
					JKEG_PERIODE,
					JKEG_NOMINAL,
					JKEG_PROV,
					JKEG_KABKOT,
					JKEG_KEC,
					JKEG_DESA,
					JKEG_ALAMATDETAIL,
					JKEG_DTDARI,
					JKEG_DTSAMPAI,
					JKEG_KET,
					JKEG_ISPUBLISH,
					JKEG_LATI,
					JKEG_LONGI,
					JKEG_DTINS,
					JKEG_DTUPDT,
					JKEG_USERINS,
					JKEG_USERUPDT,
					JKEG_KODEKANTOR



				)
				VALUES
				(
					(
						SELECT CONCAT('JKEG',FRMTGL,ORD) AS JKEG_ID
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
								COALESCE(MAX(CAST(RIGHT(JKEG_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_jadwal_kegiatan
								-- WHERE DATE_FORMAT(JKEG_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(JKEG_DTINS) = DATE(NOW())
								AND JKEG_KODEKANTOR = '".$JKEG_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$DEPT_KODE."',
					'".$JKEG_NAMA."',
					'".$JKEG_DASAR."',
					'".$JKEG_KETUA."',
					'".$JKEG_SUMBERDANA."',
					'".$JKEG_PERIODE."',
					'".$JKEG_NOMINAL."',
					'".$JKEG_PROV."',
					'".$JKEG_KABKOT."',
					'".$JKEG_KEC."',
					'".$JKEG_DESA."',
					'".$JKEG_ALAMATDETAIL."',
					'".$JKEG_DTDARI."',
					'".$JKEG_DTSAMPAI."',
					'".$JKEG_KET."',
					'".$JKEG_ISPUBLISH."',
					'".$JKEG_LATI."',
					'".$JKEG_LONGI."',
					NOW(),
					NOW(),
					'".$JKEG_USERINS."',
					'".$JKEG_USERUPDT."',
					'".$JKEG_KODEKANTOR."'
				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$JKEG_ID,
			$DEPT_KODE,
			$JKEG_NAMA,
			$JKEG_DASAR,
			$JKEG_KETUA,
			$JKEG_SUMBERDANA,
			$JKEG_PERIODE,
			$JKEG_NOMINAL,
			$JKEG_PROV,
			$JKEG_KABKOT,
			$JKEG_KEC,
			$JKEG_DESA,
			$JKEG_ALAMATDETAIL,
			$JKEG_DTDARI,
			$JKEG_DTSAMPAI,
			$JKEG_KET,
			$JKEG_ISPUBLISH,
			$JKEG_LATI,
			$JKEG_LONGI,
			$JKEG_USERUPDT,
			$JKEG_KODEKANTOR
		)
		{
			
			$query = "
					UPDATE tb_jadwal_kegiatan SET
						DEPT_KODE= '".$DEPT_KODE."',
						JKEG_NAMA= '".$JKEG_NAMA."',
						JKEG_DASAR= '".$JKEG_DASAR."',
						JKEG_KETUA= '".$JKEG_KETUA."',
						JKEG_SUMBERDANA= '".$JKEG_SUMBERDANA."',
						JKEG_PERIODE= '".$JKEG_PERIODE."',
						JKEG_NOMINAL= '".$JKEG_NOMINAL."',
						JKEG_PROV= '".$JKEG_PROV."',
						JKEG_KABKOT= '".$JKEG_KABKOT."',
						JKEG_KEC= '".$JKEG_KEC."',
						JKEG_DESA= '".$JKEG_DESA."',
						JKEG_ALAMATDETAIL= '".$JKEG_ALAMATDETAIL."',
						JKEG_DTDARI= '".$JKEG_DTDARI."',
						JKEG_DTSAMPAI= '".$JKEG_DTSAMPAI."',
						JKEG_KET= '".$JKEG_KET."',
						JKEG_ISPUBLISH= '".$JKEG_ISPUBLISH."',
						JKEG_LATI= '".$JKEG_LATI."',
						JKEG_LONGI= '".$JKEG_LONGI."',
						JKEG_USERUPDT= '".$JKEG_USERUPDT."',
						JKEG_DTUPDT = NOW()
					WHERE JKEG_KODEKANTOR = '".$JKEG_KODEKANTOR."' AND JKEG_ID = '".$JKEG_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($JKEG_KODEKANTOR,$JKEG_ID)
		{
			$this->db->query("
						DELETE FROM tb_jadwal_kegiatan 
						WHERE JKEG_KODEKANTOR = '".$JKEG_KODEKANTOR."'
						AND JKEG_ID = '".$JKEG_ID."'
					");
		}
		
		
		function get_jadwal_kegiatan($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_jadwal_kegiatan', array($berdasarkan => $cari,'JKEG_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
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