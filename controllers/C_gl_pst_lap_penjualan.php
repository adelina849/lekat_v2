<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_lap_penjualan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_lap_penjualan','M_gl_pst_lap_penjualan','M_gl_images'));
		
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
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
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
							WHERE A.kode_kantor LIKE '%".$kode_kantor."%' 
							
							
							
							AND 
							CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
								A.kode_kantor LIKE '%%'
							ELSE
								A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
							END
							
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND COALESCE(C.isProduk,'') IN ('PRODUK','JASA')
							AND A.id_d_penerimaan = ''
							AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
							(
								COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(B.no_costmer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(B.nama_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(E.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
							
					$cari_h = "
								WHERE A.kode_kantor LIKE '%".$kode_kantor."%'
								
								AND 
								CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
									A.kode_kantor LIKE '%%'
								ELSE
									A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
								END
								
								AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
								AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
								AND C.nominal > 0
								AND 
								(
									COALESCE(A.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(A.no_costmer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(A.nama_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
							";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor LIKE '%".$kode_kantor."%' 
							
							AND 
							CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
								A.kode_kantor LIKE '%%'
							ELSE
								A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
							END
							
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND COALESCE(C.isProduk,'') IN ('PRODUK','JASA')
							AND A.id_d_penerimaan = ''
							AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
						
					$cari_h = "
								WHERE A.kode_kantor LIKE '%".$kode_kantor."%' 
								
								AND 
								CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
									A.kode_kantor LIKE '%%'
								ELSE
									A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
								END
								
								AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
								AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
								AND C.nominal > 0
							";
				}
				$order_by = "ORDER BY B.no_faktur DESC,A.isProduk ASC";
				
				
				/*
				$cari_deft = "
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					-- AND COALESCE(B.sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
					AND COALESCE(B.sts_penjualan,'') = 'SELESAI'
					AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."' ";
					
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE
							(
								COALESCE(BBB.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(BBB.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "";
				}
				$order_by = "ORDER BY COALESCE(BBB.isProduk,'') ASC, COALESCE(BBB.nama_produk,'') ASC ,COALESCE(BBB.kode_produk,'') ASC";
				*/
				
				
				//UNTUK AKUMULASI INFO
					$jum_row = 1000000; //$this->M_gl_lap_penjualan->count_laporan_detail($cari)->JUMLAH;
				//UNTUK AKUMULASI INFO
				
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				//$config['first_url'] = site_url('gl-admin-laporan-transaksi?'.http_build_query($_GET));
				$config['first_url'] = site_url('gl-admin-laporan-list-detail-penjualan-row?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-laporan-list-detail-penjualan-row/');
				$config['total_rows'] = $jum_row;
				$config['uri_segment'] = 2;	
				//$config['uri_segment'] = $_GET['var_offset'];	
				$config['per_page'] = 10000;
				//$config['per_page'] = 10000;
				$config['num_links'] = 2;
				//$config['num_links'] = 20;
				//$config['suffix'] = '?' . http_build_query($_GET, '', "&");
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
				
				//$list_laporan_d_penjualan_row = $this->M_gl_lap_penjualan->list_laporan_detail($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
				{
					$list_laporan_d_penjualan_row = $this->M_gl_pst_lap_penjualan->list_laporan_detail_for_toko($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				}
				else
				{
					$list_laporan_d_penjualan_row = $this->M_gl_pst_lap_penjualan->list_laporan_detail($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				}
				
				
				/*
				$cari_dokter = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND isAKtif = 'DITERIMA' AND isDokter = 'DOKTER'";
				$list_dokter = $this->M_gl_karyawan->get_karyawan_cari($cari_dokter);
				
				$cari_therapist = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND isAKtif = 'DITERIMA' AND isDokter = 'THERAPIST'";
				$list_therapist = $this->M_gl_karyawan->get_karyawan_cari($cari_therapist);
				*/
				
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
				
				//SUMMARY
					$sum_laporan_h_penjualan = $this->M_gl_lap_penjualan->count_h_penjualan($cari_h);
					//$sum_laporan_h_penjualan = $sum_laporan_h_penjualan->row();
				//SUMMARY
				
				//SUMMARY PRODUK isReport
					$sum_laporan_d_penjualan_produk_isReport = $this->M_gl_pst_lap_penjualan->sum_laporan_d_penjualan_produk_isRport($this->session->userdata('ses_kode_kantor'),$dari,$sampai," AND A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor) ");
				//SUMMARY PRODUK isReport
				
				//DATA KANTOR
					$list_kantor = $this->M_gl_pengaturan->get_data_kantor(" WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END ");
				//DATA KANTOR
				
				
				$msgbox_title = " Laporan Transaksi Detail ";
				
				$data = array('page_content'=>'gl_pusat_lap_d_penjualan_row','halaman'=>$halaman, 'sum_pesan' => $sum_pesan,'msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_row' => $list_laporan_d_penjualan_row, 'sum_laporan_h_penjualan' => $sum_laporan_h_penjualan,'sum_laporan_d_penjualan_produk_isReport' => $sum_laporan_d_penjualan_produk_isReport,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
				
				//$data = array('page_content'=>'gl_pusat_lap_h_penjualan','msgbox_title' => $msgbox_title,'list_laporan_h_penjualan' => $list_laporan_h_penjualan);
				//$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url());
			}
		}
	}
	
	public function produk_terjual_ori()
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
							WHERE AA.kode_kantor LIKE '%".$kode_kantor."%'
							AND AA.kode_kantor <> 'PST'
							AND AA.sts_penjualan IN ('SELESAI','PEMBAYARAN')
							AND DATE(COALESCE(AA.tgl_h_penjualan,'')) BETWEEN '".$dari."' AND '".$sampai."'
							AND BB.isProduk IN ('JASA','PRODUK')
							AND AA.harga_setelah_diskon > 0
							AND 
							(
								COALESCE(BB.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(BB.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
							
				}
				else
				{
					$cari = "
							WHERE AA.kode_kantor LIKE '%".$kode_kantor."%'
							AND AA.kode_kantor <> 'PST'
							AND AA.sts_penjualan IN ('SELESAI','PEMBAYARAN')
							AND DATE(COALESCE(AA.tgl_h_penjualan,'')) BETWEEN '".$dari."' AND '".$sampai."'
							AND BB.isProduk IN ('JASA','PRODUK')
							AND AA.harga_setelah_diskon > 0
						";
						
				}
				$order_by = "ORDER BY B.no_faktur DESC,A.isProduk ASC";
				
				$list_laporan_d_penjualan_produk = $this->M_gl_pst_lap_penjualan->list_laporan_produk_jasa_terjual_hanya_kode_nama($cari);
				
				//DATA KANTOR
					$list_kantor = $this->M_gl_pengaturan->get_data_kantor(" WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END ");
				//DATA KANTOR
				
				
				$msgbox_title = " Laporan Transaksi Detail Produk/Jasa";
				
				$data = array('page_content'=>'gl_pusat_lap_d_penjualan_produk','msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_produk' => $list_laporan_d_penjualan_produk,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
				
				//$data = array('page_content'=>'gl_pusat_lap_h_penjualan','msgbox_title' => $msgbox_title,'list_laporan_h_penjualan' => $list_laporan_h_penjualan);
				//$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url());
			}
		}
	}
	
	public function produk_terjual()
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
					$isian_cari = str_replace("'","",$_GET['cari']);
					$cari = "
							WHERE B.sts_penjualan IN ('SELESAI','PEMBAYARAN') 
							AND DATE(B.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."'
							AND A.kode_kantor <> 'PST'
							AND A.kode_kantor LIKE '%".$kode_kantor."%'
							
							AND CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
								A.kode_kantor LIKE '%".$kode_kantor."%'
							ELSE
								A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
							END
							
							AND CASE WHEN A.optr_diskon = '%' THEN
								(A.harga - ((A.harga/100) * A.diskon)) > 0
							ELSE
								(A.harga - A.diskon) > 0
							END
							";
							
				}
				else
				{
					$isian_cari = "";
					$cari = "
							WHERE B.sts_penjualan IN ('SELESAI','PEMBAYARAN') 
							AND DATE(B.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."'
							AND A.kode_kantor <> 'PST'
							AND A.kode_kantor LIKE '%".$kode_kantor."%'
							
							AND CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
								A.kode_kantor LIKE '%".$kode_kantor."%'
							ELSE
								A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
							END
							
							AND CASE WHEN A.optr_diskon = '%' THEN
								(A.harga - ((A.harga/100) * A.diskon)) > 0
							ELSE
								(A.harga - A.diskon) > 0
							END
						";
						
				}
				$order_by = "ORDER BY B.no_faktur DESC,A.isProduk ASC";
				
				$list_laporan_d_penjualan_produk = $this->M_gl_pst_lap_penjualan->list_laporan_produk_jasa_terjual_by_kategori($cari,$isian_cari);
				
				//DATA KANTOR
					$list_kantor = $this->M_gl_pengaturan->get_data_kantor(" WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END ");
				//DATA KANTOR
				
				
				$msgbox_title = " Laporan Transaksi Detail Produk/Jasa";
				
				$data = array('page_content'=>'gl_pusat_lap_d_penjualan_produk','msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_produk' => $list_laporan_d_penjualan_produk,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
				
				//$data = array('page_content'=>'gl_pusat_lap_h_penjualan','msgbox_title' => $msgbox_title,'list_laporan_h_penjualan' => $list_laporan_h_penjualan);
				//$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url());
			}
		}
	}
	
	public function lap_produk_terjual_detail_v2()
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
							WHERE A.kode_kantor LIKE '%".$kode_kantor."%' 
							
							AND 
							CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
								A.kode_kantor LIKE '%%'
							ELSE
								A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
							END
							
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = ''  -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
							(
								COALESCE(A.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(A.no_costmer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(A.nama_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor LIKE '%".$kode_kantor."%' 
							
							AND 
							CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
								A.kode_kantor LIKE '%%'
							ELSE
								A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
							END
							
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				$order_by = "ORDER BY A.no_faktur ASC,B.tgl_ins DESC";
				$limit = "LIMIT 0,1000000";
				$list_laporan_d_penjualan_pengeluaran_produk = $this->M_gl_lap_penjualan->list_pengeluaran($cari,$order_by,$limit);
				
				//DATA KANTOR
					$list_kantor = $this->M_gl_pengaturan->get_data_kantor(" WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END ");
				//DATA KANTOR
				
				$msgbox_title = " Laporan Pengeluaran Produk/Jasa ";
				
				$data = array('page_content'=>'gl_admin_lap_d_penjualan_pengeluaran_produk','msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_pengeluaran_produk' => $list_laporan_d_penjualan_pengeluaran_produk,'list_kantor' => $list_kantor
				);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url());
			}
		}
	}
	
	
	function lap_produk_keluar_by_kategori_produk_akum()
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
				if((!empty($_POST['kode_kantor'])) && ($_POST['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_POST['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
				}
				
				if((!empty($_POST['dari'])) && ($_POST['dari']!= "")  )
				{
					$dari = $_POST['dari'];
					$sampai = $_POST['sampai'];
				}
				else
				{
					$dari = date("Y-m-d");
					$sampai = date("Y-m-d");
				}
				
				
				
				$cari = 
				"
					WHERE B.sts_penjualan IN ('SELESAI','PEMBAYARAN') 
					AND DATE(B.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."'
					AND A.kode_kantor <> 'PST'
					AND A.kode_kantor LIKE '%".$kode_kantor."%'
					
					AND CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
						A.kode_kantor LIKE '%".$kode_kantor."%'
					ELSE
						A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
					END
							
					AND CASE WHEN A.optr_diskon = '%' THEN
						(A.harga - ((A.harga/100) * A.diskon)) > 0
					ELSE
						(A.harga - A.diskon) > 0
					END
				";
				
				
				$list_laporan_d_pengeluaran_produk = $this->M_gl_pst_lap_penjualan->list_laporan_produk_jasa_terjual_by_kategori_by_akum_produk($cari,$_POST['id_kat_produk']);
				
				echo'<div class="box-body table-responsive no-padding">';
					if(!empty($list_laporan_d_pengeluaran_produk))
					{
						echo'<table width="100%" id="example2" class="table table-hover">';
							echo '<thead>
<tr>';
										echo '<th width="5%">NO</th>';
										echo '<th width="15%">KODE</th>';
										echo '<th width="35%">NAMA PRODUK</th>';
										echo '<th width="10%">BANYAK</th>';
										echo '<th width="20%">NOMINAL</th>';
										echo '<th width="10%">DETAIL</th>';
										//echo '<th width="15%">CABANG</th>';
							echo '</tr>
</thead>';
							$list_result = $list_laporan_d_pengeluaran_produk->result();
							$no =1;
							$total = 0;
							echo '<tbody>';
							foreach($list_result as $row)
							{
								echo'<tr>';
									echo'<td>'.$no.'</td>';
									
									echo '<td>'.$row->kode_produk.'</td>';
									echo '<td>'.$row->nama_produk.'</td>';
									echo '<td>'.number_format($row->CNT,0,'.',',').'</td>';
									echo '<td>'.number_format($row->NOMINAL,0,'.',',').'</td>';
									echo '<td>
										<a href="javascript:void(0)" class="btn btn-success btn-sm btn-flat" data-toggle="modal" data-target="#myModal_pengeluaran_produk" id="DET_KELUAR-'.$row->id_produk.'"  onclick="tampilkan_detail_produk_by_kategori_by_faktur(this)" title = "Detail Pengeluaran '.$row->nama_produk.'" alt = "Detail Pengeluaran '.$row->nama_produk.'">DETAIL</a>
									</td>';
									//echo '<td>'.$row->kode_kantor.'</td>';
									
									
									//echo '<th width="10%" style="text-align:right;">'.number_format($grandTotal,0,'.',',').'</th>';
									
									$total = $total + $row->NOMINAL;
								echo'</tr>';
								
								
								
								
								$no++;
							}
							echo'
								<tr>
									<td></td>
									<td></td>
									<td></td>
									<td><b>TOTAL</b></td>
									<td><b>'.number_format($total,0,'.',',').'</b></td>
								</tr>';
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
								
					echo'
				</div><!-- /.box-body -->
				';
			}
			else
			{
				header('Location: '.base_url());
			}
		}
	}
	
	function list_d_produk_keluar()
	{
		if((!empty($_POST['kode_kantor'])) && ($_POST['kode_kantor']!= "")  )
		{
			$kode_kantor = str_replace("'","",$_POST['kode_kantor']);
		}
		else
		{
			$kode_kantor = "";
		}
		
		if((!empty($_POST['dari'])) && ($_POST['dari']!= "")  )
		{
			$dari = $_POST['dari'];
			$sampai = $_POST['sampai'];
		}
		else
		{
			$dari = date("Y-m-d");
			$sampai = date("Y-m-d");
		}
		
		
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = 
			"
				WHERE A.kode_kantor LIKE '%".$kode_kantor."%' 
				
				AND CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
					A.kode_kantor LIKE '%".$kode_kantor."%'
				ELSE
					A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
				END
							
				AND A.kode_kantor <> 'PST'
				AND A.id_produk = '".$_POST['id_produk']."'
				AND COALESCE(B.sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
				AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
				AND CASE WHEN A.optr_diskon = '%' THEN
					(A.harga - ((A.harga/100) * A.diskon)) > 0
				ELSE
					(A.harga - A.diskon) > 0
				END
			
				AND (
						COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(B.nama_costumer,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(B.no_costmer,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						
						OR COALESCE(D.no_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(D.nama_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						
						OR COALESCE(E.no_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(E.nama_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
					)
			";		
		}
		else
		{
			$cari = 
			"
				WHERE A.kode_kantor LIKE '%".$kode_kantor."%'
				
				AND CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
					A.kode_kantor LIKE '%".$kode_kantor."%'
				ELSE
					A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
				END
				
				AND A.kode_kantor <> 'PST'
				AND A.id_produk = '".$_POST['id_produk']."'
				AND COALESCE(B.sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
				AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
				AND CASE WHEN A.optr_diskon = '%' THEN
					(A.harga - ((A.harga/100) * A.diskon)) > 0
				ELSE
					(A.harga - A.diskon) > 0
				END

				
			";
		}
		$order_by = "ORDER BY COALESCE(B.tgl_ins,'') DESC";
		
		$list_laporan_d_pengeluaran_produk = $this->M_gl_pst_lap_penjualan->list_laporan_produk_jasa_terjual_by_kategori_by_akum_faktur($cari,$_POST['id_produk']);
		
		echo'<div class="box-body table-responsive no-padding">';
			if(!empty($list_laporan_d_pengeluaran_produk))
			{
				echo'<table width="100%" id="example2" class="table table-hover hoverTable">';
					echo '<thead>';	
					
					echo'
							<tr style="background-color:green;">';
								echo '<th style="text-align:center;" width="5%">NO</th>';
								echo '<th style="text-align:center;" width="25%">DATA TRANSAKSI</th>';
								echo '<th style="text-align:center;" width="25%">DOKTER</th>';
								echo '<th style="text-align:center;" width="15%">BANYAK TERJUAL</th>';
								echo '<th style="text-align:center;" width="15%">HARGA</th>';
								echo '<th style="text-align:center;" width="15%">SUBTOTAL</th>';
					echo '</tr>
						</thead>';
					$list_result = $list_laporan_d_pengeluaran_produk->result();
					
					
					$no =1;
					/*
					$isJasa_old = "";
					$isJasa_cur = "";
					$jum_transaksi = 0;
					$cnt_transaksi = 0;
					$subtotal = 0;
					*/
					$subtotal = 0;
					
					echo '<tbody>';
					
					foreach($list_result as $row)
					{
						/*
						//SUBTOTAL
							$isJasa_cur = $row->ISPRODUK;
							
							if(($isJasa_cur != $isJasa_old) && ($isJasa_old != ""))
							{
								echo '<tr style="font-weight:bold; background-color:yellow;"> 
									<td></td> 
									<td style="text-align:right;"> '.$isJasa_old.'</td> 
									<td style="text-align:right;">'.number_format($cnt_transaksi,1,'.',',').'</td> 
									<td style="text-align:right;">'.number_format($jum_transaksi,1,'.',',').'</td>
									<td></td> 
									<td style="text-align:right;">'.number_format($subtotal,1,'.',',').'</td>
								</tr>';

								$isJasa_old = $isJasa_cur;
								
								$jum_transaksi = 0;
								$cnt_transaksi = 0;
								$subtotal = 0;
							}
							
							$jum_transaksi = $jum_transaksi + $row->JUMLAH;
							$cnt_transaksi = $cnt_transaksi + $row->CNT;
							$subtotal = $subtotal + $row->SUBTOTAL;	
						//SUBTOTAL
						*/
						
						
						echo'<tr>';
							echo'<td>'.$no.'</td>';
							
							//<b><font style="color:red;">DATA TRANSKASI </font></b>
							echo '<td>
								
								<b>FAKTUR: </b>'.$row->no_faktur.'
								<br/>'.$row->tgl_h_penjualan.'
								<br/> <b>PASIEN : </b>'.$row->nama_costumer.'
								<br/> <b>JENIS : </b>'.$row->type_h_penjualan.'
							</td>';
							
							echo '<td>
								
								<b>KONSUL: </b>'.$row->NAMA_DOKTER_KONSUL.'
								<br/> <b>TINDAKAN : </b>'.$row->NAMA_DOKTER_TINDAKAN.'
							</td>';
							
							
							echo '<td style="text-align:right;">'.number_format($row->jumlah,1,'.',',').' '.$row->satuan_jual.'</td>';
							echo '<td style="text-align:right;">'.number_format($row->harga_setelah_diskon,1,'.',',').' </td>';
							echo '<td style="text-align:right;">'.number_format($row->harga_setelah_diskon * $row->jumlah,1,'.',',').' </td>';
							
							
						echo'</tr>';
						
						//SUBTOTAL
							//$isJasa_old = $row->ISPRODUK;
						//SUBTOTAL
						
						/*
						$nominal_transaksi = $nominal_transaksi + $row->SUBTOTAL;
						$nominal_pembayaran_cash = $nominal_pembayaran_cash + $row->BAYAR_CASH;
						$nominal_pembayaran_bank = $nominal_pembayaran_bank + $row->BAYAR_BANK;
						*/
						
						$subtotal = $subtotal + ($row->harga_setelah_diskon * $row->jumlah);
						$no++;
					}
					
					
					//SUBTOTAL
					echo '<tr style="font-weight:bold; background-color:yellow;"> 
						<td colspan="5">TOTAL</td> 
						<td style="text-align:right;">'.number_format($subtotal,1,'.',',').'</td>
					</tr>';
					//SUBTOTAL
					
					
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
						
			echo'
		</div><!-- /.box-body -->
		';
		
	}
	
	function laporan_pendapatan()
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
				
				
				$list_laporan_d_penjualan_bank = $this->M_gl_pst_lap_penjualan->list_uang_masuk_perakun_bank($dari,$sampai,$cari,$kode_kantor);
				
				
				$cari_deft = "
					WHERE A.kode_kantor LIKE '%".$kode_kantor."%' 
					AND A.kode_kantor <> 'PST'
					AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
					-- AND COALESCE(C.isProduk,'') IN ('PRODUK','JASA')
					AND A.id_d_penerimaan = ''
					AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."' 
					AND 
					CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
						A.kode_kantor LIKE '%%'
					ELSE
						A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
					END
					
					";
					
				//SUMMARY
					$sum_laporan_d_penjualan = $this->M_gl_lap_penjualan->sum_d_penjualan_pusat_lap_pendapatan($cari_deft,"");
					//$sum_laporan_h_penjualan = $sum_laporan_h_penjualan->row();
				//SUMMARY
				
				//DATA KANTOR
					$list_kantor = $this->M_gl_pengaturan->get_data_kantor(" WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END ");
				//DATA KANTOR
				
				
				$msgbox_title = " Laporan Pendapatan";
				
				$data = array('page_content'=>'gl_pusat_lap_d_pendapatan','msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_bank' => $list_laporan_d_penjualan_bank,'list_kantor' => $list_kantor,'sum_laporan_d_penjualan' => $sum_laporan_d_penjualan);
				$this->load->view('pusat/container',$data);
				
				//$data = array('page_content'=>'gl_pusat_lap_h_penjualan','msgbox_title' => $msgbox_title,'list_laporan_h_penjualan' => $list_laporan_h_penjualan);
				//$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url());
			}
		}
	}
	
	function galeri_foto_pasien()
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
						WHERE A.kode_kantor LIKE '%".$kode_kantor."%' 
						AND A.kode_kantor <> 'PST'
						AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
						AND COALESCE(DATE(A.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."' 
						AND 
						CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
							A.kode_kantor LIKE '%%'
						ELSE
							A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
						END
						
						AND
						(
							COALESCE(B.no_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							OR COALESCE(B.nama_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
						)
					";
							
				}
				else
				{
					$cari = 
					"
						WHERE A.kode_kantor LIKE '%".$kode_kantor."%' 
						AND A.kode_kantor <> 'PST'
						AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
						AND COALESCE(DATE(A.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."' 
						AND 
						CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN
							A.kode_kantor LIKE '%%'
						ELSE
							A.kode_kantor IN (SELECT kode_kantor FROM tb_kantor WHERE isViewClient = 0 GROUP BY kode_kantor)
						END
					";
						
				}
				
				$order_by = "ORDER BY A.tgl_ins DESC;";
				$list_laporan_h_penjualan = $this->M_gl_pst_lap_penjualan->list_h_penjualan_images_saja($cari,$order_by);
				
				
				
				
				//DATA KANTOR
					$list_kantor = $this->M_gl_pengaturan->get_data_kantor(" WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END ");
				//DATA KANTOR
				
				
				$msgbox_title = " Galeri Foto Tindakan Pasien";
				
				$data = array('page_content'=>'gl_pusat_lap_galeri_tindakan','msgbox_title' => $msgbox_title,'list_laporan_h_penjualan' => $list_laporan_h_penjualan,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
				
				//$data = array('page_content'=>'gl_pusat_lap_h_penjualan','msgbox_title' => $msgbox_title,'list_laporan_h_penjualan' => $list_laporan_h_penjualan);
				//$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url());
			}
		}
	}
	
	function aprovalPerubahan()
	{
		$query = "UPDATE tb_h_penjualan SET isApprove = '".$_POST['nilai']."' WHERE kode_kantor = '".$_POST['kode_kantor']."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."';";
		$this->M_gl_pengaturan->exec_query_general($query);
		
		echo "BERHASIl";
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_pst_lap_penjualan.php */