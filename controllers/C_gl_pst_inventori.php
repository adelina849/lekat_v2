<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_inventori extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_pst_inventori','M_gl_h_pembelian','M_gl_stock_produk'));
		
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
					$cari = str_replace("'","",$_GET['cari']);
				}
				else
				{
					$cari = "";
				}
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
				}
				
				if((!empty($_GET['opcd'])) && ($_GET['opcd']!= "")  )
				{
					$opcd = str_replace("'","",$_GET['opcd']);
				}
				else
				{
					$opcd = "";
				}
				
				
				$list_alur_produk = $this->M_gl_pst_inventori->list_pergerakan_produk($kode_kantor,$dari,$sampai,$cari,$opcd);
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END");
				
				$msgbox_title = " Alur Pergerakan Produk ";
				
				
				$data = array('page_content'=>'gl_pusat_alur_produk','msgbox_title' => $msgbox_title, 'list_alur_produk' => $list_alur_produk,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function inv_analisa_produk_terlaris()
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
					$cari = str_replace("'","",$_GET['cari']);
				}
				else
				{
					$cari = "";
				}
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
				}
				
				if((!empty($_GET['order_by'])) && ($_GET['order_by']!= "")  )
				{
					if( $_GET['order_by'] == 'NOMINAL' )
					{
						$order_by = "ORDER BY AAA.SUBTOTAL DESC";
					}
					else
					{
						$order_by = "ORDER BY AAA.CNT DESC";
					}
				}
				else
				{
					$order_by = "ORDER BY AAA.CNT DESC";
				}
				
				
				$list_produk_terlaris = $this->M_gl_pst_inventori->list_produk_terlaris($kode_kantor,$dari,$sampai,$cari,$order_by);
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END");
				
				$msgbox_title = " Analisa Produk Terlaris ";
				
				
				$data = array('page_content'=>'gl_pusat_inv_produk_terlaris','msgbox_title' => $msgbox_title, 'list_produk_terlaris' => $list_produk_terlaris,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	public function inv_analisa_po_belum_full()
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
					$cari = str_replace("'","",$_GET['cari']);
				}
				else
				{
					$cari = "";
				}
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
				}
				
				$order_by = "ORDER BY A.tgl_ins ASC";
				
				
				
				$list_po_belum_full = $this->M_gl_pst_inventori->list_pemesanan_produk_belum_full($kode_kantor,$dari,$sampai,$cari,$order_by);
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END");
				
				$msgbox_title = " Analisa Produk Terlaris ";
				
				
				$data = array('page_content'=>'gl_pusat_inv_po_belum_full','msgbox_title' => $msgbox_title, 'list_po_belum_full' => $list_po_belum_full,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	public function inv_list_stock_produk()
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
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor2("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END");
				
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
					$cari = str_replace("'","",$_GET['cari']);
				}
				else
				{
					$cari = "";
				}
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "PST";
				}
				
				$jum_row = $this->M_gl_pst_inventori->count_stock_produk($kode_kantor,$cari)->JUMLAH;
				
				$this->load->library('pagination');
				
				$config['first_url'] = site_url('gl-pusat-inventori-produk-stock?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-pusat-inventori-produk-stock/');
				$config['total_rows'] = $jum_row;
				$config['uri_segment'] = 2;	
				$config['per_page'] = 100;
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
				
				//list_stock_produk($kode_kantor,$dari,$sampai,$offset,$limit,$cari)
				
				$list_stock_produk = $this->M_gl_pst_inventori->list_stock_produk($kode_kantor,$dari,$sampai,$this->uri->segment(2,0),$config['per_page'],$cari);
				
				//$sum_stock_produk = $this->M_gl_stock_produk->sum_stock_produk($this->session->userdata('ses_kode_kantor'),$cari,$sampai,'23','59');
				
				//($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Stock/Persediaan Produk ";
				
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
				
				
				
				$data = array('page_content'=>'gl_pusat_inv_produk_stock','halaman'=>$halaman,'list_stock_produk'=>$list_stock_produk,'msgbox_title' => $msgbox_title, 'sum_pesan' => $sum_pesan, 'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function inv_list_stock_produk_per_supplier()
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
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor2("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END");
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					//$kode_kantor = $this->session->userdata('ses_kode_kantor');
					$kode_kantor = '';
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
					$cari = str_replace("'","",$_GET['cari']);
					
					
				}
				else
				{
					$cari = "";
				}
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor2(
										"
											WHERE kode_kantor NOT IN ('SLM','INV','SAL')
										");
				
				$list_stock_produk_per_supplier = $this->M_gl_stock_produk->list_stock_produk_per_supplier($kode_kantor,$dari,$sampai,0,9999999,$cari);
				
				
				$msgbox_title = " Laporan Stock Produk Per Produsen/Supplier";
				
				
				
				
										
				$data = array('page_content'=>'gl_pusat_inv_stock_produk_per_supplier','msgbox_title' => $msgbox_title,'list_kantor' => $list_kantor,'list_stock_produk_per_supplier' => $list_stock_produk_per_supplier);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	public function inv_list_stock_produk_limit()
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
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor2("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END");
				
				if((!empty($_GET['sampai'])) && ($_GET['sampai']!= "")  )
				{
					$sampai = $_GET['sampai'];
				}
				else
				{
					$sampai = date("Y-m-d");
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = str_replace("'","",$_GET['cari']);
				}
				else
				{
					$cari = "";
				}
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "PST";
				}
				
				
				
				$list_stock_produk_limit = $this->M_gl_pst_inventori->list_stock_produk($kode_kantor,$cari,' PROD.nama_produk ASC',0,10000,$sampai,'23','59','KURANG');
				
				$msgbox_title = " Stock Limit/Hampir Habis ";
				
				
				$data = array('page_content'=>'gl_pusat_inv_produk_stock_limit','list_stock_produk_limit'=>$list_stock_produk_limit,'msgbox_title' => $msgbox_title, 'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	public function inv_list_tgl_expired()
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = $_GET['cari'];
				}
				else
				{
					$cari = "";
				}
				
				
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END");
				$list_h_pembelian_penerimaan_tgl_exp = $this->M_gl_h_pembelian->list_tgl_expired($kode_kantor,$cari);
				
				$msgbox_title = " Informasi Tanggal Expired Produk";
				
				$data = array('page_content'=>'gl_pusat_tgl_expired','list_h_pembelian_penerimaan_tgl_exp'=>$list_h_pembelian_penerimaan_tgl_exp,'msgbox_title' => $msgbox_title,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function inv_analisa_order()
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
					$cari = str_replace("'","",$_GET['cari']);
				}
				else
				{
					$cari = "";
				}
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
				}
				
				$order_by = "ORDER BY A.tgl_ins ASC";
				
				
				
				$list_po_belum_full = $this->M_gl_pst_inventori->list_pemesanan_produk_belum_full($kode_kantor,$dari,$sampai,$cari,$order_by);
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END");
				
				$msgbox_title = " Analisa Produk Terlaris ";
				
				
				$data = array('page_content'=>'gl_pusat_inv_po_belum_full','msgbox_title' => $msgbox_title, 'list_po_belum_full' => $list_po_belum_full,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
				*/
				
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
				}
				
				if((!empty($_GET['sampai'])) && ($_GET['sampai']!= "")  )
				{
					$sampai = $_GET['sampai'];
				}
				else
				{
					$sampai = date("Y-m-d");
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = str_replace("'","",$_GET['cari']);
				}
				else
				{
					$cari = "";
				}
				
				
				/*
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor2(
										"
											WHERE kode_kantor NOT IN ('SLM','INV','SAL')
										");
				*/
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END");
				
				$jum_row = $this->M_gl_stock_produk->count_stock_produk($cari)->JUMLAH;
				//$jum_row = 30;
				
				$this->load->library('pagination');
				
				$config['first_url'] = site_url('gl-pusat-inventori-analisa-order?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-pusat-inventori-analisa-order/');
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
				
				
				//$sum_stock_produk = $this->M_gl_stock_produk->sum_stock_produk($this->session->userdata('ses_kode_kantor'),$cari,$sampai,'23','59');
				
				
				
				
				//($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Analisa Order Produk ";
				
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
				
				
				
				//$list_stock_produk = $this->M_gl_stock_produk->list_analisa_order($kode_kantor,$cari,' PROD.nama_produk ASC',$this->uri->segment(2,0),$config['per_page'],$sampai,'23','59','');
				$list_stock_produk = $this->M_gl_stock_produk->list_analisa_order_new_by_avg_3_bulan_use_procedure($kode_kantor,$cari,'ORDER BY A.nama_produk ASC',$this->uri->segment(2,0),$config['per_page'],$sampai);
				
				$data = array('page_content'=>'gl_pusat_inv_analisa_order','halaman'=>$halaman,'list_stock_produk'=>$list_stock_produk,'msgbox_title' => $msgbox_title, 'sum_pesan' => $sum_pesan,'list_kantor' => $list_kantor);
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
/* Location: ./application/controllers/C_gl_pst_inventori.php */