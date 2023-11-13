<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_public_jadwal_kegiatan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_jadwal_kegiatan','M_lokasi','M_images'));
		
	}
	
	public function index()
	{
		if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
		{
			$cari = "WHERE
						A.JKEG_ISPUBLISH = 1
						AND DATE(A.JKEG_DTDARI) >= DATE(NOW()) AND DATE(A.JKEG_DTSAMPAI) <= DATE(NOW())
						AND
						(
							A.JKEG_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%' 
							OR C.name LIKE '%".str_replace("'","",$_GET['cari'])."%'
							OR D.name LIKE '%".str_replace("'","",$_GET['cari'])."%' 
							OR E.name LIKE '%".str_replace("'","",$_GET['cari'])."%'
							OR F.name LIKE '%".str_replace("'","",$_GET['cari'])."%'
							OR A.JKEG_ALAMATDETAIL '%".str_replace("'","",$_GET['cari'])."%'
						)
					";
		}
		else
		{
			$cari = " WHERE A.JKEG_ISPUBLISH = 1 AND DATE(A.JKEG_DTDARI) >= DATE(NOW()) AND DATE(A.JKEG_DTSAMPAI) <= DATE(NOW())";
		}
		
		
		$list_jadwal_kegiatan = $this->M_jadwal_kegiatan->list_jadwal_kegiatan_limit($cari," ORDER BY A.JKEG_DTINS DESC ",30,0);
		//$list_kat_pos = $this->M_kat_pos->list_kat_pos_limit('',100,0);
		//$list_prov = $this->M_lokasi->list_prov('',100,0);
		
		$list_kat_pos = "";
		$list_prov = "";
		
		$data = array('page_content'=>'jadwal_kegiatan','list_jadwal_kegiatan'=>$list_jadwal_kegiatan,'sidebar'=>1);
		$this->load->view('public/container.html',$data);
			
	}
	
	function simpan_lokasi_saya()
	{
		$lati = $_POST['lati'];
		$longi = $_POST['longi'];
		
		//echo $lati;
		
		$user = array(
			'ses_lati'  => $lati,
			'ses_longi'  => $longi
		);
		
		$this->session->set_userdata($user);
		echo $this->session->userdata('ses_lati');
	}
	
	public function tampilkan_kabkot()
	{
		$province_id = $_POST['POS_PROV'];
		$cari = " WHERE province_id = '".$province_id."'";
		$list_kabkot = $this->M_lokasi->list_kabkot($cari,1000,0);
		
		if (!empty($list_kabkot))
		{
			$list_result = $list_kabkot->result();
			foreach($list_result as $row)
			{
				echo '<option value="'.$row->id.'">'.$row->name.'</option>';
			}
		}
	}
	
	public function tampilkan_kec()
	{
		$kabkot_id = $_POST['POS_KAB'];
		$cari = " WHERE kabkot_id = '".$kabkot_id."'";
		$list_kec = $this->M_lokasi->list_kecamatan($cari,1000,0);
		if (!empty($list_kec))
		{
			$list_result = $list_kec->result();
			foreach($list_result as $row)
			{
				echo '<option value="'.$row->id.'">'.$row->name.'</option>';
			}
		}
	}
	
	public function tampilkan_desa()
	{
		$kec_id = $_POST['POS_KEC'];
		$cari = " WHERE kec_id = '".$kec_id."'";
		$list_desa = $this->M_lokasi->list_desa($cari,1000,0);
		if (!empty($list_desa))
		{
			$list_result = $list_desa->result();
			foreach($list_result as $row)
			{
				echo '<option value="'.$row->id.'">'.$row->name.'</option>';
			}
		}
	}
	
	public function arah()
	{
		$data = array('page_content'=>'direction','sidebar'=>1);
		$this->load->view('public/container.html',$data);
	}
	
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */