<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_jadwal_sholat extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_jadwal_sholat'));
		
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
					$cari = "WHERE JSHT_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND JSHT_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
				}
				else
				{
					$cari = "WHERE JSHT_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('admin-jadwal-sholat?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-jadwal-sholat/');
				$config['total_rows'] = $this->M_jadwal_sholat->count_jadwal_sholat_limit($cari)->JUMLAH;
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
				
				$list_jadwal_sholat = $this->M_jadwal_sholat->list_jadwal_sholat_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$data = array('page_content'=>'king_admin_jadwal_sholat','halaman'=>$halaman,'list_jadwal_sholat'=>$list_jadwal_sholat);
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
			$this->M_jadwal_sholat->edit
			(
				$_POST['stat_edit'],
				$_POST['JSHT_DT'],
				$_POST['JSHT_IMSAK'],
				$_POST['JSHT_SUBUH'],
				$_POST['JSHT_TERBIT'],
				$_POST['JSHT_DZUHUR'],
				$_POST['JSHT_ASHAR'],
				$_POST['JSHT_MAGRIB'],
				$_POST['JSHT_ISYA'],
				$_POST['JSHT_KET'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-jadwal-sholat');
			
			
		}
		else
		{
			$this->M_jadwal_sholat->simpan
			(
				$_POST['JSHT_DT'],
				$_POST['JSHT_IMSAK'],
				$_POST['JSHT_SUBUH'],
				$_POST['JSHT_TERBIT'],
				$_POST['JSHT_DZUHUR'],
				$_POST['JSHT_ASHAR'],
				$_POST['JSHT_MAGRIB'],
				$_POST['JSHT_ISYA'],
				$_POST['JSHT_KET'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-jadwal-sholat');
		}
		
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$JSHT_ID = $this->uri->segment(2,0);
		$this->M_jadwal_sholat->hapus($this->session->userdata('ses_kode_kantor'),$JSHT_ID);
		
			$this->M_log->simpan(
				"DELETE",
				$this->session->userdata('ses_nama_karyawan'),
				"Melakukan penghapusan jadwal sholat dengan id : ".$JSHT_ID." ",
				$this->session->userdata('ses_kode_kantor')
			);
		header('Location: '.base_url().'admin-jadwal-sholat');
	}
	
	function cek_jadwal_sholat()
	{	
		$JSHT_NAMA = $_POST['JSHT_NAMA'];
		$hasil_cek = $this->M_jadwal_sholat->get_jadwal_sholat('JSHT_NAMA',$JSHT_NAMA);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */