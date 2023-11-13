<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_public_jadwal_sholat extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_jadwal_sholat'));
	}
	
	public function index()
	{
			
		if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
		{
			$cari = "WHERE JSHT_DT LIKE '%".str_replace("'","",$_GET['cari'])."%'";
		}
		else
		{
			$cari = "";
		}
		
		$JUMLAH_RECORD = $this->M_jadwal_sholat->count_jadwal_sholat_limit($cari);
		$this->load->library('pagination');
		$config['first_url'] = site_url('jadwal-sholat?'.http_build_query($_GET));
		$config['base_url'] = site_url('jadwal-sholat');
		$config['total_rows'] = $JUMLAH_RECORD->JUMLAH;
		$config['uri_segment'] = 4;	
		$config['per_page'] = 30;
		$config['num_links'] = 2;
		$config['suffix'] = '?' . http_build_query($_GET, '', "&");
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
		
		$getJadwalSholatToday = $this->M_jadwal_sholat->getJadwalSholatToday();
		$list_jadwal_sholat = $this->M_jadwal_sholat->list_jadwal_sholat_limit($cari,$config['per_page'],$this->uri->segment(4,0));
		
		$data = array('page_content'=>'list_jadwal_sholat','halaman'=>$halaman,'list_jadwal_sholat'=>$list_jadwal_sholat,'getJadwalSholatToday' => $getJadwalSholatToday);
		$this->load->view('public/container.html',$data);
				
	}
	
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_admin_jadwal_sholat.php */