<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_akuntansi extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_lap_penjualan','M_gl_pst_akuntansi','M_gl_lap_keuangan','M_gl_kode_akun','M_gl_acc_buku_besar','M_gl_lap_neraca'));
		
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
					$cari = "
							WHERE A.kode_kantor LIKE '%".$kode_kantor."%' 
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND COALESCE(C.isProduk,'') IN ('PRODUK','JASA')
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
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND COALESCE(C.isProduk,'') IN ('PRODUK','JASA')
							AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
						
					$cari_h = "
								WHERE A.kode_kantor LIKE '%".$kode_kantor."%' 
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
					$jum_row = $this->M_gl_lap_penjualan->count_laporan_detail($cari)->JUMLAH;
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
				$config['per_page'] = 100000;
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
				
				$list_laporan_d_penjualan_row = $this->M_gl_lap_penjualan->list_laporan_detail($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				
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
				
				$msgbox_title = " Laporan Transaksi Detail ";
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
				
				$data = array('page_content'=>'gl_pusat_akun_transaksi','halaman'=>$halaman, 'sum_pesan' => $sum_pesan,'msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_row' => $list_laporan_d_penjualan_row, 'sum_laporan_h_penjualan' => $sum_laporan_h_penjualan,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
				
				
				//$data = array('page_content'=>'gl_pusat_alur_produk','msgbox_title' => $msgbox_title, 'list_alur_produk' => $list_alur_produk,'list_kantor' => $list_kantor);
				//$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function jurnal()
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
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
				}
				
				
				//GET KODE AKUN KHUSUS
				$get_kode_akun_pendapatan = $this->M_gl_kode_akun->get_kode_akun('target','PENDAPATAN');
				if(!empty($get_kode_akun_pendapatan))
				{
					$get_kode_akun_pendapatan = $get_kode_akun_pendapatan->row();
					$get_kode_akun_pendapatan = $get_kode_akun_pendapatan->kode_akun;
				}
				else
				{
					$get_kode_akun_pendapatan = '';
				}
				
				$get_kode_akun_cashintransit = $this->M_gl_kode_akun->get_kode_akun('target','CASHIN-TRANSIT');
				if(!empty($get_kode_akun_cashintransit))
				{
					$get_kode_akun_cashintransit = $get_kode_akun_cashintransit->row();
					$get_kode_akun_cashintransit = $get_kode_akun_cashintransit->kode_akun;
				}
				else
				{
					$get_kode_akun_cashintransit = '';
				}
				//GET KODE AKUN KHUSUS
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
				$list_lap_keuangan = $this->M_gl_lap_keuangan->list_lap_keuangan
									(
										$kode_kantor
										,'' //$id_bank
										,'' //$ref
										,'' //$cari
										,$dari
										,$sampai
									);
				
				//$list_lap_keuangan = false;
				
				$msgbox_title = " Jurnal Umum ";
				
				
				
				$data = array('page_content'=>'gl_pusat_jurnal','msgbox_title' => $msgbox_title,'list_kantor' => $list_kantor,'get_kode_akun_pendapatan' => $get_kode_akun_pendapatan,'get_kode_akun_cashintransit' => $get_kode_akun_cashintransit,'list_lap_keuangan' => $list_lap_keuangan);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function excel_jurnal()
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
					$kode_kantor = "";
				}
				
				if((!empty($_GET['id_bank'])) && ($_GET['id_bank']!= "")  )
				//if((!empty($_GET['id_bank']))   )
				{
					$id_bank = $_GET['id_bank'];
				}
				else
				{
					$id_bank = "";
				}
				
				if((!empty($_GET['ref'])) && ($_GET['ref']!= "")  )
				{
					$ref = $_GET['ref'];
				}
				else
				{
					$ref = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = $_GET['cari'];
				}
				else
				{
					$cari = "";
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
				
				$list_lap_keuangan = $this->M_gl_lap_keuangan->list_lap_keuangan
									(
										$kode_kantor
										,$id_bank
										,$ref
										,$cari
										,$dari
										,$sampai
									);
									
				$data = array('page_content'=>'gl_pusat_excel_jurnal','list_lap_keuangan' => $list_lap_keuangan);
				$this->load->view('pusat/page/gl_pusat_excel_jurnal.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	

	function buku_besar()
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
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
				}
				
				
				if((!empty($_GET['kode_akun'])) && ($_GET['kode_akun']!= "")  )
				{
					$cari = $_GET['kode_akun'];
				}
				else
				{
					$cari = "";
				}
				
				//$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar($kode_kantor,$dari,$sampai,$cari);
				//$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar($kode_kantor,$dari,$sampai,$cari);
				$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar_kas($kode_kantor,$dari,$sampai,$cari);
				
				
				$msgbox_title = " Laporan Buku Besar ";
				
				$cari_coa = " 
							WHERE kode_kantor = '".$kode_kantor."'
						";
				
				
				$order_by_coa = " ORDER BY nama_kode_akun ASC ";
				$list_kode_akun = $this->M_gl_kode_akun->list_kode_akun_limit_order_by($cari_coa,$order_by_coa,1000,0);
				
				$get_kode_akun = $this->M_gl_kode_akun->get_kode_akun('kode_akun',$cari);
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
				
				$data = array('page_content'=>'gl_pusat_buku_besar','msgbox_title' => $msgbox_title,'list_acc_buku_besar' => $list_acc_buku_besar,'list_kode_akun' => $list_kode_akun,'get_kode_akun' => $get_kode_akun,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function excel_buku_besar()
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
				
				if((!empty($_GET['kode_akun'])) && ($_GET['kode_akun']!= "")  )
				{
					$cari = $_GET['kode_akun'];
				}
				else
				{
					$cari = "";
				}
				
				//$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar($kode_kantor,$dari,$sampai,$cari);
				$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar_kas($kode_kantor,$dari,$sampai,$cari);
									
				$data = array('page_content'=>'gl_pusat_excel_buku_besar','list_acc_buku_besar' => $list_acc_buku_besar);
				$this->load->view('pusat/page/gl_pusat_excel_buku_besar.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function laporan_keuangan()
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
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
				}
				
				
				if((!empty($_GET['kode_akun'])) && ($_GET['kode_akun']!= "")  )
				{
					$cari = $_GET['kode_akun'];
				}
				else
				{
					$cari = "";
				}
				
				$get_id_kode_akun = $this->M_gl_acc_buku_besar->get_id_kode_akun($kode_kantor,$cari);
				if(!empty($get_id_kode_akun))
				{
					$get_id_kode_akun = $get_id_kode_akun->row();
					$id_kode_akun = $get_id_kode_akun->id_kode_akun;
				}
				else
				{
					$id_kode_akun = "";
				}
				
				$saldo_awal = 0;
				$last_saldo_awal_array = $this->M_gl_acc_buku_besar->row_last_saldo_awal_uang_masuk($kode_kantor,$cari,$dari);
				if(!empty($last_saldo_awal_array))
				{
					//$last_saldo_awal = $last_saldo_awal->row();
					$last_saldo_awal = $last_saldo_awal_array->last_saldo_uang_masuk;
					
					$saldo_awal = (int)$last_saldo_awal_array->nominal;
					//$saldo_awal = 500000;
				}
				else
				{
					$last_saldo_awal = '1900-01-01';
					$saldo_awal = 0;
				}
				
				$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar_laporan_keuangan_saldo($kode_kantor,$dari,$sampai,$cari,$last_saldo_awal,$saldo_awal);
				//$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar_laporan_keuangan_saldo($this->session->userdata('ses_kode_kantor'),$dari,$sampai,$cari);
				
				
				$msgbox_title = " Laporan Saldo Keuangan ";
				
				$cari_coa = " 
							WHERE kode_kantor = '".$kode_kantor."' AND target = 'KAS'
						";
				
				
				$order_by_coa = " ORDER BY nama_kode_akun ASC ";
				$list_kode_akun = $this->M_gl_kode_akun->list_kode_akun_limit_order_by($cari_coa,$order_by_coa,1000,0);
				
				$get_kode_akun = $this->M_gl_kode_akun->get_kode_akun('kode_akun',$cari);
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
				
				$data = array('page_content'=>'gl_pusat_laporan_keuangan','msgbox_title' => $msgbox_title,'list_acc_buku_besar' => $list_acc_buku_besar,'list_kode_akun' => $list_kode_akun,'get_kode_akun' => $get_kode_akun,'last_saldo_awal' => $last_saldo_awal,'saldo_awal' => $saldo_awal,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function excel_laporan_keuangan()
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
				
				if((!empty($_GET['kode_akun'])) && ($_GET['kode_akun']!= "")  )
				{
					$cari = $_GET['kode_akun'];
				}
				else
				{
					$cari = "";
				}
				
				$get_id_kode_akun = $this->M_gl_acc_buku_besar->get_id_kode_akun($kode_kantor,$cari);
				if(!empty($get_id_kode_akun))
				{
					$get_id_kode_akun = $get_id_kode_akun->row();
					$id_kode_akun = $get_id_kode_akun->id_kode_akun;
				}
				else
				{
					$id_kode_akun = "";
				}
				
				$saldo_awal = 0;
				$last_saldo_awal_array = $this->M_gl_acc_buku_besar->row_last_saldo_awal_uang_masuk($kode_kantor,$cari,$dari);
				if(!empty($last_saldo_awal_array))
				{
					//$last_saldo_awal = $last_saldo_awal->row();
					$last_saldo_awal = $last_saldo_awal_array->last_saldo_uang_masuk;
					
					$saldo_awal = (int)$last_saldo_awal_array->nominal;
					//$saldo_awal = 500000;
				}
				else
				{
					$last_saldo_awal = '1900-01-01';
					$saldo_awal = 0;
				}
				
				$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar_laporan_keuangan_saldo($kode_kantor,$dari,$sampai,$cari,$last_saldo_awal,$saldo_awal);
				//$list_acc_buku_besar = $this->M_gl_acc_buku_besar->list_acc_buku_besar_laporan_keuangan_saldo($this->session->userdata('ses_kode_kantor'),$dari,$sampai,$cari);
				
				
									
				$data = array('page_content'=>'gl_pusat_excel_lap_acc_keuangan','list_acc_buku_besar' => $list_acc_buku_besar);
				$this->load->view('pusat/page/gl_pusat_excel_lap_acc_keuangan.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function laba_rugi()
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
				
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
				}
				else
				{
					$kode_kantor = "";
				}
				
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
				
				//CEK DULU APAKAH ADA PENJUALAN
					$cek_h_penjualan = $this->M_gl_pst_akuntansi->list_h_penjualan_cari(" WHERE kode_kantor LIKE '%".$kode_kantor."%' AND DATE(tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."'");
					
					If(!empty($cek_h_penjualan))
					{
						$list_laba_rugi = $this->M_gl_pst_akuntansi->list_laba_rugi($kode_kantor,$dari,$sampai);
					}
					else
					{
						$list_laba_rugi = false;
					}
				//CEK DULU APAKAH ADA PENJUALAN
				
				
				
				if(!empty($list_laba_rugi))
				{
					$list_field = $list_laba_rugi->field_data();
				}
				else
				{
					$list_field = false;
				}
				
				$msgbox_title = " Laporan Laba Rugi";
				
				//JURNAL UMUM DIBUAT SATU SAJA DENGAN MENGGUNAKAN CASE IF, JIKA PUSAT AMBIL DARI PUSAT JIKA CABANG AMBIL DAR CABANG
				if( $this->session->userdata('ses_kode_kantor') == "PUSAT" )
				{
					$data = array('page_content'=>'gl_pusat_laporan_laba_rugi','msgbox_title' => $msgbox_title,'list_laba_rugi' => $list_laba_rugi,'list_field' => $list_field,'list_kantor' => $list_kantor);
					$this->load->view('pusat/container',$data);
				}
				else
				{
					$data = array('page_content'=>'gl_pusat_laporan_laba_rugi','msgbox_title' => $msgbox_title,'list_laba_rugi' => $list_laba_rugi,'list_field' => $list_field);
					//$this->load->view('pusat/container',$data);
					$this->load->view('admin/container',$data);
				}
				//JURNAL UMUM DIBUAT SATU SAJA DENGAN MENGGUNAKAN CASE IF, JIKA PUSAT AMBIL DARI PUSAT JIKA CABANG AMBIL DAR CABANG
				
				/*
				$data = array('page_content'=>'gl_pusat_laporan_laba_rugi','msgbox_title' => $msgbox_title,'list_laba_rugi' => $list_laba_rugi,'list_field' => $list_field,'list_kantor' => $list_kantor);
				$this->load->view('pusat/container',$data);
				*/
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function neraca()
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
				//if((!empty($_GET['dari'])) && ($_GET['dari']!= "")  )
					if((!empty($_GET['sampai'])) && ($_GET['sampai']!= "")  )
				{
					//$dari = $_GET['dari'];
					$sampai = $_GET['sampai'];
				}
				else
				{
					//$dari = date("Y-m-d");
					$sampai = date("Y-m-d");
				}
				
				$msgbox_title = " Laporan Neraca Perusahaan";
				
				$data = array('page_content'=>'gl_pusat_neraca','msgbox_title' => $msgbox_title);
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