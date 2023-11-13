<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_req_ubah extends CI_Controller {

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
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
				}
				
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
						WHERE 
						A.kode_kantor LIKE '%".$kode_kantor."%'
						AND A.sts_penjualan IN ('SELESAI','PEMBAYARAN')
						AND A.isApprove > 0
						AND DATE(COALESCE(A.tgl_salah,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						AND
						(
							COALESCE(A.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							OR COALESCE(A.petugas_salah,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							OR A.ket_salah LIKE '%".str_replace("'","",$_GET['cari'])."%'
						)
						";
				}
				else
				{
					$cari = "
						WHERE 
						A.kode_kantor LIKE '%".$kode_kantor."%'
						AND A.sts_penjualan IN ('SELESAI','PEMBAYARAN')
						AND A.isApprove > 0
						AND DATE(COALESCE(A.tgl_salah,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				
				$order_by = "ORDER BY A.tgl_ins DESC";
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-pusat-aktifitas-request-perubahan?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-pusat-aktifitas-request-perubahan/');
				$config['total_rows'] = $this->M_gl_log_aktifitas->count_request_perubahan($cari)->JUMLAH;
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
				
				$list_aktifitas_req_perubahan = $this->M_gl_log_aktifitas->list_request_perubahan($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
				
				$msgbox_title = " Laporan Request Perubahan";
				
				$data = array('page_content'=>'gl_pusat_aktifitas_req_ubah','halaman'=>$halaman,'list_aktifitas_req_perubahan'=>$list_aktifitas_req_perubahan,'msgbox_title' => $msgbox_title,'list_kantor'=>$list_kantor);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_pst_req_ubah.php */