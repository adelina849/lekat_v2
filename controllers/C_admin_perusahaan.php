<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_perusahaan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_perusahaan','M_lokasi'));
		
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
				// $data = array('page_content'=>'king_jabatan');
				// $this->load->view('admin/container',$data);
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE A.PRSH_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND A.PRSH_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
				}
				else
				{
					$cari = "WHERE A.PRSH_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('admin-perusahaan?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-perusahaan/');
				$config['total_rows'] = $this->M_perusahaan->count_perusahaan_limit($cari)->JUMLAH;
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
				
				$list_perusahaan = $this->M_perusahaan->list_perusahaan_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				$list_prov = $this->M_lokasi->list_prov('',100,0);
				
				$data = array('page_content'=>'king_admin_perusahaan','halaman'=>$halaman,'list_perusahaan'=>$list_perusahaan,'list_prov' => $list_prov);
				$this->load->view('admin/container',$data);
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
			$this->M_perusahaan->edit
			(
				$_POST['stat_edit'],
				$_POST['PRSH_KODE'],
				$_POST['PRSH_KAT'],
				$_POST['PRSH_NAMA'],
				$_POST['PRSH_BIDANG'],
				$_POST['PRSH_KET'],
				$_POST['PRSH_TLP'],
				$_POST['PRSH_EMAIL'],
				$_POST['POS_PROV'],
				$_POST['POS_KAB'],
				$_POST['POS_KEC'],
				$_POST['POS_DESA'],
				$_POST['PRSH_ALMTDETAIL'],
				$_POST['PRSH_LONGI'],
				$_POST['PRSH_LATI'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-perusahaan');
			
			
		}
		else
		{
			$this->M_perusahaan->simpan
			(
			
				$_POST['PRSH_KODE'],
				$_POST['PRSH_KAT'],
				$_POST['PRSH_NAMA'],
				$_POST['PRSH_BIDANG'],
				$_POST['PRSH_KET'],
				$_POST['PRSH_TLP'],
				$_POST['PRSH_EMAIL'],
				$_POST['POS_PROV'],
				$_POST['POS_KAB'],
				$_POST['POS_KEC'],
				$_POST['POS_DESA'],
				$_POST['PRSH_ALMTDETAIL'],
				$_POST['PRSH_LONGI'],
				$_POST['PRSH_LATI'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			header('Location: '.base_url().'admin-perusahaan');
		}
		
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$PRSH_ID = $this->uri->segment(2,0);
		$this->M_perusahaan->hapus($this->session->userdata('ses_kode_kantor'),$PRSH_ID);
		
			$this->M_log->simpan(
				"DELETE",
				$this->session->userdata('ses_nama_karyawan'),
				"Melakukan penghapusan perusahaan dengan id : ".$PRSH_ID." ",
				$this->session->userdata('ses_kode_kantor')
			);
		
		header('Location: '.base_url().'admin-perusahaan');
	}
	
	function cek_perusahaan()
	{	
		$PRSH_NAMA = $_POST['PRSH_NAMA'];
		$hasil_cek = $this->M_perusahaan->get_perusahaan('PRSH_NAMA',$PRSH_NAMA);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */