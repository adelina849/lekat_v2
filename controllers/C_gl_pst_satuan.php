<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_satuan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_pst_satuan'));
		
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
				$data_kantor_default = $this->M_gl_pengaturan->get_data_kantor(" WHERE isDefault = 1");
				if(!empty($data_kantor_default))
				{
					$data_kantor_default = $data_kantor_default->row();
					$kode_kantor = $data_kantor_default->kode_kantor;
					
				}
				else
				{
					$data_kantor_default = false;
					$kode_kantor = '';
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE kode_kantor = '".$kode_kantor."' 
							AND 
							(
								kode_satuan LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR nama_satuan LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "WHERE kode_kantor = '".$kode_kantor."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-pusat-satuan-produk-jasa?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-pusat-satuan-produk-jasa/');
				$config['total_rows'] = $this->M_gl_pst_satuan->count_satuan_limit($cari)->JUMLAH;
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
				
				$list_satuan = $this->M_gl_pst_satuan->list_satuan_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Satuan produk";
				
				$data = array('page_content'=>'gl_pusat_satuan','halaman'=>$halaman,'list_satuan'=>$list_satuan,'msgbox_title' => $msgbox_title);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url());
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
					$this->M_gl_pst_satuan->edit
					(
						$_POST['stat_edit'],
						$_POST['kode_satuan'],
						$_POST['nama_satuan'],
						$_POST['ket_satuan'],
						$this->session->userdata('ses_id_karyawan')
						
					);
					
					/* CATAT AKTIFITAS EDIT*/
					if($this->session->userdata('catat_log') == 'Y')
					{
						$this->M_gl_log->simpan_log
						(
							$this->session->userdata('ses_id_karyawan'),
							'UPDATE',
							'Melakukan perubahan data Satuan Produk dan Jasa '.$_POST['kode_satuan'].' | '.$_POST['nama_kat_produk'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
					$id_satuan = $this->M_gl_pst_satuan->get_id_satuan();
					
					if(!empty($list_kantor))
					{
						$list_result = $list_kantor->result();
						
						foreach($list_result as $row)
						{
							//echo'<option value="'.$row->kode_kantor.'">'.$row->nama_kantor.'</option>';
							$this->M_gl_pst_satuan->simpan
							(
								$id_satuan->id_satuan,
								$_POST['kode_satuan'],
								$_POST['nama_satuan'],
								$_POST['ket_satuan'],
								$this->session->userdata('ses_id_karyawan'),
								$row->kode_kantor //$this->session->userdata('ses_kode_kantor')
							);
						}
					}
					
				}
				header('Location: '.base_url().'gl-pusat-satuan-produk-jasa');
			}
			else
			{
				header('Location: '.base_url().'');
			}
		}
	}
	
	public function hapus()
	{
		$id_satuan = ($this->uri->segment(2,0));
		$data_satuan = $this->M_gl_pst_satuan->get_satuan('id_satuan',$id_satuan);
		if(!empty($data_satuan))
		{
			$data_satuan = $data_satuan->row();
			$this->M_gl_pst_satuan->hapus('id_satuan',$id_satuan);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data satuan produk dan jasa '.$data_kat_produk->kode_kat_produk.' | '.$data_kat_produk->nama_kat_produk,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-pusat-satuan-produk-jasa');
	}
	
	function cek_satuan()
	{
		$hasil_cek = $this->M_gl_pst_satuan->get_satuan('kode_satuan',$_POST['kode_satuan']);
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

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_pst_satuan.php */