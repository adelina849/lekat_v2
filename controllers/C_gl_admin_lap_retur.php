<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_lap_retur extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_lap_retur'));
		
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
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
				if((!empty($_GET['berdasarkan'])) && ($_GET['berdasarkan']!= "")  )
				{
					$berdasarkan = "AND A.status_penjualan = '".str_replace("'","",$_GET['berdasarkan'])."'";
				}
				else
				{
					$berdasarkan = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE
								A.kode_kantor = '".$kode_kantor."'
								AND DATE(A.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."'
								".$berdasarkan."
								AND 
								(
									A.no_faktur LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.no_faktur_penjualan LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(D.nama_lengkap,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(E.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)";
				}
				else
				{
					$cari = "WHERE 
							A.kode_kantor = '".$kode_kantor."'
							AND DATE(A.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."'
							".$berdasarkan."
							";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-laporan-retur?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-laporan-retur/');
				$config['total_rows'] = 100000; //$this->M_gl_satuan->count_satuan_limit($cari)->JUMLAH;
				$config['uri_segment'] = 2;	
				$config['per_page'] = 100000;
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
				
				$list_laporan_retur = $this->M_gl_lap_retur->list_laporan_retur_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Laporan Retur";
				
				$data = array('page_content'=>'gl_admin_lap_retur','halaman'=>$halaman,'list_laporan_retur'=>$list_laporan_retur,'msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	public function excel_lap_retur()
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
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
				if((!empty($_GET['berdasarkan'])) && ($_GET['berdasarkan']!= "")  )
				{
					$berdasarkan = "AND A.status_penjualan = '".str_replace("'","",$_GET['berdasarkan'])."'";
				}
				else
				{
					$berdasarkan = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE
								A.kode_kantor = '".$kode_kantor."'
								AND DATE(A.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."'
								".$berdasarkan."
								AND 
								(
									A.no_faktur LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.no_faktur_penjualan LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(D.nama_lengkap,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(E.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)";
				}
				else
				{
					$cari = "WHERE 
							A.kode_kantor = '".$kode_kantor."'
							AND DATE(A.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."'
							".$berdasarkan."
							";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-laporan-retur?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-laporan-retur/');
				$config['total_rows'] = 100000; //$this->M_gl_satuan->count_satuan_limit($cari)->JUMLAH;
				$config['uri_segment'] = 2;	
				$config['per_page'] = 100000;
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
				
				$list_laporan_retur = $this->M_gl_lap_retur->list_laporan_retur_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Laporan Retur";
				
				$data = array('page_content'=>'gl_admin_excel_lap_retur','halaman'=>$halaman,'list_laporan_retur'=>$list_laporan_retur,'msgbox_title' => $msgbox_title,'dari' => $dari,'sampai'=>$sampai);
				$this->load->view('admin/page/gl_admin_excel_lap_retur.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */