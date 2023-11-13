<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_jadwal_kegiatan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_jadwal_kegiatan','M_departemen','M_lokasi','M_images'));
		
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
					$cari = "WHERE JKEG_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND JKEG_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
				}
				else
				{
					$cari = "WHERE JKEG_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('admin-jadwal-kegiatan?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-jadwal-kegiatan/');
				$config['total_rows'] = $this->M_jadwal_kegiatan->count_jadwal_kegiatan_limit($cari)->JUMLAH;
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
				
				$list_jadwal_kegiatan = $this->M_jadwal_kegiatan->list_jadwal_kegiatan_limit($cari," ORDER BY A.JKEG_DTDARI DESC ",$config['per_page'],$this->uri->segment(2,0));
				
				
				$list_dept = $this->M_departemen->list_departemen_limit("",100,0);
				$list_prov = $this->M_lokasi->list_prov('',100,0);
				
				$data = array('page_content'=>'king_admin_jadwal_kegiatan','halaman'=>$halaman,'list_jadwal_kegiatan'=>$list_jadwal_kegiatan,'list_prov' => $list_prov,'list_dept' => $list_dept);
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
		$province_id = $_POST['JKEG_PROV'];
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
		$kabkot_id = $_POST['JKEG_KABKOT'];
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
		$kec_id = $_POST['JKEG_KEC'];
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
		if(!empty($_POST['JKEG_ISPUBLISH']))
		{
			$JKEG_ISPUBLISH = $_POST['JKEG_ISPUBLISH'];
		}
		else
		{
			$JKEG_ISPUBLISH = 0;
		}
		
		if (!empty($_POST['stat_edit']))
		{	
			$this->M_jadwal_kegiatan->edit
			(
				$_POST['stat_edit'],
				$_POST['DEPT_KODE'],
				$_POST['JKEG_NAMA'],
				$_POST['JKEG_DASAR'],
				$_POST['JKEG_KETUA'],
				$_POST['JKEG_SUMBERDANA'],
				$_POST['JKEG_PERIODE'],
				$_POST['JKEG_NOMINAL'],
				$_POST['JKEG_PROV'],
				$_POST['JKEG_KABKOT'],
				$_POST['JKEG_KEC'],
				$_POST['JKEG_DESA'],
				$_POST['JKEG_ALAMATDETAIL'],
				$_POST['JKEG_DTDARI'],
				$_POST['JKEG_DTSAMPAI'],
				$_POST['JKEG_KET'],
				$JKEG_ISPUBLISH,
				$_POST['JKEG_LATI'],
				$_POST['JKEG_LONGI'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-jadwal-kegiatan');
			
			
		}
		else
		{
			$this->M_jadwal_kegiatan->simpan
			(
				$_POST['DEPT_KODE'],
				$_POST['JKEG_NAMA'],
				$_POST['JKEG_DASAR'],
				$_POST['JKEG_KETUA'],
				$_POST['JKEG_SUMBERDANA'],
				$_POST['JKEG_PERIODE'],
				$_POST['JKEG_NOMINAL'],
				$_POST['JKEG_PROV'],
				$_POST['JKEG_KABKOT'],
				$_POST['JKEG_KEC'],
				$_POST['JKEG_DESA'],
				$_POST['JKEG_ALAMATDETAIL'],
				$_POST['JKEG_DTDARI'],
				$_POST['JKEG_DTSAMPAI'],
				$_POST['JKEG_KET'],
				$JKEG_ISPUBLISH,
				$_POST['JKEG_LATI'],
				$_POST['JKEG_LONGI'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-jadwal-kegiatan');
		}
		
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		
		$JKEG_ID = $this->uri->segment(2,0);
		$DATA_JADWAL_KEGIATAN = $this->M_jadwal_kegiatan->get_jadwal_kegiatan('JKEG_ID',$JKEG_ID);
		if(!empty($DATA_JADWAL_KEGIATAN))
		{
			//$DATA_JADWAL_KEGIATAN = $DATA_JADWAL_KEGIATAN->row();
			$this->M_jadwal_kegiatan->hapus($this->session->userdata('ses_kode_kantor'),$JKEG_ID);
			$this->M_log->simpan(
									"DELETE",
									$this->session->userdata('ses_nama_karyawan'),
									"Melakukan penghapusan Jadwal Kegiatan ".$DATA_JADWAL_KEGIATAN->JKEG_NAMA." dengan id : ".$DATA_JADWAL_KEGIATAN->JKEG_ID." ",
									$this->session->userdata('ses_kode_kantor')
								);
								
								
			$cari = "WHERE IMG_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
									AND IMG_GRUP = 'kegiatan'
									AND ID = '".$JKEG_ID."'";
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
		}
		
		header('Location: '.base_url().'admin-jadwal-kegiatan');
	}
	
	function cek_jadwal_kegiatan()
	{	
		$JKEG_NAMA = $_POST['JKEG_NAMA'];
		$hasil_cek = $this->M_jadwal_kegiatan->get_jadwal_kegiatan('JKEG_NAMA',$JKEG_NAMA);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */