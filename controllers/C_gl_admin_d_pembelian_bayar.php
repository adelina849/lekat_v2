<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_d_pembelian_bayar extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_lap_pembelian','M_gl_bank','M_gl_h_penerimaan'));
		
	}
	
	public function index()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$id_h_pembelian = $this->uri->segment(2,0);
				$cari_h_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(id_h_pembelian) = '".$id_h_pembelian."'";
				$data_h_pembelian = $this->M_gl_lap_pembelian->get_h_pembelian($cari_h_penjualan);
				if(!empty($data_h_pembelian))
				{
					$data_h_pembelian = $data_h_pembelian->row();
					
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_pembelian) = '".$id_h_pembelian."'
						AND (
								COALESCE(B.norek,'') = '".str_replace("'","",$_GET['cari'])."'
								OR COALESCE(B.nama_bank,'') = '".str_replace("'","",$_GET['cari'])."'
								OR COALESCE(C.atas_nama,'') = '".str_replace("'","",$_GET['cari'])."'
							)";
					}
					else
					{
						$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_pembelian) = '".$id_h_pembelian."'
						";
					}
					
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					
					
					
					$list_d_pembelian_bayar = $this->M_gl_lap_pembelian->list_d_pembelian_bayar_limit($cari,$order_by);
					
					$list_bank = $this->M_gl_bank->list_bank_limit(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'",1000,0);
					
					$msgbox_title = " Detail Pembayaran Pembelian PO ".$data_h_pembelian->no_h_pembelian;
					
					
					
					$data = array('page_content'=>'gl_admin_d_pembelian_bayar','list_d_pembelian_bayar'=>$list_d_pembelian_bayar,'msgbox_title' => $msgbox_title,'list_bank'=>$list_bank,'data_h_pembelian'=>$data_h_pembelian);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-laporan-pembelian');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function index_pembayaran_by_inv()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$id_h_penerimaan = $this->uri->segment(2,0);
				$cari_h_penerimaan = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_penerimaan) = '".$id_h_penerimaan."'";
				$data_h_penerimaan = $this->M_gl_h_penerimaan->get_h_penerimaan_cari_get_supplier($cari_h_penerimaan);
				if(!empty($data_h_penerimaan))
				{
					$data_h_penerimaan = $data_h_penerimaan->row();
					
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_penerimaan) = '".$id_h_penerimaan."'
						AND (
								COALESCE(B.norek,'') = '".str_replace("'","",$_GET['cari'])."'
								OR COALESCE(B.nama_bank,'') = '".str_replace("'","",$_GET['cari'])."'
								OR COALESCE(C.atas_nama,'') = '".str_replace("'","",$_GET['cari'])."'
							)";
					}
					else
					{
						$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_penerimaan) = '".$id_h_penerimaan."'
						";
					}
					
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					
					
					
					$list_d_pembelian_bayar = $this->M_gl_lap_pembelian->list_d_pembelian_bayar_limit_for_inv($cari,$order_by);
					
					$list_bank = $this->M_gl_bank->list_bank_limit(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'",1000,0);
					
					$msgbox_title = " Detail Pembayaran Invoice ".$data_h_penerimaan->no_surat_jalan;
					
					
					
					$data = array('page_content'=>'gl_admin_d_pembelian_bayar_inv','list_d_pembelian_bayar'=>$list_d_pembelian_bayar,'msgbox_title' => $msgbox_title,'list_bank'=>$list_bank,'data_h_penerimaan'=>$data_h_penerimaan);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-laporan-list-hutang-per-inv');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function simpan_by_inv()
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
					
				$id_h_penerimaan = $this->uri->segment(2,0);
				$cari_h_penerimaan = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_penerimaan) = '".$id_h_penerimaan."'";
				$data_h_penerimaan = $this->M_gl_h_penerimaan->get_h_penerimaan_cari_get_supplier($cari_h_penerimaan);
				if(!empty($data_h_penerimaan))
				{
					$data_h_penerimaan = $data_h_penerimaan->row();	
					if (!empty($_POST['stat_edit']))
					{	
						$this->M_gl_lap_pembelian->edit_d_pembelian_bayar_by_inv
						(
							$_POST['stat_edit'], //$id_d_bayar,
							'', //$_POST['id_h_pembelian'],
							$_POST['id_h_penerimaan'],
							$_POST['id_supplier'],
							$_POST['id_bank'],
							'', //$_POST['id_retur_pembelian'],
							$_POST['cara'],
							$_POST['norek'],
							$_POST['nama_bank'],
							$_POST['atas_nama'],
							str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
							$_POST['ket'],
							'', //$_POST['type_bayar'],
							'', //$_POST['id_pembayaran_supplier'],
							'0', //$_POST['isTabungan'],
							$_POST['tgl_bayar'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						);
						
						/* CATAT AKTIFITAS EDIT*/
						if($this->session->userdata('catat_log') == 'Y')
						{
							$this->M_gl_log->simpan_log
							(
								$this->session->userdata('ses_id_karyawan'),
								'UPDATE',
								'Melakukan perubahan Pembayaran Transaksi '.$data_h_penjualan->no_faktur.' | '.$data_h_penjualan->nama_costumer,
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS EDIT*/
					
					}
					else
					{
						$this->M_gl_lap_pembelian->simpan_d_pembelian_bayar_by_inv
						(
						
							'', //$_POST['id_h_pembelian'],
							$_POST['id_h_penerimaan'],
							$_POST['id_supplier'],
							$_POST['id_bank'],
							'', //$_POST['id_retur_pembelian'],
							$_POST['cara'],
							$_POST['norek'],
							$_POST['nama_bank'],
							$_POST['atas_nama'],
							str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
							$_POST['ket'],
							'', //$_POST['type_bayar'],
							'', //$_POST['id_pembayaran_supplier'],
							'0', //$_POST['isTabungan'],
							$_POST['tgl_bayar'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						
						);
					}
				}
				header('Location: '.base_url().'gl-admin-laporan-pembelian-pembayaran-invoice/'.MD5($_POST['id_h_penerimaan']));
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function simpan()
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
				$id_h_pembelian = $this->uri->segment(2,0);
				$cari_h_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(id_h_pembelian) = '".$id_h_pembelian."'";
				$data_h_pembelian = $this->M_gl_lap_pembelian->get_h_pembelian($cari_h_penjualan);
				if(!empty($data_h_pembelian))
				{
					$data_h_pembelian = $data_h_pembelian->row();	
					if (!empty($_POST['stat_edit']))
					{	
						$this->M_gl_lap_pembelian->edit_d_pembelian_bayar
						(
							$_POST['stat_edit'], //$id_d_bayar,
							$_POST['id_h_pembelian'],
							$_POST['id_supplier'],
							$_POST['id_bank'],
							'', //$_POST['id_retur_pembelian'],
							$_POST['cara'],
							$_POST['norek'],
							$_POST['nama_bank'],
							$_POST['atas_nama'],
							str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
							$_POST['ket'],
							'', //$_POST['type_bayar'],
							'', //$_POST['id_pembayaran_supplier'],
							'0', //$_POST['isTabungan'],
							$_POST['tgl_bayar'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						);
						
						/* CATAT AKTIFITAS EDIT*/
						if($this->session->userdata('catat_log') == 'Y')
						{
							$this->M_gl_log->simpan_log
							(
								$this->session->userdata('ses_id_karyawan'),
								'UPDATE',
								'Melakukan perubahan Pembayaran Transaksi '.$data_h_penjualan->no_faktur.' | '.$data_h_penjualan->nama_costumer,
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS EDIT*/
					
					}
					else
					{
						$this->M_gl_lap_pembelian->simpan_d_pembelian_bayar
						(
						
							$_POST['id_h_pembelian'],
							$_POST['id_supplier'],
							$_POST['id_bank'],
							'', //$_POST['id_retur_pembelian'],
							$_POST['cara'],
							$_POST['norek'],
							$_POST['nama_bank'],
							$_POST['atas_nama'],
							str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
							$_POST['ket'],
							'', //$_POST['type_bayar'],
							'', //$_POST['id_pembayaran_supplier'],
							'0', //$_POST['isTabungan'],
							$_POST['tgl_bayar'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						
						);
					}
				}
				header('Location: '.base_url().'gl-admin-laporan-pembelian-pembayaran/'.MD5($_POST['id_h_pembelian']));
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		$id_h_pembelian = ($this->uri->segment(2,0));
		$id_d_bayar = ($this->uri->segment(3,0));
		
		$cari_h_pembelian = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(id_h_pembelian) = '".$id_h_pembelian."'";
		$data_h_pembelian = $this->M_gl_lap_pembelian->get_h_pembelian($cari_h_pembelian);
		if(!empty($data_h_pembelian))
		{
			
			$data_h_pembelian = $data_h_pembelian->row();
			
			$this->M_gl_lap_pembelian->hapus_d_pembelian_bayar('MD5(id_d_bayar)',$id_d_bayar);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan Pembayaran Pembelian '.$data_h_pembelian->no_h_pembelian,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-laporan-pembelian-pembayaran/'.$id_h_pembelian);
	}
	
	public function hapus_by_inv()
	{
		
		$id_h_penerimaan = ($this->uri->segment(2,0));
		$id_d_bayar = ($this->uri->segment(3,0));
		$kode_kantor = $_GET['kode'];
		
		$cari_h_penerimaan = " WHERE MD5(A.kode_kantor) = '".$kode_kantor."' AND MD5(A.id_h_penerimaan) = '".$id_h_penerimaan."'";
		
		
		$data_h_penerimaan = $this->M_gl_h_penerimaan->get_h_penerimaan_cari_get_supplier($cari_h_penerimaan);
		if(!empty($data_h_penerimaan))
		{
			$data_h_penerimaan = $data_h_penerimaan->row();	
		
		/*$data_h_pembelian = $this->M_gl_lap_pembelian->get_h_pembelian($cari_h_penerimaan);
		if(!empty($data_h_pembelian))
		{
			
			$data_h_pembelian = $data_h_pembelian->row();
		*/
		
			$this->M_gl_lap_pembelian->hapus_d_pembelian_bayar_by_inv("WHERE MD5(kode_kantor) = '".$kode_kantor."' AND MD5(id_d_bayar) = '".$id_d_bayar."'");
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan Pembayaran Surat Jalan/Invoice '.$data_h_penerimaan->no_surat_jalan,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-laporan-pembelian-pembayaran-invoice/'.$id_h_penerimaan);
	}
	
	/*
	function cek_satuan()
	{
		$hasil_cek = $this->M_gl_satuan->get_satuan('kode_satuan',$_POST['kode_satuan']);
		echo $hasil_cek;
	}
	*/
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_d_pembelian_bayar.php */