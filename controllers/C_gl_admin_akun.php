<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_akun extends CI_Controller {

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
							AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN')";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN')";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-akun?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-akun/');
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
				
				$msgbox_title = " Pemberian Akun Karyawan";
				
				$data = array('page_content'=>'gl_admin_akun','halaman'=>$halaman,'list_karyawan_req'=>$list_karyawan_req,'msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	function cek_karyawan()
	{
		$hasil_cek = $this->M_gl_karyawan->get_karyawan('user',$_POST['user']);
		echo $hasil_cek;
	}
	
	function beri_akun()
	{
		$hasil_cek = $this->M_gl_karyawan->pemberian_akun($_POST['id_karyawan'],$_POST['user'],base64_encode(md5($_POST['pass'])));
		
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