<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_kat_costumer extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_kat_costumer'));
		
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
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (kode_kat_costumer LIKE '%".str_replace("'","",$_GET['cari'])."%' OR nama_kat_costumer LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-kategori-costumer-jasa?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-kategori-costumer-jasa/');
				$config['total_rows'] = $this->M_gl_kat_costumer->count_kat_costumer_limit($cari)->JUMLAH;
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
				
				$list_kat_costumer = $this->M_gl_kat_costumer->list_kat_costumer_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Kategori Pasien";
				
				$data = array('page_content'=>'gl_admin_kat_costumer','halaman'=>$halaman,'list_kat_costumer'=>$list_kat_costumer,'msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function simpan()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."' ");
			
			if(!empty($cek_ses_login))
			{
				if($_POST['kelipatan'] == 'on')
				{
					$strKelipatan = 1;
				}
				else
				{
					$strKelipatan = 0;
				}
				
				if (!empty($_POST['stat_edit']))
				{	
					$this->M_gl_kat_costumer->edit
					(
						$_POST['stat_edit'],
						'0', //$_POST['set_default'],
						$_POST['nama_kat_costumer'],
						'+', //$_POST['status'],
						'%', //$_POST['optr_harga'],
						'0', //$_POST['harga'],
						'1', //$_POST['hirarki_harga'],
						str_replace(",","",$_POST['minimal_belanja']) , //$_POST['minimal_belanja'],
						$_POST['kondisi'],
						$strKelipatan, //$_POST['kelipatan'],
						str_replace(",","",$_POST['besar_point']) , //$_POST['besar_point'],
						'1', //$_POST['aktif'],
						$_POST['point_dari'],
						$_POST['point_sampai'],
						'', //$_POST['point_fixed'],
						$_POST['ket_kat_costumer'],
						'', //$_POST['rumus_harga'],
						'', //$_POST['rumus_harga3'],
						'', //$_POST['rumus_harga2'],
						'1', //$_POST['pembulatan'],
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
							'Melakukan perubahan data Kategori costumer dan Jasa '.$_POST['kode_kat_costumer'].' | '.$_POST['nama_kat_costumer'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$this->M_gl_kat_costumer->simpan
					(
						'0', //$_POST['set_default'],
						$_POST['nama_kat_costumer'],
						'+', //$_POST['status'],
						'%', //$_POST['optr_harga'],
						'0', //$_POST['harga'],
						'1', //$_POST['hirarki_harga'],
						str_replace(",","",$_POST['minimal_belanja']) , //$_POST['minimal_belanja'],
						$_POST['kondisi'],
						$strKelipatan, //$_POST['kelipatan'],
						str_replace(",","",$_POST['besar_point']) , //$_POST['besar_point'],
						'1', //$_POST['aktif'],
						$_POST['point_dari'],
						$_POST['point_sampai'],
						'', //$_POST['point_fixed'],
						$_POST['ket_kat_costumer'],
						'', //$_POST['rumus_harga'],
						'', //$_POST['rumus_harga3'],
						'', //$_POST['rumus_harga2'],
						'1', //$_POST['pembulatan'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					
					);
				}
				header('Location: '.base_url().'gl-admin-kategori-pasien');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		$id_kat_costumer = ($this->uri->segment(2,0));
		$data_kat_costumer = $this->M_gl_kat_costumer->get_kat_costumer('id_kat_costumer',$id_kat_costumer);
		if(!empty($data_kat_costumer))
		{
			$data_kat_costumer = $data_kat_costumer->row();
			$this->M_gl_kat_costumer->hapus('id_kat_costumer',$id_kat_costumer);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data kategori costumer | '.$data_kat_costumer->nama_kat_costumer,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-kategori-pasien');
	}
	
	function cek_kat_costumer()
	{
		$hasil_cek = $this->M_gl_kat_costumer->get_kat_costumer('nama_kat_costumer',$_POST['nama_kat_costumer']);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */