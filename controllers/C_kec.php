<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_kec extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_dash','M_laporan','M_klaporan','M_periode'));
	}
	
	public function index()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_akun->get_cek_login($this->session->userdata('ses_user_admin'),md5(base64_decode($this->session->userdata('ses_pass_admin'))));
			
			if(!empty($cek_ses_login))
			{
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = " AND (LAP_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%' OR LAP_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = "";
				}
				
				$list_periode_aktif = $this->M_periode->list_periode_limit(' WHERE NOW() >= PER_DARI  AND NOW() <= PER_SAMPAI',100,0);
				$list_klaporan = $this->M_klaporan->list_klaporan_limit(' WHERE KLAP_ISAKTIF = 0 ',100,0);
				$list_persen_laporan_kecamatan = $this->M_dash->list_persen_laporan_kecamatan('');
				
				$data = array('page_content'=>'admin_dashboard','list_klaporan'=>$list_klaporan,'list_periode_aktif' => $list_periode_aktif,'cari' => $cari,'list_persen_laporan_kecamatan' => $list_persen_laporan_kecamatan);
				$this->load->view('kecamatan/container',$data);
				//echo "Hallo World";
			}
			else
			{
				header('Location: '.base_url().'kecamatan-admin-dashboard');
			}
		}
	}
	
	
	public function tampilkan_jenis_laporan()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_akun->get_cek_login($this->session->userdata('ses_user_admin'),md5(base64_decode($this->session->userdata('ses_pass_admin'))));
			
			if(!empty($cek_ses_login))
			{
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = " AND (LAP_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%' OR LAP_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = "";
				}
				
				$list_periode_aktif = $this->M_periode->list_periode_limit(' WHERE NOW() >= PER_DARI  AND NOW() <= PER_SAMPAI',100,0);
				$list_klaporan = $this->M_klaporan->list_klaporan_limit(' WHERE KLAP_ISAKTIF = 0 ',100,0);
				
				$data = array('page_content'=>'buat_laporan','list_klaporan'=>$list_klaporan,'list_periode_aktif' => $list_periode_aktif,'cari' => $cari);
				$this->load->view('kecamatan/container',$data);
				//echo "Hallo World";
			}
			else
			{
				header('Location: '.base_url().'kecamatan-admin-dashboard');
			}
		}
	}

	public function hitung_catatan()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_akun->get_cek_login($this->session->userdata('ses_user_admin'),md5(base64_decode($this->session->userdata('ses_pass_admin'))));
			
			if(!empty($cek_ses_login))
			{
				$KEC_ID = $this->session->userdata('ses_KEC_ID');
				$hitung = $this->M_akun->hitung_catatan($KEC_ID);
				if(!empty($hitung))
				{
					$hitung = $hitung->row();
					$hitung = $hitung->CNT;
					echo $hitung;
				}
				else
				{
					$hitung = 0;
					echo $hitung;
				}
			}
			else
			{
				header('Location: '.base_url().'kecamatan-admin-dashboard');
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin.php */