<?php

if (!defined('BASEPATH'))
    exit('No direct script access allowed');

class M_gl_pengaturan extends CI_Model
{

    public $table = 'tb_pengaturan';
    public $id = 'id_pengaturan';
    public $order = 'DESC';

    function __construct()
    {
        parent::__construct();
    }
	
	function view_query_general($query)
	{
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
	
	function exec_query_general($query)
	{
		/*SIMPAN DAN CATAT QUERY*/
			//$this->M_gl_log->simpan_query($query);
			$this->db->query($query);
		/*SIMPAN DAN CATAT QUERY*/
	}
	
	function hapus_stuck_produk($kode_kantor,$tgl_stock)
	{
		$strquery = "
			DELETE FROM tb_stock_produk WHERE kode_kantor = '".$kode_kantor."' AND MD5(tgl_stock) = '".$tgl_stock."'
		";
		
		$this->db->query($strquery);
	}
	
	function get_stock_produk($cari)
	{
		
		$query = 
		"
			SELECT * FROM tb_stock_produk ".$cari."
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
	
	function list_detail_stock_produk($kode_kantor,$cari,$tgl_stock,$limit,$offset)
	{
		$query = 
		"
			SELECT
				A.* 
				,COALESCE(B.kode_produk,'') AS kode_produk
				,COALESCE(B.nama_produk,'') AS nama_produk
				,COALESCE(D.kode_satuan,'') AS kode_satuan
			FROM tb_stock_produk AS A
			LEFT JOIN tb_produk AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
			LEFT JOIN tb_satuan_konversi AS C ON A.kode_kantor = C.kode_kantor AND A.id_produk = C.id_produk AND C.besar_konversi = 1 AND C.status = '*'
			LEFT JOIN tb_satuan AS D ON A.kode_kantor = D.kode_kantor AND C.id_satuan = D.id_satuan
			
			WHERE A.kode_kantor = '".$kode_kantor."'
			AND MD5(A.tgl_stock) = '".$tgl_stock."'
			AND (COALESCE(B.kode_produk,'') LIKE '%".$cari."%' OR COALESCE(B.nama_produk,'') LIKE '%".$cari."%')
			ORDER BY COALESCE(B.nama_produk,'') ASC
			LIMIT ".$offset.",".$limit."
			;
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
	
	function sum_detail_stock_produk($kode_kantor,$cari,$tgl_stock)
	{
		$query = 
		"
			SELECT
				COUNT(id_produk) AS JUMLAH
			FROM
			(
				SELECT
					A.* 
					,COALESCE(B.kode_produk,'') AS kode_produk
					,COALESCE(B.nama_produk,'') AS nama_produk
				FROM tb_stock_produk AS A
				LEFT JOIN tb_produk AS B ON A.kode_kantor = B.kode_kantor AND A.id_produk = B.id_produk
				WHERE A.kode_kantor = '".$kode_kantor."'
				AND MD5(A.tgl_stock) = '".$tgl_stock."'
				AND (COALESCE(B.kode_produk,'') LIKE '%".$cari."%' OR COALESCE(B.nama_produk,'') LIKE '%".$cari."%')
			) AS A
			;
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
	
	function list_stock_produk($kode_kantor,$limit,$offset)
	{
		$query = 
		"
			SELECT DISTINCT tgl_stock AS tgl_stock FROM tb_stock_produk 
			WHERE kode_kantor = '".$kode_kantor."' 
			GROUP BY tgl_stock 
			ORDER BY tgl_stock DESC
			LIMIT ".$offset.",".$limit."
			;
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
	
	function sum_stock_produk($kode_kantor)
	{
		$query = 
		"
			SELECT COUNT(A.tgl_stock) AS JUMLAH
			FROM
			(
				SELECT DISTINCT tgl_stock AS tgl_stock 
				FROM tb_stock_produk 
				WHERE kode_kantor = '".$kode_kantor."' 
				GROUP BY tgl_stock
			) AS A
			;
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
	
	function hapus_log_kadaluarsa($kode_kantor)
	{
		$get_nilai_kadaluarsa = $this->get_data_by_id(7);
		$get_nilai_kadaluarsa = $get_nilai_kadaluarsa->nilai;
		
		$strquery = "
			DELETE FROM tb_log WHERE kode_kantor = '".$kode_kantor."' AND DATEDIFF(DATE(waktu),DATE(NOW())) < -".$get_nilai_kadaluarsa.";
		";
		
		$this->db->query($strquery);
	}
	
	function update_set_0_h_diskon($kode_kantor)
	{
		$strquery = "
			UPDATE tb_h_diskon SET set_default = 0 WHERE kode_kantor = '".$kode_kantor."';
		";
		
		$this->db->query($strquery);
	}
	
	function update_set_1_h_diskon($kode_kantor)
	{
		$strquery = "
			UPDATE tb_h_diskon SET set_default = 1 WHERE id_h_diskon IN (SELECT id_h_diskon FROM tb_hari_diskon WHERE hari = DAYNAME(DATE(NOW())) AND kode_kantor = '".$kode_kantor."') AND kode_kantor = '".$kode_kantor."';
		";
		
		$this->db->query($strquery);
	}
	
	function list_pengaturan()
	{
		$query = 
		"
			SELECT * FROM tb_pengaturan ORDER BY id_pengaturan ASC;
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
	
	function list_pengaturan_cari($cari)
	{
		$query = 
		"
			SELECT * FROM tb_pengaturan ".$cari." ORDER BY id_pengaturan ASC;
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
	
	
	function get_data_by_id($id)
	{
		$query = "SELECT * FROM tb_pengaturan WHERE id_pengaturan = '".$id."';";
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
	
	function get_data_kantor($cari)
	{
		$query = "SELECT * FROM tb_kantor ".$cari."";
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
	
	function get_data_kantor2($cari)
	{
		
		$query = "SELECT * FROM tb_kantor ".$cari."";
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
	
	function get_request_cabang($kode_kantor)
	{
		$query = "
					SELECT
						COUNT(B.id_h_pembelian) AS CNT
					FROM tb_h_pembelian AS B
					LEFT JOIN tb_supplier AS D ON B.id_supplier = D.id_supplier
					LEFT JOIN tb_kantor AS E ON B.kode_kantor = E.kode_kantor
					LEFT JOIN tb_h_penjualan AS F ON B.no_h_pembelian = F.ket_penjualan AND F.kode_kantor = '".$kode_kantor."' AND F.sts_penjualan = 'SELESAI'
					WHERE
					COALESCE(B.sts_pembelian, '') = 'SELESAI' 
					AND D.kode_supplier = '".$kode_kantor."' 
					AND D.type_supplier = 'CABANG'
					AND F.id_h_penjualan IS NULL
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
	
	function get_transaksi_pending($kode_kantor)
	{
		$query = "
					SELECT COUNT(id_h_penjualan) AS CNT 
					FROM tb_h_penjualan WHERE kode_kantor = '".$kode_kantor."' 
					AND sts_penjualan IN ('PENDING') AND DATE(tgl_h_penjualan) = DATE(NOW());
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
	
	function ubah_data_kantor
	(
		$id_kantor,
		$kode_kantor,
		$SITU,
		$SIUP,
		$nama_kantor,
		$pemilik,
		$kota,
		$alamat,
		$tlp,
		$sejarah,
		$ket_kantor,
		$jenis_faktur,
		$img_kantor,
		$url_img,
		$user_updt

	)
	{
		if($img_kantor != "")
		{
			$strquery = "
				UPDATE tb_kantor SET
					SITU = '".$SITU."',
					SIUP = '".$SIUP."',
					nama_kantor = '".$nama_kantor."',
					pemilik = '".$pemilik."',
					kota = '".$kota."',
					alamat = '".$alamat."',
					tlp = '".$tlp."',
					sejarah = '".$sejarah."',
					ket_kantor = '".$ket_kantor."',
					jenis_faktur = '".$jenis_faktur."',
					img_kantor = '".$img_kantor."',
					url_img = '".$url_img."',
					tgl_updt = NOW(),
					user_updt = '".$user_updt."'
				WHERE kode_kantor = '".$kode_kantor."' AND id_kantor = '".$id_kantor
				."'
				";
		}
		else
		{
			$strquery = "
				UPDATE tb_kantor SET
					SITU = '".$SITU."',
					SIUP = '".$SIUP."',
					nama_kantor = '".$nama_kantor."',
					pemilik = '".$pemilik."',
					kota = '".$kota."',
					alamat = '".$alamat."',
					tlp = '".$tlp."',
					sejarah = '".$sejarah."',
					ket_kantor = '".$ket_kantor."',
					jenis_faktur = '".$jenis_faktur."',
					tgl_updt = NOW(),
					user_updt = '".$user_updt."'
				WHERE kode_kantor = '".$kode_kantor."' AND id_kantor = '".$id_kantor
				."'
				";
		}
		
				
		/*SIMPAN DAN CATAT QUERY*/
			$this->M_gl_log->simpan_query($strquery);
		/*SIMPAN DAN CATAT QUERY*/
	}
	
	function ubahPengaturanSatuan($id_pengaturan,$nilai)
	{
		$strquery = "
				UPDATE tb_pengaturan SET
					nilai = '".$nilai."'
				WHERE id_pengaturan = '".$id_pengaturan
				."'
				";
		/*SIMPAN DAN CATAT QUERY*/
			$this->M_gl_log->simpan_query($strquery);
		/*SIMPAN DAN CATAT QUERY*/
	}
	
	function getUserIpAddr()
	{
		$ipaddress = '';
		if (getenv('HTTP_CLIENT_IP'))
			$ipaddress = getenv('HTTP_CLIENT_IP');
		else if(getenv('HTTP_X_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_X_FORWARDED_FOR');
		else if(getenv('HTTP_X_FORWARDED'))
			$ipaddress = getenv('HTTP_X_FORWARDED');
		else if(getenv('HTTP_FORWARDED_FOR'))
			$ipaddress = getenv('HTTP_FORWARDED_FOR');
		else if(getenv('HTTP_FORWARDED'))
			$ipaddress = getenv('HTTP_FORWARDED');
		else if(getenv('REMOTE_ADDR'))
			$ipaddress = getenv('REMOTE_ADDR');
		else
			$ipaddress = 'UNKNOWN';
	 
		return $ipaddress;
	}
	
	function do_upload_global($lokasi,$nama_file,$cek_bfr)
	{
		$this->load->library('upload');
		
		if($cek_bfr != '')
		{
			//@unlink('./assets/global/karyawan/'.$cek_bfr);
			@unlink($lokasi.''.$cek_bfr);
		}
		
		if (!empty($_FILES['foto']['name']))
		{
			//$config['upload_path'] = 'assets/global/karyawan/';
			$config['upload_path'] = $lokasi;
			$config['allowed_types'] = 'gif|jpg|jpeg|png';
			$config['max_size']	= '2024';
			$config['create_thumb'] = TRUE;
			$config['maintain_ratio'] = TRUE;
			$config['maintain_ratio'] = TRUE;
			$config['quality']= '50%';
			//$config['max_widtd']  = '300';
			//$config['max_height']  = '300';
			$config['file_name']	= $nama_file;
			$config['overwrite']	= true;
			

			$this->upload->initialize($config);

			//Upload file 1
			if ($this->upload->do_upload('foto'))
			{
				$hasil = $this->upload->data();
			}
		}
		

		/*
		if($cek_bfr != '')
		{
			//@unlink('./assets/global/karyawan/'.$cek_bfr);
			@unlink($lokasi.''.$cek_bfr);
		}
		
		if (!empty($_FILES['foto']['name']))
		{
			//$config['upload_path'] = 'assets/global/karyawan/';
			$config['upload_path'] = $lokasi;
			$config['allowed_types'] = 'gif|jpg|jpeg|png';
			$config['max_size']	= '2024';
			//$config['max_widtd']  = '300';
			//$config['max_height']  = '300';
			$config['file_name']	= $nama_file;
			$config['overwrite']	= true;
			

			$this->upload->initialize($config);

			//Upload file 1
			if ($this->upload->do_upload('foto'))
			{
				$hasil = $this->upload->data();
			}
		}
		*/
	}
	
	function do_upload_global_dinamic_input_name($nama_input,$lokasi,$nama_file,$cek_bfr)
	{
		$this->load->library('upload');
		
		if($cek_bfr != '')
		{
			//@unlink('./assets/global/karyawan/'.$cek_bfr);
			@unlink($lokasi.''.$cek_bfr);
		}
		
		if (!empty($_FILES[$nama_input]['name']))
		{
			//$config['upload_path'] = 'assets/global/karyawan/';
			$config['upload_path'] = $lokasi;
			$config['allowed_types'] = 'gif|jpg|jpeg|png';
			$config['max_size']	= '2024';
			$config['create_thumb'] = TRUE;
			$config['maintain_ratio'] = TRUE;
			$config['maintain_ratio'] = TRUE;
			$config['quality']= '50%';
			//$config['max_widtd']  = '300';
			//$config['max_height']  = '300';
			$config['file_name']	= $nama_file;
			$config['overwrite']	= true;
			

			$this->upload->initialize($config);

			//Upload file 1
			if ($this->upload->do_upload($nama_input))
			{
				$hasil = $this->upload->data();
			}
		}
		
		
		/*
		if($cek_bfr != '')
		{
			//@unlink('./assets/global/karyawan/'.$cek_bfr);
			@unlink($lokasi.''.$cek_bfr);
		}
		
		if (!empty($_FILES[$nama_input]['name']))
		{
			//$config['upload_path'] = 'assets/global/karyawan/';
			$config['upload_path'] = $lokasi;
			$config['allowed_types'] = 'gif|jpg|jpeg|png';
			$config['max_size']	= '2024';
			//$config['max_widtd']  = '300';
			//$config['max_height']  = '300';
			$config['file_name']	= $nama_file;
			$config['overwrite']	= true;
			

			$this->upload->initialize($config);

			//Upload file 1
			if ($this->upload->do_upload($nama_input))
			{
				$hasil = $this->upload->data();
			}
		}
		*/
		
	}

	
	function encrypt($str) 
	{
		$kunci = '979a218e0632df2935317f98d47956c7';
		for ($i = 0; $i < strlen($str); $i++) {
			$karakter = substr($str, $i, 1);
			$kuncikarakter = substr($kunci, ($i % strlen($kunci))-1, 1);
			$karakter = chr(ord($karakter)+ord($kuncikarakter));
			$hasil = $karakter;
		}
		return urlencode(base64_encode($hasil));
	}

	function decrypt($str) 
	{
		$str = base64_decode(urldecode($str));
		$hasil = '';
		$kunci = '979a218e0632df2935317f98d47956c7';
		for ($i = 0; $i < strlen($str); $i++) {
			$karakter = substr($str, $i, 1);
			$kuncikarakter = substr($kunci, ($i % strlen($kunci))-1, 1);
			$karakter = chr(ord($karakter)-ord($kuncikarakter));
			$hasil = $karakter;
		}
		return $hasil;
	}
	
	function get_notifikasi_transaksi($kode_kantor)
	{
		$query = "
					SELECT
						SUM(PENDING) AS PENDING
						,SUM(PEMERIKSAAN) AS PEMERIKSAAN
						,SUM(PEMBAYARAN) AS PEMBAYARAN
						,SUM(PENDING) + SUM(PEMERIKSAAN) + SUM(PEMBAYARAN) AS SUM_ALL
					FROM
					(
						SELECT
							CASE WHEN sts_penjualan = 'PENDING' THEN 1 ELSE 0 END AS PENDING
							,CASE WHEN sts_penjualan IN ('PEMERIKSAAN','PESAN') THEN 1 ELSE 0 END AS PEMERIKSAAN
							,CASE WHEN sts_penjualan = 'PEMBAYARAN' THEN 1 ELSE 0 END AS PEMBAYARAN
						FROM tb_h_penjualan 
						WHERE kode_kantor = '".$kode_kantor."' AND DATE(tgl_h_penjualan) = DATE(NOW())
						
					) AS AA
					;
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
	
}

/* End of file M_gl_pengaturan.php */
/* Location: ./application/models/M_gl_pengaturan.php */
/* Please DO NOT modify this information : */
/* Generated by Harviacode Codeigniter CRUD Generator 2019-12-30 15:11:17 */
/* http://harviacode.com */