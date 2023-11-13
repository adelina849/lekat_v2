<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_gedung extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_kat_assets','M_gl_gedung','M_gl_bank','M_gl_karyawan'));
		
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
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (A.kat_gedung LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.kode_gedung LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_gedung LIKE '%".str_replace("'","",$_GET['cari'])."%' )";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-ruangan-tempat-penyimpanan-barang?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-ruangan-tempat-penyimpanan-barang/');
				$config['total_rows'] = $this->M_gl_gedung->count_gedung_limit($cari)->JUMLAH;
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
				
				$list_gedung = $this->M_gl_gedung->list_gedung_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$cari_karyawan = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN') AND A.isDokter <> 'DOKTER'";
				$list_karyawan = $this->M_gl_karyawan->list_karyawan_limit($cari_karyawan,50,0);
				
				$msgbox_title = " Gudang Penyimpanan, Kamar dan Ruangan";
				
				$data = array('page_content'=>'gl_admin_gedung','halaman'=>$halaman,'list_gedung'=>$list_gedung,'msgbox_title' => $msgbox_title,'list_karyawan' => $list_karyawan);
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
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."';");
			
			if(!empty($cek_ses_login))
			{
				
				if (!empty($_POST['stat_edit']))
				{	
					$this->M_gl_gedung->edit
					(
						$_POST['stat_edit'],
						$_POST['id_karyawan'],
						$_POST['kat_gedung'],
						$_POST['kode_gedung'],
						$_POST['nama_gedung'],
						$_POST['ket_gedung'],
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
							'Melakukan perubahan data Gedung, Ruangan Atau Tempat Penyimpanan Produk '.$_POST['kode_gedung'].' | '.$_POST['nama_gedung'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$this->M_gl_gedung->simpan
					(
						$_POST['id_karyawan'],
						$_POST['kat_gedung'],
						$_POST['kode_gedung'],
						$_POST['nama_gedung'],
						$_POST['ket_gedung'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				header('Location: '.base_url().'gl-admin-ruangan-tempat-penyimpanan-barang');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		$id_gedung = ($this->uri->segment(2,0));
		$data_gedung = $this->M_gl_gedung->get_gedung('id_gedung',$id_gedung);
		if(!empty($data_gedung))
		{
			$data_gedung = $data_gedung->row();
			$this->M_gl_gedung->hapus('id_gedung',$id_gedung);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data gedung '.$data_gedung->kode_gedung.' | '.$data_gedung->nama_gedung,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-ruangan-tempat-penyimpanan-barang');
	}
	
	function cek_gedung()
	{
		$hasil_cek = $this->M_gl_gedung->get_gedung('kode_gedung',$_POST['kode_gedung']);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */