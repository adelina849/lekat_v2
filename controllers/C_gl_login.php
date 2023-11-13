<?php

	Class C_gl_login extends CI_Controller
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->helper(array('captcha','array'));
            $this->load->library(array('form_validation'));
            $this->config->load('cap');
			$this->load->model(array('M_gl_hak_akses','M_gl_dashboard','M_gl_produk'));
		}
		
		function index()
		{
			//$kode_kantor = $_GET['kode_kantor'];
			//$data = array('kode_kantor'=>$kode_kantor);
			//$this->load->view('admin/login.html',$data);
			$this->load->view('admin/login.html');
		}
		
        
        public function cek_login()
        {
            $user = htmlentities($_POST['user'], ENT_QUOTES, 'UTF-8');
            $pass = htmlentities($_POST['pass'], ENT_QUOTES, 'UTF-8');
			$get_kode_kantor = htmlentities($_POST['kode_kantor'], ENT_QUOTES, 'UTF-8');
            $data_login = $this->M_gl_karyawan->get_karyawan_jabatan_row_when_login(" WHERE A.user = '".$user."' AND A.pass = '".base64_encode(md5($pass))."' AND A.kode_kantor = '".$get_kode_kantor."' AND A.isAktif NOT IN ('PHK','RESIGN')");
    		if((!empty($data_login)) && ($_POST['captcha'] == $_POST['captcha2']))
    		{
				//DIPINDAH KE PEMABNGGILAN CRON JOB UPDATE STOCK DI TB PRODUK
				/*
				if($get_kode_kantor == 'PST')
				{
					//UPDATE MOVINNG PROCUK
						$this->M_gl_dashboard->update_moving_produk();
					//UPDATE MOVINNG PROCUK
				}
				*/
				//DIPINDAH KE PEMABNGGILAN CRON JOB UPDATE STOCK DI TB PRODUK
				
				//UPDATE STOCK DI tb_produk
					$dt_stock = "";
					$cek_dt_stock = $this->M_gl_produk->cek_dt_stock($get_kode_kantor);
					if(!empty($cek_dt_stock))
					{
						$cek_dt_stock = $cek_dt_stock->row();
						if($cek_dt_stock->STS_DTSTOCK == '0')
						{
							$dt_stock = date('Y-m-d H:i:s');
							$this->M_gl_produk->update_stock_tb_produk($get_kode_kantor,date('Y-m-d'),date('Y-m-d'),$dt_stock);
							$this->M_gl_produk->exec_update_jenis_moving_produk($get_kode_kantor);
						}
						else
						{
							$dt_stock = $cek_dt_stock->DT_STOCK;
						}
					}
					else
					{
						$dt_stock = date('Y-m-d H:i:s');
					}
				//UPDATE STOCK DI tb_produk
				
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
					'ses_isModePst' => $data_login->isModePst,
					'ses_jenis_faktur' => $data_login->jenis_faktur,
					'ses_isDokter' => $data_login->isDokter,
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
					'ses_gnl_isPajakAktif' => $this->M_gl_pengaturan->get_data_by_id(15)->nilai,
					'ses_gnl_isJualStock0' => $this->M_gl_pengaturan->get_data_by_id(16)->nilai,
					'ses_gnl_tglStockProduk' => $dt_stock,
					'isLocal' => $isLocal,
				);
				
    			$this->session->set_userdata($user);
    			//redirect('index.php/admin','location');
				
				/*CEK HAK AKSES LEVEL 1*/
					$cek_hak_akses_lvl1 = $this->M_gl_hak_akses->cek_akses_jabatan_level1($data_login->id_jabatan,$data_login->kode_kantor);
					
					//$arr_akses = array();
					if(!empty($cek_hak_akses_lvl1))
					{
						$ses_akses_lvl1 = array
						(
							'ses_akses_lvl1_1' => $cek_hak_akses_lvl1->AKSES1
							,'ses_akses_lvl1_2' => $cek_hak_akses_lvl1->AKSES2
							,'ses_akses_lvl1_3' => $cek_hak_akses_lvl1->AKSES3
							,'ses_akses_lvl1_4' => $cek_hak_akses_lvl1->AKSES4
							,'ses_akses_lvl1_5' => $cek_hak_akses_lvl1->AKSES5
							,'ses_akses_lvl1_6' => $cek_hak_akses_lvl1->AKSES6
							,'ses_akses_lvl1_7' => $cek_hak_akses_lvl1->AKSES7
							,'ses_akses_lvl1_8' => $cek_hak_akses_lvl1->AKSES8
							,'ses_akses_lvl1_9' => $cek_hak_akses_lvl1->AKSES9
						);
						$this->session->set_userdata($ses_akses_lvl1);
					}
				/*CEK HAK AKSES LEVEL 1*/
				
				/*CEK HAK AKSES LEVEL 2*/
					$cek_hak_akses_lvl2 = $this->M_gl_hak_akses->cek_akses_jabatan_level2($data_login->id_jabatan,$data_login->kode_kantor);
					
					//$arr_akses = array();
					if(!empty($cek_hak_akses_lvl2))
					{
						$ses_akses_lvl2 = array
						(
							'ses_akses_lvl2_21' => $cek_hak_akses_lvl2->AKSES21
							,'ses_akses_lvl2_22' => $cek_hak_akses_lvl2->AKSES22
							,'ses_akses_lvl2_23' => $cek_hak_akses_lvl2->AKSES23
							,'ses_akses_lvl2_24' => $cek_hak_akses_lvl2->AKSES24
							,'ses_akses_lvl2_25' => $cek_hak_akses_lvl2->AKSES25
							,'ses_akses_lvl2_26' => $cek_hak_akses_lvl2->AKSES26
							,'ses_akses_lvl2_27' => $cek_hak_akses_lvl2->AKSES27
							,'ses_akses_lvl2_28' => $cek_hak_akses_lvl2->AKSES28
							,'ses_akses_lvl2_29' => $cek_hak_akses_lvl2->AKSES29
							,'ses_akses_lvl2_210' => $cek_hak_akses_lvl2->AKSES210
							
							,'ses_akses_lvl2_41' => $cek_hak_akses_lvl2->AKSES41
							,'ses_akses_lvl2_42' => $cek_hak_akses_lvl2->AKSES42
							,'ses_akses_lvl2_43' => $cek_hak_akses_lvl2->AKSES43
							,'ses_akses_lvl2_44' => $cek_hak_akses_lvl2->AKSES44
							,'ses_akses_lvl2_45' => $cek_hak_akses_lvl2->AKSES45
							
							,'ses_akses_lvl2_51' => $cek_hak_akses_lvl2->AKSES51
							,'ses_akses_lvl2_52' => $cek_hak_akses_lvl2->AKSES52
							,'ses_akses_lvl2_53' => $cek_hak_akses_lvl2->AKSES53
							,'ses_akses_lvl2_54' => $cek_hak_akses_lvl2->AKSES54
							,'ses_akses_lvl2_55' => $cek_hak_akses_lvl2->AKSES55
							,'ses_akses_lvl2_56' => $cek_hak_akses_lvl2->AKSES56
							,'ses_akses_lvl2_57' => $cek_hak_akses_lvl2->AKSES57
							,'ses_akses_lvl2_58' => $cek_hak_akses_lvl2->AKSES58
							,'ses_akses_lvl2_59' => $cek_hak_akses_lvl2->AKSES59
							,'ses_akses_lvl2_510' => $cek_hak_akses_lvl2->AKSES510
							,'ses_akses_lvl2_511' => $cek_hak_akses_lvl2->AKSES511
							,'ses_akses_lvl2_513' => $cek_hak_akses_lvl2->AKSES513
							,'ses_akses_lvl2_514' => $cek_hak_akses_lvl2->AKSES514
							,'ses_akses_lvl2_515' => $cek_hak_akses_lvl2->AKSES515
							
							,'ses_akses_lvl2_61' => $cek_hak_akses_lvl2->AKSES61
							,'ses_akses_lvl2_62' => $cek_hak_akses_lvl2->AKSES62
							
							,'ses_akses_lvl2_71' => $cek_hak_akses_lvl2->AKSES71
							,'ses_akses_lvl2_72' => $cek_hak_akses_lvl2->AKSES72
							
							,'ses_akses_lvl2_81' => $cek_hak_akses_lvl2->AKSES81
							,'ses_akses_lvl2_82' => $cek_hak_akses_lvl2->AKSES82
							,'ses_akses_lvl2_83' => $cek_hak_akses_lvl2->AKSES83
							,'ses_akses_lvl2_84' => $cek_hak_akses_lvl2->AKSES84
							,'ses_akses_lvl2_85' => $cek_hak_akses_lvl2->AKSES85
							
							,'ses_akses_lvl2_91' => $cek_hak_akses_lvl2->AKSES91
							
						);
						$this->session->set_userdata($ses_akses_lvl2);
					}
				/*CEK HAK AKSES LEVEL 2*/
				
				/*CEK HAK AKSES LEVEL 3*/
					$cek_hak_akses_lvl3 = $this->M_gl_hak_akses->cek_akses_jabatan_level3($data_login->id_jabatan,$data_login->kode_kantor);
					
					//$arr_akses = array();
					if(!empty($cek_hak_akses_lvl3))
					{
						$ses_akses_lvl3 = array
						(
							'ses_akses_lvl3_211' => $cek_hak_akses_lvl3->AKSES211
							,'ses_akses_lvl3_212' => $cek_hak_akses_lvl3->AKSES212
							,'ses_akses_lvl3_213' => $cek_hak_akses_lvl3->AKSES213
							,'ses_akses_lvl3_214' => $cek_hak_akses_lvl3->AKSES214
							,'ses_akses_lvl3_215' => $cek_hak_akses_lvl3->AKSES215
							,'ses_akses_lvl3_216' => $cek_hak_akses_lvl3->AKSES216
							,'ses_akses_lvl3_217' => $cek_hak_akses_lvl3->AKSES217
							,'ses_akses_lvl3_218' => $cek_hak_akses_lvl3->AKSES218
							,'ses_akses_lvl3_219' => $cek_hak_akses_lvl3->AKSES219
							
							,'ses_akses_lvl3_221' => $cek_hak_akses_lvl3->AKSES221
							,'ses_akses_lvl3_222' => $cek_hak_akses_lvl3->AKSES222
							,'ses_akses_lvl3_223' => $cek_hak_akses_lvl3->AKSES223
							
							,'ses_akses_lvl3_231' => $cek_hak_akses_lvl3->AKSES231
							,'ses_akses_lvl3_232' => $cek_hak_akses_lvl3->AKSES232
							
							,'ses_akses_lvl3_241' => $cek_hak_akses_lvl3->AKSES241
							,'ses_akses_lvl3_242' => $cek_hak_akses_lvl3->AKSES242
							
							,'ses_akses_lvl3_251' => $cek_hak_akses_lvl3->AKSES251
							,'ses_akses_lvl3_252' => $cek_hak_akses_lvl3->AKSES252
							
							,'ses_akses_lvl3_261' => $cek_hak_akses_lvl3->AKSES261
							,'ses_akses_lvl3_262' => $cek_hak_akses_lvl3->AKSES262
							,'ses_akses_lvl3_263' => $cek_hak_akses_lvl3->AKSES263
							
							,'ses_akses_lvl3_441' => $cek_hak_akses_lvl3->AKSES441
							,'ses_akses_lvl3_442' => $cek_hak_akses_lvl3->AKSES442
							
							,'ses_akses_lvl3_451' => $cek_hak_akses_lvl3->AKSES451
							,'ses_akses_lvl3_452' => $cek_hak_akses_lvl3->AKSES452
							,'ses_akses_lvl3_453' => $cek_hak_akses_lvl3->AKSES453
							,'ses_akses_lvl3_454' => $cek_hak_akses_lvl3->AKSES454
							,'ses_akses_lvl3_455' => $cek_hak_akses_lvl3->AKSES455
							
							,'ses_akses_lvl3_511' => $cek_hak_akses_lvl3->AKSES511
							,'ses_akses_lvl3_512' => $cek_hak_akses_lvl3->AKSES512
							
							,'ses_akses_lvl3_531' => $cek_hak_akses_lvl3->AKSES531
							,'ses_akses_lvl3_532' => $cek_hak_akses_lvl3->AKSES532
							,'ses_akses_lvl3_533' => $cek_hak_akses_lvl3->AKSES533
							,'ses_akses_lvl3_534' => $cek_hak_akses_lvl3->AKSES534
							,'ses_akses_lvl3_535' => $cek_hak_akses_lvl3->AKSES535
							
							,'ses_akses_lvl3_711' => $cek_hak_akses_lvl3->AKSES711
							,'ses_akses_lvl3_712' => $cek_hak_akses_lvl3->AKSES712
							,'ses_akses_lvl3_713' => $cek_hak_akses_lvl3->AKSES713
							,'ses_akses_lvl3_714' => $cek_hak_akses_lvl3->AKSES714
							,'ses_akses_lvl3_715' => $cek_hak_akses_lvl3->AKSES715
							,'ses_akses_lvl3_716' => $cek_hak_akses_lvl3->AKSES716
							,'ses_akses_lvl3_717' => $cek_hak_akses_lvl3->AKSES717
							,'ses_akses_lvl3_718' => $cek_hak_akses_lvl3->AKSES718
							,'ses_akses_lvl3_719' => $cek_hak_akses_lvl3->AKSES719
							,'ses_akses_lvl3_7110' => $cek_hak_akses_lvl3->AKSES7110
							,'ses_akses_lvl3_7111' => $cek_hak_akses_lvl3->AKSES7111
							,'ses_akses_lvl3_7112' => $cek_hak_akses_lvl3->AKSES7112
							,'ses_akses_lvl3_7113' => $cek_hak_akses_lvl3->AKSES7113
							
							,'ses_akses_lvl3_721' => $cek_hak_akses_lvl3->AKSES721
							,'ses_akses_lvl3_722' => $cek_hak_akses_lvl3->AKSES722
							,'ses_akses_lvl3_723' => $cek_hak_akses_lvl3->AKSES723
						);
						$this->session->set_userdata($ses_akses_lvl3);
					}
				/*CEK HAK AKSES LEVEL 3*/
				
				/*CEK HAK AKSES LEVEL 4*/
					$cek_hak_akses_lvl4 = $this->M_gl_hak_akses->cek_akses_jabatan_level4($data_login->id_jabatan,$data_login->kode_kantor);
					
					//$arr_akses = array();
					if(!empty($cek_hak_akses_lvl4))
					{
						$ses_akses_lvl4 = array
						(
							'ses_akses_lvl4_2182' => $cek_hak_akses_lvl4->AKSES2182
							,'ses_akses_lvl4_2183' => $cek_hak_akses_lvl4->AKSES2183
							,'ses_akses_lvl4_2184' => $cek_hak_akses_lvl4->AKSES2184
							
							,'ses_akses_lvl4_7164' => $cek_hak_akses_lvl4->AKSES7164
							
							,'ses_akses_lvl4_7212' => $cek_hak_akses_lvl4->AKSES7212
						);
						$this->session->set_userdata($ses_akses_lvl4);
					}
				/*CEK HAK AKSES LEVEL 4*/
				
				/*SET HARI DISKON*/
					$this->M_gl_pengaturan->update_set_0_h_diskon($data_login->kode_kantor);
					$this->M_gl_pengaturan->update_set_1_h_diskon($data_login->kode_kantor);
				/*SET HARI DISKON*/
				
				/*HAPUS LOG YANG KADALUARSA*/
					$this->M_gl_pengaturan->hapus_log_kadaluarsa($data_login->kode_kantor);
				/*HAPUS LOG YANG KADALUARSA*/
				
				
				header('Location: '.base_url().'gl-admin');
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
				
				header('Location: '.base_url().'gl-admin-login?kode_kantor='.$get_kode_kantor);
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