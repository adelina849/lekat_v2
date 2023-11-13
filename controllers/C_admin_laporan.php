<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_laporan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_laporan','M_klaporan'));
		
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
				//CEK APAKAH URI SEGEMET BENER ID KATEGORI LAPORANNYA
				$data_klaporan = $this->M_klaporan->get_klaporan('KLAP_ID',$this->uri->segment(2,0));
				if(!empty($data_klaporan))
				{
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE A.KLAP_ID = '".$this->uri->segment(2,0)."' AND (KLAP_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%' OR KLAP_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%' OR LAP_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%' OR LAP_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%')";
					}
					else
					{
						$cari = "WHERE A.KLAP_ID = '".$this->uri->segment(2,0)."'";
					}
					
					$this->load->library('pagination');
					$config['first_url'] = site_url('jenis-laporan?'.http_build_query($_GET));
					$config['base_url'] = site_url('jenis-laporan/');
					$config['total_rows'] = $this->M_laporan->count_laporan_limit($cari)->JUMLAH;
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
					$list_laporan = $this->M_laporan->list_laporan_limit($cari,$config['per_page'],$this->uri->segment(3,0));
					$list_klaporan = $this->M_klaporan->list_klaporan_limit("",100,0);
					
					$query_admin_kabupaten = "
										SELECT A.*,COALESCE(B.nama_jabatan,'') AS nama_jabatan
										FROM tb_karyawan AS A
										LEFT JOIN tb_jabatan AS B ON A.id_jabatan = B.id_jabatan
										WHERE COALESCE(B.nama_jabatan,'') IN ('Admin Aplikasi','Admin Kabupaten');";
					$list_karyawan_admin_kabupaten = $this->M_laporan->view_query_general($query_admin_kabupaten);
					
					$data = array('page_content'=>'ptn_admin_jenis_laporan','halaman'=>$halaman,'list_laporan' => $list_laporan,'list_klaporan'=>$list_klaporan,'data_klaporan' => $data_klaporan->row(),'list_karyawan_admin_kabupaten' => $list_karyawan_admin_kabupaten);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'board-laporan');
				}
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
	
	public function simpan()
	{
		//echo $_POST['LAP_ISPERDESA'];
		if (!empty($_POST['stat_edit']))
		{
			$this->M_laporan->edit(
					$_POST['stat_edit']
					,$_POST['KLAP_ID']
					,$_POST['LAP_KODE']
					,$_POST['LAP_NAMA']
					,$_POST['LAP_PERIODE']
					,$_POST['LAP_DASAR_HUKUM']
					,$_POST['LAP_JUMROW']
					,$_POST['LAP_PJ']
					,$_POST['LAP_KET']
					,$_POST['LAP_ISPERDESA']
					,$this->session->userdata('ses_id_karyawan')
					);
			header('Location: '.base_url().'jenis-laporan/'.$_POST['KLAP_ID']);
		}
		else
		{
			$this->M_laporan->simpan(
					$_POST['KLAP_ID']
					,$_POST['LAP_KODE']
					,$_POST['LAP_NAMA']
					,$_POST['LAP_PERIODE']
					,$_POST['LAP_DASAR_HUKUM']
					,$_POST['LAP_JUMROW']
					,$_POST['LAP_PJ']
					,$_POST['LAP_KET']
					,$_POST['LAP_ISPERDESA']
					,$this->session->userdata('ses_id_karyawan')
					,$this->session->userdata('ses_kode_kantor')
					,"KEC"
					);
			header('Location: '.base_url().'jenis-laporan/'.$_POST['KLAP_ID']);
		}
	}
	
	public function hapus()
	{
		$id = $this->uri->segment(3,0);
		$KLAP_ID = $this->uri->segment(2,0);
		
		//CEK APAKAH URI SEGEMET BENER ID KATEGORI LAPORANNYA
		$data_klaporan = $this->M_klaporan->get_klaporan('KLAP_ID',$this->uri->segment(2,0));
		if(!empty($data_klaporan))
		{
			$this->M_laporan->hapus($id);
			header('Location: '.base_url().'jenis-laporan/'.$KLAP_ID);
		}
		else
		{
			header('Location: '.base_url().'board-laporan');
		}
		
		
	}
	
	function cek_laporan()
	{
		$hasil_cek = $this->M_laporan->get_laporan('A.LAP_KODE',$_POST['LAP_KODE']);
		echo $hasil_cek;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_admin_laporan.php */