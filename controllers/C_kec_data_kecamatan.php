<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_kec_data_kecamatan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_kec'));
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
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					//$cari = "WHERE KEC_KKANTOR LIKE '%".$this->session->userdata('ses_kode_kantor')."'% AND KEC_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
					//$cari = "WHERE A.KEC_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
					$cari = " WHERE A.KEC_ID = '".$this->session->userdata('ses_KEC_ID')."' ";
					//ses_KEC_ID
				}
				else
				{
					$cari = " WHERE A.KEC_ID = '".$this->session->userdata('ses_KEC_ID')."' ";
				}
				
				$this->load->library('pagination');
				$config['first_url'] = site_url('kecamatan?'.http_build_query($_GET));
				$config['base_url'] = site_url('kecamatan/');
				$config['total_rows'] = $this->M_kec->count_kecamatan_limit($cari)->JUMLAH;
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
				$list_kecamatan = $this->M_kec->list_kecamatan_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				$data = array('page_content'=>'ptn_kec_data_kecamatan','halaman'=>$halaman,'list_kecamatan'=>$list_kecamatan);
				$this->load->view('kecamatan/container',$data);
			}
			else
			{
				header('Location: '.base_url().'kecamatan-login');
			}
		}
	}
	
	public function simpan()
	{
		//$this->do_upload(str_replace(" ","",$_FILES['foto']['name']),'');
		//$foto = str_replace(" ","",$_FILES['foto']['name']);
		if (!empty($_FILES['KEC_IMG']['name']))
		{
			$this->do_upload_img("KEC".date("YmdHis"),'');
			$KEC_IMG = "KEC".date("YmdHis").".".pathinfo($_FILES['KEC_IMG']['name'], PATHINFO_EXTENSION);
		}
		else
		{
			$KEC_IMG = '';
		}
		
		
		if (!empty($_FILES['KEC_CAMIMG']['name']))
		{
			$this->do_upload_camimg("KACAM".date("YmdHis"),'');
			$KEC_CAMIMG = "KACAM".date("YmdHis").".".pathinfo($_FILES['KEC_CAMIMG']['name'], PATHINFO_EXTENSION);
		}
		else
		{
			$KEC_CAMIMG = '';
		}
		
		
		if (!empty($_POST['stat_edit']))
		{
			$this->M_kec->edit
			(
				$_POST['stat_edit']
				,$_POST['KEC_KODE']
				,$_POST['KEC_NAMA']
				,$_POST['KEC_CAMAT']
				,$_POST['KEC_BUCAMAT']
				,$_POST['KEC_SEKWILMAT']
				,""
				,$_POST['KEC_TLP']
				,$_POST['KEC_EMAIL']
				,$_POST['KEC_ALAMAT']
				,$_POST['KEC_KET']
				,$_POST['KEC_LONGI']
				,$_POST['KEC_LATI']
				,$KEC_IMG
				,$KEC_CAMIMG
				,$this->session->userdata('ses_id_karyawan')
			);
			header('Location: '.base_url().'data-kecamatan');
		}
		else
		{
			
			/*
			$this->M_kec->simpan
			(
				$_POST['KEC_KODE']
				,$_POST['KEC_NAMA']
				,$_POST['KEC_CAMAT']
				,$_POST['KEC_SEKWILMAT']
				,""
				,$_POST['KEC_TLP']
				,$_POST['KEC_ALAMAT']
				,$_POST['KEC_KET']
				,$_POST['KEC_LONGI']
				,$_POST['KEC_LATI']
				,$KEC_IMG
				,$KEC_CAMIMG
				,$this->session->userdata('ses_id_karyawan')
				,$this->session->userdata('ses_kode_kantor')
				,"KAB"
			);
			*/
			header('Location: '.base_url().'data-kecamatan');
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
	
	function cek_kecamatan()
	{
		$hasil_cek = $this->M_kec->get_Kecamatan('KEC_KODE',$_POST['KEC_KODE']);
		echo $hasil_cek;
	}
	
	public function hapus()
	{
		$id = $this->uri->segment(2,0);
		$hasil_cek = $this->M_kec->get_Kecamatan('KEC_ID',$id);
		//$avatar = $hasil_cek->avatar;
		if(!empty($hasil_cek))
		{
			//$this->do_upload('',$avatar);
			$hasil_cek = $hasil_cek->row();
			$this->do_upload_img('',$hasil_cek->KEC_IMG);
			$this->do_upload_camimg('',$hasil_cek->KEC_CAMIMG);
			$this->M_kec->hapus($id);
		}
		header('Location: '.base_url().'data-kecamatan');
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_admin_kecamatan.php */