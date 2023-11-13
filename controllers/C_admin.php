<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_dash','M_periode'));
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
				$list_periode_aktif = $this->M_periode->list_periode_limit(' WHERE NOW() >= PER_DARI  AND NOW() <= PER_SAMPAI',100,0);
				$list_persen_laporan_kecamatan = $this->M_dash->list_persen_laporan_kecamatan('');
				$data = array('page_content'=>'admin_dashboard','list_persen_laporan_kecamatan'=>$list_persen_laporan_kecamatan,'list_periode_aktif' => $list_periode_aktif);
				$this->load->view('admin/container',$data);
				//echo "Hallo World";
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin.php */