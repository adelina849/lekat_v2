<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_lap_masuk_periode extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_lap_pemasukan'));
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
				if((!empty($_GET['tahun'])) && ($_GET['tahun']!= "")  )
				{
					$tahun = $_GET['tahun'];
				}
				else
				{
					$tahun = "";
				}
				
				if((!empty($_GET['bulan'])) && ($_GET['bulan']!= "")  )
				{
					$bulan = $_GET['bulan'];
				}
				else
				{
					$bulan = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE AA.KANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND 
							(
								AA.NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR AA.KABKOT LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "WHERE AA.KANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$list_laporan_per_periode = $this->M_lap_pemasukan->lap_per_periode($tahun,$bulan,$cari," ORDER BY AA.DT ASC, AA.INPOS_DTINS ASC,AA.NAMA ASC ");
				$data = array('page_content'=>'king_admin_lap_masuk_periode','list_laporan_per_periode' => $list_laporan_per_periode);
				$this->load->view('admin/container',$data);
				//echo "Hallo World";
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
	
	
	public function excel()
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
				if((!empty($_GET['tahun'])) && ($_GET['tahun']!= "")  )
				{
					$tahun = $_GET['tahun'];
				}
				else
				{
					$tahun = "";
				}
				
				if((!empty($_GET['bulan'])) && ($_GET['bulan']!= "")  )
				{
					$bulan = $_GET['bulan'];
				}
				else
				{
					$bulan = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					/*$cari = "WHERE AA.KANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND AA.NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'";*/
					$cari = "WHERE AA.KANTOR = '".$this->session->userdata('ses_kode_kantor')."' 
							AND 
							(
								AA.NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR AA.KABKOT LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "WHERE AA.KANTOR = '".$this->session->userdata('ses_kode_kantor')."'";
				}
				
				$list_laporan_per_periode = $this->M_lap_pemasukan->lap_per_periode($tahun,$bulan,$cari," ORDER BY AA.DT ASC, AA.INPOS_DTINS ASC,AA.NAMA ASC ");
				//$data = array('page_content'=>'king_admin_lap_masuk_periode','list_laporan_per_periode' => $list_laporan_per_periode);
				//$this->load->view('admin/container',$data);
				
				$data = array('list_laporan_per_periode' => $list_laporan_per_periode);
				$this->load->view('admin/page/king_admin_excel_lap_masuk_periode.html',$data);
				//echo "Hallo World";
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin.php */