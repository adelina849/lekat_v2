<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_kat_produk extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_pst_kat_produk','M_gl_kat_produk'));
		
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
							AND (kode_kat_produk LIKE '%".str_replace("'","",$_GET['cari'])."%' OR nama_kat_produk LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-kategori-produk-jasa?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-kategori-produk-jasa/');
				$config['total_rows'] = $this->M_gl_kat_produk->count_kat_produk_limit($cari)->JUMLAH;
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
				
				$list_kat_produk = $this->M_gl_kat_produk->list_kat_produk_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Kategori Produk Dan Jasa";
				
				$data = array('page_content'=>'gl_admin_kat_produk','halaman'=>$halaman,'list_kat_produk'=>$list_kat_produk,'msgbox_title' => $msgbox_title);
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
					if($this->session->userdata('ses_kode_kantor') == 'PST')
					{
						$this->M_gl_pst_kat_produk->edit
						(
							$_POST['stat_edit'],
							$_POST['kode_kat_produk'],
							$_POST['nama_kat_produk'],
							$_POST['ket_kat_produk'],
							$_POST['isProduk'],
							$this->session->userdata('ses_id_karyawan')
							
						);
					}
					else
					{
						$this->M_gl_kat_produk->edit
						(
							$_POST['stat_edit'],
							$_POST['kode_kat_produk'],
							$_POST['nama_kat_produk'],
							$_POST['ket_kat_produk'],
							$_POST['isProduk'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
							
						);
					}
					
					
					/* CATAT AKTIFITAS EDIT*/
					if($this->session->userdata('catat_log') == 'Y')
					{
						$this->M_gl_log->simpan_log
						(
							$this->session->userdata('ses_id_karyawan'),
							'UPDATE',
							'Melakukan perubahan data Kategoro Produk dan Jasa '.$_POST['kode_kat_produk'].' | '.$_POST['nama_kat_produk'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					if($this->session->userdata('ses_kode_kantor') == 'PST')
					{
						$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
						$id_kat_produk = $this->M_gl_pst_kat_produk->get_id_kat_produk();
						
						if(!empty($list_kantor))
						{
							$list_result = $list_kantor->result();
							
							foreach($list_result as $row)
							{
								//echo'<option value="'.$row->kode_kantor.'">'.$row->nama_kantor.'</option>';
								$this->M_gl_pst_kat_produk->simpan
								(
									$id_kat_produk->id_kat_produk,
									$_POST['kode_kat_produk'],
									$_POST['nama_kat_produk'],
									$_POST['ket_kat_produk'],
									$_POST['isProduk'],
									$this->session->userdata('ses_id_karyawan'),
									$row->kode_kantor //$this->session->userdata('ses_kode_kantor')
								);
							}
						}
					}
					else
					{
						$this->M_gl_kat_produk->simpan
						(
							$_POST['kode_kat_produk'],
							$_POST['nama_kat_produk'],
							$_POST['ket_kat_produk'],
							$_POST['isProduk'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						
						);
					}
					
				}
				header('Location: '.base_url().'gl-admin-kategori-produk-jasa');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		if($this->session->userdata('ses_kode_kantor') == 'PST')
		{
			$id_kat_produk = ($this->uri->segment(2,0));
			$data_kat_produk = $this->M_gl_pst_kat_produk->get_kat_produk('id_kat_produk',$id_kat_produk);
			if(!empty($data_kat_produk))
			{
				$data_kat_produk = $data_kat_produk->row();
				$this->M_gl_pst_kat_produk->hapus('id_kat_produk',$id_kat_produk);
				
				/* CATAT AKTIFITAS HAPUS*/
				if($this->session->userdata('catat_log') == 'Y')
				{
					$this->M_gl_log->simpan_log
					(
						$this->session->userdata('ses_id_karyawan'),
						'DELETE',
						'Melakukan penghapusan data kategori produk dan jasa '.$data_kat_produk->kode_kat_produk.' | '.$data_kat_produk->nama_kat_produk,
						$this->M_gl_pengaturan->getUserIpAddr(),
						gethostname(),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				/* CATAT AKTIFITAS HAPUS*/
			}
		}
		else
		{
			$id_kat_produk = ($this->uri->segment(2,0));
			$data_kat_produk = $this->M_gl_kat_produk->get_kat_produk('id_kat_produk',$id_kat_produk);
			if(!empty($data_kat_produk))
			{
				$data_kat_produk = $data_kat_produk->row();
				$this->M_gl_kat_produk->hapus('id_kat_produk',$id_kat_produk);
				
				/* CATAT AKTIFITAS HAPUS*/
				if($this->session->userdata('catat_log') == 'Y')
				{
					$this->M_gl_log->simpan_log
					(
						$this->session->userdata('ses_id_karyawan'),
						'DELETE',
						'Melakukan penghapusan data kategori produk dan jasa '.$data_kat_produk->kode_kat_produk.' | '.$data_kat_produk->nama_kat_produk,
						$this->M_gl_pengaturan->getUserIpAddr(),
						gethostname(),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				/* CATAT AKTIFITAS HAPUS*/
			}
		}
		
		header('Location: '.base_url().'gl-admin-kategori-produk-jasa');
	}
	
	function cek_kat_produk()
	{
		if($this->session->userdata('ses_kode_kantor') == 'PST')
		{
			$hasil_cek = $this->M_gl_pst_kat_produk->get_kat_produk('kode_kat_produk',$_POST['kode_kat_produk']);
			if(!empty($hasil_cek))
			{
				echo "SUKSES";
			}
			else
			{
				return false;
			}
		}
		else
		{
			$hasil_cek = $this->M_gl_kat_produk->get_kat_produk('kode_kat_produk',$_POST['kode_kat_produk']);
			if(!empty($hasil_cek))
			{
				echo "SUKSES";
			}
			else
			{
				return false;
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */