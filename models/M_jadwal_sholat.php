<?php
	class M_jadwal_sholat extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function list_jadwal_sholat_limit($cari,$limit,$offset)
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							SELECT * FROM tb_jadwal_sholat
							".$cari." ORDER BY JSHT_DTINS DESC LIMIT ".$offset.",".$limit
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
		
		function count_jadwal_sholat_limit($cari)
		{
			$query = $this->db->query("
										SELECT COUNT(JSHT_ID) AS JUMLAH 
										FROM tb_jadwal_sholat
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
			$JSHT_DT,
			$JSHT_IMSAK,
			$JSHT_SUBUH,
			$JSHT_TERBIT,
			$JSHT_DZUHUR,
			$JSHT_ASHAR,
			$JSHT_MAGRIB,
			$JSHT_ISYA,
			$JSHT_KET,
			$JSHT_USERINS,
			$JSHT_USERUPDT,
			$JSHT_KODEKANTOR
		)
		{
			
			$query = "
				INSERT INTO tb_jadwal_sholat
				(
					JSHT_ID,
					JSHT_DT,
					JSHT_IMSAK,
					JSHT_SUBUH,
					JSHT_TERBIT,
					JSHT_DZUHUR,
					JSHT_ASHAR,
					JSHT_MAGRIB,
					JSHT_ISYA,
					JSHT_KET,
					JSHT_USERINS,
					JSHT_USERUPDT,
					JSHT_DTINS,
					JSHT_DTUPDT,
					JSHT_KODEKANTOR



				)
				VALUES
				(
					(
						SELECT CONCAT('JSHT',FRMTGL,ORD) AS JSHT_ID
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
								COALESCE(MAX(CAST(RIGHT(JSHT_ID,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_jadwal_sholat
								-- WHERE DATE_FORMAT(JSHT_DTINS,'%m-%Y') = DATE_FORMAT(NOW(),'%m-%Y')
								WHERE DATE(JSHT_DTINS) = DATE(NOW())
								AND JSHT_KODEKANTOR = '".$JSHT_KODEKANTOR."'
							) AS A
						) AS AA
					),
					'".$JSHT_DT."',
					'".$JSHT_IMSAK."',
					'".$JSHT_SUBUH."',
					'".$JSHT_TERBIT."',
					'".$JSHT_DZUHUR."',
					'".$JSHT_ASHAR."',
					'".$JSHT_MAGRIB."',
					'".$JSHT_ISYA."',
					'".$JSHT_KET."',
					'".$JSHT_USERINS."',
					'".$JSHT_USERUPDT."',
					NOW(),
					NOW(),
					'".$JSHT_KODEKANTOR."'

				)
			";
			$query = $this->db->query($query);
			
		}
		
		function edit(
			$JSHT_ID,
			$JSHT_DT,
			$JSHT_IMSAK,
			$JSHT_SUBUH,
			$JSHT_TERBIT,
			$JSHT_DZUHUR,
			$JSHT_ASHAR,
			$JSHT_MAGRIB,
			$JSHT_ISYA,
			$JSHT_KET,
			$JSHT_USERUPDT,
			$JSHT_KODEKANTOR
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
					UPDATE tb_jadwal_sholat SET
						
						JSHT_DT = '".$JSHT_DT."',
						JSHT_IMSAK = '".$JSHT_IMSAK."',
						JSHT_SUBUH = '".$JSHT_SUBUH."',
						JSHT_TERBIT = '".$JSHT_TERBIT."',
						JSHT_DZUHUR = '".$JSHT_DZUHUR."',
						JSHT_ASHAR = '".$JSHT_ASHAR."',
						JSHT_MAGRIB = '".$JSHT_MAGRIB."',
						JSHT_ISYA = '".$JSHT_ISYA."',
						JSHT_KET = '".$JSHT_KET."',
						JSHT_USERUPDT = '".$JSHT_USERUPDT."',
						JSHT_DTUPDT = NOW()
					WHERE JSHT_KODEKANTOR = '".$JSHT_KODEKANTOR."' AND JSHT_ID = '".$JSHT_ID."'
					";
			$query = $this->db->query($query);
			
		}
		
		function hapus($JSHT_KODEKANTOR,$JSHT_ID)
		{
			$this->db->query("
						DELETE FROM tb_jadwal_sholat 
						WHERE JSHT_KODEKANTOR = '".$JSHT_KODEKANTOR."'
						AND JSHT_ID = '".$JSHT_ID."'
					");
		}
		
		
		function get_jadwal_sholat($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_jadwal_sholat', array($berdasarkan => $cari,'JSHT_KODEKANTOR' => $this->session->userdata('ses_kode_kantor')));
            if($query->num_rows() > 0)
            {
                return $query->row();
            }
            else
            {
                return false;
            }
        }
		
		
		function getJadwalSholatToday()
		{
			//$query = $this->db->query("SELECT * FROM tb_jabatan ".$cari." ORDER BY nama_jabatan ASC LIMIT ".$offset.",".$limit);
			$query = $this->db->query(" 
							
								SELECT
									COALESCE(NOW_JSHT_DT,JSHT_DT) AS JSHT_DT,
									COALESCE(NOW_JSHT_IMSAK,JSHT_IMSAK) AS JSHT_IMSAK,
									COALESCE(NOW_JSHT_SUBUH,JSHT_SUBUH) AS JSHT_SUBUH,
									COALESCE(NOW_JSHT_TERBIT,JSHT_TERBIT) AS JSHT_TERBIT,
									COALESCE(NOW_JSHT_DZUHUR,JSHT_DZUHUR) AS JSHT_DZUHUR,
									COALESCE(NOW_JSHT_ASHAR,JSHT_ASHAR) AS JSHT_ASHAR,
									COALESCE(NOW_JSHT_MAGRIB,JSHT_MAGRIB) AS JSHT_MAGRIB,
									COALESCE(NOW_JSHT_ISYA,JSHT_ISYA) AS JSHT_ISYA
								FROM
								(    
									SELECT 
										A.NOW_JSHT_DT 
										,A.NOW_JSHT_IMSAK
										,A.NOW_JSHT_SUBUH
										,A.NOW_JSHT_TERBIT
										,A.NOW_JSHT_DZUHUR
										,A.NOW_JSHT_ASHAR
										,A.NOW_JSHT_MAGRIB
										,A.NOW_JSHT_ISYA
										,B.JSHT_DT 
										,B.JSHT_IMSAK
										,B.JSHT_SUBUH
										,B.JSHT_TERBIT
										,B.JSHT_DZUHUR
										,B.JSHT_ASHAR
										,B.JSHT_MAGRIB
										,B.JSHT_ISYA
									FROM 
									(
										SELECT
											(JSHT_DT) AS NOW_JSHT_DT,
											(JSHT_IMSAK) AS NOW_JSHT_IMSAK,
											(JSHT_SUBUH) AS NOW_JSHT_SUBUH,
											(JSHT_TERBIT) AS NOW_JSHT_TERBIT,
											(JSHT_DZUHUR) AS NOW_JSHT_DZUHUR,
											(JSHT_ASHAR) AS NOW_JSHT_ASHAR,
											(JSHT_MAGRIB) AS NOW_JSHT_MAGRIB,
											(JSHT_ISYA) AS NOW_JSHT_ISYA
										FROM tb_jadwal_sholat
										WHERE JSHT_DT = DATE(NOW())
									) AS A
									LEFT JOIN
									(
										SELECT
											MAX(JSHT_DT) AS JSHT_DT,
											MAX(JSHT_IMSAK) AS JSHT_IMSAK,
											MAX(JSHT_SUBUH) AS JSHT_SUBUH,
											MAX(JSHT_TERBIT) AS JSHT_TERBIT,
											MAX(JSHT_DZUHUR) AS JSHT_DZUHUR,
											MAX(JSHT_ASHAR) AS JSHT_ASHAR,
											MAX(JSHT_MAGRIB) AS JSHT_MAGRIB,
											MAX(JSHT_ISYA) AS JSHT_ISYA
										FROM tb_jadwal_sholat
									) AS B ON A.NOW_JSHT_DT = B.JSHT_DT

									UNION ALL

									SELECT 
										A.NOW_JSHT_DT 
										,A.NOW_JSHT_IMSAK
										,A.NOW_JSHT_SUBUH
										,A.NOW_JSHT_TERBIT
										,A.NOW_JSHT_DZUHUR
										,A.NOW_JSHT_ASHAR
										,A.NOW_JSHT_MAGRIB
										,A.NOW_JSHT_ISYA
										,B.JSHT_DT 
										,B.JSHT_IMSAK
										,B.JSHT_SUBUH
										,B.JSHT_TERBIT
										,B.JSHT_DZUHUR
										,B.JSHT_ASHAR
										,B.JSHT_MAGRIB
										,B.JSHT_ISYA
									FROM 
									(
										SELECT
											(JSHT_DT) AS NOW_JSHT_DT,
											(JSHT_IMSAK) AS NOW_JSHT_IMSAK,
											(JSHT_SUBUH) AS NOW_JSHT_SUBUH,
											(JSHT_TERBIT) AS NOW_JSHT_TERBIT,
											(JSHT_DZUHUR) AS NOW_JSHT_DZUHUR,
											(JSHT_ASHAR) AS NOW_JSHT_ASHAR,
											(JSHT_MAGRIB) AS NOW_JSHT_MAGRIB,
											(JSHT_ISYA) AS NOW_JSHT_ISYA
										FROM tb_jadwal_sholat
										WHERE JSHT_DT = DATE(NOW())
									) AS A
									RIGHT JOIN
									(
										SELECT
											MAX(JSHT_DT) AS JSHT_DT,
											MAX(JSHT_IMSAK) AS JSHT_IMSAK,
											MAX(JSHT_SUBUH) AS JSHT_SUBUH,
											MAX(JSHT_TERBIT) AS JSHT_TERBIT,
											MAX(JSHT_DZUHUR) AS JSHT_DZUHUR,
											MAX(JSHT_ASHAR) AS JSHT_ASHAR,
											MAX(JSHT_MAGRIB) AS JSHT_MAGRIB,
											MAX(JSHT_ISYA) AS JSHT_ISYA
										FROM tb_jadwal_sholat
									) AS B ON A.NOW_JSHT_DT = B.JSHT_DT
								) AS AA
							"
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
		
		
		public function getHariIndonesia($hariEng)
		{
			if ($hariEng == "Sunday") return"Minggu"; 
			else if ($hariEng == "Monday") return"Senin"; 
			else if ($hariEng == "Tuesday") return"Selasa"; 
			else if ($hariEng == "Wednesday") return"Rabu"; 
			else if ($hariEng == "Thursday") return"Kamis"; 
			else if ($hariEng == "Friday") return"Jumat"; 
			else if ($hariEng == "Saturday") return"Sabtu";
		}
		
		public function makeInt($angka)
		{
			if ($angka < -0.0000001)
			{
				return ceil($angka-0.0000001);
			}
			else
			{
				return floor($angka+0.0000001);
			}
		}



		public function konvhijriah($tanggal)
		{

			$array_bulan = array("Muharram", "Safar", "Rabiul Awwal", "Rabiul Akhir",
			"Jumadil Awwal","Jumadil Akhir", "Rajab", "Sya’ban",
			"Ramadhan","Syawwal", "Zulqaidah", "Zulhijjah");

			$date = $this->makeInt(substr($tanggal,8,2));
			$month = $this->makeInt(substr($tanggal,5,2));
			$year = $this->makeInt(substr($tanggal,0,4));

			if (($year>1582)||(($year == "1582") && ($month > 10))||(($year == "1582") && ($month=="10")&&($date >14)))
			{
				$jd = $this->makeInt((1461*($year+4800+$this->makeInt(($month-14)/12)))/4)+
				$this->makeInt((367*($month-2-12*($this->makeInt(($month-14)/12))))/12)-
				$this->makeInt( (3*($this->makeInt(($year+4900+$this->makeInt(($month-14)/12))/100))) /4)+
				$date-32075;
			}
			else
			{
				$jd = 367*$year-$this->makeInt((7*($year+5001+$this->makeInt(($month-9)/7)))/4)+
				$this->makeInt((275*$month)/9)+$date+1729777;
			}
			
			$wd = $jd%7;
			$l = $jd-1948440+10632;
			$n=$this->makeInt(($l-1)/10631);
			$l=$l-10631*$n+354;
			$z=($this->makeInt((10985-$l)/5316))*($this->makeInt((50*$l)/17719))+($this->makeInt($l/5670))*($this->makeInt((43*$l)/15238));
			$l=$l-($this->makeInt((30-$z)/15))*($this->makeInt((17719*$z)/50))-($this->makeInt($z/16))*($this->makeInt((15238*$z)/43))+29;
			$m=$this->makeInt((24*$l)/709);
			//$d=$l-$this->makeInt((709*$m)/24);
			$d=$l-$this->makeInt((709*$m)/24) - 1;
			$y=30*$n+$z-30;
			$g = $m-1;
			
			$final = "$d $array_bulan[$g] $y H";
			
			return $final;
		}

	}
?>