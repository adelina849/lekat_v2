<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_costumer_service extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_costumer','M_gl_kat_costumer'));
		
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
				if((!empty($_GET['isUltah'])) && ($_GET['isUltah']!= "") && ($_GET['isUltah']== "YA")  )
				{
					$isUltah = "
									AND MONTH(DATE(A.tgl_lahir)) = MONTH(DATE(NOW()))
									-- AND DAY(DATE(A.tgl_lahir)) = DAY(DATE(NOW()))
								";
				}
				else
				{
					$isUltah = "";
				}
				
				if((!empty($_GET['cari_jenis_kunjungan'])) && ($_GET['cari_jenis_kunjungan']!= "")  )
				{
					$cari_jenis_kunjungan = $_GET['cari_jenis_kunjungan'];
				}
				else
				{
					$cari_jenis_kunjungan = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND A.jenis_kunjungan LIKE '%".$cari_jenis_kunjungan."%'
							".$isUltah."
							AND (A.no_costumer LIKE '%".str_replace("'","",$_GET['cari'])."%' OR A.nama_lengkap LIKE '%".str_replace("'","",$_GET['cari'])."%' )
							";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
							AND A.jenis_kunjungan LIKE '%".$cari_jenis_kunjungan."%'
							".$isUltah."
							";
				}
				
				$jum_row = $this->M_gl_costumer->count_costumer_biasa($cari)->JUMLAH;
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-pasien-service?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-pasien-service/');
				$config['total_rows'] = $jum_row;
				$config['uri_segment'] = 2;	
				$config['per_page'] = 60;
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
				
				$cari_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
				$list_kat_costumer = $this->M_gl_kat_costumer->list_kat_costumer_limit($cari_kat_costumer,50,0);
				
				$list_costumer = $this->M_gl_costumer->list_costumer_biasa($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$no_cos = $this->M_gl_costumer->get_no_costumer();
				$no_cos = $no_cos->NO_COS;
				$msgbox_title = " Costumer Management Service(CMS) ";
				
				
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
				
				$data = array('page_content'=>'gl_admin_costumer_service','halaman'=>$halaman,'list_costumer'=>$list_costumer,'msgbox_title' => $msgbox_title,'no_cos' => $no_cos, 'list_kat_costumer' => $list_kat_costumer, 'sum_pesan' => $sum_pesan);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function kirim_sms()
	{
		$this->M_gl_costumer->simpan_outbox($_POST['no_hp'],$_POST['isi_sms']);
		echo"TERKIRIM";
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_costumer_service.php */