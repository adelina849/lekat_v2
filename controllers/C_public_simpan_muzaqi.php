<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_public_simpan_muzaqi extends CI_Controller {

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
		// echo $_POST['MUZ_NAMA'].'<br/>';
		// echo $_POST['MUZ_EMAIL'].'<br/>';
		// echo $_POST['MUZ_TLP'].'<br/>';
		// echo $_POST['MUZ_PASS'].'<br/>';
		// echo $_POST['MUZ_PASS2'].'<br/>';
		
		
		if((!empty($_POST['MUZ_NAMA'])) && ($_POST['MUZ_NAMA']!= "")  )
		{
			$MUZ_NAMA =  $_POST['MUZ_NAMA'];
		}
		else
		{
			$MUZ_NAMA = "";
		}
		
		if((!empty($_POST['MUZ_EMAIL'])) && ($_POST['MUZ_EMAIL']!= "")  )
		{
			$MUZ_EMAIL =  $_POST['MUZ_EMAIL'];
		}
		else
		{
			$MUZ_EMAIL = "";
		}
		
		if((!empty($_POST['MUZ_TLP'])) && ($_POST['MUZ_TLP']!= "")  )
		{
			$MUZ_TLP =  $_POST['MUZ_TLP'];
		}
		else
		{
			$MUZ_TLP = "";
		}
		
		if((!empty($_POST['MUZ_PASS'])) && ($_POST['MUZ_PASS']!= "")  )
		{
			$MUZ_PASS =  $_POST['MUZ_PASS'];
		}
		else
		{
			$MUZ_PASS = "";
		}
		
		if((!empty($_POST['MUZ_PASS2'])) && ($_POST['MUZ_PASS2']!= "")  )
		{
			$MUZ_PASS2 =  $_POST['MUZ_PASS2'];
		}
		else
		{
			$MUZ_PASS2 = "";
		}
		
		
		
		if( ($MUZ_NAMA == "") )
		{
			$error_nama = "Nama Tidak Boleh Kosong";
		}
		else
		{
			$error_nama = "";
		}
		
		if( ($MUZ_EMAIL == "") )
		{
			$error_email = "Email Tidak Boleh Kosong";
		}
		else
		{
			$error_email = "";
		}
		
		if( ($MUZ_TLP == "") )
		{
			$error_tlp = "Telpon Tidak Boleh Kosong";
		}
		else
		{
			$error_tlp = "";
		}
		
		if(($MUZ_PASS != $MUZ_PASS2) OR ($MUZ_PASS == ""))
		{
			$error_password = "Password tidak boleh kosong dan harus konfirmasi";
		}
		else
		{
			$error_password = "";
		}
		
		if( ($error_nama == "") && ($error_email == "") && ($error_tlp == "") && ($error_password == "") )
		{
			//echo"SELAMAT ANDA BERHASIL";			
			
			$status_ada = 0;
			$cek_data_muzaqi = $this->M_muzaqi->list_muzaqi_limit(" WHERE MUZ_EMAIL = '".$MUZ_EMAIL."' ",1,0);
			if(!empty($cek_data_muzaqi))
			{
				$cek_data_muzaqi = $cek_data_muzaqi->row();
				$status_ada = 1;
			}
			else
			{
				
				$this->M_muzaqi->simpan
				(
				
					'',
					'',
					'',
					'',
					$MUZ_NAMA,
					$MUZ_TLP,
					$MUZ_EMAIL,
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					'',
					//$_POST['MUZ_AVATAR'],
					//$_POST['MUZ_AVATARLINK'],
					$MUZ_EMAIL,
					md5($MUZ_PASS),
					$MUZ_PASS,
					'1',
					'',
					'',
					'KABCJR'
				
				);
				$cek_data_muzaqi = $this->M_muzaqi->list_muzaqi_limit(" WHERE MUZ_EMAIL = '".$MUZ_EMAIL."' ",1,0);
				$cek_data_muzaqi = $cek_data_muzaqi->row();
				
				$status_ada = 0;
			}
			
			//echo $status_ada;
			if($status_ada == 0)
			{
				$this->load->library('email');
				$this->load->config('email');
				//$url = base_url()."v/".$saltid."/".$saltusername;
				//$url ="";
				$this->email->set_mailtype("html");
				$this->email->set_newline("\r\n");

				//Email content
				$htmlContent = "<h1>Aktifasi Akun</h1>";
				//$htmlContent .= "<html><head><head></head><body><p>Hai ".$cek_ses_login->MUZ_NAMA."</p><p>Email ini dikirim karena anda meminta kami untuk mengirim password akun anda</p><p><b>Password anda : ".$cek_ses_login->MUZ_PASSORI."</b></p><br/><p>Hormat kami,</p><p>Tim BAZNAS Kab.Sukabumi</p></body></html>";
				$htmlContent .= '<html><head><head></head><body><p>Hai '. $cek_data_muzaqi->MUZ_NAMA .'</p><p>Silahkan klik tombol dibawah ini agar akun anda terverifikasi </p><br/><a href="http://www.kabsukabumi.baznas.go.id/aktifasi-akun?id='.$cek_data_muzaqi->MUZ_LINKAKTIFASI.'"> <button class="button" style="background-color: #4CAF50; /* Green */  
	   border: none;  
	   color: white;  
	   padding: 15px 32px;  
	   text-align: center;  
	   text-decoration: none;  
	   display: inline-block;  
	   font-size: 16px;  
	   margin: 4px 2px;  
	   cursor: pointer; ">Aktifasi Akun BAZNAS Kab.Sukabumi</button>  </a><br/><p>Hormat kami,</p><p>Tim BAZNAS Kab.Sukabumi</p></body></html>';

				$this->email->to($cek_data_muzaqi->MUZ_EMAIL);
				//$this->email->to('baznas.kabsukabumi2018@gmail.com');
				$this->email->from('admin@kabsukabumi.baznas.go.id','BAZNAS Kab.Sukabumi');
				$this->email->subject('Aktifasi Akun BAZNAS Kabupaten Sukabumi (Do not reply)');
				$this->email->message($htmlContent);

				//Send email
				//$this->email->send();
				
				if($this->email->send())
				{
					//BERHASIL
					//echo"SELAMAT ANDA BERHASIL";
					$data = array('page_content'=>'info','judul' => "REGISTRASI BERHASIL",'isi' => $cek_data_muzaqi->MUZ_NAMA ." Selamat, registrasi anda telah berhasil. Silahkan cek email ".$cek_data_muzaqi->MUZ_EMAIL." untuk mengaktifkan akun anda");
					$this->load->view('public/container.html',$data);
				} else {
					//GAGAL
					//echo"GAGAL";
					$data = array('page_content'=>'info','judul' => "REGISTRASI GAGAL",'isi' => $cek_data_muzaqi->MUZ_NAMA ." Maaf, registrasi anda tidak berhasil. Silahkan cek email ".$cek_data_muzaqi->MUZ_EMAIL." untuk memastikan email tersebut aktif");
					$this->load->view('public/container.html',$data);
				}
			}
			else
			{
				$data = array('page_content'=>'info','judul' => "EMAIL SUDAH TERDAFTAR",'isi' => $cek_data_muzaqi->MUZ_NAMA ." Maaf, email yang anda masukan ".$cek_data_muzaqi->MUZ_EMAIL." Sudah terdaftar pada ".$cek_data_muzaqi->MUZ_DTINS);
				$this->load->view('public/container.html',$data);
			}
			
			
		}
		else
		{
			$data = array('page_content'=>'registrasi','MUZ_NAMA' => $MUZ_NAMA,'MUZ_EMAIL' => $MUZ_EMAIL,'MUZ_TLP' => $MUZ_TLP,'MUZ_PASS' => $MUZ_PASS,'error_nama' => $error_nama,'error_email' => $error_email,'error_tlp' => $error_tlp,'error_password' => $error_password);
			//$this->load->view('admin/container',$data);
			$this->load->view('public/container.html',$data);
		}
		
		
	}
	
	public function login_view()
	{
		
		if((!empty($_POST['MUZ_EMAIL'])) && ($_POST['MUZ_EMAIL']!= "")  )
		{
			$MUZ_EMAIL =  $_POST['MUZ_EMAIL'];
		}
		else
		{
			$MUZ_EMAIL = "";
		}
		
		
		if((!empty($_POST['MUZ_PASS'])) && ($_POST['MUZ_PASS']!= "")  )
		{
			$MUZ_PASS =  $_POST['MUZ_PASS'];
		}
		else
		{
			$MUZ_PASS = "";
		}
		
		
		if( ($MUZ_EMAIL == "") )
		{
			$error_email = "Email Tidak Boleh Kosong";
		}
		else
		{
			$error_email = "";
		}
		
		
		if( ($MUZ_EMAIL == "") )
		{
			$error_email = "Email Tidak Boleh Kosong";
		}
		else
		{
			$error_email = "";
		}
		
		
		
		
		$cek_data_muzaqi = $this->M_muzaqi->list_muzaqi_limit(" WHERE MUZ_EMAIL = '".$MUZ_EMAIL."' AND MUZ_PASS = '". md5($MUZ_PASS)."' AND MUZ_AKTIFASI IN (0,2) ",1,0);
		
		
		if(!empty($cek_data_muzaqi))
		{
			$data_muzaqi = $cek_data_muzaqi->row();
			$user = array(
				'ses_MUZ_ID' => $data_muzaqi->MUZ_ID,
				'ses_KMUZ_ID' => $data_muzaqi->KMUZ_ID,
				'ses_PRSH_NAMA' => $data_muzaqi->PRSH_NAMA,
				'ses_PROF_NAMA' => $data_muzaqi->PROF_NAMA,
				'ses_MUZ_KODE' => $data_muzaqi->MUZ_KODE,
				'ses_MUZ_NAMA' => $data_muzaqi->MUZ_NAMA,
				'ses_MUZ_TLP' => $data_muzaqi->MUZ_TLP,
				'ses_MUZ_EMAIL' => $data_muzaqi->MUZ_EMAIL,
				'ses_MUZ_GAJI' => $data_muzaqi->MUZ_GAJI,
				'ses_MUZ_TLAHIR' => $data_muzaqi->MUZ_TLAHIR,
				'ses_MUZ_DTLAHIR' => $data_muzaqi->MUZ_DTLAHIR,
				'ses_MUZ_PROV' => $data_muzaqi->MUZ_PROV,
				'ses_MUZ_KAB' => $data_muzaqi->MUZ_KAB,
				'ses_MUZ_KEC' => $data_muzaqi->MUZ_KEC,
				'ses_MUZ_DESA' => $data_muzaqi->MUZ_DESA,
				'ses_MUZ_ALMTDETAIL' => $data_muzaqi->MUZ_ALMTDETAIL,
				'ses_MUZ_LONGI' => $data_muzaqi->MUZ_LONGI,
				'ses_MUZ_LATI' => $data_muzaqi->MUZ_LATI,
				'ses_MUZ_KET' => $data_muzaqi->MUZ_KET,
				'ses_MUZ_AVATAR' => $data_muzaqi->MUZ_AVATAR,
				'ses_MUZ_AVATARLINK' => $data_muzaqi->MUZ_AVATARLINK,
				'ses_MUZ_USER' => $data_muzaqi->MUZ_USER,
				'ses_MUZ_PASS' => $data_muzaqi->MUZ_PASS,
				'ses_MUZ_PASSORI' => $data_muzaqi->MUZ_PASSORI,
				'ses_MUZ_AKTIFASI' => $data_muzaqi->MUZ_AKTIFASI,
				'ses_MUZ_LINKAKTIFASI' => $data_muzaqi->MUZ_LINKAKTIFASI,
				'ses_MUZ_USERINS' => $data_muzaqi->MUZ_USERINS,
				'ses_MUZ_USERUPDT' => $data_muzaqi->MUZ_USERUPDT,
				'ses_MUZ_DTINS' => $data_muzaqi->MUZ_DTINS,
				'ses_MUZ_DTUPDT' => $data_muzaqi->MUZ_DTUPDT,
				'ses_MUZ_KODEKANTOR' => $data_muzaqi->MUZ_KODEKANTOR

			);
			
			$this->session->set_userdata($user);
			//header('Location: '.base_url().'admin-muzaqi?cari='.$cari);
			//header('Location: '.base_url().'/public-profile');
			header('Location: '.$_POST['LINK_URL']);
			
		}
		else
		{
			$data = array('page_content'=>'login','MUZ_EMAIL' => $MUZ_EMAIL,'error_email' => $error_email);
			//$this->load->view('admin/container',$data);
			$this->load->view('public/container.html',$data);
		}
		
	}
	
	
	public function akun_aktifasi()
	{
		$kode_aktifasi = $_GET['id'];
		$cek_data_muzaqi = $this->M_muzaqi->list_muzaqi_limit(" WHERE MUZ_LINKAKTIFASI = '".$kode_aktifasi."' ",1,0);
		if(!empty($cek_data_muzaqi))
		{
			$this->M_muzaqi->edit_aktifasi
			(
				$kode_aktifasi,
				2
			);
			
			$cek_data_muzaqi = $cek_data_muzaqi->row();
			$data = array('page_content'=>'info','judul' => "AKTIFASI AKUN",'isi' => $cek_data_muzaqi->MUZ_NAMA ." Selamat, akun anda sudah terverifikasi, dilahkan login untuk melanjutkan aktifitas anda ");
			$this->load->view('public/container.html',$data);
		}
		else
		{
			$data = array('page_content'=>'info','judul' => "AKTIFASI GAGAL",'isi' => "Maaf, Validasi email gagal mohon perhatikan link yang ada di email anda");
			$this->load->view('public/container.html',$data);
		}
	}
	
	
	public function ubah_data_muzaki()
	{
		if(($this->session->userdata('ses_MUZ_ID') == null) or ($this->session->userdata('ses_MUZ_ID') == null))
		{
			header('Location: '.base_url().'muzaqi-login');
		}
		else
		{
			$this->M_muzaqi->edit
			(
				$_POST['MUZ_ID'],
				$_POST['KMUZ_ID'],
				$_POST['PRSH_NAMA'],
				$_POST['PROF_NAMA'],
				$_POST['MUZ_KODE'],
				$_POST['MUZ_NAMA'],
				$_POST['MUZ_TLP'],
				$_POST['MUZ_EMAIL'],
				$_POST['MUZ_GAJI'],
				$_POST['MUZ_TLAHIR'],
				$_POST['MUZ_DTLAHIR'],
				$_POST['MUZ_PROV'],
				$_POST['MUZ_KAB'],
				$_POST['MUZ_KEC'],
				$_POST['MUZ_DESA'],
				$_POST['MUZ_ALMTDETAIL'],
				$_POST['MUZ_LONGI'],
				$_POST['MUZ_LATI'],
				$_POST['MUZ_KET'],
				/*$_POST['MUZ_AVATAR'],
				$_POST['MUZ_AVATARLINK'],
				/*$_POST['MUZ_USER'],*/
				$_POST['MUZ_PASS'],
				$_POST['MUZ_PASSORI'],
				$_POST['MUZ_USERUPDT'],
				$_POST['MUZ_KODEKANTOR']
			);
			
			
			$cek_data_muzaqi = $this->M_muzaqi->list_muzaqi_limit(" WHERE MUZ_EMAIL = '".$_POST['MUZ_EMAIL']."' AND MUZ_AKTIFASI IN (0,2) ",1,0);
		
		
			if(!empty($cek_data_muzaqi))
			{
				$data_muzaqi = $cek_data_muzaqi->row();
				$user = array(
					'ses_MUZ_ID' => $data_muzaqi->MUZ_ID,
					'ses_KMUZ_ID' => $data_muzaqi->KMUZ_ID,
					'ses_PRSH_NAMA' => $data_muzaqi->PRSH_NAMA,
					'ses_PROF_NAMA' => $data_muzaqi->PROF_NAMA,
					'ses_MUZ_KODE' => $data_muzaqi->MUZ_KODE,
					'ses_MUZ_NAMA' => $data_muzaqi->MUZ_NAMA,
					'ses_MUZ_TLP' => $data_muzaqi->MUZ_TLP,
					'ses_MUZ_EMAIL' => $data_muzaqi->MUZ_EMAIL,
					'ses_MUZ_GAJI' => $data_muzaqi->MUZ_GAJI,
					'ses_MUZ_TLAHIR' => $data_muzaqi->MUZ_TLAHIR,
					'ses_MUZ_DTLAHIR' => $data_muzaqi->MUZ_DTLAHIR,
					'ses_MUZ_PROV' => $data_muzaqi->MUZ_PROV,
					'ses_MUZ_KAB' => $data_muzaqi->MUZ_KAB,
					'ses_MUZ_KEC' => $data_muzaqi->MUZ_KEC,
					'ses_MUZ_DESA' => $data_muzaqi->MUZ_DESA,
					'ses_MUZ_ALMTDETAIL' => $data_muzaqi->MUZ_ALMTDETAIL,
					'ses_MUZ_LONGI' => $data_muzaqi->MUZ_LONGI,
					'ses_MUZ_LATI' => $data_muzaqi->MUZ_LATI,
					'ses_MUZ_KET' => $data_muzaqi->MUZ_KET,
					'ses_MUZ_AVATAR' => $data_muzaqi->MUZ_AVATAR,
					'ses_MUZ_AVATARLINK' => $data_muzaqi->MUZ_AVATARLINK,
					'ses_MUZ_USER' => $data_muzaqi->MUZ_USER,
					'ses_MUZ_PASS' => $data_muzaqi->MUZ_PASS,
					'ses_MUZ_PASSORI' => $data_muzaqi->MUZ_PASSORI,
					'ses_MUZ_AKTIFASI' => $data_muzaqi->MUZ_AKTIFASI,
					'ses_MUZ_LINKAKTIFASI' => $data_muzaqi->MUZ_LINKAKTIFASI,
					'ses_MUZ_USERINS' => $data_muzaqi->MUZ_USERINS,
					'ses_MUZ_USERUPDT' => $data_muzaqi->MUZ_USERUPDT,
					'ses_MUZ_DTINS' => $data_muzaqi->MUZ_DTINS,
					'ses_MUZ_DTUPDT' => $data_muzaqi->MUZ_DTUPDT,
					'ses_MUZ_KODEKANTOR' => $data_muzaqi->MUZ_KODEKANTOR

				);
				
				$this->session->set_userdata($user);
				//header('Location: '.base_url().'admin-muzaqi?cari='.$cari);
				header('Location: '.base_url().'/public-profile');
				
			}
			else
			{
				$data = array('page_content'=>'login','MUZ_EMAIL' => $MUZ_EMAIL,'error_email' => $error_email);
				//$this->load->view('admin/container',$data);
				$this->load->view('public/container.html',$data);
			}
		}
	}
	
	public function ubah_avatar()
	{
		if(($this->session->userdata('ses_MUZ_ID') == null) or ($this->session->userdata('ses_MUZ_ID') == null))
		{
			header('Location: '.base_url().'muzaqi-login');
		}
		else
		{
			if (!empty($_FILES['foto']['name']))
			{
				
					$cek_data_muzaqi = $this->M_muzaqi->list_muzaqi_limit(" WHERE MUZ_ID = '".$_POST['MUZ_ID']."' AND MUZ_AKTIFASI IN (0,2) ",1,0);
					
					if(!empty($cek_data_muzaqi))
					{
						$cek_data_muzaqi = $cek_data_muzaqi->row();
						//$this->do_upload($_FILES['foto']['name'],$data_karyawan->avatar);
						$this->do_upload($cek_data_muzaqi->MUZ_ID,$cek_data_muzaqi->avatar);
						//$foto = $_FILES['foto']['name'];
						$this->M_muzaqi->edit_avatar
						(
							$_POST['MUZ_ID'],
							//$cek_data_muzaqi->MUZ_ID.'.'.$_FILES['foto']['type'],
							$cek_data_muzaqi->MUZ_ID.'.'.str_replace("image/","",$_FILES['foto']['type']),
							base_url().'assets/global/costumer/'.$cek_data_muzaqi->MUZ_ID.'.'.str_replace("image/","",$_FILES['foto']['type'])
						);
						
						$user = array(
							'ses_MUZ_AVATAR' => $cek_data_muzaqi->MUZ_ID.'.'.str_replace("image/","",$_FILES['foto']['type']),
							'ses_MUZ_AVATARLINK' => base_url().'assets/global/costumer/'.$cek_data_muzaqi->MUZ_ID.'.'.str_replace("image/","",$_FILES['foto']['type'])

						);
						
						$this->session->set_userdata($user);
						
						header('Location: '.base_url().'public-profile');
					}
					else
					{
						header('Location: '.base_url().'muzaqi-login');
					}
			}
			else
			{
				header('Location: '.base_url().'public-profile');
			}
		}
	}
	
	public function ubah_password()
	{
		if(($this->session->userdata('ses_MUZ_ID') == null) or ($this->session->userdata('ses_MUZ_ID') == null))
		{
			header('Location: '.base_url().'muzaqi-login');
		}
		else
		{
			$cek_data_muzaqi = $this->M_muzaqi->list_muzaqi_limit(" WHERE MUZ_ID = '".$_POST['PASS_MUZ_ID']."' AND MUZ_AKTIFASI IN (0,2) ",1,0);
			
			if(!empty($cek_data_muzaqi))
			{
				$cek_data_muzaqi = $cek_data_muzaqi->row();
				$this->M_muzaqi->edit_password
				(
					$_POST['PASS_MUZ_ID'],
					$_POST['PASS_MUZ_PASSORI1']
				);
				
				$user = array(
					'ses_MUZ_PASS' => $data_muzaqi->MUZ_PASS,
					'ses_MUZ_PASSORI' => $data_muzaqi->MUZ_PASSORI

				);
				
				$this->session->set_userdata($user);
				
				header('Location: '.base_url().'public-profile');
			}
			else
			{
				header('Location: '.base_url().'muzaqi-login');
			}
		}
	}
	
	function logout()
	{
		//UNSET SUB GROUP
			$this->session->unset_userdata('ses_MUZ_ID');
			$this->session->unset_userdata('ses_KMUZ_ID');
			$this->session->unset_userdata('ses_PRSH_NAMA');
			$this->session->unset_userdata('ses_PROF_NAMA');
			$this->session->unset_userdata('ses_MUZ_KODE');
			$this->session->unset_userdata('ses_MUZ_NAMA');
			$this->session->unset_userdata('ses_MUZ_TLP');
			$this->session->unset_userdata('ses_MUZ_EMAIL');
			$this->session->unset_userdata('ses_MUZ_GAJI');
			$this->session->unset_userdata('ses_MUZ_TLAHIR');
			$this->session->unset_userdata('ses_MUZ_DTLAHIR');
			$this->session->unset_userdata('ses_MUZ_PROV');
			$this->session->unset_userdata('ses_MUZ_KAB');
			$this->session->unset_userdata('ses_MUZ_KEC');
			$this->session->unset_userdata('ses_MUZ_DESA');
			$this->session->unset_userdata('ses_MUZ_ALMTDETAIL');
			$this->session->unset_userdata('ses_MUZ_LONGI');
			$this->session->unset_userdata('ses_MUZ_LATI');
			$this->session->unset_userdata('ses_MUZ_KET');
			$this->session->unset_userdata('ses_MUZ_AVATAR');
			$this->session->unset_userdata('ses_MUZ_AVATARLINK');
			$this->session->unset_userdata('ses_MUZ_USER');
			$this->session->unset_userdata('ses_MUZ_PASS');
			$this->session->unset_userdata('ses_MUZ_PASSORI');
			$this->session->unset_userdata('ses_MUZ_AKTIFASI');
			$this->session->unset_userdata('ses_MUZ_LINKAKTIFASI');
			$this->session->unset_userdata('ses_MUZ_USERINS');
			$this->session->unset_userdata('ses_MUZ_USERUPDT');
			$this->session->unset_userdata('ses_MUZ_DTINS');
			$this->session->unset_userdata('ses_MUZ_DTUPDT');
			$this->session->unset_userdata('ses_MUZ_KODEKANTOR');
		//UNSET SUB GROUP
		//redirect('index.php/login','location');
		header('Location: '.base_url().'muzaqi-login');
		
	}
	
	function do_upload($id,$cek_bfr)
	{
		$this->load->library('upload');

		if($cek_bfr != '')
		{
			@unlink('./assets/global/costumer/'.$cek_bfr);
		}
		
		if (!empty($_FILES['foto']['name']))
		{
			$config['upload_path'] = 'assets/global/costumer/';
			$config['allowed_types'] = 'gif|jpg|png';
			$config['max_size']	= '2024';
			//$config['max_widtd']  = '300';
			//$config['max_height']  = '300';
			$config['file_name']	= $id;
			$config['overwrite']	= true;
			

			$this->upload->initialize($config);

			//Upload file 1
			if ($this->upload->do_upload('foto'))
			{
				$hasil = $this->upload->data();
			}
		}
	}
	
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */