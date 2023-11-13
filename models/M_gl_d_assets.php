<?php
	class M_gl_d_assets extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_d_assets_limit($cari,$limit,$offset)
		{
			$query = 
			"
				SELECT
					A.*
					,COALESCE(B.nama_kat_assets,'') AS nama_kat_assets
					,COALESCE(C.nama_bank,'') AS nama_bank
					,COALESCE(C.norek,'') AS norek
					,TIMESTAMPDIFF(MONTH, A.tgl_beli, NOW()) AS selisih_bulan
					,ROUND(((A.nominal/100) * A.penyusutan) * (TIMESTAMPDIFF(MONTH, A.tgl_beli, NOW())),0) AS NOMINAL_PENYUSUTAN
					,A.nominal - (ROUND(((A.nominal/100) * A.penyusutan) * (TIMESTAMPDIFF(MONTH, A.tgl_beli, NOW())),0)) AS SETELAH_PENYUSUTAN
				FROM tb_d_assets AS A 
				LEFT JOIN tb_kat_assets AS B ON A.id_kat_assets = B.id_kat_assets AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_bank AS C ON A.id_bank = C.id_bank AND A.kode_kantor = B.kode_kantor
				".$cari." ORDER BY A.tgl_ins DESC LIMIT ".$offset.",".$limit."
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
		
		function count_d_assets_limit($cari)
		{
			$query = 
			"
				SELECT
					COUNT(A.id_d_assets) AS JUMLAH
				FROM tb_d_assets AS A 
				LEFT JOIN tb_kat_assets AS B ON A.id_kat_assets = B.id_kat_assets AND A.kode_kantor = B.kode_kantor
				".$cari."
			";
			
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
		
		function simpan
		(
			$id_kat_assets,
			$id_assets,
			$id_bank,
			$kode_assets,
			$nama_assets,
			$kode_d_assets,
			$pemegang,
			$tgl_beli,
			$nominal_dp,
			$nominal,
			$ket_d_assets,
			$apakah_angsuran,
			$isUang,
			$penyusutan,
			$tgl_mulai_angsur,
			$tgl_akhir_angsur,
			$nominal_angsur,
			$jenis_angsur,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_d_assets
				(
					id_d_assets,
					id_kat_assets,
					id_assets,
					id_bank,
					kode_assets,
					nama_assets,
					kode_d_assets,
					pemegang,
					tgl_beli,
					nominal_dp,
					nominal,
					ket_d_assets,
					apakah_angsuran,
					isUang,
					penyusutan,
					tgl_mulai_angsur,
					tgl_akhir_angsur,
					nominal_angsur,
					jenis_angsur,
					tgl_ins,
					tgl_updt,
					user_updt,
					user_ins,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_d_assets
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
								COALESCE(MAX(CAST(RIGHT(id_d_assets,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_d_assets
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					'".$id_kat_assets."',
					'".$id_assets."',
					'".$id_bank."',
					'".$kode_assets."',
					'".$nama_assets."',
					'".$kode_d_assets."',
					'".$pemegang."',
					'".$tgl_beli."',
					'".$nominal_dp."',
					'".$nominal."',
					'".$ket_d_assets."',
					'".$apakah_angsuran."',
					'".$isUang."',
					'".$penyusutan."',
					'".$tgl_mulai_angsur."',
					'".$tgl_akhir_angsur."',
					'".$nominal_angsur."',
					'".$jenis_angsur."',
					NOW(),
					NOW(),
					'',
					'".$user_ins."',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_d_assets,
			$id_kat_assets,
			$id_assets,
			$id_bank,
			$kode_assets,
			$nama_assets,
			$kode_d_assets,
			$pemegang,
			$tgl_beli,
			$nominal_dp,
			$nominal,
			$ket_d_assets,
			$apakah_angsuran,
			$isUang,
			$penyusutan,
			$tgl_mulai_angsur,
			$tgl_akhir_angsur,
			$nominal_angsur,
			$jenis_angsur,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_d_assets SET
						id_kat_assets = '".$id_kat_assets."',
						id_assets = '".$id_assets."',
						id_bank = '".$id_bank."',
						kode_assets = '".$kode_assets."',
						nama_assets = '".$nama_assets."',
						kode_d_assets = '".$kode_d_assets."',
						pemegang = '".$pemegang."',
						tgl_beli = '".$tgl_beli."',
						nominal_dp = '".$nominal_dp."',
						nominal = '".$nominal."',
						ket_d_assets = '".$ket_d_assets."',
						apakah_angsuran = '".$apakah_angsuran."',
						isUang = '".$isUang."',
						penyusutan = '".$penyusutan."',
						tgl_mulai_angsur = '".$tgl_mulai_angsur."',
						tgl_akhir_angsur = '".$tgl_akhir_angsur."',
						nominal_angsur = '".$nominal_angsur."',
						jenis_angsur = '".$jenis_angsur."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_d_assets = '".$id_d_assets
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_d_assets)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_d_assets WHERE ".$berdasarkan." = '".$id_d_assets."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_d_assets($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_d_assets', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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