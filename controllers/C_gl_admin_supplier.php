<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_supplier extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_supplier','M_gl_kat_supplier','M_gl_produk'));
		
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
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (A.kode_supplier LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_supplier LIKE '%".str_replace("'","",$_GET['cari'])."%')
							";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$jum_row = $this->M_gl_supplier->count_supplier_limit($cari)->JUMLAH;
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-supplier?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-supplier/');
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
				
				$list_kat_supplier = $this->M_gl_kat_supplier->list_kat_supplier_limit(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'",100,0);
				
				$list_supplier = $this->M_gl_supplier->list_supplier_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$no_spl = $this->M_gl_supplier->get_no_supplier();
				$no_spl = $no_spl->NO_SPL;
				$msgbox_title = " Supplier";
				
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
				
				$data = array('page_content'=>'gl_admin_supplier','halaman'=>$halaman,'list_supplier'=>$list_supplier,'msgbox_title' => $msgbox_title,'no_spl' => $no_spl,'list_kat_supplier' => $list_kat_supplier,'sum_pesan' => $sum_pesan);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function index_penerimaan_po()
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
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (A.kode_supplier LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_supplier LIKE '%".str_replace("'","",$_GET['cari'])."%')
							";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$jum_row = $this->M_gl_supplier->count_supplier_limit($cari)->JUMLAH;
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-supplier-penerimaan-po?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-supplier-penerimaan-po/');
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
				
				$list_kat_supplier = $this->M_gl_kat_supplier->list_kat_supplier_limit(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'",100,0);
				
				$list_supplier = $this->M_gl_supplier->list_supplier_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$no_spl = $this->M_gl_supplier->get_no_supplier();
				$no_spl = $no_spl->NO_SPL;
				$msgbox_title = " Penerimaan PO dari Supplier";
				
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
				
				$data = array('page_content'=>'gl_admin_supplier_penerimaan_po','halaman'=>$halaman,'list_supplier'=>$list_supplier,'msgbox_title' => $msgbox_title,'no_spl' => $no_spl,'list_kat_supplier' => $list_kat_supplier,'sum_pesan' => $sum_pesan);
				$this->load->view('admin/container',$data);
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
					
					if (empty($_FILES['foto']['name']))
					{
						$this->M_gl_supplier->edit
						(
							
							$_POST['id_supplier'],
							$_POST['id_kat_supplier'],
							$_POST['kode_supplier'],
							'',
							$_POST['nama_supplier'],
							$_POST['pemilik_supplier'],
							$_POST['situ'],
							$_POST['siup'],
							$_POST['bidang'],
							$_POST['ket_supplier'],
							"",
							"",
							$_POST['email'],
							$_POST['tlp'],
							$_POST['alamat'],
							$_POST['limit_budget'],
							'0',//$_POST['allow_budget'],
							$_POST['bank'],
							$_POST['norek'],
							$_POST['hutang_awal'],
							$_POST['type_supplier'],
							$_POST['tgl_hutang_awal'],
							$_POST['hari_tempo'],
							$_POST['isPajak'],
							$this->session->userdata('ses_id_supplier'),
							$this->session->userdata('ses_kode_kantor')
							
						);
					}
					else
					{	
						/*DAPATKAN DATA supplier*/
							$data_supplier = $this->M_gl_supplier->get_supplier("id_supplier",$_POST['id_supplier']);
							$data_supplier = $data_supplier->row();
						/*DAPATKAN DATA supplier*/
				
						/*AMBIL EXT*/
							$path = $_FILES['foto']['name'];
							$ext = pathinfo($path, PATHINFO_EXTENSION);
						/*AMBIL EXT*/

						/*PROSES UPLOAD*/
							$lokasi_gambar_disimpan = 'assets/global/supplier/';					
							$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,$_POST['id_supplier'].'.'.$ext,$data_supplier->avatar);
						/*PROSES UPLOAD*/
						
						$this->M_gl_supplier->edit
						(
							$_POST['id_supplier'],
							$_POST['id_kat_supplier'],
							$_POST['kode_supplier'],
							'',
							$_POST['nama_supplier'],
							$_POST['pemilik_supplier'],
							$_POST['situ'],
							$_POST['siup'],
							$_POST['bidang'],
							$_POST['ket_supplier'],
							$_POST['id_supplier'].'.'.$ext,
							$lokasi_gambar_disimpan,
							$_POST['email'],
							$_POST['tlp'],
							$_POST['alamat'],
							$_POST['limit_budget'],
							'0',//$_POST['allow_budget'],
							$_POST['bank'],
							$_POST['norek'],
							$_POST['hutang_awal'],
							$_POST['type_supplier'],
							$_POST['tgl_hutang_awal'],
							$_POST['hari_tempo'],
							$_POST['isPajak'],
							$this->session->userdata('ses_id_supplier'),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					
					/* CATAT AKTIFITAS EDIT*/
					if($this->session->userdata('catat_log') == 'Y')
					{
						$this->M_gl_log->simpan_log
						(
							$this->session->userdata('ses_id_supplier'),
							'UPDATE',
							'Melakukan perubahan data supplier '.$_POST['kode_supplier'].' | '.$_POST['nama_supplier'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
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
							$lokasi_gambar_disimpan = 'assets/global/supplier/';
							$avatar = str_replace(" ","",$_FILES['foto']['name']);							
							
							$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,$avatar,"");
						/*PROSES UPLOAD*/
					}
					
					$this->M_gl_supplier->simpan
					(
						$_POST['id_kat_supplier'],
						$_POST['kode_supplier'],
						'',
						$_POST['nama_supplier'],
						$_POST['pemilik_supplier'],
						$_POST['situ'],
						$_POST['siup'],
						$_POST['bidang'],
						$_POST['ket_supplier'],
						$avatar,
						$lokasi_gambar_disimpan,
						$_POST['email'],
						$_POST['tlp'],
						$_POST['alamat'],
						$_POST['limit_budget'],
						'0',//$_POST['allow_budget'],
						$_POST['bank'],
						$_POST['norek'],
						$_POST['hutang_awal'],
						$_POST['type_supplier'],
						$_POST['tgl_hutang_awal'],
						$_POST['hari_tempo'],
						$_POST['isPajak'],
						$this->session->userdata('ses_id_supplier'),
						$this->session->userdata('ses_kode_kantor')
					
					);
					
				}
				
				//echo $_POST['isPajak'];
				header('Location: '.base_url().'gl-admin-supplier');
		
			}
		}			
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$id_supplier = ($this->uri->segment(2,0));
		$data_supplier = $this->M_gl_supplier->get_supplier('id_supplier',$id_supplier);
		if(!empty($data_supplier))
		{
			$data_supplier = $data_supplier->row();
			$this->M_gl_supplier->hapus('id_supplier',$id_supplier);
			
			/*HAPUS GAMBAR DARI SERVER*/
				$lokasi_gambar_disimpan = 'assets/global/supplier/';
				$avatar = $data_supplier->avatar;							
				
				$this->M_gl_pengaturan->do_upload_global($lokasi_gambar_disimpan,"",$avatar);
			/*HAPUS GAMBAR DARI SERVER*/
						
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_supplier'),
					'DELETE',
					'Melakukan penghapusan data supplier '.$data_supplier->kode_supplier.' | '.$data_supplier->nama_supplier,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-admin-supplier');
	}
	
	function cek_supplier()
	{
		$hasil_cek = $this->M_gl_supplier->get_supplier('kode_supplier',$_POST['kode_supplier']);
		echo $hasil_cek;
	}
	
	function view_alias_produk_supplier()
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
				$data_supplier = $this->M_gl_supplier->get_supplier('MD5(id_supplier)',$this->uri->segment(2,0));
				if(!empty($data_supplier))
				{
					$data_supplier = $data_supplier->row();
				
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
								AND A.grup = 'PURCHASE'
								AND MD5(A.id_supplier) = '".$this->uri->segment(2,0)."'
								AND (
									COALESCE(B.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(B.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									)";
					}
					else
					{
						$cari = "
									WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
									AND A.grup = 'PURCHASE'
									AND MD5(A.id_supplier) = '".$this->uri->segment(2,0)."'
								";
					}
					
					/*
					$this->load->library('pagination');
					
					$config['first_url'] = site_url('gl-admin-supplier-alias-produk/'.md5($data_supplier->id_supplier).'?'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-admin-supplier-alias-produk/'.md5($data_supplier->id_supplier));
					
					$config['total_rows'] = $this->M_gl_hak_akses->count_hak_akses_limit($cari)->JUMLAH;
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
					*/
					
					
					$list_produk_alias = $this->M_gl_supplier->list_alias_produk($cari,10000,0);
					
					$msgbox_title = " Pengaturan Alias Produk";
					
					$data = array('page_content'=>'gl_admin_supplier_alias_produk','list_produk_alias'=>$list_produk_alias,'msgbox_title' => $msgbox_title,'data_supplier' => $data_supplier);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-supplier');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function alias_produk_supplier_simpan()
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
				$data_supplier = $this->M_gl_supplier->get_supplier('MD5(id_supplier)',$this->uri->segment(2,0));
				if(!empty($data_supplier))
				{
					$data_supplier = $data_supplier->row();
					if (!empty($_POST['stat_edit']))
					{	
						
						$this->M_gl_supplier->edit_alias_produk
						(
							$_POST['stat_edit'], //$id_alias,
							$_POST['id_supplier'],
							$_POST['id_produk'],
							$_POST['kode_produk_alias'],
							$_POST['nama_produk_alias'],
							'PURCHASE',
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
								'Melakukan perubahan data alias produk '.$_POST['kode_produk_alias'].' | '.$_POST['nama_produk_alias'],
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS EDIT*/
					}
					else
					{
						$this->M_gl_supplier->simpan_alias_produk
						(
							$_POST['id_supplier'],
							$_POST['id_produk'],
							$_POST['kode_produk_alias'],
							$_POST['nama_produk_alias'],
							'PURCHASE',
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						
						);
					}
					header('Location: '.base_url().'gl-admin-supplier-alias-produk/'.md5($data_supplier->id_supplier));
				}
				else
				{
					header('Location: '.base_url().'gl-admin-supplier');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function alias_produk_supplier_hapus()
	{
		
		$id_supplier = ($this->uri->segment(2,0));
		$id_alias = ($this->uri->segment(3,0));
		$data_alias = $this->M_gl_supplier->get_alias('MD5(id_alias)',$id_alias);
		if(!empty($data_alias))
		{
			echo'ada';
			$data_alias = $data_alias->row();
			$this->M_gl_supplier->hapus_alias_produk('MD5(id_alias)',$id_alias);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data alias produk '.$data_alias->kode_produk.' | '.$data_alias->nama_produk,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		else
		{
			echo'tidak';
		}
		
		
		header('Location: '.base_url().'gl-admin-supplier-alias-produk/'.$id_supplier);
	}
	
	function list_produk()
	{	
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "
						WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
						AND A.isNotActive = 0
						AND A.isProduk IN ('PRODUK','CONSUMABLE')
						AND B.id_alias IS NULL
						AND 
						( 
							TRIM(A.kode_produk) LIKE '%".str_replace("'","",$_POST['cari'])."%' 
							OR REPLACE(A.nama_produk,'  ',' ') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						)";
		}
		else
		{
			$cari = "
						WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
						AND A.isNotActive = 0
						AND A.isProduk IN ('PRODUK','CONSUMABLE')
						AND B.id_alias IS NULL
					";
		}
		
		//$order_by = " ORDER BY A.nama_produk ASC ";
		
		//$list_produk = $this->M_gl_produk->list_produk_limit_harga_dasar_untuk_PENJUALAN($_POST['id_h_penjualan'],$_POST['id_kat_costumer'],$_POST['media_transaksi'],$cari,$order_by,$_POST['limit'],$_POST['offset']);
		
		$list_produk = $this->M_gl_supplier->list_produk_for_alias($_POST['id_supplier'],$cari,30,0);
		
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_produk))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">ANO</th>';
							//echo '<th width="5%">PILIH</th>';
							//echo '<th width="10%">FOTO</th>';
							echo '<th width="70%">DATA PRODUK</th>';
							echo '<th width="15%">PILIH</th>';
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
						//echo'<td><input type="checkbox" name="record"></td>';
						
						/*
						if($row->img_file == "")
						{
							$src = base_url().'assets/global/no-image.jpg';
							echo '<td><img id="IMG_'.$no.'"  width="100px" height="100px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" />
							</td>';
						}
						else
						{
							$src = base_url().''.$row->img_url.''.$row->img_file;
							echo '<td><img id="IMG_'.$no.'"  width="100px" height="100px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" />
							</td>';
						}
						*/
						
						
						//<br/> <b>PRODUKSI : </b>'.$row->produksi_oleh.'
						echo'<td>
								<b>KODE : </b>'.$row->kode_produk.'
								<br/> <b>JENIS : </b>'.$row->isProduk.'
								<br/> <b>NAMA : </b><b style="color:red;">'.$row->nama_produk.'</b> 
							</td>';
						echo'<td>
								<!-- <button type="button" onclick="insert_produk('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button> -->
								<button id="btn_pilih_'.$no.'" type="button" onclick="insert_produk('.$no.')" class="btn btn-primary btn-sm" >Pilih</button>
							</td>';
							
						echo'<input type="hidden" id="get_tr_3_'.$no.'" name="get_tr_3_'.$no.'" value="tr_list_produk-'.$no.'" />';
						echo'<input type="hidden" id="id_produk_3_'.$no.'" name="id_produk_3_'.$no.'" value="'.$row->id_produk.'" />';
						echo'<input type="hidden" id="kode_produk_3_'.$no.'" name="kode_produk_3_'.$no.'" value="'.$row->kode_produk.'" />';
						echo'<input type="hidden" id="nama_produk_3_'.$no.'" name="nama_produk_3_'.$no.'" value="'.$row->nama_produk.'" />';
						
						
						echo'<input type="hidden" id="isProduk_3_'.$no.'" name="isProduk_3_'.$no.'" value="'.$row->isProduk.'" />';
						
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