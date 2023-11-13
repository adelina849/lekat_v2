<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_kantor extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_kantor'));
		
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
					$cari = "WHERE (
									kode_kantor LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR nama_kantor LIKE '%".str_replace("'","",$_GET['cari'])."%'
									)";
				}
				else
				{
					$cari = "";
				}
				
				$this->load->library('pagination');
				
				$config['first_url'] = site_url('gl-pusat-pengaturan-kantor?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-pusat-pengaturan-kantor/');
				$config['total_rows'] = $this->M_gl_kantor->count_kantor_limit($cari)->JUMLAH;
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
				
				$list_kantor = $this->M_gl_kantor->list_kantor($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Pengaturan Kantor";
				
				$data = array('page_content'=>'gl_pst_kantor','halaman'=>$halaman,'list_kantor'=>$list_kantor,'msgbox_title' => $msgbox_title);
				$this->load->view('pusat/container',$data);
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
				$cek_kode_kantor = $this->M_gl_kantor->list_kantor(" WHERE kode_kantor = '".$_POST['kode_kantor']."'",1,0);
				//if (!empty($_POST['stat_edit']))
				if(!empty($cek_kode_kantor))
				{	
					
					$this->M_gl_kantor->edit
					(
						$_POST['kode_kantor'],
						$_POST['SITU'],
						$_POST['SIUP'],
						$_POST['nama_kantor'],
						$_POST['pemilik'],
						$_POST['kota'],
						$_POST['alamat'],
						$_POST['tlp'],
						$_POST['sejarah'],
						$_POST['ket_kantor'],
						$_POST['isViewClient'],
						0, //$_POST['isDefault'],
						$_POST['isInventory'],
						'', //$_POST['img_kantor'],
						'', //$_POST['url_img'],
						$_POST['sts_kantor'],
						$_POST['isModePst'],
						$this->session->userdata('ses_id_karyawan'), //$_POST['user_updt'],
						'', //$_POST['id_alamat'],
						$_POST['LOK_LATI'],
						$_POST['LOK_LONGI']
					);
					
					/* CATAT AKTIFITAS EDIT*/
					if($this->session->userdata('catat_log') == 'Y')
					{
						$this->M_gl_log->simpan_log
						(
							$this->session->userdata('ses_id_karyawan'),
							'UPDATE',
							'Melakukan perubahan data satuan '.$_POST['kode_satuan'].' | '.$_POST['nama_satuan'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					
					$this->M_gl_kantor->simpan
					(
						$_POST['kode_kantor'],
						$_POST['SITU'],
						$_POST['SIUP'],
						$_POST['nama_kantor'],
						$_POST['pemilik'],
						$_POST['kota'],
						$_POST['alamat'],
						$_POST['tlp'],
						$_POST['sejarah'],
						$_POST['ket_kantor'],
						$_POST['isViewClient'],
						0, //$_POST['isDefault'],
						$_POST['isInventory'],
						'', //$_POST['img_kantor'],
						'', //$_POST['url_img'],
						$_POST['sts_kantor'],
						$_POST['isModePst'],
						$this->session->userdata('ses_id_karyawan'), //$_POST['user_updt'],
						'', //$_POST['id_alamat'],
						$_POST['LOK_LATI'],
						$_POST['LOK_LONGI']
					);
					
				}
				header('Location: '.base_url().'gl-pusat-pengaturan-kantor');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	
	function cek_kantor()
	{
		$cek_kode_kantor = $this->M_gl_kantor->list_kantor(" WHERE kode_kantor = '".$_POST['kode_kantor']."'",1,0);
		//if (!empty($_POST['stat_edit']))
		if(!empty($cek_kode_kantor))
		{	
			$cek_kode_kantor = $cek_kode_kantor->row();
			echo $cek_kode_kantor->kode_kantor;
		}
		else
		{
			return false;
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */