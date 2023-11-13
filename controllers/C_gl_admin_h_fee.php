<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_h_fee extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_h_fee','M_gl_satuan'));
		
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
					$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND nama_h_fee LIKE '%".str_replace("'","",$_GET['cari'])."%'";
				}
				else
				{
					$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-pengaturan-fee?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-pengaturan-fee/');
				$config['total_rows'] = $this->M_gl_h_fee->count_h_fee_limit($cari)->JUMLAH;
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
				
				$order_by = " ORDER BY tgl_ins DESC";
				$list_h_fee = $this->M_gl_h_fee->list_h_fee_limit($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				$list_satuan = $this->M_gl_satuan->list_satuan_limit(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'",30,0);
				
				$msgbox_title = " Pengaturan Fee/Komisi";
				
				$data = array('page_content'=>'gl_admin_h_fee','halaman'=>$halaman,'list_h_fee'=>$list_h_fee,'msgbox_title' => $msgbox_title,'list_satuan' => $list_satuan);
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
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."' ");
			
			if(!empty($cek_ses_login))
			{
				if($_POST['isGlobal'] == 'on')
				{
					$isGlobal = 1;
				}
				else
				{
					$isGlobal = 0;
				}
				
				if (!empty($_POST['stat_edit']))
				{	
					$this->M_gl_h_fee->edit
					(
						$_POST['id_h_fee'],
						$_POST['nama_h_fee'],
						$_POST['diberikan_kepada'],
						
						$_POST['jenis_poli'],
						$_POST['optr_masa_kerja'],
						$_POST['masa_kerja_hari'],
						$_POST['isPasienBaru'],
						$_POST['isMembuatResep'],
						$_POST['min_jasa'],
						$_POST['optr_min_jasa'],
						$_POST['isKelipatanJasa'],
						$_POST['min_produk'],
						$_POST['optr_min_produk'],
						$_POST['isKelipatanProduk'],
						$_POST['nominal_tr_per_pasien'],
						$_POST['total_periksa_hari'],
						
						$_POST['kat_fee'],
						$isGlobal,
						$_POST['optr_kondisi'],
						$_POST['satuan_jual_fee'],
						str_replace(",","",$_POST['besar_penjualan']) , //$_POST['besar_penjualan'],
						$_POST['optr_fee'],
						str_replace(",","",$_POST['besar_fee']) , //$_POST['besar_fee'],
						$_POST['ket_fee'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					);
					
					/* CATAT AKTIFITAS EDIT*/
					if($this->session->userdata('catat_log') == 'Y')
					{
						$this->M_gl_log->simpan_log
						(
							$this->session->userdata('ses_id_karyawan'),
							'UPDATE',
							'Melakukan perubahan data Pengaturan Fee/Komisi '.$_POST['kode_h_fee'].' | '.$_POST['nama_h_fee'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$this->M_gl_h_fee->simpan
					(
						$_POST['nama_h_fee'],
						$_POST['diberikan_kepada'],
						
						$_POST['jenis_poli'],
						$_POST['optr_masa_kerja'],
						$_POST['masa_kerja_hari'],
						$_POST['isPasienBaru'],
						$_POST['isMembuatResep'],
						$_POST['min_jasa'],
						$_POST['optr_min_jasa'],
						$_POST['isKelipatanJasa'],
						$_POST['min_produk'],
						$_POST['optr_min_produk'],
						$_POST['isKelipatanProduk'],
						$_POST['nominal_tr_per_pasien'],
						$_POST['total_periksa_hari'],
						
						$_POST['kat_fee'],
						$isGlobal,
						$_POST['optr_kondisi'],
						$_POST['satuan_jual_fee'],
						str_replace(",","",$_POST['besar_penjualan']) , //$_POST['besar_penjualan'],
						$_POST['optr_fee'],
						str_replace(",","",$_POST['besar_fee']) , //$_POST['besar_fee'],
						$_POST['ket_fee'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				header('Location: '.base_url().'gl-admin-pengaturan-fee');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		$id_h_fee = ($this->uri->segment(2,0));
		$data_h_fee = $this->M_gl_h_fee->get_h_fee('id_h_fee',$id_h_fee);
		if(!empty($data_h_fee))
		{
			$data_h_fee = $data_h_fee->row();
			$this->M_gl_h_fee->hapus('id_h_fee',$id_h_fee);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data Pengaturan Fee/Komisi | '.$data_h_fee->nama_h_fee,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-pengaturan-fee');
	}
	
	function cek_h_fee()
	{
		$hasil_cek = $this->M_gl_h_fee->get_h_fee('nama_h_fee',$_POST['nama_h_fee']);
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->nama_h_fee;
		}
		else
		{
			return false;
		}
		
	}
	
	public function list_kat_h_fee_ditampilkan_di_combobox()
	{
		$diberikan_untuk = $_POST['kat_fee'];;
		if($diberikan_untuk == 'PRODUK')
		{
			echo '<option value="ITEM PRODUK">ITEM PRODUK</option>';
			echo '<option value="AKUMULASI PRODUK">AKUMULASI PRODUK</option>';
		}
		elseif($diberikan_untuk == 'JASA')
		{
			echo '<option value="ITEM JASA">ITEM JASA</option>';
			echo '<option value="AKUMULASI JASA">AKUMULASI JASA</option>';
		}
		elseif($diberikan_untuk == 'PASIEN BARU')
		{
			echo '<option value="AKUMULASI TRANSAKSI">AKUMULASI TRANSAKSI</option>';
		}
		elseif($diberikan_untuk == 'JUMLAH PENANGANAN')
		{
			echo '<option value="AKUMULASI TRANSAKSI">AKUMULASI TRANSAKSI</option>';
		}
				
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */