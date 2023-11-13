<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_data_karyawan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_karyawan','M_gl_keluarga','M_gl_sekolah','M_gl_karyawan_training','M_gl_karyawan_jabatan','M_gl_punish','M_gl_lap_apoteker'));
		
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
				if((!empty($_GET['ultah'])) && ($_GET['ultah']== "YA")  )
				{
					$ultah = " AND MONTH(A.tgl_lahir) = MONTH(NOW())";
				}
				else
				{
					$ultah = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					/*
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (A.no_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nik_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%')
							AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN' OR A.isAktif = 'MUTASI')";
					*/
					
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (A.no_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nik_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%')
							".$ultah."
							AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'MUTASI')";
				}
				else
				{
					//$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN' OR A.isAktif = 'MUTASI')";
					
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
						".$ultah."
						AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'MUTASI')";
				}
				
				$jum_row = $this->M_gl_karyawan->count_karyawan_limit($cari)->JUMLAH;
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-data-karyawan?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-data-karyawan/');
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
				
				$list_karyawan_req = $this->M_gl_karyawan->list_karyawan_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$no_kry = $this->M_gl_karyawan->get_no_karyawan();
				$no_kry = $no_kry->NO_KRY;
				$msgbox_title = " Data Karyawan";
				
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
				
				$data = array('page_content'=>'gl_admin_data_karyawan','halaman'=>$halaman,'list_karyawan_req'=>$list_karyawan_req,'msgbox_title' => $msgbox_title,'no_kry' => $no_kry, 'sum_pesan' => $sum_pesan);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function list_karyawan_resign()
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
					/*
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (A.no_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nik_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%')
							AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN' OR A.isAktif = 'MUTASI')";
					*/
					
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (A.no_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nik_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_karyawan LIKE '%".str_replace("'","",$_GET['cari'])."%')
							AND A.isAktif = 'RESIGN'";
				}
				else
				{
					//$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.isAktif = 'DITERIMA' OR A.isAktif = '' OR A.isAktif = '0'  OR A.isAktif = 'PHK' OR A.isAktif = 'RESIGN' OR A.isAktif = 'MUTASI')";
					
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.isAktif = 'RESIGN'";
				}
				
				$jum_row = $this->M_gl_karyawan->count_karyawan_limit($cari)->JUMLAH;
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-data-karyawan-resign?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-data-karyawan-resign/');
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
				
				$list_karyawan_req = $this->M_gl_karyawan->list_karyawan_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$no_kry = $this->M_gl_karyawan->get_no_karyawan();
				$no_kry = $no_kry->NO_KRY;
				$msgbox_title = " Data Karyawan Resign";
				
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
				
				$data = array('page_content'=>'gl_admin_data_karyawan_resign','halaman'=>$halaman,'list_karyawan_req'=>$list_karyawan_req,'msgbox_title' => $msgbox_title,'no_kry' => $no_kry, 'sum_pesan' => $sum_pesan);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
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
			//$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."';");
			
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."'  AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
			if(!empty($cek_ses_login))
			{
				$id_karyawan = $this->uri->segment(2,0);
				$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_karyawan = '".$id_karyawan."'";
				$data_karyawan = $this->M_gl_karyawan->list_karyawan_limit($cari,1,0);
				if(!empty($data_karyawan))
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
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
								AND COALESCE(C.isProduk,'') IN ('PRODUK','JASA')
								AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) BETWEEN DATE('".$dari."') AND DATE('".$sampai."')
								AND COALESCE(F.NOMINAL,0) > 0
								
								AND (
										COALESCE(B.id_dokter,'') = '".$this->session->userdata('ses_id_karyawan')."'
										OR COALESCE(B.id_dokter2,'') = '".$this->session->userdata('ses_id_karyawan')."'
										OR COALESCE(B.id_ass_dok,'') = '".$this->session->userdata('ses_id_karyawan')."'
										OR COALESCE(B.id_ass_dok2,'') = '".$this->session->userdata('ses_id_karyawan')."'
										OR 'Admin Aplikasi' = '".$this->session->userdata('ses_nama_jabatan')."'
									)
									
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
					}
					else
					{
						$cari = "
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
								AND COALESCE(C.isProduk,'') IN ('PRODUK','JASA')
								AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) BETWEEN DATE('".$dari."') AND DATE('".$sampai."')
								AND COALESCE(F.NOMINAL,0) > 0
								AND (
										COALESCE(B.id_dokter,'') = '".$this->session->userdata('ses_id_karyawan')."'
										OR COALESCE(B.id_dokter2,'') = '".$this->session->userdata('ses_id_karyawan')."'
										OR COALESCE(B.id_ass_dok,'') = '".$this->session->userdata('ses_id_karyawan')."'
										OR COALESCE(B.id_ass_dok2,'') = '".$this->session->userdata('ses_id_karyawan')."'
										OR 'Admin Aplikasi' = '".$this->session->userdata('ses_nama_jabatan')."'
									)
							";
					}
					$order_by = "ORDER BY A.id_h_penjualan DESC, COALESCE(C.isProduk,'') ASC";
					
					
					$list_apoteker = $this->M_gl_lap_apoteker->list_apoteker_today($cari,$order_by);
					
					//ROW DATA KARYAWAN
					$data_karyawan = $data_karyawan->row();
					
					$list_data_kantor = $this->M_gl_karyawan->get_data_kantor(" WHERE kode_kantor <> '".$this->session->userdata('ses_kode_kantor')."'");
					
					$msgbox_title = " Detail Karyawan ".$data_karyawan->nama_karyawan;
					
					
					
					$data = array('page_content'=>'gl_admin_data_karyawan_detail','data_karyawan' => $data_karyawan,'msgbox_title' => $msgbox_title,'list_apoteker' => $list_apoteker,'list_data_kantor' => $list_data_kantor);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-data-karyawan');
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
		echo $_POST['alamat'];
	}
	*/
	
	public function mutasi()
	{
		$id_karyawan = $_POST["id_karyawan"];
		$kode_kantor_sblm = $_POST["kode_kantor_sblm"];
		$kode_kantor_baru = $_POST["kode_kantor_baru"];
		
		$this->M_gl_karyawan->mutasi($id_karyawan,$kode_kantor_sblm,$kode_kantor_baru);
		
		echo "BERHASIL";
	}
	
	/*
	function simpan()
	{
		echo $_POST['isSales'];
	}
	*/
	
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
					//PASTIKAN DATA KARYAWAN ADA
					$get_data_karyawan = $this->M_gl_karyawan->get_karyawan_cari(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_karyawan = '".$_POST['id_karyawan']."'" );
					if(!empty($get_data_karyawan))
					{
						$get_data_karyawan = $get_data_karyawan->row();
						if (empty($_FILES['foto']['name']))
						{
							$this->M_gl_karyawan->edit
							(
								$_POST['id_karyawan'],
								"",//$_POST['id_jabatan'],
								$_POST['no_karyawan'],
								$_POST['nik_karyawan'],
								$_POST['nama_karyawan'],
								$_POST['pnd'],
								$_POST['tlp'],
								$_POST['email'],
								$_POST['tmp_lahir'],
								$_POST['tgl_lahir'],
								$_POST['kelamin'],
								$_POST['sts_nikah'],
								"",
								"",
								$_POST['alamat'],
								$_POST['ket_karyawan'],
								$_POST['tgl_diterima'],
								'DITERIMA',
								$_POST['isDokter'],
								$_POST['lamar_via'],
								
								$_POST['bpjs_kes'],
								$_POST['bpjs_ket'],
								$_POST['isSales'],
								
								$get_data_karyawan->user, //$_POST['user'],
								$get_data_karyawan->pass, //$_POST['pass'],
								$this->session->userdata('ses_id_karyawan'),
								$this->session->userdata('ses_kode_kantor')
							);
							
							//echo $_POST['isSales'].' | 1';
							
						}
						else
						{	
							/*DAPATKAN DATA KARYAWAN*/
								$data_karyawan = $this->M_gl_karyawan->get_karyawan("id_karyawan",$_POST['id_karyawan']);
								$data_karyawan = $data_karyawan->row();
							/*DAPATKAN DATA KARYAWAN*/
					
							/*AMBIL EXT*/
								$path = $_FILES['foto']['name'];
								$ext = pathinfo($path, PATHINFO_EXTENSION);
							/*AMBIL EXT*/

							/*PROSES UPLOAD*/
								$lokasi_gambar_disimpan = 'assets/global/karyawan/';					
								$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,$_POST['id_karyawan'].'.'.$ext,$data_karyawan->avatar);
							/*PROSES UPLOAD*/
							
							
							$this->M_gl_karyawan->edit
							(
								
								$_POST['id_karyawan'],
								"",//$_POST['id_jabatan'],
								$_POST['no_karyawan'],
								$_POST['nik_karyawan'],
								$_POST['nama_karyawan'],
								$_POST['pnd'],
								$_POST['tlp'],
								$_POST['email'],
								$_POST['tmp_lahir'],
								$_POST['tgl_lahir'],
								$_POST['kelamin'],
								$_POST['sts_nikah'],
								$_POST['id_karyawan'].'.'.$ext,
								$lokasi_gambar_disimpan,
								$_POST['alamat'],
								$_POST['ket_karyawan'],
								$_POST['tgl_diterima'],
								'DITERIMA',
								$_POST['isDokter'],
								$_POST['lamar_via'],
								
								$_POST['bpjs_kes'],
								$_POST['bpjs_ket'],
								$_POST['isSales'],
								
								$get_data_karyawan->user, //$_POST['user'],
								$get_data_karyawan->pass, //$_POST['pass'],
								$this->session->userdata('ses_id_karyawan'),
								$this->session->userdata('ses_kode_kantor')
							);
							//echo $_POST['isSales'].' | 2';
						}
						
						/* CATAT AKTIFITAS EDIT*/
						if($this->session->userdata('catat_log') == 'Y')
						{
							$this->M_gl_log->simpan_log
							(
								$this->session->userdata('ses_id_karyawan'),
								'UPDATE',
								'Melakukan perubahan data recruitment karyawan '.$_POST['no_karyawan'].' | '.$_POST['nama_karyawan'],
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS EDIT*/
					}
					//PASTIKAN DATA KARYAWAN ADA
				}
				else
				{
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
							$lokasi_gambar_disimpan = 'assets/global/karyawan/';
							$avatar = str_replace(" ","",$_FILES['foto']['name']);							
							
							$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,$avatar,"");
						/*PROSES UPLOAD*/
					}
					
					$this->M_gl_karyawan->simpan
					(
						"",//$_POST['id_jabatan'],
						$_POST['no_karyawan'],
						$_POST['nik_karyawan'],
						$_POST['nama_karyawan'],
						$_POST['pnd'],
						$_POST['tlp'],
						$_POST['email'],
						$_POST['tmp_lahir'],
						$_POST['tgl_lahir'],
						$_POST['kelamin'],
						$_POST['sts_nikah'],
						$avatar,
						$lokasi_gambar_disimpan,
						$_POST['alamat'],
						$_POST['ket_karyawan'],
						$_POST['tgl_diterima'],
						'DITERIMA',
						$_POST['isDokter'],
						$_POST['lamar_via'],
						
						$_POST['bpjs_kes'],
						$_POST['bpjs_ket'],
						$_POST['isSales'],
						
						"", //$_POST['user'],
						"", //$_POST['pass'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					
					);
					
					//echo $_POST['isSales'].' | 3';
				}
				
				header('Location: '.base_url().'gl-admin-data-karyawan');
		
			}
		}			
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$id = ($this->uri->segment(2,0));
		$data_karyawan = $this->M_gl_karyawan->get_karyawan('id_karyawan',$id);
		if(!empty($data_karyawan))
		{
			$data_karyawan = $data_karyawan->row();
			$this->M_gl_karyawan->hapus($id);
			
			/*HAPUS GAMBAR DARI SERVER*/
				$lokasi_gambar_disimpan = 'assets/global/karyawan/';
				$avatar = $data_karyawan->avatar;							
				
				$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,"",$avatar);
			/*HAPUS GAMBAR DARI SERVER*/
						
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data recruitment karyawan '.$data_karyawan->no_karyawan.' | '.$data_karyawan->nama_karyawan,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-data-karyawan');
	}
	
	function cek_karyawan()
	{
		$hasil_cek = $this->M_gl_karyawan->get_karyawan('no_karyawan',$_POST['no_karyawan']);
		echo $hasil_cek;
	}
	
	function list_keluarga()
	{
		/*
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (COALESCE(A.no_karyawan,'') LIKE '%".$_POST['cari']."%' OR COALESCE(A.nama_karyawan,'') LIKE '%".$_POST['cari']."%')
							";
		}
		else
		{
			$cari = "AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
		}
		*/
		
		$cari = " WHERE id_karyawan = '".$_POST['id_karyawan']."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
		
		
		
		$list_keluarga = $this->M_gl_keluarga->list_keluarga_limit($cari,20,0);
		
		if(!empty($list_keluarga))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="30%">BIODATA</th>';
							echo '<th width="45%">KETERANGAN</th>';
							echo '<th width="20%">AKSI</th>';
				echo '</tr>
</thead>';
				$list_result = $list_keluarga->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						
						echo'<td>
								<b>HUBUNGAN : </b>'.$row->nama_hub .' 
								<br/> <b>NIK : </b>'.$row->nik_kel.' 
								<br/> <b>NAMA : </b>'.$row->nama.'
								<br/> <b>TTL : </b>'.$row->tempat_lahir.','.$row->tgl_lahir.' ('.$row->USIA.')
								<br/> <b>PENDIDIKAN : </b>'.$row->pnd.'
							</td>';
						echo'<td>
								<b>TELPON : </b>'.$row->tlp .' 
								<br/> <b>EMAIL : </b>'.$row->email.'
								<br/> <b>HIRARKI : </b>'.$row->hirarki.'
								<br/> <b>KELAMIN : </b>'.$row->kelamin.'
								<br/> <b>ALAMAT : </b>'.$row->alamat.' 
								<br/> <b>KETERANGAN : </b>'.$row->nama.'
							</td>';
						echo'<td>				
								<a href="javascript:void(0)" class="btn btn-warning btn-sm" onclick="edit_keluarga('.$no.')" title = "Ubah Data '.$row->nama.'" alt = "Ubah Data '.$row->nama.'">Edit</a>
								
								<a class="confirm-btn btn btn-danger btn-sm" href="'.base_url().'gl-admin-data-karyawan-detail-keluarga-hapus/'.($row->id_karyawan).'/'.($row->id_kel).'" title = "Hapus Data '.$row->nama.'" alt = "Hapus Data '.$row->nama.'">Hapus</a>
							</td>';
						// echo'<td>
								// <button type="button" onclick="insert('.$no.')" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal">Pilih</button>
							// </td>';
						/*
						echo'<input type="hidden" id="id_karyawan_3_'.$no.'" name="id_karyawan_3_'.$no.'" value="'.$row->id_karyawan.'" />';
						echo'<input type="hidden" id="no_karyawan_3_'.$no.'" name="no_karyawan_3_'.$no.'" value="'.$row->no_karyawan.'" />';
						echo'<input type="hidden" id="nama_karyawan_3_'.$no.'" name="nama_karyawan_3_'.$no.'" value="'.$row->nama_karyawan.'" />';
						*/
						
						echo'<input type="hidden" id="id_kel_'.$no.'" name="id_kel_'.$no.'" value="'.$row->id_kel.'" />';
						echo'<input type="hidden" id="id_karyawan_'.$no.'" name="id_karyawan_'.$no.'" value="'.$row->id_karyawan.'" />';
						echo'<input type="hidden" id="nama_hub_'.$no.'" name="nama_hub_'.$no.'" value="'.$row->nama_hub.'" />';
						echo'<input type="hidden" id="nik_kel_'.$no.'" name="nik_kel_'.$no.'" value="'.$row->nik_kel.'" />';
						echo'<input type="hidden" id="nama_'.$no.'" name="nama_'.$no.'" value="'.$row->nama.'" />';
						echo'<input type="hidden" id="tempat_lahir_'.$no.'" name="tempat_lahir_'.$no.'" value="'.$row->tempat_lahir.'" />';
						echo'<input type="hidden" id="tgl_lahir_'.$no.'" name="tgl_lahir_'.$no.'" value="'.$row->tgl_lahir.'" />';
						echo'<input type="hidden" id="kelamin_'.$no.'" name="kelamin_'.$no.'" value="'.$row->kelamin.'" />';
						echo'<input type="hidden" id="tlp_'.$no.'" name="tlp_'.$no.'" value="'.$row->tlp.'" />';
						echo'<input type="hidden" id="email_'.$no.'" name="email_'.$no.'" value="'.$row->email.'" />';
						echo'<input type="hidden" id="pnd_'.$no.'" name="pnd_'.$no.'" value="'.$row->pnd.'" />';
						echo'<input type="hidden" id="alamat_'.$no.'" name="alamat_'.$no.'" value="'.$row->alamat.'" />';
						echo'<input type="hidden" id="ket_kel_'.$no.'" name="ket_kel_'.$no.'" value="'.$row->ket_kel.'" />';
						echo'<input type="hidden" id="hirarki_'.$no.'" name="hirarki_'.$no.'" value="'.$row->hirarki.'" />';
						echo'<input type="hidden" id="user_ins_'.$no.'" name="user_ins_'.$no.'" value="'.$row->user_ins.'" />';
						echo'<input type="hidden" id="user_updt_'.$no.'" name="user_updt_'.$no.'" value="'.$row->user_updt.'" />';
						echo'<input type="hidden" id="tgl_ins_'.$no.'" name="tgl_ins_'.$no.'" value="'.$row->tgl_ins.'" />';
						echo'<input type="hidden" id="tgl_updt_'.$no.'" name="tgl_updt_'.$no.'" value="'.$row->tgl_updt.'" />';
						echo'<input type="hidden" id="kode_kantor_'.$no.'" name="kode_kantor_'.$no.'" value="'.$row->kode_kantor.'" />';

						
						
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
	
	public function simpan_keluarga()
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
				if (!empty($_POST['id_kel']))
				{	
					$this->M_gl_keluarga->edit
					(
						
						$_POST['id_kel'],
						$_POST['id_karyawan_kel'],
						$_POST['nama_hub'],
						$_POST['nik_kel'],
						$_POST['nama'],
						$_POST['tempat_lahir'],
						$_POST['tgl_lahir'],
						$_POST['kelamin'],
						$_POST['tlp'],
						$_POST['email'],
						$_POST['pnd'],
						$_POST['alamat'],
						$_POST['ket_kel'],
						$_POST['hirarki'],
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
							'Melakukan perubahan data Keluarga '.$_POST['nik_kel'].' | '.$_POST['nama'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$this->M_gl_keluarga->simpan
					(
						$_POST['id_karyawan_kel'],
						$_POST['nama_hub'],
						$_POST['nik_kel'],
						$_POST['nama'],
						$_POST['tempat_lahir'],
						$_POST['tgl_lahir'],
						$_POST['kelamin'],
						$_POST['tlp'],
						$_POST['email'],
						$_POST['pnd'],
						$_POST['alamat'],
						$_POST['ket_kel'],
						$_POST['hirarki'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					
					);
				}
				header('Location: '.base_url().'gl-admin-data-karyawan-detail/'.$_POST['id_karyawan_kel'].'?tab=keluarga');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function cek_keluarga()
	{
		$hasil_cek = $this->M_gl_keluarga->get_keluarga('nik_kel',$_POST['nik_kel']);
		echo $hasil_cek;
	}
	
	public function hapus_keluarga()
	{
		$id_karyawan = $this->uri->segment(2,0);
		$data_karyawan = $this->M_gl_karyawan->get_karyawan('id_karyawan',$id_karyawan);
		if(!empty($data_karyawan))
		{
			$data_karyawan = $data_karyawan->row();
			
			$id_kel = $this->uri->segment(3,0);
			$data_keluarga = $this->M_gl_keluarga->get_keluarga('id_kel',$id_kel);
			if(!empty($data_keluarga))
			{
			
				$data_keluarga = $data_keluarga->row();
				
				//HAPUS KELUARGA
					$this->M_gl_keluarga->hapus("id_kel",$id_kel);
				//HAPUS KELUARGA
							
				/* CATAT AKTIFITAS HAPUS*/
				if($this->session->userdata('catat_log') == 'Y')
				{
					$this->M_gl_log->simpan_log
					(
						$this->session->userdata('ses_id_karyawan'),
						'DELETE',
						'Melakukan penghapusan data keluarga karyawan '.$data_karyawan->nama_karyawan.' | '.$data_karyawan->no_karyawan.' Dengan nama '.$data_keluarga->nama.' Sebagai '.$data_keluarga->nama_hub,
						$this->M_gl_pengaturan->getUserIpAddr(),
						gethostname(),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				/* CATAT AKTIFITAS HAPUS*/
			}
		}
		
		header('Location: '.base_url().'gl-admin-data-karyawan-detail/'.$id_karyawan.'?tab=keluarga');
	}
	
	function list_sekolah()
	{
		/*
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (COALESCE(A.no_karyawan,'') LIKE '%".$_POST['cari']."%' OR COALESCE(A.nama_karyawan,'') LIKE '%".$_POST['cari']."%')
							";
		}
		else
		{
			$cari = "AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
		}
		*/
		
		$cari = " WHERE id_karyawan = '".$_POST['id_karyawan']."' AND kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
		
		
		
		$list_sekolah = $this->M_gl_sekolah->list_sekolah_limit($cari,20,0);
		
		if(!empty($list_sekolah))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="35%">BIODATA</th>';
							echo '<th width="40%">KETERANGAN</th>';
							echo '<th width="20%">AKSI</th>';
				echo '</tr>
</thead>';
				$list_result = $list_sekolah->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					if($row->tingkat != 'NON FORMAL')
					{
						echo'<tr id="tr_'.$no.'" style="background-color:#90EE90;">';
					}
					else
					{
						echo'<tr id="tr_'.$no.'">';
					}
					
					
						echo'<td>'.$no.'</td>';
						
						echo'<td>
								<b>TINGKAT : </b>'.$row->tingkat .' 
								<br/> <b>NAMA : </b>'.$row->nama_sekolah.'
								<br/> <b>NILAI : </b>'.$row->nilai.'
								<br/> <b>TAHUN LULUS : </b>'.$row->tahun_lulus.'
							</td>';
						echo'<td>
								<b>HIRARKI : </b>'.$row->hirarki .'
								<br/> <b>ALAMAT : </b>'.$row->alamat .'
								<br/> <b>KETERANGAN : </b>'.$row->ket_sekolah .'
							</td>';
						echo'<td>				
								<a href="javascript:void(0)" class="btn btn-warning btn-sm" onclick="edit_sekolah('.$no.')" title = "Ubah Data '.$row->nama_sekolah.'" alt = "Ubah Data '.$row->nama_sekolah.'">Edit</a>
								
								<a class="confirm-btn btn btn-danger btn-sm" href="'.base_url().'gl-admin-data-karyawan-detail-sekolah-hapus/'.($row->id_karyawan).'/'.($row->id_sekolah).'" title = "Hapus Data '.$row->nama_sekolah.'" alt = "Hapus Data '.$row->nama_sekolah.'">Hapus</a>
							</td>';
						// echo'<td>
								// <button type="button" onclick="insert('.$no.')" class="btn btn-primary btn-sm" data-toggle="modal" data-target="#myModal">Pilih</button>
							// </td>';
						/*
						echo'<input type="hidden" id="id_karyawan_3_'.$no.'" name="id_karyawan_3_'.$no.'" value="'.$row->id_karyawan.'" />';
						echo'<input type="hidden" id="no_karyawan_3_'.$no.'" name="no_karyawan_3_'.$no.'" value="'.$row->no_karyawan.'" />';
						echo'<input type="hidden" id="nama_karyawan_3_'.$no.'" name="nama_karyawan_3_'.$no.'" value="'.$row->nama_karyawan.'" />';
						*/
						
						
						echo'<input type="hidden" id="id_sekolah_2_'.$no.'" name="id_sekolah_2_'.$no.'" value="'.$row->id_sekolah.'" />';
						echo'<input type="hidden" id="id_karyawan_2_'.$no.'" name="id_karyawan_2_'.$no.'" value="'.$row->id_karyawan.'" />';
						echo'<input type="hidden" id="tingkat_2_'.$no.'" name="tingkat_2_'.$no.'" value="'.$row->tingkat.'" />';
						echo'<input type="hidden" id="nama_sekolah_2_'.$no.'" name="nama_sekolah_2_'.$no.'" value="'.$row->nama_sekolah.'" />';
						echo'<input type="hidden" id="nilai_2_'.$no.'" name="nilai_2_'.$no.'" value="'.$row->nilai.'" />';
						echo'<input type="hidden" id="tahun_lulus_2_'.$no.'" name="tahun_lulus_2_'.$no.'" value="'.$row->tahun_lulus.'" />';
						echo'<input type="hidden" id="alamat_2_'.$no.'" name="alamat_2_'.$no.'" value="'.$row->alamat.'" />';
						echo'<input type="hidden" id="ket_sekolah_2_'.$no.'" name="ket_sekolah_2_'.$no.'" value="'.$row->ket_sekolah.'" />';
						echo'<input type="hidden" id="hirarki_2_'.$no.'" name="hirarki_2_'.$no.'" value="'.$row->hirarki.'" />';
						echo'<input type="hidden" id="user_ins_2_'.$no.'" name="user_ins_2_'.$no.'" value="'.$row->user_ins.'" />';
						echo'<input type="hidden" id="user_updt_2_'.$no.'" name="user_updt_2_'.$no.'" value="'.$row->user_updt.'" />';
						echo'<input type="hidden" id="tgl_ins_2_'.$no.'" name="tgl_ins_2_'.$no.'" value="'.$row->tgl_ins.'" />';
						echo'<input type="hidden" id="tgl_updt_2_'.$no.'" name="tgl_updt_2_'.$no.'" value="'.$row->tgl_updt.'" />';
						echo'<input type="hidden" id="kode_kantor_2_'.$no.'" name="kode_kantor_2_'.$no.'" value="'.$row->kode_kantor.'" />';
						
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
	
	public function simpan_sekolah()
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
				if (!empty($_POST['id_sekolah']))
				{	
					$this->M_gl_sekolah->edit
					(
						$_POST['id_sekolah'],
						$_POST['id_karyawan_sekolah'],
						$_POST['tingkat'],
						$_POST['nama_sekolah'],
						$_POST['nilai'],
						$_POST['tahun_lulus'],
						$_POST['alamat_sekolah'],
						$_POST['ket_sekolah'],
						$_POST['hirarki_sekolah'],
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
							'Melakukan perubahan data Sekolah '.$_POST['tingkat'].' | '.$_POST['nama_sekolah'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$this->M_gl_sekolah->simpan
					(
					
						$_POST['id_karyawan_sekolah'],
						$_POST['tingkat'],
						$_POST['nama_sekolah'],
						$_POST['nilai'],
						$_POST['tahun_lulus'],
						$_POST['alamat_sekolah'],
						$_POST['ket_sekolah'],
						$_POST['hirarki_sekolah'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					
					);
				}
				header('Location: '.base_url().'gl-admin-data-karyawan-detail/'.$_POST['id_karyawan_sekolah'].'?tab=pendidikan');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus_sekolah()
	{
		$id_karyawan = $this->uri->segment(2,0);
		$data_karyawan = $this->M_gl_karyawan->get_karyawan('id_karyawan',$id_karyawan);
		if(!empty($data_karyawan))
		{
			$data_karyawan = $data_karyawan->row();
			
			$id_sekolah = $this->uri->segment(3,0);
			$data_sekolah = $this->M_gl_sekolah->get_sekolah('id_sekolah',$id_sekolah);
			if(!empty($data_sekolah))
			{
			
				$data_sekolah = $data_sekolah->row();
				
				//HAPUS KELUARGA
					$this->M_gl_sekolah->hapus("id_sekolah",$id_sekolah);
				//HAPUS KELUARGA
							
				/* CATAT AKTIFITAS HAPUS*/
				if($this->session->userdata('catat_log') == 'Y')
				{
					$this->M_gl_log->simpan_log
					(
						$this->session->userdata('ses_id_karyawan'),
						'DELETE',
						'Melakukan penghapusan data pendidikan karyawan '.$data_karyawan->nama_karyawan.' | '.$data_karyawan->no_karyawan.' Dengan nama '.$data_sekolah->tingkat.' Sebagai '.$data_sekolah->nama_sekolah,
						$this->M_gl_pengaturan->getUserIpAddr(),
						gethostname(),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				/* CATAT AKTIFITAS HAPUS*/
			}
		}
		
		header('Location: '.base_url().'gl-admin-data-karyawan-detail/'.$id_karyawan.'?tab=pendidikan');
	}
	
	function list_training()
	{
		/*
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (COALESCE(A.no_karyawan,'') LIKE '%".$_POST['cari']."%' OR COALESCE(A.nama_karyawan,'') LIKE '%".$_POST['cari']."%')
							";
		}
		else
		{
			$cari = "AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
		}
		*/
		
		$cari = " WHERE A.id_karyawan = '".$_POST['id_karyawan']."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
		
		
		
		$list_training = $this->M_gl_karyawan_training->get_list_karyawan_training($cari,30,0);
		//($cari,20,0);
		
		if(!empty($list_training))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="55%">EVENT</th>';
							echo '<th width="40%">HASIL</th>';
				echo '</tr>
</thead>';
				$list_result = $list_training->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
						echo'<td>'.$no.'</td>';
						
						echo'<td>
								<b>NAMA : </b>'.$row->nama_event .' 
								<br/> <b>PANITIA : </b>'.$row->panitia.'
								<br/> <b>TAHUN : </b>'.$row->tahun.'
								<br/> <b>ALAMAT : </b>'.$row->alamat.'
							</td>';
						echo'<td>
								<b>NILAI : </b>'.$row->nilai .'
								<br/> <b>KETERANGAN : </b>'.$row->ket_karyawan_training .'
							</td>';
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
	
	function phk()
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
				$id_karyawan = $_POST['id_karyawan_phk'];
				$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_karyawan = '".$id_karyawan."'";
				$data_karyawan = $this->M_gl_karyawan->list_karyawan_limit($cari,1,0);
				if(!empty($data_karyawan))
				{
					$data_karyawan = $data_karyawan->row();
					$this->M_gl_karyawan->edit_phk($this->session->userdata('ses_kode_kantor'),$id_karyawan,$_POST['isAktif'],$_POST['tgl_phk'],$_POST['alasan_phk']);
					
					/* CATAT AKTIFITAS EDIT*/
					if($this->session->userdata('catat_log') == 'Y')
					{
						$this->M_gl_log->simpan_log
						(
							$this->session->userdata('ses_id_karyawan'),
							'UPDATE',
							'Melakukan PHK/RESIGN karyawan '.$data_karyawan->no_karyawan.' | '.$data_karyawan->nama_karyawan,
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
					
					header('Location: '.base_url().'gl-admin-data-karyawan-detail/'.$id_karyawan);
					
				}
				else
				{
					header('Location: '.base_url().'gl-admin-data-karyawan');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_riwayat_jabatan()
	{
		$list_riwayat_jabatan = $this->M_gl_karyawan_jabatan->list_riwayat_jabatan_karyawan($this->session->userdata('ses_kode_kantor'),$_POST['id_karyawan']);
		//($cari,20,0);
		
		if(!empty($list_riwayat_jabatan))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="27%">JABATAN</th>';
							echo '<th width="43%">KETERANGAN</th>';
							echo '<th width="25%">HASIL</th>';
				echo '</tr>
</thead>';
				$list_result = $list_riwayat_jabatan->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
						echo'<td>'.$no.'</td>';
						
						echo'<td>
								<b>TYPE : </b>'.$row->tipe.'
								<br/> <b>DEPT : </b>'.$row->nama_dept .' ('.$row->nama_dept.') 
								<br/> <b>JABATAN : </b>'.$row->nama_jabatan.' ('.$row->kode_jabatan.')
							</td>';
						echo'<td>
								<b>KODE : </b>'.$row->kode_promosi .'
								<br/> <b>SURAT SK : </b>'.$row->no_surat .'
								<br/> <b>PERIODE : </b>'.$row->rekomendasi .'
								<br/> <b>MASA PERCOBAAN : </b>'.$row->tgl_dari .' - '.$row->Akhir_percobaan.'
								<br/> <b>KETERANGAN : </b>'.$row->keterangan .'
							</td>';
						echo'<td>
								<b>STATUS : </b>'.$row->status .'
								<br/> <b>NILAI : </b>'.$row->nilai .'
								<br/> <b>CATATAN : </b>'.$row->ket_nilai .'
							</td>';
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
	
	function list_riwayat_punishment()
	{
		$cari = " WHERE A.id_karyawan = '".$_POST['id_karyawan']."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
		$list_riwayat_punishment = $this->M_gl_punish->list_punish_limit($cari,30,0);
		//($cari,20,0);
		
		if(!empty($list_riwayat_punishment))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							echo '<th width="40%">PERATURRAN</th>';
							echo '<th width="55%">PUNISHMENT</th>';
				echo '</tr>
</thead>';
				$list_result = $list_riwayat_punishment->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					echo'<tr id="tr_'.$no.'">';
						echo'<td>'.$no.'</td>';
						
						echo'<td>
								<b>KODE : </b>'.$row->kode_per.'
								<br/> <b>NAMA : </b>'.$row->nama_per .' 
								<br/> <b>KETERANGAN : </b>'.$row->ket_per.'
							</td>';
						echo'<td>
								<b>NO : </b>'.$row->no_pelanggaran .'
								<br/> <b>JUDUL : </b>'.$row->nama_pelanggaran .'
								<br/> <b>HUKUMAN : </b>'.$row->hukuman .'
								<br/> <b>MASA PERCOBAAN : </b>'.$row->tgl_mulai .' - '.$row->tgl_selesai.'
								<br/> <b>KRONOLOGI : </b>'.$row->kronologi .'
							</td>';
						
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
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */