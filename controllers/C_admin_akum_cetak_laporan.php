<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_admin_akum_cetak_laporan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_buat_laporan','M_d_buat_laporan','M_kec','M_laporan','M_item_laporan','M_klaporan','M_periode'));
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
					$cari = "  WHERE KLAP_ISAKTIF = 0 AND (LAP_KODE LIKE '%".str_replace("'","",$_GET['cari'])."%' OR LAP_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = " WHERE KLAP_ISAKTIF = 0 ";
				}
				$list_klaporan = $this->M_klaporan->list_klaporan_limit($cari,100,0);
				$data = array('page_content'=>'ptn_admin_akum_laporan_cetak','list_klaporan'=>$list_klaporan,'cari' => $cari);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
	
	function akumulasi_periode()
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
					$data_laporan = $data_laporan->row();
					
					$list_periode = $this->M_periode->list_periode_limit(" WHERE PER_KATEGORI = '".$data_laporan->LAP_PERIODE."'",10,0);
					if(!empty($list_periode))
					{
						$jum_periode = $list_periode->num_rows();
					}
					else
					{
						$jum_periode =0;
					}
					
					$list_kecamatan = $this->M_kec->list_kecamatan_limit('',50,0);
					if(!empty($list_kecamatan))
					{
						$jum_kecamatan = $list_kecamatan->num_rows();
					}
					else
					{
						$jum_kecamatan =0;
					}
					
					$data = array('page_content'=>'ptn_admin_akum_laporan_kec_cetak','data_laporan'=>$data_laporan,'jum_kecamatan'=>$jum_kecamatan,'list_periode' => $list_periode,'jum_periode' => $jum_periode,'list_kecamatan' => $list_kecamatan);
					$this->load->view('admin/container',$data);
				}
				else
				{
					header('Location: '.base_url().'akumulasi-laporan-kecamatan-cetak');
				}
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}

	public function proses_laporan()
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
				$LAP_ID = $_POST['LAP_ID'];
				$data_laporan = $this->M_laporan->get_laporan('LAP_ID',$LAP_ID);
				if(!empty($data_laporan))
				{
					$data_laporan = $data_laporan->row();
					$jum_periode = $_POST['jum_periode'];
					$jum_kecamatan = $_POST['jum_kecamatan'];
					
					$data_item_laporan = $this->M_item_laporan->get_item_laporan('LAP_ID',$LAP_ID);
					$jum_col = $data_item_laporan->num_rows();
					// // echo'PERIODE';
					// // echo'</br>';
					// for($no = 1;$no<=$jum_periode;$no++)
					// {
						// //echo $no.'</br>';
						// //echo $_POST['per_'.$no].'</br>';
						// //echo $_POST['LAP_ID'].'</br>';
					// }
					// // echo'</br>';
					// // echo'</br>KECAMATAN';
					// // echo'</br>';
					// for($no = 1;$no<=$jum_kecamatan;$no++)
					// {
						// //echo $no.'</br>';
					// }
					
					
					$data = array('page_content'=>'ptn_admin_akum_detail_laporan_cetak','data_laporan'=>$data_laporan,'jum_kecamatan'=>$jum_kecamatan,'jum_periode' => $jum_periode,'data_item_laporan' => $data_item_laporan,'jum_col' => $jum_col);
					//$this->load->view('admin/container',$data);
					
					$this->load->view('admin/page/ptn_admin_akum_detail_laporan_cetak.html',$data);
					
					/*$list_periode = $this->M_periode->list_periode_limit(" WHERE PER_KATEGORI = '".$data_laporan->LAP_PERIODE."'",10,0);
					if(!empty($list_periode))
					{
						$jum_periode = $list_periode->num_rows();
					}
					else
					{
						$jum_periode =0;
					}
					
					$list_kecamatan = $this->M_kec->list_kecamatan_limit('',50,0);
					if(!empty($list_kecamatan))
					{
						$jum_kecamatan = $list_kecamatan->num_rows();
					}
					else
					{
						$jum_kecamatan =0;
					}
					
					$data = array('page_content'=>'ptn_admin_akum_laporan_kec','data_laporan'=>$data_laporan,'jum_kecamatan'=>$jum_kecamatan,'list_periode' => $list_periode,'jum_periode' => $jum_periode,'list_kecamatan' => $list_kecamatan);
					$this->load->view('admin/container',$data);*/
				}
				else
				{
					header('Location: '.base_url().'akumulasi-laporan-kecamatan-cetak');
				}
			}
			else
			{
				header('Location: '.base_url().'admin-login');
			}
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_admin_akum_laporan.php */