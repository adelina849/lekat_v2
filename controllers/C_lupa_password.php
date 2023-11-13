<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class C_lupa_password extends CI_Controller {

	/**
	 * Index Page for this controller.
	 *
	 * Maps to the following URL
	 * 		http://example.com/index.php/welcome
	 *	- or -
	 * 		http://example.com/index.php/welcome/index
	 *	- or -
	 * Since this controller is set as the default controller in
	 * config/routes.php, it's displayed at http://example.com/
	 *
	 * So any other public methods not prefixed with an underscore will
	 * map to /index.php/welcome/<method_name>
	 * @see https://codeigniter.com/user_guide/general/urls.html
	 */
	 
	public function __construct()
	{
		parent::__construct();
		$this->load->helper(array('captcha','array'));
		$this->load->library(array('form_validation'));
		$this->config->load('cap');
		//$this->load->model(array(''));
	}
	
	public function index()
	{
		//$this->load->view('index.html');
		
		if(($this->session->userdata('ses_MUZ_EMAIL_public') == null) or ($this->session->userdata('ses_MUZ_PASS_public') == null))
		{
			$this->load->view('lupa_password.html');
		}
		else
		{
			$cari = " WHERE A.MUZ_EMAIL = '".$this->session->userdata('ses_MUZ_EMAIL_public')."' AND A.MUZ_PASS = '".$this->session->userdata('ses_MUZ_PASS_public')."' ";
			$cek_ses_login = $this->M_muzaqi->list_muzaqi_limit($cari,1,0);
			if(!empty($cek_ses_login))
			{
				header('Location: '.base_url().'admin-dashboard');
			}
			else
			{
				$this->load->view('lupa_password.html');
			}
		}
	}
	
	
	function send_email_lupa_password()
	{
		$MUZ_EMAIL = $_POST['MUZ_EMAIL'];
		$cari = " WHERE A.MUZ_EMAIL = '".$MUZ_EMAIL."' ";
		$cek_ses_login = $this->M_muzaqi->list_muzaqi_limit($cari,1,0);
		if(!empty($cek_ses_login))
		{
			$cek_ses_login = $cek_ses_login->row();
			
			$this->load->library('email');
			$this->load->config('email');
			//$url = base_url()."v/".$saltid."/".$saltusername;
			//$url ="";
			$this->email->set_mailtype("html");
			$this->email->set_newline("\r\n");

			//Email content
			$htmlContent = "<h1>Lupa Password</h1>";
			$htmlContent .= "<html><head><head></head><body><p>Hai ".$cek_ses_login->MUZ_NAMA."</p><p>Email ini dikirim karena anda meminta kami untuk mengirim password akun anda</p><p><b>Password anda : ".$cek_ses_login->MUZ_PASSORI."</b></p><br/><p>Hormat kami,</p><p>Tim BAZNAS Kab.Sukabumi</p></body></html>";

			$this->email->to($cek_ses_login->MUZ_EMAIL);
			$this->email->from('admin@kabsukabumi.baznas.go.id','BAZNAS Kab.Sukabumi');
			$this->email->subject('Forgot Password (Do not reply)');
			$this->email->message($htmlContent);

			//Send email
			//$this->email->send();
			
			if($this->email->send())
			{
				//echo $this->email->print_debugger();
				//return true;
				$this->load->view('login.html');
			} else {
				//echo $this->email->print_debugger();
				//return false;
				$this->load->view('login.html');
			}
		}
		else
		{
			$this->load->view('lupa_password.html');
		}
		
		
		
		
		
	}
	
	function send_email_jadi()
	{
		$this->load->library('email');
		$this->load->config('email');
		//$url = base_url()."v/".$saltid."/".$saltusername;
		$url ="";
		
		$this->email->set_mailtype("html");
		$this->email->set_newline("\r\n");

		//Email content
		$htmlContent = "<h1>Verifikasi Akun</h1>";
		$htmlContent .= "<html><head><head></head><body><p>Hai</p><p>Terima kasih telah mendaftar di warungakinini.com.</p><p>Silakan klik link di bawah ini untuk melakukan verifikasi.</p>".$url."<br/><p>Hormat kami,</p><p>Tim Warungakinini</p></body></html>";

		$this->email->to('mulyanayusuf30@gmail.com');
		$this->email->from('ryuur3i@gmail.com','Warungakinini.com');
		$this->email->subject('Please Verify Your Email Address');
		$this->email->message($htmlContent);

		//Send email
		//$this->email->send();
		
		if($this->email->send())
		{
			echo $this->email->print_debugger();
			return true;
		} else {
			echo $this->email->print_debugger();
			return false;
		}
      

		
		

	}
	
	
}
