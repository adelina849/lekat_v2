<?php
	class M_gl_h_penjualan_catat_untuk_fee extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		
		function simpan
		(
			$id_h_penjualan,
			$jenis_poli,
			$isPasienBaru,
			$jasa,
			$produk,
			$nominal_transaksi,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_h_penjualan_catat_untuk_fee
				(
					id_catatan,
					id_h_penjualan,
					jenis_poli,
					isPasienBaru,
					jasa,
					produk,
					nominal_transaksi,
					tgl_ins,
					tgl_updt,
					kode_kantor
				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_catatan
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
								COALESCE(MAX(CAST(RIGHT(id_catatan,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_h_penjualan_catat_untuk_fee
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_h_penjualan."',
					'".$jenis_poli."',
					'".$isPasienBaru."',
					'".$jasa."',
					'".$produk."',
					'".$nominal_transaksi."',
					NOW(),
					NOW(),
					'".$kode_kantor."'
				)
			";
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function edit
		(
			$id_catatan,
			$id_h_penjualan,
			$jenis_poli,
			$isPasienBaru,
			$jasa,
			$produk,
			$nominal_transaksi,
			$kode_kantor
		
		)
		{
			$strquery = "
					UPDATE tb_h_penjualan_catat_untuk_fee SET
						
						id_h_penjualan = '".$id_h_penjualan."',
						jenis_poli = '".$jenis_poli."',
						isPasienBaru = '".$isPasienBaru."',
						jasa = '".$jasa."',
						produk = '".$produk."',
						nominal_transaksi = '".$nominal_transaksi."',
						tgl_updt = NOW(),
						kode_kantor = '".$kode_kantor."'

					WHERE kode_kantor = '".$kode_kantor."' AND id_catatan = '".$id_catatan
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($cari)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_satuan ".$cari."";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_h_penjualan_catat_untuk_fee($cari)
		{
			$query = "SELECT * FROM tb_h_penjualan_catat_untuk_fee ".$cari." ";
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
		
	}
?>