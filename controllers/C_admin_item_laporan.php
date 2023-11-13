<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_item_laporan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_laporan','M_item_laporan'));
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
				$data_laporan = $this->M_laporan->get_laporan('LAP_ID',$LAP_ID);
				if(!empty($data_laporan))
				{
					$jenis_laporan = $data_laporan->row();
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE A.LAP_ID = '".$LAP_ID."' AND A.ILAP_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";
					}
					else
					{
						$cari = "WHERE A.LAP_ID = '".$LAP_ID."' ";
					}
					
					$this->load->library('pagination');
					$config['first_url'] = site_url('item-jenis-laporan?'.http_build_query($_GET));
					$config['base_url'] = site_url('item-jenis-laporan/');
					$config['total_rows'] = $this->M_item_laporan->count_item_laporan_limit($cari)->JUMLAH;
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
					$list_item_laporan = $this->M_item_laporan->list_item_laporan_limit($cari,$config['per_page'],$this->uri->segment(3,0));
					$NoOrderMax = $this->M_item_laporan->getMaxOrder($LAP_ID);
					$data = array('page_content'=>'ptn_admin_item_laporan','halaman'=>$halaman,'data_laporan' => $jenis_laporan,'list_item_laporan'=>$list_item_laporan,'NoOrderMax'=>$NoOrderMax);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'jenis-laporan');
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
		
		if (!empty($_POST['stat_edit']))
		{
			$this->M_item_laporan->edit
			(
				$_POST['stat_edit']
				,$_POST['LAP_ID']
				,$_POST['ILAP_NAMA']
				,$_POST['ILAP_TYPE']
				,$_POST['ILAP_KET']
				,$_POST['ILAP_ORDR']
				,$this->session->userdata('ses_id_karyawan')
			);
			
			$ORDER_BFR = $_POST['ILAP_ORDR_EDIT'];
			$ORDER_CUR = $_POST['ILAP_ORDR'];
			
			// echo $ORDER_BFR.'</br>'.$ORDER_CUR;
			
			if($ORDER_BFR > $ORDER_CUR)
			{
				$this->M_item_laporan->editOrderNaik($_POST['stat_edit'],$_POST['LAP_ID'],$ORDER_BFR,$ORDER_CUR);
			}
			elseif($ORDER_BFR < $ORDER_CUR)
			{
				$this->M_item_laporan->editOrderTurun($_POST['stat_edit'],$_POST['LAP_ID'],$ORDER_BFR,$ORDER_CUR);
			}
			
			
			header('Location: '.base_url().'item-jenis-laporan/'.$_POST['LAP_ID']);
		}
		else
		{
			
			$this->M_item_laporan->simpan
			(
				$_POST['LAP_ID']
				,$_POST['ILAP_NAMA']
				,$_POST['ILAP_TYPE']
				,$_POST['ILAP_KET']
				,$_POST['ILAP_ORDR']
				,$this->session->userdata('ses_id_karyawan')
				,$this->session->userdata('ses_kode_kantor')
				,"KEC"
			);
			header('Location: '.base_url().'item-jenis-laporan/'.$_POST['LAP_ID']);
		}
		
	}
	
	function cek_item_laporan()
	{
		$hasil_cek = $this->M_item_laporan->get_item_laporan('ILAP_NAMA',$_POST['ILAP_NAMA']);
		if(!empty($hasil_cek))
		{
			echo $hasil_cek->row()->ILAP_NAMA;
		}
		else
		{
			false;
		}
	}
	
	function cek_no_laporan()
	{
		$hasil_cek = $this->M_item_laporan->get_no_order_laporan($_POST['LAP_ID'],$_POST['ILAP_ORDR']);
		if(!empty($hasil_cek))
		{
			echo $hasil_cek->row()->ILAP_NAMA;
		}
		else
		{
			false;
		}
		
	}
	
	public function hapus()
	{
		$LAP_ID = $this->uri->segment(2,0);
		$id = $this->uri->segment(3,0);
		
		$hasil_cek = $this->M_item_laporan->get_item_laporan('ILAP_ID',$id);
		//$avatar = $hasil_cek->avatar;
		if(!empty($hasil_cek))
		{
			//$this->do_upload('',$avatar);
			//$hasil_cek = $hasil_cek->row();
			$this->M_item_laporan->hapus($id);
		}
		header('Location: '.base_url().'item-jenis-laporan/'.$LAP_ID);
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_admin_kecamatan.php */