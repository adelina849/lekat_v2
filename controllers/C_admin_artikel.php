<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_artikel extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_artikel','M_kat_artikel','M_images'));
		
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
					$cari = "WHERE A.ART_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND A.ART_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
				}
				else
				{
					$cari = "WHERE A.ART_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('admin-artikel?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-artikel/');
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
				
				$list_kat_artikel = $this->M_kat_artikel->list_kat_artikel_limit(
										" WHERE KART_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'"
										,100
										,0
										);
				$list_artikel = $this->M_artikel->list_artikel_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$data = array('page_content'=>'king_admin_artikel','halaman'=>$halaman,'list_artikel'=>$list_artikel,'list_kat_artikel' => $list_kat_artikel);
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
			$this->M_artikel->edit
			(
			
				$_POST['stat_edit'],
				$_POST['KART_ID'],
				date('ymdHis'), //$_POST['ART_KODE'],
				$_POST['ART_NAMA'],
				$_POST['ART_DTWRITE'],
				str_replace("'","", str_replace('"','',$_POST['ART_ISI'])),
				$_POST['ART_KEYWORDS'],
				$_POST['ART_DESC'],
				url_title($_POST['ART_NAMA']), //$ART_LINKTITLE,
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-artikel');
			
			
		}
		else
		{
			$this->M_artikel->simpan
			(
				$_POST['KART_ID'],
				date('ymdHis'), //$_POST['ART_KODE'],
				$_POST['ART_NAMA'],
				$_POST['ART_DTWRITE'],
				str_replace("'","", str_replace('"','',$_POST['ART_ISI'])),
				$_POST['ART_KEYWORDS'],
				$_POST['ART_DESC'],
				url_title($_POST['ART_NAMA']), //$ART_LINKTITLE,
				$this->session->userdata('ses_nama_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-artikel');
		}
		
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$ART_ID = $this->uri->segment(2,0);
		$this->M_artikel->hapus($this->session->userdata('ses_kode_kantor'),$ART_ID);
		
			$this->M_log->simpan(
				"DELETE",
				$this->session->userdata('ses_nama_karyawan'),
				"Melakukan penghapusan artikel dengan id : ".$ART_ID." ",
				$this->session->userdata('ses_kode_kantor')
			);
		
		$cari = "WHERE IMG_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
									AND IMG_GRUP = 'artikel'
									AND ID = '".$ART_ID."'";
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
		
		
		header('Location: '.base_url().'admin-artikel');
	}
	
	function cek_artikel()
	{	
		$ART_NAMA = $_POST['ART_NAMA'];
		$hasil_cek = $this->M_artikel->get_artikel('ART_NAMA',$ART_NAMA);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */