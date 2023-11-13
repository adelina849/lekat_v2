<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_dash extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_gl_pst_dash','M_gl_pst_inventori'));
	}
	
	public function index()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."' AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ");
			
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
				
				
				$statistik_kunjungan_cabang = $this->M_gl_pst_dash->ST_KUNJUNGAN("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN isViewClient IN (0,1) ELSE isViewClient = 0 END",$dari,$sampai);
				
				/*
				//LOG AKTIFITAS
				$cari_log = "WHERE A.kode_kantor <> 'PUSAT'";
				$order_by_log = "ORDER BY A.waktu DESC";
				$limit_log = 30;
				$offset_log = 0;
				$log_aktifitas = $this->M_gl_pst_dash->list_log_aktifitas_limit($cari_log,$order_by_log,$limit_log,$offset_log);
				//LOG AKTIFITAS
				
				//PERGERAKAN PRODUK
				
				$dari_pergeran_produk = date("Y-m-d");
				$sampai_pergeran_produk = date("Y-m-d");
				$cari_pergeran_produk = "";
				$kode_kantor_pergeran_produk = "";
				$opcd_pergeran_produk = "";
				
				
				
				$list_alur_produk = $this->M_gl_pst_inventori->list_pergerakan_produk($kode_kantor_pergeran_produk,$dari_pergeran_produk,$sampai_pergeran_produk,$cari_pergeran_produk,$opcd_pergeran_produk);
				//PERGERAKAN PRODUK
				*/
				
				//$list_uang_perakun_bank = $this->M_gl_pst_dash->list_uang_masuk_perakun_bank();
				$akumulasi_pendapatan_percabang = $this->M_gl_pst_dash->akumulasi_pendapatan_percabang("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN B.isViewClient IN (0,1) ELSE B.isViewClient = 0 END",$dari,$sampai);
				
				$akumulasi_pendapatan_percabang_perbulan = $this->M_gl_pst_dash->akumulasi_pendapatan_percabang_perbulan("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN B.isViewClient IN (0,1) ELSE B.isViewClient = 0 END");
				
				//$list_20_produk_terlaris = $this->M_gl_pst_dash->list_produk_terlaris();
				$list_produk_terlaris_percabang = $this->M_gl_pst_dash->produk_terlaris_percabang("WHERE CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN C.isViewClient IN (0,1) ELSE C.isViewClient = 0 END",$dari,$sampai);
				
				$list_perubahan_transaksi_cabang = $this->M_gl_pst_dash->list_count_perubahan_transaksi(" AND CASE WHEN ".$this->session->userdata('ses_hirarki')." = 1 THEN B.isViewClient IN (0,1) ELSE B.isViewClient = 0 END ",$dari,$sampai);
				//$list_nominal_stock_percabang = $this->M_gl_pst_dash->nominal_stock_percabang();
				
				//$data = array('page_content'=>'gl_pusat_dashboard','statistik_kunjungan_cabang' => $statistik_kunjungan_cabang,'log_aktifitas' => $log_aktifitas,'list_alur_produk' => $list_alur_produk,'list_uang_perakun_bank' => $list_uang_perakun_bank,'list_20_produk_terlaris' => $list_20_produk_terlaris);
				
				$msgbox_title = "Filter Dashboard";
				
				$data = array(
							'page_content'=>'gl_pusat_dashboard'
							,'statistik_kunjungan_cabang' => $statistik_kunjungan_cabang
							
							//,'list_uang_perakun_bank' => $list_uang_perakun_bank
							,'akumulasi_pendapatan_percabang' => $akumulasi_pendapatan_percabang
							
							//,'list_20_produk_terlaris' => $list_20_produk_terlaris
							,'list_produk_terlaris_percabang' => $list_produk_terlaris_percabang
							
							,'list_perubahan_transaksi_cabang' => $list_perubahan_transaksi_cabang
							//,'list_nominal_stock_percabang' => $list_nominal_stock_percabang
							,'akumulasi_pendapatan_percabang_perbulan' => $akumulasi_pendapatan_percabang_perbulan
							,'msgbox_title' => $msgbox_title
							);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin.php */