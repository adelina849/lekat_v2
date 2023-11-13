<?php

	Class C_admin_hasil_laporan Extends CI_Controller
	{
		function __construct()
		{
			parent::__construct();
			$this->load->model(array('M_laporan','M_buat_laporan','M_klaporan','M_kec','M_desa','M_item_laporan','M_d_buat_laporan'));
		}
		
		function index()
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
						$cari = "WHERE B.KLAP_ISAKTIF = 0 AND (KLAP_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%' OR KLAP_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%' OR LAP_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%' OR LAP_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%')";
					}
					else
					{
						$cari = "WHERE B.KLAP_ISAKTIF = 0";
					}
					
					$this->load->library('pagination');
					$config['first_url'] = site_url('laporan-kecamatan?'.http_build_query($_GET));
					$config['base_url'] = site_url('laporan-kecamatan/');
					$config['total_rows'] = $this->M_laporan->count_laporan_limit($cari)->JUMLAH;
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
					
					if(($this->session->userdata('ses_filter_KEC_ID') == null) or ($this->session->userdata('ses_filter_KEC_ID') == null))
					{
						$list_laporan = $this->M_laporan->list_laporan_limit($cari,$config['per_page'],$this->uri->segment(2,0));
					}
					else
					{
						$list_laporan = $this->M_laporan->get_laporan_perkecamatan($this->session->userdata('ses_filter_KEC_ID'),$cari);
					}
					
					//$list_klaporan = $this->M_klaporan->list_klaporan_limit("",100,0);
					$list_kecamatan = $this->M_kec->list_kecamatan_limit('',10000,0);
					$data = array('page_content'=>'ptn_admin_laporan_kecamatan','halaman'=>$halaman,'list_laporan' => $list_laporan,'list_kecamatan' => $list_kecamatan);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'admin-login');
				}
			}
		}
		
		function tanggal_indonesia($tanggal){
			$bulan = array (
			1 =>   'Januari',
			'Februari',
			'Maret',
			'April',
			'Mei',
			'Juni',
			'Juli',
			'Agustus',
			'September',
			'Oktober',
			'November',
			'Desember'
			);
			
			$pecahkan = explode('-', $tanggal);
			
			// variabel pecahkan 0 = tanggal
			// variabel pecahkan 1 = bulan
			// variabel pecahkan 2 = tahun
			 
			return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
		}
	
		function createKecSession()
		{
			$data_kecamatan = $this->M_kec->get_Kecamatan('KEC_ID',$_POST['KEC_ID'])->row();
			$user = array(
				'ses_filter_KEC_ID'  => $data_kecamatan->KEC_ID,
				'ses_filter_KEC_NAMA'  => $data_kecamatan->KEC_NAMA
			);
			$this->session->set_userdata($user);
		}
		
		function listHasilLaporan_perjenis()
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
					$LAP_ID = $this->uri->segment(2,0);
					//echo $LAP_KODE;
					$data_laporan = $this->M_laporan->get_laporan('LAP_ID',$LAP_ID);
					if(!empty($data_laporan))
					{
						$data_laporan = $data_laporan->row();
						
						if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
						{
							$cari = "WHERE 
								A.LAP_ID = '". $LAP_ID ."'
								AND A.KEC_ID = '". $this->session->userdata('ses_filter_KEC_ID') ."'
								AND (A.BLAP_JUDUL LIKE '%".str_replace("'","",$_GET['cari'])."%' OR BLAP_PERIODE LIKE '%".str_replace("'","",$_GET['cari'])."%')
								";
						}
						else
						{
							$cari = "WHERE
								A.LAP_ID = '". $LAP_ID ."'
								AND A.KEC_ID = '". $this->session->userdata('ses_filter_KEC_ID') ."'
								";
						}
						
						$this->load->library('pagination');
						//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
						//$config['base_url'] = base_url().'admin/jabatan/';
						$config['first_url'] = site_url('laporan-kecamatan-perjenis/'.$LAP_ID.'/?'.http_build_query($_GET));
						$config['base_url'] = site_url('laporan-kecamatan-perjenis/'.$LAP_ID.'/');
						$config['total_rows'] = $this->M_buat_laporan->count_buat_laporan_limit($cari)->JUMLAH;
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
						$list_desa = $this->M_desa->get_desa('KEC_ID',$this->session->userdata('ses_filter_KEC_ID'));
						$list_buat_laporan = $this->M_buat_laporan->list_buat_laporan_limit($cari,$config['per_page'],$this->uri->segment(3,0),$this->session->userdata('ses_filter_KEC_ID'));
						$data = array('page_content'=>'ptn_laporan_kecamatan_perjenis','halaman'=>$halaman,'list_buat_laporan'=>$list_buat_laporan,'data_laporan' => $data_laporan,'list_desa' => $list_desa);
						$this->load->view('admin/container',$data);
					}
					else
					{
						header('Location: '.base_url().'admin');
					}
				}
				else
				{
					header('Location: '.base_url().'admin-login');
				}
			}
		}
		
		function listHasilLaporan_perjenis_detail()
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
					$BLAP_ID = $this->uri->segment(2,0);
					//echo $LAP_KODE;
					$data_buat_laporan = $this->M_buat_laporan->get_buat_laporan('BLAP_ID',$BLAP_ID);
					if(!empty($data_buat_laporan))
					{
						$data_buat_laporan = $data_buat_laporan->row();
						$LAP_ID = $data_buat_laporan->LAP_ID;
						$data_laporan = $this->M_laporan->get_laporan('LAP_ID',$LAP_ID);
						if(!empty($data_laporan))
						{
							$data_laporan = $data_laporan->row();
							$data_item_laporan = $this->M_item_laporan->get_item_laporan('LAP_ID',$LAP_ID);
							
							$data_seqn = $this->M_d_buat_laporan->get_Seqn_d_laporan($BLAP_ID);
							
							
							$list_d_buat_laporan = $this->M_d_buat_laporan->list_d_buat_laporan_proc($this->session->userdata('ses_filter_KEC_ID'),$LAP_ID,$BLAP_ID);
							
							$data_laporan_yang_sudah_buat = $this->M_buat_laporan->get_laporan_yang_sudah_buat($LAP_ID,$this->session->userdata('ses_filter_KEC_ID'),$BLAP_ID);
							
							$tgl_hari_ini_indo = $this->tanggal_indonesia(date('Y-m-d'));
							//$data_laporan_yang_sudah_buat = false;
							
							$data = array('page_content'=>'ptn_kec_laporan_detail_proc','data_buat_laporan' => $data_buat_laporan,'data_laporan' => $data_laporan,'data_item_laporan' => $data_item_laporan,'list_d_buat_laporan' => $list_d_buat_laporan,'data_seqn' => $data_seqn,'data_laporan_yang_sudah_buat' => $data_laporan_yang_sudah_buat,'tgl_hari_ini_indo'=>$tgl_hari_ini_indo);
							$this->load->view('admin/container',$data);
						}
						else
						{
							header('Location: '.base_url().'admin');
						}
					}
					else
					{
						header('Location: '.base_url().'admin');
					}
				}
				else
				{
					header('Location: '.base_url().'admin-login');
				}
			}
		}
	}

?>