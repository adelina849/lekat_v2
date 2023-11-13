<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_lap_h_penjualan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_lap_penjualan','M_gl_h_penjualan','M_gl_images','M_gl_kode_akun','M_gl_kat_costumer'));
		
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
				
				if((!empty($_GET['cari_nama_kat_costumer'])) && ($_GET['cari_nama_kat_costumer']!= "")  )
				{
					$cari_nama_kat_costumer = "AND COALESCE(D.nama_kat_costumer,'') LIKE '%".str_replace("'","",$_GET['cari_nama_kat_costumer'])."%'" ;
				}
				else
				{
					$cari_nama_kat_costumer = "";
				}
				
				if((!empty($_GET['from'])) && ($_GET['from']!= "")  )
				{
					if($_GET['from'] == "lap_hutang")
					{
						$from = "AND ((COALESCE(B.SUBTOTAL,0) + A.pajak + A.biaya) - (COALESCE(C.TOTAL_BAYAR,0) + A.diskon + A.nominal_retur)) > 1";
					}
					else
					{
						$from = "";
					}
				}
				else
				{
					$from = "";
				}
				
				
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND A.sts_penjualan IN ('SELESAI','PEMERIKSAAN','PEMBAYARAN','PENDING')
							AND DATE(A.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."'
							".$from."
							".$cari_nama_kat_costumer."
							AND 
							(
								A.no_faktur LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(D.no_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.nama_lengkap,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(E.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								
								OR COALESCE(H.no_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(H.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
					
								OR COALESCE(I.no_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(I.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND A.sts_penjualan IN ('SELESAI','PEMERIKSAAN','PEMBAYARAN','PENDING')
							AND DATE(A.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."' 
							".$from."
							".$cari_nama_kat_costumer."
							";
				}
				$order_by = "ORDER BY A.tgl_ins DESC";
				
				
				//UNTUK AKUMULASI INFO
				if($from == "")
				{
					$jum_row = $this->M_gl_lap_penjualan->count_laporan_h_penjualan($cari)->JUMLAH;
					$per_page = 100;
				}
				else
				{
					$jum_row = 10000;
					$per_page = 10000;
				}
				//UNTUK AKUMULASI INFO
				
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				//$config['first_url'] = site_url('gl-admin-laporan-transaksi?'.http_build_query($_GET));
				$config['first_url'] = site_url('gl-admin-laporan-transaksi?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-laporan-transaksi/');
				$config['total_rows'] = $jum_row;
				$config['uri_segment'] = 2;	
				//$config['uri_segment'] = $_GET['var_offset'];	
				$config['per_page'] = $per_page;
				//$config['per_page'] = 10000;
				$config['num_links'] = 2;
				//$config['num_links'] = 20;
				//$config['suffix'] = '?' . http_build_query($_GET, '', "&");
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
				
				$list_laporan_h_penjualan = $this->M_gl_lap_penjualan->list_laporan_h_penjualan($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				
				if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
				{
					$cari_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ORDER BY nama_kat_costumer ASC;";
					$list_kat_costumer = $this->M_gl_kat_costumer->get_kat_costumer_cari($cari_kat_costumer);
				}
				
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
				
				//SUMMARY
					//$sum_laporan_h_penjualan = $this->M_gl_lap_penjualan->sum_laporan_h_penjualan($cari);
					//$sum_laporan_h_penjualan = $sum_laporan_h_penjualan->row();
					$sum_laporan_h_penjualan = false;
				//SUMMARY
				
				$msgbox_title = " Laporan Transaksi ";
				
				if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
				{
					$data = array('page_content'=>'gl_admin_lap_h_penjualan','halaman'=>$halaman, 'sum_pesan' => $sum_pesan,'msgbox_title' => $msgbox_title,'list_laporan_h_penjualan' => $list_laporan_h_penjualan, 'sum_laporan_h_penjualan' => $sum_laporan_h_penjualan,'from' => $from,'list_kat_costumer' => $list_kat_costumer);
					$this->load->view('admin/container',$data);
				}
				else
				{
					$data = array('page_content'=>'gl_admin_lap_h_penjualan','halaman'=>$halaman, 'sum_pesan' => $sum_pesan,'msgbox_title' => $msgbox_title,'list_laporan_h_penjualan' => $list_laporan_h_penjualan, 'sum_laporan_h_penjualan' => $sum_laporan_h_penjualan,'from' => $from);
					$this->load->view('admin/container',$data);
				}
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function cek_user_pass_for_ubah()
	{
		if(($this->session->userdata('ses_user_admin') == null) or ($this->session->userdata('ses_pass_admin') == null))
		{
			header('Location: '.base_url().'gl-admin-login');
		}
		else
		{
			$kode_kantor = $_POST['kode_kantor'];
			$user = $_POST['user'];
			$pass = $_POST['pass'];
			
			
			$data_login = $this->M_gl_karyawan->get_karyawan_jabatan_row_when_login(" WHERE A.user = '".$user."' AND A.pass = '".base64_encode(md5($pass))."' AND A.kode_kantor = '".$kode_kantor."' AND A.isAktif NOT IN ('PHK','RESIGN')");
			if(!empty($data_login))
			{
				//$data_login = $data_login->row();
				if($data_login->nama_jabatan == 'Admin Aplikasi')
				{
					echo'BERHASIL';
				}
				else
				{
					$cari_akses = " WHERE kode_kantor = '".$kode_kantor."' AND id_jabatan = '".$data_login->id_jabatan."' AND id_fasilitas = '85'";
					$cek = $this->M_gl_lap_penjualan->cek_hak_akse_for_ubah($cari_akses);
					if(!empty($cek))
					{
						$cek = $cek->row();
						echo'BERHASIL';
					}
					else
					{
						echo'SALAH';
					}
				}
			}
			else
			{
				echo"SALAH";
			}
			
		}
	}
	
	function list_h_penjualan_general()
	{
		if((!empty($_POST['dari'])) && ($_POST['dari']!= "")  )
		{
			$dari = $_POST['dari'];
			$sampai = $_POST['sampai'];
		}
		else
		{
			$dari = date("Y-m-d");
			$sampai = date("Y-m-d");
		}
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					-- AND A.sts_penjualan IN ('SELESAI','PEMBAYARAN')
					-- AND A.sts_penjualan = 'SELESAI'
					AND DATE(A.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."'
					AND 
					(
						A.no_faktur LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(D.no_costumer,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(D.nama_lengkap,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(E.nama_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
					)";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					-- AND A.sts_penjualan IN ('SELESAI','PEMBAYARAN') 
					-- AND A.sts_penjualan = 'SELESAI'
					AND DATE(A.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."' ";
		}
		$order_by = "ORDER BY A.tgl_ins DESC";
		
		
		//UNTUK AKUMULASI INFO
			$jum_row = $this->M_gl_lap_penjualan->count_laporan_h_penjualan($cari)->JUMLAH;
		//UNTUK AKUMULASI INFO
		
		
		$this->load->library('pagination');
		//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_POST);
		//$config['base_url'] = base_url().'admin/jabatan/';
		
		//$config['first_url'] = site_url('gl-admin-laporan-transaksi?'.http_build_query($_GET));
		$config['first_url'] = site_url('gl-admin-laporan-transaksi?'.http_build_query($_POST));
		$config['base_url'] = site_url('gl-admin-laporan-transaksi/');
		$config['total_rows'] = $jum_row;
		$config['uri_segment'] = 2;	
		//$config['uri_segment'] = $_POST['var_offset'];	
		$config['per_page'] = 100;
		//$config['per_page'] = 10000;
		//$config['num_links'] = 2;
		$config['num_links'] = 20;
		//$config['suffix'] = '?' . http_build_query($_GET, '', "&");
		$config['suffix'] = '?' . http_build_query($_POST, '', "&");
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
		
		//$list_laporan_h_penjualan = $this->M_gl_lap_penjualan->list_laporan_h_penjualan($cari,$order_by,$config['per_page'],$_POST['var_offset']);
		$list_laporan_h_penjualan = $this->M_gl_lap_penjualan->list_laporan_h_penjualan($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
		
		
		//UNTUK AKUMULASI INFO
			if($config['per_page'] > $jum_row)
			{
				$jum_row_tampil = $jum_row;
			}
			else
			{
				$jum_row_tampil = $config['per_page'];
			}
			
			//$offset = (integer) $_POST['var_offset'];
			$offset = (integer) $this->uri->segment(2,0);
			$max_data = $offset + $jum_row_tampil;
			$offset = $offset + 1;
			if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
			{
				$sum_pesan = "Menampilkan ".$jum_row_tampil." Dari ".$jum_row." Data Pencarian ".str_replace("'","",$_POST['cari'])." dimulai dari data ke ".$offset." Sampai ".$max_data;
			}
			else
			{
				$sum_pesan = "Menampilkan ".$jum_row_tampil." Dari ".$jum_row." Data dimulai dari data ke ".$offset." Sampai ".$max_data;
			}
		//UNTUK AKUMULASI INFO
		
		
		
		echo'<div class="box-body table-responsive no-padding">';
			if(!empty($list_laporan_h_penjualan))
			{
				//SUMMARY
					$sum_laporan_h_penjualan = $this->M_gl_lap_penjualan->sum_laporan_h_penjualan($cari);
					$sum_laporan_h_penjualan = $sum_laporan_h_penjualan->row();
				//SUMMARY
									echo'<table width="100%" id="example2" class="table table-hover hoverTable">';
										echo '<thead>';
											//echo'<tr style="background-color:green;font-weight:bold;">';
											echo'<tr>';
												echo'<td colspan="4" style="text-align:right;">GRAND TOTAL TRANSAKSI</td>';
												echo'<td style="text-align:right;">'.number_format($sum_laporan_h_penjualan->SUBTOTAL_ALL,1,'.',',').'</td>';
												echo'<td></td>';
											echo'</tr>';
											
											echo'<tr>';
												echo'<td colspan="4" style="text-align:right;">GRAND TOTAL PEMBAYARAN CASH</td>';
												echo'<td style="text-align:right;">'.number_format($sum_laporan_h_penjualan->BAYAR_CASH,1,'.',',').'</td>';
												echo'<td></td>';
											echo'</tr>';
											
											echo'<tr>';
												echo'<td colspan="4" style="text-align:right;">GRAND TOTAL PEMBAYARAN BANK</td>';
												echo'<td style="text-align:right;">'.number_format($sum_laporan_h_penjualan->BAYAR_BANK,1,'.',',').'</td>';
												echo'<td></td>';
											echo'</tr>';
											
											echo'<tr>';
												echo'<td colspan="6" style="text-align:right;"><i>'.strtoupper(Terbilang($sum_laporan_h_penjualan->SUBTOTAL_ALL)).'</i></td>';
												
											echo'</tr>';
										echo'
												<tr style="background-color:green;">';
													echo '<th width="5%">NO</th>';
													echo '<th width="30%">DATA</th>';
													echo '<th width="18%">PENGELUARAN</th>';
													echo '<th width="17%">PENGURANG</th>';
													echo '<th width="20%">PEMBAYARAN</th>';
													echo '<th width="10%">AKSI</th>';
										echo '</tr>
											</thead>';
										$list_result = $list_laporan_h_penjualan->result();
										
										
										//$no =$_POST['var_offset']+1;
										$no =$this->uri->segment(2,0)+1;
										
										/*
										$nominal_transaksi = 0;
										$nominal_pembayaran_cash = 0;
										$nominal_pembayaran_bank = 0;
										*/
										
										echo '<tbody>';
										
										foreach($list_result as $row)
										{
											
											if($row->AVATAR_PASIEN == "")
											{
												$src = base_url().'assets/global/costumer/loading.gif';
											}
											else
											{
												$src = base_url().''.$row->AVATAR_URL_PASIEN.''.$row->AVATAR_PASIEN;
											}
											
											if($row->sts_penjualan != "SELESAI")
											{
												echo'<tr style="background-color:red;">';
											}
											else
											{
												echo'<tr>';
											}
											
												echo'<td>'.$no.'</td>';
												
												//<b><font style="color:red;">DATA TRANSKASI </font></b>
												echo '<td>
													<b style="color:red;">'.$row->sts_penjualan.' - '.$row->type_h_penjualan.'</b>
													<div style="width:100%;">
														<img id="IMG_'.$no.'"  width="30%" height="100px" style="margin-left:15%;border:1px solid #C8C8C8; padding:5px;" src="'.$src.'" />
													</div>
													<b>FAKTUR : </b>'.$row->no_faktur.'
													<br/> <b>WAKTU : </b>'.$row->DT_PENJUALAN.'('.$row->LAMA_KONSUL.' Menit)
													
													<br/> <b>NO PASIEN : </b>'.$row->NO_PASIEN.'
													<br/> <b>NAMA PASIEN : </b>'.$row->NAMA_PASIEN.'
													
													
													<br/> <b>NAMA DOKTER : </b>'.$row->NAMA_KARYAWAN.'
												</td>';
												
												echo '<td>
												
														<b>SUBTOTAL : </b>'.number_format($row->SUBTOTAL,1,'.',',').' 
														<br/> <b>PAJAK : </b>'.number_format($row->pajak,1,'.',',').'
														<br/> <b>BIAYA : </b>'.number_format($row->biaya,1,'.',',').'
												</td>';
												
												echo '<td>
												
														<b>DISKON : </b>'.number_format($row->diskon,1,'.',',').' 
														<br/> <b>POT. RETUR : </b>'.number_format($row->nominal_retur,1,'.',',').'
												</td>';
												
												echo '<td>
												
														<b>BAYAR : </b>'.number_format($row->TOTAL_BAYAR,1,'.',',').' 
														<br/> <b>CASH : </b>'.number_format($row->BAYAR_CASH,1,'.',',').'
														<br/> <b>BANK : </b>'.number_format($row->BAYAR_BANK,1,'.',',').'
														<br/> <b>-</b>('.$row->BANK.')
												</td>';
												
												/*
												echo'<td>';
	echo'<a href="javascript:void(0)" class="btn btn-success btn-block btn-flat" data-toggle="modal" data-target="#myModal_detail_penjualan" id="DETAIL-'.$row->id_h_penjualan.'-'.$row->id_costumer.'-'.$row->no_faktur.'"  onclick="rekam_medis(this)" title = "Ubah Data '.$row->no_faktur.'" alt = "Ubah Data '.$row->no_faktur.'">DETAIL</a>';
	
	if( ($this->session->userdata('ses_akses_lvl3_535') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
	{
		echo'<a href="javascript:void(0)" class="btn btn-warning btn-block btn-flat" id="FAKTUR-'.$row->id_h_penjualan.'" onclick="print_faktur(this)" title = "Cetak Ulang Faktur Data '.$row->no_faktur.'" alt = "Cetak Ulang Faktur Data '.$row->no_faktur.'">FAKTUR</a>';
	}
	
	
	if( ($this->session->userdata('ses_akses_lvl3_532') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
	{
		echo'<a href="'.base_url().'gl-admin-pemeriksaan-dokter-proses/'.md5($row->id_h_penjualan).'" class="btn btn-warning btn-block btn-flat" title = "Ubah Data '.$row->no_faktur.'" alt = "Ubah Data '.$row->no_faktur.'">UBAH</a>';
	}
	
	if( ($this->session->userdata('ses_akses_lvl3_533') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
	{
		echo'<a class="confirm-btn btn btn-danger  btn-block btn-flat" href="javascript:void(0)" id="HAPUS-'.$row->id_h_penjualan.'-'.$row->id_costumer.'-'.$row->no_faktur.'" onclick="hapus_h_penjualan(this)" title = "Hapus Data '.$row->no_faktur.'" alt = "Hapus Data '.$row->no_faktur.'">HAPUS</a>';
	}
	
												echo'</td>';
												*/
												
												echo'<td>';
				echo'<a class="confirm-btn" href="javascript:void(0)" data-toggle="modal" data-target="#myModal_detail_penjualan" id="DETAIL-'.$row->id_h_penjualan.'-'.$row->id_costumer.'-'.$row->no_faktur.'"  onclick="rekam_medis(this)" title = "Detail Transaksi '.$row->no_faktur.'" alt = "Detail Transaksi '.$row->no_faktur.'">
					<i class="fa fa-search"></i> DETAIL
				</a>
				<br/>
				';
				
				echo'<a class="confirm-btn" href="'.base_url().'gl-admin-images/penjualan/'.$row->id_h_penjualan.'?from=laporanpenjualan"  id="FOTO-'.$row->id_h_penjualan.'-'.$row->id_costumer.'-'.$row->no_faktur.'" title = "FOTO Transaksi '.$row->no_faktur.'" alt = "FOTO Transaksi '.$row->no_faktur.'">
					<i class="fa fa-image"></i> FOTO
				</a>
				<br/>
				';
				
				/*LACAK DAN MUTASI*/
					if($this->session->userdata('ses_kode_kantor') == "SLM")
					{
						echo'<a class="confirm-btn" href="#" title = "Detail Pembayaran '.$row->no_faktur.'" alt = "Detail Pembayaran '.$row->no_faktur.'">
						<i class="fa fa-truck"></i> LACAK
						</a>
						<br/>
						';
					}
				/*LACAK DAN MUTASI*/
				
				/*DETAIL PENERIMAAN*/
					if($this->session->userdata('ses_isModePst') == "YA")
					{
						if($row->tgl_terima == '')
						{
							echo'<a class="" href="#" title = "Detail Penerimaan '.$row->no_faktur.'" alt = "Detail Penerimaan '.$row->no_faktur.'" style="background-color:red;padding:0.5%;">
								<i class="fa fa-truck"></i> BELUM TERIMA
							</a>
							<br/>
							';
						}
						else
						{
							echo'<a class="confirm-btn" href="'.base_url().'gl-admin-purchase-order-terima-cek-pusat/'.md5($row->no_faktur).'" title = "Detail Penerimaan '.$row->no_faktur.'" alt = "Detail Penerimaan '.$row->no_faktur.'">
								<i class="fa fa-truck"></i> TERIMA
							</a>
							<br/>
							';
						}
					}
				/*DETAIL PENERIMAAN*/
				
				/*PASTIKAN MEMILIKI AKSES MEMBAYAR*/
				if( ($this->session->userdata('ses_akses_lvl2_54') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
					echo'<a class="confirm-btn" href="'.base_url().'gl-admin-laporan-transaksi-pembayaran/'.md5($row->id_h_penjualan).'" title = "Detail Pembayaran '.$row->no_faktur.'" alt = "Detail Pembayaran '.$row->no_faktur.'">
						<i class="fa fa-money"></i> BAYAR
					</a>
					<br/>
					';
				}
				/*PASTIKAN MEMILIKI AKSES MEMBAYAR*/
				
				if( ($this->session->userdata('ses_akses_lvl3_535') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
					echo'<a class="confirm-btn" href="javascript:void(0)" id="FAKTUR-'.$row->id_h_penjualan.'" onclick="print_faktur(this)" title = "Cetak Ulang Faktur Data '.$row->no_faktur.'" alt = "Cetak Ulang Faktur Data '.$row->no_faktur.'">
						<i class="fa fa-print"></i> FAKTUR
					</a>
					<br/>
					';
					
					echo'<a class="confirm-btn" href="javascript:void(0)" id="FAKTUR-'.$row->id_h_penjualan.'" onclick="print_surat_jalan(this)" title = "Cetak Ulang Faktur Data '.$row->no_faktur.'" alt = "Cetak Ulang Faktur Data '.$row->no_faktur.'">
						<i class="fa fa-print"></i> SURAT JALAN
					</a>
					<br/>
					';
				}
				
				
				if( ($this->session->userdata('ses_akses_lvl3_532') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
					if( ($row->sts_penjualan == "SELESAI") or ($row->sts_penjualan == "PEMBAYARAN") )
					//if( ($row->sts_penjualan == "SELESAI") )
					{
						echo'<a class="confirm-btn" href="javascript:void(0)" data-toggle="modal" data-target="#myModal_berita_acara" id="UBAH-'.$row->id_h_penjualan.'-'.$row->id_costumer.'-'.$row->no_faktur.'"  onclick="upload_berita_acara(this)" title = "Ubah Transaksi '.$row->no_faktur.'" alt = "Ubah Transaksi '.$row->no_faktur.'">
							<i class="fa fa-edit"></i> UBAH
						</a>
						<br/>
						';
						
					}
					//else if(( $row->sts_penjualan == "PEMERIKSAAN" ) or ($row->sts_penjualan == "PEMBAYARAN"))
					else if( $row->sts_penjualan == "PEMERIKSAAN" )
					{
						echo'<a class="confirm-btn" href="'.base_url().'gl-admin-pemeriksaan-dokter-proses/'.md5($row->id_h_penjualan).'" title = "Ubah Data '.$row->no_faktur.'" alt = "Ubah Data '.$row->no_faktur.'">
							<i class="fa fa-edit"></i> UBAH
						</a>
						<br/>
						';
					}
					
					if(($row->type_h_penjualan != 'PENJUALAN LANGSUNG') && ($row->sts_penjualan == 'SELESAI'))
					{
						
						echo'<a class="confirm-btn" href="javascript:void(0)" id="BUKA-'.$row->id_h_penjualan.'-'.$row->id_costumer.'-'.$row->no_faktur.'" onclick="buka_h_penjualan(this)" title = "Buka Data '.$row->no_faktur.'" alt = "Buka Data '.$row->no_faktur.'">
						<i class="fa fa-stethoscope"></i> BUKA TINDAKAN
						</a>
						<br/>
						';
					}
				}
				
				if( ($this->session->userdata('ses_akses_lvl3_533') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
				{
					if( ($row->sts_penjualan == "PENDING") or ($row->sts_penjualan == "PEMERIKSAAN") )
					{
						echo'<a class="confirm-btn" href="javascript:void(0)" id="HAPUS-'.$row->id_h_penjualan.'-'.$row->id_costumer.'-'.$row->no_faktur.'" onclick="hapus_h_penjualan(this)" title = "Hapus Data '.$row->no_faktur.'" alt = "Hapus Data '.$row->no_faktur.'">
							<i class="fa fa-trash"></i> HAPUS
						</a>';
					}
				}
				
															echo'</td>';
												
											echo'</tr>';
											
											/*
											$nominal_transaksi = $nominal_transaksi + $row->SUBTOTAL;
											$nominal_pembayaran_cash = $nominal_pembayaran_cash + $row->BAYAR_CASH;
											$nominal_pembayaran_bank = $nominal_pembayaran_bank + $row->BAYAR_BANK;
											*/
											$no++;
										}
										
										
										
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
						
			echo'
			<center>
				<div class="halaman">'.$halaman.'</div>
			</center>
		</div><!-- /.box-body -->
		';
	}
	
	function rekam_medis_lengkap()
	{
		$id_costumer = $_POST['id_costumer'];
		/*REKAM MEDIS PASIEN*/
			//$cari_rekam_medis = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_costumer = '".$id_costumer."' AND A.sts_penjualan IN ('SELESAI','PEMBAYARAN')";
			
			//$cari_rekam_medis = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_costumer = '".$id_costumer."'";
			
			
			if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
			{
				$cari_gets = $_POST['cari'];
				
				$cari_rekam_medis = " 
									WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
									AND A.id_costumer = '".$id_costumer."' 
									-- AND A.sts_penjualan = 'SELESAI' 
									AND A.sts_penjualan IN ('SELESAI','PEMBAYARAN')
									AND (A.no_faktur = '".str_replace("'","",$_POST['cari'])."')";
			}
			else
			{
				$cari_gets = "";
				
				$cari_rekam_medis = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_costumer = '".$id_costumer."' AND A.sts_penjualan = 'SELESAI'";
			}
			
			
			
			$order_by = " ORDER BY A.tgl_ins DESC";
			
			$list_rekam_medis = $this->M_gl_lap_penjualan->list_rekam_medis_lengkap($cari_rekam_medis,$order_by,5,0);
			if(!empty($list_rekam_medis))
			{
				$list_result = $list_rekam_medis->result();
				foreach($list_result as $row)
				{
					if($row->avatar_dokter != "")
					//if(file_exists(base_url().''.$row->avatar_url_dokter.''.$row->avatar_dokter))
					{
						$src = base_url().''.$row->avatar_url_dokter.''.$row->avatar_dokter;
					}
					else
					{
						$src = base_url().'assets/global/costumer/loading.gif';
					}
					
					echo'
					<!-- SATU KOLOM REKAM MEDSI -->
						<li class="time-label">
							<span class="bg-red">
							'.$row->format_tgl_h_penjualan.'
							</span>
						</li>
						<li style="border-bottom:1px solid #d2d2d2;">
							<i class="fa fa-comments bg-yellow"></i>

							<div class="timeline-item">
								<span class="time"><i class="fa fa-clock-o"></i> '.$row->selisih_hari.' hari yang lalu</span>
								';
								
								//if($row->ket_penjualan == "PENJUALAN LANGSUNG")
								//{
									echo'<h3 class="timeline-header"><a href="#">'.$row->nama_karyawan.' ('.$row->no_faktur.')</a> </br><font style="color:red;">Keluhan</font><br/>'.$row->ket_penjualan.'
									</br></br><font style="color:red;">Diagnosa</font><br/>'.$row->ket_penjualan2.'
									</h3>';
								//}
								//else
								//{
									//echo'<h3 class="timeline-header"><a href="#">'.$row->nama_karyawan.' ('.$row->no_faktur.')</a> </br><font style="color:red;">Keluhan</font>'.$row->ket_penjualan.'</h3>';
								//}
								
					echo'
								<div class="timeline-body">
								<table style="width:100%;border:1 px solid black;">
									<tr>
										<td width="20%" style="text-align:center;">
											<span id="img_span_dr">
												<img id="IMG_dr" name="IMG_dr"  width="100%" class="profile-user-img img-responsive"  src="'.$src.'" />
											</span>
											<font style="color:red;">'.$row->nama_dokter.'</font>
											<br/>
											<font style="color:red;">Lama Tindakan : '.$row->LAMA_TINDAKAN.' Menit</font>
											<br/>
											<font style="color:red;">Lama Konsul : '.$row->LAMA_KONSUL.' Menit</font>
											
											
											<!-- <a href="'.base_url().'gl-admin-images/penjualan/'.$row->id_h_penjualan.'" class="btn btn-warning btn-flat btn-xs" >Tampilkan Foto Pemeriksaan</a> -->
											
											<a href="javascript:void(0)" id="'.$row->id_h_penjualan.'" class="btn btn-warning btn-flat btn-block" onclick="tampilkan_gambar(this);" title = "Tampilkan Gambar/Foto Pasien '.$row->no_faktur.'" alt = "Tampilkan Gambar/Foto Pasien '.$row->no_faktur.'" data-toggle="modal" data-target="#myModal_foto_pasien" >LIHAT FOTO</a>
											
											
										</td>
										<td width="80%">
					';
						/*MASUKAN detail_penjualan*/
						echo'<table style="margin-left:2%;width:100%;">';
						if((!empty($_POST['cari_d_penjualan'])) && ($_POST['cari_d_penjualan']!= "")  )
						{
							$cari_d_penjualan = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
									AND A.id_h_penjualan = '".$row->id_h_penjualan."'
									AND (B.kode_produk LIKE '%".str_replace("'","",$_POST['cari_d_penjualan'])."%' OR B.nama_produk LIKE '%".str_replace("'","",$_POST['cari_d_penjualan'])."%')";
						}
						else
						{
							$cari_d_penjualan = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
									AND A.id_h_penjualan = '".$row->id_h_penjualan."'";
						}
						
						$order_by = " ORDER BY A.isProduk ASC, A.tgl_ins DESC ";
						
						$list_d_penjualan = $this->M_gl_h_penjualan->list_d_penjualan($cari_d_penjualan,$order_by,1000,0);
						//($cari,$_POST['limit'],$_POST['offset']);
						
						if(!empty($list_d_penjualan))
						{
								$list_result = $list_d_penjualan->result();
								//$no =$this->uri->segment(2,0)+1;
								$no = 1;
								$isProdukCur = "";
								$isProdukOld = "";
								
								echo'
								<thead>
									<tr style="background-color:green;text-align:center;">
										<!-- <th>Select</th> -->
										<th width="20%" style="text-align:center;">KODE</th>
										<th width="30%" style="text-align:center;">NAMA PRODUK/TINDAKAN</th>
										<th width="15%" style="text-align:center;">BANYAK</th>
										<th width="30%" style="text-align:center;">ATURAN PAKAI</th>
										<th width="5%" style="text-align:center;padding-right:1%;">TERIMA</th>
									</tr>
								</thead>
								';
								
								foreach($list_result as $row_d_penjualan)
								{
									//echo'<tr id="tr_'.$no.'">';
									$isProdukCur = $row_d_penjualan->isProduk;
									if($isProdukCur != $isProdukOld)
									{
										echo'<tr><td colspan="4" style="color:red;">LIST '.$row_d_penjualan->isProduk.' DIPAKAI </td></tr>';
										
										$isProdukOld = $row_d_penjualan->isProduk;
									}
									
									
									
									echo'<tr id="tr_list_transaksi-'.$row_d_penjualan->id_produk.'-'.$row_d_penjualan->id_h_diskon.'" >';
										echo'<td style="text-align:left;border:black 0.5px solid;padding:0.5%;">'.$row_d_penjualan->kode_produk.'</td>';
										echo'<td style="text-align:left;border:black 0.5px solid;padding:0.5%;">'.$row_d_penjualan->nama_produk.'</td>';
										echo'<td style="text-align:center;border:black 0.5px solid;padding:0.5%;">'.$row_d_penjualan->jumlah.' '.$row_d_penjualan->satuan_jual.'</td>';
										echo'<td style="text-align:left;border:black 0.5px solid;padding:0.5%;">'.$row_d_penjualan->aturan_minum.' </td>';
										
										if(($row_d_penjualan->isTerima == '1') && ($row_d_penjualan->isProduk == 'PRODUK'))
										{
											echo'<td style="text-align:center;border:black 0.5px solid;padding:0.5%;"> 
												<input type="checkbox" name="ready" id="ready" checked="checked" disabled="true" readonly>
											</td>';
										}
										else
										{
											echo'<td style="text-align:center;border:black 0.5px solid;padding:0.5%;"> </td>';
										}
										
									echo'</tr>';
									
									
									$no++;
								}
								
						}
						
						
						
						echo'</table>';
						/*MASUKAN detail_penjualan*/
					echo'
										</td>
									</tr>
								</table>
					';
					
					echo'
								</div>
								
								<!--<div class="timeline-footer">
									<a class="btn btn-warning btn-flat btn-xs" onclick="tampilkan_gambar('.$row->id_h_penjualan.')">Tampilkan Foto</a>
								</div>-->
							</div>
						</li>
					<!-- SATU KOLOM REKAM MEDSI -->
					';
				}
			}
			else
			{
				echo'<center><font style="color:red;font-weight:bold;">Belum ada rekam medis</font></center>';
			}
		/*REKAM MEDIS PASIEN*/
	}

	function hapus_h_penjulan()
	{
		//HAPUS GAMBAR TRANSAKSI
			$IMG_GRUP = 'penjualan'; //$this->uri->segment(2,0);
			$ID = $_POST['id_h_penjualan'];//$this->uri->segment(3,0);
			// $IMG_ID = $this->uri->segment(4,0);
			$cari = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
										AND group_by = '".$this->uri->segment(2,0)."'
										AND id = '".$this->uri->segment(3,0)."'
										";
										
			$hasil_cek = $this->M_gl_images->get_images_cari($cari);
			if(!empty($hasil_cek))
			{
				//$hasil_cek = $hasil_cek->row();
				$this->M_gl_pengaturan->do_upload_global(base_url().'/'.$hasil_cek->img_url,"",$hasil_cek->img_file);
				$this->M_gl_images->hapus('id_images',$IMG_ID);
			}
		//HAPUS GAMBAR TRANSAKSI
		
		//HAPUS D PENJUALAN
			$cari_d_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' ";
			$this->M_gl_h_penjualan->hapus_d_penjualan_with_cari($cari_d_penjualan);
		//HAPUS D PENJUALAN
		
		//HAPUS D PENJUALAN BAYAR
			$this->M_gl_h_penjualan->hapus_d_penjualan_bayar($cari_d_penjualan);
		//HAPUS D PENJUALAN BAYAR
		
		//HAPUS D PENJUALAN FEE
			$this->M_gl_h_penjualan->hapus_d_penjualan_fee_cari($cari_d_penjualan);
		//HAPUS D PENJUALAN FEE
		
		//UBAH STATUS H PENJUALAN
			$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja
					(
						$this->session->userdata('ses_kode_kantor')
						,$_POST['id_h_penjualan'] //$id_h_penjualan
						,'HAPUS' //$status
						,0 //$pajak
						,0 //$diskon
						,'' //$ket_penjualan2
					);
		//UBAH STATUS H PENJUALAN
			
			
			
		/* CATAT AKTIFITAS EDIT*/
			if($this->session->userdata('catat_log') == 'Y')
			{
				$this->M_gl_log->simpan_log
				(
					$this->session->userdata('ses_id_karyawan'),
					'UPDATE',
					'Melakukan pengahpusan transaksi dengan no faktur '.$_POST['no_faktur'],
					$this->M_gl_pengaturan->getUserIpAddr(),
					gethostname(),
					$this->session->userdata('ses_kode_kantor')
				);
			}
		/* CATAT AKTIFITAS EDIT*/
		echo"TERHAPUS";
	}

	function list_d_penjualan_ori()
	{
		if((!empty($_POST['dari'])) && ($_POST['dari']!= "")  )
		{
			$dari = $_POST['dari'];
			$sampai = $_POST['sampai'];
		}
		else
		{
			$dari = date("Y-m-d");
			$sampai = date("Y-m-d");
		}
		
		$cari_deft = "
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND COALESCE(B.sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
					AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."' ";
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "
					WHERE
					(
						COALESCE(BBB.nama_produk,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(BBB.kode_produk,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
					)";
		}
		else
		{
			$cari = "";
		}
		$order_by = "ORDER BY COALESCE(BBB.isProduk,'') ASC, COALESCE(BBB.nama_produk,'') ASC ,COALESCE(BBB.kode_produk,'') ASC";
		
		$list_laporan_d_penjualan = $this->M_gl_lap_penjualan->list_d_penjualan($cari_deft,$cari,$order_by);
		
		echo'<div class="box-body table-responsive no-padding">';
			if(!empty($list_laporan_d_penjualan))
			{
				//SUMMARY
					$sum_laporan_d_penjualan = $this->M_gl_lap_penjualan->sum_d_penjualan($cari_deft,$cari);
					//$sum_laporan_d_penjualan = $sum_laporan_d_penjualan->row();
				//SUMMARY
									echo'<table width="100%" id="example2" class="table table-hover hoverTable">';
										echo '<thead>';
											
											$list_sum_result = $sum_laporan_d_penjualan->result();
											foreach($list_sum_result as $row_sum)
											{
												echo '<tr> <td> '.$row_sum->ISPRODUK.'</td> <td>:</td> <td>'.number_format($row_sum->CNT,1,'.',',').'</td> <td>'.number_format($row_sum->TOTAL,1,'.',',').'</td> </tr>';
											}
										
										echo'
												<tr style="background-color:green;">';
													echo '<th style="text-align:center;" width="5%">NO</th>';
													echo '<th style="text-align:center;" width="40%">PRODUK/JASA</th>';
													echo '<th style="text-align:center;" width="10%">TRANSAKSI</th>';
													echo '<th style="text-align:center;" width="10%">BANYAK TERJUAL</th>';
													echo '<th style="text-align:center;" width="15%">HARGA</th>';
													echo '<th style="text-align:center;"  width="20%">SUBTOTAL</th>';
										echo '</tr>
											</thead>';
										$list_result = $list_laporan_d_penjualan->result();
										
										
										$no =1;
										$isJasa_old = "";
										$isJasa_cur = "";
										$jum_transaksi = 0;
										$cnt_transaksi = 0;
										$subtotal = 0;
										echo '<tbody>';
										
										foreach($list_result as $row)
										{
											//SUBTOTAL
												$isJasa_cur = $row->ISPRODUK;
												
												if(($isJasa_cur != $isJasa_old) && ($isJasa_old != ""))
												{
													echo '<tr style="font-weight:bold; background-color:yellow;"> 
														<td></td> 
														<td style="text-align:right;"> '.$isJasa_old.'</td> 
														<td style="text-align:right;">'.number_format($cnt_transaksi,1,'.',',').'</td> 
														<td style="text-align:right;">'.number_format($jum_transaksi,1,'.',',').'</td>
														<td></td> 
														<td style="text-align:right;">'.number_format($subtotal,1,'.',',').'</td>
													</tr>';

													$isJasa_old = $isJasa_cur;
													
													$jum_transaksi = 0;
													$cnt_transaksi = 0;
													$subtotal = 0;
												}
												
												$jum_transaksi = $jum_transaksi + $row->JUMLAH;
												$cnt_transaksi = $cnt_transaksi + $row->CNT;
												$subtotal = $subtotal + $row->SUBTOTAL;
												
											//SUBTOTAL
											
											echo'<tr>';
												echo'<td>'.$no.'</td>';
												
												//<b><font style="color:red;">DATA TRANSKASI </font></b>
												echo '<td>
													
													<b>KODE PRODUK: </b>'.$row->KODE_PRODUK.'
													<br/> <b>NAMA PRODUK : </b>'.$row->NAMA_PRODUK.'
													<br/> <b>JENIS : </b>'.$row->ISPRODUK.'
													<br/>
													<a href="javascript:void(0)" class="btn btn-success btn-sm btn-flat" data-toggle="modal" data-target="#myModal_pengeluaran_produk" id="DET_KELUAR-'.$row->id_produk.'"  onclick="detail_keluar_produk(this)" title = "Detail Pengeluaran '.$row->NAMA_PRODUK.'" alt = "Detail Pengeluaran '.$row->NAMA_PRODUK.'">DETAIL '.strtoupper($row->NAMA_PRODUK).'</a>
													
												</td>';
												
												echo '<td style="text-align:right;">'.number_format($row->CNT,1,'.',',').' </td>';
												echo '<td style="text-align:right;">'.number_format($row->JUMLAH,1,'.',',').' '.$row->SATUAN.'</td>';
												echo '<td style="text-align:right;">'.number_format($row->HARGA,1,'.',',').' </td>';
												echo '<td style="text-align:right;">'.number_format($row->SUBTOTAL,1,'.',',').' </td>';
												
												
											echo'</tr>';
											
											//SUBTOTAL
												$isJasa_old = $row->ISPRODUK;
											//SUBTOTAL
											
											/*
											$nominal_transaksi = $nominal_transaksi + $row->SUBTOTAL;
											$nominal_pembayaran_cash = $nominal_pembayaran_cash + $row->BAYAR_CASH;
											$nominal_pembayaran_bank = $nominal_pembayaran_bank + $row->BAYAR_BANK;
											*/
											$no++;
										}
										
										//SUBTOTAL
										echo '<tr style="font-weight:bold; background-color:yellow;"> 
											<td></td> 
											<td style="text-align:right;"> '.$isJasa_old.'</td> 
											<td style="text-align:right;">'.number_format($cnt_transaksi,1,'.',',').'</td> 
											<td style="text-align:right;">'.number_format($jum_transaksi,1,'.',',').'</td>
											<td></td> 
											<td style="text-align:right;">'.number_format($subtotal,1,'.',',').'</td>
										</tr>';
										//SUBTOTAL
										
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
						
			echo'
		</div><!-- /.box-body -->
		';
		
	}

	function list_d_penjualan()
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
				
				
				$cari_deft = "
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND COALESCE(B.sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
					-- AND COALESCE(B.sts_penjualan,'') = 'SELESAI'
					AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."' ";
					
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE
							(
								COALESCE(BBB.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(BBB.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "";
				}
				$order_by = "ORDER BY COALESCE(BBB.isProduk,'') ASC, COALESCE(BBB.nama_produk,'') ASC ,COALESCE(BBB.kode_produk,'') ASC";
				
				
				//UNTUK AKUMULASI INFO
					$jum_row = $this->M_gl_lap_penjualan->count_d_penjualan($cari_deft,$cari)->JUMLAH;
				//UNTUK AKUMULASI INFO
				
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				//$config['first_url'] = site_url('gl-admin-laporan-transaksi?'.http_build_query($_GET));
				$config['first_url'] = site_url('gl-admin-laporan-list-detail-penjualan?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-laporan-list-detail-penjualan/');
				$config['total_rows'] = $jum_row;
				$config['uri_segment'] = 2;	
				//$config['uri_segment'] = $_GET['var_offset'];	
				$config['per_page'] = 30;
				//$config['per_page'] = 10000;
				$config['num_links'] = 2;
				//$config['num_links'] = 20;
				//$config['suffix'] = '?' . http_build_query($_GET, '', "&");
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
				
				$list_laporan_d_penjualan = $this->M_gl_lap_penjualan->list_d_penjualan($cari_deft,$cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				
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
				
				//SUMMARY
					$sum_laporan_d_penjualan = $this->M_gl_lap_penjualan->sum_d_penjualan($cari_deft,$cari);
					//$sum_laporan_h_penjualan = $sum_laporan_h_penjualan->row();
				//SUMMARY
				
				$msgbox_title = " Laporan Transaksi ";
				
				$data = array('page_content'=>'gl_admin_lap_d_penjualan','halaman'=>$halaman, 'sum_pesan' => $sum_pesan,'msgbox_title' => $msgbox_title,'list_laporan_d_penjualan' => $list_laporan_d_penjualan, 'sum_laporan_d_penjualan' => $sum_laporan_d_penjualan);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	function list_d_produk_keluar()
	{
		if((!empty($_POST['dari'])) && ($_POST['dari']!= "")  )
		{
			$dari = $_POST['dari'];
			$sampai = $_POST['sampai'];
		}
		else
		{
			$dari = date("Y-m-d");
			$sampai = date("Y-m-d");
		}
		
		if((!empty($_POST['harga'])) && ($_POST['harga']!= "")  )
		{
			$harga = $_POST['harga'];
		}
		else
		{
			$harga = 0;
		}
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = 
			"
				WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
				AND A.id_produk = '".$_POST['id_produk']."'
				AND COALESCE(B.sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
				AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
				AND CASE WHEN ".$harga." > 0 THEN
					harga = ".$harga."
				ELSE
					harga > 0
				END
			
				AND (
						COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(B.nama_costumer,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(B.no_costmer,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						
						OR COALESCE(D.no_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(D.nama_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						
						OR COALESCE(E.no_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(E.nama_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
					)
			";		
		}
		else
		{
			$cari = 
			"
				WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
				AND A.id_produk = '".$_POST['id_produk']."'
				AND COALESCE(B.sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
				AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
				AND CASE WHEN ".$harga." > 0 THEN
					harga = ".$harga."
				ELSE
					harga > 0
				END
			";
		}
		$order_by = "ORDER BY COALESCE(B.tgl_ins,'') DESC";
		
		$list_laporan_d_pengeluaran_produk = $this->M_gl_lap_penjualan->list_d_pengeluaran_produk($cari,$order_by);
		
		echo'<div class="box-body table-responsive no-padding">';
			if(!empty($list_laporan_d_pengeluaran_produk))
			{
				echo'<table width="100%" id="example2" class="table table-hover hoverTable">';
					echo '<thead>';	
					
					echo'
							<tr style="background-color:green;">';
								echo '<th style="text-align:center;" width="5%">NO</th>';
								echo '<th style="text-align:center;" width="25%">DATA TRANSAKSI</th>';
								echo '<th style="text-align:center;" width="25%">DOKTER</th>';
								echo '<th style="text-align:center;" width="15%">BANYAK TERJUAL</th>';
								echo '<th style="text-align:center;" width="15%">HARGA</th>';
								echo '<th style="text-align:center;" width="15%">SUBTOTAL</th>';
					echo '</tr>
						</thead>';
					$list_result = $list_laporan_d_pengeluaran_produk->result();
					
					
					$no =1;
					/*
					$isJasa_old = "";
					$isJasa_cur = "";
					$jum_transaksi = 0;
					$cnt_transaksi = 0;
					$subtotal = 0;
					*/
					$subtotal = 0;
					
					echo '<tbody>';
					
					foreach($list_result as $row)
					{
						/*
						//SUBTOTAL
							$isJasa_cur = $row->ISPRODUK;
							
							if(($isJasa_cur != $isJasa_old) && ($isJasa_old != ""))
							{
								echo '<tr style="font-weight:bold; background-color:yellow;"> 
									<td></td> 
									<td style="text-align:right;"> '.$isJasa_old.'</td> 
									<td style="text-align:right;">'.number_format($cnt_transaksi,1,'.',',').'</td> 
									<td style="text-align:right;">'.number_format($jum_transaksi,1,'.',',').'</td>
									<td></td> 
									<td style="text-align:right;">'.number_format($subtotal,1,'.',',').'</td>
								</tr>';

								$isJasa_old = $isJasa_cur;
								
								$jum_transaksi = 0;
								$cnt_transaksi = 0;
								$subtotal = 0;
							}
							
							$jum_transaksi = $jum_transaksi + $row->JUMLAH;
							$cnt_transaksi = $cnt_transaksi + $row->CNT;
							$subtotal = $subtotal + $row->SUBTOTAL;	
						//SUBTOTAL
						*/
						
						
						echo'<tr>';
							echo'<td>'.$no.'</td>';
							
							//<b><font style="color:red;">DATA TRANSKASI </font></b>
							echo '<td>
								
								<b>FAKTUR: </b>'.$row->no_faktur.'
								<br/>'.$row->tgl_h_penjualan.'
								<br/> <b>PASIEN : </b>'.$row->nama_costumer.'
								<br/> <b>JENIS : </b>'.$row->type_h_penjualan.'
							</td>';
							
							echo '<td>
								
								<b>KONSUL: </b>'.$row->NAMA_DOKTER_KONSUL.'
								<br/> <b>TINDAKAN : </b>'.$row->NAMA_DOKTER_TINDAKAN.'
							</td>';
							
							
							echo '<td style="text-align:right;">'.number_format($row->jumlah,1,'.',',').' '.$row->satuan_jual.'</td>';
							echo '<td style="text-align:right;">'.number_format($row->harga,1,'.',',').' </td>';
							echo '<td style="text-align:right;">'.number_format($row->harga * $row->jumlah,1,'.',',').' </td>';
							
							
						echo'</tr>';
						
						//SUBTOTAL
							//$isJasa_old = $row->ISPRODUK;
						//SUBTOTAL
						
						/*
						$nominal_transaksi = $nominal_transaksi + $row->SUBTOTAL;
						$nominal_pembayaran_cash = $nominal_pembayaran_cash + $row->BAYAR_CASH;
						$nominal_pembayaran_bank = $nominal_pembayaran_bank + $row->BAYAR_BANK;
						*/
						
						$subtotal = $subtotal + ($row->harga * $row->jumlah);
						$no++;
					}
					
					
					//SUBTOTAL
					echo '<tr style="font-weight:bold; background-color:yellow;"> 
						<td colspan="5">TOTAL</td> 
						<td style="text-align:right;">'.number_format($subtotal,1,'.',',').'</td>
					</tr>';
					//SUBTOTAL
					
					
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
						
			echo'
		</div><!-- /.box-body -->
		';
		
	}

	function list_d_penjualan_fee()
	{
		if((!empty($_POST['dari'])) && ($_POST['dari']!= "")  )
		{
			$dari = $_POST['dari'];
			$sampai = $_POST['sampai'];
		}
		else
		{
			$dari = date("Y-m-d");
			$sampai = date("Y-m-d");
		}
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
					AND 
					(
						COALESCE(D.nama_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(D.no_karyawan,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(D.isDokter,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
					)";
		}
		else
		{
			$cari = "
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
					
					";
		}
		$order_by = "ORDER BY A3.ISDOKTER ASC, A3.NAMA_KARYAWAN ASC";
		
		$list_laporan_d_penjualan_fee = $this->M_gl_lap_penjualan->list_akumulasi_laporan_fee($cari,$order_by);
		
		echo'<div class="box-body table-responsive no-padding">';
			if(!empty($list_laporan_d_penjualan_fee))
			{
				
									echo'<table width="100%" id="example2" class="table table-hover hoverTable">';
										echo '<thead>';
											
										echo'
												<tr style="background-color:green;">';
													echo '<th style="text-align:center;" width="5%">NO</th>';
													echo '<th style="text-align:center;" width="20%">NAMA</th>';
													echo '<th style="text-align:center;" width="10%">PENANGANAN</th>';
													echo '<th style="text-align:center;" width="10%">PASIEN LAMA</th>';
													echo '<th style="text-align:center;" width="10%">PASIEN BARU </th>';
													echo '<th style="text-align:center;" width="10%">BUAT RESEP</th>';
													echo '<th style="text-align:center;"  width="20%">SUBTOTAL</th>';
													echo '<th style="text-align:center;" width="15%">AKSI </th>';
										echo '</tr>
											</thead>';
										$list_result = $list_laporan_d_penjualan_fee->result();
										
										
										$no =1;
										
										/*
										$isJasa_old = "";
										$isJasa_cur = "";
										$jum_transaksi = 0;
										$cnt_transaksi = 0;
										$subtotal = 0;
										*/
										
										$grandTotal = 0;
										
										echo '<tbody>';
										
										foreach($list_result as $row)
										{
											//SUBTOTAL
												/*
												$isJasa_cur = $row->ISPRODUK;
												
												if(($isJasa_cur != $isJasa_old) && ($isJasa_old != ""))
												{
													echo '<tr style="font-weight:bold; background-color:yellow;"> 
														<td></td> 
														<td style="text-align:right;"> '.$isJasa_old.'</td> 
														<td style="text-align:right;">'.number_format($cnt_transaksi,1,'.',',').'</td> 
														<td style="text-align:right;">'.number_format($jum_transaksi,1,'.',',').'</td>
														<td></td> 
														<td style="text-align:right;">'.number_format($subtotal,1,'.',',').'</td>
													</tr>';

													$isJasa_old = $isJasa_cur;
													
													$jum_transaksi = 0;
													$cnt_transaksi = 0;
													$subtotal = 0;
												}
												
												$jum_transaksi = $jum_transaksi + $row->JUMLAH;
												$cnt_transaksi = $cnt_transaksi + $row->CNT;
												$subtotal = $subtotal + $row->SUBTOTAL;
												*/
											//SUBTOTAL
											
											echo'<tr>';
												echo'<td>'.$no.'</td>';
												
												echo'<td style="text-align:left;">
													'.$row->NAMA_KARYAWAN.'
													<br/> ('.$row->ISDOKTER.')
													
												</td> ';
												echo'<td style="text-align:right;">'.number_format($row->CNT,1,'.',',').'</td>  ';
												echo'<td style="text-align:right;">'.number_format($row->PASIEN_LAMA,1,'.',',').'</td>  ';
												echo'<td style="text-align:right;">'.number_format($row->PASIEN_BARU,1,'.',',').'</td>  ';
												echo'<td style="text-align:right;">'.number_format($row->RESEP,1,'.',',').'</td>  ';
												echo'<td style="text-align:right;">'.number_format($row->TOTAL,1,'.',',').'</td>  ';
												
												echo
												'
													<td>
														<a href="javascript:void(0)" class="btn btn-success btn-block btn-flat" data-toggle="modal" data-target="#myModal_detail_fee" id="DET_FEE-'.$row->ID_KARYAWAN.'"  onclick="detail_penjualan_fee(this)" title = "Detail Fee '.$row->NAMA_KARYAWAN.'" alt = "Detail Fee '.$row->NAMA_KARYAWAN.'">DETAIL '.strtoupper($row->NAMA_KARYAWAN).'</a>
													</td>
												';
														
											echo'</tr>';
											
											//SUBTOTAL
												//$isJasa_old = $row->ISPRODUK;
												$grandTotal = $grandTotal + $row->TOTAL;
											//SUBTOTAL
											
											/*
											$nominal_transaksi = $nominal_transaksi + $row->SUBTOTAL;
											$nominal_pembayaran_cash = $nominal_pembayaran_cash + $row->BAYAR_CASH;
											$nominal_pembayaran_bank = $nominal_pembayaran_bank + $row->BAYAR_BANK;
											*/
											$no++;
										}
										
										//SUBTOTAL
										/*
										echo '<tr style="font-weight:bold; background-color:yellow;"> 
											<td></td> 
											<td style="text-align:right;"> '.$isJasa_old.'</td> 
											<td style="text-align:right;">'.number_format($cnt_transaksi,1,'.',',').'</td> 
											<td style="text-align:right;">'.number_format($jum_transaksi,1,'.',',').'</td>
											<td></td> 
											<td style="text-align:right;">'.number_format($subtotal,1,'.',',').'</td>
										</tr>';
										*/
										
										echo'
											<tr>
												<td colspan="6" style="text-align:right;font-weight:bold;">GRAND TOTAL</td>
												<td colspan="1" style="text-align:right;font-weight:bold;">'.number_format($grandTotal,1,'.',',').'</td>
												<td></td>
											</tr>
										';
										//SUBTOTAL
										
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
						
			echo'
		</div><!-- /.box-body -->
		';
	}
	
	function list_d_penjualan_row()
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
				
				if((!empty($_GET['cari_nama_kat_costumer'])) && ($_GET['cari_nama_kat_costumer']!= "")  )
				{
					$cari_nama_kat_costumer = "AND COALESCE(B.jenis_costumer,'') = '".str_replace("'","",$_GET['cari_nama_kat_costumer'])."'";
				}
				else
				{
					$cari_nama_kat_costumer = "";
				}
				
				if($this->session->userdata('ses_gnl_isPajakAktif')  == "Y") 
				{
					$isPajakaON = "AND COALESCE(C.isNPKP,'') <> 'TIDAK'";
				}
				else
				{
					$isPajakaON = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND COALESCE(C.isProduk,'') IN ('PRODUK','JASA')
							AND A.id_d_penerimaan = ''  -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							".$cari_nama_kat_costumer."
							".$isPajakaON."
							AND 
							(
								COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(B.no_costmer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(B.nama_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(E.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
							
					$cari_h = "
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
								
								AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
								AND C.nominal > 0
								AND 
								(
									COALESCE(A.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(A.no_costmer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(A.nama_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
							";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND COALESCE(C.isProduk,'') IN ('PRODUK','JASA')
							AND A.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							".$cari_nama_kat_costumer."
							".$isPajakaON."
						";
						
					$cari_h = "
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
								
								AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
								AND C.nominal > 0
							";
				}
				$order_by = "ORDER BY B.no_faktur DESC,A.isProduk ASC";
				
				
				
				/*
				$cari_deft = "
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					-- AND COALESCE(B.sts_penjualan,'') IN ('SELESAI','PEMBAYARAN')
					AND COALESCE(B.sts_penjualan,'') = 'SELESAI'
					AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."' ";
					
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE
							(
								COALESCE(BBB.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(BBB.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "";
				}
				$order_by = "ORDER BY COALESCE(BBB.isProduk,'') ASC, COALESCE(BBB.nama_produk,'') ASC ,COALESCE(BBB.kode_produk,'') ASC";
				*/
				
				
				//UNTUK AKUMULASI INFO
					$jum_row = 1000000; //$this->M_gl_lap_penjualan->count_laporan_detail($cari)->JUMLAH;
				//UNTUK AKUMULASI INFO
				
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				//$config['first_url'] = site_url('gl-admin-laporan-transaksi?'.http_build_query($_GET));
				$config['first_url'] = site_url('gl-admin-laporan-list-detail-penjualan-row?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-laporan-list-detail-penjualan-row/');
				$config['total_rows'] = $jum_row;
				$config['uri_segment'] = 2;	
				//$config['uri_segment'] = $_GET['var_offset'];	
				$config['per_page'] = 10000;
				//$config['per_page'] = 10000;
				$config['num_links'] = 2;
				//$config['num_links'] = 20;
				//$config['suffix'] = '?' . http_build_query($_GET, '', "&");
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
				
				if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
				{
					$cari_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ORDER BY nama_kat_costumer ASC;";
					$list_kat_costumer = $this->M_gl_kat_costumer->get_kat_costumer_cari($cari_kat_costumer);
					$list_laporan_d_penjualan_row = $this->M_gl_lap_penjualan->list_laporan_detail_for_toko($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				}
				else
				{
					$list_laporan_d_penjualan_row = $this->M_gl_lap_penjualan->list_laporan_detail($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				}
				
				
				/*
				$cari_dokter = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND isAKtif = 'DITERIMA' AND isDokter = 'DOKTER'";
				$list_dokter = $this->M_gl_karyawan->get_karyawan_cari($cari_dokter);
				
				$cari_therapist = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND isAKtif = 'DITERIMA' AND isDokter = 'THERAPIST'";
				$list_therapist = $this->M_gl_karyawan->get_karyawan_cari($cari_therapist);
				*/
				
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
				
				//SUMMARY
					$sum_laporan_h_penjualan = $this->M_gl_lap_penjualan->count_h_penjualan($cari_h);
					//$sum_laporan_h_penjualan = $sum_laporan_h_penjualan->row();
				//SUMMARY
				
				//SUMMARY PRODUK isReport
					$sum_laporan_d_penjualan_produk_isReport = $this->M_gl_lap_penjualan->sum_laporan_d_penjualan_produk_isRport($this->session->userdata('ses_kode_kantor'),$dari,$sampai);
				//SUMMARY PRODUK isReport
				
				//GET KODE AKUN KHUSUS
				
				$cari_kode_akun_kas_all = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND target = 'KAS' AND id_bank = '' ";
				$get_kode_akun_kas_tunai = $this->M_gl_kode_akun->get_kode_akun_by_cari($cari_kode_akun_kas_all);
				if(!empty($get_kode_akun_kas_tunai))
				{
					$get_kode_akun_kas_tunai = $get_kode_akun_kas_tunai->row();
					$get_kode_akun_kas_tunai = $get_kode_akun_kas_tunai->kode_akun;
				}
				else
				{
					$get_kode_akun_kas_tunai = '';
				}
				
				$cari_kode_akun_bank_all = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND target = 'BANK' AND id_bank = '' ";
				$get_kode_akun_bank_all = $this->M_gl_kode_akun->get_kode_akun_by_cari($cari_kode_akun_bank_all);
				if(!empty($get_kode_akun_bank_all))
				{
					$get_kode_akun_bank_all = $get_kode_akun_bank_all->row();
					$get_kode_akun_bank_all = $get_kode_akun_bank_all->kode_akun;
				}
				else
				{
					$get_kode_akun_bank_all = '';
				}
				
				//GET KODE AKUN KHUSUS
				
				$msgbox_title = " Laporan Transaksi Detail ";
				
				if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
				{
					$data = array('page_content'=>'gl_admin_lap_d_penjualan_row','halaman'=>$halaman, 'sum_pesan' => $sum_pesan,'msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_row' => $list_laporan_d_penjualan_row, 'sum_laporan_h_penjualan' => $sum_laporan_h_penjualan,'sum_laporan_d_penjualan_produk_isReport' => $sum_laporan_d_penjualan_produk_isReport
					,'get_kode_akun_kas_tunai' => $get_kode_akun_kas_tunai
					,'get_kode_akun_bank_all' => $get_kode_akun_bank_all
					,'list_kat_costumer' => $list_kat_costumer
					);
					$this->load->view('admin/container',$data);
				}
				else
				{
					$data = array('page_content'=>'gl_admin_lap_d_penjualan_row','halaman'=>$halaman, 'sum_pesan' => $sum_pesan,'msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_row' => $list_laporan_d_penjualan_row, 'sum_laporan_h_penjualan' => $sum_laporan_h_penjualan,'sum_laporan_d_penjualan_produk_isReport' => $sum_laporan_d_penjualan_produk_isReport
					,'get_kode_akun_kas_tunai' => $get_kode_akun_kas_tunai
					,'get_kode_akun_bank_all' => $get_kode_akun_bank_all
					);
					$this->load->view('admin/container',$data);
				}
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_statistik_pelanggan()
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
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = ''  -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
							(
								COALESCE(A.no_costmer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(A.nama_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR SUBSTRING_INDEX(COALESCE(D.wil_prov,''),'|',-1) LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR SUBSTRING_INDEX(COALESCE(D.wil_kabkot,''),'|',-1) LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR SUBSTRING_INDEX(COALESCE(D.wil_kec,''),'|',-1) LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR SUBSTRING_INDEX(COALESCE(D.wil_des,''),'|',-1) LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				$order_by = "ORDER BY A.no_faktur ASC,B.tgl_ins DESC";
				$limit = "LIMIT 0,1000000";
				$list_laporan_d_penjualan_statistik = $this->M_gl_lap_penjualan->list_statistik_penjualan_pelanggan($cari);
				
				
				$msgbox_title = " Statistik Penjualan Berdasarkan Pelanggan ";
				
				$data = array('page_content'=>'gl_admin_statistik_penjualan_pelanggan','msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_statistik' => $list_laporan_d_penjualan_statistik
				);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_statistik_pelanggan_by_daerah()
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
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = ''  -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
							(
								SUBSTRING_INDEX(COALESCE(D.wil_prov,''),'|',-1) LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR SUBSTRING_INDEX(COALESCE(D.wil_kabkot,''),'|',-1) LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR SUBSTRING_INDEX(COALESCE(D.wil_kec,''),'|',-1) LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR SUBSTRING_INDEX(COALESCE(D.wil_des,''),'|',-1) LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				$order_by = "ORDER BY A.no_faktur ASC,B.tgl_ins DESC";
				$limit = "LIMIT 0,1000000";
				$list_laporan_d_penjualan_statistik_per_wilayah = $this->M_gl_lap_penjualan->list_statistik_penjualan_per_daerah($cari);
				
				
				$msgbox_title = " Statistik Penjualan Berdsarkan Wilayah ";
				
				$data = array('page_content'=>'gl_admin_statistik_penjualan_per_wilayah','msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_statistik_per_wilayah' => $list_laporan_d_penjualan_statistik_per_wilayah
				);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_statistik_pelanggan_produk()
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
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = ''  -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
							(
								COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				$order_by = "ORDER BY A.no_faktur ASC,B.tgl_ins DESC";
				$limit = "LIMIT 0,1000000";
				$list_laporan_d_penjualan_statistik_produk = $this->M_gl_lap_penjualan->list_statistik_penjualan_produk($cari);
				
				
				$msgbox_title = " Statistik Penjualan Produk ";
				
				$data = array('page_content'=>'gl_admin_statistik_penjualan_produk','msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_statistik_produk' => $list_laporan_d_penjualan_statistik_produk
				);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_statistik_penjualan_produk_per_supplier()
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
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
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
				
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = ''  -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
							(
								COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.pencipta,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.produksi_oleh,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				$order_by = "ORDER BY A.no_faktur ASC,B.tgl_ins DESC";
				$limit = "LIMIT 0,1000000";
				$list_laporan_d_penjualan_statistik_produk = $this->M_gl_lap_penjualan->list_statistik_penjualan_produk_per_supplier($cari);
				
				
				$msgbox_title = " Statistik Penjualan Produk Per Supplier";
				
				$data = array('page_content'=>'gl_admin_statistik_penjualan_produk_per_supplier','msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_statistik_produk' => $list_laporan_d_penjualan_statistik_produk
				);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_statistik_pelanggan_produk_by_wilayah()
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
				if((!empty($_GET['kode_kantor'])) && ($_GET['kode_kantor']!= "")  )
				{
					$kode_kantor = $_GET['kode_kantor'];
				}
				else
				{
					$kode_kantor = $this->session->userdata('ses_kode_kantor');
				}
				
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = ''  -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND SUBSTRING_INDEX(COALESCE(D.wil_prov,''),'|',-1) <> '' 
							AND SUBSTRING_INDEX(COALESCE(D.wil_kabkot,''),'|',-1) <> ''
							AND 
							(
								COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR SUBSTRING_INDEX(COALESCE(D.wil_prov,''),'|',-1) LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR SUBSTRING_INDEX(COALESCE(D.wil_kabkot,''),'|',-1) LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR SUBSTRING_INDEX(COALESCE(D.wil_kec,''),'|',-1) LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$kode_kantor."' 
							
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND SUBSTRING_INDEX(COALESCE(D.wil_prov,''),'|',-1) <> '' 
							AND SUBSTRING_INDEX(COALESCE(D.wil_kabkot,''),'|',-1) <> ''
						";
				}
				$order_by = "ORDER BY A.no_faktur ASC,B.tgl_ins DESC";
				$limit = "LIMIT 0,1000000";
				$list_laporan_d_penjualan_statistik_produk = $this->M_gl_lap_penjualan->list_statistik_penjualan_produk_by_daerah($cari);
				
				
				$msgbox_title = " Statistik Penjualan Produk ";
				
				$data = array('page_content'=>'gl_admin_statistik_penjualan_produk_per_daerah','msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_statistik_produk' => $list_laporan_d_penjualan_statistik_produk
				);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	function list_pengeluaran_produk()
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
				if((!empty($_GET['cari_nama_kat_costumer'])) && ($_GET['cari_nama_kat_costumer']!= "")  )
				{
					$cari_nama_kat_costumer = "AND COALESCE(D.nama_kat_costumer,'') = '".str_replace("'","",$_GET['cari_nama_kat_costumer'])."'";
				}
				else
				{
					$cari_nama_kat_costumer = "";
				}
				
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
				
				if($this->session->userdata('ses_gnl_isPajakAktif')  == "Y") 
				{
					$isPajakaON = "AND COALESCE(C.isNPKP,'') <> 'TIDAK'";
				}
				else
				{
					$isPajakaON = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = ''  -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							".$cari_nama_kat_costumer."
							".$isPajakaON."
							AND 
							(
								COALESCE(A.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(A.no_costmer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(A.nama_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							".$cari_nama_kat_costumer."
							".$isPajakaON."
						";
				}
				$order_by = "ORDER BY A.no_faktur ASC,B.tgl_ins DESC";
				$limit = "LIMIT 0,1000000";
				
				$list_laporan_d_penjualan_pengeluaran_produk = $this->M_gl_lap_penjualan->list_pengeluaran($cari,$order_by,$limit);
				
				//if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
				//{
					$cari_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ORDER BY nama_kat_costumer ASC;";
					$list_kat_costumer = $this->M_gl_kat_costumer->get_kat_costumer_cari($cari_kat_costumer);
				//}
				
				$msgbox_title = " Laporan Pengeluaran Produk/Jasa ";
				
				$data = array('page_content'=>'gl_admin_lap_d_penjualan_pengeluaran_produk','msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_pengeluaran_produk' => $list_laporan_d_penjualan_pengeluaran_produk,'list_kat_costumer'=>$list_kat_costumer
				);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_penjualan_by_kasir()
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE SUDAH DI SUB QUERY
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
							(
								COALESCE(D.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE SUDAH DI SUB QUERY
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				$order_by = "ORDER BY (SUM(TUNAI) + SUM(NONTUNAI)) DESC";
				$limit = "LIMIT 0,1000000";
				$list_penjualan_by_kasir = $this->M_gl_lap_penjualan->list_penjualan_by_kasir($cari,$order_by,$limit);
				
				
				$msgbox_title = " Laporan Transaksi/Penjualan Per Kasir ";
				
				$data = array('page_content'=>'gl_admin_lap_h_penjualan_per_kasir','msgbox_title' => $msgbox_title,'list_penjualan_by_kasir' => $list_penjualan_by_kasir
				);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	function list_penjualan_by_sales()
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE SUDAH DI SUB QUERY
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
							(
								COALESCE(D.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE SUDAH DI SUB QUERY
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				$order_by = "ORDER BY (SUM(TUNAI) + SUM(NONTUNAI)) DESC";
				$limit = "LIMIT 0,1000000";
				$list_penjualan_by_sales = $this->M_gl_lap_penjualan->list_penjualan_by_sales($cari,$order_by,$limit);
				
				
				$msgbox_title = " Laporan Transaksi/Penjualan Per Kasir ";
				
				$data = array('page_content'=>'gl_admin_lap_h_penjualan_per_sales','msgbox_title' => $msgbox_title,'list_penjualan_by_sales' => $list_penjualan_by_sales
				);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function list_penjualan_piutang_pelanggan()
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
				if((!empty($_GET['cari_nama_kat_costumer'])) && ($_GET['cari_nama_kat_costumer']!= "")  )
				{
					$cari_nama_kat_costumer = "AND COALESCE(D.nama_kat_costumer,'') = '".str_replace("'","",$_GET['cari_nama_kat_costumer'])."'";
				}
				else
				{
					$cari_nama_kat_costumer = "";
				}
				
				if((!empty($_GET['sampai'])) && ($_GET['sampai']!= "")  )
				{
					$sampai = $_GET['sampai'];
				}
				else
				{
					$sampai = date("Y-m-d");
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (COALESCE(B.SISA,0) + (A.piutang_awal - COALESCE(C.bayar_piutang_awal,0))) > 0
							".$cari_nama_kat_costumer."
							AND 
							(
								COALESCE(A.no_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(A.nama_lengkap,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
							)
							
							
							";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND (COALESCE(B.SISA,0) + (A.piutang_awal - COALESCE(C.bayar_piutang_awal,0))) > 0
							".$cari_nama_kat_costumer."
							
						";
				}
				//$order_by = "ORDER BY (SUM(TUNAI) + SUM(NONTUNAI)) DESC";
				//$limit = "LIMIT 0,1000000";
				$list_penjualan_piutang_pelanggan = $this->M_gl_lap_penjualan->list_penjualan_piutang_pelanggan($cari,$sampai);
				
				//if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
				//{
					$cari_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ORDER BY nama_kat_costumer ASC;";
					$list_kat_costumer = $this->M_gl_kat_costumer->get_kat_costumer_cari($cari_kat_costumer);
				//}
				
				$msgbox_title = " Laporan Piutang Pelanggan ";
				
				$data = array('page_content'=>'gl_admin_lap_h_penjualan_piutang_pelanggan','msgbox_title' => $msgbox_title,'list_penjualan_piutang_pelanggan' => $list_penjualan_piutang_pelanggan,'list_kat_costumer'=>$list_kat_costumer
				);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	function excel_list_penjualan_by_kasir()
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE SUDAH DI SUB QUERY
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
							(
								COALESCE(D.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE SUDAH DI SUB QUERY
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				$order_by = "ORDER BY (SUM(TUNAI) + SUM(NONTUNAI)) DESC";
				$limit = "LIMIT 0,1000000";
				$list_penjualan_by_kasir = $this->M_gl_lap_penjualan->list_penjualan_by_kasir($cari,$order_by,$limit);
				
				
				$msgbox_title = " Laporan Transaksi/Penjualan Per Kasir ";
				
				$data = array('page_content'=>'gl_admin_excel_lap_h_penjualan_per_kasir','msgbox_title' => $msgbox_title,'list_penjualan_by_kasir' => $list_penjualan_by_kasir,'dari' => $dari,'sampai'=>$sampai
				);
				$this->load->view('admin/page/gl_admin_excel_lap_h_penjualan_per_kasir.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function excel_list_penjualan_by_sales()
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE SUDAH DI SUB QUERY
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							AND 
							(
								COALESCE(D.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE SUDAH DI SUB QUERY
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
						";
				}
				$order_by = "ORDER BY (SUM(TUNAI) + SUM(NONTUNAI)) DESC";
				$limit = "LIMIT 0,1000000";
				$list_penjualan_by_sales = $this->M_gl_lap_penjualan->list_penjualan_by_sales($cari,$order_by,$limit);
				
				
				$msgbox_title = " Laporan Transaksi/Penjualan Per Kasir ";
				
				$data = array('page_content'=>'gl_admin_excel_lap_h_penjualan_per_sales','msgbox_title' => $msgbox_title,'list_penjualan_by_sales' => $list_penjualan_by_sales,'dari' => $dari,'sampai'=>$sampai
				);
				$this->load->view('admin/page/gl_admin_excel_lap_h_penjualan_per_sales.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	
	function excel_list_pengeluaran_produk()
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
				if((!empty($_GET['cari_nama_kat_costumer'])) && ($_GET['cari_nama_kat_costumer']!= "")  )
				{
					$cari_nama_kat_costumer = "AND COALESCE(D.nama_kat_costumer,'') = '".str_replace("'","",$_GET['cari_nama_kat_costumer'])."'";
				}
				else
				{
					$cari_nama_kat_costumer = "";
				}
				
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = ''  -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							".$cari_nama_kat_costumer."
							AND 
							(
								COALESCE(A.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(A.no_costmer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(A.nama_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.nama_supplier,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							
							AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							AND B.id_d_penerimaan = '' -- UNTUK MEMUNCULKAN YANG CONSUMABLE
							AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							".$cari_nama_kat_costumer."
						";
				}
				$order_by = "ORDER BY A.no_faktur ASC,B.tgl_ins DESC";
				$limit = "LIMIT 0,1000000";
				
				$list_laporan_d_penjualan_pengeluaran_produk = $this->M_gl_lap_penjualan->list_pengeluaran($cari,$order_by,$limit);
				
				
				$msgbox_title = " Laporan Pengeluaran Produk/Jasa ";
				
				$data = array('page_content'=>'gl_admin_excel_lap_pengeluaran_produk','msgbox_title' => $msgbox_title,'list_laporan_d_penjualan_pengeluaran_produk' => $list_laporan_d_penjualan_pengeluaran_produk,'dari'=>$dari,'sampai'=>$sampai
				);
				
				//$this->load->view('admin/container',$data);
				$this->load->view('admin/page/gl_admin_excel_lap_pengeluaran_produk.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	
	function detail_tindakan_diskon()
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = str_replace("'","",$_GET['cari']);
				}
				else
				{
					$cari = "";
				}
				
				
				$list_laporan_tindakan_dokter = $this->M_gl_lap_penjualan->list_laporan_untuk_fee($this->session->userdata('ses_kode_kantor'),$dari,$sampai,$cari);
				
				/*
				$cari_dokter = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND isAKtif = 'DITERIMA' AND isDokter = 'DOKTER'";
				$list_dokter = $this->M_gl_karyawan->get_karyawan_cari($cari_dokter);
				
				$cari_therapist = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND isAKtif = 'DITERIMA' AND isDokter = 'THERAPIST'";
				$list_therapist = $this->M_gl_karyawan->get_karyawan_cari($cari_therapist);
				*/
				
				$msgbox_title = " Laporan Transaksi Dokter & Therapist (Tindakan dan Promo)";
				
				$data = array('page_content'=>'gl-admin_lap_h_penjualan_detail_tindakan_dokter','msgbox_title' => $msgbox_title,'list_laporan_tindakan_dokter' => $list_laporan_tindakan_dokter);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function excel_detail_tindakan_diskon()
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
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = str_replace("'","",$_GET['cari']);
				}
				else
				{
					$cari = "";
				}
				
				
				$list_laporan_tindakan_dokter = $this->M_gl_lap_penjualan->list_laporan_untuk_fee($this->session->userdata('ses_kode_kantor'),$dari,$sampai,$cari);
				
				$msgbox_title = " Laporan Transaksi Dokter & Therapist (Tindakan dan Promo)";
				
				$data = array('page_content'=>'gl_admin_excel_lap_tindakan_diskon','msgbox_title' => $msgbox_title,'list_laporan_tindakan_dokter' => $list_laporan_tindakan_dokter,'dari' => $dari,'sampai'=>$sampai);
				$this->load->view('admin/page/gl_admin_excel_lap_tindakan_diskon.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function excel_list_d_penjualan_row()
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
				
				if((!empty($_GET['cari_nama_kat_costumer'])) && ($_GET['cari_nama_kat_costumer']!= "")  )
				{
					$cari_nama_kat_costumer = "AND COALESCE(B.jenis_costumer,'') = '".str_replace("'","",$_GET['cari_nama_kat_costumer'])."'";
				}
				else
				{
					$cari_nama_kat_costumer = "";
				}
				
				if($this->session->userdata('ses_gnl_isPajakAktif')  == "Y") 
				{
					$isPajakaON = "AND COALESCE(C.isNPKP,'') <> 'TIDAK'";
				}
				else
				{
					$isPajakaON = "";
				}
				
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND COALESCE(C.isProduk,'') IN ('PRODUK','JASA')
							AND A.id_d_penerimaan = ''
							AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							".$cari_nama_kat_costumer."
							".$isPajakaON."
							AND 
							(
								COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
								OR COALESCE(B.no_costmer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(B.nama_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(D.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(E.nama_karyawan,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.kode_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								OR COALESCE(C.nama_produk,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
							)";
							
					$cari_h = "
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
								AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
								AND C.nominal > 0
								AND 
								(
									COALESCE(A.no_faktur,'') LIKE '%".str_replace("'","",$_GET['cari'])."%' 
									OR COALESCE(A.no_costmer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
									OR COALESCE(A.nama_costumer,'') LIKE '%".str_replace("'","",$_GET['cari'])."%'
								)
							";
				}
				else
				{
					$cari = "
							WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND COALESCE(B.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI') 
							-- AND COALESCE(C.isProduk,'') IN ('PRODUK','JASA')
							AND A.id_d_penerimaan = ''
							AND DATE(COALESCE(B.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
							".$cari_nama_kat_costumer."
							".$isPajakaON."
						";
						
					$cari_h = "
								WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND COALESCE(A.sts_penjualan,'') IN ('PEMBAYARAN','SELESAI')
								AND DATE(COALESCE(A.tgl_h_penjualan,NOW())) BETWEEN '".$dari."' AND '".$sampai."'
								AND C.nominal > 0
							";
				}
				$order_by = "ORDER BY B.no_faktur DESC,A.isProduk ASC";
				
				
				
				
				if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
				{
					$list_laporan_d_penjualan_row = $this->M_gl_lap_penjualan->list_laporan_detail_for_toko($cari,$order_by,100000,0);
				}
				else
				{
					$list_laporan_d_penjualan_row = $this->M_gl_lap_penjualan->list_laporan_detail($cari,$order_by,100000,0);
				}
				
				
				//SUMMARY
					$sum_laporan_h_penjualan = $this->M_gl_lap_penjualan->count_h_penjualan($cari_h);
					//$sum_laporan_h_penjualan = $sum_laporan_h_penjualan->row();
				//SUMMARY
				
				
				//SUMMARY PRODUK isReport
					$sum_laporan_d_penjualan_produk_isReport = $this->M_gl_lap_penjualan->sum_laporan_d_penjualan_produk_isRport($this->session->userdata('ses_kode_kantor'),$dari,$sampai);
				//SUMMARY PRODUK isReport
				
				$data = array('page_content'=>'gl_admin_excel_lap_d_penjualan_row','list_laporan_d_penjualan_row' => $list_laporan_d_penjualan_row,'sum_laporan_h_penjualan' => $sum_laporan_h_penjualan,'dari'=>$dari,'sampai'=>$sampai,'sum_laporan_d_penjualan_produk_isReport' => $sum_laporan_d_penjualan_produk_isReport);
				$this->load->view('admin/page/gl_admin_excel_lap_d_penjualan_row.html',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function detail_penjualan_fee()
	{
		if((!empty($_POST['dari'])) && ($_POST['dari']!= "")  )
		{
			$dari = $_POST['dari'];
			$sampai = $_POST['sampai'];
		}
		else
		{
			$dari = date("Y-m-d");
			$sampai = date("Y-m-d");
		}
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.id_karyawan = '".$_POST['id_karyawan']."'
					AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
					AND 
					(
						COALESCE(B.no_faktur,'') LIKE '%".str_replace("'","",$_POST['cari'])."%' 
						OR COALESCE(B.no_costmer,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						OR COALESCE(B.nama_costumer,'') LIKE '%".str_replace("'","",$_POST['cari'])."%'
					)";
		}
		else
		{
			$cari = "
					WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.id_karyawan = '".$_POST['id_karyawan']."'
					AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
					
					";
		}
		$order_by = "ORDER BY COALESCE(B.no_faktur,'') DESC, A.tgl_ins DESC";
		
		$list_detail_penjualan_fee = $this->M_gl_lap_penjualan->list_d_laporan_fee($cari,$order_by);
		
		echo'<div class="box-body table-responsive no-padding">';
			if(!empty($list_detail_penjualan_fee))
			{
				echo'<table width="100%" id="example2" class="table table-hover hoverTable">';
					echo '<thead>';	
					
					echo'
							<tr style="background-color:green;">';
								echo '<th style="text-align:center;" width="5%">NO</th>';
								echo '<th style="text-align:center;" width="20%">NAMA FEE</th>';
								echo '<th style="text-align:center;" width="20%">TRANSAKSI</th>';
								echo '<th style="text-align:center;" width="40%">KUALIFIKASI</th>';
								echo '<th style="text-align:center;" width="15%">NOMINAL</th>';
					echo '</tr>
						</thead>';
					$list_result = $list_detail_penjualan_fee->result();
					
					
					$no =1;
					/*
					$isJasa_old = "";
					$isJasa_cur = "";
					$jum_transaksi = 0;
					$cnt_transaksi = 0;
					$subtotal = 0;
					*/
					$subtotal = 0;
					
					echo '<tbody>';
					
					foreach($list_result as $row)
					{
						/*
						//SUBTOTAL
							$isJasa_cur = $row->ISPRODUK;
							
							if(($isJasa_cur != $isJasa_old) && ($isJasa_old != ""))
							{
								echo '<tr style="font-weight:bold; background-color:yellow;"> 
									<td></td> 
									<td style="text-align:right;"> '.$isJasa_old.'</td> 
									<td style="text-align:right;">'.number_format($cnt_transaksi,1,'.',',').'</td> 
									<td style="text-align:right;">'.number_format($jum_transaksi,1,'.',',').'</td>
									<td></td> 
									<td style="text-align:right;">'.number_format($subtotal,1,'.',',').'</td>
								</tr>';

								$isJasa_old = $isJasa_cur;
								
								$jum_transaksi = 0;
								$cnt_transaksi = 0;
								$subtotal = 0;
							}
							
							$jum_transaksi = $jum_transaksi + $row->JUMLAH;
							$cnt_transaksi = $cnt_transaksi + $row->CNT;
							$subtotal = $subtotal + $row->SUBTOTAL;	
						//SUBTOTAL
						*/
						
						
						echo'<tr>';
							echo'<td>'.$no.'</td>';
							echo'<td>'.$row->NAMA_H_FEE.'</td>';
							
							//<b><font style="color:red;">DATA TRANSKASI </font></b>
							echo '<td>
								
								<b>FAKTUR: </b>'.$row->NO_FAKTUR.'
								<br/>'.$row->TGL_H_PENJUALAN.'
								<br/> <b>PASIEN : </b><br/> '.$row->NAMA_COST.'
								
							</td>';
							
							echo '<td>
								<b>POLI: </b>'.$row->POLI.'
								<br/> <b>PASIEN BARU: </b>'.$row->PASIEN_BARU.'
								<br/> <b>BUAT RESEP : </b>'.$row->IS_MEMBUAT_RESEP.'
								<br/> <b>BELI PRODUK : </b>'.$row->PRODUK.'
								<br/> <b>TINDAKAN : </b>'.$row->JASA.'
								<br/> <b>PEMBELIAN : </b>'.number_format($row->NOMINAL_TR,0,'.',',').'
							</td>';
							
							
							echo '<td style="text-align:right;">'.number_format($row->nominal_fee,1,'.',',').' </td>';
							
							
						echo'</tr>';
						
						//SUBTOTAL
							$subtotal = $subtotal + $row->nominal_fee;
						//SUBTOTAL
						
						/*
						$nominal_transaksi = $nominal_transaksi + $row->SUBTOTAL;
						$nominal_pembayaran_cash = $nominal_pembayaran_cash + $row->BAYAR_CASH;
						$nominal_pembayaran_bank = $nominal_pembayaran_bank + $row->BAYAR_BANK;
						*/
						
						//$subtotal = $subtotal + ($row->harga * $row->jumlah);
						$no++;
					}
					
					
					//SUBTOTAL
					echo '<tr style="font-weight:bold; background-color:yellow;"> 
						<td colspan="4">TOTAL</td> 
						<td style="text-align:right;">'.number_format($subtotal,1,'.',',').'</td>
					</tr>';
					//SUBTOTAL
					
					
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
						
			echo'
		</div><!-- /.box-body -->
		';
		
	}
	
	function buka_kembali_tindakan()
	{
		$id_h_penjualan = $_POST['id_h_penjualan'];
		$this->M_gl_lap_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$id_h_penjualan,"PEMBAYARAN");
		echo "BERHASIL";
	}

	function upload_berita_acara()
	{
		//PASTIKAN MASUKAN GAMBAR ATAU TIDAK
		if(!empty($_FILES['file']['name']))
		{
			$id_h_penjualan = substr_replace($_POST['nama_ba'],"",0,2); //MENGHILANGKAN AWALAN BA
			$nama_gambar = $_POST['nama_ba'];
			/*AMBIL EXT*/
				$path = $_FILES['file']['name'];
				$ext = pathinfo($path, PATHINFO_EXTENSION);
			/*AMBIL EXT*/

			/*PROSES UPLOAD*/
				$lokasi_gambar_disimpan = 'assets/global/images/';
				//$avatar = $this->session->userdata('ses_kode_kantor').''.$_POST['stat_edit'];
				$nama_gambar = $nama_gambar.'.'.$ext;
				
				$this->M_gl_pengaturan->do_upload_global_dinamic_input_name("file",$lokasi_gambar_disimpan,$nama_gambar,"");
			/*PROSES UPLOAD*/
			
			if($_POST['petugas_salah'] != "")
			{
				//SIMPAN KE DB
				$this->M_gl_lap_penjualan->ubah_berita_acara_with_img($this->session->userdata('ses_kode_kantor'),$id_h_penjualan,$nama_gambar,$lokasi_gambar_disimpan,$_POST['petugas_salah'],$_POST['tgl_salah'],$_POST['jam_salah'],$_POST['ket_salah']);
				//SIMPAN KE DB
				
				//CEK APAKAH SUDAH DI BCAKUP
				$cek_backup = $this->M_gl_lap_penjualan->cek_apakah_perubahan_pertama($this->session->userdata('ses_kode_kantor'),$id_h_penjualan);
				//if(!empty($cek_backup))
				if(empty($cek_backup))
				{
					$this->M_gl_lap_penjualan->simpan_backup_penjualan($this->session->userdata('ses_kode_kantor'),$id_h_penjualan);
					
					$this->M_gl_lap_penjualan->ubah_h_penjualan_sts_isApprove_ubah($this->session->userdata('ses_kode_kantor'),$id_h_penjualan,1);
				}
				//CEK APAKAH SUDAH DI BCAKUP
				
				echo"SUKSES";
			}
			else
			{
				echo"GAGAL";
			}
		}
		else
		{
			$id_h_penjualan = substr_replace($_POST['nama_ba'],"",0,2); //MENGHILANGKAN AWALAN BA
			if($_POST['petugas_salah'] != "")
			{
				//SIMPAN KE DB
					$this->M_gl_lap_penjualan->ubah_berita_acara_no_img($this->session->userdata('ses_kode_kantor'),$id_h_penjualan,$_POST['petugas_salah'],$_POST['tgl_salah'],$_POST['jam_salah'],$_POST['ket_salah']);
				//SIMPAN KE DB
				
				//CEK APAKAH SUDAH DI BCAKUP
				$cek_backup = $this->M_gl_lap_penjualan->cek_apakah_perubahan_pertama($this->session->userdata('ses_kode_kantor'),$id_h_penjualan);
				//if(!empty($cek_backup))
				if(empty($cek_backup))
				{
					$this->M_gl_lap_penjualan->simpan_backup_penjualan($this->session->userdata('ses_kode_kantor'),$id_h_penjualan);
					
					$this->M_gl_lap_penjualan->ubah_h_penjualan_sts_isApprove_ubah($this->session->userdata('ses_kode_kantor'),$id_h_penjualan,1);
				}
				//CEK APAKAH SUDAH DI BCAKUP
				
				echo"SUKSES";
			}
			else
			{
				echo"GAGAL";
			}
		}
	}
	
	function ubah_status_approve()
	{
		$this->M_gl_lap_penjualan->ubah_h_penjualan_sts_isApprove_ubah($_POST['kode_kantor'],$_POST['id_h_penjualan'],$_POST['nilai']);
		
		echo $_POST['id_h_penjualan'].' - '.$_POST['nilai'];
	}

	function list_ba_penjualan()
	{
		if((!empty($_POST['kode_kantor'])) && ($_POST['kode_kantor']!= "")  )
		{
			$kode_kantor = $_POST['kode_kantor'];
		}
		else
		{
			$kode_kantor = $this->session->userdata('ses_kode_kantor');
		}
		
		$id_h_penjualan = substr_replace($_POST['nama_ba'],"",0,2); //MENGHILANGKAN AWALAN BA
		$list_ba_penjualan = $this->M_gl_lap_penjualan->list_ba_penjualan($kode_kantor,$id_h_penjualan);
		echo'<div class="box-body table-responsive no-padding">';
			if(!empty($list_ba_penjualan))
			{
				
									echo'<table width="100%" id="example2" class="table table-hover hoverTable" style="font-size:11px;">';
										echo '<thead>';
										echo'
												<tr style="background-color:green;">';
													echo '<th width="50%">NAMA</th>';
													echo '<th width="15%">JUMLAH</th>';
													echo '<th width="15%">DISKON</th>';
													echo '<th width="20%">HARGA</th>';
										echo '</tr>
											</thead>';
										$list_result = $list_ba_penjualan->result();
										
										
										$no = 1;
										
										/*
										$nominal_transaksi = 0;
										$nominal_pembayaran_cash = 0;
										$nominal_pembayaran_bank = 0;
										*/
										
										echo '<tbody>';
										foreach($list_result as $row)
										{
											echo'<tr>';
												echo'<td>'.$row->nama_produk.'</td>';
												echo'<td>'.$row->jumlah.' '.$row->satuan_jual.'</td>';
												echo'<td>'.$row->diskon.'</td>';
												echo'<td>'.number_format($row->harga,0,',','.').'</td>';
											echo'</tr>';
											$no++;
										}
										
										
										
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
						
			echo'
			<center>
			</center>
		</div><!-- /.box-body -->
		';
	}
	
	function list_ba_penjualan_bck()
	{
		if((!empty($_POST['kode_kantor'])) && ($_POST['kode_kantor']!= "")  )
		{
			$kode_kantor = $_POST['kode_kantor'];
		}
		else
		{
			$kode_kantor = $this->session->userdata('ses_kode_kantor');
		}
		
		
		$id_h_penjualan = substr_replace($_POST['nama_ba'],"",0,2); //MENGHILANGKAN AWALAN BA
		$list_ba_penjualan_bck = $this->M_gl_lap_penjualan->list_ba_penjualan_bck($kode_kantor,$id_h_penjualan);
		echo'<div class="box-body table-responsive no-padding">';
			if(!empty($list_ba_penjualan_bck))
			{
				
									echo'<table width="100%" id="example2" class="table table-hover hoverTable" style="font-size:11px;">';
										echo '<thead>';
										echo'
												<tr style="background-color:green;">';
													echo '<th width="50%">NAMA</th>';
													echo '<th width="15%">JUMLAH</th>';
													echo '<th width="15%">DISKON</th>';
													echo '<th width="20%">HARGA</th>';
										echo '</tr>
											</thead>';
										$list_result = $list_ba_penjualan_bck->result();
										
										
										$no = 1;
										
										/*
										$nominal_transaksi = 0;
										$nominal_pembayaran_cash = 0;
										$nominal_pembayaran_bank = 0;
										*/
										
										echo '<tbody>';
										foreach($list_result as $row)
										{
											echo'<tr>';
												echo'<td>'.$row->nama_produk.'</td>';
												echo'<td>'.$row->jumlah.' '.$row->satuan_jual.'</td>';
												echo'<td>'.$row->diskon.'</td>';
												echo'<td>'.number_format($row->harga,0,',','.').'</td>';
											echo'</tr>';
											$no++;
										}
										
										
										
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
						
			echo'
			<center>
			</center>
		</div><!-- /.box-body -->
		';
	}
	
	function update_no_resi()
	{
		$strQuery = "UPDATE tb_h_penjualan SET no_antrian = '".$_POST['no_resi']."', sts_penjualan = 'SELESAI', ket_penjualan2 = '".$_POST['ket_pengiriman']."' WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."'";
		$this->M_gl_h_penjualan->ubah_h_penjualan_by_kriteria_query($strQuery);
		
		echo'SUKSES';
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_lap_h_penjualan.php */

