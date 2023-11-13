<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_costumer extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_costumer','M_gl_kat_costumer','M_gl_h_penjualan'));
		
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
				
				$jum_row = $this->M_gl_costumer->count_costumer_biasa($cari)->JUMLAH;
				
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
				
				$list_costumer = $this->M_gl_costumer->list_costumer_biasa($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$no_cos = $this->M_gl_costumer->get_no_costumer();
				$no_cos = $no_cos->NO_COS;
				
				
				
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
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
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
				
				//WILAYAH
					//$cari_prov = " AND LEFT(kode,2) = '32'";
					$cari_prov = "";
					$get_prov = $this->M_gl_costumer->get_prov($cari_prov);
					
					//$cari_kabkot = " AND LEFT(kode,2) = '32'";
					//$get_kabkot = $this->M_gl_costumer->get_kabkot($cari_kabkot);
				//WILAYAH
				
				if($this->session->userdata('ses_isModePst') == "YA")
				{
					$msgbox_title = " Data Customer/Cabang";
					
					$data = array('page_content'=>'gl_admin_costumer_pst','halaman'=>$halaman,'list_costumer'=>$list_costumer,'msgbox_title' => $msgbox_title,'no_cos' => $no_cos, 'list_kat_costumer' => $list_kat_costumer, 'sum_pesan' => $sum_pesan,'list_kantor' => $list_kantor,'kode_kantor'=>$kode_kantor,'get_prov' => $get_prov);
					$this->load->view('admin/container',$data);
				}
				else
				{
					
					
					if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
					{
						$msgbox_title = " Data Pelanggan";
					}
					else
					{
						$msgbox_title = " Data Pasien";
					}
					
					
					$data = array('page_content'=>'gl_admin_costumer','halaman'=>$halaman,'list_costumer'=>$list_costumer,'msgbox_title' => $msgbox_title,'no_cos' => $no_cos, 'list_kat_costumer' => $list_kat_costumer, 'sum_pesan' => $sum_pesan,'list_kantor' => $list_kantor,'kode_kantor'=>$kode_kantor,'get_prov' => $get_prov);
					$this->load->view('admin/container',$data);
				}
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	/*
	function simpan()
	{
		echo $_POST['stat_edit'].'<br/>';
		echo $_POST['id_costumer'].'<br/>';
		echo $_POST['no_costumer'].'<br/>';
		echo $_POST['nama_lengkap'].'<br/>';
		
	}
	*/
	
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
				$data_costumer = $this->M_gl_costumer->list_costumer_biasa($cari_costumer,1,0);
				if(!empty($data_costumer))
				{
					$data_costumer = $data_costumer->row();
					
					//GET AWAL DAN AKHIR REKAM MEDIS
						$awal_akhir_rekmed = $this->M_gl_costumer->get_costumer_mulai_akhir_rekam_medik($kode_kantor,$data_costumer->id_costumer);
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
						
						$cari_rekam_medis = " WHERE A.id_costumer = '".$data_costumer->id_costumer."' 
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
						
						$cari_rekam_medis = " WHERE A.id_costumer = '".$data_costumer->id_costumer."'
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
					
					
					if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
					{
						$msgbox_title = " Detail Informasi Pelanggan";
					}
					else
					{
						$msgbox_title = " Detail Informasi Pasien (Rekam medis dan kunjungan)";
					}
							
					
					
					
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
					
					$data = array('page_content'=>'gl_admin_costumer_detail','halaman'=>$halaman,'list_rekam_medis'=>$list_rekam_medis,'msgbox_title' => $msgbox_title,'sum_pesan' => $sum_pesan,'data_costumer' => $data_costumer,'kode_kantor' => $kode_kantor,'awal_akhir_rekmed' => $awal_akhir_rekmed);
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
				$data_costumer = $this->M_gl_costumer->list_costumer_biasa($cari_costumer,1,0);
				if(!empty($data_costumer))
				{
					$data_costumer = $data_costumer->row();
					
					/*DAPATKAN DATA KATEGORI MEMBER*/
						$cari_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_kat_costumer <> '".$data_costumer->id_kat_costumer."'";
						$list_kat_costumer = $this->M_gl_kat_costumer->list_kat_costumer_limit($cari_kat_costumer,50,0);
					/*DAPATKAN DATA KATEGORI MEMBER*/
					
					/*CEK UPGRADE MEMBER DULU*/
						$this->M_gl_costumer->update_member($this->session->userdata('ses_kode_kantor'));
					/*CEK UPGRADE MEMBER DULU*/
					
					/*LIST UPGRADE*/
						$cari_upgrade = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_costumer) = '".$id_costumer."'";
						$list_upgrade = $this->M_gl_costumer->list_costumer_upgrade($cari_upgrade);
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
	
	function hapus_upgrade_member()
	{
		$id_costumer = $this->uri->segment(2,0);
		$id_upgrade = $this->uri->segment(3,0);
		$this->M_gl_costumer->hapus_upgrade_member('id_upgrade',$id_upgrade);
		header('Location: '.base_url().'gl-admin-pasien-upgrade/'.($id_costumer));
	}
	
	public function simpan_upgrade_member()
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
				$id_costumer = $_POST['id_costumer']; //$this->uri->segment(2,0);
				$cari_costumer ="
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND MD5(A.id_costumer) = '".md5($id_costumer)."'
								";
				$data_costumer = $this->M_gl_costumer->list_costumer_biasa($cari_costumer,1,0);
				if(!empty($data_costumer))
				{
					$data_costumer = $data_costumer->row();
					if (!empty($_POST['stat_edit']))
					{	
						$this->M_gl_costumer->edit_upgrade_member
						(
							$_POST['stat_edit'],
							$_POST['id_kat_costumer'],
							$_POST['id_costumer'],
							$_POST['tgl_aktif'],
							$_POST['ket_upgrade'],
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
								'Melakukan perubahan data upgrade member '.$data_costumer->nama_lengkap,
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS EDIT*/
					}
					else
					{
						$this->M_gl_costumer->simpan_upgrade_member
						(
							$_POST['id_kat_costumer'],
							$_POST['id_costumer'],
							$_POST['tgl_aktif'],
							$_POST['ket_upgrade'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					header('Location: '.base_url().'gl-admin-pasien-upgrade/'.md5($id_costumer));
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
				//echo $_POST['lamar_via'];
				if (!empty($_POST['stat_edit']))
				{	
					/*DAPATKAN DATA costumer*/
						$data_costumer = $this->M_gl_costumer->get_costumer('id_costumer',$_POST['stat_edit']);
						$data_costumer = $data_costumer->row();
					/*DAPATKAN DATA costumer*/
					
					if (empty($_FILES['foto']['name']))
					{
						$this->M_gl_costumer->edit
						(
							$_POST['stat_edit'],
							$data_costumer->id_karyawan, //$_POST['id_karyawan'],
							$_POST['id_kat_costumer'],
							$data_costumer->id_kat_costumer2, //$_POST['id_kat_costumer2'],
							$data_costumer->tgl_pengajuan, //$_POST['tgl_pengajuan'],
							$_POST['no_costumer'],
							$_POST['nama_lengkap'],
							$_POST['panggilan'],
							$_POST['nik'],
							$data_costumer->no_ktp, //$_POST['no_ktp'],
							$_POST['tmp_lahir'],
							$_POST['tgl_lahir'],
							$_POST['jenis_kelamin'],
							$_POST['status'],
							$_POST['alamat_rumah_sekarang'],
							$_POST['hp'],
							$data_costumer->status_rumah, //$_POST['status_rumah'],
							$data_costumer->lama_menempati, //$_POST['lama_menempati'],
							$_POST['pendidikan'],
							$data_costumer->ibu_kandung, //$_POST['ibu_kandung'],
							$_POST['nama_perusahaan'],
							$data_costumer->alamat_perusahaan, //$_POST['alamat_perusahaan'],
							$data_costumer->bidang_usaha, //$_POST['bidang_usaha'],
							$data_costumer->jabatan, //$_POST['jabatan'],
							$data_costumer->penghasilan_perbulan, //$_POST['penghasilan_perbulan'],
							$data_costumer->nama_lengkap_pnd, //$_POST['nama_lengkap_pnd'],
							$data_costumer->alamat_rumah_pnd, //$_POST['alamat_rumah_pnd'],
							$_POST['hp_pnd'],
							$_POST['pekerjaan'],
							$data_costumer->hubungan, //$_POST['hubungan'],
							$data_costumer->username, //$_POST['username'],
							$data_costumer->pass, //$_POST['pass'],
							$data_costumer->avatar, //$_POST['avatar'],
							$data_costumer->avatar_url, //$_POST['avatar_url'],
							//$lokasi_gambar_disimpan, //$_POST['avatar_url'],
							$_POST['piutang_awal'],
							$_POST['tgl_piutang_awal'],
							$data_costumer->isDefault, //$_POST['isDefault'],
							$data_costumer->point_awal, //$_POST['point_awal'],
							$_POST['tgl_registrasi'],
							$_POST['jenis_kunjungan'],
							$this->session->userdata('ses_id_costumer'),
							$this->session->userdata('ses_kode_kantor'),
							$_POST['email_costumer'],
							$_POST['ket_costumer'],
							
							$_POST['wil_prov'],
							$_POST['wil_kabkot'],
							$_POST['wil_kec'],
							$_POST['wil_des']
						
						);
						
						//echo'INPUT GK FOTO';
					}
					else
					{	
				
						/*AMBIL EXT*/
							$path = $_FILES['foto']['name'];
							$ext = pathinfo($path, PATHINFO_EXTENSION);
						/*AMBIL EXT*/

						/*PROSES UPLOAD*/
							$lokasi_gambar_disimpan = 'assets/global/costumer/';					
							$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,$_POST['id_costumer'].'.'.$ext,$data_costumer->avatar);
						/*PROSES UPLOAD*/
						
						$this->M_gl_costumer->edit
						(
							
							$_POST['stat_edit'],
							$data_costumer->id_karyawan, //$_POST['id_karyawan'],
							$_POST['id_kat_costumer'],
							$data_costumer->id_kat_costumer2, //$_POST['id_kat_costumer2'],
							$data_costumer->tgl_pengajuan, //$_POST['tgl_pengajuan'],
							$_POST['no_costumer'],
							$_POST['nama_lengkap'],
							$_POST['panggilan'],
							$_POST['nik'],
							$data_costumer->no_ktp, //$_POST['no_ktp'],
							$_POST['tmp_lahir'],
							$_POST['tgl_lahir'],
							$_POST['jenis_kelamin'],
							$_POST['status'],
							$_POST['alamat_rumah_sekarang'],
							$_POST['hp'],
							$data_costumer->status_rumah, //$_POST['status_rumah'],
							$data_costumer->lama_menempati, //$_POST['lama_menempati'],
							$_POST['pendidikan'],
							$data_costumer->ibu_kandung, //$_POST['ibu_kandung'],
							$_POST['nama_perusahaan'],
							$data_costumer->alamat_perusahaan, //$_POST['alamat_perusahaan'],
							$data_costumer->bidang_usaha, //$_POST['bidang_usaha'],
							$data_costumer->jabatan, //$_POST['jabatan'],
							$data_costumer->penghasilan_perbulan, //$_POST['penghasilan_perbulan'],
							$data_costumer->nama_lengkap_pnd, //$_POST['nama_lengkap_pnd'],
							$data_costumer->alamat_rumah_pnd, //$_POST['alamat_rumah_pnd'],
							$_POST['hp_pnd'],
							$_POST['pekerjaan'],
							$data_costumer->hubungan, //$_POST['hubungan'],
							$data_costumer->username, //$_POST['username'],
							$data_costumer->pass, //$_POST['pass'],
							$_POST['id_costumer'].'.'.$ext, //$_POST['avatar'],
							$lokasi_gambar_disimpan, //$_POST['avatar_url'],
							$_POST['piutang_awal'],
							$_POST['tgl_piutang_awal'],
							$data_costumer->isDefault, //$_POST['isDefault'],
							$data_costumer->point_awal, //$_POST['point_awal'],
							$_POST['tgl_registrasi'],
							$_POST['jenis_kunjungan'],
							$this->session->userdata('ses_id_costumer'),
							$this->session->userdata('ses_kode_kantor'),
							$_POST['email_costumer'],
							$_POST['ket_costumer'],
							
							$_POST['wil_prov'],
							$_POST['wil_kabkot'],
							$_POST['wil_kec'],
							$_POST['wil_des']
						);
						
						//echo'INPUT FOTO';
					}
					
					/* CATAT AKTIFITAS EDIT*/
					if($this->session->userdata('catat_log') == 'Y')
					{
						$this->M_gl_log->simpan_log
						(
							$this->session->userdata('ses_id_costumer'),
							'UPDATE',
							'Melakukan perubahan data recruitment costumer '.$_POST['no_costumer'].' | '.$_POST['nama_lengkap'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$id_costumer = $this->M_gl_costumer->get_id_costumer($this->session->userdata('ses_kode_kantor'));
					//$id_costumer = $id_costumer->row();
					$id_costumer = $id_costumer->id_costumer;
					if (empty($_FILES['foto']['name']))
					{
						$avatar = "";
						$lokasi_gambar_disimpan = "";
					}
					else
					{
						/*AMBIL EXT*/
							$path = $_FILES['foto']['name'];
							$ext = pathinfo($path, PATHINFO_EXTENSION);
						/*AMBIL EXT*/

						/*PROSES UPLOAD*/
							$lokasi_gambar_disimpan = 'assets/global/costumer/';
							//$avatar = str_replace(" ","",$_FILES['foto']['name']);	
							$avatar = $id_costumer.'.'.$ext;							
							
							$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,$avatar,"");
						/*PROSES UPLOAD*/
					}
					
					//PASTIKAN NO PASIEN TIDAK ADA YANG SAMA
					//$no_cos = $this->M_gl_costumer->get_no_costumer();
					//$no_cos = $no_cos->NO_COS;
					//PASTIKAN NO PASIEN TIDAK ADA YANG SAMA
					
					if($this->session->userdata('ses_kode_kantor') == "OLN")
					{
						$this->M_gl_costumer->simpan
						(
							$id_costumer,
							'', //$_POST['id_karyawan'],
							$_POST['id_kat_costumer'],
							'', //$_POST['id_kat_costumer2'],
							'', //$_POST['tgl_pengajuan'],
							$_POST['no_costumer'], //$no_cos, //$_POST['no_costumer'],
							$_POST['nama_lengkap'],
							$_POST['panggilan'],
							$_POST['nik'],
							'', //$_POST['no_ktp'],
							$_POST['tmp_lahir'],
							$_POST['tgl_lahir'],
							$_POST['jenis_kelamin'],
							$_POST['status'],
							$_POST['alamat_rumah_sekarang'],
							$_POST['hp'],
							'', //$_POST['status_rumah'],
							'', //$_POST['lama_menempati'],
							$_POST['pendidikan'],
							'', //$_POST['ibu_kandung'],
							$_POST['nama_perusahaan'],
							'', //$_POST['alamat_perusahaan'],
							'', //$_POST['bidang_usaha'],
							'', //$_POST['jabatan'],
							'', //$_POST['penghasilan_perbulan'],
							'', //$_POST['nama_lengkap_pnd'],
							'', //$_POST['alamat_rumah_pnd'],
							'', //$_POST['hp_pnd'],
							$_POST['pekerjaan'],
							'', //$_POST['hubungan'],
							'', //$_POST['username'],
							'', //$_POST['pass'],
							$avatar, //$_POST['avatar'],
							$lokasi_gambar_disimpan, //$_POST['avatar_url'],
							$_POST['piutang_awal'],
							$_POST['tgl_piutang_awal'],
							'0', //$_POST['isDefault'],
							'0', //$_POST['point_awal'],
							$_POST['tgl_registrasi'],
							$_POST['jenis_kunjungan'],
							$this->session->userdata('ses_id_costumer'),
							$this->session->userdata('ses_kode_kantor'),
							$_POST['email_costumer'],
							$_POST['ket_costumer'],
							
							$_POST['wil_prov'],
							$_POST['wil_kabkot'],
							$_POST['wil_kec'],
							$_POST['wil_des']
						
						);
					}
					else
					{
						$this->M_gl_costumer->simpan_with_no_costumer
						(
							$id_costumer,
							'', //$_POST['id_karyawan'],
							$_POST['id_kat_costumer'],
							'', //$_POST['id_kat_costumer2'],
							'', //$_POST['tgl_pengajuan'],
							$_POST['no_costumer'], //$no_cos, //$_POST['no_costumer'],
							$_POST['nama_lengkap'],
							$_POST['panggilan'],
							$_POST['nik'],
							'', //$_POST['no_ktp'],
							$_POST['tmp_lahir'],
							$_POST['tgl_lahir'],
							$_POST['jenis_kelamin'],
							$_POST['status'],
							$_POST['alamat_rumah_sekarang'],
							$_POST['hp'],
							'', //$_POST['status_rumah'],
							'', //$_POST['lama_menempati'],
							$_POST['pendidikan'],
							'', //$_POST['ibu_kandung'],
							$_POST['nama_perusahaan'],
							'', //$_POST['alamat_perusahaan'],
							'', //$_POST['bidang_usaha'],
							'', //$_POST['jabatan'],
							'', //$_POST['penghasilan_perbulan'],
							'', //$_POST['nama_lengkap_pnd'],
							'', //$_POST['alamat_rumah_pnd'],
							'', //$_POST['hp_pnd'],
							$_POST['pekerjaan'],
							'', //$_POST['hubungan'],
							'', //$_POST['username'],
							'', //$_POST['pass'],
							$avatar, //$_POST['avatar'],
							$lokasi_gambar_disimpan, //$_POST['avatar_url'],
							$_POST['piutang_awal'],
							$_POST['tgl_piutang_awal'],
							'0', //$_POST['isDefault'],
							'0', //$_POST['point_awal'],
							$_POST['tgl_registrasi'],
							$_POST['jenis_kunjungan'],
							$this->session->userdata('ses_id_costumer'),
							$this->session->userdata('ses_kode_kantor'),
							$_POST['email_costumer'],
							$_POST['ket_costumer'],
							
							$_POST['wil_prov'],
							$_POST['wil_kabkot'],
							$_POST['wil_kec'],
							$_POST['wil_des']
						
						);
					}
					
				}
				
				header('Location: '.base_url().'gl-admin-pasien');
		
			}
		}			
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$id_costumer = ($this->uri->segment(2,0));
		$data_costumer = $this->M_gl_costumer->get_costumer('id_costumer',$id_costumer);
		if(!empty($data_costumer))
		{
			$data_costumer = $data_costumer->row();
			$this->M_gl_costumer->hapus('id_costumer',$id_costumer);
			
			/*HAPUS GAMBAR DARI SERVER*/
				$lokasi_gambar_disimpan = 'assets/global/costumer/';
				$avatar = $data_costumer->avatar;							
				
				$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,"",$avatar);
			/*HAPUS GAMBAR DARI SERVER*/
						
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_costumer'),
					'DELETE',
					'Melakukan penghapusan data costumer '.$data_costumer->no_costumer.' | '.$data_costumer->nama_costumer,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-pasien');
	}
	
	function cek_costumer()
	{
		$hasil_cek = $this->M_gl_costumer->get_costumer('no_costumer',$_POST['no_costumer']);
		echo $hasil_cek;
	}
	
	function cek_costumer_by_nama()
	{
		$hasil_cek = $this->M_gl_costumer->get_costumer('nama_lengkap',$_POST['nama_lengkap']);
		echo $hasil_cek;
	}

	function cek_isDefault()
	{
		$this->M_gl_costumer->set_default($_POST['id_costumer'],$this->session->userdata('ses_kode_kantor'));
		echo $_POST['id_costumer'];
	}
	
	function simpan_akun_costumer()
	{
		$this->M_gl_costumer->edit_akun
		(
			$_POST['id_costumer'],
			$_POST['username'],
			base64_encode(md5($_POST['pass'])),
			$this->session->userdata('ses_id_costumer'),
			$this->session->userdata('ses_kode_kantor')
		);
		
		echo"BERHASIL";
	}

	function get_list_kabkot()
	{
		$cari_kabkot = " AND LEFT(kode,2) = LEFT('".$_POST['kode_prov']."',2)";
		$get_kabkot = $this->M_gl_costumer->get_kabkot($cari_kabkot);
		if(!empty($get_kabkot))
		{
			$list_result = $get_kabkot->result();
			foreach($list_result as $row)
			{
				echo'<option value="'.$row->kode.'|'.$row->nama.'">'.$row->nama.'</option>';
			}
		}
	}

	function get_list_kec()
	{
		$cari_kec = " AND LEFT(kode,5) = LEFT('".$_POST['kode_kabkot']."',5)";
		$get_kec = $this->M_gl_costumer->get_kec($cari_kec);
		if(!empty($get_kec))
		{
			$list_result = $get_kec->result();
			foreach($list_result as $row)
			{
				echo'<option value="'.$row->kode.'|'.$row->nama.'">'.$row->nama.'</option>';
			}
		}
	}
	
	function get_list_desa()
	{
		$cari_desa = " AND LEFT(kode,8) = LEFT('".$_POST['kode_kec']."',8)";
		$get_desa = $this->M_gl_costumer->get_desa($cari_desa);
		if(!empty($get_desa))
		{
			$list_result = $get_desa->result();
			foreach($list_result as $row)
			{
				echo'<option value="'.$row->kode.'|'.$row->nama.'">'.$row->nama.'</option>';
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */