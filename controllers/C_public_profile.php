<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_public_profile extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->helper(array('captcha','array'));
		$this->load->library(array('form_validation'));
		
		$this->load->model(array('M_muzaqi'));
		
	}
	
	public function index()
	{
		$data = array('page_content'=>'profile','sidebar'=>1);
		$this->load->view('public/container.html',$data);
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */