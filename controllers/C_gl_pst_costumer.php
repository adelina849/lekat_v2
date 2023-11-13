<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_costumer extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_pst_costumer','M_gl_kat_costumer','M_gl_h_penjualan'));
		
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
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
				if((!empty($_GET['cari_jenis_kunjungan'])) && ($_GET['cari_jenis_kunjungan']!= "")  )
				{
					$cari_jenis_kunjungan = $_GET['cari_jenis_kunjungan'];
				}
				else
				{
					$cari_jenis_kunjungan = "";
				}
				
				if((!empty($_GET['nama_kat_costumer'])) && ($_GET['nama_kat_costumer']!= "")  )
				{
					$nama_kat_costumer = $_GET['nama_kat_costumer'];
				}
				else
				{
					$nama_kat_costumer = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND A.jenis_kunjungan LIKE '%".$cari_jenis_kunjungan."%'
							AND COALESCE(B.nama_kat_costumer,'') LIKE '%".$nama_kat_costumer."%'
							AND (A.no_costumer LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_lengkap LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.hp LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.hp_pnd LIKE '%".str_replace("'","",$_GET['cari'])."%' )
							";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND A.jenis_kunjungan LIKE '%".$cari_jenis_kunjungan."%'
							AND COALESCE(B.nama_kat_costumer,'') LIKE '%".$nama_kat_costumer."%'
							";
				}
				
				$jum_row = $this->M_gl_pst_costumer->count_costumer_biasa($cari)->JUMLAH;
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-pasien?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-pasien/');
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
				
				$cari_kat_costumer = " WHERE kode_kantor = '".$kode_kantor."' ";
				$list_kat_costumer = $this->M_gl_kat_costumer->list_kat_costumer_limit($cari_kat_costumer,50,0);
				
				$list_costumer = $this->M_gl_pst_costumer->list_costumer_biasa($cari,$config['per_page'],$this->uri->segment(2,0));
				
				
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
				
				//LIST KANTOR
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor(" WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END ");
				/*
				if(!empty($list_kantor))
				{
					$list_kantor = $list_kantor->row();
					
				}
				else
				{
					$list_kantor = false;
				}
				*/
				//LIST KANTOR
				
				if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
				{
					$msgbox_title = " Data Pelanggan";
				}
				else
				{
					$msgbox_title = " Data Pasien";
				}
				
				
				$data = array('page_content'=>'gl_pusat_costumer','halaman'=>$halaman,'list_costumer'=>$list_costumer,'msgbox_title' => $msgbox_title, 'list_kat_costumer' => $list_kat_costumer, 'sum_pesan' => $sum_pesan,'list_kantor' => $list_kantor,'kode_kantor'=>$kode_kantor);
				$this->load->view('pusat/container',$data);
				
				
			}
			else
			{
				header('Location: '.base_url().'gl-pusat-login-view');
			}
		}
	}
	
	
	function detail()
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
				$id_costumer = $this->uri->segment(2,0);
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
				$cari_costumer ="
								WHERE A.kode_kantor = '".$kode_kantor."' 
								AND MD5(A.id_costumer) = '".$id_costumer."'
								";
				$data_costumer = $this->M_gl_pst_costumer->list_costumer_biasa($cari_costumer,1,0);
				if(!empty($data_costumer))
				{
					$data_costumer = $data_costumer->row();
					
					//GET AWAL DAN AKHIR REKAM MEDIS
						$awal_akhir_rekmed = $this->M_gl_pst_costumer->get_costumer_mulai_akhir_rekam_medik($kode_kantor,$data_costumer->id_costumer);
					//GET AWAL DAN AKHIR REKAM MEDIS
					
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
						$cari_gets = $_GET['cari'];
						
						/*
						$cari_rekam_medis = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_costumer) = '".$id_costumer."' AND A.sts_penjualan = 'SELESAI' 
						AND COALESCE(DATE(A.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."' 
						AND (A.no_faktur = '".str_replace("'","",$_GET['cari'])."')";
						*/
						
						$cari_rekam_medis = " WHERE A.no_costmer = '".$data_costumer->no_costumer."' 
						-- AND A.sts_penjualan = 'SELESAI' 
						AND A.sts_penjualan IN ('SELESAI','PEMBAYARAN')
						AND COALESCE(DATE(A.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."' 
						AND (A.no_faktur = '".str_replace("'","",$_GET['cari'])."')";
					}
					else
					{
						$cari_gets = "";
						
						/*
						$cari_rekam_medis = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_costumer) = '".$id_costumer."' AND A.sts_penjualan = 'SELESAI'
						AND COALESCE(DATE(A.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
						";
						*/
						
						$cari_rekam_medis = " WHERE A.no_costmer = '".$data_costumer->no_costumer."' 
						-- AND A.sts_penjualan = 'SELESAI'
						AND A.sts_penjualan IN ('SELESAI','PEMBAYARAN')
						AND COALESCE(DATE(A.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
						";
					}
					
					
					$order_by = " ORDER BY A.tgl_ins DESC";
					
					
					$jum_row = $this->M_gl_h_penjualan->count_rekam_medis($cari_rekam_medis)->JUMLAH;
					
					$this->load->library('pagination');
					//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
					//$config['base_url'] = base_url().'admin/jabatan/';
					
					$config['first_url'] = site_url('gl-admin-pasien-detail/'.$id_costumer.'?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-pasien-detail/'.$id_costumer);
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
					
					$list_rekam_medis = $this->M_gl_h_penjualan->list_rekam_medis($cari_rekam_medis,$order_by,$config['per_page'],$this->uri->segment(3,0));
					
					$msgbox_title = " Detail Pasien (Rekam medis dan kunjungan)";
					
					
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
					
					$data = array('page_content'=>'gl_pusat_costumer_detail','halaman'=>$halaman,'list_rekam_medis'=>$list_rekam_medis,'msgbox_title' => $msgbox_title,'sum_pesan' => $sum_pesan,'data_costumer' => $data_costumer,'kode_kantor' => $kode_kantor,'awal_akhir_rekmed' => $awal_akhir_rekmed);
					$this->load->view('pusat/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-pusat-login-view');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function view_upgrade()
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
				$id_costumer = $this->uri->segment(2,0);
				$cari_costumer ="
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND MD5(A.id_costumer) = '".$id_costumer."'
								";
				$data_costumer = $this->M_gl_pst_costumer->list_costumer_biasa($cari_costumer,1,0);
				if(!empty($data_costumer))
				{
					$data_costumer = $data_costumer->row();
					
					/*DAPATKAN DATA KATEGORI MEMBER*/
						$cari_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_kat_costumer <> '".$data_costumer->id_kat_costumer."'";
						$list_kat_costumer = $this->M_gl_kat_costumer->list_kat_costumer_limit($cari_kat_costumer,50,0);
					/*DAPATKAN DATA KATEGORI MEMBER*/
					
					/*CEK UPGRADE MEMBER DULU*/
						$this->M_gl_pst_costumer->update_member($this->session->userdata('ses_kode_kantor'));
					/*CEK UPGRADE MEMBER DULU*/
					
					/*LIST UPGRADE*/
						$cari_upgrade = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_costumer) = '".$id_costumer."'";
						$list_upgrade = $this->M_gl_pst_costumer->list_costumer_upgrade($cari_upgrade);
					/*LIST UPGRADE*/
					
					$msgbox_title = " Detail pasien untuk Upgrade Member";
					
					$data = array('page_content'=>'gl_admin_costumer_upgrade','msgbox_title' => $msgbox_title,'data_costumer' => $data_costumer,'list_kat_costumer' => $list_kat_costumer,'list_upgrade' => $list_upgrade);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-pasien');
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
/* Location: ./application/controllers/C_gl_pst_costumer.php */