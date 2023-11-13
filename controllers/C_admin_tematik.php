<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_tematik extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_tematik'));
		
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
					$cari = "WHERE TEMA_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND TEMA_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
				}
				else
				{
					$cari = "WHERE TEMA_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('admin-tematik?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-tematik/');
				$config['total_rows'] = $this->M_tematik->count_tematik_limit($cari)->JUMLAH;
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
				
				$list_tematik = $this->M_tematik->list_tematik_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$data = array('page_content'=>'king_admin_tematik','halaman'=>$halaman,'list_tematik'=>$list_tematik);
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
			$this->M_tematik->edit
			(
				$_POST['stat_edit'],
				'',//$_POST['TEMA_KODE'],
				$_POST['TEMA_NAMA'],
				$_POST['TEMA_KET'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-tematik');
			
			
		}
		else
		{
			$this->M_tematik->simpan
			(
				'',//$_POST['TEMA_KODE'],
				$_POST['TEMA_NAMA'],
				$_POST['TEMA_KET'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-tematik');
		}
		
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$TEMA_ID = $this->uri->segment(2,0);
		$this->M_tematik->hapus($this->session->userdata('ses_kode_kantor'),$TEMA_ID);
		
			$this->M_log->simpan(
				"DELETE",
				$this->session->userdata('ses_nama_karyawan'),
				"Melakukan penghapusan kategori pos dengan id : ".$TEMA_ID." ",
				$this->session->userdata('ses_kode_kantor')
			);
		header('Location: '.base_url().'admin-tematik');
	}
	
	function cek_tematik()
	{	
		$TEMA_NAMA = $_POST['TEMA_NAMA'];
		$hasil_cek = $this->M_tematik->get_tematik('TEMA_NAMA',$TEMA_NAMA);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */