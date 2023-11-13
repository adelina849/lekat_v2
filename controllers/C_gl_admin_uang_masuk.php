<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_uang_masuk extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_uang_masuk','M_gl_bank','M_gl_kode_akun','M_gl_costumer'));
		
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
					$cari = 
						"
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND A.id_uang_masuk = A.id_induk_uang_masuk
							AND DATE(A.tgl_uang_masuk) BETWEEN '".$dari."' AND '".$sampai."'
							AND (
									A.no_bukti LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.nama_uang_masuk LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR A.terima_dari LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(F.nama_lengkap,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
						";
				}
				else
				{
					$cari = 
						"
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND A.id_uang_masuk = A.id_induk_uang_masuk
							AND DATE(A.tgl_uang_masuk) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				
				$order_by = " ORDER BY A.id_induk_uang_masuk DESC, A.tgl_ins ASC ";
				
				$jum_row = $this->M_gl_uang_masuk->count_uang_masuk_limit($cari)->JUMLAH;
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-pemasukan?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-pemasukan/');
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
				
				//DATA BANK
				$cari_bank = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
				$list_bank = $this->M_gl_bank->list_bank_limit($cari_bank,1000,0);
				//DATA BANK
				
				//DATA COSTUMER
					$cari_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ORDER BY nama_lengkap ASC";
					$list_costumer = $this->M_gl_costumer->get_costumer_cari($cari_costumer);
				//DATA COSTUMER
				
				//DAPATKAN NO UANG masuk
				$get_no_uang_masuk = $this->M_gl_uang_masuk->get_no_uang_masuk($this->session->userdata('ses_kode_kantor'));
				if(!empty($get_no_uang_masuk))
				{
					$get_no_uang_masuk = $get_no_uang_masuk->row();
					$get_no_uang_masuk = $get_no_uang_masuk->no_bukti;
				}
				else
				{
					$get_no_uang_masuk = "";
				}
				//DAPATKAN NO UANG masuk
					
				
				$list_uang_masuk = $this->M_gl_uang_masuk->list_uang_masuk_limit_untuk_induk($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				
				//UNTUK AKUMULASI INFO
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
				//UNTUK AKUMULASI INFO
				
				//SUMAARY LIST
					$sum_uang_masuk = $this->M_gl_uang_masuk->sum_uang_masuk_limit($cari);
					if(!empty($sum_uang_masuk))
					{
						$sum_uang_masuk = $sum_uang_masuk->row();
						$sum_uang_masuk = $sum_uang_masuk->NOMINAL;
					}
					else
					{
						$sum_uang_masuk = 0;
					}
				//SUMAARY LIST
				
				
				$msgbox_title = " Pemasukan/Uang Masuk";
				
				$cari_coa = " 
							WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
						";
				
				
				$order_by_coa = " ORDER BY nama_kode_akun ASC ";
				$list_kode_akun = $this->M_gl_kode_akun->list_kode_akun_limit_order_by($cari_coa,$order_by_coa,1000,0);
				
				
				//GET KODE AKUN KHUSUS
				if((!empty($_GET['kode'])) && ($_GET['kode']!= "")  )
				{
					$kode_from_lap = str_replace("'","",$_GET['kode']);
				}
				else
				{
					$kode_from_lap = "";
				}
				
				$cari_kode_akun_from_lap = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND kode_akun = '".$kode_from_lap."' ";
				$get_kode_akun_from_lap = $this->M_gl_kode_akun->get_kode_akun_by_cari($cari_kode_akun_from_lap);
				//GET KODE AKUN KHUSUS
				
				$data = array('page_content'=>'gl_admin_uang_masuk','halaman'=>$halaman,'list_uang_masuk'=>$list_uang_masuk,'msgbox_title' => $msgbox_title,'sum_pesan' => $sum_pesan,'list_bank'=>$list_bank,'get_no_uang_masuk'=>$get_no_uang_masuk,'sum_uang_masuk'=>$sum_uang_masuk,'list_kode_akun' => $list_kode_akun
				,'get_kode_akun_from_lap' => $get_kode_akun_from_lap
				,'list_costumer' => $list_costumer);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function list_tarik_transaksi_ke_akun()
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
					$kode_kantor_cari = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor_cari = $this->session->userdata('ses_kode_kantor');
				}
				
				if((!empty($_GET['tgl_uang_masuk'])) && ($_GET['tgl_uang_masuk']!= "")  )
				{
					$tgl_uang_masuk_cari = $_GET['tgl_uang_masuk'];
				}
				else
				{
					$tgl_uang_masuk_cari = '';
				}
				
				$list_transfer_to_akun = $this->M_gl_uang_masuk->list_transfer_to_akun($kode_kantor_cari,$tgl_uang_masuk_cari);
				
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
					$cari = 
						"
							WHERE A.kode_kantor = '".$kode_kantor_cari."' 
							AND A.id_uang_masuk = A.id_induk_uang_masuk
							AND A.is_tf_to_akun = 'YA'
							AND DATE(A.tgl_uang_masuk) BETWEEN '".$dari."' AND '".$sampai."'
							AND (
									A.no_bukti LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.nama_uang_masuk LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR A.terima_dari LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
								
						";
				}
				else
				{
					$cari = 
						"
							WHERE A.kode_kantor = '".$kode_kantor_cari."'
							AND A.id_uang_masuk = A.id_induk_uang_masuk
							AND A.is_tf_to_akun = 'YA'
							AND DATE(A.tgl_uang_masuk) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				
				$order_by = " ORDER BY A.id_induk_uang_masuk DESC, A.tgl_ins ASC ";
				
				$jum_row = $this->M_gl_uang_masuk->count_uang_masuk_limit($cari)->JUMLAH;
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-pemasukan?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-pemasukan/');
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
				
				//DATA BANK
				$cari_bank = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
				$list_bank = $this->M_gl_bank->list_bank_limit($cari_bank,1000,0);
				//DATA BANK
				
				//DAPATKAN NO UANG masuk
				$get_no_uang_masuk = $this->M_gl_uang_masuk->get_no_uang_masuk($this->session->userdata('ses_kode_kantor'));
				if(!empty($get_no_uang_masuk))
				{
					$get_no_uang_masuk = $get_no_uang_masuk->row();
					$get_no_uang_masuk = $get_no_uang_masuk->no_bukti;
				}
				else
				{
					$get_no_uang_masuk = "";
				}
				//DAPATKAN NO UANG masuk
					
				
				$list_uang_masuk = $this->M_gl_uang_masuk->list_uang_masuk_limit_untuk_induk($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				
				//UNTUK AKUMULASI INFO
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
				//UNTUK AKUMULASI INFO
				
				//SUMAARY LIST
					$sum_uang_masuk = $this->M_gl_uang_masuk->sum_uang_masuk_limit($cari);
					if(!empty($sum_uang_masuk))
					{
						$sum_uang_masuk = $sum_uang_masuk->row();
						$sum_uang_masuk = $sum_uang_masuk->NOMINAL;
					}
					else
					{
						$sum_uang_masuk = 0;
					}
				//SUMAARY LIST
				
				
				$msgbox_title = " Transfer Hasil Transaksi Ke Akun/Kode Akun";
				
				$cari_coa = " 
							WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
						";
				
				
				$order_by_coa = " ORDER BY nama_kode_akun ASC ";
				$list_kode_akun = $this->M_gl_kode_akun->list_kode_akun_limit_order_by($cari_coa,$order_by_coa,1000,0);
				
				
				//LIST KANTOR
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
				//LIST KANTOR
				
				$data = array('page_content'=>'gl_admin_transfer_ke_akun','halaman'=>$halaman,'list_uang_masuk'=>$list_uang_masuk,'msgbox_title' => $msgbox_title,'sum_pesan' => $sum_pesan,'list_bank'=>$list_bank,'get_no_uang_masuk'=>$get_no_uang_masuk,'sum_uang_masuk'=>$sum_uang_masuk,'list_kode_akun' => $list_kode_akun,'list_kantor' => $list_kantor,'list_transfer_to_akun' => $list_transfer_to_akun);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function tambah_pemasukan()
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
				$id_uang_masuk = $this->uri->segment(2,0);
				$cari_uang_masuk = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_uang_masuk) = '".$id_uang_masuk."'";
				$get_data_uang_masuk = $this->M_gl_uang_masuk->list_uang_masuk_limit($cari_uang_masuk,"",1,0);
				if(!empty($get_data_uang_masuk))
				{
					$get_data_uang_masuk = $get_data_uang_masuk->row();
					
					
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = 
							"
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND MD5(A.id_induk_uang_masuk) = '".$id_uang_masuk."'
								AND MD5(A.id_uang_masuk) <> '".$id_uang_masuk."'
								AND (
										A.no_bukti LIKE '%".str_replace("'","",$_GET['cari'])."%' 
										OR A.nama_uang_masuk LIKE '%".str_replace("'","",$_GET['cari'])."%'
										OR A.terima_dari LIKE '%".str_replace("'","",$_GET['cari'])."%'
										OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%'
									)
							
								
							";
					}
					else
					{
						$cari = 
							"
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND MD5(A.id_induk_uang_masuk) = '".$id_uang_masuk."'
								AND MD5(A.id_uang_masuk) <> '".$id_uang_masuk."'
							";
					}
					
					$order_by = " ORDER BY A.tgl_ins DESC ";
					
					$jum_row = $this->M_gl_uang_masuk->count_uang_masuk_limit($cari)->JUMLAH;
					
					$this->load->library('pagination');
					//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
					//$config['base_url'] = base_url().'admin/jabatan/';
					
					$config['first_url'] = site_url('gl-admin-pemasukan-tambah/'.$id_uang_masuk.'??'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-pemasukan-tambah/'.$id_uang_masuk);
					$config['total_rows'] = $jum_row;
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
					
					//DATA BANK
					$cari_bank = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
					$list_bank = $this->M_gl_bank->list_bank_limit($cari_bank,1000,0);
					//DATA BANK
					
					//DAPATKAN NO UANG MASUK
					$get_no_uang_masuk = $this->M_gl_uang_masuk->get_no_uang_masuk($this->session->userdata('ses_kode_kantor'));
					if(!empty($get_no_uang_masuk))
					{
						$get_no_uang_masuk = $get_no_uang_masuk->row();
						$get_no_uang_masuk = $get_no_uang_masuk->no_bukti;
					}
					else
					{
						$get_no_uang_masuk = "";
					}
					//DAPATKAN NO UANG MASUK
						
					
					$list_uang_masuk = $this->M_gl_uang_masuk->list_uang_masuk_limit($cari,$order_by,$config['per_page'],$this->uri->segment(3,0));
					
					
					//UNTUK AKUMULASI INFO
						if($config['per_page'] > $jum_row)
						{
							$jum_row_tampil = $jum_row;
						}
						else
						{
							$jum_row_tampil = $config['per_page'];
						}
						
						$offset = (integer) $this->uri->segment(3,0);
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
					//UNTUK AKUMULASI INFO
					
					//SUMAARY LIST
						$sum_uang_masuk = $this->M_gl_uang_masuk->sum_uang_masuk_limit($cari);
						if(!empty($sum_uang_masuk))
						{
							$sum_uang_masuk = $sum_uang_masuk->row();
							$sum_uang_masuk = $sum_uang_masuk->NOMINAL;
						}
						else
						{
							$sum_uang_masuk = 0;
						}
					//SUMAARY LIST
					
					$msgbox_title = " Tambah Jurnal Pembalik (Uang Masuk)";
					
					$data = array('page_content'=>'gl_admin_uang_masuk_tambah','halaman'=>$halaman,'get_data_uang_masuk' => $get_data_uang_masuk,'list_uang_masuk'=>$list_uang_masuk,'msgbox_title' => $msgbox_title,'sum_pesan' => $sum_pesan,'list_bank'=>$list_bank,'get_no_uang_masuk'=>$get_no_uang_masuk,'sum_uang_masuk'=>$sum_uang_masuk);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-pemasukan');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function tampilkan_pemasukan_tambah()
	{
		$cari = 
				"
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
					AND (A.id_induk_uang_masuk) = '".$_POST['id_uang_masuk']."'
					AND (A.id_uang_masuk) <> '".$_POST['id_uang_masuk']."'
				";
		
		$order_by = " ORDER BY A.tgl_ins DESC ";
					
		$list_uang_masuk = $this->M_gl_uang_masuk->list_uang_masuk_limit($cari,$order_by,1000,0);
		
		if(!empty($list_uang_masuk))
		{
			
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead style="background-color:green;">
<tr>';
							
							echo '<th style="border:1px black solid;" width="20%">ACCOUNT</th>';
							echo '<th style="border:1px black solid;text-align:center;" colspan="2" width="45%">NAMA AKUN  DAN KETERANGAN</th>';
							echo '<th style="border:1px black solid;" width="20%">NOMINAL</th>';
							echo '<th style="border:1px black solid;" width="15%">AKSI</th>';
				echo '</tr>
</thead>';
				$list_result = $list_uang_masuk->result();
				$no =$this->uri->segment(3,0)+1;
				$sub_total = 0;
				echo '<tbody>';
				foreach($list_result as $row)
				{
				
					
						echo'<tr>';
						echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN.'</td>';
						//echo'<td style="border:1px black solid;" colspan="2">'.$row->NAMA_AKUN2.'<br/><i>'.$row->ket_uang_keluar.'</i></td>';
						echo'<td style="border:1px black solid;" colspan="2">'.$row->ket_uang_masuk.'</td>';
						
						//echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN2.'</td>';
						//echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN.'</td>';
						echo'<td style="text-align:right;font-weight:normal;border:1px black solid;">'.number_format($row->nominal,0,'.',',').'</td>';
						//echo'<td style="border:1px black solid;"></td>';
						echo'<td rowspan="1" style="border:1px black solid;">
						


<a class="confirm-btn" href="javascript:void(0)" onclick="hapus_debet(this)" id="'.$no.'-'.$row->id_uang_masuk.'" title = "Hapus Data '.$row->nama_uang_masuk.'" alt = "Hapus Data '.$row->nama_uang_masuk.'">
<i class="fa fa-trash"></i> HAPUS
</a>
						</td>';
						
					echo'</tr>';
					
					
						
						
						echo'<input type="hidden" id="id_uang_keluar_'.$no.'" name="id_uang_keluar_'.$no.'" value="'.$row->id_uang_masuk.'" />';
						
					$no++;
					$sub_total = $sub_total + $row->nominal;
					//sum_uang_keluar
				}
				
				echo'<tr>';
					echo'<td colspan="3" style="text-align:center;font-weight:bold;border:1px black solid;">TOTAL</td>';
					echo'<td  style="text-align:right;font-weight:bold;border:1px black solid;">'.number_format($sub_total,0,'.',',').'</td>';
					echo'<td  style="border:1px black solid;" colspan="2"></td>';
				echo'</tr>';
				
				echo '</tbody>';
			echo'</table>';
		}
		else
		{
			echo'<center>';
			echo'Tidak Ada Data Yang Ditampilkan !';
			echo'</center>';
		}
	//TABLE
	}
	
	function simpan_ajax()
	{
		$this->M_gl_uang_masuk->simpan
		(
			
			$_POST['id_kat_uang_masuk'],
			$_POST['id_induk_uang_masuk'],
			'', //$_POST['id_costumer'],
			'', //$_POST['id_supplier'],
			$_POST['id_bank'],
			'', //$_POST['id_retur_penjualan'],
			'', //$_POST['id_retur_pembelian'],
			'', //$_POST['id_karyawan'],
			'', //$_POST['id_d_assets'],
			$_POST['no_bukti'],
			$_POST['nama_uang_masuk'],
			$_POST['terima_dari'],
			$_POST['diterima_oleh'],
			$_POST['untuk'],
			str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
			$_POST['ket_uang_masuk'],
			$_POST['tgl_uang_masuk'],
			'', //$_POST['isTabungan'],
			'', //$_POST['isPiutang'],
			'', //$_POST['noPinjamanCos'],
			$this->session->userdata('ses_id_karyawan'),
			$this->session->userdata('ses_kode_kantor')
		);
		
		$cari = 
				"
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
					AND (A.id_induk_uang_masuk) = '".$_POST['id_induk_uang_masuk']."'
					AND (A.id_uang_masuk) <> '".$_POST['id_induk_uang_masuk']."'
				";
		
		$order_by = " ORDER BY A.tgl_ins DESC ";
					
		$list_uang_masuk = $this->M_gl_uang_masuk->list_uang_masuk_limit($cari,$order_by,1000,0);
		
		if(!empty($list_uang_masuk))
		{
			
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead style="background-color:green;">
<tr>';
							
							echo '<th style="border:1px black solid;" width="20%">ACCOUNT</th>';
							echo '<th style="border:1px black solid;text-align:center;" colspan="2" width="45%">NAMA AKUN  DAN KETERANGAN</th>';
							echo '<th style="border:1px black solid;" width="20%">NOMINAL</th>';
							echo '<th style="border:1px black solid;" width="15%">AKSI</th>';
				echo '</tr>
</thead>';
				$list_result = $list_uang_masuk->result();
				$no =$this->uri->segment(3,0)+1;
				$sub_total = 0;
				echo '<tbody>';
				foreach($list_result as $row)
				{
				
					
						echo'<tr>';
						echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN.'</td>';
						//echo'<td style="border:1px black solid;" colspan="2">'.$row->NAMA_AKUN2.'<br/><i>'.$row->ket_uang_keluar.'</i></td>';
						echo'<td style="border:1px black solid;" colspan="2">'.$row->ket_uang_masuk.'</td>';
						
						//echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN2.'</td>';
						//echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN.'</td>';
						echo'<td style="text-align:right;font-weight:normal;border:1px black solid;">'.number_format($row->nominal,0,'.',',').'</td>';
						//echo'<td style="border:1px black solid;"></td>';
						echo'<td rowspan="1" style="border:1px black solid;">
						


<a class="confirm-btn" href="javascript:void(0)" onclick="hapus_debet(this)" id="'.$no.'-'.$row->id_uang_masuk.'" title = "Hapus Data '.$row->nama_uang_masuk.'" alt = "Hapus Data '.$row->nama_uang_masuk.'">
<i class="fa fa-trash"></i> HAPUS
</a>
						</td>';
						
					echo'</tr>';
					
					
						
						
						echo'<input type="hidden" id="id_uang_keluar_'.$no.'" name="id_uang_keluar_'.$no.'" value="'.$row->id_uang_masuk.'" />';
						
					$no++;
					$sub_total = $sub_total + $row->nominal;
					//sum_uang_keluar
				}
				
				echo'<tr>';
					echo'<td colspan="3" style="text-align:center;font-weight:bold;border:1px black solid;">TOTAL</td>';
					echo'<td  style="text-align:right;font-weight:bold;border:1px black solid;">'.number_format($sub_total,0,'.',',').'</td>';
					echo'<td  style="border:1px black solid;" colspan="2"></td>';
				echo'</tr>';
				
				echo '</tbody>';
			echo'</table>';
		}
		else
		{
			echo'<center>';
			echo'Tidak Ada Data Yang Ditampilkan !';
			echo'</center>';
		}
	//TABLE
	}
	
	public function hapus_ajax()
	{
		$id_uang_masuk = ($_POST['id_uang_masuk']);
		$data_uang_masuk = $this->M_gl_uang_masuk->get_uang_masuk('(id_uang_masuk)',$id_uang_masuk);
		if(!empty($data_uang_masuk))
		{
			$data_uang_masuk = $data_uang_masuk->row();
			$this->M_gl_uang_masuk->hapus('(id_uang_masuk)',$id_uang_masuk);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data uang masuk '.$data_uang_masuk->no_bukti.' | '.$data_uang_masuk->ket_uang_masuk,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		$cari = 
				"
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
					AND (A.id_induk_uang_masuk) = '".$_POST['id_induk_uang_masuk']."'
					AND (A.id_uang_masuk) <> '".$_POST['id_induk_uang_masuk']."'
				";
		
		$order_by = " ORDER BY A.tgl_ins DESC ";
					
		$list_uang_masuk = $this->M_gl_uang_masuk->list_uang_masuk_limit($cari,$order_by,1000,0);
		
		if(!empty($list_uang_masuk))
		{
			
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead style="background-color:green;">
<tr>';
							
							echo '<th style="border:1px black solid;" width="20%">ACCOUNT</th>';
							echo '<th style="border:1px black solid;text-align:center;" colspan="2" width="45%">NAMA AKUN  DAN KETERANGAN</th>';
							echo '<th style="border:1px black solid;" width="20%">NOMINAL</th>';
							echo '<th style="border:1px black solid;" width="15%">AKSI</th>';
				echo '</tr>
</thead>';
				$list_result = $list_uang_masuk->result();
				$no =$this->uri->segment(3,0)+1;
				$sub_total = 0;
				echo '<tbody>';
				foreach($list_result as $row)
				{
				
					
						echo'<tr>';
						echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN.'</td>';
						//echo'<td style="border:1px black solid;" colspan="2">'.$row->NAMA_AKUN2.'<br/><i>'.$row->ket_uang_keluar.'</i></td>';
						echo'<td style="border:1px black solid;" colspan="2">'.$row->ket_uang_masuk.'</td>';
						
						//echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN2.'</td>';
						//echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN.'</td>';
						echo'<td style="text-align:right;font-weight:normal;border:1px black solid;">'.number_format($row->nominal,0,'.',',').'</td>';
						//echo'<td style="border:1px black solid;"></td>';
						echo'<td rowspan="1" style="border:1px black solid;">
						


<a class="confirm-btn" href="javascript:void(0)" onclick="hapus_debet(this)" id="'.$no.'-'.$row->id_uang_masuk.'" title = "Hapus Data '.$row->nama_uang_masuk.'" alt = "Hapus Data '.$row->nama_uang_masuk.'">
<i class="fa fa-trash"></i> HAPUS
</a>
						</td>';
						
					echo'</tr>';
					
					
						
						
						echo'<input type="hidden" id="id_uang_keluar_'.$no.'" name="id_uang_keluar_'.$no.'" value="'.$row->id_uang_masuk.'" />';
						
					$no++;
					$sub_total = $sub_total + $row->nominal;
					//sum_uang_keluar
				}
				
				echo'<tr>';
					echo'<td colspan="3" style="text-align:center;font-weight:bold;border:1px black solid;">TOTAL</td>';
					echo'<td  style="text-align:right;font-weight:bold;border:1px black solid;">'.number_format($sub_total,0,'.',',').'</td>';
					echo'<td  style="border:1px black solid;" colspan="2"></td>';
				echo'</tr>';
				
				echo '</tbody>';
			echo'</table>';
		}
		else
		{
			echo'<center>';
			echo'Tidak Ada Data Yang Ditampilkan !';
			echo'</center>';
		}
	}
	
	public function print_bukti_uang_masuk()
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
				$id_uang_masuk = $this->uri->segment(2,0);
				$cari_uang_masuk = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.id_uang_masuk) = '".$id_uang_masuk."'";
				$get_uang_masuk = $this->M_gl_uang_masuk->list_uang_masuk_limit($cari_uang_masuk,"",1,0);
				if(!empty($get_uang_masuk))
				{
					$get_uang_masuk = $get_uang_masuk->row();
					
					
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = 
							"
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND (A.id_induk_uang_masuk) = '".$id_uang_masuk."'
								AND (A.id_uang_masuk) <> '".$id_uang_masuk."'
								AND (
										A.no_bukti LIKE '%".str_replace("'","",$_GET['cari'])."%' 
										OR A.nama_uang_masuk LIKE '%".str_replace("'","",$_GET['cari'])."%'
										OR A.terima_dari LIKE '%".str_replace("'","",$_GET['cari'])."%'
										OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%'
									)
							
								
							";
					}
					else
					{
						$cari = 
							"
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND (A.id_induk_uang_masuk) = '".$id_uang_masuk."'
								AND (A.id_uang_masuk) <> '".$id_uang_masuk."'
							";
					}
					
					$order_by = " ORDER BY A.tgl_ins DESC ";
					
					
					$list_uang_masuk = $this->M_gl_uang_masuk->list_uang_masuk_limit($cari,$order_by,100,0);
					
					
					
					
					$msgbox_title = " Rincian Pemasukan";
					
					$data = array('page_content'=>'gl_admin_uang_masuk_tambah','get_uang_masuk' => $get_uang_masuk,'list_uang_masuk'=>$list_uang_masuk);
					
					//$this->load->view('admin/container',$data);
					$this->load->view('admin/page/gl_admin_print_bukti_uang_masuk.html',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-pemasukan');
				}
				
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
				if (!empty($_POST['stat_edit']))
				{	
					$this->M_gl_uang_masuk->edit
					(
						$_POST['stat_edit'],
						$_POST['id_kat_uang_masuk'],
						$_POST['id_costumer'],
						'', //$_POST['id_supplier'],
						$_POST['id_bank'],
						'', //$_POST['id_retur_penjualan'],
						'', //$_POST['id_retur_pembelian'],
						'', //$_POST['id_karyawan'],
						'', //$_POST['id_d_assets'],
						$_POST['no_bukti'],
						$_POST['nama_uang_masuk'],
						$_POST['terima_dari'],
						$_POST['diterima_oleh'],
						$_POST['untuk'],
						str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
						$_POST['ket_uang_masuk'],
						$_POST['tgl_uang_masuk'],
						'', //$_POST['isTabungan'],
						'', //$_POST['isPiutang'],
						'', //$_POST['noPinjamanCos'],
						$_POST['isAwal'],
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
							'Melakukan perubahan data uang masuk '.$_POST['no_bukti'].' | '.$_POST['nama_uang_masuk'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					if((!empty($_POST['id_induk_uang_masuk'])) && ($_POST['id_induk_uang_masuk']!= "")  )
					{
						$this->M_gl_uang_masuk->simpan
						(
							
							$_POST['id_kat_uang_masuk'],
							$_POST['id_induk_uang_masuk'],
							$_POST['id_costumer'],
							'', //$_POST['id_supplier'],
							$_POST['id_bank'],
							'', //$_POST['id_retur_penjualan'],
							'', //$_POST['id_retur_pembelian'],
							'', //$_POST['id_karyawan'],
							'', //$_POST['id_d_assets'],
							$_POST['no_bukti'],
							$_POST['nama_uang_masuk'],
							$_POST['terima_dari'],
							$_POST['diterima_oleh'],
							$_POST['untuk'],
							str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
							$_POST['ket_uang_masuk'],
							$_POST['tgl_uang_masuk'],
							'', //$_POST['isTabungan'],
							'', //$_POST['isPiutang'],
							'', //$_POST['noPinjamanCos'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					else
					{
						$this->M_gl_uang_masuk->simpan_utama
						(
							
							$_POST['id_kat_uang_masuk'],
							$_POST['id_costumer'],
							'', //$_POST['id_supplier'],
							$_POST['id_bank'],
							'', //$_POST['id_retur_penjualan'],
							'', //$_POST['id_retur_pembelian'],
							'', //$_POST['id_karyawan'],
							'', //$_POST['id_d_assets'],
							$_POST['is_tf_to_akun'],
							$_POST['no_bukti'],
							$_POST['nama_uang_masuk'],
							$_POST['terima_dari'],
							$_POST['diterima_oleh'],
							$_POST['untuk'],
							str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
							$_POST['ket_uang_masuk'],
							$_POST['tgl_uang_masuk'],
							'', //$_POST['isTabungan'],
							'', //$_POST['isPiutang'],
							'', //$_POST['noPinjamanCos'],
							$_POST['isAwal'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					
				}
				//header('Location: '.base_url().'gl-admin-pemasukan');
				if((!empty($_POST['id_induk_uang_masuk'])) && ($_POST['id_induk_uang_masuk']!= "")  )
				{
					if($_POST['is_tf_to_akun'] == 'YA')
					{
						header('Location: '.base_url().'gl-admin-transfer-ke-akun?stat_edit=&kode_kantor='.$this->session->userdata('ses_kode_kantor').'&tgl_uang_masuk='.$_POST['tgl_uang_masuk'].'');
					}
					else
					{
						header('Location: '.base_url().'gl-admin-pemasukan-tambah/'.md5($_POST['id_induk_uang_masuk']));
					}
					
				}
				else
				{
					if($_POST['is_tf_to_akun'] == 'YA')
					{
						header('Location: '.base_url().'gl-admin-transfer-ke-akun?stat_edit=&kode_kantor='.$this->session->userdata('ses_kode_kantor').'&tgl_uang_masuk='.$_POST['tgl_uang_masuk'].'');
					}
					else
					{
						header('Location: '.base_url().'gl-admin-pemasukan');
					}
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		$id_uang_masuk = ($this->uri->segment(2,0));
		$data_uang_masuk = $this->M_gl_uang_masuk->get_uang_masuk('MD5(id_uang_masuk)',$id_uang_masuk);
		if(!empty($data_uang_masuk))
		{
			$data_uang_masuk = $data_uang_masuk->row();
			$this->M_gl_uang_masuk->hapus('MD5(id_induk_uang_masuk)',$id_uang_masuk);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data uang masuk '.$data_uang_masuk->no_bukti.' | '.$data_uang_masuk->nama_uang_masuk,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-pemasukan');
	}
	
	function cek_uang_masuk()
	{
		$hasil_cek = $this->M_gl_uang_masuk->get_uang_masuk('no_bukti',$_POST['no_bukti']);
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->no_bukti;
		}
		else
		{
			return false;
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */