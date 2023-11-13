<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_public_pos extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		$this->load->model(array('M_pos','M_kat_pos','M_lokasi','M_images'));
		
	}
	
	public function index()
	{
		
		
		if((!empty($_GET['jenis'])) && ($_GET['jenis']!= "")  )
		{
			$jenis = $_GET['jenis'];
		}
		else
		{
			$jenis = "";
		}
		
		if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
		{
			if($jenis == '')
			{
				$cari = "WHERE 
						(
							KPOS_NAMA = 'Kotak Amal Baznas'
							OR POS_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'
							OR COALESCE(D.name,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
						)
						AND (A.KPOS_ID <> '' OR A.KPOS_ID IS NOT NULL)
						AND A.POS_LONGI <> 0 AND A.POS_LATI <> 0
					";
			}
			elseif($jenis == 'konter')
			{
				$cari = "WHERE 
						(
							KPOS_NAMA = 'Konter Ramadhan'
							OR POS_NAMA LIKE '%".str_replace("'","",$_GET['cari'])."%'
							OR COALESCE(D.name,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
						)
						AND (A.KPOS_ID <> '' OR A.KPOS_ID IS NOT NULL)
						AND A.POS_LONGI <> 0 AND A.POS_LATI <> 0
					";
			}
		}
		else
		{
			if($jenis == '')
			{
				$cari = " WHERE  KPOS_NAMA = 'Kotak Amal Baznas' AND (A.KPOS_ID <> '' OR A.KPOS_ID IS NOT NULL) AND A.POS_LONGI <> 0 AND A.POS_LATI <> 0 ";
			}
			elseif($jenis == 'konter')
			{
				$cari = " WHERE  KPOS_NAMA = 'Konter Ramadhan' AND (A.KPOS_ID <> '' OR A.KPOS_ID IS NOT NULL) AND A.POS_LONGI <> 0 AND A.POS_LATI <> 0 ";
			}
		}
		
		
		$list_pos = $this->M_pos->list_pos_limit($cari,100,0);
		//$list_kat_pos = $this->M_kat_pos->list_kat_pos_limit('',100,0);
		//$list_prov = $this->M_lokasi->list_prov('',100,0);
		
		$list_kat_pos = "";
		$list_prov = "";
		
		if($jenis == '')
		{
			$judul = "Pos Pelayanan Baznas";
			$subjudul = "Menampilkan lokasi - lokasi pos pelayanan BAZNAS";
		}
		elseif($jenis == 'konter')
		{
			$judul = "Konter Ramadhan BAZNAS Kab.Sukabumi";
			$subjudul = "Menampilkan lokasi - lokasi konter Ramadhan BAZNAS Kab.Sukabumi";
		}
		
		
		$data = array('page_content'=>'pos_pelayanan','list_pos'=>$list_pos,'list_kat_pos' => $list_kat_pos,'list_prov' => $list_prov,'sidebar'=>1,'judul' => $judul,'subjudul' => $subjudul );
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