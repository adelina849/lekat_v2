<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_galeri_video extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_video','M_kat_video'));
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
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE A.VID_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
									AND A.VID_GRUP = 'video'
									AND A.VID_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
				}
				else
				{
					$cari = "WHERE A.VID_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
									AND A.VID_GRUP = 'video'";
				}
				
				$JUMLAH_RECORD = $this->M_video->count_video_limit($cari);
				if(!empty($JUMLAH_RECORD))
				{
					
					
				
					$this->load->library('pagination');
					$config['first_url'] = site_url('admin-galeri-video?'.http_build_query($_GET));
					$config['base_url'] = site_url('admin-galeri-video');
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
					
					$list_video = $this->M_video->list_video_limit($cari,$config['per_page'],$this->uri->segment(4,0));
					$list_kat_video = $this->M_kat_video->list_kat_video_limit('',100,0);
					
					$data = array('page_content'=>'king_admin_galeri_video','halaman'=>$halaman,'list_video'=>$list_video,'list_kat_video' => $list_kat_video);
					$this->load->view('admin/container',$data);
				}
				else
				{
					if($this->uri->segment(2,0) == "artikel")
					{
						header('Location: '.base_url().'admin-artikel');
					}
					else if($this->uri->segment(2,0) == "berita")
					{
						header('Location: '.base_url().'admin-berita');
					}
					else
					{
						header('Location: '.base_url().'admin');
					}
					
				}
				
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
				
				$this->M_video->edit
				(	
					$_POST['stat_edit'],
					$_POST['KVID_ID'],
					$_POST['VID_GRUP'],
					$_POST['ID'],
					'', //$_POST['VID_KODE'],
					$_POST['VID_NAMA'],
					$_POST['VID_KET'],
					$_POST['VID_LINK'],
					$this->session->userdata('ses_id_karyawan'),
					$this->session->userdata('ses_kode_kantor')
					
				);
				header('Location: '.base_url().'admin-galeri-video');
			}
			else
			{
				$this->M_video->simpan
				(
					$_POST['KVID_ID'],
					$_POST['VID_GRUP'],
					$_POST['ID'],
					'', //$_POST['VID_KODE'],
					$_POST['VID_NAMA'],
					$_POST['VID_KET'],
					$_POST['VID_LINK'],
					$this->session->userdata('ses_id_karyawan'),
					$this->session->userdata('ses_id_karyawan'),
					$this->session->userdata('ses_kode_kantor')
			
				);
				header('Location: '.base_url().'admin-galeri-video');
			}
		
	}
	
	function cek_video()
	{
		$hasil_cek = $this->M_video->get_video($_POST['id'],'pengajuan','VID_nama',$_POST['nama']);
		echo $hasil_cek;
	}
	
	public function hapus()
	{
		$VID_ID = $this->uri->segment(2,0);
		$cari = "WHERE VID_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
									AND VID_ID = '".$VID_ID."'";
									
		$hasil_cek = $this->M_video->get_video($cari);
		if(!empty($hasil_cek))
		{
			//$hasil_cek = $hasil_cek->row();
			$video = $hasil_cek->VID_FILE;
			$this->M_video->hapus($VID_ID);
		}
		header('Location: '.base_url().'admin-galeri-video');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_admin_video.php */