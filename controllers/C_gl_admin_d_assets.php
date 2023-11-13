<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_d_assets extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_kat_assets','M_gl_d_assets','M_gl_bank'));
		
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
							AND 
								(
									A.kode_assets LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.nama_assets LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(B.nama_kat_assets,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-assets-pinjaman?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-assets-pinjaman/');
				$config['total_rows'] = $this->M_gl_d_assets->count_d_assets_limit($cari)->JUMLAH;
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
				
				$list_d_assets = $this->M_gl_d_assets->list_d_assets_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$cari_bank = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				$list_bank = $this->M_gl_bank->list_bank_limit($cari_bank,30,0);
				
				$cari_kat_assets = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				$list_kat_assets = $this->M_gl_kat_assets->list_kat_assets_limit($cari_kat_assets,30,0);
				
				$msgbox_title = " Assets dan Pinjaman";
				
				$data = array('page_content'=>'gl_admin_d_assets','halaman'=>$halaman,'list_d_assets'=>$list_d_assets,'msgbox_title' => $msgbox_title,'list_bank' => $list_bank,'list_kat_assets' => $list_kat_assets);
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
				if($_POST['isUang'] == 'on')
				{
					$isUang = 1;
				}
				else
				{
					$isUang = 0;
				}
				
				if($_POST['apakah_angsuran'] == 'on')
				{
					$apakah_angsuran = 1;
				}
				else
				{
					$apakah_angsuran = 0;
				}
				
				if (!empty($_POST['id_bank']))
				{
					$id_bank = $_POST['id_bank'];
				}
				else
				{
					$id_bank = "";
				}
				
				if (!empty($_POST['stat_edit']))
				{	
					$this->M_gl_d_assets->edit
					(
						$_POST['stat_edit'],
						$_POST['id_kat_assets'],
						'', //$_POST['id_assets'],
						$id_bank, //$_POST['id_bank'],
						$_POST['kode_assets'],
						$_POST['nama_assets'],
						'', //$_POST['kode_d_assets'],
						$_POST['pemegang'],
						$_POST['tgl_beli'],
						str_replace(",","",$_POST['nominal_dp']) , //$_POST['nominal_dp'],
						str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
						$_POST['ket_d_assets'],
						$apakah_angsuran, //$_POST['apakah_angsuran'],
						$isUang, //$_POST['isUang'],
						str_replace(",","",$_POST['penyusutan']) , //$_POST['penyusutan'],
						$_POST['tgl_mulai_angsur'],
						$_POST['tgl_akhir_angsur'],
						str_replace(",","",$_POST['nominal_angsur']) , //$_POST['nominal_angsur'],
						$_POST['jenis_angsur'],
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
							'Melakukan perubahan data Assets dan Pinjaman '.$_POST['kode_d_assets'].' | '.$_POST['nama_d_assets'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$this->M_gl_d_assets->simpan
					(
						$_POST['id_kat_assets'],
						$_POST['id_assets'],
						$id_bank, //$_POST['id_bank'],
						$_POST['kode_assets'],
						$_POST['nama_assets'],
						'', //$_POST['kode_d_assets'],
						$_POST['pemegang'],
						$_POST['tgl_beli'],
						str_replace(",","",$_POST['nominal_dp']) , //$_POST['nominal_dp'],
						str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
						$_POST['ket_d_assets'],
						$apakah_angsuran, //$_POST['apakah_angsuran'],
						$isUang, //$_POST['isUang'],
						str_replace(",","",$_POST['penyusutan']) , //$_POST['penyusutan'],
						$_POST['tgl_mulai_angsur'],
						$_POST['tgl_akhir_angsur'],
						str_replace(",","",$_POST['nominal_angsur']) , //$_POST['nominal_angsur'],
						$_POST['jenis_angsur'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					
					);
				}
				header('Location: '.base_url().'gl-admin-assets-pinjaman');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		$id_d_assets = ($this->uri->segment(2,0));
		$data_d_assets = $this->M_gl_d_assets->get_d_assets('id_d_assets',$id_d_assets);
		if(!empty($data_d_assets))
		{
			$data_d_assets = $data_d_assets->row();
			$this->M_gl_d_assets->hapus('id_d_assets',$id_d_assets);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data d_assets '.$data_d_assets->kode_d_assets.' | '.$data_d_assets->nama_d_assets,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-assets-pinjaman');
	}
	
	function cek_d_assets()
	{
		$hasil_cek = $this->M_gl_d_assets->get_d_assets('kode_assets',$_POST['kode_assets']);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */