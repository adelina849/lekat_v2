<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_board_laporan extends CI_Controller {

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
				/*
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = " WHERE KLAP_ISAKTIF = '0' 
									AND (KLAP_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%' 
										OR KLAP_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = " WHERE KLAP_ISAKTIF = '0'";
				}
				*/
				$cari = " WHERE KLAP_ISAKTIF = '0'";
				
				$list_klaporan = $this->M_klaporan->list_klaporan_limit($cari,100,0);
				$data = array('page_content'=>'ptn_admin_board_laporan','list_klaporan'=>$list_klaporan,'cari' => $cari);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_admin_laporan.php */