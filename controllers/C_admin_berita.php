<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_berita extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_berita','M_kat_berita','M_images'));
		
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
					$cari = "WHERE A.BRT_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND A.BRT_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
				}
				else
				{
					$cari = "WHERE A.BRT_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('admin-berita?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-berita/');
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
				
				$list_kat_berita = $this->M_kat_berita->list_kat_berita_limit(
										" WHERE KBRT_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'"
										,100
										,0
										);
				$list_berita = $this->M_berita->list_berita_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$data = array('page_content'=>'king_admin_berita','halaman'=>$halaman,'list_berita'=>$list_berita,'list_kat_berita' => $list_kat_berita);
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
			$this->M_berita->edit
			(
			
				$_POST['stat_edit'],
				$_POST['KBRT_ID'],
				date('ymdHis'), //$_POST['BRT_KODE'],
				$_POST['BRT_NAMA'],
				$_POST['BRT_DTWRITE'],
				str_replace("'","", str_replace('"','',$_POST['BRT_ISI'])),
				$_POST['BRT_KEYWORDS'],
				$_POST['BRT_DESC'],
				url_title($_POST['BRT_NAMA']), //$BRT_LINKTITLE,
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-berita');
			
			
		}
		else
		{
			$this->M_berita->simpan
			(
				$_POST['KBRT_ID'],
				date('ymdHis'), //$_POST['BRT_KODE'],
				$_POST['BRT_NAMA'],
				$_POST['BRT_DTWRITE'],
				str_replace("'","", str_replace('"','',$_POST['BRT_ISI'])),
				$_POST['BRT_KEYWORDS'],
				$_POST['BRT_DESC'],
				url_title($_POST['BRT_NAMA']), //$BRT_LINKTITLE,
				$this->session->userdata('ses_nama_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-berita');
		}
		
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$BRT_ID = $this->uri->segment(2,0);
		$this->M_berita->hapus($this->session->userdata('ses_kode_kantor'),$BRT_ID);
		
			$this->M_log->simpan(
				"DELETE",
				$this->session->userdata('ses_nama_karyawan'),
				"Melakukan penghapusan berita dengan id : ".$BRT_ID." ",
				$this->session->userdata('ses_kode_kantor')
			);
		
		$cari = "WHERE IMG_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
									AND IMG_GRUP = 'berita'
									AND ID = '".$BRT_ID."'";
		$list_images = $this->M_images->list_images_limit($cari,1000,0);
		
		if(!empty($list_images))
		{
			$list_result = $list_images->result();
			foreach($list_result as $row)
			{
				//HAPUS GAMBAR
				$this->M_images->do_upload('',$row->IMG_FILE);
				
				//HAPUS DATA GAMBAR
				$this->M_images->hapus($row->IMG_ID);
			}
		}
		
		
		header('Location: '.base_url().'admin-berita');
	}
	
	function cek_berita()
	{	
		$BRT_NAMA = $_POST['BRT_NAMA'];
		$hasil_cek = $this->M_berita->get_berita('BRT_NAMA',$BRT_NAMA);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */