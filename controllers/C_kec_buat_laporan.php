<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_kec_buat_laporan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_buat_laporan','M_laporan','M_klaporan','M_desa','M_periode'));
		
	}
	
	public function index()
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
							-- A.BLAP_KKANTOR ='".$this->session->userdata('ses_KEC_ID')." AND' 
							A.LAP_ID = '". $LAP_ID ."'
							AND A.KEC_ID = '". $this->session->userdata('ses_KEC_ID') ."'
							AND (A.BLAP_JUDUL LIKE '%".str_replace("'","",$_GET['cari'])."%' OR BLAP_PERIODE LIKE '%".str_replace("'","",$_GET['cari'])."%')
							";
					}
					else
					{
						$cari = "WHERE 
							-- A.BLAP_KKANTOR ='".$this->session->userdata('ses_KEC_ID')." AND' 
							A.LAP_ID = '". $LAP_ID ."'
							AND A.KEC_ID = '". $this->session->userdata('ses_KEC_ID') ."'
							";
					}
					
					$this->load->library('pagination');
					//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
					//$config['base_url'] = base_url().'admin/jabatan/';
					$config['first_url'] = site_url('buat-laporan/'.$LAP_ID.'/?'.http_build_query($_GET));
					$config['base_url'] = site_url('buat-laporan/'.$LAP_ID.'/');
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
					$list_desa = $this->M_desa->get_desa('KEC_ID',$this->session->userdata('ses_KEC_ID'));
					$list_buat_laporan = $this->M_buat_laporan->list_buat_laporan_limit($cari,$config['per_page'],$this->uri->segment(3,0),$this->session->userdata('ses_KEC_ID'));
					$data = array('page_content'=>'ptn_kec_buat_laporan','halaman'=>$halaman,'list_buat_laporan'=>$list_buat_laporan,'data_laporan' => $data_laporan,'list_desa' => $list_desa);
					$this->load->view('kecamatan/container',$data);
				}
				else
				{
					header('Location: '.base_url().'kecamatan-admin-dashboard');
				}
			}
			else
			{
				header('Location: '.base_url().'kecamatan-login');
			}
		}
	}
	
	public function simpan()
	{
		if (!empty($_POST['stat_edit']))
		{
			$this->M_buat_laporan->edit(
				$_POST['stat_edit']
				,$_POST['KEC_ID']
				,$_POST['DES_ID']
				,$_POST['LAP_ID']
				,$_POST['BLAP_JUDUL']
				,$_POST['BLAP_PERIODE']
				,$_POST['BLAP_DTDARI']
				,$_POST['BLAP_DTSAMPAI']
				,$_POST['BLAP_KET']
				,$this->session->userdata('ses_id_karyawan')
				);
			header('Location: '.base_url().'buat-laporan/'.$_POST['LAP_ID']);
		}
		else
		{
			$this->M_buat_laporan->simpan(
				$_POST['KEC_ID']
				,$_POST['DES_ID']
				,$_POST['LAP_ID']
				,$_POST['BLAP_JUDUL']
				,$_POST['BLAP_PERIODE']
				,$_POST['BLAP_DTDARI']
				,$_POST['BLAP_DTSAMPAI']
				,$_POST['BLAP_KET']
				,$this->session->userdata('ses_id_karyawan')
				,$this->session->userdata('ses_KEC_ID')
				,"KEC"
				);
			header('Location: '.base_url().'buat-laporan/'.$_POST['LAP_ID']);
		}
		
		//echo 'ade';
	}
	
	public function hapus()
	{
		$LAP_ID = $this->uri->segment(2,0);
		$BLAP_ID = $this->uri->segment(3,0);
		
		$hasil_cek = $this->M_laporan->get_laporan('LAP_ID',$LAP_ID);
		//$avatar = $hasil_cek->avatar;
		if(!empty($hasil_cek))
		{
			//$this->do_upload('',$avatar);
			//$hasil_cek = $hasil_cek->row();
			$this->M_buat_laporan->hapus($BLAP_ID);
		}
		header('Location: '.base_url().'buat-laporan/'.$LAP_ID);
	}
	
	
	function cek_buat_laporan()
	{
		/*
		echo 'LAP_ID :'.$_POST['LAP_ID'].'<br/>';
		echo 'KEC_ID :'.$_POST['KEC_ID'].'<br/>';
		echo 'PERIODE :'.$_POST['PERIODE'].'<br/>';
		*/
		
		$query = "SELECT * FROM tb_buat_laporan WHERE LAP_ID = '".$_POST['LAP_ID']."' AND KEC_ID = '".$_POST['KEC_ID']."' AND BLAP_PERIODE = '".$_POST['PERIODE']."'";
		
		$cek_buat_laporan = $this->M_laporan->view_query_general($query);
		if(!empty($cek_buat_laporan))
		{
			echo'BERHASIL';
		}
		else
		{
			return false;
		}
		
		//$hasil_cek = $this->M_buat_laporan->get_laporan_yang_sudah_buat_by_periode($_POST['LAP_ID'],$_POST['KEC_ID'],$_POST['PERIODE']);
		//echo $hasil_cek;
	}
	
	function view_tambah_gambar()
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
				$BLAP_ID = $this->uri->segment(3,0);
				$cek_buat_laporan = $this->M_buat_laporan->get_buat_laporan('BLAP_ID',$BLAP_ID);
				if(!empty($cek_buat_laporan))
				{
					$cek_buat_laporan = $cek_buat_laporan->row();
					$data = array('page_content'=>'ptn_kec_buat_laporan_gambar','cek_buat_laporan'=>$cek_buat_laporan);
					$this->load->view('kecamatan/container',$data);
				}
				else
				{
					header('Location: '.base_url().'buat-laporan/'.$LAP_ID);
				}
			}
			else
			{
				header('Location: '.base_url().'kecamatan-login');
			}
		}
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */