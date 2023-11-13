<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_jabatan_akses extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_hak_akses','M_gl_jabatan'));
		
	}
	
	public function index()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$id_jabatan = $this->uri->segment(2,0);
				$data_jabatan = $this->M_gl_jabatan->get_jabatan('id_jabatan',$id_jabatan);
				if(!empty($data_jabatan))
				{
					$data_jabatan = $data_jabatan->row();
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE (A.group1 LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.main_group LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_fasilitas LIKE '%".str_replace("'","",$_GET['cari'])."%' ) ";
					}
					else
					{
						//$cari = "WHERE A.main_group <> ''";
						$cari = '';
					}
					
					
					$this->load->library('pagination');
					
					$config['first_url'] = site_url('gl-admin-jabatan-hak-akses/'.$id_jabatan.'?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-jabatan-hak-akses/'.$id_jabatan);
					
					$config['total_rows'] = $this->M_gl_hak_akses->count_hak_akses_limit($cari)->JUMLAH;
					$config['uri_segment'] = 3;	
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
					
					$list_jabatan_akses = $this->M_gl_hak_akses->list_hak_akses_limit($id_jabatan,$cari,$config['per_page'],$this->uri->segment(3,0));
					
					$msgbox_title = " Hak Akses Jabatan ".$data_jabatan->nama_jabatan;
					
					$data = array('page_content'=>'gl_admin_jabatan_akses','halaman'=>$halaman,'list_jabatan_akses'=>$list_jabatan_akses,'msgbox_title' => $msgbox_title,'data_jabatan' => $data_jabatan);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-jabatan');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function beri_akses_masal()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$id_jabatan = $this->uri->segment(2,0); //SUDAH MD5
				$data_jabatan = $this->M_gl_jabatan->get_jabatan('MD5(id_jabatan)',$id_jabatan);
				if(!empty($data_jabatan))
				{
					$data_jabatan = $data_jabatan->row();
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE COALESCE(B.JUMLAH,0) = 0 AND (A.group1 LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.main_group LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_fasilitas LIKE '%".str_replace("'","",$_GET['cari'])."%' ) ";
					}
					else
					{
						//$cari = "WHERE A.main_group <> ''";
						$cari = " WHERE COALESCE(B.JUMLAH,0) = 0";
					}
					
					if((!empty($_GET['offset'])) && ($_GET['offset']!= "")  )
					{
						$offset = str_replace("'","",$_GET['offset']);
					}
					else
					{
						//$cari = "WHERE A.main_group <> ''";
						$offset = 0;
					}
					
					//echo $data_jabatan->id_jabatan;
					$this->M_gl_hak_akses->beri_akses_masal($data_jabatan->id_jabatan,$cari,30,$offset); //ID MD5 TIDAK MD5
					header('Location: '.base_url().'gl-admin-jabatan-hak-akses/'.$data_jabatan->id_jabatan);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-jabatan');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function simpan()
	{
		if (!empty($_POST['stat_edit']))
		{	
			$this->M_gl_jabatan->edit
			(
			
				$_POST['stat_edit'],
				$_POST['kode_jabatan'],
				$_POST['nama_jabatan'],
				$_POST['ket_jabatan'],
				$_POST['hirarki'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			
			/* CATAT AKTIFITAS EDIT*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'UPDATE',
					'Melakukan perubahan data jabatan '.$_POST['kode_jabatan'].' | '.$_POST['nama_jabatan'],
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS EDIT*/
			
			header('Location: '.base_url().'gl-admin-jabatan');
			
			
		}
		else
		{
			$this->M_gl_jabatan->simpan
			(
				$_POST['kode_jabatan'],
				$_POST['nama_jabatan'],
				$_POST['hirarki'],
				$_POST['ket_jabatan'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			
			);
			header('Location: '.base_url().'gl-admin-jabatan');
		}
		
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$id = ($this->uri->segment(2,0));
		$data_jabatan = $this->M_gl_jabatan->get_jabatan('id_jabatan',$id);
		if(!empty($data_jabatan))
		{
			$data_jabatan = $data_jabatan->row();
			$this->M_gl_jabatan->hapus($id);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data jabatan '.$data_jabatan->kode_jabatan.' | '.$data_jabatan->nama_jabatan,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-jabatan');
	}
	
	function cek_hak_akses()
	{	/*
		$id_jabatan = $_POST['id_jabatan'];
		$id_fasilitas = $_POST['id_fasilitas'];
		$hasil_cek = $this->M_gl_hak_akses->get_akses_fasilitas($id_jabatan,$id_fasilitas);
		echo $hasil_cek;
		*/
		
		$query = $this->M_gl_hak_akses->get_akses_fasilitas($_POST['id_jabatan'],$_POST['id_fasilitas']);
		if(!empty($query))
		{
			$this->M_gl_hak_akses->hapus($_POST['id_jabatan'],$_POST['id_fasilitas']);
			echo('Belum Terdaftar');   
		}
		else
		{
			////simpan($id_jabatan,$id_fasilitas,$id_user,$kode_kantor)
			
			$this->M_gl_hak_akses->simpan
			(
				$_POST['id_jabatan'],
				$_POST['id_fasilitas'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			
			echo('Terdaftar');
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */