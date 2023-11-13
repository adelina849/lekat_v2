<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_public extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('m_dash'));
		$this->load->model('M_amal');
	}
	
	public function index()
	{
		$list_amal_terbatas = $this->M_amal->list_amal_limit('',2,0);
		$data = array('list_amal_terbatas' => $list_amal_terbatas);
		$this->load->view('public/home/container_home.html',$data);
	}
	
	public function detail_halaman()
	{
		$link = $this->uri->segment(2,0);
		$data_halaman = $this->M_public->detail_halaman(" WHERE HAL_LINKTITLE = '".$link."'");
		if(!empty($data_halaman))
		{
			$data_halaman = $data_halaman->row();
			$data = array('data_halaman' => $data_halaman);
			$this->load->view('public/container.html',$data);
		}
		else
		{
			$list_amal_terbatas = $this->M_amal->list_amal_limit('',2,0);
			$data = array('list_amal_terbatas' => $list_amal_terbatas);
			$this->load->view('public/home/container_home.html',$data);
		}
	}
	
	function simpan_lokasi_saya()
	{
		$lati = $_POST['lati'];
		$longi = $_POST['longi'];
		
		$user = array(
			'ses_lati'  => $lati,
			'ses_longi'  => $longi
		);
		
		$this->session->set_userdata($user);
		echo $this->session->userdata('ses_lati');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin.php */