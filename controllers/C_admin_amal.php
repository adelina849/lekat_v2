<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_amal extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_amal','M_kat_amal','M_lokasi','M_images'));
		
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
					$cari = "WHERE A.AML_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (
									KAML_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR AML_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR AML_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR AML_PROV LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR AML_KAB LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR AML_KEC LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR AML_DESA LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
							";
				}
				else
				{
					$cari = "WHERE A.AML_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('admin-amal?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-amal/');
				$config['total_rows'] = $this->M_amal->count_amal_limit($cari)->JUMLAH;
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
				
				$list_amal = $this->M_amal->list_amal_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				$list_kat_amal = $this->M_kat_amal->list_kat_amal_limit('',100,0);
				$list_prov = $this->M_lokasi->list_prov('',100,0);
				
				$data = array('page_content'=>'king_admin_amal','halaman'=>$halaman,'list_amal'=>$list_amal,'list_kat_amal' => $list_kat_amal,'list_prov' => $list_prov);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
	
	public function tampilkan_kabkot()
	{
		$province_id = $_POST['AML_PROV'];
		$cari = " WHERE province_id = '".$province_id."'";
		$list_kabkot = $this->M_lokasi->list_kabkot($cari,1000,0);
		
		if (!empty($list_kabkot))
		{
			$list_result = $list_kabkot->result();
			foreach($list_result as $row)
			{
				echo '<option value="'.$row->id.'">'.$row->name.'</option>';
			}
		}
	}
	
	public function tampilkan_kec()
	{
		$kabkot_id = $_POST['AML_KAB'];
		$cari = " WHERE kabkot_id = '".$kabkot_id."'";
		$list_kec = $this->M_lokasi->list_kecamatan($cari,1000,0);
		if (!empty($list_kec))
		{
			$list_result = $list_kec->result();
			foreach($list_result as $row)
			{
				echo '<option value="'.$row->id.'">'.$row->name.'</option>';
			}
		}
	}
	
	public function tampilkan_desa()
	{
		$kec_id = $_POST['AML_KEC'];
		$cari = " WHERE kec_id = '".$kec_id."'";
		$list_desa = $this->M_lokasi->list_desa($cari,1000,0);
		if (!empty($list_desa))
		{
			$list_result = $list_desa->result();
			foreach($list_result as $row)
			{
				echo '<option value="'.$row->id.'">'.$row->name.'</option>';
			}
		}
	}
	
	public function simpan()
	{
		if (!empty($_POST['stat_edit']))
		{	
			$this->M_amal->edit
			(
			
				$_POST['stat_edit'],
				$_POST['KAML_ID'],
				$_POST['AML_KODE'],
				$_POST['AML_NAMA'],
				url_title($_POST['AML_NAMA']), //$BRT_LINKTITLE,
				$_POST['AML_KEYWORDS'],
				$_POST['AML_DESC'],
				$_POST['AML_PIC'],
				$_POST['AML_PICTLP'],
				$_POST['AML_NAMALOK'],
				$_POST['AML_PICLOK'],
				$_POST['AML_PICLOKTLP'],
				$_POST['AML_NOMINAL'],
				$_POST['AML_KET'],
				$_POST['AML_PROV'],
				$_POST['AML_KAB'],
				$_POST['AML_KEC'],
				$_POST['AML_DESA'],
				$_POST['AML_ALMTDETAIL'],
				$_POST['AML_LONGI'],
				$_POST['AML_LATI'],
				$_POST['AML_START'],
				$_POST['AML_END'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-amal');
			
			
		}
		else
		{
			$this->M_amal->simpan
			(
				$_POST['KAML_ID'],
				$_POST['AML_KODE'],
				$_POST['AML_NAMA'],
				url_title($_POST['AML_NAMA']), //$BRT_LINKTITLE,
				$_POST['AML_KEYWORDS'],
				$_POST['AML_DESC'],
				$_POST['AML_PIC'],
				$_POST['AML_PICTLP'],
				$_POST['AML_NAMALOK'],
				$_POST['AML_PICLOK'],
				$_POST['AML_PICLOKTLP'],
				$_POST['AML_NOMINAL'],
				$_POST['AML_KET'],
				$_POST['AML_PROV'],
				$_POST['AML_KAB'],
				$_POST['AML_KEC'],
				$_POST['AML_DESA'],
				$_POST['AML_ALMTDETAIL'],
				$_POST['AML_LONGI'],
				$_POST['AML_LATI'],
				$_POST['AML_START'],
				$_POST['AML_END'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-amal');
		}
		
		//echo 'ade';*/
	}
	
	function hapus()
	{
		/*$AML_ID = $this->uri->segment(2,0);
		$this->M_amal->hapus($this->session->userdata('ses_kode_kantor'),$AML_ID);
		header('Location: '.base_url().'admin-amal');*/
		
		$AML_ID = $this->uri->segment(2,0);
		$this->M_amal->hapus($this->session->userdata('ses_kode_kantor'),$AML_ID);
		
			$this->M_log->simpan(
				"DELETE",
				$this->session->userdata('ses_nama_karyawan'),
				"Melakukan penghapusan amal dengan id : ".$AML_ID." ",
				$this->session->userdata('ses_kode_kantor')
			);
		
		$cari = "WHERE IMG_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
									AND IMG_GRUP = 'amal'
									AND ID = '".$AML_ID."'";
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
		
		
		header('Location: '.base_url().'admin-amal');
	}
	
	function cek_amal()
	{	
		$AML_KODE = $_POST['AML_KODE'];
		$hasil_cek = $this->M_amal->get_amal('AML_KODE',$AML_KODE);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */