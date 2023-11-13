<?php

	Class C_kec_login extends CI_Controller
	{
		public function __construct()
		{
			parent::__construct();
			/*$this->load->helper(array('captcha','array'));
            $this->load->library(array('form_validation'));
            $this->config->load('cap');*/
		}
		
		function index()
		{
			
			$this->load->view('kecamatan/login.html');
		}
        
        public function cek_login()
        {
            $user = htmlentities($_POST['user'], ENT_QUOTES, 'UTF-8');
            $pass = htmlentities($_POST['pass'], ENT_QUOTES, 'UTF-8');
            $data_login = $this->M_akun->get_login($user,md5($pass));
    		if(!empty($data_login))
    		{
                if ($data_login->avatar <> "")
                {
                    $src = $data_login->avatar_url;
                }
                else
                {
                	$src = base_url().'assets/global/users/loading.gif';
                }
				
				$user = array(
					'ses_user_admin'  => $user,
					'ses_pass_admin'  => base64_encode($pass),
					'ses_pass_admin_pure'  => ($pass),
					'ses_id_karyawan' => $data_login->id_karyawan,
					'ses_id_jabatan' => $data_login->id_jabatan,
					'ses_KEC_ID' => $data_login->KEC_ID,
					'ses_KEC_KODE' => $data_login->KEC_KODE,
					'ses_KEC_NAMA' => $data_login->KEC_NAMA,
					'ses_nama_jabatan' => $data_login->nama_jabatan,
					'ses_nama_karyawan' => $data_login->nama_karyawan,
					'ses_kode_kantor' => $data_login->kode_kantor,
					'ses_status_kantor' => $data_login->status_kantor,
					'ses_avatar_url' => $src,
					'ses_tgl_ins' => $data_login->tgl_ins
				);
				
    			
    
    			$this->session->set_userdata($user);
    			//redirect('index.php/admin','location');
				header('Location: '.base_url().'kecamatan-admin-dashboard');
    		}
    		else
    		{
    			//redirect('index.php/login','location');
				header('Location: '.base_url().'kecamatan-login');
    		}
        }
        
        public function validasi_input_captcha()
        {
            $this->form_validation->set_rules('captcha','Captcha','required|callback_check_captcha');
            return ($this->form_validation->run() == false)? False : true;
        }
        
        
        function logout()
		{
			$this->session->unset_userdata('ses_user_admin');
			$this->session->unset_userdata('ses_pass_admin');
			$this->session->unset_userdata('ses_id_karyawan');
            $this->session->unset_userdata('ses_id_jabatan');
			$this->session->unset_userdata('ses_nama_jabatan');
			$this->session->unset_userdata('ses_nama_karyawan');
			$this->session->unset_userdata('ses_avatar_url');
			$this->session->unset_userdata('ses_tgl_ins');
			
			//redirect('index.php/login','location');
			header('Location: '.base_url().'kecamatan-login');
		}
        
        
        
        
        function auto_remove_captcha()
		{
			list($usec,$sec) = explode(" ",microtime());
			$now = ((float)$usec + (float)$sec);
			$expiration = 60;//10menit
			$captcha_dir = @opendir("./assets/captcha/");
			while($filename = @readdir($captcha_dir))
			{
				if($filename != "." and $filename != ".." and $filename != "index.php")
				{
					$name = str_replace(".jpg","",$filename);
					if($name+$expiration < $now)
					{
						@unlink("./assets/captcha/".$filename);
					}
				}
			}
			@closedir($captcha_dir);
			//redirect(base_url(),'localtion');
		}
        
        function check_captcha()
		{
			//batas waktu
			$expiration = time()-300;
			//hapus berkas cptcha yang kadaluarsa dalam direktori
			//hapus data captcah yang kadaluarsa pada database
			$this->db->query("DELETE FROM tb_captcha where captcha_time < ".$expiration);
			//$this->db->query("DELETE FROM tb_captcha");
			$sql = "select count(*) as count from tb_captcha where word = ? and ip_address = ? and captcha_time > ?";
			$binds = array($this->input->post('captcha'),$this->input->ip_address(),$expiration);
			
			$query = $this->db->query($sql,$binds);
			$row = $query->row();
			
			if($row->count == 0)
			{
				$this->form_validation->set_message('check_captcha','Captcha yang anda masukan salah atau sudah kadaluarsa');
				return false;
			}
			else
			{
				return true;
				
			}
		}
	}

?>