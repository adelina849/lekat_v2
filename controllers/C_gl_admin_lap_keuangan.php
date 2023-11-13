<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_lap_keuangan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_lap_keuangan','M_gl_bank','M_gl_kode_akun'));
		
	}
	
	public function index()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				
				if((!empty($_GET['id_bank'])) && ($_GET['id_bank']!= "")  )
				//if((!empty($_GET['id_bank']))   )
				{
					$id_bank = $_GET['id_bank'];
				}
				else
				{
					$id_bank = "";
				}
				
				if((!empty($_GET['ref'])) && ($_GET['ref']!= "")  )
				{
					$ref = $_GET['ref'];
				}
				else
				{
					$ref = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = $_GET['cari'];
				}
				else
				{
					$cari = "";
				}
				
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
				
				
				//DATA BANK
				$cari_bank = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
				$list_bank = $this->M_gl_bank->list_bank_limit($cari_bank,100,0);
				//DATA BANK
				
				$list_lap_keuangan = $this->M_gl_lap_keuangan->list_lap_keuangan
									(
										$this->session->userdata('ses_kode_kantor')
										,$id_bank
										,$ref
										,$cari
										,$dari
										,$sampai
									);
									
				
				
				$msgbox_title = " Laporan Ikhtisar Keuangan";
				
				$data = array('page_content'=>'gl_admin_lap_keuangan','list_lap_keuangan'=>$list_lap_keuangan,'msgbox_title' => $msgbox_title,'list_bank' => $list_bank);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function jurnal()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				
				if((!empty($_GET['id_bank'])) && ($_GET['id_bank']!= "")  )
				//if((!empty($_GET['id_bank']))   )
				{
					$id_bank = $_GET['id_bank'];
				}
				else
				{
					$id_bank = "";
				}
				
				if((!empty($_GET['ref'])) && ($_GET['ref']!= "")  )
				{
					$ref = $_GET['ref'];
				}
				else
				{
					$ref = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = $_GET['cari'];
				}
				else
				{
					$cari = "";
				}
				
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
				
				
				//DATA BANK
				$cari_bank = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
				$list_bank = $this->M_gl_bank->list_bank_limit($cari_bank,100,0);
				//$list_bank = false;
				//DATA BANK
				
				//GET KODE AKUN KHUSUS
				/*
				$get_kode_akun_pendapatan = $this->M_gl_kode_akun->get_kode_akun('target','PENDAPATAN');
				if(!empty($get_kode_akun_pendapatan))
				{
					$get_kode_akun_pendapatan = $get_kode_akun_pendapatan->row();
					$get_kode_akun_pendapatan = $get_kode_akun_pendapatan->kode_akun;
				}
				else
				{
					$get_kode_akun_pendapatan = '';
				}
				
				$get_kode_akun_cashintransit = $this->M_gl_kode_akun->get_kode_akun('target','CASHIN-TRANSIT');
				if(!empty($get_kode_akun_cashintransit))
				{
					$get_kode_akun_cashintransit = $get_kode_akun_cashintransit->row();
					$get_kode_akun_cashintransit = $get_kode_akun_cashintransit->kode_akun;
				}
				else
				{
					$get_kode_akun_cashintransit = '';
				}
				*/
				//GET KODE AKUN KHUSUS
				
				$list_lap_keuangan = $this->M_gl_lap_keuangan->list_lap_keuangan
									(
										$this->session->userdata('ses_kode_kantor')
										,$id_bank
										,$ref
										,$cari
										,$dari
										,$sampai
									);
				
				//$list_lap_keuangan = false;
									
				
				
				$msgbox_title = " Jurnal Umum";
				
				$data = array('page_content'=>'gl_admin_lap_jurnal','list_lap_keuangan'=>$list_lap_keuangan,'msgbox_title' => $msgbox_title,'list_bank' => $list_bank
				//,'get_kode_akun_pendapatan' => $get_kode_akun_pendapatan
				//,'get_kode_akun_cashintransit' => $get_kode_akun_cashintransit
				);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function excel_jurnal()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = "";
				}
				
				if((!empty($_GET['id_bank'])) && ($_GET['id_bank']!= "")  )
				//if((!empty($_GET['id_bank']))   )
				{
					$id_bank = $_GET['id_bank'];
				}
				else
				{
					$id_bank = "";
				}
				
				if((!empty($_GET['ref'])) && ($_GET['ref']!= "")  )
				{
					$ref = $_GET['ref'];
				}
				else
				{
					$ref = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = $_GET['cari'];
				}
				else
				{
					$cari = "";
				}
				
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
				
				$list_lap_keuangan = $this->M_gl_lap_keuangan->list_lap_keuangan
									(
										$kode_kantor
										,$id_bank
										,$ref
										,$cari
										,$dari
										,$sampai
									);
									
				$data = array('page_content'=>'gl_admin_excel_jurnal','list_lap_keuangan' => $list_lap_keuangan);
				$this->load->view('admin/page/gl_admin_excel_jurnal.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}



}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_lap_keuangan.php */