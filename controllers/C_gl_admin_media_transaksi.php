<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_media_transaksi extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_media_transaksi'));
		
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
							AND (nama_media LIKE '%".str_replace("'","",$_GET['cari'])."%' OR nama_akun LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$order_by = " ORDER BY tgl_ins DESC";
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-media-transaksi?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-media-transaksi/');
				$config['total_rows'] = $this->M_gl_media_transaksi->count_media_transaksi_limit($cari)->JUMLAH;
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
				
				$list_media_transaksi = $this->M_gl_media_transaksi->list_media_transaksi_limit($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Media transaksi yang digunakan";
				
				$data = array('page_content'=>'gl_admin_media_transaksi','halaman'=>$halaman,'list_media_transaksi'=>$list_media_transaksi,'msgbox_title' => $msgbox_title);
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
					$this->M_gl_media_transaksi->edit
					(
						$_POST['stat_edit'], //$_POST['id_tr_media'],
						$_POST['nama_media'],
						$_POST['nama_akun'],
						$_POST['ket_media'],
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
							'Melakukan perubahan data media transaksi '.$_POST['nama_media'].' | '.$_POST['nama_akun'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$this->M_gl_media_transaksi->simpan
					(
					
						$_POST['nama_media'],
						$_POST['nama_akun'],
						$_POST['ket_media'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					
					);
				}
				header('Location: '.base_url().'gl-admin-media-transaksi');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		$id_tr_media = ($this->uri->segment(2,0));
		$dat_media_transaksi = $this->M_gl_media_transaksi->get_media_transaksi('id_tr_media',$id_tr_media);
		if(!empty($dat_media_transaksi))
		{
			$dat_media_transaksi = $dat_media_transaksi->row();
			$this->M_gl_media_transaksi->hapus('id_tr_media',$id_tr_media);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data media transaksi '.$dat_media_transaksi->nama_media.' | '.$dat_media_transaksi->nama_akun,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-media-transaksi');
	}
	
	function cek_media_transaksi()
	{
		$hasil_cek = $this->M_gl_media_transaksi->get_media_transaksi('nama_media',$_POST['nama_media']);
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->nama_media;
		}
		else
		{
			return false;
		}
		
	}

	function cek_isDefault()
	{
		$this->M_gl_media_transaksi->set_default($_POST['id_tr_media'],$this->session->userdata('ses_kode_kantor'));
		echo $_POST['id_tr_media'];
	}


}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */