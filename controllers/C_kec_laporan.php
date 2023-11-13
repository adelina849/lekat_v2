<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_kec_laporan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_laporan','M_klaporan','M_item_laporan','M_buat_laporan','M_d_buat_laporan'));
		
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
				$BLAP_ID = $this->uri->segment(2,0);
				//echo $LAP_KODE;
				$data_buat_laporan = $this->M_buat_laporan->get_buat_laporan('BLAP_ID',$BLAP_ID);
				if(!empty($data_buat_laporan))
				{
					$data_buat_laporan = $data_buat_laporan->row();
					$LAP_ID = $data_buat_laporan->LAP_ID;
					$data_laporan = $this->M_laporan->get_laporan('LAP_ID',$LAP_ID);
					if(!empty($data_laporan))
					{
						$data_laporan = $data_laporan->row();
						$data_item_laporan = $this->M_item_laporan->get_item_laporan('LAP_ID',$LAP_ID);
						
						$data_seqn = $this->M_d_buat_laporan->get_Seqn_d_laporan($BLAP_ID);
						
						//$list_d_buat_laporan = $this->M_d_buat_laporan->list_d_buat_laporan_limit_for_table(" WHERE A.DBLAP_KKANTOR = '".$this->session->userdata('ses_KEC_ID')."' AND A.BLAP_ID = '".$data_buat_laporan->BLAP_ID."' ",100000,0);
						
						$list_d_buat_laporan = $this->M_d_buat_laporan->list_d_buat_laporan_proc($this->session->userdata('ses_KEC_ID'),$LAP_ID,$BLAP_ID);
						
						$data_laporan_yang_sudah_buat = $this->M_buat_laporan->get_laporan_yang_sudah_buat($LAP_ID,$this->session->userdata('ses_KEC_ID'),$BLAP_ID);
						
						$tgl_hari_ini_indo = $this->tanggal_indonesia(date('Y-m-d'));
						//$data_laporan_yang_sudah_buat = false;
						
						$data = array('page_content'=>'ptn_kec_laporan_proc','data_buat_laporan' => $data_buat_laporan,'data_laporan' => $data_laporan,'data_item_laporan' => $data_item_laporan,'list_d_buat_laporan' => $list_d_buat_laporan,'data_seqn' => $data_seqn,'data_laporan_yang_sudah_buat' => $data_laporan_yang_sudah_buat,'tgl_hari_ini_indo' => $tgl_hari_ini_indo);
						$this->load->view('kecamatan/container',$data);
					}
					else
					{
						header('Location: '.base_url().'kecamatan-admin-dashboard');
					}
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
	
	function tanggal_indonesia($tanggal)
	{
		$bulan = array (
		1 =>   'Januari',
		'Februari',
		'Maret',
		'April',
		'Mei',
		'Juni',
		'Juli',
		'Agustus',
		'September',
		'Oktober',
		'November',
		'Desember'
		);
		
		$pecahkan = explode('-', $tanggal);
		
		// variabel pecahkan 0 = tanggal
		// variabel pecahkan 1 = bulan
		// variabel pecahkan 2 = tahun
		 
		return $pecahkan[2] . ' ' . $bulan[ (int)$pecahkan[1] ] . ' ' . $pecahkan[0];
	}
	
	function copy_laporan()
	{
		$this->M_d_buat_laporan->hapus_all($_POST['BLAP_ID_CUR']);
		$this->M_buat_laporan->update_hitung_d_laporan($_POST['BLAP_ID_CUR'],10);
		$this->M_d_buat_laporan->copy_laporan($_POST['BLAP_ID_CUR'],$_POST['BLAP_ID_BFR'],$this->session->userdata('ses_id_karyawan'));
		header('Location: '.base_url().'buat-detail-laporan/'.$_POST['BLAP_ID_CUR']);
	}
	
	public function view_edit()
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
				$BLAP_ID = $this->uri->segment(2,0);
				$SEQN = $this->uri->segment(3,0);
				//echo $LAP_KODE;
				$data_buat_laporan = $this->M_buat_laporan->get_buat_laporan('BLAP_ID',$BLAP_ID);
				if(!empty($data_buat_laporan))
				{
					$data_buat_laporan = $data_buat_laporan->row();
					$LAP_ID = $data_buat_laporan->LAP_ID;
					$data_laporan = $this->M_laporan->get_laporan('LAP_ID',$LAP_ID);
					if(!empty($data_laporan))
					{
						$data_laporan = $data_laporan->row();
						$data_d_buat_laporan = $this->M_d_buat_laporan->list_d_buat_laporan_isi_edit($BLAP_ID,$SEQN);
						
						$data = array('page_content'=>'ptn_kec_edit_laporan','data_buat_laporan' => $data_buat_laporan,'data_laporan' => $data_laporan,'data_d_buat_laporan' => $data_d_buat_laporan,'SEQN' => $SEQN);
						$this->load->view('kecamatan/container',$data);
					}
					else
					{
						header('Location: '.base_url().'kecamatan-admin-dashboard');
					}
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
		$this->M_d_buat_laporan->hapus($_POST['BLAP_ID'],$_POST['SEQN']);
		echo $_POST['BLAP_ID'];
		echo'<br/>';
		echo $_POST['LAP_ID'];
		echo'<br/>';
		echo $_POST['KEC_ID'];
		echo'<br/>';
		echo $_POST['no'];
		$no = $_POST['no']*1;
		for($i=0;$i<$no;$i++)
		{
			echo'<br/>';
			echo $_POST[$i].' - '.$_POST[$i.'_ILAP_ID'] ;
			
			$this->M_d_buat_laporan->simpan(
					$_POST['BLAP_ID']
					,$_POST['LAP_ID']
					,$_POST['KEC_ID']
					,$_POST[$i.'_ILAP_ID'] 
					,$_POST['SEQN']
					,$_POST[$i]
					,$this->session->userdata('ses_id_karyawan')
					,$this->session->userdata('ses_KEC_ID')
					,"KEC"
				);
			
			
		}
		
		$this->M_buat_laporan->update_hitung_d_laporan($_POST['BLAP_ID'],$_POST['SEQN']);
		header('Location: '.base_url().'buat-detail-laporan/'.$_POST['BLAP_ID']);
	}
	
	public function hapus()
	{
		$BLAP_ID = $this->uri->segment(2,0);
		$SEQN = $this->uri->segment(3,0);
		$this->M_d_buat_laporan->hapus($BLAP_ID,$SEQN);
		header('Location: '.base_url().'buat-detail-laporan/'.$BLAP_ID);
	}
	
	function cek_laporan()
	{
		$hasil_cek = $this->M_laporan->get_laporan('LAP_KODE',$_POST['LAP_KODE']);
		echo $hasil_cek;
	}
	
	
	
	public function print_excel()
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
				$BLAP_ID = $this->uri->segment(3,0);
				//echo $LAP_KODE;
				$data_buat_laporan = $this->M_buat_laporan->get_buat_laporan_join_dengan_kecamatan('A.BLAP_ID',$BLAP_ID);
				if(!empty($data_buat_laporan))
				{
					//echo'ADA DATA';
					
					$data_buat_laporan = $data_buat_laporan->row();
					$LAP_ID = $data_buat_laporan->LAP_ID;
					$data_laporan = $this->M_laporan->get_laporan('LAP_ID',$LAP_ID);
					if(!empty($data_laporan))
					{
						$data_laporan = $data_laporan->row();
						$data_item_laporan = $this->M_item_laporan->get_item_laporan('LAP_ID',$LAP_ID);
						
						$data_seqn = $this->M_d_buat_laporan->get_Seqn_d_laporan($BLAP_ID);
						
						//$list_d_buat_laporan = $this->M_d_buat_laporan->list_d_buat_laporan_limit_for_table(" WHERE A.DBLAP_KKANTOR = '".$this->session->userdata('ses_KEC_ID')."' AND A.BLAP_ID = '".$data_buat_laporan->BLAP_ID."' ",100000,0);
						
						if(($this->session->userdata('ses_filter_KEC_ID') == null) or ($this->session->userdata('ses_filter_KEC_ID') == null))
						{
							$KEC_ID = $this->session->userdata('ses_KEC_ID');
						}
						else
						{
							$KEC_ID = $this->session->userdata('ses_filter_KEC_ID');
						}
						
						$list_d_buat_laporan = $this->M_d_buat_laporan->list_d_buat_laporan_proc($KEC_ID,$LAP_ID,$BLAP_ID);
						
						$data = array('page_content'=>'ptn_kec_laporan','data_buat_laporan' => $data_buat_laporan,'data_laporan' => $data_laporan,'data_item_laporan' => $data_item_laporan,'list_d_buat_laporan' => $list_d_buat_laporan,'data_seqn' => $data_seqn);
						//$this->load->view('kecamatan/container',$data);
						$this->load->view('kecamatan/page/ptn_kec_laporan_excel.html',$data);
					}
					else
					{
						header('Location: '.base_url().'kecamatan-admin-dashboard');
					}
				}
				else
				{
					// echo'GAK ADA DATA';
					header('Location: '.base_url().'kecamatan-admin-dashboard');
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
/* Location: ./application/controllers/C_admin_laporan.php */