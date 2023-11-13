<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_stock_produk extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_pengaturan'));
		
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
				$tgl_stock = $this->uri->segment(2,0);
				$data_stock = $this->M_gl_pengaturan->get_stock_produk(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(tgl_stock) = '".$tgl_stock."'");
				if(!empty($data_stock))
				{
					$data_stock = $data_stock->row();
					
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = str_replace("'","",$_GET['cari']);
					}
					else
					{
						$cari = "";
					}
					
					$this->load->library('pagination');
					
					$config['first_url'] = site_url('gl-admin-stock-so-detail/'.md5($data_stock->tgl_stock).'?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-stock-so-detail/'.md5($data_stock->tgl_stock));
					$config['total_rows'] = $this->M_gl_pengaturan->sum_detail_stock_produk($this->session->userdata('ses_kode_kantor'),$cari,$tgl_stock)->JUMLAH;
					$config['uri_segment'] = 3;	
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
					
					$list_detail_stock_so = $this->M_gl_pengaturan->list_detail_stock_produk($this->session->userdata('ses_kode_kantor'),$cari,$tgl_stock,$config['per_page'],$this->uri->segment(3,0));
					
					$msgbox_title = " Detail Table Hasil SO Tanggal ".($data_stock->tgl_stock);
					
					$data = array('page_content'=>'gl_admin_hasil_so','halaman'=>$halaman,'list_detail_stock_so'=>$list_detail_stock_so,'msgbox_title' => $msgbox_title);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-pengaturan-upload-excel-stock');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function hapus()
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
				$tgl_stock = $this->uri->segment(2,0);
				$data_stock = $this->M_gl_pengaturan->get_stock_produk(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(tgl_stock) = '".$tgl_stock."'");
				if(!empty($data_stock))
				{
					$data_stock = $data_stock->row();
					
					
					$this->M_gl_pengaturan->hapus_stuck_produk($this->session->userdata('ses_kode_kantor'),$tgl_stock);
			
					/* CATAT AKTIFITAS HAPUS*/
					if($this->session->userdata('catat_log') == 'Y')
					{
						$this->M_gl_log->simpan_log
						(
							$this->session->userdata('ses_id_karyawan'),
							'DELETE',
							'Melakukan penghapusan data detail stock opname '.$data_stock->tgl_stock.' | '.$data_jabatan->nama_jabatan,
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS HAPUS*/
					
					header('Location: '.base_url().'gl-admin-pengaturan-upload-excel-stock');
				}
				else
				{
					header('Location: '.base_url().'gl-admin-pengaturan-upload-excel-stock');
				}
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