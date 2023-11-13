<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_pst_admin_uang_keluar extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_pst_uang_keluar','M_gl_bank','M_gl_kode_akun','M_gl_pengaturan','M_gl_supplier'));
		
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

				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				} else {
					$kode_kantor = '';
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = 
						"
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND DATE(A.tgl_dikeluarkan) BETWEEN '".$dari."' AND '".$sampai."'
							AND A.id_kat_uang_keluar2 = '' -- Hanya menampilkan uang keluar induk
							AND (
									A.no_uang_keluar LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR A.nama_uang_keluar LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR A.diberikan_kepada LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR A.untuk LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
						";
				}
				else
				{
					$cari = 
						"
							WHERE A.kode_kantor = '".$kode_kantor."'
							AND DATE(A.tgl_dikeluarkan) BETWEEN '".$dari."' AND '".$sampai."'
							AND A.id_kat_uang_keluar2 = '' -- Hanya menampilkan uang keluar induk
						";
				}
				
				$order_by = " ORDER BY A.id_induk_uang_keluar ASC,A.tgl_ins ASC ";
				
				$jum_row = $this->M_gl_pst_uang_keluar->count_uang_keluar_limit($cari)->JUMLAH;
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-pusat-admin-pengeluaran?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-pusat-admin-pengeluaran/');
				$config['total_rows'] = $jum_row;
				$config['uri_segment'] = 2;	
				$config['per_page'] = 30;
				$config['num_links'] = 2;
				$config['suffix'] = '?' . http_build_query($_GET, '', "&");
				//$config['use_page_numbers'] = TRUE;
				//$config['page_query_string'] = false;
				//$config['query_string_segment'] = '';
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
				
				//DATA BANK
				$cari_bank = " WHERE kode_kantor = '".$kode_kantor."' ";
				$list_bank = $this->M_gl_bank->list_bank_limit($cari_bank,1000,0);
				//DATA BANK
				
				//DAPATKAN NO UANG KELUAR
				/*
				$get_no_uang_keluar = $this->M_gl_pst_uang_keluar->get_no_uang_keluar($kode_kantor);
				if(!empty($get_no_uang_keluar))
				{
					$get_no_uang_keluar = $get_no_uang_keluar->row();
					$get_no_uang_keluar = $get_no_uang_keluar->no_uang_keluar;
				}
				else
				{
					$get_no_uang_keluar = "";
				}
				*/
				//DAPATKAN NO UANG KELUAR
					
				
				$list_uang_keluar = $this->M_gl_pst_uang_keluar->list_uang_keluar_limit_hanya_uang_keluar_induk($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				
				//UNTUK AKUMULASI INFO
					if($config['per_page'] > $jum_row)
					{
						$jum_row_tampil = $jum_row;
					}
					else
					{
						$jum_row_tampil = $config['per_page'];
					}
					
					$offset = (integer) $this->uri->segment(2,0);
					$max_data = $offset + $jum_row_tampil;
					$offset = $offset + 1;
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$sum_pesan = "Menampilkan ".$jum_row_tampil." Dari ".$jum_row." Data Pencarian ".str_replace("'","",$_GET['cari'])." dimulai dari data ke ".$offset." Sampai ".$max_data;
					}
					else
					{
						$sum_pesan = "Menampilkan ".$jum_row_tampil." Dari ".$jum_row." Data dimulai dari data ke ".$offset." Sampai ".$max_data;
					}
				//UNTUK AKUMULASI INFO
				
				//SUMAARY LIST
					// $sum_uang_keluar = $this->M_gl_pst_uang_keluar->sum_uang_keluar_limit($cari);
					// if(!empty($sum_uang_keluar))
					// {
						// $sum_uang_keluar = $sum_uang_keluar->row();
						// $sum_uang_keluar = $sum_uang_keluar->NOMINAL;
					// }
					// else
					// {
						$sum_uang_keluar = 0;
					//}
				//SUMAARY LIST
				
				$msgbox_title = " Jurnal Pengeluaran";
				
				
				
				// $cari_coa = " 
				// 			WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
				// 		";
				
				
				// $order_by_coa = " ORDER BY nama_kode_akun ASC ";
				// $list_kode_akun = $this->M_gl_kode_akun->list_kode_akun_limit_order_by($cari_coa,$order_by_coa,1000,0);
				$list_kantor = $this->M_gl_pengaturan->get_data_kantor("")->result();
				
				$data = array('page_content'=>'c_gl_akuntansi/gl_admin_uang_keluar',
							  'halaman'=>$halaman,
							  'list_uang_keluar'=>$list_uang_keluar,
							  'msgbox_title' => $msgbox_title,
							  'sum_pesan' => $sum_pesan,
							  'list_bank'=>$list_bank,
							  //'get_no_uang_keluar'=>$get_no_uang_keluar,
							  'sum_uang_keluar'=>$sum_uang_keluar,
							  //'list_kode_akun' => $list_kode_akun
							  'list_kantor' => $list_kantor,
								);
				$this->load->view('pusat/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	
	function get_no_uang_keluar_percab_jurnal_umum()
	{
		//DAPATKAN NO UANG KELUAR JURNAL UMUM, KODE JU
		$get_no_uang_keluar = $this->M_gl_pst_uang_keluar->get_no_uang_keluar_jurnal_umum($_POST['kode_kantor']);
		if(!empty($get_no_uang_keluar))
		{
			$get_no_uang_keluar = $get_no_uang_keluar->row();
			$get_no_uang_keluar = $get_no_uang_keluar->no_uang_keluar;
		}
		else
		{
			$get_no_uang_keluar = "";
		}
		echo $get_no_uang_keluar;
		//DAPATKAN NO UANG KELUAR JURNAL UMUM, KODE JU
	}
	
	function get_no_uang_keluar_percab()
	{
		//DAPATKAN NO UANG KELUAR JURNAL UMUM, KODE JU
		$get_no_uang_keluar = $this->M_gl_pst_uang_keluar->get_no_uang_keluar($_POST['kode_kantor']);
		if(!empty($get_no_uang_keluar))
		{
			$get_no_uang_keluar = $get_no_uang_keluar->row();
			$get_no_uang_keluar = $get_no_uang_keluar->no_uang_keluar;
		}
		else
		{
			$get_no_uang_keluar = "";
		}
		echo $get_no_uang_keluar;
		//DAPATKAN NO UANG KELUAR JURNAL UMUM, KODE JU
	}
	
	public function list_kode_akun()
	{
		$cari_coa = "WHERE kode_kantor = '".$this->input->post('kode_kantor')."'";
		$order_by_coa = " ORDER BY nama_kode_akun ASC ";
		$list_kode_akun = $this->M_gl_kode_akun->list_kode_akun_limit_order_by($cari_coa,$order_by_coa,1000,0);

		echo '<option value=""></option>';
									
		if(!empty($list_kode_akun))
		{
			$list_result = $list_kode_akun->result();
			foreach($list_result as $row)
			{
				echo'<option value="'.$row->id_kode_akun.'">'.$row->nama_kode_akun.' ('.$row->kode_akun.')</option>';
			}
		}
	}
	
	public function list_kode_akun_supplier()
	{
		//MENAMPILKAN DATA SUPPLIER
			$cari_supplier = " WHERE kode_kantor = '".$_POST['kode_kantor']."' AND target = 'PEMBELIAN'";
			$list_supllier = $this->M_gl_kode_akun->list_kode_akun_limit_order_by($cari_supplier," ORDER BY kode_akun ASC",10000,0);
		//MENAMPILKAN DATA SUPPLIER
		
		echo '<option value=""></option>';
		if(!empty($list_supllier))
		{
			$list_result = $list_supllier->result();
			foreach($list_result as $row)
			{
				echo'<option value="'.$row->id_supplier.'-'.$row->id_kode_akun.'">'.$row->nama_kode_akun.' ('.$row->kode_akun.')</option>';
			}
		}
	}
	
	public function list_supplier()
	{
		//MENAMPILKAN DATA SUPPLIER
			$cari_supplier = " WHERE kode_kantor = '".$_POST['kode_kantor']."' ";
			$list_supllier = $this->M_gl_supplier->list_hanya_supplier_limit($cari_supplier,10000,0);
		//MENAMPILKAN DATA SUPPLIER
		
		echo '<option value=""></option>';
		if(!empty($list_supllier))
		{
			$list_result = $list_supllier->result();
			foreach($list_result as $row)
			{
				echo'<option value="'.$row->id_supplier.'">'.$row->nama_supplier.' ('.$row->kode_supplier.')</option>';
			}
		}
	}
	
	public function tambah_biaya()
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
				$id_uang_keluar = $this->uri->segment(2,0);
				$cari_pengeluaran = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_uang_keluar) = '".$id_uang_keluar."'";
				$get_data_uang_keluar = $this->M_gl_pst_uang_keluar->list_uang_keluar_limit($cari_pengeluaran,"",1,0);
				if(!empty($get_data_uang_keluar))
				{
					$get_data_uang_keluar = $get_data_uang_keluar->row();
					
					
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = 
							"
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND MD5(A.id_induk_uang_keluar) = '".$id_uang_keluar."'
								AND MD5(A.id_uang_keluar) <> '".$id_uang_keluar."'
								AND (
										A.no_uang_keluar LIKE '%".str_replace("'","",$_GET['cari'])."%' 
										OR A.nama_uang_keluar LIKE '%".str_replace("'","",$_GET['cari'])."%'
										OR A.diberikan_kepada LIKE '%".str_replace("'","",$_GET['cari'])."%'
										OR A.untuk LIKE '%".str_replace("'","",$_GET['cari'])."%'
									)
							";
					}
					else
					{
						$cari = 
							"
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
								AND MD5(A.id_induk_uang_keluar) = '".$id_uang_keluar."'
								AND MD5(A.id_uang_keluar) <> '".$id_uang_keluar."'
							";
					}
					
					$order_by = " ORDER BY A.tgl_ins DESC ";
					
					$jum_row = $this->M_gl_pst_uang_keluar->count_uang_keluar_limit($cari)->JUMLAH;
					
					$this->load->library('pagination');
					//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
					//$config['base_url'] = base_url().'admin/jabatan/';
					
					$config['first_url'] = site_url('gl-pusat-admin-pengeluaran-tambah/'.$id_uang_keluar.'??'.http_build_query($_GET));
					$config['base_url'] = site_url('gl-pusat-admin-pengeluaran-tambah/'.$id_uang_keluar);
					$config['total_rows'] = $jum_row;
					$config['uri_segment'] = 3;	
					$config['per_page'] = 30;
					$config['num_links'] = 2;
					$config['suffix'] = '?' . http_build_query($_GET, '', "&");
					//$config['use_page_numbers'] = TRUE;
					//$config['page_query_string'] = false;
					//$config['query_string_segment'] = '';
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
					
					//DATA BANK
					$cari_bank = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
					$list_bank = $this->M_gl_bank->list_bank_limit($cari_bank,1000,0);
					//DATA BANK
					
					//DAPATKAN NO UANG KELUAR
					$get_no_uang_keluar = $this->M_gl_pst_uang_keluar->get_no_uang_keluar($this->session->userdata('ses_kode_kantor'));
					if(!empty($get_no_uang_keluar))
					{
						$get_no_uang_keluar = $get_no_uang_keluar->row();
						$get_no_uang_keluar = $get_no_uang_keluar->no_uang_keluar;
					}
					else
					{
						$get_no_uang_keluar = "";
					}
					//DAPATKAN NO UANG KELUAR
						
					
					$list_uang_keluar = $this->M_gl_pst_uang_keluar->list_uang_keluar_limit($cari,$order_by,$config['per_page'],$this->uri->segment(3,0));
					
					
					//UNTUK AKUMULASI INFO
						if($config['per_page'] > $jum_row)
						{
							$jum_row_tampil = $jum_row;
						}
						else
						{
							$jum_row_tampil = $config['per_page'];
						}
						
						$offset = (integer) $this->uri->segment(3,0);
						$max_data = $offset + $jum_row_tampil;
						$offset = $offset + 1;
						if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
						{
							$sum_pesan = "Menampilkan ".$jum_row_tampil." Dari ".$jum_row." Data Pencarian ".str_replace("'","",$_GET['cari'])." dimulai dari data ke ".$offset." Sampai ".$max_data;
						}
						else
						{
							$sum_pesan = "Menampilkan ".$jum_row_tampil." Dari ".$jum_row." Data dimulai dari data ke ".$offset." Sampai ".$max_data;
						}
					//UNTUK AKUMULASI INFO
					
					//SUMAARY LIST
						// $sum_uang_keluar = $this->M_gl_pst_uang_keluar->sum_uang_keluar_limit($cari);
						// if(!empty($sum_uang_keluar))
						// {
							// $sum_uang_keluar = $sum_uang_keluar->row();
							// $sum_uang_keluar = $sum_uang_keluar->NOMINAL;
						// }
						// else
						// {
							$sum_uang_keluar = 0;
						//}
					//SUMAARY LIST
					
					$msgbox_title = " Rincian Pengeluaran";
					
					$data = array('page_content'=>'gl_admin_uang_keluar_tambah','halaman'=>$halaman,'get_data_uang_keluar' => $get_data_uang_keluar,'list_uang_keluar'=>$list_uang_keluar,'msgbox_title' => $msgbox_title,'sum_pesan' => $sum_pesan,'list_bank'=>$list_bank,'get_no_uang_keluar'=>$get_no_uang_keluar,'sum_uang_keluar'=>$sum_uang_keluar);
					$this->load->view('pusat/container',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-pusat-admin-pengeluaran');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function tampilkan_list_hutang_by_uang_masuk()
	{
		$kode_kantor = $_POST['kode_kantor'];
		$id_supplier = $_POST['id_supplier'];
		$id_induk_uang_keluar = $_POST['id_induk_uang_keluar'];
		
		$list_hutang = $this->M_gl_pst_uang_keluar->list_hutang_by_supp_uang_masuk_limit($kode_kantor,$id_supplier,$id_induk_uang_keluar);
		
		if(!empty($list_hutang))
		{
			
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead style="background-color:green;">
<tr>';
							
							echo '<th style="border:1px black solid;" width="25%">TGL</th>';
							echo '<th style="border:1px black solid;" width="25%">INV</th>';
							echo '<th style="border:1px black solid;" width="10%">AWAL</th>';
							echo '<th style="border:1px black solid;" width="10%">T.BAYAR</th>';
							echo '<th style="border:1px black solid;" width="15%">BAYAR</th>';
							echo '<th style="border:1px black solid;" width="15%">SISA</th>';
							//echo '<th style="border:1px black solid;" width="10%">BIAYA</th>';
							//echo '<th style="border:1px black solid;" width="10%">PENDPAATAN</th>';
				echo '</tr>
</thead>';
				$list_result = $list_hutang->result();
				$no =$this->uri->segment(3,0)+1;
				$sub_total = 0;
				echo '<tbody>';
				foreach($list_result as $row)
				{
				
					
						echo'<tr>';
						echo'<td rowspan="1" style="border:1px black solid;">'.$row->TGL_T.'</td>';
						echo'<td rowspan="1" style="border:1px black solid;">'.$row->no_surat_jalan.'</td>';
						echo'<td style="text-align:right;font-weight:normal;border:1px black solid;">
								<span id="awal-'.$row->id_h_penerimaan.'">'.number_format($row->NOMINAL,0,'.',',').'</span>
							</td>';
						echo'<td style="text-align:right;font-weight:normal;border:1px black solid;">
								<span id="tlh_bayar-'.$row->id_h_penerimaan.'">'.number_format($row->TLH_BAYAR,0,'.',',').'</span>
							</td>';
						echo'<td style="text-align:right;font-weight:normal;border:1px black solid;">
								
								<input type="text" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" maxlength="10" size="10" id="cur_bayar-'.$row->id_h_penerimaan.'" value="'.number_format($row->CUR_BAYAR,0,'.',',').'" onchange="bayar(this);" style="background-color:green;color:white;text-align:right;" />
								
							</td>';
						echo'<td style="text-align:right;font-weight:normal;border:1px black solid;"><span id="sisa-'.$row->id_h_penerimaan.'">'.number_format($row->SISA,0,'.',',').'</span></td>';
						
						
						echo'
							<!-- <td style="text-align:right;font-weight:normal;border:1px black solid;"> -->
						
								<input type="hidden" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" maxlength="10" size="10" id="cur_biaya-'.$row->id_h_penerimaan.'" value="'.number_format($row->CUR_BIAYA,0,'.',',').'" onchange="bayar(this);" style="background-color:green;color:white;text-align:right;" />
								
							<!-- </td> -->';
						echo'
							<!-- <td style="text-align:right;font-weight:normal;border:1px black solid;"> -->
								<input type="hidden" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" maxlength="10" size="10" id="cur_pendapatan-'.$row->id_h_penerimaan.'" value="'.number_format($row->CUR_PENDAPATAN,0,'.',',').'" onchange="bayar(this);" style="background-color:green;color:white;text-align:right;" />
							<!-- </td> -->';
						
						
					echo'</tr>';
					
					
						
						
						echo'<input type="hidden" id="id_uang_keluar_'.$no.'" name="id_uang_keluar_'.$no.'" value="'.$row->id_h_penerimaan.'" />';
						echo'<input type="hidden" id="no_invoice-'.$row->id_h_penerimaan.'" name="no_invoice-'.$row->id_h_penerimaan.'" value="'.$row->no_surat_jalan.'" />';
						
					$no++;
					$sub_total = $sub_total + $row->SISA;
					//sum_uang_keluar
				}
				
				echo'<tr>';
					echo'<td colspan="5" style="text-align:center;font-weight:bold;border:1px black solid;">TOTAL</td>';
					echo'<td  style="text-align:right;font-weight:bold;border:1px black solid;">'.number_format($sub_total,0,'.',',').'</td>';
					//echo'<td  style="border:1px black solid;" colspan="2"></td>';
				echo'</tr>';
				
				echo '</tbody>';
			echo'</table>';
		}
		else
		{
			echo'<center>';
			echo'Tidak Ada Data Yang Ditampilkan !';
			echo'</center>';
		}
	//TABLE
		
		
	}
	
	
	function tampilkan_pengeluaran_tambah()
	{
		$kode_kantor = $this->input->post('kode_kantor');
		$cari = 
				"
					WHERE A.kode_kantor = '".$kode_kantor."'
					AND (A.id_induk_uang_keluar) = '".$_POST['id_uang_keluar']."'
					AND (A.id_uang_keluar) <> '".$_POST['id_uang_keluar']."'
				";
		
		$order_by = " ORDER BY A.tgl_ins DESC ";
					
		$list_uang_keluar = $this->M_gl_pst_uang_keluar->list_uang_keluar_limit($cari,$order_by,1000,0);
		
		if(!empty($list_uang_keluar))
		{
			
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead style="background-color:green;">
<tr>';
							
							echo '<th style="border:1px black solid;" width="20%">ACCOUNT</th>';
							echo '<th style="border:1px black solid;text-align:center;" colspan="2" width="45%">NAMA AKUN  DAN KETERANGAN</th>';
							echo '<th style="border:1px black solid;" width="20%">NOMINAL</th>';
							echo '<th style="border:1px black solid;" width="15%">AKSI</th>';
				echo '</tr>
</thead>';
				$list_result = $list_uang_keluar->result();
				$no =$this->uri->segment(3,0)+1;
				$sub_total = 0;
				echo '<tbody>';
				foreach($list_result as $row)
				{
				
					
						echo'<tr>';
						echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN2.'</td>';
						//echo'<td style="border:1px black solid;" colspan="2">'.$row->NAMA_AKUN2.'<br/><i>'.$row->ket_uang_keluar.'</i></td>';
						echo'<td style="border:1px black solid;" colspan="2">'.$row->ket_uang_keluar.'</td>';
						
						//echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN2.'</td>';
						//echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN.'</td>';
						echo'<td style="text-align:right;font-weight:normal;border:1px black solid;">'.number_format($row->nominal,0,'.',',').'</td>';
						//echo'<td style="border:1px black solid;"></td>';
						echo'<td rowspan="1" style="border:1px black solid;">
						


<a class="confirm-btn" href="javascript:void(0)" onclick="hapus_debet(this)" id="'.$no.'-'.$row->id_uang_keluar.'" title = "Hapus Data '.$row->nama_uang_keluar.'" alt = "Hapus Data '.$row->nama_uang_keluar.'">
<i class="fa fa-trash"></i> HAPUS
</a>
						</td>';
						
					echo'</tr>';
					
					
						
						
						echo'<input type="hidden" id="id_uang_keluar_'.$no.'" name="id_uang_keluar_'.$no.'" value="'.$row->id_uang_keluar.'" />';
						
					$no++;
					$sub_total = $sub_total + $row->nominal;
					//sum_uang_keluar
				}
				
				echo'<tr>';
					echo'<td colspan="3" style="text-align:center;font-weight:bold;border:1px black solid;">TOTAL</td>';
					echo'<td  style="text-align:right;font-weight:bold;border:1px black solid;">'.number_format($sub_total,0,'.',',').'</td>';
					echo'<td  style="border:1px black solid;" colspan="2"></td>';
				echo'</tr>';
				
				echo '</tbody>';
			echo'</table>';
		}
		else
		{
			echo'<center>';
			echo'Tidak Ada Data Yang Ditampilkan !';
			echo'</center>';
		}
	//TABLE
		
		
	}
	
	public function print_bukti_uang_keluar()
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
				$id_uang_keluar = $this->uri->segment(2,0);
				$kode_kantor = $this->uri->segment(3,0);
				$cari_pengeluaran = "WHERE A.kode_kantor = '".$kode_kantor."' AND (A.id_uang_keluar) = '".$id_uang_keluar."'";
				$get_data_uang_keluar = $this->M_gl_pst_uang_keluar->list_uang_keluar_limit($cari_pengeluaran,"",1,0);
				if(!empty($get_data_uang_keluar))
				{
					$get_data_uang_keluar = $get_data_uang_keluar->row();
					
					
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = 
							"
								WHERE A.kode_kantor = '".$kode_kantor."' 
								AND (A.id_induk_uang_keluar) = '".$id_uang_keluar."'
								AND (A.id_uang_keluar) <> '".$id_uang_keluar."'
								AND (
										A.no_uang_keluar LIKE '%".str_replace("'","",$_GET['cari'])."%' 
										OR A.nama_uang_keluar LIKE '%".str_replace("'","",$_GET['cari'])."%'
										OR A.diberikan_kepada LIKE '%".str_replace("'","",$_GET['cari'])."%'
										OR A.untuk LIKE '%".str_replace("'","",$_GET['cari'])."%'
									)
							";
					}
					else
					{
						$cari = 
							"
								WHERE A.kode_kantor = '".$kode_kantor."'
								AND (A.id_induk_uang_keluar) = '".$id_uang_keluar."'
								AND (A.id_uang_keluar) <> '".$id_uang_keluar."'
							";
					}
					
					$order_by = " ORDER BY A.tgl_ins DESC ";
					
					
					$list_uang_keluar = $this->M_gl_pst_uang_keluar->list_uang_keluar_limit($cari,$order_by,100,0);
					
					
					
					
					$msgbox_title = " Rincian Pengeluaran";
					
					$data = array('page_content'=>'gl_admin_uang_keluar_tambah','get_data_uang_keluar' => $get_data_uang_keluar,'list_uang_keluar'=>$list_uang_keluar);
					
					//$this->load->view('pusat/container',$data);
					$this->load->view('pusat/page/c_gl_akuntansi/gl_admin_print_bukti_uang_keluar.html',$data);
				}
				else
				{
					header('Location: '.base_url().'gl-pusat-admin-pengeluaran');
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_akun_coa()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "
					WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND target = ''
					AND (
							kode_akun LIKE '%".str_replace("'","",$_POST['cari'])."%' 
							OR nama_kode_akun LIKE '%".str_replace("'","",$_POST['cari'])."%'
						)";
		}
		else
		{
			$cari = " 
						WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND target = ''
					";
		}
		
		$list_kode_akun = $this->M_gl_kode_akun->list_kode_akun_limit($cari,$_POST['limit'],$_POST['offset']);
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_kode_akun))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							//echo '<th width="10%">FOTO</th>';
							echo '<th width="65%">DATA ASSETS</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_kode_akun->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_produk-'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						echo'<td>
								<b>KODE : </b>'.$row->kode_akun.' 
								<br/> <b>NAMA : </b>'.$row->nama_kode_akun.' 
								<br/> <b>KETERANGAN : </b>'.$row->ket_kode_akun.'
							</td>';
						echo'<td>
								<button type="button" onclick="insert_akun('.$no.')" class="btn btn-primary btn-sm"  data-dismiss="modal" >Pilih</button>
							</td>';
							
						echo'<input type="hidden" id="get_tr_1_'.$no.'" name="get_tr_1_'.$no.'" value="tr_list_produk-'.$no.'" />';
						echo'<input type="hidden" id="id_kode_akun_1_'.$no.'" name="id_kode_akun_1_'.$no.'" value="'.$row->id_kode_akun.'" />';
						echo'<input type="hidden" id="kode_akun_1_'.$no.'" name="kode_akun_1_'.$no.'" value="'.$row->kode_akun.'" />';
						echo'<input type="hidden" id="nama_kode_akun_1_'.$no.'" name="nama_kode_akun_1_'.$no.'" value="'.$row->nama_kode_akun.'" />';
					echo'</tr>';
					$no++;
				}
				
				echo '</tbody>';
			echo'</table>';
			echo'</div>';
		}
		else
		{
			echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}
	
	
	function list_akun_coa2()
	{	
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "
					WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND target = ''
					AND (
							kode_akun LIKE '%".str_replace("'","",$_POST['cari'])."%' 
							OR nama_kode_akun LIKE '%".str_replace("'","",$_POST['cari'])."%'
						)";
		}
		else
		{
			$cari = " 
						WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND target = ''
					";
		}
		
		$list_kode_akun = $this->M_gl_kode_akun->list_kode_akun_limit($cari,$_POST['limit'],$_POST['offset']);
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_kode_akun))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">NO</th>';
							//echo '<th width="10%">FOTO</th>';
							echo '<th width="65%">DATA ASSETS</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_kode_akun->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_produk-'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						echo'<td>
								<b>KODE : </b>'.$row->kode_akun.' 
								<br/> <b>NAMA : </b>'.$row->nama_kode_akun.' 
								<br/> <b>KETERANGAN : </b>'.$row->ket_kode_akun.'
							</td>';
						echo'<td>
								<button type="button" onclick="insert_akun2('.$no.')" class="btn btn-primary btn-sm"  data-dismiss="modal" >Pilih</button>
							</td>';
							
						echo'<input type="hidden" id="get_tr_2_'.$no.'" name="get_tr_2_'.$no.'" value="tr_list_produk-'.$no.'" />';
						echo'<input type="hidden" id="id_kode_akun_2_'.$no.'" name="id_kode_akun_2_'.$no.'" value="'.$row->id_kode_akun.'" />';
						echo'<input type="hidden" id="kode_akun_2_'.$no.'" name="kode_akun_2_'.$no.'" value="'.$row->kode_akun.'" />';
						echo'<input type="hidden" id="nama_kode_akun_2_'.$no.'" name="nama_kode_akun_2_'.$no.'" value="'.$row->nama_kode_akun.'" />';
					echo'</tr>';
					$no++;
				}
				
				echo '</tbody>';
			echo'</table>';
			echo'</div>';
		}
		else
		{
			echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}
	
	public function simpan()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$cek_ses_login = $this->M_gl_karyawan->get_karyawan_jabatan_row(" WHERE A.user = '".$this->session->userdata('ses_user_admin')."' AND A.pass = '".base64_encode(md5($this->session->userdata('ses_pass_admin_pure')))."';");
			
			if(!empty($cek_ses_login))
			{
				if (!empty($_POST['stat_edit']))
				{	
					$this->M_gl_pst_uang_keluar->edit
					(
						$_POST['stat_edit'],
						$_POST['id_kat_uang_keluar'],
						$_POST['id_kat_uang_keluar2'],
						'',//$_POST['id_costumer'],
						'',//$_POST['id_supplier'],
						'',//$_POST['id_karyawan'],
						'',//$_POST['id_proyek'],
						$_POST['id_bank'],
						'',//$_POST['id_d_assets'],
						'',//$_POST['id_retur_penjualan'],
						'',//$_POST['id_retur_pembelian'],
						'',//$_POST['id_hadiah'],
						$_POST['no_uang_keluar'],
						$_POST['nama_uang_keluar'],
						$_POST['diberikan_kepada'],
						$_POST['diberikan_oleh'],
						$_POST['untuk'],
						str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
						'',//$_POST['jenis'],
						$_POST['ket_uang_keluar'],
						'0',//$_POST['isTabungan'],
						'0',//$_POST['isPinjamanCos'],
						$_POST['tgl_dikeluarkan'],
						$_POST['tgl_diterima'],
						$this->session->userdata('ses_id_karyawan'),
						$_POST['cari_kode_kantor']
					);
					
					/* CATAT AKTIFITAS EDIT*/
					if($this->session->userdata('catat_log') == 'Y')
					{
						$this->M_gl_log->simpan_log
						(
							$this->session->userdata('ses_id_karyawan'),
							'UPDATE',
							'Melakukan perubahan data uang keluar '.$_POST['no_uang_keluar'].' | '.$_POST['nama_uang_keluar'],
							$this->M_gl_pengaturan->getUserIpAddr(),
							gethostname(),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					/* CATAT AKTIFITAS EDIT*/
				}
				else
				{
					if((!empty($_POST['id_induk_uang_keluar'])) && ($_POST['id_induk_uang_keluar']!= "")  )
					{
						$this->M_gl_pst_uang_keluar->simpan
						(
							$_POST['id_kat_uang_keluar'],
							$_POST['id_kat_uang_keluar2'],
							$_POST['id_induk_uang_keluar'],
							'',//$_POST['id_costumer'],
							'',//$_POST['id_supplier'],
							'',//$_POST['id_karyawan'],
							'',//$_POST['id_proyek'],
							$_POST['id_bank'],
							'',//$_POST['id_d_assets'],
							'',//$_POST['id_retur_penjualan'],
							'',//$_POST['id_retur_pembelian'],
							'',//$_POST['id_hadiah'],
							$_POST['no_uang_keluar'],
							$_POST['nama_uang_keluar'],
							$_POST['diberikan_kepada'],
							$_POST['diberikan_oleh'],
							$_POST['untuk'],
							str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
							'',//$_POST['jenis'],
							$_POST['ket_uang_keluar'],
							'0',//$_POST['isTabungan'],
							'0',//$_POST['isPinjamanCos'],
							$_POST['tgl_dikeluarkan'],
							$_POST['tgl_diterima'],
							$this->session->userdata('ses_id_karyawan'),
							$_POST['cari_kode_kantor']
						
						);
					}
					else
					{
						$this->M_gl_pst_uang_keluar->simpan_utama
						(
							$_POST['id_kat_uang_keluar'],
							$_POST['id_kat_uang_keluar2'],
							'',//$_POST['id_costumer'],
							'',//$_POST['id_supplier'],
							'',//$_POST['id_karyawan'],
							'',//$_POST['id_proyek'],
							$_POST['id_bank'],
							'',//$_POST['id_d_assets'],
							'',//$_POST['id_retur_penjualan'],
							'',//$_POST['id_retur_pembelian'],
							'',//$_POST['id_hadiah'],
							$_POST['no_uang_keluar'],
							$_POST['nama_uang_keluar'],
							$_POST['diberikan_kepada'],
							$_POST['diberikan_oleh'],
							$_POST['untuk'],
							str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
							'',//$_POST['jenis'],
							$_POST['ket_uang_keluar'],
							'0',//$_POST['isTabungan'],
							'0',//$_POST['isPinjamanCos'],
							$_POST['tgl_dikeluarkan'],
							$_POST['tgl_diterima'],
							$this->session->userdata('ses_id_karyawan'),
							$_POST['cari_kode_kantor']
						
						);
					}
					
				}
				
				if((!empty($_POST['id_induk_uang_keluar'])) && ($_POST['id_induk_uang_keluar']!= "")  )
				{
					header('Location: '.base_url().'gl-pusat-admin-pengeluaran-tambah/'.md5($_POST['id_induk_uang_keluar']));
				}
				else
				{
					header('Location: '.base_url().'gl-pusat-admin-pengeluaran');
				}
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function simpan_ajax_uang_keluar_hutang()
	{
			
			
			if($_POST['val'] > 0)
			{
				$cari = "WHERE 
					kode_kantor = '".$_POST['kode_kantor']."' 
					AND id_supplier = '".$_POST['id_supplier']."' 
					AND id_h_penerimaan = '".$_POST['id_h_penerimaan']."' 
					AND id_induk_uang_keluar = '".$_POST['id_induk_uang_keluar']."'
				";
				$data_uang_keluar = $this->M_gl_pst_uang_keluar->get_uang_keluar_cari($cari);
				if(!empty($data_uang_keluar))
				{
					$data_uang_keluar = $data_uang_keluar->row();
					$this->M_gl_pst_uang_keluar->edit_uang_keluar_nominal_saja
					(
						$_POST['kode_kantor']
						,$data_uang_keluar->id_uang_keluar
						,str_replace(",","",$_POST['val'])
						,str_replace(",","",$_POST['biaya'])
						,str_replace(",","",$_POST['pendapatan'])
						,$this->session->userdata('ses_id_karyawan')
					);
					
					//echo'TERUBAH';
					$sisa = str_replace(",","",$_POST['awal']) - (str_replace(",","",$_POST['val']) + str_replace(",","",$_POST['tlh_bayar']));
					echo number_format($sisa,0,'.',',');
				}
				else
				{
					$this->M_gl_pst_uang_keluar->simpan_uang_keluar_hutang
					(
						'', //$_POST['id_kat_uang_keluar'],
						$_POST['id_kode_akun'], //$_POST['id_kat_uang_keluar2'],
						$_POST['id_induk_uang_keluar'],
						'',//$_POST['id_costumer'],
						$_POST['id_supplier'],//$_POST['id_supplier'],
						'',//$_POST['id_karyawan'],
						'',//$_POST['id_proyek'],
						'',//$_POST['id_bank'],
						'',//$_POST['id_d_assets'],
						'',//$_POST['id_retur_penjualan'],
						'',//$_POST['id_retur_pembelian'],
						'',//$_POST['id_hadiah'],
						$_POST['id_h_penerimaan'],//$_POST['id_h_penerimaan'],
						$_POST['no_uang_keluar'],
						'Pembayaran Invoice '.$_POST['no_invoice'], //$_POST['nama_uang_keluar'],
						'', //$_POST['diberikan_kepada'],
						'', //$_POST['diberikan_oleh'],
						'', //$_POST['untuk'],
						str_replace(",","",$_POST['val']) , //$_POST['nominal'],
						str_replace(",","",$_POST['biaya']) , //$_POST['biaya'],
						str_replace(",","",$_POST['pendapatan']) , //$_POST['pendapatan'],
						'',//$_POST['jenis'],
						'Pembayaran Invoice '.$_POST['no_invoice'], //$_POST['ket_uang_keluar'],
						'0',//$_POST['isTabungan'],
						'0',//$_POST['isPinjamanCos'],
						$_POST['tgl_uang_keluar'],
						date('Y-m-d'), //$_POST['tgl_diterima'],
						$this->session->userdata('ses_id_karyawan'),
						$_POST['kode_kantor']
					
					);
					
					//echo'TERSIMPAN';
					$sisa = str_replace(",","",$_POST['awal']) - (str_replace(",","",$_POST['val']) + str_replace(",","",$_POST['tlh_bayar']));
					echo number_format($sisa,0,'.',',');
				}
			}
			else
			{
				$cari = "WHERE 
					kode_kantor = '".$_POST['kode_kantor']."' 
					AND id_supplier = '".$_POST['id_supplier']."' 
					AND id_h_penerimaan = '".$_POST['id_h_penerimaan']."' 
					AND id_induk_uang_keluar = '".$_POST['id_induk_uang_keluar']."'
				";
				$data_uang_keluar = $this->M_gl_pst_uang_keluar->get_uang_keluar_cari($cari);
				if(!empty($data_uang_keluar))
				{
					$data_uang_keluar = $data_uang_keluar->row();
					$this->M_gl_pst_uang_keluar->hapus('(id_uang_keluar)',$data_uang_keluar->id_uang_keluar,$_POST['kode_kantor']);
					
					//echo'TERHAPUS';
					$sisa = str_replace(",","",$_POST['awal']) - (str_replace(",","",$_POST['tlh_bayar']));
					echo number_format($sisa,0,'.',',');
				}
				else
				{
					echo 'KOSONG';
				}
		
				
			}
		
	}
	
	
	function simpan_ajax()
	{
		$this->M_gl_pst_uang_keluar->simpan
		(
			$_POST['id_kat_uang_keluar'],
			$_POST['id_kat_uang_keluar2'],
			$_POST['id_induk_uang_keluar'],
			'',//$_POST['id_costumer'],
			'',//$_POST['id_supplier'],
			'',//$_POST['id_karyawan'],
			'',//$_POST['id_proyek'],
			$_POST['id_bank'],
			'',//$_POST['id_d_assets'],
			'',//$_POST['id_retur_penjualan'],
			'',//$_POST['id_retur_pembelian'],
			'',//$_POST['id_hadiah'],
			$_POST['no_uang_keluar'],
			$_POST['nama_uang_keluar'],
			$_POST['diberikan_kepada'],
			$_POST['diberikan_oleh'],
			$_POST['untuk'],
			str_replace(",","",$_POST['nominal']) , //$_POST['nominal'],
			'',//$_POST['jenis'],
			$_POST['ket_uang_keluar'],
			'0',//$_POST['isTabungan'],
			'0',//$_POST['isPinjamanCos'],
			$_POST['tgl_dikeluarkan'],
			$_POST['tgl_diterima'],
			$this->session->userdata('ses_id_karyawan'),
			$_POST['kode_kantor']
		
		);
		
		$cari = 
				"
					WHERE A.kode_kantor = '".$_POST['kode_kantor']."'
					AND (A.id_induk_uang_keluar) = '".$_POST['id_induk_uang_keluar']."'
					AND (A.id_uang_keluar) <> '".$_POST['id_induk_uang_keluar']."'
				";
		
		$order_by = " ORDER BY A.tgl_ins DESC ";
					
		$list_uang_keluar = $this->M_gl_pst_uang_keluar->list_uang_keluar_limit($cari,$order_by,1000,0);
		
		if(!empty($list_uang_keluar))
		{
			
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead style="background-color:green;">
<tr>';
							
							echo '<th style="border:1px black solid;" width="20%">ACCOUNT</th>';
							echo '<th style="border:1px black solid;text-align:center;" colspan="2" width="45%">NAMA AKUN  DAN KETERANGAN</th>';
							echo '<th style="border:1px black solid;" width="20%">NOMINAL</th>';
							echo '<th style="border:1px black solid;" width="15%">AKSI</th>';
				echo '</tr>
</thead>';
				$list_result = $list_uang_keluar->result();
				$no =$this->uri->segment(3,0)+1;
				$sub_total = 0;
				echo '<tbody>';
				foreach($list_result as $row)
				{
				
					
						echo'<tr>';
						echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN2.'</td>';
						//echo'<td style="border:1px black solid;" colspan="2">'.$row->NAMA_AKUN2.'<br/><i>'.$row->ket_uang_keluar.'</i></td>';
						echo'<td style="border:1px black solid;" colspan="2">'.$row->ket_uang_keluar.'</td>';
						
						//echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN2.'</td>';
						//echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN.'</td>';
						echo'<td style="text-align:right;font-weight:normal;border:1px black solid;">'.number_format($row->nominal,0,'.',',').'</td>';
						//echo'<td style="border:1px black solid;"></td>';
						echo'<td rowspan="1" style="border:1px black solid;">
						


<a class="confirm-btn" href="javascript:void(0)" onclick="hapus_debet(this)" id="'.$no.'-'.$row->id_uang_keluar.'" title = "Hapus Data '.$row->nama_uang_keluar.'" alt = "Hapus Data '.$row->nama_uang_keluar.'">
<i class="fa fa-trash"></i> HAPUS
</a>
						</td>';
						
					echo'</tr>';
					
					
						
						
						echo'<input type="hidden" id="id_uang_keluar_'.$no.'" name="id_uang_keluar_'.$no.'" value="'.$row->id_uang_keluar.'" />';
						
					$no++;
					$sub_total = $sub_total + $row->nominal;
					//sum_uang_keluar
				}
				
				echo'<tr>';
					echo'<td colspan="3" style="text-align:center;font-weight:bold;border:1px black solid;">TOTAL</td>';
					echo'<td  style="text-align:right;font-weight:bold;border:1px black solid;">'.number_format($sub_total,0,'.',',').'</td>';
					echo'<td  style="border:1px black solid;" colspan="2"></td>';
				echo'</tr>';
				
				echo '</tbody>';
			echo'</table>';
		}
		else
		{
			echo'<center>';
			echo'Tidak Ada Data Yang Ditampilkan !';
			echo'</center>';
		}
	}
	
	public function hapus()
	{
		$id_uang_keluar = ($this->uri->segment(2,0));
		$kode_kantor = ($this->uri->segment(3,0));

		
		//$data_uang_keluar = $this->M_gl_pst_uang_keluar->get_uang_keluar('MD5(id_uang_keluar)',$id_uang_keluar,$kode_kantor);
		
		
		$cari = "WHERE 
					kode_kantor = '".$kode_kantor."' 
					AND MD5(id_uang_keluar) = '".$id_uang_keluar."'
				";
		$data_uang_keluar = $this->M_gl_pst_uang_keluar->get_uang_keluar_cari($cari);
		
		if(!empty($data_uang_keluar))
		{
			//echo'ADA DATA';
			$data_uang_keluar = $data_uang_keluar->row();
			//$this->M_gl_pst_uang_keluar->hapus('MD5(id_uang_keluar)',$id_uang_keluar);
			//$this->M_gl_pst_uang_keluar->hapus('MD5(id_induk_uang_keluar)',$id_uang_keluar,$kode_kantor);
			$this->M_gl_pst_uang_keluar->hapus('MD5(id_uang_keluar)',$id_uang_keluar,$kode_kantor);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data uang keluar '.$data_uang_keluar->no_uang_keluar.' | '.$data_uang_keluar->nama_uang_keluar,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		header('Location: '.base_url().'gl-pusat-admin-pengeluaran');
	}
	
	public function hapus_ajax()
	{
		$id_uang_keluar = ($_POST['id_uang_keluar']);
		$kode_kantor = ($_POST['kode_kantor']);

		$data_uang_keluar = $this->M_gl_pst_uang_keluar->get_uang_keluar('(id_uang_keluar)',$id_uang_keluar,$kode_kantor);
		if(!empty($data_uang_keluar))
		{
			$data_uang_keluar = $data_uang_keluar->row();
			$this->M_gl_pst_uang_keluar->hapus('(id_uang_keluar)',$id_uang_keluar,$kode_kantor);
			
			/* CATAT AKTIFITAS HAPUS*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'DELETE',
					'Melakukan penghapusan data uang keluar '.$data_uang_keluar->no_uang_keluar.' | '.$data_uang_keluar->nama_uang_keluar,
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
			/* CATAT AKTIFITAS HAPUS*/
		}
		
		$cari = 
				"
					WHERE A.kode_kantor = '".$kode_kantor."'
					AND (A.id_induk_uang_keluar) = '".$_POST['id_induk_uang_keluar']."'
					AND (A.id_uang_keluar) <> '".$_POST['id_induk_uang_keluar']."'
				";
		
		$order_by = " ORDER BY A.tgl_ins DESC ";
					
		$list_uang_keluar = $this->M_gl_pst_uang_keluar->list_uang_keluar_limit($cari,$order_by,1000,0);
		
		if(!empty($list_uang_keluar))
		{
			
			echo'<table width="100%" id="example2" class="table table-hover">';
				echo '<thead style="background-color:green;">
<tr>';
							
							echo '<th style="border:1px black solid;" width="20%">ACCOUNT</th>';
							echo '<th style="border:1px black solid;text-align:center;" colspan="2" width="45%">NAMA AKUN  DAN KETERANGAN</th>';
							echo '<th style="border:1px black solid;" width="20%">NOMINAL</th>';
							echo '<th style="border:1px black solid;" width="15%">AKSI</th>';
				echo '</tr>
</thead>';
				$list_result = $list_uang_keluar->result();
				$no =$this->uri->segment(3,0)+1;
				$sub_total = 0;
				echo '<tbody>';
				foreach($list_result as $row)
				{
				
					
						echo'<tr>';
						echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN2.'</td>';
						//echo'<td style="border:1px black solid;" colspan="2">'.$row->NAMA_AKUN2.'<br/><i>'.$row->ket_uang_keluar.'</i></td>';
						echo'<td style="border:1px black solid;" colspan="2">'.$row->ket_uang_keluar.'</td>';
						
						//echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN2.'</td>';
						//echo'<td rowspan="1" style="border:1px black solid;">'.$row->KODE_AKUN.'</td>';
						echo'<td style="text-align:right;font-weight:normal;border:1px black solid;">'.number_format($row->nominal,0,'.',',').'</td>';
						//echo'<td style="border:1px black solid;"></td>';
						echo'<td rowspan="1" style="border:1px black solid;">
						


<a class="confirm-btn" href="javascript:void(0)" onclick="hapus_debet(this)" id="'.$no.'-'.$row->id_uang_keluar.'" title = "Hapus Data '.$row->nama_uang_keluar.'" alt = "Hapus Data '.$row->nama_uang_keluar.'">
<i class="fa fa-trash"></i> HAPUS
</a>
						</td>';
						
					echo'</tr>';
					
					
						
						
						echo'<input type="hidden" id="id_uang_keluar_'.$no.'" name="id_uang_keluar_'.$no.'" value="'.$row->id_uang_keluar.'" />';
						
					$no++;
					$sub_total = $sub_total + $row->nominal;
					//sum_uang_keluar
				}
				
				echo'<tr>';
					echo'<td colspan="3" style="text-align:center;font-weight:bold;border:1px black solid;">TOTAL</td>';
					echo'<td  style="text-align:right;font-weight:bold;border:1px black solid;">'.number_format($sub_total,0,'.',',').'</td>';
					echo'<td  style="border:1px black solid;" colspan="2"></td>';
				echo'</tr>';
				
				echo '</tbody>';
			echo'</table>';
		}
		else
		{
			echo'<center>';
			echo'Tidak Ada Data Yang Ditampilkan !';
			echo'</center>';
		}
	}
	
	function cek_uang_keluar()
	{
		$hasil_cek = $this->M_gl_pst_uang_keluar->get_uang_keluar('no_uang_keluar',$_POST['no_uang_keluar']);
		if(!empty($hasil_cek))
		{
			$hasil_cek = $hasil_cek->row();
			echo $hasil_cek->no_uang_keluar;
		}
		else
		{
			return false;
		}
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */