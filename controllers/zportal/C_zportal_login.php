<?php

	Class C_zportal_login extends CI_Controller
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->helper(array('captcha','array'));
            $this->load->library(array('form_validation'));
            $this->config->load('cap');
			$this->load->model(array('M_muzaqi'));
		}
		
		function index()
		{
			// $this->auto_remove_captcha();
			// $user = $this->input->post('user');
			// $pass = $this->input->post('pass');
			
            // if($this->validasi_input_captcha() == false)
            // {
                // $data = array();
				
				// $text = ucfirst(random_element($this->config->item('captcha_word')));
					// $number = random_element($this->config->item('captcha_num'));
					// $word = $text.$number;

					// $expiration = time()-300; //batas waktu 5 menit
					// $this->db->query('DELETE FROM tb_captcha WHERE captcha_time < '.$expiration);
					// //konfigurasi captcha
					// $vals = array(
						// 'font_path' => 'system/fonts/texb.ttf',
						// 'img_path' => './assets/captcha/',
						// 'img_url' => base_url().'assets/captcha/',
						// 'img_width' => '235',
						// 'img_height' => 50,
						// 'word' => $word,
						// 'expiration' => $expiration,
						// 'time' => date('Y-m-d H:i:s')
						// );
					
					// $cap = create_captcha($vals);
					// $data['captcha_img'] = $cap['image'];
					
					 // $captcha = array(   'captcha_id' => '',
										// 'captcha_time' => date('Y-m-d H:i:s'),
										// 'ip_address' => $this->input->ip_address(),
										// 'word' => "TEST"
										// );
					 // $query = $this->db->insert_string('tb_captcha',$captcha);
					// $this->db->query($query);	
					
					
					// $this->db->query('OPTIMIZE TABLE `tb_captcha` ');
                    // $this->load->view('admin/login.html',$data);
                    
            // }
            // else
            // {
                    // $this->cek_login();
            // }
			
			
			// if(($this->session->userdata('user_admin') == null) or ($this->session->userdata('pass_admin') == null))
			// {
				// //redirect('index.php/admin-login','location');
				// header('Location: '.base_url().'admin-login');
			// }
			// else
			// {
				// $data_login = $this->M_a_akun->cek_login($this->session->userdata('user_admin'),md5(base64_decode($this->session->userdata('pass_admin'))),'0');
				// if (!empty($data_login))
				// {
                    // $listKategori = $this->M_kat_produk->listKategori();
                    // $listPrduk = $this->M_produk->listproduk('','');
					// $data = array('page_content'=>'adm_produk','title'=>'Elga Network | Dashboard Admin Produk','listKategori'=>$listKategori,'listPrduk'=>$listPrduk);
					// $this->load->view('admin/container',$data);
				// //}
				// // else
				// // {
					// // header('Location: '.base_url().'admin-login');
				// // }
				
				$this->load->view('zportal/login.html');
				////}
			////}
		}
        
        public function cek_login()
        {
            $user = htmlentities($_POST['user'], ENT_QUOTES, 'UTF-8');
            $pass = htmlentities($_POST['pass'], ENT_QUOTES, 'UTF-8');
			
			$cari = " WHERE A.MUZ_EMAIL = '".$user."' AND A.MUZ_PASS = '".md5($pass)."' ";
			$data_login = $this->M_muzaqi->list_muzaqi_limit($cari,1,0);
    		if(!empty($data_login))
    		{
                $data_login = $data_login->row();
				if ($data_login->MUZ_AVATAR <> "")
				{
					$src = $data_login->MUZ_AVATARLINK;
				}
				else
				{
					$src = base_url().'assets/global/images/akun_no_image.gif';
				}
				
				$user = array(
					'ses_MUZ_ID_public' => $data_login->MUZ_ID,
					'ses_KMUZ_ID_public' => $data_login->KMUZ_ID,
					'ses_PRSH_NAMA_public' => $data_login->PRSH_NAMA,
					'ses_PROF_NAMA_public' => $data_login->PROF_NAMA,
					'ses_MUZ_KODE_public' => $data_login->MUZ_KODE,
					'ses_MUZ_NAMA_public' => $data_login->MUZ_NAMA,
					'ses_MUZ_TLP_public' => $data_login->MUZ_TLP,
					'ses_MUZ_EMAIL_public' => $data_login->MUZ_EMAIL,
					'ses_MUZ_GAJI_public' => $data_login->MUZ_GAJI,
					'ses_MUZ_TLAHIR_public' => $data_login->MUZ_TLAHIR,
					'ses_MUZ_DTLAHIR_public' => $data_login->MUZ_DTLAHIR,
					'ses_MUZ_PROV_public' => $data_login->MUZ_PROV,
					'ses_MUZ_KAB_public' => $data_login->MUZ_KAB,
					'ses_MUZ_KEC_public' => $data_login->MUZ_KEC,
					'ses_MUZ_DESA_public' => $data_login->MUZ_DESA,
					'ses_MUZ_ALMTDETAIL_public' => $data_login->MUZ_ALMTDETAIL,
					'ses_MUZ_LONGI_public' => $data_login->MUZ_LONGI,
					'ses_MUZ_LATI_public' => $data_login->MUZ_LATI,
					'ses_MUZ_KET_public' => $data_login->MUZ_KET,
					'ses_MUZ_AVATAR_public' => $data_login->MUZ_AVATAR,
					'ses_MUZ_AVATARLINK_public' => $data_login->MUZ_AVATARLINK,
					'ses_avatar_url_public' => $src,
					'ses_MUZ_USER_public' => $data_login->MUZ_USER,
					'ses_MUZ_PASS_public' => $data_login->MUZ_PASS,
					'ses_MUZ_PASSORI_public' => $data_login->MUZ_PASSORI,
					'ses_MUZ_USERINS_public' => $data_login->MUZ_USERINS,
					'ses_MUZ_USERUPDT_public' => $data_login->MUZ_USERUPDT,
					'ses_MUZ_DTINS_public' => $data_login->MUZ_DTINS,
					'ses_MUZ_DTUPDT_public' => $data_login->MUZ_DTUPDT,
					'ses_MUZ_KODEKANTOR_public' => $data_login->MUZ_KODEKANTOR
				);
				
				$this->session->set_userdata($user);
				header('Location: '.base_url().'zportal-dashboard');
    		}
    		else
    		{
    			//redirect('index.php/login','location');
				header('Location: '.base_url().'zportal');
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
			header('Location: '.base_url().'admin-login');
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