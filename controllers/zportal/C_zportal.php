<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_zportal extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_zportal_dash'));
	}
	
	public function index()
	{
		if(($this->session->userdata('ses_MUZ_EMAIL_public') == null) or ($this->session->userdata('ses_MUZ_PASS_public') == null))
		{
			header('Location: '.base_url().'zportal');
		}
		else
		{
			// $cek_ses_login = $this->M_akun->get_cek_login($this->session->userdata('ses_user_admin'),md5(base64_decode($this->session->userdata('ses_pass_admin'))));
			
			// if(!empty($cek_ses_login))
			// {
				// $ST_PENERIMAAN_PERPERIODE = $this->M_dash->ST_PENERIMAAN_PERPERIODE();
				
				// if((!empty($_GET['tahun'])) && ($_GET['tahun']!= "")   && (!empty($_GET['bulan'])) && ($_GET['bulan']!= "")  )
				// {
					// $LIST_KOTAK_PER_KECAMATAN = $this->M_dash->LIST_KOTAK_PER_KECAMATAN_CARI($_GET['tahun'],$_GET['bulan']);
				// }
				// else
				// {
					// $LIST_KOTAK_PER_KECAMATAN = $this->M_dash->LIST_KOTAK_PER_KECAMATAN();
				// }
				
				// $data = array('page_content'=>'admin_dashboard','ST_PENERIMAAN_PERPERIODE' => $ST_PENERIMAAN_PERPERIODE,'LIST_KOTAK_PER_KECAMATAN'=>$LIST_KOTAK_PER_KECAMATAN);
				// $this->load->view('admin/container',$data);
				// //echo "Hallo World";
			// }
			// else
			// {
				// header('Location: '.base_url().'admin-login');
			// }
			
			$list_pembayaran_zakat_perusahaan = $this->M_zportal_dash->ST_PENERIMAAN_PERPERIODE($this->session->userdata('ses_MUZ_ID_public')," WHERE AA.INMUZ_KAT = 'ZAKAT PERUSAHAAN'");
			
			$list_pembayaran_csr = $this->M_zportal_dash->ST_PENERIMAAN_PERPERIODE($this->session->userdata('ses_MUZ_ID_public')," WHERE AA.INMUZ_KAT = 'CSR'");
			
			$data = array('page_content' => 'admin_dashboard','list_pembayaran_zakat_perusahaan' => $list_pembayaran_zakat_perusahaan,'list_pembayaran_csr' => $list_pembayaran_csr);
			$this->load->view('zportal/container',$data);
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin.php */