<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_kec_persen_laporan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_laporan','M_kec'));
		
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
					$cari = str_replace("'","",$_GET['cari']);
				}
				else
				{
					$cari = "";
				}
			
				$KEC_ID = $this->session->userdata('ses_KEC_ID');
				$data_kecamatan = $this->M_kec->get_Kecamatan('KEC_ID',$KEC_ID)->row();
				$list_persen_laporan_perkecamatan = $this->M_laporan->list_persen_laporan_perkecamatan($KEC_ID,$cari);
				$data = array('page_content'=>'ptn_kec_persen_laporan','data_kecamatan' => $data_kecamatan,'list_persen_laporan_perkecamatan' => $list_persen_laporan_perkecamatan);
				$this->load->view('kecamatan/container',$data);
						
				
			}
			else
			{
				header('Location: '.base_url().'kecamatan-login');
			}
		}
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_admin_laporan.php */