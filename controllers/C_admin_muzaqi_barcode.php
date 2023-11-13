<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_muzaqi_barcode extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_barcode'));
		
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
				// $data = array('page_content'=>'king_jabatan');
				// $this->load->view('admin/container',$data);
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE BAR_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND BAR_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%'";
				}
				else
				{
					$cari = "WHERE BAR_KODEKANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('admin-muzaqi-barcode?'.http_build_query($_GET));
				$config['base_url'] = site_url('admin-muzaqi-barcode/');
				$config['total_rows'] = $this->M_barcode->count_barcode_limit($cari)->JUMLAH;
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
				
				$list_kategori_pos = $this->M_barcode->list_barcode_limit($cari,$config['per_page'],$this->uri->segment(2,0));
				
				$data = array('page_content'=>'king_admin_muzaqi_barcode','halaman'=>$halaman,'list_kategori_pos'=>$list_kategori_pos);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
	
	public function simpan()
	{
		$BANYAK = $_POST['REQ_BAR'];
		if($BANYAK > 20)
		{
			$BANYAK = 20;
		}
		
		for($i=1;$i<=$BANYAK;$i++)
		{
			$this->M_barcode->simpan
			(
				'MUZAQI',
				$this->session->userdata('ses_kode_kantor')
			);
			
			$data_barcode = $this->M_barcode->list_barcode_limit('',1,0)	;
			if(!empty($data_barcode))
			{
				$data_barcode = $data_barcode->row();
				//GENERATE QR CODE
					$this->load->library('ciqrcode'); //pemanggilan library QR CODE
					$config['cacheable']    = true; //boolean, the default is true
					$config['cachedir']     = './assets/'; //string, the default is application/cache/
					$config['errorlog']     = './assets/'; //string, the default is application/logs/
					$config['imagedir']     = './assets/global/images/qrcode/'; //direktori penyimpanan qr code
					$config['quality']      = true; //boolean, the default is true
					$config['size']         = '1024'; //interger, the default is 1024
					$config['black']        = array(224,255,255); // array, default is array(255,255,255)
					$config['white']        = array(70,130,180); // array, default is array(0,0,0)
					$this->ciqrcode->initialize($config);
			 
					$image_name=$data_barcode->BAR_KODE.'.png'; //buat name dari qr code sesuai dengan nim
			 
					$params['data'] = $data_barcode->BAR_KODE; //data yang akan di jadikan QR CODE
					$params['level'] = 'H'; //H=High
					$params['size'] = 10;
					$params['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
					$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
				//GENERATE QR CODE
			}
		}
			
			header('Location: '.base_url().'admin-muzaqi-barcode');
		
		
		//echo 'ade';*/
	}
	
	public function hapus()
	{
		$BAR_KODE = $this->uri->segment(2,0);
		$this->M_barcode->hapus($this->session->userdata('ses_kode_kantor'),$BAR_KODE);
		$this->do_upload('',$BAR_KODE);
			$this->M_log->simpan(
				"DELETE",
				$this->session->userdata('ses_nama_karyawan'),
				"Melakukan penghapusan barcode dengan id : ".$BAR_KODE." ",
				$this->session->userdata('ses_kode_kantor')
			);
		header('Location: '.base_url().'admin-muzaqi-barcode');
	}
	
	function cek_barcode()
	{	
		$BAR_NAMA = $_POST['BAR_NAMA'];
		$hasil_cek = $this->M_barcode->get_barcode('BAR_NAMA',$BAR_NAMA);
		echo $hasil_cek;
	}
	
	function do_upload($id,$cek_bfr)
	{
		$this->load->library('upload');

		if($cek_bfr != '')
		{
			@unlink('./assets/global/images/qrcode/'.$cek_bfr.'.png');
		}
		
		if (!empty($_FILES['foto']['name']))
		{
			$config['upload_path'] = 'assets/global/images/qrcode/';
			$config['allowed_types'] = 'gif|jpg|jpeg|png';
			//$config['max_size']	= '2024';
			//$config['max_widtd']  = '300';
			//$config['max_height']  = '300';
			$config['file_name']	= $id;
			$config['overwrite']	= true;
			

			$this->upload->initialize($config);

			//Upload file 1
			if ($this->upload->do_upload('foto'))
			{
				$hasil = $this->upload->data();
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */