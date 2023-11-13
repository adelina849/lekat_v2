<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_kode_akun extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_kode_akun','M_gl_bank','M_gl_supplier'));
		
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
							AND (
									A.kode_akun LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.nama_kode_akun LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-kode-akuntansi?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-kode-akuntansi/');
				$config['total_rows'] = $this->M_gl_kode_akun->count_kode_akun_limit($cari)->JUMLAH;
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
				
				$list_kode_akun = $this->M_gl_kode_akun->list_kode_akun_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$cari_kode_akun_induk = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
										-- AND A.kode_akun_induk = '' ";
				$list_kode_akun_induk = $this->M_gl_kode_akun->list_kode_akun_limit($cari_kode_akun_induk,1000,0);
				
				$msgbox_title = " Kode Akuntansi";
				
				//DATA BANK
				//$cari_bank = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
				$cari_bank = "
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
								-- AND B.id_bank IS NULL
				";
				$list_bank = $this->M_gl_bank->list_bank_kode_akun($cari_bank);
				//DATA BANK
				
				/*SUPPLIER*/
					//$list_supplier = $this->M_gl_supplier->list_hanya_supplier_kode_akun_limit("WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND COALESCE(B.kode_akun,'') = '' ",500,0);
					$list_supplier = $this->M_gl_supplier->list_hanya_supplier_kode_akun_limit("WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ",500,0);
				/*SUPPLIER*/
				
				$data = array('page_content'=>'gl_admin_kode_akun','halaman'=>$halaman,'list_kode_akun'=>$list_kode_akun,'msgbox_title' => $msgbox_title,'list_kode_akun_induk'=>$list_kode_akun_induk,'list_bank' => $list_bank,'list_supplier' => $list_supplier);
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
				if (!empty($_POST['stat_edit']))
				{	
					$this->M_gl_kode_akun->edit
					(
						$_POST['stat_edit'],
						$_POST['id_bank'],
						$_POST['id_supplier'],
						$_POST['kat_akun_jurnal'],
						$_POST['kode_akun_induk'],
						$_POST['target'],
						$_POST['kode_akun'],
						$_POST['nama_kode_akun'],
						$_POST['ket_kode_akun'],
						$_POST['isLabaRugi'],
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
							'Melakukan perubahan data kode akuntansi '.$_POST['norek'].' | '.$_POST['nama_kode_akun'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					$this->M_gl_kode_akun->simpan
					(
						$_POST['id_bank'],
						$_POST['id_supplier'],
						$_POST['kat_akun_jurnal'],
						$_POST['kode_akun_induk'],
						$_POST['target'],
						$_POST['kode_akun'],
						$_POST['nama_kode_akun'],
						$_POST['ket_kode_akun'],
						$_POST['isLabaRugi'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				header('Location: '.base_url().'gl-kode-akuntansi');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		$id_kode_akun = ($this->uri->segment(2,0));
		$data_kode_akun = $this->M_gl_kode_akun->get_kode_akun('id_kode_akun',$id_kode_akun);
		if(!empty($data_kode_akun))
		{
			$data_kode_akun = $data_kode_akun->row();
			$this->M_gl_kode_akun->hapus('id_kode_akun',$id_kode_akun);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data kode akuntansi '.$data_kode_akun->norek.' | '.$data_kode_akun->nama_kode_akun,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-kode-akuntansi');
	}
	
	function cek_kode_akun()
	{
		//$hasil_cek = $this->M_gl_kode_akun->get_kode_akun('kode_akun',$_POST['kode_akun']);
		
		$hasil_cek = $this->M_gl_kode_akun->get_kode_akun_by_cari("WHERE kode_akun = '".$_POST['kode_akun']."' AND kode_kantor = '".$_POST['kode_kantor']."'");
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->kode_akun;
		}
		else
		{
			return false;
		}
	}
	
	function cek_kode_akun_cari()
	{
		$hasil_cek = $this->M_gl_kode_akun->get_kode_akun_by_cari("WHERE target = '".$_POST['nilai']."' AND kode_kantor = '".$_POST['kode_kantor']."'");
		
		
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->kode_akun;
		}
		else
		{
			return false;
		}
	}
	
	function cek_kode_akun_cari_with_hasil_target()
	{
		if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
		{
			$kode_kantor = $_GET['kode_kantor'];
		}
		else
		{
			$kode_kantor = $this->session->userdata('ses_kode_kantor');
		}
				
		$hasil_cek = $this->M_gl_kode_akun->get_kode_akun_by_cari("WHERE kode_akun = '".$_POST['kode_akun']."' AND kode_kantor = '".$kode_kantor."'");
		
		
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->target;
		}
		else
		{
			return false;
		}
	}
	
	function cek_kode_akun_cari_bank()
	{
		$hasil_cek = $this->M_gl_kode_akun->get_kode_akun_by_cari("WHERE target = '".$_POST['nilai']."' AND kode_kantor = '".$_POST['kode_kantor']."' AND id_bank = '".$_POST['id_bank']."'");
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->kode_akun;
		}
		else
		{
			return false;
		}
	}
	
	function cek_kode_akun_cari_supplier()
	{
		$hasil_cek = $this->M_gl_kode_akun->get_kode_akun_by_cari("WHERE target = '".$_POST['nilai']."' AND kode_kantor = '".$_POST['kode_kantor']."' AND id_supplier = '".$_POST['id_supplier']."'");
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->kode_akun;
		}
		else
		{
			return false;
		}
	}
	
	function cek_kode_akun_cari_induk()
	{
		$hasil_cek = $this->M_gl_kode_akun->get_kode_akun_by_cari("WHERE kode_akun_induk = '' AND kode_kantor = '".$_POST['kode_kantor']."'");
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->kode_akun;
		}
		else
		{
			return false;
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */