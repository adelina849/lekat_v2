<?php

	Class C_admin_persen_laporan Extends CI_Controller
	{
		public function __construct()
		{
			parent::__construct();
			$this->load->model(array('M_laporan','M_kec','M_dash'));
		}
		
		function index()
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
					$KEC_ID = $this->uri->segment(2,0);
					
					$data_kecamatan = $this->M_kec->get_Kecamatan('KEC_ID',$KEC_ID)->row();
					$list_persen_laporan_perkecamatan = $this->M_laporan->list_persen_laporan_perkecamatan($KEC_ID,'');
					$data = array('page_content'=>'ptn_admin_persen_laporan_kecamatan','data_kecamatan' => $data_kecamatan,'list_persen_laporan_perkecamatan' => $list_persen_laporan_perkecamatan);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'admin-login');
				}
			}
		}
		
		function ubah_catatan_buat_laporan()
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
					$this->M_laporan->ubah_catatan_buat_laporan($_POST['KEC_ID'],$_POST['LAP_ID'],$_POST['BLAP_PERIODE'],$_POST['BLAP_CATATAN']);
					echo'BERHASIL';
				}
				else
				{
					header('Location: '.base_url().'admin-login');
				}
			}
		}
		
		function arsip_laporan()
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
					$KEC_ID = $this->uri->segment(2,0);
					$KAT_PERIODE = $_GET['kategori'];
					$PERIODE = $_GET['periode'];
					
					$data_kecamatan = $this->M_kec->get_Kecamatan('KEC_ID',$KEC_ID)->row();
					$list_persen_laporan_perkecamatan = $this->M_laporan->list_arsip_persen_laporan_perkecamatan($KEC_ID,'',$KAT_PERIODE,$PERIODE);
					$data = array('page_content'=>'ptn_admin_persen_laporan_kecamatan','data_kecamatan' => $data_kecamatan,'list_persen_laporan_perkecamatan' => $list_persen_laporan_perkecamatan);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'admin-login');
				}
			}
		}
	
		function list_catatan_laporan()
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
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = str_replace("'","",$_GET['cari']);
					}
					else
					{
						$cari = "";
					}
					
					$list_catatan_laporan = $this->M_akun->list_catatan_laporan($cari);
					$data = array('page_content'=>'ptn_admin_catatan_laporan','list_catatan_laporan' => $list_catatan_laporan);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'admin-login');
				}
			}
		}
		
		function list_rangking_kecamatan()
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
					if((!empty($_GET['periode'])) && ($_GET['periode']!= "")  )
					{
						$periode = str_replace("'","",$_GET['periode']);
					}
					else
					{
						$periode = "";
					}
					
					if((!empty($_GET['kec_nama'])) && ($_GET['kec_nama']!= "")  )
					{
						$kec_nama = str_replace("'","",$_GET['kec_nama']);
					}
					else
					{
						$kec_nama = "";
					}
					
					$cari = " WHERE PERIODE = '".$periode."' AND KEC_NAMA LIKE '%".$kec_nama."%'";
					
					$list_rangking = $this->M_dash->list_rangking_from_table($cari);
					$data = array('page_content'=>'ptn_admin_list_rangking','list_rangking' => $list_rangking);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'admin-login');
				}
			}
		}
		
		function export_rangking()
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
					$this->M_dash->exec_export_rangking_kecamatan('',$_POST['PERIODE']);
					echo"BERHASIL";
				}
				else
				{
					header('Location: '.base_url().'admin-login');
				}
			}
		}
		
		function ubah_rangking()
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
					$this->M_dash->ubah_rangking($_POST['NO_RANGKING'],$_POST['KEC_ID'],$_POST['PERIODE']);
					echo"BERHASIL";
				}
				else
				{
					header('Location: '.base_url().'admin-login');
				}
			}
		}
		
		function akumulasi_rangking()
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
					if((!empty($_GET['dari'])) && ($_GET['dari']!= "")  )
					{
						$dari = $_GET['dari'];
						$sampai = $_GET['sampai'];
					}
					else
					{
						$dari = date("Y-m-d");
						$sampai = date("Y-m-d");
					}
					/*
					echo $dari;
					echo $sampai
					*/
					$list_akumulasi_rangking = $this->M_dash->akumulasi_rangking($dari,$sampai);
					$data = array('page_content'=>'ptn_admin_list_akumulasi_rangking','list_akumulasi_rangking' => $list_akumulasi_rangking);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'admin-login');
				}
			}
		}
	}

?>