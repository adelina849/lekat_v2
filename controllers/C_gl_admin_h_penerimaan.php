<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_h_penerimaan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_h_penerimaan','M_gl_d_penerimaan','M_gl_h_pembelian','M_gl_gedung','M_gl_supplier'));
		
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
				$id_h_pembelian = $this->uri->segment(2,0);
				$cari_h_pemberlian = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_pembelian = '".$id_h_pembelian."' ";
				$data_h_pembelian = $this->M_gl_h_pembelian->get_h_pembelian_cari($cari_h_pemberlian);
				if(!empty($data_h_pembelian))
				{
					$data_h_pembelian = $data_h_pembelian->row();
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						//$cari = "WHERE (A.group1 LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.main_group LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_fasilitas LIKE '%".str_replace("'","",$_GET['cari'])."%' ) ";
						
						$cari = " WHERE A.id_h_pembelian = '".$id_h_pembelian."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.no_surat_jalan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_pengirim LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%' )";
					}
					else
					{
						$cari = " WHERE A.id_h_pembelian = '".$id_h_pembelian."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
					}
					
					
					$this->load->library('pagination');
					
					$config['first_url'] = site_url('gl-admin-purchase-order-terima-surat-jalan/'.$id_h_pembelian.'?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-purchase-order-terima-surat-jalan/'.$id_h_pembelian);
					
					$config['total_rows'] = $this->M_gl_h_penerimaan->count_h_penerimaan_limit($cari)->JUMLAH;
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
					
					$list_h_penerimaan = $this->M_gl_h_penerimaan->list_h_penerimaan_limit($cari,$config['per_page'],$this->uri->segment(3,0));
					
					$msgbox_title = " Penerimaan produk/Surat PO ".$data_h_pembelian->no_h_pembelian;
					
					$list_gedung = $this->M_gl_gedung->list_gedung_limit(" WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.kat_gedung  = 'WAREHOUSE' ",100,0);
					
					$data = array('page_content'=>'gl_admin_h_penerimaan','halaman'=>$halaman,'list_h_penerimaan'=>$list_h_penerimaan,'msgbox_title' => $msgbox_title,'data_h_pembelian' => $data_h_pembelian,'list_gedung'=> $list_gedung);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-purchase-order-terima');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_penerimaan_produk()
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
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
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
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND DATE(COALESCE(A.tgl_terima,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
							(
								COALESCE(E.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(E.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.no_h_pembelian,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(A.no_surat_jalan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND DATE(COALESCE(A.tgl_terima,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				
				$list_penerimaan_produk = $this->M_gl_h_penerimaan->list_penerimaan_produk($cari);
				
				
				$msgbox_title = " Laporan Penerimaan Produk Dari Supplier ";
				
				$data = array('page_content'=>'gl_admin_terima_produk_dari_supplier','msgbox_title' => $msgbox_title,'list_penerimaan_produk' => $list_penerimaan_produk
				);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function excel_list_penerimaan_produk()
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
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
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
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND DATE(COALESCE(A.tgl_terima,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
							(
								COALESCE(E.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(E.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.no_h_pembelian,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(A.no_surat_jalan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND DATE(COALESCE(A.tgl_terima,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				
				$list_penerimaan_produk = $this->M_gl_h_penerimaan->list_penerimaan_produk($cari);
				
				
				$msgbox_title = " Laporan Penerimaan Produk Dari Supplier ";
				
				$data = array('page_content'=>'gl_admin_excel_terima_produk_dari_supplier','msgbox_title' => $msgbox_title,'list_penerimaan_produk' => $list_penerimaan_produk,'dari'=>$dari,'sampai'=> $sampai
				);
				$this->load->view('admin/page/gl_admin_excel_terima_produk_dari_supplier.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function list_penerimaan_for_hutang()
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
				/*
				$id_h_pembelian = $this->uri->segment(2,0);
				$data_h_pembelian = $this->M_gl_h_pembelian->get_h_pembelian('id_h_pembelian',$id_h_pembelian);
				if(!empty($data_h_pembelian))
				{
				*/
					//$data_h_pembelian = $data_h_pembelian->row();
					if((!empty($_GET['from'])) && ($_GET['from']!= "")  )
					{
						$from = $_GET['from'];
					}
					else
					{
						$from = '';
					}
					
					
					if((!empty($_GET['kode_supplier'])) && ($_GET['kode_supplier']!= "")  )
					{
						$kode_supplier = $_GET['kode_supplier'];
					}
					else
					{
						$kode_supplier = '';
					}
					
					if((!empty($_GET['status_hutang'])) && ($_GET['status_hutang']!= "")  )
					{
						//$status_hutang = $_GET['status_hutang'];
						if($_GET['status_hutang'] == 'SISA')
						{
							$status_hutang = " AND (COALESCE(C.NOMINAL,0) - COALESCE(F.BAYAR,0)) > 0 ";
						}
						else
						{
							$status_hutang = " AND (COALESCE(C.NOMINAL,0) - COALESCE(F.BAYAR,0)) <= 0 ";
						}
					}
					else
					{
						$status_hutang = '';
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
						//$cari = " WHERE A.id_h_pembelian = '".$id_h_pembelian."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.no_surat_jalan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_pengirim LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%' )";
						
						if($status_hutang == '')
						{
							$cari = " 
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND CASE WHEN '".$kode_supplier."' <> '' THEN
									COALESCE(E.kode_supplier,'') = '".$kode_supplier."'
								ELSE
									COALESCE(E.kode_supplier,'') LIKE '%".$kode_supplier."%'
								END
							AND 
								CASE WHEN '".$from."' = 'vipot_hutang' THEN
									-- tgl_tempo_next
									CASE WHEN COALESCE(A.tgl_tempo_next,'') = '' THEN
										DATE_ADD(A.tgl_terima, INTERVAL (COALESCE(E.hari_tempo,0)) DAY) BETWEEN '".$dari."' AND '".$sampai."'
									ELSE
										A.tgl_tempo_next BETWEEN '".$dari."' AND '".$sampai."'
									END
									
								ELSE
									COALESCE(DATE(A.tgl_terima),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
								END
								
							AND (
									A.no_surat_jalan LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.nama_pengirim LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(E.kode_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(E.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(D.no_h_pembelian,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)";
						}
						else
						{
							$cari = " 
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND CASE WHEN '".$kode_supplier."' <> '' THEN
									COALESCE(E.kode_supplier,'') = '".$kode_supplier."'
								ELSE
									COALESCE(E.kode_supplier,'') LIKE '%".$kode_supplier."%'
								END
							".$status_hutang."
							
							AND 
								CASE WHEN '".$from."' = 'vipot_hutang' THEN
									-- tgl_tempo_next
									CASE WHEN COALESCE(A.tgl_tempo_next,'') = '' THEN
										DATE_ADD(A.tgl_terima, INTERVAL (COALESCE(E.hari_tempo,0)) DAY) BETWEEN '".$dari."' AND '".$sampai."'
									ELSE
										A.tgl_tempo_next BETWEEN '".$dari."' AND '".$sampai."'
									END
									
								ELSE
									COALESCE(DATE(A.tgl_terima),'1900-01-01') BETWEEN '1900-01-01' AND '".$sampai."'
								END
							
							AND (
									A.no_surat_jalan LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.nama_pengirim LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(E.kode_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(E.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(D.no_h_pembelian,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)";
						}
								
					}
					else
					{
						//$cari = " WHERE A.id_h_pembelian = '".$id_h_pembelian."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
						if($status_hutang == '')
						{
							$cari = " 
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND CASE WHEN '".$kode_supplier."' <> '' THEN
									COALESCE(E.kode_supplier,'') = '".$kode_supplier."'
								ELSE
									COALESCE(E.kode_supplier,'') LIKE '%".$kode_supplier."%'
								END
							AND 
								CASE WHEN '".$from."' = 'vipot_hutang' THEN
									-- tgl_tempo_next
									CASE WHEN COALESCE(A.tgl_tempo_next,'') = '' THEN
										DATE_ADD(A.tgl_terima, INTERVAL (COALESCE(E.hari_tempo,0)) DAY) BETWEEN '".$dari."' AND '".$sampai."'
									ELSE
										A.tgl_tempo_next BETWEEN '".$dari."' AND '".$sampai."'
									END
									
								ELSE
									COALESCE(DATE(A.tgl_terima),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
								END
							
							";
						}
						else
						{
							$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND CASE WHEN '".$kode_supplier."' <> '' THEN
									COALESCE(E.kode_supplier,'') = '".$kode_supplier."'
								ELSE
									COALESCE(E.kode_supplier,'') LIKE '%".$kode_supplier."%'
								END
							".$status_hutang." 
							AND 
								CASE WHEN '".$from."' = 'vipot_hutang' THEN
									-- tgl_tempo_next
									CASE WHEN COALESCE(A.tgl_tempo_next,'') = '' THEN
										DATE_ADD(A.tgl_terima, INTERVAL (COALESCE(E.hari_tempo,0)) DAY) BETWEEN '".$dari."' AND '".$sampai."'
									ELSE
										A.tgl_tempo_next BETWEEN '".$dari."' AND '".$sampai."'
									END
									
								ELSE
									COALESCE(DATE(A.tgl_terima),'1900-01-01') BETWEEN '1900-01-01' AND '".$sampai."'
								END
							
							";
						}
					}
					
					
					$this->load->library('pagination');
					
					$config['first_url'] = site_url('gl-admin-laporan-list-hutang-per-inv?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-laporan-list-hutang-per-inv/');
					
					$config['total_rows'] = 300; //$this->M_gl_h_penerimaan->count_h_penerimaan_limit_by_inv($cari)->JUMLAH;
					$config['uri_segment'] = 2;	
					$config['per_page'] = 1000;
					$config['num_links'] = 3;
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
					
					$list_h_penerimaan = $this->M_gl_h_penerimaan->list_h_penerimaan_limit_for_hutang_by_inv($cari,$config['per_page'],$this->uri->segment(3,0));
					
					$msgbox_title = " List Hutang Per Invoice/Surat Jalan";
					
					$list_gedung = $this->M_gl_gedung->list_gedung_limit(" WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.kat_gedung  = 'WAREHOUSE' ",100,0);
					
					//MENAMPILKAN DATA SUPPLIER
					$cari_supplier = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
					$list_supllier = $this->M_gl_supplier->list_hanya_supplier_limit($cari_supplier,10000,0);
					//MENAMPILKAN DATA SUPPLIER
					
					$data = array('page_content'=>'gl_admin_h_penerimaan_for_hutang','halaman'=>$halaman,'list_h_penerimaan'=>$list_h_penerimaan,'msgbox_title' => $msgbox_title
					//,'data_h_pembelian' => $data_h_pembelian
					,'list_supllier' => $list_supllier
					,'list_gedung'=> $list_gedung);
					$this->load->view('admin/container',$data);
				/*
				}
				else
				{
					header('Location: '.base_url().'gl-admin-purchase-order-terima');
				}
				*/
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function excel_list_penerimaan_for_hutang()
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
				/*
				$id_h_pembelian = $this->uri->segment(2,0);
				$data_h_pembelian = $this->M_gl_h_pembelian->get_h_pembelian('id_h_pembelian',$id_h_pembelian);
				if(!empty($data_h_pembelian))
				{
				*/
					//$data_h_pembelian = $data_h_pembelian->row();
					if((!empty($_GET['from'])) && ($_GET['from']!= "")  )
					{
						$from = $_GET['from'];
					}
					else
					{
						$from = '';
					}
					
					
					if((!empty($_GET['kode_supplier'])) && ($_GET['kode_supplier']!= "")  )
					{
						$kode_supplier = $_GET['kode_supplier'];
					}
					else
					{
						$kode_supplier = '';
					}
					
					if((!empty($_GET['status_hutang'])) && ($_GET['status_hutang']!= "")  )
					{
						//$status_hutang = $_GET['status_hutang'];
						if($_GET['status_hutang'] == 'SISA')
						{
							$status_hutang = " AND (COALESCE(C.NOMINAL,0) - COALESCE(F.BAYAR,0)) > 0 ";
						}
						else
						{
							$status_hutang = " AND (COALESCE(C.NOMINAL,0) - COALESCE(F.BAYAR,0)) <= 0 ";
						}
					}
					else
					{
						$status_hutang = '';
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
						//$cari = " WHERE A.id_h_pembelian = '".$id_h_pembelian."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.no_surat_jalan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_pengirim LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%' )";
						
						if($status_hutang == '')
						{
							$cari = " 
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND CASE WHEN '".$kode_supplier."' <> '' THEN
									COALESCE(E.kode_supplier,'') = '".$kode_supplier."'
								ELSE
									COALESCE(E.kode_supplier,'') LIKE '%".$kode_supplier."%'
								END
							AND 
								CASE WHEN '".$from."' = 'vipot_hutang' THEN
									-- tgl_tempo_next
									CASE WHEN COALESCE(A.tgl_tempo_next,'') = '' THEN
										DATE_ADD(A.tgl_terima, INTERVAL (COALESCE(E.hari_tempo,0)) DAY) BETWEEN '".$dari."' AND '".$sampai."'
									ELSE
										A.tgl_tempo_next BETWEEN '".$dari."' AND '".$sampai."'
									END
									
								ELSE
									COALESCE(DATE(A.tgl_terima),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
								END
								
							AND (
									A.no_surat_jalan LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.nama_pengirim LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(E.kode_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(E.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(D.no_h_pembelian,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)";
						}
						else
						{
							$cari = " 
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND CASE WHEN '".$kode_supplier."' <> '' THEN
									COALESCE(E.kode_supplier,'') = '".$kode_supplier."'
								ELSE
									COALESCE(E.kode_supplier,'') LIKE '%".$kode_supplier."%'
								END
							".$status_hutang."
							
							AND 
								CASE WHEN '".$from."' = 'vipot_hutang' THEN
									-- tgl_tempo_next
									CASE WHEN COALESCE(A.tgl_tempo_next,'') = '' THEN
										DATE_ADD(A.tgl_terima, INTERVAL (COALESCE(E.hari_tempo,0)) DAY) BETWEEN '".$dari."' AND '".$sampai."'
									ELSE
										A.tgl_tempo_next BETWEEN '".$dari."' AND '".$sampai."'
									END
									
								ELSE
									COALESCE(DATE(A.tgl_terima),'1900-01-01') BETWEEN '1900-01-01' AND '".$sampai."'
								END
							
							AND (
									A.no_surat_jalan LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.nama_pengirim LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.diterima_oleh LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(E.kode_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(E.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(D.no_h_pembelian,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)";
						}
								
					}
					else
					{
						//$cari = " WHERE A.id_h_pembelian = '".$id_h_pembelian."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
						if($status_hutang == '')
						{
							$cari = " 
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND CASE WHEN '".$kode_supplier."' <> '' THEN
									COALESCE(E.kode_supplier,'') = '".$kode_supplier."'
								ELSE
									COALESCE(E.kode_supplier,'') LIKE '%".$kode_supplier."%'
								END
							AND 
								CASE WHEN '".$from."' = 'vipot_hutang' THEN
									-- tgl_tempo_next
									CASE WHEN COALESCE(A.tgl_tempo_next,'') = '' THEN
										DATE_ADD(A.tgl_terima, INTERVAL (COALESCE(E.hari_tempo,0)) DAY) BETWEEN '".$dari."' AND '".$sampai."'
									ELSE
										A.tgl_tempo_next BETWEEN '".$dari."' AND '".$sampai."'
									END
									
								ELSE
									COALESCE(DATE(A.tgl_terima),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
								END
							
							";
						}
						else
						{
							$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND CASE WHEN '".$kode_supplier."' <> '' THEN
									COALESCE(E.kode_supplier,'') = '".$kode_supplier."'
								ELSE
									COALESCE(E.kode_supplier,'') LIKE '%".$kode_supplier."%'
								END
							".$status_hutang." 
							AND 
								CASE WHEN '".$from."' = 'vipot_hutang' THEN
									-- tgl_tempo_next
									CASE WHEN COALESCE(A.tgl_tempo_next,'') = '' THEN
										DATE_ADD(A.tgl_terima, INTERVAL (COALESCE(E.hari_tempo,0)) DAY) BETWEEN '".$dari."' AND '".$sampai."'
									ELSE
										A.tgl_tempo_next BETWEEN '".$dari."' AND '".$sampai."'
									END
									
								ELSE
									COALESCE(DATE(A.tgl_terima),'1900-01-01') BETWEEN '1900-01-01' AND '".$sampai."'
								END
							
							";
						}
					}
					
					
					$this->load->library('pagination');
					
					$config['first_url'] = site_url('gl-admin-laporan-list-hutang-per-inv?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-laporan-list-hutang-per-inv/');
					
					$config['total_rows'] = 300; //$this->M_gl_h_penerimaan->count_h_penerimaan_limit_by_inv($cari)->JUMLAH;
					$config['uri_segment'] = 2;	
					$config['per_page'] = 1000;
					$config['num_links'] = 3;
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
					
					$list_h_penerimaan = $this->M_gl_h_penerimaan->list_h_penerimaan_limit_for_hutang_by_inv($cari,$config['per_page'],$this->uri->segment(3,0));
					
					$msgbox_title = " List Hutang Per Invoice/Surat Jalan";
					
					$list_gedung = $this->M_gl_gedung->list_gedung_limit(" WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.kat_gedung  = 'WAREHOUSE' ",100,0);
					
					//MENAMPILKAN DATA SUPPLIER
					$cari_supplier = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
					$list_supllier = $this->M_gl_supplier->list_hanya_supplier_limit($cari_supplier,10000,0);
					//MENAMPILKAN DATA SUPPLIER
					
					$data = array('page_content'=>'gl_admin_excel_laporan_hutang','halaman'=>$halaman,'list_h_penerimaan'=>$list_h_penerimaan,'msgbox_title' => $msgbox_title
					//,'data_h_pembelian' => $data_h_pembelian
					,'list_supllier' => $list_supllier
					,'list_gedung'=> $list_gedung);
					$this->load->view('admin/page/gl_admin_excel_laporan_hutang.html',$data);
				/*
				}
				else
				{
					header('Location: '.base_url().'gl-admin-purchase-order-terima');
				}
				*/
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
				$id_h_pembelian = $this->uri->segment(2,0);
				$data_h_pembelian = $this->M_gl_h_pembelian->get_h_pembelian('id_h_pembelian',$id_h_pembelian);
				if(!empty($data_h_pembelian))
				{
					if (!empty($_POST['stat_edit']))
					{	
						$this->M_gl_h_penerimaan->edit
						(
							$_POST['stat_edit'],
							$_POST['id_h_pembelian'],
							$_POST['id_gedung'],
							$_POST['no_surat_jalan'],
							$_POST['nama_pengirim'],
							$_POST['cara_pengiriman'],
							$_POST['diterima_oleh'],
							str_replace(",","",$_POST['biaya_kirim']) , //$_POST['biaya_kirim'],
							$_POST['tgl_kirim'],
							$_POST['tgl_terima'],
							$_POST['tgl_tempo_next'],
							$_POST['ket_h_penerimaan'],
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
								'Melakukan perubahan data surat jalan penerimaan PO '.$_POST['kode_satuan'].' | '.$_POST['nama_satuan'],
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS EDIT*/
					}
					else
					{
						$this->M_gl_h_penerimaan->simpan
						(
							$_POST['id_h_pembelian'],
							$_POST['id_gedung'],
							$_POST['no_surat_jalan'],
							$_POST['nama_pengirim'],
							$_POST['cara_pengiriman'],
							$_POST['diterima_oleh'],
							str_replace(",","",$_POST['biaya_kirim']) , //$_POST['biaya_kirim'],
							$_POST['tgl_kirim'],
							$_POST['tgl_terima'],
							$_POST['tgl_tempo_next'],
							$_POST['ket_h_penerimaan'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						
						);
					}
					
					header('Location: '.base_url().'gl-admin-purchase-order-terima-surat-jalan/'.$_POST['id_h_pembelian']);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-purchase-order-terima');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function simpan_d_penerimaan()
	{
		$cari_d_penerimaan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_pembelian = '".$_POST['id_h_pembelian']."' AND id_h_penerimaan = '".$_POST['id_h_penerimaan']."' AND id_produk = '".$_POST['id_produk']."'";
		$cek_data_d_penerimaan = $this->M_gl_d_penerimaan->get_d_penerimaan($cari_d_penerimaan);
		if(!empty($cek_data_d_penerimaan))
		{
			$cek_data_d_penerimaan = $cek_data_d_penerimaan->row();
			$this->M_gl_d_penerimaan->hapus($cari_d_penerimaan);
			
			if($_POST['diterima'] > 0 )
			{
				$this->M_gl_d_penerimaan->simpan
				(
					$_POST['id_h_penerimaan'],
					$_POST['id_h_pembelian'],
					$_POST['id_d_pembelian'],
					$_POST['id_produk'],
					$_POST['diterima'],
					$_POST['konversi'],
					$_POST['diterima_satuan_beli'],
					$_POST['status_konversi'],
					$_POST['kode_satuan'],
					$_POST['nama_satuan'],
					$_POST['harga_beli'],
					$_POST['harga_konversi'],
					$_POST['optr_diskon'],
					$_POST['diskon'],
					$_POST['tgl_exp'],
					$this->session->userdata('ses_id_karyawan'),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			echo "BERHASIL";
		}
		else
		{	
			$this->M_gl_d_penerimaan->simpan
			(
				$_POST['id_h_penerimaan'],
				$_POST['id_h_pembelian'],
				$_POST['id_d_pembelian'],
				$_POST['id_produk'],
				$_POST['diterima'],
				$_POST['konversi'],
				$_POST['diterima_satuan_beli'],
				$_POST['status_konversi'],
				$_POST['kode_satuan'],
				$_POST['nama_satuan'],
				$_POST['harga_beli'],
				$_POST['harga_konversi'],
				$_POST['optr_diskon'],
				$_POST['diskon'],
				$_POST['tgl_exp'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			);
			echo "BERHASIL";
		}
		
		// LAKUKAN UPDATE HARGA DASAR HPP DI TB_SATUAN_KONVERSI JIKA HARGA LEBIH DARI 0
		if( $_POST['harga_beli'] > 0)
		{
			$this->M_gl_d_penerimaan->ubah_harga_dasar_satuan_konversi($this->session->userdata('ses_kode_kantor'),$_POST['id_produk'],$_POST['kode_satuan'],$_POST['harga_beli']);
		}
	}
	
	public function hapus()
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
				$id_h_pembelian = $this->uri->segment(2,0);
				$data_h_pembelian = $this->M_gl_h_pembelian->get_h_pembelian('id_h_pembelian',$id_h_pembelian);
				if(!empty($data_h_pembelian))
				{
					$data_h_pembelian = $data_h_pembelian->row();
					$id_h_penerimaan = ($this->uri->segment(3,0));
					$data_h_penerimaan = $this->M_gl_h_penerimaan->get_h_penerimaan('id_h_penerimaan',$id_h_penerimaan);
					if(!empty($data_h_penerimaan))
					{
						$data_h_penerimaan = $data_h_penerimaan->row();
						$this->M_gl_h_penerimaan->hapus('id_h_penerimaan',$id_h_penerimaan);
						$this->M_gl_h_penerimaan->hapus_d_penerimaan('id_h_penerimaan',$id_h_penerimaan);
						
						/* CATAT AKTIFITAS HAPUS*/
						if($this->session->userdata('catat_log') == 'Y')
						{
							$this->M_gl_log->simpan_log
							(
								$this->session->userdata('ses_id_karyawan'),
								'DELETE',
								'Melakukan penghapusan data Penerimaan Surat jalan '.$data_h_penerimaan->no_surat_jalan.' | '.$data_h_penerimaan->nama_pengirim,
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS HAPUS*/
					}
				}
				else
				{
					header('Location: '.base_url().'gl-admin-purchase-order-terima-surat-jalan/'.$id_h_pembelian);
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
		
	}
	
	function cek_satuan()
	{
		$hasil_cek = $this->M_gl_satuan->get_satuan('kode_satuan',$_POST['kode_satuan']);
		echo $hasil_cek;
	}
	
	function list_produk_terima()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = str_replace("'","",$_POST['cari']);
		}
		else
		{
			$cari = "";
		}
		
		$list_produk = $this->M_gl_h_penerimaan->list_d_penerimaan($_POST['id_h_pembelian'],$_POST['id_h_penerimaan'],$cari,$this->session->userdata('ses_kode_kantor'));
		
		if(!empty($list_produk))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="35%">PRODUK</th>';
							echo '<th width="15%">ORDER</th>';
							echo '<th width="15%">TELAH TERIMA</th>';
							echo '<th width="15%">TERIMA</th>';
							echo '<th width="15%">SISA</th>';
				echo '</tr>
</thead>';
				$list_result = $list_produk->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_produk-'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						echo'<td>
								<b>KODE : </b>'.$row->kode_produk.' 
								<br/> <b>NAMA : </b>'.$row->nama_produk.' 
								<br/> <b>HARGA : </b><b style="color:red;">'.number_format($row->BL_HARGA,2,'.',',').' /'.$row->kode_satuan.'</b>
								<br/> <b>EXPIRED : </b>
								';
						if($row->TGL_EXP <> '0000-00-00')
						{
							echo'
									<div class="form-group">
										<div class="input-group date">
											<div class="input-group-addon">
												<i class="fa fa-calendar"></i>
											</div>
											<input name="tgl_exp_'.$no.'" id="tgl_exp_'.$no.'" type="text" class="required form-control pull-right settingDate" alt="Tanggal Pengiriman" title="Tanggal Pengiriman" value="'.$row->TGL_EXP.'" data-date-format="yyyy-mm-dd">
										</div>
									</div>
									
								</td>';
						}
						else
						{
							echo'
									<div class="form-group">
										<div class="input-group date">
											<div class="input-group-addon">
												<i class="fa fa-calendar"></i>
											</div>
											<input name="tgl_exp_'.$no.'" id="tgl_exp_'.$no.'" type="text" class="required form-control pull-right settingDate" alt="Tanggal Pengiriman" title="Tanggal Pengiriman" value="'.date('Y-m-d', strtotime('+1 years')).'" data-date-format="yyyy-mm-dd">
										</div>
									</div>
									
								</td>';
						}
						
						echo'<td>
								<input type="text" name="BL_JUMLAH_'.$no.'" id="BL_JUMLAH_'.$no.'" value="'.$row->BL_JUMLAH.'" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)"  size="4" style="background-color:#D3D3D3;"  disabled="true"/>
							</td>';
						echo'<td>
								<input type="text" name="TLH_TERIMA_'.$no.'" id="TLH_TERIMA_'.$no.'" value="'.$row->TLH_TRM_SATUAN_BELI.'" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" size="4" style="background-color:#D3D3D3;"  disabled="true"/>
							</td>';
						echo'<td>
								<input type="text" name="TERIMA_'.$no.'" id="TERIMA_'.$no.'" value="'.$row->TRM_SATUAN_BELI.'" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" onkeyup="get_sisa('.$no.')" onchange="simpan_d_terima('.$no.')" size="4" style="background-color:green;"/>
							</td>';
							
						$sisa = $row->BL_JUMLAH - ($row->TLH_TRM_SATUAN_BELI + $row->TRM_SATUAN_BELI);
						echo'<td>
								<input type="text" name="SISA_'.$no.'" id="SISA_'.$no.'" value="'.$sisa.'" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)"  size="4" style="background-color:#D3D3D3;color:red;" disabled="true"/>
							</td>';
												
						echo'<input type="hidden" id="get_tr_'.$no.'" name="get_tr_'.$no.'" value="tr_list_produk-'.$no.'" />';
						echo'<input type="hidden" id="id_h_penerimaan_'.$no.'" name="id_h_penerimaan_'.$no.'" value="'.$_POST['id_h_penerimaan'].'" />';
						
						echo'<input type="hidden" id="id_d_pembelian_'.$no.'" name="id_d_pembelian_'.$no.'" value="'.$row->id_d_pembelian.'" />';
						echo'<input type="hidden" id="id_produk_'.$no.'" name="id_produk_'.$no.'" value="'.$row->id_produk.'" />';
						echo'<input type="hidden" id="kode_satuan_'.$no.'" name="kode_satuan_'.$no.'" value="'.$row->kode_satuan.'" />';
						echo'<input type="hidden" id="BL_HARGA_'.$no.'" name="BL_HARGA_'.$no.'" value="'.$row->BL_HARGA.'" />';
						echo'<input type="hidden" id="BL_STATUS_KONVERSI_'.$no.'" name="BL_STATUS_KONVERSI_'.$no.'" value="'.$row->BL_STATUS_KONVERSI.'" />';
						echo'<input type="hidden" id="BL_KONVERSI_'.$no.'" name="BL_KONVERSI_'.$no.'" value="'.$row->BL_KONVERSI.'" />';
						
					echo'</tr>';
					$no++;
				}
				
				echo '</tbody>';
			echo'</table>';
			echo'</div>';
		}
		else
		{
			echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}

	function view_d_penerimaan()
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
				$id_h_penerimaan = $this->uri->segment(2,0);
				$cari_h_penerimaan = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_penerimaan) = '".$id_h_penerimaan."'";
				$data_penerimaan_pembelian = $this->M_gl_h_penerimaan->list_penerimaan_pembelian_sj($cari_h_penerimaan,"");
				if(!empty($data_penerimaan_pembelian))
				{
					$data_penerimaan_pembelian = $data_penerimaan_pembelian->row();
					
					$msgbox_title = " Penerimaan Surat Jalan ".$data_penerimaan_pembelian->no_surat_jalan;
					
					$data = array('page_content'=>'gl_admin_d_penerimaan_sj','msgbox_title' => $msgbox_title);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-purchase-order-terima');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function cek_no_surat_jalan()
	{
		$cari = "WHERE no_faktur = '".$_POST['no_surat_jalan']."';";
		$hasil_cek = $this->M_gl_h_penerimaan->get_surat_jalan_atau_faktur_penjualan($cari);
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->tgl_h_penjualan;
		}
		else
		{
			return false;
		}
	}

	function print_bukti_penerimaan()
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
				$id_h_penerimaan = $this->uri->segment(2,0);
				$cari_h_penerimaan = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_penerimaan) = '".$id_h_penerimaan."'";
				$data_penerimaan_pembelian = $this->M_gl_h_penerimaan->list_penerimaan_pembelian_sj($cari_h_penerimaan,"");
				if(!empty($data_penerimaan_pembelian))
				{
					$data_penerimaan_pembelian = $data_penerimaan_pembelian->row();
					
					$msgbox_title = " Penerimaan Surat Jalan ".$data_penerimaan_pembelian->no_surat_jalan;
					
					$data = array('page_content'=>'gl_admin_print_bukti_penerimaan','msgbox_title' => $msgbox_title,'data_penerimaan_pembelian' => $data_penerimaan_pembelian);
					$this->load->view('admin/page/gl_admin_print_bukti_penerimaan.html',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-purchase-order-terima');
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