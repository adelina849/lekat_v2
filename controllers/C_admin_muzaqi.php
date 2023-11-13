<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_muzaqi extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_muzaqi','M_kat_muzaqi','M_lokasi','M_images'));
		
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
					$cari = "WHERE A.MUZ_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (
									KMUZ_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR MUZ_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR MUZ_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR MUZ_PROV LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR MUZ_KAB LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR MUZ_KEC LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR MUZ_DESA LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
							";
				}
				else
				{
					$cari = "WHERE A.MUZ_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('admin-muzaqi?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-muzaqi/');
				$config['total_rows'] = $this->M_muzaqi->count_muzaqi_limit($cari)->JUMLAH;
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
				
				$list_muzaqi = $this->M_muzaqi->list_muzaqi_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				$list_kat_muzaqi = $this->M_kat_muzaqi->list_kat_muzaqi_limit('',100,0);
				$list_prov = $this->M_lokasi->list_prov('',100,0);
				
				$data = array('page_content'=>'king_admin_muzaqi','halaman'=>$halaman,'list_muzaqi'=>$list_muzaqi,'list_kat_muzaqi' => $list_kat_muzaqi,'list_prov' => $list_prov);
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
		$province_id = $_POST['MUZ_PROV'];
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
		$kabkot_id = $_POST['MUZ_KAB'];
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
		$kec_id = $_POST['MUZ_KEC'];
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
		if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
		{
			$cari = $_GET['cari'];
		}
		else
		{
			$cari = "";
		}
		
		if (!empty($_POST['stat_edit']))
		{	
			$this->M_muzaqi->edit
			(
			
				$_POST['stat_edit'],
				$_POST['KMUZ_ID'],
				$_POST['PRSH_NAMA'],
				$_POST['PROF_NAMA'],
				$_POST['MUZ_KODE'],
				$_POST['MUZ_NAMA'],
				$_POST['MUZ_TLP'],
				$_POST['MUZ_EMAIL'],
				$_POST['MUZ_GAJI'],
				$_POST['MUZ_TLAHIR'],
				$_POST['MUZ_DTLAHIR'],
				$_POST['MUZ_PROV'],
				$_POST['MUZ_KAB'],
				$_POST['MUZ_KEC'],
				$_POST['MUZ_DESA'],
				$_POST['MUZ_ALMTDETAIL'],
				$_POST['MUZ_LONGI'],
				$_POST['MUZ_LATI'],
				$_POST['MUZ_KET'],
				//$_POST['MUZ_AVATAR'],
				//$_POST['MUZ_AVATARLINK'],
				md5($_POST['MUZ_PASSORI']),
				$_POST['MUZ_PASSORI'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-muzaqi?cari='.$cari);
			
			
		}
		else
		{
			$this->M_muzaqi->simpan
			(
			
				$_POST['KMUZ_ID'],
				$_POST['PRSH_NAMA'],
				$_POST['PROF_NAMA'],
				$_POST['MUZ_KODE'],
				$_POST['MUZ_NAMA'],
				$_POST['MUZ_TLP'],
				$_POST['MUZ_EMAIL'],
				$_POST['MUZ_GAJI'],
				$_POST['MUZ_TLAHIR'],
				$_POST['MUZ_DTLAHIR'],
				$_POST['MUZ_PROV'],
				$_POST['MUZ_KAB'],
				$_POST['MUZ_KEC'],
				$_POST['MUZ_DESA'],
				$_POST['MUZ_ALMTDETAIL'],
				$_POST['MUZ_LONGI'],
				$_POST['MUZ_LATI'],
				$_POST['MUZ_KET'],
				//$_POST['MUZ_AVATAR'],
				//$_POST['MUZ_AVATARLINK'],
				$_POST['MUZ_USER'],
				md5($_POST['MUZ_PASSORI']),
				$_POST['MUZ_PASSORI'],
				'2',
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			
			);
			header('Location: '.base_url().'admin-muzaqi?cari='.$cari);
		}
		
		//echo 'ade';*/
	}
	
	function hapus()
	{
		
		$MUZ_ID = $this->uri->segment(2,0);
		$this->M_muzaqi->hapus($this->session->userdata('ses_kode_kantor'),$MUZ_ID);
		
			$this->M_log->simpan(
				"DELETE",
				$this->session->userdata('ses_nama_karyawan'),
				"Melakukan penghapusan muzaqi dengan id : ".$MUZ_ID." ",
				$this->session->userdata('ses_kode_kantor')
			);
		
		$cari = "WHERE IMG_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
									AND IMG_GRUP = 'muzaqi'
									AND ID = '".$MUZ_ID."'";
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
		
		
		header('Location: '.base_url().'admin-muzaqi');
	}
	
	function cek_muzaqi()
	{	
		$MUZ_KODE = $_POST['MUZ_KODE'];
		$hasil_cek = $this->M_muzaqi->get_muzaqi('MUZ_KODE',$MUZ_KODE);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */