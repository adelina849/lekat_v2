<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_kec_list_laporan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_laporan','M_klaporan'));
		
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
				$KLAP_KODE = $this->uri->segment(2,0);
				//echo $LAP_KODE;
				$data_klaporan = $this->M_klaporan->get_klaporan('KLAP_KODE',$KLAP_KODE);
				if(!empty($data_klaporan))
				{
					$data_klaporan = $data_klaporan->row();
					
					$list_laporan = $this->M_laporan->get_laporan_perkecamatan($this->session->userdata('ses_KEC_ID'),"WHERE A.KLAP_ID = '".$data_klaporan->KLAP_ID."'");
					$data = array('page_content'=>'ptn_kec_list_laporan','data_klaporan' => $data_klaporan,'list_laporan' => $list_laporan);
					$this->load->view('kecamatan/container',$data);
				}
				else
				{
					header('Location: '.base_url().'kecamatan-admin-dashboard');
				}
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