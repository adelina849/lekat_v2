<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_mutasi_kas extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_mutasi_kas','M_gl_kode_akun'));
		
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
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND DATE(A.tgl_transaksi) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
								(
									A.no_bukti LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.ket_mutasi LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
							";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND DATE(A.tgl_transaksi) BETWEEN '".$dari."' AND '".$sampai."'
							";
				}
				$order_by = " ORDER BY A.tgl_ins DESC";
				
				$this->load->library('pagination');
				
				$config['first_url'] = site_url('gl-admin-mutasi-kas?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-mutasi-kas/');
				$config['total_rows'] = $this->M_gl_mutasi_kas->count_mutasi_kas_limit($cari)->JUMLAH;
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
				
				$list_mutasi_kas = $this->M_gl_mutasi_kas->list_mutasi_kas_limit($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				$cari_coa = " 
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							-- AND target = ''
						";
				$list_kode_akun = $this->M_gl_kode_akun->list_kode_akun_limit($cari_coa,1000,0);
				
				$get_no_bukti = $this->M_gl_mutasi_kas->get_no_bukti($this->session->userdata('ses_kode_kantor'));
				$get_no_bukti = $get_no_bukti->row();
				$get_no_bukti = $get_no_bukti->no_bukti;
				
				
				$msgbox_title = " Mutasi Kas";
				
				$data = array('page_content'=>'gl_admin_mutasi_kas','halaman'=>$halaman,'list_mutasi_kas'=>$list_mutasi_kas,'msgbox_title' => $msgbox_title,'list_kode_akun' => $list_kode_akun,'get_no_bukti' => $get_no_bukti);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function print_bukti_mutasi_kas()
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
				if((!empty($_GET['id'])) && ($_GET['id']!= "")  )
				{
					$id_mutasi = $_GET['id'];
				}
				else
				{
					$id_mutasi = "";
				}
				
				$cari_mutasi = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (A.id_mutasi) = '".$id_mutasi."' ";
				$data_mutasi_kas = $this->M_gl_mutasi_kas->list_mutasi_kas_limit($cari_mutasi,"",1,0);
				if(!empty($data_mutasi_kas))
				{
					$mutasi_kas = $data_mutasi_kas->row();
					$msgbox_title = " Cetak Mutasi Kas";
					
					$data = array('page_content'=>'gl_admin_print_mutasi_kas','mutasi_kas' => $mutasi_kas);
					
					//$this->load->view('admin/container',$data);
					$this->load->view('admin/page/gl_admin_print_mutasi_kas.html',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-admin-mutasi-kas');
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
				if (!empty($_POST['stat_edit']))
				{	
					
					$this->M_gl_mutasi_kas->edit
					(
						$_POST['stat_edit'],
						$_POST['id_kode_in'],
						$_POST['id_kode_out'],
						$_POST['no_bukti'],
						$_POST['tgl_transaksi'],
						str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
						$_POST['ket_mutasi'],
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
							'Melakukan perubahan data mutasi kas '.$_POST['no_bukti'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					
					$this->M_gl_mutasi_kas->simpan
					(
						$_POST['id_kode_in'],
						$_POST['id_kode_out'],
						$_POST['no_bukti'],
						$_POST['tgl_transaksi'],
						str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
						$_POST['ket_mutasi'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					);
					
				}
				header('Location: '.base_url().'gl-admin-mutasi-kas');
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function hapus()
	{
		if((!empty($_GET['id'])) && ($_GET['id']!= "")  )
		{
			$id_mutasi = $_GET['id'];
		}
		else
		{
			$id_mutasi = "";
		}
		
		if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
		{
			$cari = $_GET['cari'];
		}
		else
		{
			$cari = "";
		}
		
		
		$cari_mutasi = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(id_mutasi) = '".$id_mutasi."' ";
		$data_mutasi_kas = $this->M_gl_mutasi_kas->get_mutasi_cari($cari_mutasi);
		if(!empty($data_mutasi_kas))
		{
			$data_mutasi_kas = $data_mutasi_kas->row();
			$this->M_gl_mutasi_kas->hapus('MD5(id_mutasi)',$id_mutasi,$this->session->userdata('ses_kode_kantor'));
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data mutasi '.$data_mutasi_kas->no_bukti,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		
		header('Location: '.base_url().'gl-admin-mutasi-kas?cari='.$cari);
	}
	
	function cek_satuan()
	{
		$hasil_cek = $this->M_gl_satuan->get_satuan('kode_satuan',$_POST['kode_satuan']);
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->kode_satuan;
		}
		else
		{
			return false;
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_mutasi_kas.php */