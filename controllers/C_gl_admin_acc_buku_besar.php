<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_acc_buku_besar extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_acc_buku_besar','M_gl_kode_akun'));
		
	}
	
	function index()
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
				
				if((!empty($_GET['kode_akun'])) && ($_GET['kode_akun']!= "")  )
				{
					$cari = $_GET['kode_akun'];
				}
				else
				{
					$cari = "";
				}
				
				
				if((!empty($_GET['target'])) && ($_GET['target']!= "")  )
				{
					$target = $_GET['target'];
				}
				else
				{
					$target = "";
				}
				
				
				/*
				if($target == "KAS")
				{
					
					$cari_kode_akun = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND target = 'KAS'";
					$get_kode_akun = $this->M_gl_kode_akun->get_kode_akun_by_cari($cari_kode_akun);
					if(!empty($get_kode_akun))
					{
					$get_kode_akun = $get_kode_akun->row();
				*/
					$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar_kas_tambah_saldo_awal($this->session->userdata('ses_kode_kantor'),$dari,$sampai,$cari);
				/*	
					}
					else
					{
						$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar($this->session->userdata('ses_kode_kantor'),$dari,$sampai,$cari);
					}
					
					//$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar_kas($this->session->userdata('ses_kode_kantor'),$dari,$sampai,$cari);
				}
				else
				{
					$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar($this->session->userdata('ses_kode_kantor'),$dari,$sampai,$cari);
				}
				*/
				
				
				
				$msgbox_title = " Laporan Buku Besar ";
				
				$cari_coa = " 
							WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
						";
				
				
				$order_by_coa = " ORDER BY nama_kode_akun ASC ";
				$list_kode_akun = $this->M_gl_kode_akun->list_kode_akun_limit_order_by($cari_coa,$order_by_coa,1000,0);
				
				$get_kode_akun = $this->M_gl_kode_akun->get_kode_akun('kode_akun',$cari);
				
				$data = array(
					'page_content'=>'gl_admin_lap_acc_buku_besar'
					,'msgbox_title' => $msgbox_title
					,'list_acc_buku_besar' => $list_acc_buku_besar,'list_kode_akun' => $list_kode_akun
					,'get_kode_akun' => $get_kode_akun
					,'target' => $target
					);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function laporan_keuangan_saldo()
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
				
				if((!empty($_GET['kode_akun'])) && ($_GET['kode_akun']!= "")  )
				{
					$cari = $_GET['kode_akun'];
				}
				else
				{
					$cari = "";
				}
				
				$get_id_kode_akun = $this->M_gl_acc_buku_besar->get_id_kode_akun($this->session->userdata('ses_kode_kantor'),$cari);
				if(!empty($get_id_kode_akun))
				{
					$get_id_kode_akun = $get_id_kode_akun->row();
					$id_kode_akun = $get_id_kode_akun->id_kode_akun;
				}
				else
				{
					$id_kode_akun = "";
				}
				
				$saldo_awal = 0;
				$last_saldo_awal_array = $this->M_gl_acc_buku_besar->row_last_saldo_awal_uang_masuk($this->session->userdata('ses_kode_kantor'),$cari,$dari);
				if(!empty($last_saldo_awal_array))
				{
					//$last_saldo_awal = $last_saldo_awal->row();
					$last_saldo_awal = $last_saldo_awal_array->last_saldo_uang_masuk;
					
					$saldo_awal = (int)$last_saldo_awal_array->nominal;
					//$saldo_awal = 500000;
				}
				else
				{
					$last_saldo_awal = '1900-01-01';
					$saldo_awal = 0;
				}
				
				$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar_laporan_keuangan_saldo($this->session->userdata('ses_kode_kantor'),$dari,$sampai,$cari,$last_saldo_awal,$saldo_awal);
				//$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar_laporan_keuangan_saldo($this->session->userdata('ses_kode_kantor'),$dari,$sampai,$cari);
				
				
				$msgbox_title = " Laporan Saldo Keuangan ";
				
				$cari_coa = " 
							WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							-- AND target IN ('KAS','BANK','CASHIN-TRANSIT')
						";
				
				
				$order_by_coa = " ORDER BY nama_kode_akun ASC ";
				$list_kode_akun = $this->M_gl_kode_akun->list_kode_akun_limit_order_by($cari_coa,$order_by_coa,1000,0);
				
				$get_kode_akun = $this->M_gl_kode_akun->get_kode_akun('kode_akun',$cari);
				
				$data = array('page_content'=>'gl_admin_lap_acc_laporan_keuangan_saldo','msgbox_title' => $msgbox_title,'list_acc_buku_besar' => $list_acc_buku_besar,'list_kode_akun' => $list_kode_akun,'get_kode_akun' => $get_kode_akun,'last_saldo_awal' => $last_saldo_awal,'saldo_awal' => $saldo_awal);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function excel_buku_besar()
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
				
				if((!empty($_GET['kode_akun'])) && ($_GET['kode_akun']!= "")  )
				{
					$cari = $_GET['kode_akun'];
				}
				else
				{
					$cari = "";
				}
				
				//$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar($kode_kantor,$dari,$sampai,$cari);
				$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar_kas($this->session->userdata('ses_kode_kantor'),$dari,$sampai,$cari);
									
				$data = array('page_content'=>'gl_admin_excel_buku_besar','list_acc_buku_besar' => $list_acc_buku_besar);
				$this->load->view('admin/page/gl_admin_excel_buku_besar.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	function excel_laporan_keuangan()
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
				
				if((!empty($_GET['kode_akun'])) && ($_GET['kode_akun']!= "")  )
				{
					$cari = $_GET['kode_akun'];
				}
				else
				{
					$cari = "";
				}
				
				$get_id_kode_akun = $this->M_gl_acc_buku_besar->get_id_kode_akun($this->session->userdata('ses_kode_kantor'),$cari);
				if(!empty($get_id_kode_akun))
				{
					$get_id_kode_akun = $get_id_kode_akun->row();
					$id_kode_akun = $get_id_kode_akun->id_kode_akun;
				}
				else
				{
					$id_kode_akun = "";
				}
				
				$saldo_awal = 0;
				$last_saldo_awal_array = $this->M_gl_acc_buku_besar->row_last_saldo_awal_uang_masuk($this->session->userdata('ses_kode_kantor'),$cari,$dari);
				if(!empty($last_saldo_awal_array))
				{
					//$last_saldo_awal = $last_saldo_awal->row();
					$last_saldo_awal = $last_saldo_awal_array->last_saldo_uang_masuk;
					
					$saldo_awal = (int)$last_saldo_awal_array->nominal;
					//$saldo_awal = 500000;
				}
				else
				{
					$last_saldo_awal = '1900-01-01';
					$saldo_awal = 0;
				}
				
				$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar_laporan_keuangan_saldo($this->session->userdata('ses_kode_kantor'),$dari,$sampai,$cari,$last_saldo_awal,$saldo_awal);
				//$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar_laporan_keuangan_saldo($this->session->userdata('ses_kode_kantor'),$dari,$sampai,$cari);
				
				
									
				$data = array('page_content'=>'gl_admin_excel_lap_acc_keuangan','list_acc_buku_besar' => $list_acc_buku_besar);
				$this->load->view('admin/page/gl_admin_excel_lap_acc_keuangan.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */

