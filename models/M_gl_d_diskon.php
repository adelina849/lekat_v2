<?php
	class M_gl_d_diskon extends CI_Model 
	{

		function __construct()
		{
			parent::__construct();
		}
		
		function get_d_diskon_cari($cari,$order_by)
		{
			$query = 
			"
				SELECT
					A.*
					,COALESCE(B.kode_produk,'') AS kode_produk
					,COALESCE(B.nama_produk,'') AS nama_produk
					,COALESCE(D.harga_jual,0) AS harga_modal
					,COALESCE(B.isProduk,'') AS isProduk
				FROM tb_d_diskon AS A
				LEFT JOIN tb_produk AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor
				LEFT JOIN tb_satuan AS C ON A.kode_kantor = C.kode_kantor AND A.kode_satuan = C.kode_satuan
				LEFT JOIN tb_satuan_konversi AS D ON A.kode_kantor = D.kode_kantor AND A.id_produk = D.id_produk AND C.id_satuan = D.id_satuan 
				".$cari." 
				".$order_by."
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
		
		function list_d_diskon_limit($cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT
					A.*
					,COALESCE(B.kode_produk,'') AS kode_produk
					,COALESCE(B.nama_produk,'') AS nama_produk
				FROM tb_d_diskon AS A
				LEFT JOIN tb_produk AS B ON A.id_produk = B.id_produk AND A.kode_kantor = B.kode_kantor
				".$cari." 
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
		
		function count_d_diskon_limit($cari)
		{
			$query = 
			"
				SELECT COUNT(A.id_d_diskon) AS JUMLAH FROM tb_d_diskon AS A ".$cari."
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
		
		function list_produk_d_diskon_limit($id_h_diskon,$cari,$order_by,$limit,$offset)
		{
			$query = 
			"
				SELECT
					A.id_produk,
					A.id_supplier,
					A.kode_produk,
					A.nama_produk,
					A.produksi_oleh,
					A.pencipta,
					A.charge,
					A.optr_charge,
					A.charge_beli,
					A.optr_charge_beli,
					A.min_stock,
					A.max_stock,
					A.harga_minimum,
					A.spek_produk,
					A.ket_produk,
					A.isNotActive,
					A.isNotRetur,
					A.stock,
					A.dtstock,
					A.isProduk,
					A.tgl_ins,
					A.tgl_updt,
					A.user_ins,
					A.user_updt,
					A.kode_kantor
					,MAX(COALESCE(C.img_nama,'')) AS img_nama
					,MAX(COALESCE(C.img_file,'')) AS img_file
					,MAX(COALESCE(C.img_url,'')) AS img_url
					,MAX(COALESCE(C.ket_img,'')) AS ket_img
				FROM tb_produk AS A
				LEFT JOIN tb_images AS C ON A.id_produk = C.id AND A.kode_kantor = C.kode_kantor AND C.group_by = 'produks'
				LEFT JOIN tb_d_diskon AS D ON A.id_produk = D.id_produk AND A.kode_kantor = D.kode_kantor AND D.id_h_diskon = '".$id_h_diskon."'
				".$cari."
				
				GROUP BY A.id_produk,
					A.id_supplier,
					A.kode_produk,
					A.nama_produk,
					A.produksi_oleh,
					A.pencipta,
					A.charge,
					A.optr_charge,
					A.charge_beli,
					A.optr_charge_beli,
					A.min_stock,
					A.max_stock,
					A.harga_minimum,
					A.spek_produk,
					A.ket_produk,
					A.isNotActive,
					A.isNotRetur,
					A.stock,
					A.dtstock,
					A.isProduk,
					A.tgl_ins,
					A.tgl_updt,
					A.user_ins,
					A.user_updt,
					A.kode_kantor
	
				".$order_by."
				-- ORDER BY A.kode_produk ASC 
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
		
		function simpan
		(
			$id_h_diskon,
			$id_produk,
			$id_kat_produk,
			$kode_kat_produk,
			$nama_kat_produk,
			$nama_d_diskon,
			$nominal,
			$banyaknya,
			$kode_satuan,
			$status_konversi,
			$konversi,
			$ket_d_diskon,
			$user_ins,
			$kode_kantor
		)
		{
			$strquery = "
				INSERT INTO tb_d_diskon
				(
					id_d_diskon,
					id_h_diskon,
					id_produk,
					id_kat_produk,
					kode_kat_produk,
					nama_kat_produk,
					nama_d_diskon,
					nominal,
					banyaknya,
					kode_satuan,
					status_konversi,
					konversi,
					ket_d_diskon,
					tgl_ins,
					tgl_updt,
					user_updt,
					user_ins,
					kode_kantor

				)
				VALUES
				(
					(
						SELECT CONCAT('".$this->session->userdata('isLocal')."','".$kode_kantor."',FRMTGL,ORD) AS id_d_diskon
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
								COALESCE(MAX(CAST(RIGHT(id_d_diskon,5) AS UNSIGNED)) + 1,1) AS ORD
								From tb_d_diskon
								WHERE DATE(tgl_ins) = DATE(NOW())
								AND kode_kantor = '".$kode_kantor."'
							) AS A
						) AS AA
					),
					
					'".$id_h_diskon."',
					'".$id_produk."',
					'".$id_kat_produk."',
					'".$kode_kat_produk."',
					'".$nama_kat_produk."',
					'".$nama_d_diskon."',
					'".$nominal."',
					'".$banyaknya."',
					'".$kode_satuan."',
					'".$status_konversi."',
					'".$konversi."',
					'".$ket_d_diskon."',
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
			$id_d_diskon,
			$id_h_diskon,
			$id_produk,
			$id_kat_produk,
			$kode_kat_produk,
			$nama_kat_produk,
			$nama_d_diskon,
			$nominal,
			$banyaknya,
			$kode_satuan,
			$status_konversi,
			$konversi,
			$ket_d_diskon,
			$user_updt,
			$kode_kantor
		)
		{
			$strquery = "
					UPDATE tb_d_diskon SET
						id_h_diskon = '".$id_h_diskon."',
						id_produk = '".$id_produk."',
						
						id_kat_produk = '".$id_kat_produk."',
						kode_kat_produk = '".$kode_kat_produk."',
						nama_kat_produk = '".$nama_kat_produk."',
						
						nama_d_diskon = '".$nama_d_diskon."',
						nominal = '".$nominal."',
						banyaknya = '".$banyaknya."',
						kode_satuan = '".$kode_satuan."',
						status_konversi = '".$status_konversi."',
						konversi = '".$konversi."',
						ket_d_diskon = '".$ket_d_diskon."',
						tgl_updt = NOW(),
						user_updt = '".$user_updt."'
					WHERE kode_kantor = '".$kode_kantor."' AND id_d_diskon = '".$id_d_diskon
					."'
					";
					
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function hapus($berdasarkan,$id_d_diskon)
		{
			/*HAPUS JABATAN*/
				$strquery = "DELETE FROM tb_d_diskon WHERE ".$berdasarkan." = '".$id_d_diskon."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ;";
			/*HAPUS JABATAN*/
			
			/*SIMPAN DAN CATAT QUERY*/
				$this->M_gl_log->simpan_query($strquery);
			/*SIMPAN DAN CATAT QUERY*/
		}
		
		function get_d_diskon($berdasarkan,$cari)
        {
            $query = $this->db->get_where('tb_d_diskon', array($berdasarkan => $cari,'kode_kantor' => $this->session->userdata('ses_kode_kantor')));
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