<?php
	class M_gl_h_fee extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function list_h_fee_limit($cari,$order_by,$limit,$offset)
		{
			$query =
			"
				SELECT *
				FROM tb_h_fee ".$cari." 
				".$order_by."
				LIMIT ".$offset.",".$limit."
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
		
		function count_h_fee_limit($cari)
		{
			$query = $this->db->query("SELECT COUNT(id_h_fee) AS JUMLAH FROM tb_h_fee ".$cari);
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
			$nama_h_fee,
			$diberikan_kepada,
			
			$jenis_poli,
			$optr_masa_kerja,
			$masa_kerja_hari,
			$isPasienBaru,
			$isMembuatResep,
			$min_jasa,
			$optr_min_jasa,
			$isKelipatanJasa,
			$min_produk,
			$optr_min_produk,
			$isKelipatanProduk,
			$nominal_tr_per_pasien,
			$total_periksa_hari,

			
			
			$kat_fee,
			$isGlobal,
			$optr_kondisi,
			$satuan_jual_fee,
			$besar_penjualan,
			$optr_fee,
			$besar_fee,
			$ket_fee,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_h_fee
				(
					id_h_fee,
					nama_h_fee,
					diberikan_kepada,
					
					jenis_poli,
					optr_masa_kerja,
					masa_kerja_hari,
					isPasienBaru,
					isMembuatResep,
					min_jasa,
					optr_min_jasa,
					isKelipatanJasa,
					min_produk,
					optr_min_produk,
					isKelipatanProduk,
					nominal_tr_per_pasien,
					total_periksa_hari,
					
					kat_fee,
					isGlobal,
					optr_kondisi,
					satuan_jual_fee,
					besar_penjualan,
					optr_fee,
					besar_fee,
					ket_fee,
					tgl_ins,
					tgl_updt,
					user_ins,
					user_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_h_fee
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
								COALESCE(MAX(CAST(RIGHT(id_h_fee,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_h_fee
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$nama_h_fee."',
					'".$diberikan_kepada."',
					
					'".$jenis_poli."',
					'".$optr_masa_kerja."',
					'".$masa_kerja_hari."',
					'".$isPasienBaru."',
					'".$isMembuatResep."',
					'".$min_jasa."',
					'".$optr_min_jasa."',
					'".$isKelipatanJasa."',
					'".$min_produk."',
					'".$optr_min_produk."',
					'".$isKelipatanProduk."',
					'".$nominal_tr_per_pasien."',
					'".$total_periksa_hari."',
					
					'".$kat_fee."',
					'".$isGlobal."',
					'".$optr_kondisi."',
					'".$satuan_jual_fee."',
					'".$besar_penjualan."',
					'".$optr_fee."',
					'".$besar_fee."',
					'".$ket_fee."',
					NOW(), 
					NOW(), 
					'".$user_ins."',
					'',
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_h_fee,
			$nama_h_fee,
			$diberikan_kepada,
			
			$jenis_poli,
			$optr_masa_kerja,
			$masa_kerja_hari,
			$isPasienBaru,
			$isMembuatResep,
			$min_jasa,
			$optr_min_jasa,
			$isKelipatanJasa,
			$min_produk,
			$optr_min_produk,
			$isKelipatanProduk,
			$nominal_tr_per_pasien,
			$total_periksa_hari,
			
			$kat_fee,
			$isGlobal,
			$optr_kondisi,
			$satuan_jual_fee,
			$besar_penjualan,
			$optr_fee,
			$besar_fee,
			$ket_fee,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_h_fee SET
						
						nama_h_fee = '".$nama_h_fee."',
						diberikan_kepada = '".$diberikan_kepada."',
						
						jenis_poli = '".$jenis_poli."',
						optr_masa_kerja = '".$optr_masa_kerja."',
						masa_kerja_hari = '".$masa_kerja_hari."',
						isPasienBaru = '".$isPasienBaru."',
						isMembuatResep = '".$isMembuatResep."',
						min_jasa = '".$min_jasa."',
						optr_min_jasa = '".$optr_min_jasa."',
						isKelipatanJasa = '".$isKelipatanJasa."',
						min_produk = '".$min_produk."',
						optr_min_produk = '".$optr_min_produk."',
						isKelipatanProduk = '".$isKelipatanProduk."',
						nominal_tr_per_pasien = '".$nominal_tr_per_pasien."',
						total_periksa_hari = '".$total_periksa_hari."',

						
						kat_fee = '".$kat_fee."',
						isGlobal = '".$isGlobal."',
						optr_kondisi = '".$optr_kondisi."',
						satuan_jual_fee = '".$satuan_jual_fee."',
						besar_penjualan = '".$besar_penjualan."',
						optr_fee = '".$optr_fee."',
						besar_fee = '".$besar_fee."',
						ket_fee = '".$ket_fee."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
						
					WHERE kode_kantor = '".$kode_kantor."' AND id_h_fee = '".$id_h_fee
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_h_fee)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_h_fee WHERE ".$berdasarkan." = '".$id_h_fee."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_h_fee($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_h_fee', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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