<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_lap_periode extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_periode','M_dash'));
		
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
					$cari = "WHERE (PER_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%' OR PER_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = "";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				$config['first_url'] = site_url('laporan-periode?'.http_build_query($_GET));
				$config['base_url'] = site_url('laporan-periode/');
				$config['total_rows'] = $this->M_periode->count_periode_limit($cari)->JUMLAH;
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
				$list_periode = $this->M_periode->list_periode_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				$data = array('page_content'=>'ptn_admin_lap_periode','halaman'=>$halaman,'list_periode'=>$list_periode);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
	
	
	public function detail()
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
				/*$list_periode_aktif = $this->M_periode->list_periode_limit(' WHERE NOW() >= PER_DARI  AND NOW() <= PER_SAMPAI',100,0);
				$list_persen_laporan_kecamatan = $this->M_dash->list_persen_laporan_kecamatan('');
				$data = array('page_content'=>'admin_dashboard','list_persen_laporan_kecamatan'=>$list_persen_laporan_kecamatan,'list_periode_aktif' => $list_periode_aktif);
				$this->load->view('admin/container',$data);
				//echo "Hallo World";*/
				
				$KAT_PERIODE = $_GET['kategori'];
				$PERIODE = $_GET['periode'];
				
				$list_periode_aktif = $this->M_periode->list_periode_limit(" WHERE PER_KATEGORI = '".$KAT_PERIODE."'  AND PER_KODE = '".$PERIODE."'",100,0);
				$list_persen_laporan_kecamatan = $this->M_dash->list_arsip_persen_laporan_kecamatan('',$KAT_PERIODE,$PERIODE);
				$data = array('page_content'=>'king_admin_arsip_laporan','list_persen_laporan_kecamatan'=>$list_persen_laporan_kecamatan,'list_periode_aktif' => $list_periode_aktif);
				$this->load->view('admin/container',$data);
				//echo "Hallo World";
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
	
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */