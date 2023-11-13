<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_penjualan_langsung extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_costumer','M_gl_h_penjualan','M_gl_media_transaksi','M_gl_kat_costumer'));
		
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
				$cari_costumer = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.isDefault = '1' ";
				$data_costumer_default = $this->M_gl_costumer->list_costumer_biasa($cari_costumer,1,0);
				if(!empty($data_costumer_default))
				{
					$data_costumer_default = $data_costumer_default->row();
				}
				else
				{
					$cari_costumer = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
					$data_costumer_default = $this->M_gl_costumer->list_costumer_biasa($cari_costumer,1,0);
					
					if(!empty($data_costumer_default))
					{
						$data_costumer_default = $data_costumer_default->row();
					}
					else
					{
						$data_costumer_default = false;
					}
				}
				
				$cari_media = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
				$order_by_media = " ORDER BY nama_media ASC";
				$media_transaksi = $this->M_gl_media_transaksi->list_media_transaksi_limit($cari_media,$order_by_media,1000,0);
				
				/*DATA KANTOR*/
					// $cari_kantor = " WHERE kode_kantor <> '".$this->session->userdata('ses_kode_kantor')."'";
					$list_kantor = $this->M_gl_pengaturan->get_data_kantor("");
				/*DATA KANTOR*/
				
				//if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
				//{
					$cari_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ORDER BY nama_kat_costumer ASC;";
					$list_kat_costumer = $this->M_gl_kat_costumer->get_kat_costumer_cari($cari_kat_costumer);
				//}
				
				$msgbox_title = " Transaksi Penjualan";
				
				if($this->session->userdata('ses_isModePst') == "YA")
				{
					$data = array('page_content'=>'gl_admin_penjualan_langsung_pst','msgbox_title' => $msgbox_title,'data_costumer_default' => $data_costumer_default,'media_transaksi' => $media_transaksi,'list_kantor' => $list_kantor);
					$this->load->view('admin/container',$data);
				}
				else
				{
					$data = array('page_content'=>'gl_admin_penjualan_langsung','msgbox_title' => $msgbox_title,'data_costumer_default' => $data_costumer_default,'media_transaksi' => $media_transaksi,'list_kantor' => $list_kantor,'list_kat_costumer'=>$list_kat_costumer);
					$this->load->view('admin/container',$data);
				}
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	public function proses_awal_penjualan_langsung()
	{
		if((!empty($_GET['token'])) && ($_GET['token']!= "")  )
		{
			$id_costumer = $_GET['token'];
		}
		else
		{
			$id_costumer = md5($_POST['id_costumer']);
		}
		
		if((!empty($_POST['ket_penjualan'])) && ($_POST['ket_penjualan']!= "")  )
		{
			$ket_penjualan = $_POST['ket_penjualan'];
		}
		else
		{
			$ket_penjualan = "";
		}
		
		if((!empty($_POST['no_costumer_ori'])) && ($_POST['no_costumer_ori']!= "")  )
		{
			$no_costumer = $_POST['no_costumer_ori'];
		}
		else
		{
			$no_costumer = "";
		}
		
		//DATA COSTUMER
			//$cari_costumer = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND md5(A.id_costumer) = '".$id_costumer."' ";
			$cari_costumer = "WHERE A.no_costumer = '".$no_costumer."'";
			
			
			$data_costumer = $this->M_gl_costumer->list_costumer_biasa($cari_costumer,1,0);
			if(!empty($data_costumer))
			{
				$data_costumer = $data_costumer->row();
				//echo $id_costumer;
				//BUAT SIMPAN H PENJUALAN
					$data_no_antrian = $this->M_gl_h_penjualan->get_no_antrian($this->session->userdata('ses_kode_kantor'));
					if(!empty($data_no_antrian))
					{
						$data_no_antrian = $data_no_antrian->row();
						//echo $data_costumer->no_costumer.'<br/>'.$data_costumer->nama_lengkap;
						
						$this->M_gl_h_penjualan->simpan_h_penjualan_awal
						(
							$data_no_antrian->id_h_penjualan,
							$data_costumer->id_costumer, //$_POST['id_costumer'],
							$this->session->userdata('ses_id_karyawan'), //$_POST['id_karyawan'],
							'', //$_POST['id_dokter'], //id_dokter
							'', //$_POST['id_h_diskon'], //id_h_diskon
							$data_no_antrian->no_faktur,
							$data_no_antrian->no_antrian,
							$data_costumer->no_costumer, //$_POST['no_costmer'],
							$data_costumer->nama_lengkap, //$_POST['nama_costumer'],
							$data_costumer->nama_kat_costumer, //$_POST['nama_kat_costumer'],
							$data_costumer->id_kat_costumer, //$_POST['id_kat_costumer'],
							$data_costumer->kode_kantor, //$_POST['kode_kantor_costumer'],
							date("Y-m-d"), //$_POST['tgl_h_penjualan'],
							'CASH',
							$ket_penjualan.' (PENJUALAN LANGSUNG)', //$_POST['ket_penjualan'],
							'PENJUALAN LANGSUNG', //$_POST['type_h_penjualan'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor'),
							'PENDING', //$_POST['sts_penjualan']
							$_POST['media_transaksi']
						);
						//echo $data_no_antrian->id_h_penjualan.'|'.$data_no_antrian->no_faktur.'|'.$data_no_antrian->no_antrian;
						
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
					 
							$image_name= $data_no_antrian->no_faktur.'.png'; //buat name dari qr code sesuai dengan nim
					 
							$params['data'] = $data_no_antrian->no_faktur; //data yang akan di jadikan QR CODE
							$params['level'] = 'H'; //H=High
							$params['size'] = 10;
							$params['savename'] = FCPATH.$config['imagedir'].$image_name; //simpan image QR CODE ke folder assets/images/
							$this->ciqrcode->generate($params); // fungsi untuk generate QR CODE
						//GENERATE QR CODE
						
						header('Location: '.base_url().'gl-admin-pemeriksaan-dokter-proses/'.md5($data_no_antrian->id_h_penjualan));
						
					}
				//BUAT SIMPAN H PENJUALAN
			}
			else
			{
				echo $no_costumer;
			}
		//DATA COSTUMER
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */