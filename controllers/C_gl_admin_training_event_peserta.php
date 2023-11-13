<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_training_event_peserta extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_karyawan_training','M_gl_training','M_gl_training_event'));
		
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
				$id_training = $this->uri->segment(2,0);
				$data_training = $this->M_gl_training->get_training('id_training',$id_training);
				if(!empty($data_training)) //GET INFO TRAINING
				{
					$data_training = $data_training->row();
					
					$id_training_event = $this->uri->segment(3,0);
					$data_training_event = $this->M_gl_training_event->get_training_event('id_training_event',$id_training_event);
					
					if(!empty($data_training_event))  //GET INFO EVENT TRAINING
					{
						//MULAI PROSES
						$data_training_event = $data_training_event->row();
						if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
						{
							$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.nama_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%') ";
						}
						else
						{
							$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
						}
						
						$this->load->library('pagination');
					
						$config['first_url'] = site_url('gl-admin-training-event-peserta/'.$id_training.'/'.'/'.$id_training_event.'?'.http_build_query($_GET));
						$config['base_url'] = site_url('gl-admin-training-event-peserta/'.$id_training.'/'.'/'.$id_training_event);
						
						$config['total_rows'] = $this->M_gl_karyawan_training->count_karyawan_training_limit($cari)->JUMLAH;
						$config['uri_segment'] = 4;	
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
						
						$list_training_event_peserta = $this->M_gl_karyawan_training->list_karyawan_training_limit($id_training_event,$cari,$config['per_page'],$this->uri->segment(4,0));
						
						$msgbox_title = " Data Peserta ".$data_training_event->nama_event;
						
						$data = array('page_content'=>'gl_admin_training_event_peserta','halaman'=>$halaman,'list_training_event_peserta'=>$list_training_event_peserta,'msgbox_title' => $msgbox_title,'data_training' => $data_training,'data_training_event' => $data_training_event);
						$this->load->view('admin/container',$data);
					}
					else
					{
						header('Location: '.base_url().'gl-admin-training-event/'.$id_training);
					}
				}
				else
				{
					header('Location: '.base_url().'gl-admin-training');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function cek_peserta()
	{	/*
		$id_jabatan = $_POST['id_jabatan'];
		$id_fasilitas = $_POST['id_fasilitas'];
		$hasil_cek = $this->M_gl_hak_akses->get_akses_fasilitas($id_jabatan,$id_fasilitas);
		echo $hasil_cek;
		*/
		
		$query = $this->M_gl_karyawan_training->get_karyawan_training($_POST['id_karyawan'],$_POST['id_training_event']);
		if(!empty($query))
		{
			$this->M_gl_karyawan_training->hapus($_POST['id_karyawan'],$_POST['id_training_event']);
			echo('Belum Terdaftar');   
		}
		else
		{
			////simpan($id_jabatan,$id_fasilitas,$id_user,$kode_kantor)
			
			$this->M_gl_karyawan_training->simpan
			(
				$_POST['id_karyawan'],
				$_POST['id_training'],
				$_POST['id_training_event'],
				$_POST['nilai'],
				$_POST['ket_karyawan_training'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			
			);
			
			echo('Terdaftar');
		}
	}
	
	function cek_nilai()
	{	
		$this->M_gl_karyawan_training->edit($_POST['id_karyawan'],$_POST['id_training'],$_POST['id_training_event'],$_POST['nilai'],$_POST['ket_karyawan_training']);
		echo('Berhasil');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */