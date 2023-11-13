<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_halaman extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_halaman','M_menu'));
		
		
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
					$cari = "WHERE A.HAL_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND A.HAL_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
				}
				else
				{
					$cari = "WHERE A.HAL_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('admin-halaman?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-halaman/');
				$config['total_rows'] = $this->M_halaman->count_halaman_limit($cari)->JUMLAH;
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
				
				$list_menu = $this->M_menu->list_menu_not_halaman(
															" AND A.MENU_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' "
															);
				$list_halaman = $this->M_halaman->list_halaman_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				
				
				$data = array('page_content'=>'king_admin_halaman','halaman'=>$halaman,'list_halaman'=>$list_halaman,'list_menu' => $list_menu);
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
		if (!empty($_POST['stat_edit']))
		{					
			//$this->M_jabatan->edit($_POST['stat_edit'],$_POST['nama'],$_POST['ket'],$this->session->userdata('ses_id_karyawan'));
			
			$this->M_halaman->edit
			(
				$_POST['stat_edit'],
				$_POST['MENU_ID'],
				$_POST['HAL_KODE'],
				$_POST['HAL_NAMA'],
				str_replace("'","", str_replace('"','',$_POST['HAL_ISI'])),
				$_POST['MENU_KEYWORDS'],
				$_POST['MENU_DESC'],
				url_title($_POST['HAL_NAMA']), //$_POST['HAL_LINKTITLE'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-halaman');
			
			
		}
		else
		{
			//$this->M_jabatan->simpan($_POST['nama'],$_POST['ket'],$this->session->userdata('ses_id_karyawan'),$this->session->userdata('ses_kode_kantor'));
			$this->M_halaman->simpan
			(
				$_POST['MENU_ID'],
				$_POST['HAL_KODE'],
				$_POST['HAL_NAMA'],
				str_replace("'","", str_replace('"','',$_POST['HAL_ISI'])),
				$_POST['MENU_KEYWORDS'],
				$_POST['MENU_DESC'],
				url_title($_POST['HAL_NAMA']), //$_POST['HAL_LINKTITLE'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-halaman');
		}
		
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$HAL_ID = $this->uri->segment(2,0);
		$this->M_halaman->hapus($this->session->userdata('ses_kode_kantor'),$HAL_ID);
		header('Location: '.base_url().'admin-halaman');
	}
	
	function cek_halaman()
	{	
		$HAL_NAMA = $_POST['HAL_NAMA'];
		$hasil_cek = $this->M_halaman->get_halaman('HAL_NAMA',$HAL_NAMA);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */