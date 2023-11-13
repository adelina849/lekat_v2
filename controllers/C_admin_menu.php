<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_menu extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_menu'));
		
	}
	
	public function index()
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
				// $data = array('page_content'=>'king_jabatan');
				// $this->load->view('admin/container',$data);
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE A.MENU_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND A.MENU_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'
							-- AND A.MENU_ISMAINMENU = 1
							";
				}
				else
				{
					$cari = "
								WHERE 
								A.MENU_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'
								-- AND A.MENU_ISMAINMENU = 1
							";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('admin-menu?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-menu/');
				$config['total_rows'] = $this->M_menu->count_menu_limit($cari)->JUMLAH;
				$config['uri_segment'] = 2;	
				$config['per_page'] = 30;
				$config['num_links'] = 2;
				$config['suffix'] = '?' . http_build_query($_GET, '', "&");
				//$config['use_page_numbers'] = TRUE;
				//$config['page_query_string'] = false;
				//$config['query_string_segment'] = '';
				$config['first_page'] = 'Awal';
				$config['last_page'] = 'Akhir';
				$config['next_page'] = '&laquo;';
				$config['prev_page'] = '&raquo;';
				
				
				$config['full_tag_open'] = '<div><ul class="pagination">';
				$config['full_tag_close'] = '</ul></div>';
				$config['first_link'] = '&laquo; First';
				$config['first_tag_open'] = '<li class="prev page">';
				$config['first_tag_close'] = '</li>';
				$config['last_link'] = 'Last &raquo;';
				$config['last_tag_open'] = '<li class="next page">';
				$config['last_tag_close'] = '</li>';
				$config['next_link'] = 'Next &rarr;';
				$config['next_tag_open'] = '<li class="next page">';
				$config['next_tag_close'] = '</li>';
				$config['prev_link'] = '&larr; Previous';
				$config['prev_tag_open'] = '<li class="prev page">';
				$config['prev_tag_close'] = '</li>';
				$config['cur_tag_open'] = '<li class="active"><a href="">';
				$config['cur_tag_close'] = '</a></li>';
				$config['num_tag_open'] = '<li class="page">';
				$config['num_tag_close'] = '</li>';
				
				
				//inisialisasi config
				$this->pagination->initialize($config);
				$halaman = $this->pagination->create_links();
				$list_menu_utama = $this->M_menu->list_menu_limit(
											"WHERE A.MENU_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' AND A.MENU_ISMAINMENU = '1'"
											,100
											,0
											);
				
				$list_menu = $this->M_menu->list_menu_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				
				
				$data = array('page_content'=>'king_admin_menu','halaman'=>$halaman,'list_menu'=>$list_menu,'list_menu_utama' => $list_menu_utama);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
	
	public function simpan()
	{
		//echo $_POST['MENU_ISPUNYAHAL'];
		
		if(!empty($_POST['MENU_ISPUNYAHAL']))
		{
			$MENU_ISPUNYAHAL = $_POST['MENU_ISPUNYAHAL'];
		}
		else
		{
			$MENU_ISPUNYAHAL = 0;
		}
		
		if(!empty($_POST['MENU_ISMAINMENU']))
		{
			$MENU_ISMAINMENU = $_POST['MENU_ISMAINMENU'];
		}
		else
		{
			$MENU_ISMAINMENU = 0;
		}
		
		if (!empty($_POST['stat_edit']))
		{					
			//$this->M_jabatan->edit($_POST['stat_edit'],$_POST['nama'],$_POST['ket'],$this->session->userdata('ses_id_karyawan'));
			
			$this->M_menu->edit
			(
				$_POST['stat_edit'],
				$_POST['MENU_MAINID'],
				$_POST['MENU_KAT'],
				$MENU_ISMAINMENU,
				$_POST['MENU_KODE'],
				$_POST['MENU_NAMA'],
				'0',//$_POST['MENU_LEVEL'],
				$_POST['MENU_ORDER'],
				$MENU_ISPUNYAHAL,
				$_POST['MENU_LINK'],
				$_POST['MENU_KET'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-menu');
			
			
		}
		else
		{
			//$this->M_jabatan->simpan($_POST['nama'],$_POST['ket'],$this->session->userdata('ses_id_karyawan'),$this->session->userdata('ses_kode_kantor'));
			$this->M_menu->simpan
			(
				$_POST['MENU_MAINID'],
				$_POST['MENU_KAT'],
				$MENU_ISMAINMENU,
				$_POST['MENU_KODE'],
				$_POST['MENU_NAMA'],
				'0',//$_POST['MENU_LEVEL'],
				$_POST['MENU_ORDER'],
				$MENU_ISPUNYAHAL,
				$_POST['MENU_LINK'],
				$_POST['MENU_KET'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-menu');
		}
		
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$MENU_ID = $this->uri->segment(2,0);
		$data_menu = $this->M_menu->get_menu('MENU_ID',$MENU_ID);
		
		if(!empty($data_menu))
		{
			$this->M_menu->hapus($this->session->userdata('ses_kode_kantor'),$MENU_ID);
			
			$this->M_log->simpan(
				"DELETE",
				$this->session->userdata('ses_nama_karyawan'),
				"Melakukan penghapusan menu ".$data_menu->MENU_NAMA." dengan id : ".$MENU_ID." ",
				$this->session->userdata('ses_kode_kantor')
			);
		}
		header('Location: '.base_url().'admin-menu');
	}
	
	function cek_menu()
	{	
		$MENU_KODE = $_POST['MENU_KODE'];
		$hasil_cek = $this->M_menu->get_menu('MENU_KODE',$MENU_KODE);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */