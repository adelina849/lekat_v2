<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_training extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_training','M_gl_training_event','M_gl_karyawan_training'));
		
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
							AND A.nama_training LIKE '%".str_replace("'","",$_GET['cari'])."%'";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/training?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/training/';
				
				$config['first_url'] = site_url('gl-admin-training?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-training/');
				$config['total_rows'] = $this->M_gl_training->count_training_limit($cari)->JUMLAH;
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
				
				$list_training = $this->M_gl_training->list_training_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Training";
				
				$data = array('page_content'=>'gl_admin_training','halaman'=>$halaman,'list_training'=>$list_training,'msgbox_title' => $msgbox_title);
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
					$this->M_gl_training->edit
					(
						$_POST['stat_edit'],
						$_POST['kode_training'],
						$_POST['nama_training'],
						$_POST['point_training'],
						$_POST['hirarki'],
						$_POST['ket_training'],
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
							'Melakukan perubahan data training '.$_POST['kode_training'].' | '.$_POST['nama_training'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$this->M_gl_training->simpan
					(
						$_POST['kode_training'],
						$_POST['nama_training'],
						$_POST['point_training'],
						$_POST['hirarki'],
						$_POST['ket_training'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					
					);
				}
				
				header('Location: '.base_url().'gl-admin-training');
		
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}			
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$id = ($this->uri->segment(2,0));
		$data_training = $this->M_gl_training->get_training('id_training',$id);
		if(!empty($data_training))
		{
			$data_training = $data_training->row();
			
			//HAPUS TRAINING
				$this->M_gl_training->hapus($id);
			//HAPUS TRAINING
			
			//HAPUS EVENT
				$this->M_gl_training_event->hapus("id_training",$id);
			//HAPUS EVENT
			
			//HAPUS PESERTA
				$this->M_gl_karyawan_training->hapus_2("id_training",$id);
			//HAPUS PESERTA
			
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data training '.$data_training->kode_training.' | '.$data_training->nama_training,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-training');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_training.php */