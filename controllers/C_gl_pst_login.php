<?php

	Class C_gl_pst_login extends CI_Controller
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->helper(array('captcha','array'));
            $this->load->library(array('form_validation'));
            $this->config->load('cap');
			$this->load->model(array('M_gl_hak_akses'));
		}
		
		function index()
		{
			//$kode_kantor = $_GET['kode_kantor'];
			//$data = array('kode_kantor'=>$kode_kantor);
			//$this->load->view('admin/login.html',$data);
			$this->load->view('pusat/login.html');
		}
		
        
        public function cek_login()
        {
            $user = htmlentities($_POST['user'], ENT_QUOTES, 'UTF-8');
            $pass = htmlentities($_POST['pass'], ENT_QUOTES, 'UTF-8');
			//$get_kode_kantor = htmlentities($_POST['kode_kantor'], ENT_QUOTES, 'UTF-8');
			$get_kode_kantor = 'PUSAT';
            $data_login = $this->M_gl_karyawan->get_karyawan_jabatan_row_when_login(" WHERE A.user = '".$user."' AND A.pass = '".base64_encode(md5($pass))."' AND A.kode_kantor = '".$get_kode_kantor."' ");
    		if((!empty($data_login)) && ($_POST['captcha'] == $_POST['captcha2']))
    		{
				$isLocal = strpos(base_url(),".com");
				if($isLocal > 0)
				{
					$isLocal = 'ON';
				}
				else
				{
					$isLocal = 'OF';
				}
					
                if ($data_login->avatar <> "")
                {
                    //$src = $data_login->avatar_url;
					//$src = base_url().'assets/global/karyawan/'.$data_login->avatar;
					$src = base_url().''.$data_login->avatar_url.''.$data_login->avatar;
                }
                else
                {
                	$src = base_url().'assets/global/users/loading.gif';
                }
				
				$catat_log = $this->M_gl_pengaturan->get_data_by_id(5)->nilai;
				$catat_query = $this->M_gl_pengaturan->get_data_by_id(6)->nilai;
				
				$user = array(
					'ses_user_admin'  => $user,
					'ses_pass_admin'  => base64_encode(md5($pass)),
					'ses_pass_admin_pure'  => ($pass),
					'ses_id_karyawan' => $data_login->id_karyawan,
					'ses_id_jabatan' => $data_login->id_jabatan,
					'ses_nama_jabatan' => $data_login->nama_jabatan,
					'ses_kode_dept' => $data_login->kode_dept,
					'ses_nama_dept' => $data_login->nama_dept,
					'ses_no_karyawan' => $data_login->no_karyawan,
					'ses_nik_karyawan' => $data_login->nik_karyawan,
					'ses_nama_karyawan' => $data_login->nama_karyawan,
					'ses_pnd' => $data_login->pnd,
					'ses_tlp' => $data_login->tlp,
					'ses_email' => $data_login->email,
					'ses_tmp_lahir' => $data_login->tmp_lahir,
					'ses_tgl_lahir' => $data_login->tgl_lahir,
					'ses_kelamin' => $data_login->kelamin,
					'ses_sts_nikah' => $data_login->sts_nikah,
					'ses_alamat' => $data_login->alamat,
					'ses_kode_kantor' => $data_login->kode_kantor,
					'ses_nama_kantor' => $data_login->nama_kantor,
					'ses_pemilik_kantor' => $data_login->pemilik,
					'ses_kota_kantor' => $data_login->kota,
					'ses_alamat_kantor' => $data_login->alamat,
					'ses_tlp_kantor' => $data_login->tlp,
					'ses_isDokter' => $data_login->isDokter,
					'ses_hirarki' => $data_login->hirarki,
					'ses_avatar_url' => $src,
					'catat_log' => $catat_log,
					'catat_query' => $catat_query,
					'ses_tgl_ins' => $data_login->tgl_ins,
					'ses_gnl_nama_aplikasi' => $this->M_gl_pengaturan->get_data_by_id(1)->nilai,
					'ses_gnl_logo_aplikasi_thumb' => $this->M_gl_pengaturan->get_data_by_id(2)->nilai,
					'ses_gnl_logo_aplikasi_besar' => $this->M_gl_pengaturan->get_data_by_id(3)->nilai,
					'ses_gnl_deskripsi_aplikasi' => $this->M_gl_pengaturan->get_data_by_id(4)->nilai,
					'ses_gnl_pencatatan_log' => $this->M_gl_pengaturan->get_data_by_id(5)->nilai,
					'ses_gnl_pencatatan_query' => $this->M_gl_pengaturan->get_data_by_id(6)->nilai,
					'ses_gnl_pajak_transaksi' => $this->M_gl_pengaturan->get_data_by_id(8)->nilai,
					'ses_gnl_isToko' => $this->M_gl_pengaturan->get_data_by_id(14)->nilai,
					'isLocal' => $isLocal,
				);
				
    			$this->session->set_userdata($user);
    			//redirect('index.php/admin','location');
				
				
				/*HAPUS LOG YANG KADALUARSA*/
					$this->M_gl_pengaturan->hapus_log_kadaluarsa($data_login->kode_kantor);
				/*HAPUS LOG YANG KADALUARSA*/
				
				
				header('Location: '.base_url().'gl-pusat-dashboard');
    		}
    		else
    		{
    			//redirect('index.php/login','location');
				
				
				if($_POST['user'] == "")
				{
					$this->session->set_flashdata('msg', '<div style="color:red;"><b>Mohon isi terlebih dahulu kolom pengguna/user</b> </div>');
				}
				elseif($_POST['captcha2'] == "")
				{
					$this->session->set_flashdata('msg', '<div style="color:red;"><b>Mohon isi terlebih dahulu kolom captcha</b> </div>');
				}
				elseif($_POST['captcha'] != $_POST['captcha2'])
				{
					$this->session->set_flashdata('msg', '<div style="color:red;"><b>Captcha yang anda masukan tidak sesuai</b> </div>');
				}
				else
				{
					$this->session->set_flashdata('msg', '<div style="color:red;"><b>User dan Password yang anda masukan tidak ditemukan</b> </div>');
				}
				
				/*
				echo $pass.'<br/>';
				echo md5($pass).'<br/>';
				echo base64_encode(md5($pass)).'<br/>';
				*/
				//echo base64_encode(md5($pass));
				
				header('Location: '.base_url().'gl-pusat-login-view');
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
			$this->session->unset_userdata('ses_kode_dept');
			$this->session->unset_userdata('ses_nama_dept');
			$this->session->unset_userdata('ses_no_karyawan');
			$this->session->unset_userdata('ses_nik_karyawan');
			$this->session->unset_userdata('ses_nama_karyawan');
			$this->session->unset_userdata('ses_pnd');
			$this->session->unset_userdata('ses_tlp');
			$this->session->unset_userdata('ses_email');
			$this->session->unset_userdata('ses_tmp_lahir');
			$this->session->unset_userdata('ses_tgl_lahir');
			$this->session->unset_userdata('ses_kelamin');
			$this->session->unset_userdata('ses_sts_nikah');
			$this->session->unset_userdata('ses_alamat');
			$this->session->unset_userdata('ses_kode_kantor');
			$this->session->unset_userdata('ses_nama_kantor');
			$this->session->unset_userdata('ses_pemilik_kantor');
			$this->session->unset_userdata('ses_alamat_kantor');
			$this->session->unset_userdata('ses_tlp_kantor');
			$this->session->unset_userdata('ses_avatar_url');
			$this->session->unset_userdata('catat_log');
			$this->session->unset_userdata('catat_query');
			$this->session->unset_userdata('ses_tgl_ins');
			$this->session->unset_userdata('ses_gnl_nama_aplikasi');
			$this->session->unset_userdata('ses_gnl_logo_aplikasi_thumb');
			$this->session->unset_userdata('ses_gnl_logo_aplikasi_besar');
			$this->session->unset_userdata('ses_gnl_deskripsi_aplikasi');
			$this->session->unset_userdata('ses_gnl_pencatatan_log');
			$this->session->unset_userdata('ses_gnl_pencatatan_query');
			$this->session->unset_userdata('isLocal');
			
			//UNSET SESSION GROUP1
				$this->session->unset_userdata('ses_akses_lvl1_1');
				$this->session->unset_userdata('ses_akses_lvl1_2');
				$this->session->unset_userdata('ses_akses_lvl1_3');
				$this->session->unset_userdata('ses_akses_lvl1_4');
				$this->session->unset_userdata('ses_akses_lvl1_5');
				$this->session->unset_userdata('ses_akses_lvl1_6');
				$this->session->unset_userdata('ses_akses_lvl1_7');
				$this->session->unset_userdata('ses_akses_lvl1_8');
				$this->session->unset_userdata('ses_akses_lvl1_9');
			//UNSET SESSION GROUP1
			
			//UNSET MAIN GROUP
				$this->session->unset_userdata('ses_akses_lvl2_21');
				$this->session->unset_userdata('ses_akses_lvl2_22');
				$this->session->unset_userdata('ses_akses_lvl2_23');
				$this->session->unset_userdata('ses_akses_lvl2_24');
				$this->session->unset_userdata('ses_akses_lvl2_25');
				$this->session->unset_userdata('ses_akses_lvl2_26');
				$this->session->unset_userdata('ses_akses_lvl2_27');
				$this->session->unset_userdata('ses_akses_lvl2_28');
				$this->session->unset_userdata('ses_akses_lvl2_29');
				
				$this->session->unset_userdata('ses_akses_lvl2_41');
				$this->session->unset_userdata('ses_akses_lvl2_42');
				$this->session->unset_userdata('ses_akses_lvl2_43');
				$this->session->unset_userdata('ses_akses_lvl2_44');
				$this->session->unset_userdata('ses_akses_lvl2_45');
				$this->session->unset_userdata('ses_akses_lvl2_51');
				$this->session->unset_userdata('ses_akses_lvl2_52');
				$this->session->unset_userdata('ses_akses_lvl2_53');
				$this->session->unset_userdata('ses_akses_lvl2_54');
				$this->session->unset_userdata('ses_akses_lvl2_55');
				$this->session->unset_userdata('ses_akses_lvl2_56');
				$this->session->unset_userdata('ses_akses_lvl2_57');
				$this->session->unset_userdata('ses_akses_lvl2_58');
				$this->session->unset_userdata('ses_akses_lvl2_59');
				$this->session->unset_userdata('ses_akses_lvl2_510');
				$this->session->unset_userdata('ses_akses_lvl2_511');
				
				$this->session->unset_userdata('ses_akses_lvl2_61');
				$this->session->unset_userdata('ses_akses_lvl2_62');
				$this->session->unset_userdata('ses_akses_lvl2_71');
				$this->session->unset_userdata('ses_akses_lvl2_72');
				$this->session->unset_userdata('ses_akses_lvl2_81');
				$this->session->unset_userdata('ses_akses_lvl2_82');
				$this->session->unset_userdata('ses_akses_lvl2_83');
				$this->session->unset_userdata('ses_akses_lvl2_84');
				$this->session->unset_userdata('ses_akses_lvl2_85');
				
				$this->session->unset_userdata('ses_akses_lvl2_91');
			//UNSET MAIN GROUP
			
			//redirect('index.php/login','location');
			//header('Location: '.base_url().'admin-login');
			header('Location: '.base_url());
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