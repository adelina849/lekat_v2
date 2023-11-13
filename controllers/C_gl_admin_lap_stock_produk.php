<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_lap_stock_produk extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_stock_produk','M_gl_produk','M_gl_pst_inventori','M_gl_temp_d_pembelian','M_gl_h_pembelian'));
		
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
				
				if((!empty($_GET['sortir_by'])) && ($_GET['sortir_by']!= "")  )
				{
					$sortir_by = str_replace("'","",$_GET['sortir_by']);
				}
				else
				{
					$sortir_by = "";
				}
				
				
				//if($sortir_by == "NOMINAL_TINGGI")
				if( ($sortir_by == "NAMA_PRODUK") || ($sortir_by == "") )
				{
					//$sortir_by = " (AA.STOCK_AKHIR * AA.harga) DESC";
					$per_page = 100;
				}
				else
				{
					$per_page = 99999;
				}
				
				
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
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
					$cari = str_replace("'","",$_GET['cari']);
				}
				else
				{
					$cari = "";
				}
				
				$jum_row = $this->M_gl_stock_produk->count_stock_produk($cari)->JUMLAH; //KODE KANTOR MASIH AMBIL DRI SESI
				
				$this->load->library('pagination');
				
				$config['first_url'] = site_url('gl-admin-laporan-stock-produk?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-laporan-stock-produk/');
				$config['total_rows'] = $jum_row;
				$config['uri_segment'] = 2;	
				$config['per_page'] = $per_page;
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
				
				IF(!empty($this->session->userdata('ses_hirarki')))
				{
				    $ses_hirarki = $this->session->userdata('ses_hirarki');
				}
				else
				{
				    $ses_hirarki = 0;
				}
				
				//$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
				
				/*
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor2(
										"
											WHERE CASE WHEN ".$ses_hirarki." = 1 THEN 
												isViewClient IN (0,1) 
											ELSE 
												isViewClient = 0 
											END
										");
				*/
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor2(
										"
											WHERE kode_kantor NOT IN ('SLM','INV','SAL')
										");
				
				//$list_stock_produk = $this->M_gl_stock_produk->list_stock_produk($kode_kantor,$cari,' PROD.nama_produk ASC',$this->uri->segment(2,0),$config['per_page'],$sampai,'23','59','');
				
				//list_stock_produk($kode_kantor,$dari,$sampai,$offset,$limit,$cari)
				
				//if($sortir_by == "NOMINAL_TINGGI")
				if( ($sortir_by == "NAMA_PRODUK") || ($sortir_by == "") )
				{
					$list_stock_produk = $this->M_gl_pst_inventori->list_stock_produk($kode_kantor,$dari,$sampai,$this->uri->segment(2,0),$config['per_page'],$cari);
				}
				else
				{
					$list_stock_produk = $this->M_gl_pst_inventori->list_stock_produk_v3_with_order($kode_kantor,$dari,$sampai,$this->uri->segment(2,0),$config['per_page'],$cari,$sortir_by);
				}
				
				
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
					$sum_pesan = "Menampilkan ".$jum_row_tampil." Dari ".$jum_row." Data Pencarian ".str_replace("'","",$_GET['cari'])." dimulai dari data ke ".$offset." Sampai ".$max_data." (".date('Y-m-d H:i:s').")";
				}
				else
				{
					$sum_pesan = "Menampilkan ".$jum_row_tampil." Dari ".$jum_row." Data dimulai dari data ke ".$offset." Sampai ".$max_data." (".date('Y-m-d H:i:s').")";
				}
				
				
				if($this->session->userdata('ses_kode_kantor') == 'PST')
				{
					
					$data = array('page_content'=>'gl_admin_stock_produk','halaman'=>$halaman,'list_stock_produk'=>$list_stock_produk,'msgbox_title' => $msgbox_title, 'sum_pesan' => $sum_pesan,'list_kantor' => $list_kantor,'kode_kantor' => $kode_kantor);
					$this->load->view('admin/container',$data);
				}
				else
				{
					//$list_kantor = false;
					$data = array('page_content'=>'gl_admin_stock_produk','halaman'=>$halaman,'list_stock_produk'=>$list_stock_produk,'msgbox_title' => $msgbox_title, 'sum_pesan' => $sum_pesan,'list_kantor' => $list_kantor,'kode_kantor' => $kode_kantor);
					$this->load->view('admin/container',$data);
				}
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function view_analisa_order_old_20210429()
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
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
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
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor2(
										"
											WHERE kode_kantor NOT IN ('SLM','INV','SAL')
										");
				
				$jum_row = $this->M_gl_stock_produk->count_stock_produk($cari)->JUMLAH;
				
				$this->load->library('pagination');
				
				$config['first_url'] = site_url('gl-admin-analisa-order?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-analisa-order/');
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
				
				$list_stock_produk = $this->M_gl_stock_produk->list_analisa_order($kode_kantor,$cari,' PROD.nama_produk ASC',$this->uri->segment(2,0),$config['per_page'],$sampai,'23','59','');
				
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
				
				
				
				$data = array('page_content'=>'gl_admin_analisa_order','halaman'=>$halaman,'list_stock_produk'=>$list_stock_produk,'msgbox_title' => $msgbox_title, 'sum_pesan' => $sum_pesan,'list_kantor' => $list_kantor);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function view_analisa_order()
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
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
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
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor2(
										"
											WHERE kode_kantor NOT IN ('SLM','INV','SAL')
										");
				
				$jum_row = $this->M_gl_stock_produk->count_stock_produk($cari)->JUMLAH;
				
				$this->load->library('pagination');
				
				$config['first_url'] = site_url('gl-admin-analisa-order?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-analisa-order/');
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
				
				//$list_stock_produk = $this->M_gl_stock_produk->list_analisa_order($kode_kantor,$cari,' PROD.nama_produk ASC',$this->uri->segment(2,0),$config['per_page'],$sampai,'23','59','');
				$list_stock_produk = $this->M_gl_stock_produk->list_analisa_order_new_by_avg_3_bulan($kode_kantor,$cari,'ORDER BY A.nama_produk ASC',$this->uri->segment(2,0),$config['per_page'],$sampai);
				
				
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
				
				$cari_list_temp_d_pembelian = "WHERE A.kode_kantor = '".$kode_kantor."'";
				$order_by_temp_d_pembelian = "ORDER BY COALESCE(B.nama_produk,'') ASC";
				$list_produk_di_temp_d_pembelian = $this->M_gl_temp_d_pembelian->list_d_pembelian($cari_list_temp_d_pembelian,$order_by_temp_d_pembelian,10000,0);
				
				$data = array('page_content'=>'gl_admin_analisa_order','halaman'=>$halaman,'list_stock_produk'=>$list_stock_produk,'msgbox_title' => $msgbox_title, 'sum_pesan' => $sum_pesan,'list_kantor' => $list_kantor,'list_produk_di_temp_d_pembelian' => $list_produk_di_temp_d_pembelian);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function view_analisa_order_v2()
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
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
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
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor2(
										"
											WHERE kode_kantor NOT IN ('SLM','INV','SAL')
										");
				
				$jum_row = $this->M_gl_stock_produk->count_stock_produk($cari)->JUMLAH;
				//$jum_row = 30;
				
				$this->load->library('pagination');
				
				$config['first_url'] = site_url('gl-admin-analisa-order-v2?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-analisa-order-v2/');
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
				
				$cari_list_temp_d_pembelian = "WHERE A.kode_kantor = '".$kode_kantor."'";
				$order_by_temp_d_pembelian = "ORDER BY COALESCE(B.nama_produk,'') ASC";
				$list_produk_di_temp_d_pembelian = $this->M_gl_temp_d_pembelian->list_d_pembelian($cari_list_temp_d_pembelian,$order_by_temp_d_pembelian,10000,0);
				
				
				//$list_stock_produk = $this->M_gl_stock_produk->list_analisa_order($kode_kantor,$cari,' PROD.nama_produk ASC',$this->uri->segment(2,0),$config['per_page'],$sampai,'23','59','');
				$list_stock_produk = $this->M_gl_stock_produk->list_analisa_order_new_by_avg_3_bulan_use_procedure($kode_kantor,$cari,'ORDER BY A.nama_produk ASC',$this->uri->segment(2,0),$config['per_page'],$sampai);
				
				$data = array('page_content'=>'gl_admin_analisa_order_v2','halaman'=>$halaman,'list_stock_produk'=>$list_stock_produk,'msgbox_title' => $msgbox_title, 'sum_pesan' => $sum_pesan,'list_kantor' => $list_kantor,'list_produk_di_temp_d_pembelian' => $list_produk_di_temp_d_pembelian);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function tampil_ajax_analisa_order()
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
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
				$cari_list_temp_d_pembelian = "WHERE A.kode_kantor = '".$kode_kantor."'";
				$order_by_temp_d_pembelian = "ORDER BY COALESCE(B.nama_produk,'') ASC";
				$list_produk_di_temp_d_pembelian = $this->M_gl_temp_d_pembelian->list_d_pembelian($cari_list_temp_d_pembelian,$order_by_temp_d_pembelian,10000,0);
				//TABLE
				if(!empty($list_produk_di_temp_d_pembelian))
					{
						//echo gethostname();
						//echo $this->M_gl_pengaturan->getUserIpAddr();
						//$sts_query = strpos(base_url(),"localhost");
						//echo $sts_query;
						//$nama = "Mulyana Yusuf";
						//echo str_replace("f","849",$nama);
						echo'<table width="50%" style="border: 1px black solid;">';
							echo '<thead style="background-color:grey;">
<tr>';
										echo '<th style="border: 1px black solid;">NO</th>';
										echo '<th style="border: 1px black solid;">NAMA PRODUK</th>';
										//echo '<th>HPP</th>';
										//echo '<th>SATUAN</th>';
										//echo '<th>BATASAN</th>';
										//echo '<th>AWAL</th>';
										
										echo '<th style="border: 1px black solid;">JUMLAH</th>';
										echo '<th style="border: 1px black solid;">HARGA</th>';
										
							echo '</tr>
</thead>';
							$list_result = $list_produk_di_temp_d_pembelian->result();
							$no = 1;
							$sub_total = 0;
							echo '<tbody>';
							foreach($list_result as $row)
							{
								echo'<tr>';
									echo'<td style="border: 1px black solid;">'.$no.'</td>';
									echo '<td style="border: 1px black solid;">
										'.$row->nama_produk.'
									</td>';
									echo'<td style="text-align:center;border: 1px black solid;">'.number_format($row->jumlah,0,'.',',').' '.$row->kode_satuan.'</td>';
									
									
									echo'<td style="text-align:center;border: 1px black solid;">'.number_format($row->harga,0,'.',',').'</td>';
									
									
								echo'</tr>';
								$no++;
								//$sub_total = $sub_total + $row->nominal;
								//sum_uang_keluar
							}
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
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function proses_analisa_order()
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
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
				$get_id_pembelian = $this->M_gl_h_pembelian->get_id_h_pembelian_dan_faktur($this->session->userdata('ses_kode_kantor'));
				$id_h_pembelian = $get_id_pembelian->id_h_pembelian;
				$no_h_pembelian = $get_id_pembelian->NO_FAKTUR;
				
				$this->M_gl_h_pembelian->simpan
				(
					$id_h_pembelian,
					'', //$_POST['id_supplier'],
					'', //$id_h_retur,
					$no_h_pembelian,
					'', //$nama_h_pembelian,
					date("Y-m-d"), //$_POST['tgl_h_pembelian'],
					'', //$tgl_jatuh_tempo,
					0, //$nominal_transaksi,
					0, //$nominal_retur,
					0, //$bayar_detail,
					0, //$biaya_tambahan,
					0, //$pengurang,
					0, //$ket_h_pembelian,
					'PENDING', //$sts_pembelian,
					$this->session->userdata('ses_id_karyawan'),
					$kode_kantor
				);
		
				//MASUKAN D Pembelian
					$this->M_gl_h_pembelian->proses_looping_analisa_order($kode_kantor,$id_h_pembelian,$this->session->userdata('ses_id_karyawan'));
				//MASUKAN D Pembelian
				//echo"PROSES ANALISA ORDER";
				
				header('Location: '.base_url().'gl-admin-purchase-order/'.$id_h_pembelian);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function view_lap_rata_penjualan()
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
					$cari = "
							WHERE 
							-- kode_kantor LIKE '%".$kode_kantor."%' 
							-- AND COALESCE(sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
							
							CASE WHEN '".$kode_kantor."' = '' THEN
								kode_kantor NOT IN ('SLM') 
							ELSE
								kode_kantor = '".$kode_kantor."' 
							END
							
							AND COALESCE(sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
							AND isProduk <> 'JASA'
							AND COALESCE(DATE(tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
							AND id_produk <> ''
							AND (kode_produk LIKE '%".str_replace("'","",$_GET['cari'])."%' OR nama_produk LIKE '%".str_replace("'","",$_GET['cari'])."%')
					";
					
					
				}
				else
				{
					$cari = "
							WHERE 
							-- kode_kantor LIKE '%".$kode_kantor."%' 
							-- AND COALESCE(sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
							
							CASE WHEN '".$kode_kantor."' = '' THEN
								kode_kantor NOT IN ('SLM') 
							ELSE
								kode_kantor = '".$kode_kantor."' 
							END
							
							AND COALESCE(sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
							AND isProduk <> 'JASA'
							AND COALESCE(DATE(tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
							AND id_produk <> ''
							AND (kode_produk LIKE '%%' OR nama_produk LIKE '%%')
					";
				}
				
				$order_by = "ORDER BY nama_produk ASC";
				$jum_row = $this->M_gl_stock_produk->count_rata_produk_terjual($cari)->JUMLAH;
				
				$this->load->library('pagination');
				
				$config['first_url'] = site_url('gl-admin-laporan-rata-produk-terjual?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-laporan-rata-produk-terjual/');
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
				
				$list_rata_produk_terjual = $this->M_gl_stock_produk->list_rata_produk_terjual($dari,$sampai,$cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				//$sum_stock_produk = $this->M_gl_stock_produk->sum_stock_produk($this->session->userdata('ses_kode_kantor'),$cari,$sampai,'23','59');
				
				//($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Rata-rata produk terjual ";
				
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
				
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor2(
										"
											WHERE kode_kantor NOT IN ('SLM','INV','SAL')
										");
										
				$data = array('page_content'=>'gl_admin_rata_produk_terjual','halaman'=>$halaman,'list_rata_produk_terjual'=>$list_rata_produk_terjual,'msgbox_title' => $msgbox_title, 'sum_pesan' => $sum_pesan,'list_kantor' => $list_kantor);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	public function view_lap_stock_produk_per_supplier()
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
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
					//$kode_kantor = '';
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
				
				
				
				
										
				$data = array('page_content'=>'gl_admin_stock_produk_per_supplier','msgbox_title' => $msgbox_title,'list_kantor' => $list_kantor,'list_stock_produk_per_supplier' => $list_stock_produk_per_supplier);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function view_lap_stock_produk_per_kategori()
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
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
					//$kode_kantor = '';
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
				
				$list_stock_produk_per_kategori = $this->M_gl_stock_produk->list_stock_produk_per_kategori($kode_kantor,$dari,$sampai,0,9999999,$cari);
				
				
										
				$msgbox_title = " Laporan Stock Produk Per Kategori";
				
				
				
				
										
				$data = array('page_content'=>'gl_admin_stock_produk_per_kategori','msgbox_title' => $msgbox_title,'list_kantor' => $list_kantor,'list_stock_produk_per_kategori' => $list_stock_produk_per_kategori);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function view_lap_stock_produk_per_produk_jelek()
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
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
					//$kode_kantor = '';
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
				
				$list_stock_produk_per_jelek = $this->M_gl_stock_produk->list_stock_produk_per_produk_jelek($kode_kantor,$dari,$sampai,$cari);
				
				
										
				$msgbox_title = " Laporan Stock Produk Retur/Jelek/BS";
				
				
				
				
										
				$data = array('page_content'=>'gl_admin_stock_produk_jelek','msgbox_title' => $msgbox_title,'list_kantor' => $list_kantor,'list_stock_produk_per_jelek' => $list_stock_produk_per_jelek);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	public function detail_penjualan()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			/*
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
			*/
				$id_produk = $this->uri->segment(2,0);
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
				
				$data_produk = $this->M_gl_produk->get_produk_with_kode_kantor($kode_kantor,'MD5(id_produk)',$id_produk);
				if(!empty($data_produk))
				{
					$data_produk = $data_produk->row();
					
					
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
					
					if((!empty($_GET['waktu_st_awal'])) && ($_GET['waktu_st_awal']!= "")  )
					{
						$waktu_st_awal = $_GET['waktu_st_awal'];
					}
					else
					{
						$waktu_st_awal = '1900-01-01 00:00:00';
					}
					
					
				
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = " WHERE A.kode_kantor = '".$kode_kantor."' 
						AND B.sts_penjualan NOT IN ('PENDING','DIBATALKAN')
						AND LEFT(B.ket_penjualan,7) <> 'DIHAPUS'
						-- AND A.isReady = 1
						AND MD5(A.id_produk) = '".$id_produk."'
						-- AND COALESCE(DATE(B.tgl_h_penjualan),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_ins > '".$dari." 00:00:00'
						AND A.tgl_ins <= '".$sampai." 23:59:00'
						
						
						AND
							CASE WHEN '".$waktu_st_awal."' > ('".$dari." 00:00:00') THEN
								(A.tgl_ins) > '".$waktu_st_awal."'
							ELSE
								A.tgl_ins > '".$dari." 00:00:00'
							END
						
							
						AND COALESCE(B.sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
						AND (
								COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(B.no_costmer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(B.nama_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
					}
					else
					{
						$cari = " WHERE A.kode_kantor = '".$kode_kantor."'
						AND B.sts_penjualan NOT IN ('PENDING','DIBATALKAN')
						AND LEFT(B.ket_penjualan,7) <> 'DIHAPUS'
						-- AND A.isReady = 1						
						AND MD5(A.id_produk) = '".$id_produk."'
						-- AND COALESCE(DATE(B.tgl_h_penjualan),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_ins > '".$dari." 00:00:00'
						AND A.tgl_ins <= '".$sampai." 23:59:00'
						
						
						AND
							CASE WHEN '".$waktu_st_awal."' > ('".$dari." 00:00:00') THEN
								(A.tgl_ins) > '".$waktu_st_awal."'
							ELSE
								A.tgl_ins > '".$dari." 00:00:00'
							END
						
						AND COALESCE(B.sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
						";
					}
					
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					
					$akumulasi_d_penjualan = $this->M_gl_stock_produk->count_laporan_detail_penjualan($cari);
					
					if(!empty($akumulasi_d_penjualan))
					{
						$jum_row = $this->M_gl_stock_produk->count_laporan_detail_penjualan($cari)->JUMLAH;
					}
					else
					{
						$jum_row =0;
					}
					
					
					$this->load->library('pagination');
					//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
					//$config['base_url'] = base_url().'admin/jabatan/';
					
					$config['first_url'] = site_url('gl-admin-laporan-stock-produk-detail-penjualan/'.$id_produk.'?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-laporan-stock-produk-detail-penjualan/'.$id_produk);
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
					
					$list_detail_penjualan = $this->M_gl_stock_produk->list_laporan_detail_penjualan($cari,$order_by,$config['per_page'],$this->uri->segment(3,0));
					
					$msgbox_title = " Laporan Detail Penjualan Produk ".$data_produk->nama_produk;
					
					
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
					
					$data = array('page_content'=>'gl_admin_stock_produk_detail_penjualan','halaman'=>$halaman,'list_detail_penjualan'=>$list_detail_penjualan,'msgbox_title' => $msgbox_title,'sum_pesan' => $sum_pesan,'data_produk'=>$data_produk,'akumulasi_d_penjualan' => $akumulasi_d_penjualan,'dari' => $dari,'sampai'=>$sampai);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-laporan-stock-produk');
				}
			/*
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
			*/
		}
	}

	function detail_penerimaan()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			/*
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
			*/
				$id_produk = $this->uri->segment(2,0);
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
				
				if((!empty($_GET['waktu_st_awal'])) && ($_GET['waktu_st_awal']!= "")  )
				{
					$waktu_st_awal = $_GET['waktu_st_awal'];
				}
				else
				{
					$waktu_st_awal = '1900-01-01 00:00:00';
				}
					
				
				$data_produk = $this->M_gl_produk->get_produk_with_kode_kantor($kode_kantor,'MD5(id_produk)',$id_produk);
				if(!empty($data_produk))
				{
					$data_produk = $data_produk->row();
					
					
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
						$cari = " WHERE A.kode_kantor = '".$kode_kantor."' 
						AND MD5(A.id_produk) = '".$id_produk."'
						-- AND COALESCE(DATE(B.tgl_terima),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_ins > '".$dari." 00:00:00'
						AND A.tgl_ins <= '".$sampai." 23:59:00'
						
						AND
							CASE WHEN '".$waktu_st_awal."' > ('".$dari." 00:00:00') THEN
								(A.tgl_ins) > '".$waktu_st_awal."'
							ELSE
								A.tgl_ins > '".$dari." 00:00:00'
							END
							
						AND (
								COALESCE(B.no_surat_jalan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(B.nama_pengirim,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.no_h_pembelian,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
					}
					else
					{
						$cari = " WHERE A.kode_kantor = '".$kode_kantor."' 
						AND MD5(A.id_produk) = '".$id_produk."'
						-- AND COALESCE(DATE(B.tgl_terima),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
						AND A.tgl_ins > '".$dari." 00:00:00'
						AND A.tgl_ins <= '".$sampai." 23:59:00'
						
						AND
							CASE WHEN '".$waktu_st_awal."' > ('".$dari." 00:00:00') THEN
								(A.tgl_ins) > '".$waktu_st_awal."'
							ELSE
								A.tgl_ins > '".$dari." 00:00:00'
							END
						
						";
					}
					
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					
					
					$akumulasi_d_penerimaan = $this->M_gl_stock_produk->count_laporan_detail_penerimaan($cari);
					if(!empty($akumulasi_d_penerimaan))
					{
						$jum_row = $this->M_gl_stock_produk->count_laporan_detail_penerimaan($cari)->JUMLAH;
					}
					else
					{
						$jum_row = 0;
					}
					
					
					$this->load->library('pagination');
					//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
					//$config['base_url'] = base_url().'admin/jabatan/';
					
					$config['first_url'] = site_url('gl-admin-laporan-stock-produk-detail-penerimaan/'.$id_produk.'?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-laporan-stock-produk-detail-penerimaan/'.$id_produk);
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
					
					$list_detail_penerimaan = $this->M_gl_stock_produk->list_laporan_detail_penerimaan($cari,$order_by,$config['per_page'],$this->uri->segment(3,0));
					
					$msgbox_title = " Laporan Detail Penerimaan/Pembelian Produk ".$data_produk->nama_produk;
					
					
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
					
					$data = array('page_content'=>'gl_admin_stock_produk_detail_penerimaan','halaman'=>$halaman,'list_detail_penerimaan'=>$list_detail_penerimaan,'msgbox_title' => $msgbox_title,'sum_pesan' => $sum_pesan,'data_produk'=>$data_produk,'akumulasi_d_penerimaan' => $akumulasi_d_penerimaan,'dari'=> $dari,'sampai'=>$sampai);
					$this->load->view('admin/container',$data);
				}
				else
				{
					//header('Location: '.base_url().'gl-admin-laporan-stock-produk');
				}
			/*
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
			*/
		}
	}
	
	
	function detail_mutasi_in()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			/*
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
			*/
				$id_produk = $this->uri->segment(2,0);
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
				
				$data_produk = $this->M_gl_produk->get_produk_with_kode_kantor($kode_kantor,'MD5(id_produk)',$id_produk);
				if(!empty($data_produk))
				{
					$data_produk = $data_produk->row();
					
					
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
					
					if((!empty($_GET['waktu_st_awal'])) && ($_GET['waktu_st_awal']!= "")  )
					{
						$waktu_st_awal = $_GET['waktu_st_awal'];
					}
					else
					{
						$waktu_st_awal = '1900-01-01 00:00:00';
					}
				
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = " 
							WHERE A.kode_kantor = '".$kode_kantor."'
							-- AND COALESCE(B.sts_penjualan,'') = 'SELESAI'
							AND COALESCE(B.status_penjualan,'') = 'MUTASI-IN'
							AND MD5(A.id_produk) = '".$id_produk."'
							-- AND COALESCE(DATE(B.tgl_h_penjualan),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
							
							AND A.tgl_ins > '".$dari." 00:00:00'
							AND A.tgl_ins <= '".$sampai." 23:59:00'
						
							AND
							CASE WHEN '".$waktu_st_awal."' > ('".$dari." 00:00:00') THEN
								(A.tgl_ins) > '".$waktu_st_awal."'
							ELSE
								A.tgl_ins > '".$dari." 00:00:00'
							END
							
							AND (
									COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
						";
					}
					else
					{
						$cari = " 
							WHERE A.kode_kantor = '".$kode_kantor."'
							-- AND COALESCE(B.sts_penjualan,'') = 'SELESAI'
							AND COALESCE(B.status_penjualan,'') = 'MUTASI-IN'
							AND MD5(A.id_produk) = '".$id_produk."'
							-- AND COALESCE(DATE(B.tgl_h_penjualan),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
							AND A.tgl_ins > '".$dari." 00:00:00'
							AND A.tgl_ins <= '".$sampai." 23:59:00'
						
							AND
							CASE WHEN '".$waktu_st_awal."' > ('".$dari." 00:00:00') THEN
								(A.tgl_ins) > '".$waktu_st_awal."'
							ELSE
								A.tgl_ins > '".$dari." 00:00:00'
							END
							
						";
					}
					
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					
					$jum_row = $this->M_gl_stock_produk->count_laporan_detail_mutasi($cari)->JUMLAH;
					
					$this->load->library('pagination');
					//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
					//$config['base_url'] = base_url().'admin/jabatan/';
					
					$config['first_url'] = site_url('gl-admin-laporan-stock-produk-detail-mutasiin/'.$id_produk.'?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-laporan-stock-produk-detail-mutasiin/'.$id_produk);
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
					
					$list_detail_mutasiin = $this->M_gl_stock_produk->list_laporan_detail_mutasi($cari,$order_by,$config['per_page'],$this->uri->segment(3,0));
					
					$msgbox_title = " Laporan Detail Mutasi IN/Masuk Produk ".$data_produk->nama_produk;
					
					
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
					
					$data = array('page_content'=>'gl_admin_stock_produk_detail_mutasiin','halaman'=>$halaman,'list_detail_mutasiin'=>$list_detail_mutasiin,'msgbox_title' => $msgbox_title,'sum_pesan' => $sum_pesan,'data_produk'=>$data_produk);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-laporan-stock-produk');
				}
			
			/*
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
			*/
		}
	}

	function detail_mutasi_out()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			/*
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
			*/
				$id_produk = $this->uri->segment(2,0);
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
					
				
				$data_produk = $this->M_gl_produk->get_produk_with_kode_kantor($kode_kantor,'MD5(id_produk)',$id_produk);
				if(!empty($data_produk))
				{
					$data_produk = $data_produk->row();
					
					
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
					
					if((!empty($_GET['waktu_st_awal'])) && ($_GET['waktu_st_awal']!= "")  )
					{
						$waktu_st_awal = $_GET['waktu_st_awal'];
					}
					else
					{
						$waktu_st_awal = '1900-01-01 00:00:00';
					}
					
				
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = " 
							WHERE A.kode_kantor = '".$kode_kantor."'
							-- AND COALESCE(B.sts_penjualan,'') = 'SELESAI'
							AND COALESCE(B.status_penjualan,'') = 'MUTASI-OUT'
							AND MD5(A.id_produk) = '".$id_produk."'
							-- AND COALESCE(DATE(B.tgl_h_penjualan),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
							
							AND A.tgl_ins > '".$dari." 00:00:00'
							AND A.tgl_ins <= '".$sampai." 23:59:00'
							AND
							CASE WHEN '".$waktu_st_awal."' > ('".$dari." 00:00:00') THEN
								(A.tgl_ins) > '".$waktu_st_awal."'
							ELSE
								A.tgl_ins > '".$dari." 00:00:00'
							END
							
							AND (
									COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
						";
					}
					else
					{
						$cari = " 
							WHERE A.kode_kantor = '".$kode_kantor."'
							-- AND COALESCE(B.sts_penjualan,'') = 'SELESAI'
							AND COALESCE(B.status_penjualan,'') = 'MUTASI-OUT'
							AND MD5(A.id_produk) = '".$id_produk."'
							-- AND COALESCE(DATE(B.tgl_h_penjualan),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
							
							AND A.tgl_ins > '".$dari." 00:00:00'
							AND A.tgl_ins <= '".$sampai." 23:59:00'
							AND
							CASE WHEN '".$waktu_st_awal."' > ('".$dari." 00:00:00') THEN
								(A.tgl_ins) > '".$waktu_st_awal."'
							ELSE
								A.tgl_ins > '".$dari." 00:00:00'
							END
						";
					}
					
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					
					$jum_row = $this->M_gl_stock_produk->count_laporan_detail_mutasi($cari)->JUMLAH;
					
					$this->load->library('pagination');
					//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
					//$config['base_url'] = base_url().'admin/jabatan/';
					
					$config['first_url'] = site_url('gl-admin-laporan-stock-produk-detail-mutasiout/'.$id_produk.'?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-laporan-stock-produk-detail-mutasiout/'.$id_produk);
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
					
					$list_detail_mutasiout = $this->M_gl_stock_produk->list_laporan_detail_mutasi($cari,$order_by,$config['per_page'],$this->uri->segment(3,0));
					
					$msgbox_title = " Laporan Detail Mutasi Out/Pemakaian Produk ".$data_produk->nama_produk;
					
					
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
					
					$data = array('page_content'=>'gl_admin_stock_produk_detail_mutasiout','halaman'=>$halaman,'list_detail_mutasiout'=>$list_detail_mutasiout,'msgbox_title' => $msgbox_title,'sum_pesan' => $sum_pesan,'data_produk'=>$data_produk);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-laporan-stock-produk');
				}
			/*
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
			*/
		}
	}

	function detail_retur_pembelian()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			/*
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
			*/
				$id_produk = $this->uri->segment(2,0);
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
					
				
				$data_produk = $this->M_gl_produk->get_produk_with_kode_kantor($kode_kantor,'MD5(id_produk)',$id_produk);
				if(!empty($data_produk))
				{
					$data_produk = $data_produk->row();
					
					
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
					
					if((!empty($_GET['waktu_st_awal'])) && ($_GET['waktu_st_awal']!= "")  )
					{
						$waktu_st_awal = $_GET['waktu_st_awal'];
					}
					else
					{
						$waktu_st_awal = '1900-01-01 00:00:00';
					}
				
				
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = " 
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') = 'SELESAI'
							-- AND COALESCE(B.status_penjualan,'') = 'RETUR-PEMBELIAN'  -- DIGABUNG DENGAN RETUR PENJUALAN, NANTI TINGGAL KASH KETERANGAN DI TAMPILAN
							AND MD5(A.id_produk) = '".$id_produk."'
							-- AND COALESCE(DATE(B.tgl_h_penjualan),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
							
							AND A.tgl_ins > '".$dari." 00:00:00'
							AND A.tgl_ins <= '".$sampai." 23:59:00'
							AND
							CASE WHEN '".$waktu_st_awal."' > ('".$dari." 00:00:00') THEN
								(A.tgl_ins) > '".$waktu_st_awal."'
							ELSE
								A.tgl_ins > '".$dari." 00:00:00'
							END
							
							AND (
									COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(C.kode_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(B.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
						";
					}
					else
					{
						$cari = " 
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND COALESCE(B.sts_penjualan,'') = 'SELESAI'
							-- AND COALESCE(B.status_penjualan,'') = 'RETUR-PEMBELIAN' -- DIGABUNG DENGAN RETUR PENJUALAN, NANTI TINGGAL KASH KETERANGAN DI TAMPILAN
							AND MD5(A.id_produk) = '".$id_produk."'
							-- AND COALESCE(DATE(B.tgl_h_penjualan),'1900-01-01') BETWEEN '".$dari."' AND '".$sampai."'
							
							AND A.tgl_ins > '".$dari." 00:00:00'
							AND A.tgl_ins <= '".$sampai." 23:59:00'
							
							AND
							CASE WHEN '".$waktu_st_awal."' > ('".$dari." 00:00:00') THEN
								(A.tgl_ins) > '".$waktu_st_awal."'
							ELSE
								A.tgl_ins > '".$dari." 00:00:00'
							END
						";
					}
					
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					
					$jum_row = $this->M_gl_stock_produk->count_laporan_detail_retur($cari)->JUMLAH;
					
					$this->load->library('pagination');
					//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
					//$config['base_url'] = base_url().'admin/jabatan/';
					
					$config['first_url'] = site_url('gl-admin-laporan-stock-produk-detail-retur-pembelian/'.$id_produk.'?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-laporan-stock-produk-detail-retur-pembelian/'.$id_produk);
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
					
					$list_detail_retur_pembelian = $this->M_gl_stock_produk->list_laporan_detail_retur($cari,$order_by,$config['per_page'],$this->uri->segment(3,0));
					
					$msgbox_title = " Laporan Detail Retur Ke Supplier/Pembelian Produk ".$data_produk->nama_produk;
					
					
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
					
					$data = array('page_content'=>'gl_admin_stock_produk_detail_retur_pembelian','halaman'=>$halaman,'list_detail_retur_pembelian'=>$list_detail_retur_pembelian,'msgbox_title' => $msgbox_title,'sum_pesan' => $sum_pesan,'data_produk'=>$data_produk);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-laporan-stock-produk');
				}
			/*
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
			*/
		}
	}
	
	function detail_histori_produk()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			/*
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
			*/
				$id_produk = $this->uri->segment(2,0);
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
					
				$data_produk = $this->M_gl_produk->get_produk_with_kode_kantor($kode_kantor,'MD5(id_produk)',$id_produk);
				if(!empty($data_produk))
				{
					$data_produk = $data_produk->row();
					
					
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
					
					if((!empty($_GET['waktu_st_awal'])) && ($_GET['waktu_st_awal']!= "")  )
					{
						$waktu_st_awal = $_GET['waktu_st_awal'];
					}
					else
					{
						$waktu_st_awal = '1900-01-01 00:00:00';
					}
					
					if((!empty($_GET['st_awal'])) && ($_GET['st_awal']!= "")  )
					{
						$st_awal = $_GET['st_awal'];
					}
					else
					{
						$st_awal = 0;
					}
					
					$list_detail_histori = $this->M_gl_stock_produk->list_histori_produk($kode_kantor,$data_produk->id_produk,$dari,$sampai,$waktu_st_awal,$st_awal);
					
					$msgbox_title = " Laporan History Produk ".$data_produk->nama_produk;
					
					$data = array('page_content'=>'gl_admin_stock_produk_detail_histori_produk','list_detail_histori'=>$list_detail_histori,'msgbox_title' => $msgbox_title,'data_produk'=>$data_produk,'waktu_st_awal' => $waktu_st_awal,'st_awal' => $st_awal);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-laporan-stock-produk');
				}
			/*
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
			*/
		}
	}
	
	
	public function excel_stock_produk()
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
					$cari = str_replace("'","",$_GET['cari']);
				}
				else
				{
					$cari = "";
				}
				
				$data_kantor = $this->M_gl_pengaturan->get_data_kantor(" WHERE kode_kantor = '".$kode_kantor."'");
				
				if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
				{
					$list_stock_produk = $this->M_gl_pst_inventori->list_stock_produk($kode_kantor,$dari,$sampai,0,30000,$cari);
				}
				else
				{
					if($this->session->userdata('ses_kode_kantor') == 'PST')
					{
						$list_stock_produk = $this->M_gl_pst_inventori->list_stock_produk($kode_kantor,$dari,$sampai,0,30000,$cari);
					}
					else
					{
						$list_stock_produk = $this->M_gl_pst_inventori->list_stock_produk_harga_jual($kode_kantor,$dari,$sampai,0,30000,$cari);
					}
				}
				
				
				//list_stock_produk_harga_jual
				
				
				//$sum_stock_produk = $this->M_gl_stock_produk->sum_stock_produk($this->session->userdata('ses_kode_kantor'),$cari,$sampai,'23','59');
				
				//($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$data = array('page_content'=>'gl_admin_excel_stock','list_stock_produk'=>$list_stock_produk,'data_kantor' => $data_kantor);
				$this->load->view('admin/page/gl_admin_excel_stock.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function excel_view_lap_rata_penjualan()
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
					$cari = "
							WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND COALESCE(sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
							AND isProduk <> 'JASA'
							AND COALESCE(DATE(tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
							AND id_produk <> ''
							AND (kode_produk LIKE '%".str_replace("'","",$_GET['cari'])."%' OR nama_produk LIKE '%".str_replace("'","",$_GET['cari'])."%')
					";
					
					
				}
				else
				{
					$cari = "
							WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND COALESCE(sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
							AND isProduk <> 'JASA'
							AND COALESCE(DATE(tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
							AND id_produk <> ''
							AND (kode_produk LIKE '%%' OR nama_produk LIKE '%%')
					";
				}
				
				$order_by = "ORDER BY nama_produk ASC";
				
				$list_rata_produk_terjual = $this->M_gl_stock_produk->list_rata_produk_terjual($dari,$sampai,$cari,$order_by,10000,0);
				
				//$sum_stock_produk = $this->M_gl_stock_produk->sum_stock_produk($this->session->userdata('ses_kode_kantor'),$cari,$sampai,'23','59');
				
				//($cari,$config['per_page'],$this->uri->segment(2,0));
				
				
				
				$data = array('page_content'=>'gl_admin_excel_rata_produk','list_rata_produk_terjual'=>$list_rata_produk_terjual);
				//$this->load->view('admin/container',$data);
				$this->load->view('admin/page/gl_admin_excel_rata_produk.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function excel_view_analisa_order()
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
				if((!empty($_GET['sampai'])) && ($_GET['sampai']!= "")  )
				{
					$sampai = $_GET['sampai'];
				}
				else
				{
					$sampai = date("Y-m-d");
				}
				
				
				
				$list_stock_produk = $this->M_gl_stock_produk->list_analisa_order($this->session->userdata('ses_kode_kantor'),"",' PROD.nama_produk ASC',0,10000,$sampai,'23','59','');
				
				//$sum_stock_produk = $this->M_gl_stock_produk->sum_stock_produk($this->session->userdata('ses_kode_kantor'),$cari,$sampai,'23','59');
				
				//($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$data = array('page_content'=>'gl_admin_excel_analisa_order','list_stock_produk'=>$list_stock_produk);
				$this->load->view('admin/page/gl_admin_excel_analisa_order.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function simpan_temp_d_pembelian()
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
				$cari = " WHERE id_produk = '".$_POST['id_produk']."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				$cek_produk_di_temp_d_pembelian = $this->M_gl_temp_d_pembelian->get_temp_d_pembelian_by_query($cari);
				
				if(!empty($cek_produk_di_temp_d_pembelian))
				{
					$this->M_gl_temp_d_pembelian->hapus
					(
						$cari
					);
					
					echo"DELETE";
				}
				else
				{
					$this->M_gl_temp_d_pembelian->simpan
					(
						$_POST['id_produk'],
						$_POST['jumlah'],
						$_POST['harga'],
						$_POST['harga_dasar'],
						'0', //$_POST['diskon'],
						'%', //$_POST['optr_diskon'],
						$_POST['kode_satuan'],
						$_POST['nama_satuan'],
						'*', //$_POST['status_konversi'],
						'1', //$_POST['konversi'],
						'1', //$_POST['acc'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					);
					
					echo"SUKSES";
				}
				//header('Location: '.base_url().'gl-admin-analisa-order');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	function update_ajax_penerimaan()
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
				$kode_kantor=$_POST['kode_kantor'];
				$id_d_penerimaan=$_POST['id_d_penerimaan'];
				$id_produk=$_POST['id_produk'];
				$konversi=$_POST['konversi'];
				$harga_beli=str_replace(",","",$_POST['harga_beli']);
				$nama_produk=$_POST['nama_produk'];
				$kode_satuan=$_POST['kode_satuan'];
				$no_surat_jalan=$_POST['no_surat_jalan'];
				$diterima_satuan_beli=$_POST['diterima_satuan_beli'];
				$status_konversi=$_POST['status_konversi'];
				
				if($status_konversi == '*')
				{
					$diterima = $diterima_satuan_beli * $konversi;
				}
				else
				{
					$diterima = $diterima_satuan_beli / $konversi;
				}
				
				//UPDATE
				$this->M_gl_stock_produk->M_ajax_update_penerimaan($kode_kantor,$id_d_penerimaan,$id_produk,$konversi,$harga_beli);
				
				/* CATAT AKTIFITAS EDIT*/
				if($this->session->userdata('catat_log') == 'Y')
				{
					$this->M_gl_log->simpan_log
					(
						$this->session->userdata('ses_id_karyawan'),
						'UPDATE',
						'Melakukan perubahan konversi dan harga beli penerimaan pada produk '.$_POST['nama_produk'].' dengan surat jalan/Invoice '.$_POST['no_surat_jalan'].' dengan konversi baru '.$konversi,
						$this->M_gl_pengaturan->getUserIpAddr(),
						gethostname(),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				/* CATAT AKTIFITAS EDIT*/
				//header('Location: '.base_url().'gl-admin-analisa-order');
				echo $diterima;
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function update_ajax_penjualan()
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
				
				$kode_kantor=$_POST['kode_kantor'];
				$id_d_penjualan=$_POST['id_d_penjualan'];
				$id_produk=$_POST['id_produk'];
				$konversi=$_POST['konversi'];
				$harga=str_replace(",","",$_POST['harga']);
				$nama_produk=$_POST['nama_produk'];
				$satuan_jual=$_POST['satuan_jual'];
				$no_faktur=$_POST['no_faktur'];
				$jumlah=$_POST['jumlah'];
				$status_konversi=$_POST['status_konversi'];
				
				
				if($status_konversi == '*')
				{
					$dikeluarkan = $jumlah * $konversi;
				}
				else
				{
					$dikeluarkan = $jumlah / $konversi;
				}
				
				//UPDATE
				$this->M_gl_stock_produk->M_ajax_update_penjualan($kode_kantor,$id_d_penjualan,$id_produk,$konversi,$harga);
				
				
				/* CATAT AKTIFITAS EDIT*/
				if($this->session->userdata('catat_log') == 'Y')
				{
					$this->M_gl_log->simpan_log
					(
						$this->session->userdata('ses_id_karyawan'),
						'UPDATE',
						'Melakukan perubahan konversi dan harga jual penjualan pada produk '.$_POST['nama_produk'].' dengan no faktur '.$_POST['no_faktur'].' Dengan konversi baru '.$konversi,
						$this->M_gl_pengaturan->getUserIpAddr(),
						gethostname(),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				/* CATAT AKTIFITAS EDIT*/
				//header('Location: '.base_url().'gl-admin-analisa-order');
				echo $dikeluarkan;
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