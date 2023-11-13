<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_lap_neraca extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_log_aktifitas','M_gl_lap_neraca','M_gl_acc_buku_besar','M_gl_pst_akuntansi'));
		
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
				
				
				$msgbox_title = " Laporan Neraca Perusahaan";
				
				
				
				$data = array('page_content'=>'gl_admin_lap_neraca','msgbox_title' => $msgbox_title);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function get_kas()
	{
		/*
		if((!empty( $this->session->userdata('ses_kode_kantor') )) && (	$this->session->userdata('ses_kode_kantor') != "")  )
		{
			//$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
			$kode_kantor =  $this->session->userdata('ses_kode_kantor');
		}
		else
		{
			$kode_kantor = "PST";
		}
		*/
		
		/*
		$hasil_cek = $this->M_gl_lap_neraca->get_kas
													(
														$kode_kantor
														,$_POST['id_bank']
														,''
														,$_POST['cari']
														,$_POST['tgl_sampai']
													);
		*/
		
		if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
		{
			$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
		}
		else
		{
			$kode_kantor = $this->session->userdata('ses_kode_kantor');
		}
		
		$tgl_sampai = $_POST['tgl_sampai'];
		$cari = "WHERE COALESCE(CC.kode_akun,'') <> '' AND COALESCE(CC.target,'') IN ('KAS','KAS-BESAR')";
				
		$hasil_cek = $this->M_gl_acc_buku_besar->saldo_acc_buku_besar_kas_tambah_saldo_awal_per_pencarian($kode_kantor,$tgl_sampai,$cari);
		
		
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			//$hasil_cek = $hasil_cek->DEBIT - $hasil_cek->KREDIT;
			$hasil_cek = $hasil_cek->SALDO;
		}
		else
		{
			$hasil_cek = 0;
		}
		
		echo $hasil_cek;
	}
	
	function get_bank()
	{
		/*
		if((!empty( $this->session->userdata('ses_kode_kantor') )) && (	$this->session->userdata('ses_kode_kantor') != "")  )
		{
			//$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
			$kode_kantor =  $this->session->userdata('ses_kode_kantor');
		}
		else
		{
			$kode_kantor = "PST";
		}
		*/
		
		/*
		$hasil_cek = $this->M_gl_lap_neraca->get_kas
													(
														$kode_kantor
														,$_POST['id_bank']
														,''
														,$_POST['cari']
														,$_POST['tgl_sampai']
													);
		*/
		
		if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
		{
			$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
		}
		else
		{
			$kode_kantor = $this->session->userdata('ses_kode_kantor');
		}
		
		$tgl_sampai = $_POST['tgl_sampai'];
		$cari = "WHERE COALESCE(CC.kode_akun,'') <> '' AND COALESCE(CC.target,'') IN ('BANK')";
				
		$hasil_cek = $this->M_gl_acc_buku_besar->saldo_acc_buku_besar_kas_tambah_saldo_awal_per_pencarian($kode_kantor,$tgl_sampai,$cari);
		
		
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			//$hasil_cek = $hasil_cek->DEBIT - $hasil_cek->KREDIT;
			$hasil_cek = $hasil_cek->SALDO;
		}
		else
		{
			$hasil_cek = 0;
		}
		
		echo $hasil_cek;
	}
	
	function get_hutang_bank()
	{
		/*
		if((!empty( $this->session->userdata('ses_kode_kantor') )) && (	$this->session->userdata('ses_kode_kantor') != "")  )
		{
			//$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
			$kode_kantor =  $this->session->userdata('ses_kode_kantor');
		}
		else
		{
			$kode_kantor = "PST";
		}
		*/
		
		/*
		$hasil_cek = $this->M_gl_lap_neraca->get_kas
													(
														$kode_kantor
														,$_POST['id_bank']
														,''
														,$_POST['cari']
														,$_POST['tgl_sampai']
													);
		*/
		
		if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
		{
			$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
		}
		else
		{
			$kode_kantor = $this->session->userdata('ses_kode_kantor');
		}
		
		$tgl_sampai = $_POST['tgl_sampai'];
		$cari = "WHERE COALESCE(CC.kode_akun,'') <> '' AND COALESCE(CC.target,'') IN ('HUTANG-BANK')";
				
		$hasil_cek = $this->M_gl_acc_buku_besar->saldo_acc_buku_besar_kas_tambah_saldo_awal_per_pencarian($kode_kantor,$tgl_sampai,$cari);
		
		
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			//$hasil_cek = $hasil_cek->DEBIT - $hasil_cek->KREDIT;
			$hasil_cek = abs($hasil_cek->SALDO);
		}
		else
		{
			$hasil_cek = 0;
		}
		
		echo $hasil_cek;
	}
	
	function get_assets()
	{
		
		if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
		{
			$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
		}
		else
		{
			$kode_kantor = $this->session->userdata('ses_kode_kantor');
		}
		
		$tgl_sampai = $_POST['tgl_sampai'];
				
		$hasil_cek = $this->M_gl_lap_neraca->get_assets($kode_kantor,$tgl_sampai);
		
		
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			//$hasil_cek = $hasil_cek->DEBIT - $hasil_cek->KREDIT;
			$hasil_cek = $hasil_cek->SETELAH_PENYUSUTAN;
		}
		else
		{
			$hasil_cek = 0;
		}
		
		echo $hasil_cek;
	}
	
	function get_laba_rugi()
	{
		if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
		{
			$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
		}
		else
		{
			$kode_kantor = $this->session->userdata('ses_kode_kantor');
		}
		
		$tgl_sampai = $_POST['tgl_sampai'];
				
		//$hasil_cek = $this->M_gl_lap_neraca->get_assets($kode_kantor,$tgl_sampai);
		$list_laba_rugi = $this->M_gl_pst_akuntansi->list_laba_rugi($kode_kantor,substr('2021-09-09',0,-6).'-01-01',$tgl_sampai);
		if(!empty($list_laba_rugi))
		{
			$list_field = $list_laba_rugi->field_data();
		}
		else
		{
			$list_field = false;
		}
				
		if(!empty($list_laba_rugi))
		{
		
			$list_result = $list_laba_rugi->result_array();
			$num_col=0;
			$toal_col = count($list_field);
			foreach($list_field as $field)
			{
				if($num_col>=1)
				{
					$str_data = str_replace("1","",$field->name);
				}
				$num_col++;
			}
				
				
				$no = 1;
				foreach($list_result as $row)
				{
					if($row['OPCD'] == "4.LABA")
					{
						$no = 0;
					}
					elseif($row['OPCD'] == "ZTOTAL BIAYA")
					{
						$no = 0;
					}
					elseif($row['OPCD'] == "ZZGRAND TOTAL")
					{
						$no = 0;
					}
					else
					{
					}
					
					$num_col=0;
					$subtotal = 0;
					$grandtotal =0;
					foreach($list_field as $field)
					{
						
						if($num_col>=1)
						{
							
							//if($row[$field->name] > 0)
							
							$strIdSatuan = str_replace("1","",$field->name);
							if($row[$field->name] <> "-")
							{
								$harga_jual = $row[$field->name];
								$subtotal = $subtotal + $row[$field->name];
								
								$grandtotal = $grandtotal + $subtotal;
							}
							else
							{
								$harga_jual = "0";
								$subtotal = $subtotal + 0;
							}
							
							if($harga_jual <> 0)
							{
							}
							else
							{
							}
							
						}
						
						if($num_col == ($toal_col - 1))
						{
							$subtotal = 0;
							$num_col = 0;
						}
						$num_col++;
					}
					$no++;
				}
			//echo number_format($grandtotal,0,'.',',');
			$hasil_cek = ROUND($grandtotal,1);
		}
		else
		{
			$hasil_cek = 0;
		}
		
		echo $hasil_cek;
	}
	
	function get_stock_persediaan()
	{
		/*
		if((!empty( $this->session->userdata('ses_kode_kantor') )) && (	$this->session->userdata('ses_kode_kantor') != "")  )
		{
			//$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
			$kode_kantor =  $this->session->userdata('ses_kode_kantor');
		}
		else
		{
			$kode_kantor = "PST";
		}
		*/
		
		if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
		{
			$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
		}
		else
		{
			$kode_kantor = $this->session->userdata('ses_kode_kantor');
		}
		
		$hasil_cek = $this->M_gl_lap_neraca->get_persediaan
													(
														$kode_kantor
														,''
														,$_POST['tgl_sampai']
														,'23'
														,'59'
													);
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			$hasil_cek = $hasil_cek->NOMINAL_STOCK;
		}
		else
		{
			$hasil_cek = 0;
		}
		
		echo $hasil_cek;
	}
	
	function get_piutang_awal_tr()
	{
		/*
		if((!empty( $this->session->userdata('ses_kode_kantor') )) && (	$this->session->userdata('ses_kode_kantor') != "")  )
		{
			//$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
			$kode_kantor =  $this->session->userdata('ses_kode_kantor');
		}
		else
		{
			$kode_kantor = "PST";
		}
		
		$hasil_cek = $this->M_gl_lap_neraca->get_piutang_awal_tr
													(
														$kode_kantor
														,$_POST['tgl_sampai']
														,'1990-01-01 00:00:00'
													);
		*/
		
		if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
		{
			$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
		}
		else
		{
			$kode_kantor = $this->session->userdata('ses_kode_kantor');
		}
		$cari = "
			WHERE A.kode_kantor = '".$kode_kantor."' 
			AND (COALESCE(B.SISA,0) + (A.piutang_awal - COALESCE(C.bayar_piutang_awal,0))) > 0
			
		";
		$tgl_sampai = $_POST['tgl_sampai'];
		
		$hasil_cek = $this->M_gl_lap_neraca->saldo_piutang_pelanggan_disamakan($cari,$tgl_sampai);
		
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			$hasil_cek = $hasil_cek->SISA_AWAL ."|".$hasil_cek->SISA_TR;
		}
		else
		{
			$hasil_cek = "0|0";
		}
		
		echo $hasil_cek;
	}
	
	function get_hutang_awal()
	{
		/*
		if((!empty( $this->session->userdata('ses_kode_kantor') )) && (	$this->session->userdata('ses_kode_kantor') != "")  )
		{
			//$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
			$kode_kantor =  $this->session->userdata('ses_kode_kantor');
		}
		else
		{
			$kode_kantor = "PST";
		}
		
		$hasil_cek = $this->M_gl_lap_neraca->get_hutang_awal
													(
														$kode_kantor
														,$_POST['tgl_sampai']
													);
		*/
		
		if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
		{
			$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
		}
		else
		{
			$kode_kantor = $this->session->userdata('ses_kode_kantor');
		}
		$cari = "
			WHERE A.kode_kantor = '".$kode_kantor."'	
		";
		$tgl_sampai = $_POST['tgl_sampai'];
		
		$hasil_cek = $this->M_gl_lap_neraca->saldo_akumulasi_hutang_per_supplier_disamakan($cari,$tgl_sampai);
		
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			$hasil_cek = $hasil_cek->SISA_AWAL ."|".$hasil_cek->SISA_TR;
		}
		else
		{
			$hasil_cek = "0";
		}
		
		echo $hasil_cek;
	}
	
	function get_saldo_assets()
	{
		if((!empty($_POST['kode_kantor'])) && ($_POST['kode_kantor']!= "")  )
		{
			$kode_kantor = $_POST['kode_kantor'];
		}
		else
		{
			$kode_kantor = "PST";
		}
		
		if((!empty($_POST['kategori'])) && ($_POST['kategori']!= "")  )
		{
			$kategori = $_POST['kategori'];
		}
		else
		{
			$kategori = "";
		}
		
		$hasil_cek = $this->M_gl_lap_neraca->sum_assets_by_aktegori
													(
														$kode_kantor
														,$kategori
													);
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			$hasil_cek = $hasil_cek->ASSET;
		}
		else
		{
			$hasil_cek = "0";
		}
		
		echo $hasil_cek;
	}
	
	function get_hutang_tr()
	{
		/*
		if((!empty( $this->session->userdata('ses_kode_kantor') )) && (	$this->session->userdata('ses_kode_kantor') != "")  )
		{
			//$kode_kantor = str_replace("'","",$_GET['kode_kantor']);
			$kode_kantor =  $this->session->userdata('ses_kode_kantor');
		}
		else
		{
			$kode_kantor = "PST";
		}
		*/
		$kode_kantor = "PST";
		$tgl_awal = '1900-01-01';
		$nominal_awal = 0;
			
		$get_data_hutang_awal = $this->M_gl_lap_neraca->get_hutang_awal_2($kode_kantor,$_POST['tgl_sampai']);
		if(!empty($get_data_hutang_awal))
		{
			$get_data_hutang_awal = $get_data_hutang_awal->row();
			$tgl_awal = $get_data_hutang_awal->tgl_uang_masuk;
			$nominal_awal = $get_data_hutang_awal->nominal;
		}
		else
		{
			$tgl_awal = '1900-01-01';
			$nominal_awal = 0;
		}
		
		
		$hasil_cek = $this->M_gl_lap_neraca->get_hutang_tr_by_inv
													(
														$kode_kantor
														,$tgl_awal
														,$nominal_awal
														,$_POST['tgl_sampai']
														,'1990-01-01 00:00:00'
													);
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			$hasil_cek = $hasil_cek->HUTANG;
		}
		else
		{
			$hasil_cek = "0";
		}
		
		
		
		echo $hasil_cek;
		//echo '<br/>'.$tgl_awal;
		//echo '<br/>'.$nominal_awal;
	}
	
	
	function get_saldo_kas_per_target()
	{
		if((!empty($_POST['kode_kantor'])) && ($_POST['kode_kantor']!= "")  )
		{
			$kode_kantor = $_POST['kode_kantor'];
		}
		else
		{
			$kode_kantor = "";
		}
		
		$target = 'PENJUALAN LAIN';
		//$tgl_awal = '1900-01-01';
		$nominal_awal = 0;
			
		$get_saldo_awal_target = $this->M_gl_lap_neraca->get_kas_per_target
		(
			$kode_kantor
			,$target
			,$_POST['tgl_sampai']
			,$_POST['tgl_sampai']
		);
		//get_hutang_awal_2($kode_kantor,$_POST['tgl_sampai']);
		if(!empty($get_saldo_awal_target))
		{
			$get_saldo_awal_target = $get_saldo_awal_target->row();
			$get_saldo_awal_target = $get_saldo_awal_target->SALDO;
			$saldo = $get_saldo_awal_target;
		}
		else
		{
			$saldo = 0;
		}
		
		
		echo $saldo;
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_lap_neraca.php */