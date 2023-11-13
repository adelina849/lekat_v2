<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_public_artikel extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_artikel','M_kat_artikel','M_images'));
		
	}
	
	public function index()
	{
		
				// $data = array('page_content'=>'king_jabatan');
				// $this->load->view('admin/container',$data);
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE A.ART_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
				}
				else
				{
					$cari = "";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('artikel?'.http_build_query($_GET));
				$config['base_url'] = site_url('artikel/');
				$config['total_rows'] = $this->M_artikel->count_artikel_limit($cari)->JUMLAH;
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
				
				
				$list_artikel = $this->M_artikel->list_artikel_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$data = array('page_content'=>'list_artikel','halaman'=>$halaman,'list_artikel'=>$list_artikel);
				$this->load->view('public/container.html',$data);
			
	}
	
	function detail_artikel()
	{
		$ART_LINKTITLE = $this->uri->segment(4,0);
		//echo $ART_TITLE;
		$data_artikel = $this->M_artikel->list_artikel_limit(" WHERE A.ART_LINKTITLE = '".$ART_LINKTITLE."' ",1,0);
		if(!empty($data_artikel))
		{
			$data_artikel = $data_artikel->row();
			$data = array('page_content'=>'detail_artikel','desc'=> word_limiter($data_artikel->ART_ISI,25),'data_artikel'=>$data_artikel,'title'=> "BAZNAS Kab. Sukabumi | ".$data_artikel->ART_NAMA,'sidebar'=>1,'images_icon' => $data_artikel->IMG_LINK);
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