<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_log_aktifitas extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_log_aktifitas'));
		
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
				if((!empty($_GET['dari'])) && ($_GET['dari']!= "")  )
				{
					$dari = $_GET['dari'];
					$sampai = $_GET['sampai'];
				}
				else
				{
					$dari = date("Y-m-d");
					$sampai = date("Y-m-d");
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND DATE(A.waktu) BETWEEN '".$dari."' AND '".$sampai."' 
							AND 
							(
								A.ket_aktifitas LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(A.aktifitas,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND DATE(A.waktu) BETWEEN '".$dari."' AND '".$sampai."' 
							";
				}
				$order_by = "ORDER BY A.waktu DESC";
				
				$jum_row = $this->M_gl_log_aktifitas->count_log_aktifitas_limit($cari)->JUMLAH;
				
				$this->load->library('pagination');
				
				$config['first_url'] = site_url('gl-admin-laporan-log-aktifitas?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-laporan-log-aktifitas/');
				$config['total_rows'] = $jum_row;
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
				
				$list_log_aktifitas = $this->M_gl_log_aktifitas->list_log_aktifitas_limit($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				
				$msgbox_title = " Laporan Log Aktifitas";
				
				if($config['per_page'] > $jum_row)
				{
					$jum_row_tampil = $jum_row;
				}
				else
				{
					$jum_row_tampil = $config['per_page'];
				}
				
				$offset = (integer) $this->uri->segment(2,0);
				$max_data = $offset + $jum_row_tampil;
				$offset = $offset + 1;
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$sum_pesan = "Menampilkan ".$jum_row_tampil." Dari ".$jum_row." Data Pencarian ".str_replace("'","",$_GET['cari'])." dimulai dari data ke ".$offset." Sampai ".$max_data;
				}
				else
				{
					$sum_pesan = "Menampilkan ".$jum_row_tampil." Dari ".$jum_row." Data dimulai dari data ke ".$offset." Sampai ".$max_data;
				}
				
				$data = array('page_content'=>'gl_admin_log_aktifitas','halaman'=>$halaman,'list_log_aktifitas'=>$list_log_aktifitas,'msgbox_title' => $msgbox_title, 'sum_pesan' => $sum_pesan);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_log_aktifitas.php */