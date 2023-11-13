<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_req extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_karyawan'));
		
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
							AND (A.no_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nik_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%')
							AND A.lamar_via <> ''";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.lamar_via <> ''";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-recruitment?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-recruitment/');
				$config['total_rows'] = $this->M_gl_karyawan->count_karyawan_limit($cari)->JUMLAH;
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
				
				$list_karyawan_req = $this->M_gl_karyawan->list_karyawan_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$no_kry = $this->M_gl_karyawan->get_no_karyawan();
				$no_kry = $no_kry->NO_KRY;
				$msgbox_title = " Recruitment Karyawan";
				
				$data = array('page_content'=>'gl_admin_req_karyawan','halaman'=>$halaman,'list_karyawan_req'=>$list_karyawan_req,'msgbox_title' => $msgbox_title,'no_kry' => $no_kry);
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
				//echo $_POST['lamar_via'];
				if (!empty($_POST['stat_edit']))
				{	
					
					if (empty($_FILES['foto']['name']))
					{
						$this->M_gl_karyawan->edit
						(
							
							$_POST['id_karyawan'],
							"",//$_POST['id_jabatan'],
							$_POST['no_karyawan'],
							$_POST['nik_karyawan'],
							$_POST['nama_karyawan'],
							$_POST['pnd'],
							$_POST['tlp'],
							$_POST['email'],
							$_POST['tmp_lahir'],
							$_POST['tgl_lahir'],
							$_POST['kelamin'],
							$_POST['sts_nikah'],
							"",
							"",
							$_POST['alamat'],
							$_POST['ket_karyawan'],
							$_POST['tgl_diterima'],
							'BELUM PROSES',
							$_POST['isDokter'],
							$_POST['lamar_via'],
							"", //$_POST['user'],
							"", //$_POST['pass'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					else
					{	
						/*DAPATKAN DATA KARYAWAN*/
							$data_karyawan = $this->M_gl_karyawan->get_karyawan("id_karyawan",$_POST['id_karyawan']);
							$data_karyawan = $data_karyawan->row();
						/*DAPATKAN DATA KARYAWAN*/
				
						/*AMBIL EXT*/
							$path = $_FILES['foto']['name'];
							$ext = pathinfo($path, PATHINFO_EXTENSION);
						/*AMBIL EXT*/

						/*PROSES UPLOAD*/
							$lokasi_gambar_disimpan = 'assets/global/karyawan/';					
							$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,$_POST['id_karyawan'].'.'.$ext,$data_karyawan->avatar);
						/*PROSES UPLOAD*/
						
						$this->M_gl_karyawan->edit
						(
							
							$_POST['id_karyawan'],
							"",//$_POST['id_jabatan'],
							$_POST['no_karyawan'],
							$_POST['nik_karyawan'],
							$_POST['nama_karyawan'],
							$_POST['pnd'],
							$_POST['tlp'],
							$_POST['email'],
							$_POST['tmp_lahir'],
							$_POST['tgl_lahir'],
							$_POST['kelamin'],
							$_POST['sts_nikah'],
							$_POST['id_karyawan'].'.'.$ext,
							$lokasi_gambar_disimpan,
							$_POST['alamat'],
							$_POST['ket_karyawan'],
							$_POST['tgl_diterima'],
							'BELUM PROSES',
							$_POST['isDokter'],
							$_POST['lamar_via'],
							"", //$_POST['user'],
							"", //$_POST['pass'],
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
							'Melakukan perubahan data recruitment karyawan '.$_POST['no_karyawan'].' | '.$_POST['nama_karyawan'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					if (empty($_FILES['foto']['name']))
					{
						$avatar = "";
						$lokasi_gambar_disimpan = "";
					}
					else
					{
						/*AMBIL EXT*/
							$path = $_FILES['foto']['name'];
							$ext = pathinfo($path, PATHINFO_EXTENSION);
						/*AMBIL EXT*/

						/*PROSES UPLOAD*/
							$lokasi_gambar_disimpan = 'assets/global/karyawan/';
							$avatar = str_replace(" ","",$_FILES['foto']['name']);							
							
							$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,$avatar,"");
						/*PROSES UPLOAD*/
					}
					
					$this->M_gl_karyawan->simpan
					(
						"",//$_POST['id_jabatan'],
						$_POST['no_karyawan'],
						$_POST['nik_karyawan'],
						$_POST['nama_karyawan'],
						$_POST['pnd'],
						$_POST['tlp'],
						$_POST['email'],
						$_POST['tmp_lahir'],
						$_POST['tgl_lahir'],
						$_POST['kelamin'],
						$_POST['sts_nikah'],
						$avatar,
						$lokasi_gambar_disimpan,
						$_POST['alamat'],
						$_POST['ket_karyawan'],
						$_POST['tgl_diterima'],
						'BELUM PROSES',
						$_POST['isDokter'],
						$_POST['lamar_via'],
						"", //$_POST['user'],
						"", //$_POST['pass'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					
					);
					
				}
				
				header('Location: '.base_url().'gl-admin-recruitment');
		
			}
		}			
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$id = ($this->uri->segment(2,0));
		$data_karyawan = $this->M_gl_karyawan->get_karyawan('id_karyawan',$id);
		if(!empty($data_karyawan))
		{
			$data_karyawan = $data_karyawan->row();
			$this->M_gl_karyawan->hapus($id);
			
			/*HAPUS GAMBAR DARI SERVER*/
				$lokasi_gambar_disimpan = 'assets/global/karyawan/';
				$avatar = $data_karyawan->avatar;							
				
				$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,"",$avatar);
			/*HAPUS GAMBAR DARI SERVER*/
						
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data recruitment karyawan '.$data_karyawan->no_karyawan.' | '.$data_karyawan->nama_karyawan,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-recruitment');
	}
	
	function cek_karyawan()
	{
		$hasil_cek = $this->M_gl_karyawan->get_karyawan('no_karyawan',$_POST['no_karyawan']);
		echo $hasil_cek;
	}
	
	function ubah_proses_karyawan_req()
	{
		$hasil_cek = $this->M_gl_karyawan->ubah_proses_karyawan_req($_POST['id_karyawan'],$_POST['isAktif'],$_POST['nilai_ujian'],$_POST['ket_hasil_ujian']);
		
		if($hasil_cek == "BERHASIL")
		{
			echo $hasil_cek;
		}
		else
		{
			false;
		}
		
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */