<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_public_berita extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_berita','M_kat_berita','M_images'));
		
	}
	
	public function index()
	{
		
			
		// $data = array('page_content'=>'king_jabatan');
		// $this->load->view('admin/container',$data);
		if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
		{
			$cari = "WHERE A.BRT_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
		}
		else
		{
			$cari = "";
		}
		
		$this->load->library('pagination');
		//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
		//$config['base_url'] = base_url().'admin/jabatan/';
		
		$config['first_url'] = site_url('berita-kegiatan?'.http_build_query($_GET));
		$config['base_url'] = site_url('berita-kegiatan/');
		$config['total_rows'] = $this->M_berita->count_berita_limit($cari)->JUMLAH;
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
		
		$list_berita = $this->M_berita->list_berita_limit($cari,$config['per_page'],$this->uri->segment(2,0));
		$title = "SISTEM TERINTEGRASI BAZNAS SUKABUMI | Artikel BAZNAS";
		
		$data = array('page_content'=>'list_berita','halaman'=>$halaman,'list_berita'=>$list_berita,'title'=>$title);
		//$this->load->view('admin/container',$data);
		$this->load->view('public/container.html',$data);
			
		
	}
	
	function detail_berita()
	{
		$BRT_LINKTITLE = $this->uri->segment(3,0);
		$data_berita = $this->M_berita->list_berita_limit(" WHERE A.BRT_LINKTITLE = '".$BRT_LINKTITLE."' ",1,0);
		if(!empty($data_berita))
		{
			$data_berita = $data_berita->row();
			$data = array('page_content'=>'detail_berita','desc'=> word_limiter($data_berita->BRT_ISI,25),'data_berita'=>$data_berita,'title'=> "BAZNAS Kab. Sukabumi | ".$data_berita->BRT_NAMA,'sidebar'=>1,'images_icon' => $data_berita->IMG_LINK);
			$this->load->view('public/container.html',$data);
		}
		else
		{
			header('Location: '.base_url());
		}
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */