<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_d_penjualan_bayar extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_d_penjualan_bayar','M_gl_bank'));
		
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
				$id_h_penjualan = $this->uri->segment(2,0);
				$cari_h_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(id_h_penjualan) = '".$id_h_penjualan."'";
				$data_h_penjualan = $this->M_gl_d_penjualan_bayar->cek_h_penjualan($cari_h_penjualan);
				if(!empty($data_h_penjualan))
				{
					$data_h_penjualan = $data_h_penjualan->row();
					
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						AND MD5(A.id_h_penjualan) = '".$id_h_penjualan."'
						AND (
								COALESCE(B.norek,'') = '".str_replace("'","",$_GET['cari'])."'
								OR COALESCE(B.nama_bank,'') = '".str_replace("'","",$_GET['cari'])."'
								OR COALESCE(C.atas_nama,'') = '".str_replace("'","",$_GET['cari'])."'
							)";
					}
					else
					{
						$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						AND MD5(A.id_h_penjualan) = '".$id_h_penjualan."'
						";
					}
					
					if((!empty($_GET['nominal'])) && ($_GET['nominal']!= "")  )
					{
						$nominal = $_GET['nominal'];
					}
					else
					{
						$nominal = 0;
					}
					
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					
					
					
					$list_d_penjualan_bayar = $this->M_gl_d_penjualan_bayar->list_d_penjualan_bayar_limit($cari,$order_by);
					
					$list_bank = $this->M_gl_bank->list_bank_limit(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'",1000,0);
					
					$msgbox_title = " Detail Pembayaran Transaksi Faktur ".$data_h_penjualan->no_faktur." Atas Nama ".$data_h_penjualan->nama_costumer;
					
					
					
					$data = array('page_content'=>'gl_admin_d_penjualan_bayar','list_d_penjualan_bayar'=>$list_d_penjualan_bayar,'msgbox_title' => $msgbox_title,'list_bank'=>$list_bank,'data_h_penjualan'=>$data_h_penjualan,'nominal' => $nominal);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-laporan-transaksi');
				}
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
				$id_h_penjualan = MD5($_POST['id_h_penjualan']);
				$cari_h_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(id_h_penjualan) = '".$id_h_penjualan."'";
				$data_h_penjualan = $this->M_gl_d_penjualan_bayar->cek_h_penjualan($cari_h_penjualan);
				if(!empty($data_h_penjualan))
				{
					$data_h_penjualan = $data_h_penjualan->row();
						
					if (!empty($_POST['stat_edit']))
					{	
							
						$this->M_gl_d_penjualan_bayar->edit_tampilan_d_bayar
						(
							$_POST['stat_edit'], //$id_d_bayar,
							$_POST['id_h_penjualan'],
							$_POST['id_bank'],
							$_POST['id_costumer'],
							'', //$_POST['no_pembayaran'],
							'', //$_POST['isTabungan'],
							$_POST['cara'],
							'', //$_POST['jenis'],
							$_POST['norek'],
							$_POST['nama_bank'],
							$_POST['atas_nama'],
							$_POST['tgl_pencairan'],
							str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
							$_POST['ket'],
							$_POST['tgl_bayar'],
							$_POST['tgl_tempo_next'],
							'', //$_POST['dari'],
							$_POST['diterima_oleh'],
							'1', //$_POST['isCair'],
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
						$this->M_gl_d_penjualan_bayar->simpan_tampilan_d_bayar
						(
							$_POST['id_h_penjualan'],
							$_POST['id_bank'],
							$_POST['id_costumer'],
							'', //$_POST['no_pembayaran'],
							'', //$_POST['isTabungan'],
							$_POST['cara'],
							'', //$_POST['jenis'],
							$_POST['norek'],
							$_POST['nama_bank'],
							$_POST['atas_nama'],
							$_POST['tgl_pencairan'],
							str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
							$_POST['ket'],
							$_POST['tgl_bayar'],
							$_POST['tgl_tempo_next'],
							'', //$_POST['dari'],
							$_POST['diterima_oleh'],
							'1', //$_POST['isCair'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						
						);
					}
				}
				
				//UPDATE TEMPO
				//UPDATE TEMPO
				header('Location: '.base_url().'gl-admin-laporan-transaksi-pembayaran/'.MD5($_POST['id_h_penjualan']));
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		$id_h_penjualan = ($this->uri->segment(2,0));
		$id_d_bayar = ($this->uri->segment(3,0));
		
		$cari_h_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(id_h_penjualan) = '".$id_h_penjualan."'";
		$data_h_penjualan = $this->M_gl_d_penjualan_bayar->cek_h_penjualan($cari_h_penjualan);
		if(!empty($data_h_penjualan))
		{
			
			$data_h_penjualan = $data_h_penjualan->row();
			
			$this->M_gl_d_penjualan_bayar->hapus('MD5(id_d_bayar)',$id_d_bayar);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan Pembayaran Transaksi '.$data_h_penjualan->no_faktur.' | '.$data_h_penjualan->nama_costumer,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-laporan-transaksi-pembayaran/'.$id_h_penjualan);
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
/* Location: ./application/controllers/C_gl_admin_d_penjualan_bayar.php */