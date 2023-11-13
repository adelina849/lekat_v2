<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_tr_keluar_operasional extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_tr_keluar_operasional'));
		
	}
	
	public function index()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_akun->get_cek_login($this->session->userdata('ses_user_admin'),md5(base64_decode($this->session->userdata('ses_pass_admin'))));
			
			if(!empty($cek_ses_login))
			{
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE 
								A.OTOPRS_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
								AND 
								(
									A.OTOPRS_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)	
								";
				}
				else
				{
					$cari = "WHERE A.OTOPRS_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				$config['first_url'] = site_url('admin-transaksi-keluar-operasional?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-transaksi-keluar-operasional/');
				$config['total_rows'] = $this->M_tr_keluar_operasional->count_tr_keluar_operasional_limit($cari)->JUMLAH;
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
				
				$list_tr_keluar_operasional = $this->M_tr_keluar_operasional->list_tr_keluar_operasional_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$data = array('page_content'=>'king_admin_tr_keluar_operasional','halaman'=>$halaman,'list_tr_keluar_operasional'=>$list_tr_keluar_operasional);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
	
	
	public function simpan()
	{
		if (!empty($_POST['stat_edit']))
		{	
			$this->M_tr_keluar_operasional->edit
			(
			
				$_POST['stat_edit'],
				$_POST['OTOPRS_KAT'],
				$_POST['OTOPRS_NOMOR'],
				$_POST['OTOPRS_NAMA'],
				$_POST['OTOPRS_TAHUN'],
				$_POST['OTOPRS_BULAN'],
				$_POST['OTOPRS_DTTRAN'],
				$_POST['OTOPRS_DISERAHKAN'],
				$_POST['OTOPRS_DITERIMA'],
				$_POST['OTOPRS_NOMINAL'],
				$_POST['OTOPRS_PERIHAL'],
				$_POST['OTOPRS_KET'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-transaksi-keluar-operasional');
			
			
		}
		else
		{
			$this->M_tr_keluar_operasional->simpan
			(
			
				$_POST['OTOPRS_KAT'],
				$_POST['OTOPRS_NOMOR'],
				$_POST['OTOPRS_NAMA'],
				$_POST['OTOPRS_TAHUN'],
				$_POST['OTOPRS_BULAN'],
				$_POST['OTOPRS_DTTRAN'],
				$_POST['OTOPRS_DISERAHKAN'],
				$_POST['OTOPRS_DITERIMA'],
				$_POST['OTOPRS_NOMINAL'],
				$_POST['OTOPRS_PERIHAL'],
				$_POST['OTOPRS_KET'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-transaksi-keluar-operasional');
		}
		
		//echo $_POST['OTOPRS_KAT'];
	}
	
	public function hapus()
	{
		$OTOPRS_ID = $this->uri->segment(2,0);
		$this->M_tr_keluar_operasional->hapus($this->session->userdata('ses_kode_kantor'),$OTOPRS_ID);
		
			$this->M_log->simpan(
				"DELETE",
				$this->session->userdata('ses_nama_karyawan'),
				"Melakukan penghapusan Pengeluran Operasional dengan id : ".$OTOPRS_ID." ",
				$this->session->userdata('ses_kode_kantor')
			);
		
		header('Location: '.base_url().'admin-transaksi-keluar-operasional');
	}
	
	function cek_tr_keluar_operasional()
	{	
		$OTOPRS_NAMA = $_POST['OTOPRS_NAMA'];
		$hasil_cek = $this->M_tr_keluar_operasional->get_tr_keluar_operasional('OTOPRS_NAMA',$OTOPRS_NAMA);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */