<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_d_jadwal_jemput_zakat extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_jadwal_jemput_zakat','M_d_jadwal_jemput_zakat','M_lokasi'));
		
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
				$JMPT_ID = $this->uri->segment(2,0);
				$data_jadwal_jemput_zakat = $this->M_jadwal_jemput_zakat->list_jemput_zakat_limit(" WHERE A.JMPT_ID = '".$JMPT_ID."'",1,0);
				if(!empty($data_jadwal_jemput_zakat))
				{
					$data_jadwal_jemput_zakat = $data_jadwal_jemput_zakat->row();

					
					$this->load->library('pagination');
					//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
					//$config['base_url'] = base_url().'admin/jabatan/';
					
					$config['first_url'] = site_url('admin-lokasi-jadwal-jemput-zakat/'.$JMPT_ID.'?'.http_build_query($_GET));
					$config['base_url'] = site_url('admin-lokasi-jadwal-jemput-zakat/'.$JMPT_ID.'/');
					$config['total_rows'] = $this->M_d_jadwal_jemput_zakat->count_d_jadwal_jemput_zakat_limit("")->JUMLAH;
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
					
					$list_d_jadwal_jemput_zakat_limit = $this->M_d_jadwal_jemput_zakat->list_d_jadwal_jemput_zakat_limit("",$config['per_page'],$this->uri->segment(3,0));
					$list_prov = $this->M_lokasi->list_prov('',100,0);
					
					$data = array('page_content'=>'king_admin_d_jemput_zakat','halaman'=>$halaman,'list_d_jadwal_jemput_zakat_limit'=>$list_d_jadwal_jemput_zakat_limit,'list_prov' => $list_prov);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'admin-jadwal-jemput-zakat');
				}
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
	
	public function tampilkan_kabkot()
	{
		$province_id = $_POST['POS_PROV'];
		$cari = " WHERE province_id = '".$province_id."'";
		$list_kabkot = $this->M_lokasi->list_kabkot($cari,1000,0);
		
		if (!empty($list_kabkot))
		{
			$list_result = $list_kabkot->result();
			foreach($list_result as $row)
			{
				echo '<option value="'.$row->id.'">'.$row->name.'</option>';
			}
		}
	}
	
	public function tampilkan_kec()
	{
		$kabkot_id = $_POST['POS_KAB'];
		$cari = " WHERE kabkot_id = '".$kabkot_id."'";
		$list_kec = $this->M_lokasi->list_kecamatan($cari,1000,0);
		if (!empty($list_kec))
		{
			$list_result = $list_kec->result();
			foreach($list_result as $row)
			{
				echo '<option value="'.$row->id.'">'.$row->name.'</option>';
			}
		}
	}
	
	public function tampilkan_desa()
	{
		$kec_id = $_POST['POS_KEC'];
		$cari = " WHERE kec_id = '".$kec_id."'";
		$list_desa = $this->M_lokasi->list_desa($cari,1000,0);
		if (!empty($list_desa))
		{
			$list_result = $list_desa->result();
			foreach($list_result as $row)
			{
				echo '<option value="'.$row->id.'">'.$row->name.'</option>';
			}
		}
	}
	
	public function simpan()
	{
		if (!empty($_POST['stat_edit']))
		{	
			$this->M_d_jadwal_jemput_zakat->edit
			(
				$_POST['stat_edit'],
				$_POST['JMPT_ID'],
				$_POST['DJMPT_NAMALOK'],
				$_POST['DJMPT_KET'],
				$_POST['DJMPT_PROV'],
				$_POST['DJMPT_KABKOT'],
				$_POST['DJMPT_KEC'],
				$_POST['DJMPT_DESA'],
				$_POST['DJMPT_LATI'],
				$_POST['DJMPT_LONGI'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-lokasi-jadwal-jemput-zakat/'.$_POST['JMPT_ID']);
			
			
		}
		else
		{
			$this->M_d_jadwal_jemput_zakat->simpan
			(
			
				$_POST['JMPT_ID'],
				$_POST['DJMPT_NAMALOK'],
				$_POST['DJMPT_KET'],
				$_POST['POS_PROV'],
				$_POST['POS_KAB'],
				$_POST['POS_KEC'],
				$_POST['POS_DESA'],
				$_POST['DJMPT_LATI'],
				$_POST['DJMPT_LONGI'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-lokasi-jadwal-jemput-zakat/'.$_POST['JMPT_ID']);
		}
		
		//echo 'ade';*/
	}
	
	function hapus()
	{
		/*$POS_ID = $this->uri->segment(2,0);
		$this->M_d_jadwal_jemput_zakat->hapus($this->session->userdata('ses_kode_kantor'),$POS_ID);
		header('Location: '.base_url().'admin-pos');*/
		
		$JMPT_ID = $this->uri->segment(2,0);
		$DJMPT_ID = $this->uri->segment(3,0);
		$this->M_d_jadwal_jemput_zakat->hapus($this->session->userdata('ses_kode_kantor'),$DJMPT_ID);
		
			$this->M_log->simpan(
				"DELETE",
				$this->session->userdata('ses_nama_karyawan'),
				"Melakukan penghapusan lokasi penjemputan zakat dengan id : ".$POS_ID." ",
				$this->session->userdata('ses_kode_kantor')
			);
		
		
		header('Location: '.base_url().'admin-lokasi-jadwal-jemput-zakat/'.$JMPT_ID);
	}
	
	// function cek_pos()
	// {	
		// $POS_KODE = $_POST['POS_KODE'];
		// $hasil_cek = $this->M_d_jadwal_jemput_zakat->get_pos('POS_KODE',$POS_KODE);
		// echo $hasil_cek;
	// }
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */