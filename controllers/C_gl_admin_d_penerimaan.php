<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_d_penerimaan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_h_penerimaan','M_gl_d_penerimaan','M_gl_h_pembelian','M_gl_gedung'));
		
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
				$id_h_penerimaan = $this->uri->segment(2,0);
				$cek_h_penerimaan = $this->M_gl_h_penerimaan->get_h_penerimaan_cari_with_id_supplier(" WHERE MD5(A.id_h_penerimaan) = '".$id_h_penerimaan."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
				if(!empty($cek_h_penerimaan))
				{
					$cek_h_penerimaan = $cek_h_penerimaan->row();
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = str_replace("'","",$_GET['cari']);
					}
					else
					{
						$cari = "";
					}
					
					$list_d_penerimaan_produk = $this->M_gl_h_penerimaan->list_d_penerimaan_pakai_alias($cek_h_penerimaan->id_supplier,$cek_h_penerimaan->id_h_pembelian,$cek_h_penerimaan->id_h_penerimaan,$cari,$this->session->userdata('ses_kode_kantor'));
					
					$msgbox_title = " Penerimaan produk/Surat PO ".$cek_h_penerimaan->no_surat_jalan;
					
					$data = array('page_content'=>'gl_admin_d_penerimaan_produk','list_d_penerimaan_produk'=>$list_d_penerimaan_produk,'msgbox_title' => $msgbox_title,'cek_h_penerimaan' => $cek_h_penerimaan);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-purchase-order-terima');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function cek_d_penerimaan_dari_pusat()
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
				$no_surat_jalan = $this->uri->segment(2,0);
				
				$cari = "WHERE MD5(no_surat_jalan) = '".$no_surat_jalan."'";
				$cek_h_penerimaan = $this->M_gl_h_penerimaan->get_h_penerimaan_cari($cari);
				if(!empty($cek_h_penerimaan))
				{
					$cek_h_penerimaan = $cek_h_penerimaan->row();
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = str_replace("'","",$_GET['cari']);
					}
					else
					{
						$cari = "";
					}
					
					$list_d_penerimaan_produk = $this->M_gl_h_penerimaan->list_d_penerimaan($cek_h_penerimaan->id_h_pembelian,$cek_h_penerimaan->id_h_penerimaan,$cari,$cek_h_penerimaan->kode_kantor);
					
					$msgbox_title = " Penerimaan produk/Surat PO ".$cek_h_penerimaan->no_surat_jalan;
					
					$data = array('page_content'=>'gl_admin_d_penerimaan_produk_info_pusat','list_d_penerimaan_produk'=>$list_d_penerimaan_produk,'msgbox_title' => $msgbox_title,'cek_h_penerimaan' => $cek_h_penerimaan);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-purchase-order-terima');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */