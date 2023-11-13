<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_kec_data_desa extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_desa','M_kec'));
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
				$KEC_ID = $this->uri->segment(2,0);
				$data_kecamatan = $this->M_kec->get_Kecamatan('KEC_ID',$KEC_ID);
				if(!empty($data_kecamatan))
				{
					$data_kecamatan = $data_kecamatan->row();
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE A.KEC_ID = '".$KEC_ID."' AND A.DES_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
					}
					else
					{
						$cari = "WHERE A.KEC_ID = '".$KEC_ID."' ";
					}
					
					$this->load->library('pagination');
					$config['first_url'] = site_url('kecamatan-desa?'.http_build_query($_GET));
					$config['base_url'] = site_url('kecamatan-desa/');
					$config['total_rows'] = $this->M_desa->count_desa_limit($cari)->JUMLAH;
					$config['uri_segment'] = 3;	
					$config['per_page'] = 30;
					$config['num_links'] = 2;
					$config['suffix'] = '?' . http_build_query($_GET, '', "&");
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
					$list_desa = $this->M_desa->list_desa_limit($cari,$config['per_page'],$this->uri->segment(3,0));
					$data = array('page_content'=>'ptn_kec_data_desa','halaman'=>$halaman,'data_kecamatan' => $data_kecamatan,'list_desa'=>$list_desa);
					$this->load->view('kecamatan/container',$data);
				}
				else
				{
					header('Location: '.base_url().'data-kecamatan-desa');
				}
			}
			else
			{
				header('Location: '.base_url().'kecamatan-login');
			}
		}
	}
	
	public function index_list_desa()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'kecamatan-login');
		}
		else
		{
			$cek_ses_login = $this->M_akun->get_cek_login($this->session->userdata('ses_user_admin'),md5(base64_decode($this->session->userdata('ses_pass_admin'))));
			
			if(!empty($cek_ses_login))
			{
				
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE (A.DES_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.KEC_NAMA,'') LIKE '%".str_replace("'","",$_GET['cari'])."%') ";
					}
					else
					{
						$cari = "";
					}
					
					$this->load->library('pagination');
					$config['first_url'] = site_url('kecamatan-list-desa?'.http_build_query($_GET));
					$config['base_url'] = site_url('kecamatan-list-desa/');
					$config['total_rows'] = $this->M_desa->count_desa_limit($cari)->JUMLAH;
					$config['uri_segment'] = 2;	
					$config['per_page'] = 30;
					$config['num_links'] = 2;
					$config['suffix'] = '?' . http_build_query($_GET, '', "&");
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
					$list_desa = $this->M_desa->list_desa_limit($cari,$config['per_page'],$this->uri->segment(2,0));
					$data = array('page_content'=>'ptn_admin_list_desa','halaman'=>$halaman,'list_desa'=>$list_desa);
					$this->load->view('admin/container',$data);
				
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
			$this->M_desa->edit
			(
				$_POST['stat_edit']
				,$_POST['DES_NAMA']
				,$_POST['DES_KADES']
				,$_POST['DES_TLP']
				,$_POST['DES_KET']
				,$_POST['DES_ALAMAT']
				,$_POST['DES_LONGI']
				,$_POST['DES_LATI']
				,$this->session->userdata('ses_id_karyawan')
			);
			header('Location: '.base_url().'data-kecamatan-desa/'.$_POST['KEC_ID']);
		}
		else
		{
			
			$this->M_desa->simpan
			(
				$_POST['KEC_ID']
				,$_POST['DES_NAMA']
				,$_POST['DES_KADES']
				,$_POST['DES_TLP']
				,$_POST['DES_KET']
				,$_POST['DES_ALAMAT']
				,$_POST['DES_LONGI']
				,$_POST['DES_LATI']
				,$this->session->userdata('ses_id_karyawan')
				,$this->session->userdata('ses_kode_kantor')
				,"KEC"
			);
			header('Location: '.base_url().'data-kecamatan-desa/'.$_POST['KEC_ID']);
		}
		
	}
	
	function do_upload_img($id,$cek_bfr)
	{
		$this->load->library('upload');

		if($cek_bfr != '')
		{
			@unlink('./assets/global/images/'.$cek_bfr);
		}
		
		if (!empty($_FILES['KEC_IMG']['name']))
		{
			$config['upload_path'] = 'assets/global/images/';
			$config['allowed_types'] = 'gif|jpg|png';
			$config['max_size']	= '2024';
			//$config['max_widtd']  = '300';
			//$config['max_height']  = '300';
			$config['file_name']	= $id;
			$config['overwrite']	= true;
			

			$this->upload->initialize($config);

			//Upload file 1
			if ($this->upload->do_upload('KEC_IMG'))
			{
				$hasil = $this->upload->data();
			}
		}
	}
	
	
	function do_upload_camimg($id,$cek_bfr)
	{
		$this->load->library('upload');

		if($cek_bfr != '')
		{
			@unlink('./assets/global/images/'.$cek_bfr);
		}
		
		if (!empty($_FILES['KEC_CAMIMG']['name']))
		{
			$config['upload_path'] = 'assets/global/images/';
			$config['allowed_types'] = 'gif|jpg|png';
			$config['max_size']	= '2024';
			//$config['max_widtd']  = '300';
			//$config['max_height']  = '300';
			$config['file_name']	= $id;
			$config['overwrite']	= true;
			

			$this->upload->initialize($config);

			//Upload file 1
			if ($this->upload->do_upload('KEC_CAMIMG'))
			{
				$hasil = $this->upload->data();
			}
		}
	}
	
	function cek_desa()
	{
		//$hasil_cek = $this->M_desa->get_desa('DES_NAMA',$_POST['DES_NAMA']);
		$hasil_cek = $this->M_desa->get_desa2($_POST['KEC_ID'],$_POST['DES_NAMA']);
		echo $hasil_cek;
	}
	
	public function hapus()
	{
		$kec_id = $this->uri->segment(2,0);
		$id = $this->uri->segment(3,0);
		
		$hasil_cek = $this->M_desa->get_desa('DES_ID',$id);
		//$avatar = $hasil_cek->avatar;
		if(!empty($hasil_cek))
		{
			//$this->do_upload('',$avatar);
			//$hasil_cek = $hasil_cek->row();
			$this->M_desa->hapus($id);
		}
		header('Location: '.base_url().'data-kecamatan-desa/'.$kec_id);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_admin_kecamatan.php */