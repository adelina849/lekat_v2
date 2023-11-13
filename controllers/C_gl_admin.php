<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_dashboard','M_gl_stock_produk','M_gl_costumer','M_gl_lap_pembelian'));
	}
	
	public function index()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$data_kantor = $this->M_gl_pengaturan->get_data_kantor(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'");
				$data_kantor = $data_kantor->row();
				
				/*CEK UPGRADE MEMBER DULU*/
						$this->M_gl_costumer->update_member($this->session->userdata('ses_kode_kantor'));
				/*CEK UPGRADE MEMBER DULU*/
				
				/*DATA JUM PASIEN*/
				$data_jumlah_pasien = $this->M_gl_dashboard->JUM_PASIEN()->JUMLAH_PASIEN;
				if(!empty($data_jumlah_pasien))
				{
					$data_jumlah_pasien = $data_jumlah_pasien;
				}
				else
				{
					$data_jumlah_pasien = 0;
				}
				/*DATA JUM PASIEN*/
				
				/*DATA JUM DOKTER*/
				if($this->session->userdata('ses_isModePst') != "YA")
				{
					if($this->session->userdata('ses_gnl_isToko') == 'Y')
					{
						$data_jumlah_dokter = $this->M_gl_dashboard->JUM_KARYAWAN()->JUMLAH_DOKTER;
					}
					else
					{
						$data_jumlah_dokter = $this->M_gl_dashboard->JUM_DOKTER()->JUMLAH_DOKTER;
					}
					
					if(!empty($data_jumlah_dokter))
					{
						$data_jumlah_dokter = $data_jumlah_dokter;
					}
					else
					{
						$data_jumlah_dokter = 0;
					}
				}
				
				/*DATA JUM DOKTER*/
				
				/*DATA JUM KUNJUNGAN*/
				$data_jumlah_kunjungan = $this->M_gl_dashboard->JUM_KUNJUNGAN()->JUMLAH_KUNJUNGAN;
				if(!empty($data_jumlah_kunjungan))
				{
					$data_jumlah_kunjungan = $data_jumlah_kunjungan;
				}
				else
				{
					$data_jumlah_kunjungan = 0;
				}
				/*DATA JUM KUNJUNGAN*/
				
				
				/*LIST DISKON PROMO*/
				if($this->session->userdata('ses_isModePst') != "YA")
				{
					$cari_diskon = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND DATE(NOW()) >= DATE(dari) AND DATE(NOW()) <= DATE(sampai) ";
					$list_diskon_promo = $this->M_gl_dashboard->list_h_diskon_limit($cari_diskon,20,0);
				}
				/*LIST DISKON PROMO*/
				
				//LIST PRODUK BUFFER
				/*
				$list_stock_produk = $this->M_gl_stock_produk->list_stock_produk($this->session->userdata('ses_kode_kantor'),'',' PROD.nama_produk ASC',0,50,date("Y-m-d"),'23','59','KURANG');
				*/
				//LIST PRODUK BUFFER
				
				//LOG AKTIFITAS TERAKHIR
				/*
				$cari_log_aktifitas = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
				$order_by_log_aktifitas = "ORDER BY A.waktu DESC";
				$list_log_aktifitas = $this->M_gl_dashboard->list_log_aktifitas_limit($cari_log_aktifitas,$order_by_log_aktifitas,10,0);
				*/
				//LOG AKTIFITAS TERAKHIR
				
				//STATISTIK KUNJUNGAN
				$list_statistik_kunjungan = $this->M_gl_dashboard->list_statistik_kunjungan($this->session->userdata('ses_kode_kantor'));
				//STATISTIK KUNJUNGAN
				
				//ORDER DARI CABANG LAIN
				if($this->session->userdata('ses_isModePst') == "YA")
				{
					/*
					$cari_h_pembelian = "
							WHERE 
							COALESCE(B.sts_pembelian,'') = 'SELESAI'
							AND D.kode_supplier = '".$this->session->userdata('ses_kode_kantor')."'
							AND D.type_supplier = 'CABANG'
					";
					
					$order_by_h_pembelian = "ORDER BY A.tgl_ins DESC";
					$list_pemesanan_dari_cabang_lain = $this->M_gl_lap_pembelian->list_pemesanan_dari_cabang_lain($cari_h_pembelian,$order_by_h_pembelian,150,0);
					*/
					
					//BEKAS UPDATE MOVING PRODUK
				}
				else
				{
					/*
					$cari_h_pembelian = "
							WHERE 
							COALESCE(B.sts_pembelian,'') = 'SELESAI'
							AND D.kode_supplier = '".$this->session->userdata('ses_kode_kantor')."'
							AND D.type_supplier = 'CABANG'
					";
					
					$order_by_h_pembelian = "ORDER BY A.tgl_ins DESC";
					$list_pemesanan_dari_cabang_lain = $this->M_gl_lap_pembelian->list_pemesanan_dari_cabang_lain($cari_h_pembelian,$order_by_h_pembelian,150,0);
					*/
				}
				//ORDER DARI CABANG LAIN
				
				//HITUNG ORDER DARI CABANG LAIN
				/*
				if($this->session->userdata('ses_isModePst') == "YA")
				{
					$cari_h_pembelian_hitung = "
							WHERE 
							COALESCE(B.sts_pembelian,'') = 'SELESAI'
							AND D.kode_supplier = '".$this->session->userdata('ses_kode_kantor')."'
							AND D.type_supplier = 'CABANG'
							AND DATE(COALESCE(B.tgl_h_pembelian,'')) = DATE(NOW())
					";
					
					$order_by_h_pembelian_hitung = "ORDER BY A.tgl_ins DESC";
					$list_pemesanan_dari_cabang_lain_hitung = $this->M_gl_lap_pembelian->list_pemesanan_dari_cabang_lain($cari_h_pembelian_hitung,$order_by_h_pembelian_hitung,150,0);
					
					if(!empty($list_pemesanan_dari_cabang_lain_hitung))
					{
						$list_pemesanan_dari_cabang_lain_hitung = $list_pemesanan_dari_cabang_lain_hitung->num_rows();
					}
					else
					{
						$list_pemesanan_dari_cabang_lain_hitung = 0;
					}
				}
				*/
				//HITUNG ORDER DARI CABANG LAIN
				
				
				if($this->session->userdata('ses_isModePst') == "YA")
				{
					//$list_nominal_stock_percabang = $this->M_gl_dashboard->nominal_stock_percabang();
					
					$data = array('page_content'=>'gl_admin_dashboard_pst','data_kantor' => $data_kantor,'data_jumlah_pasien' => $data_jumlah_pasien,'data_jumlah_kunjungan' => $data_jumlah_kunjungan,'list_statistik_kunjungan' => $list_statistik_kunjungan,//'list_pemesanan_dari_cabang_lain' => $list_pemesanan_dari_cabang_lain,
					//'list_pemesanan_dari_cabang_lain_hitung'=>$list_pemesanan_dari_cabang_lain_hitung,
					//'list_nominal_stock_percabang' => $list_nominal_stock_percabang
					);
					$this->load->view('admin/container',$data);
				}
				else
				{
					$data = array('page_content'=>'gl_admin_dashboard','data_kantor' => $data_kantor,'data_jumlah_pasien' => $data_jumlah_pasien,'data_jumlah_dokter' => $data_jumlah_dokter,'data_jumlah_kunjungan' => $data_jumlah_kunjungan,'list_diskon_promo' => $list_diskon_promo,'list_statistik_kunjungan' => $list_statistik_kunjungan);
					$this->load->view('admin/container',$data);
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function ubah_data_kantor()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."';");
			
			if(!empty($cek_ses_login))
			{
				//$this->M_gl_pengaturan->do_upload_global($lokasi,$nama_file,$cek_bfr);
				
				if (empty($_FILES['foto']['name']))
				{
					$this->M_gl_pengaturan->ubah_data_kantor
					(
						$_POST['id_kantor'],
						$this->session->userdata('ses_kode_kantor'),
						$_POST['SITU'],
						$_POST['SIUP'],
						$_POST['nama_kantor'],
						$_POST['pemilik'],
						$_POST['kota'],
						$_POST['alamat'],
						$_POST['tlp'],
						$_POST['sejarah'],
						$_POST['ket_kantor'],
						$_POST['jenis_faktur'],
						"",
						"",
						$this->session->userdata('ses_id_karyawan')
					);
				}
				else
				{
					/*DAPATKAN DATA KANTOR*/
						$data_kantor = $this->M_gl_pengaturan->get_data_kantor(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'");
						$data_kantor = $data_kantor->row();
					/*DAPATKAN DATA KANTOR*/
					
					/*AMBIL EXT*/
						$path = $_FILES['foto']['name'];
						$ext = pathinfo($path, PATHINFO_EXTENSION);
					/*AMBIL EXT*/

					/*PROSES UPLOAD*/
						$lokasi_gambar_disimpan = 'assets/global/images/';					
						$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,'KTR'.$_POST['id_kantor'].'.'.$ext,$data_kantor->img_kantor);
					/*PROSES UPLOAD*/					
					
					
					$this->M_gl_pengaturan->ubah_data_kantor
					(
						$_POST['id_kantor'],
						$this->session->userdata('ses_kode_kantor'),
						$_POST['SITU'],
						$_POST['SIUP'],
						$_POST['nama_kantor'],
						$_POST['pemilik'],
						$_POST['kota'],
						$_POST['alamat'],
						$_POST['tlp'],
						$_POST['sejarah'],
						$_POST['ket_kantor'],
						$_POST['jenis_faktur'],
						'KTR'.$_POST['id_kantor'].'.'.$ext,//$_POST['img_kantor'],
						$lokasi_gambar_disimpan,
						$this->session->userdata('ses_id_karyawan')
					);
				}
				
				/* CATAT AKTIFITAS EDIT*/
				if($this->session->userdata('catat_log') == 'Y')
				{
					$this->M_gl_log->simpan_log
					(
						$this->session->userdata('ses_id_karyawan'),
						'UPDATE',
						'Melakukan perubahan data kantor '.$_POST['kode_kantor'].' | '.$_POST['nama_kantor'],
						$this->M_gl_pengaturan->getUserIpAddr(),
						gethostname(),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				/* CATAT AKTIFITAS EDIT*/
				
				header('Location: '.base_url().'gl-admin');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin.php */