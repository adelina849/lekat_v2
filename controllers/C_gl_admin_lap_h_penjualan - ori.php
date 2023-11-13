<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_lap_h_penjualan extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_lap_penjualan','M_gl_h_penjualan','M_gl_images'));
		
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
				
				$msgbox_title = " Laporan Transaksi ";
				$data = array('page_content'=>'gl_admin_lap_h_penjualan','msgbox_title' => $msgbox_title,'dari' => $dari,'sampai' => $sampai);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
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
					AND A.sts_penjualan = 'SELESAI'
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
					AND A.sts_penjualan = 'SELESAI'
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
		//$config['uri_segment'] = 2;	
		$config['uri_segment'] = $_POST['var_offset'];	
		$config['per_page'] = 50;
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
		
		$list_laporan_h_penjualan = $this->M_gl_lap_penjualan->list_laporan_h_penjualan($cari,$order_by,$config['per_page'],$_POST['var_offset']);
		
		
		//UNTUK AKUMULASI INFO
			if($config['per_page'] > $jum_row)
			{
				$jum_row_tampil = $jum_row;
			}
			else
			{
				$jum_row_tampil = $config['per_page'];
			}
			
			$offset = (integer) $_POST['var_offset'];
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
										
										
										$no =$_POST['var_offset']+1;
										
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
											
											echo'<tr>';
												echo'<td>'.$no.'</td>';
												
												//<b><font style="color:red;">DATA TRANSKASI </font></b>
												echo '<td>
													<b style="color:red;">'.$row->type_h_penjualan.'</b>
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
				
				$cari_rekam_medis = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_costumer = '".$id_costumer."' AND A.sts_penjualan = 'SELESAI' AND (A.no_faktur = '".str_replace("'","",$_POST['cari'])."')";
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
						<li style="width:100%;border-bottom:1px solid #d2d2d2;">
							<i class="fa fa-comments bg-yellow"></i>

							<div class="timeline-item">
								<!-- <span class="time"><i class="fa fa-clock-o"></i> '.$row->selisih_hari.' hari yang lalu</span> -->

								<h3 class="timeline-header"><a href="#">'.$row->nama_karyawan.'</a> 
								<br/>
								<br/><b>KELUHAN/PEMERIKSAAN AWAL : </b><br/>'.$row->ket_penjualan.'
								<br/><b>SARAN DOKTER : </b><br/>'.$row->ket_penjualan2.'
								
								</h3>

								<div class="timeline-body" style="width:100%;">
								<table style="width:100%;border:1 px solid black;">
									<tr>
										<td width="20%" style="text-align:center;margin-right:10%;">
											<!--
											<span id="img_span_dr">
												<img id="IMG_dr" name="IMG_dr"  width="100%" class="profile-user-img img-responsive"  src="'.$src.'" />
											</span>
											-->
											<b>Dokter Konsultasi : </b></br><font style="color:red;">'.$row->nama_dokter.'</font>
											</br><b>Dokter Tindakan : </b></br><font style="color:red;">'.$row->NAMA_DOKTER_TINDAKAN.'</font>
											</br><b>Therapist 1 : </b></br><font style="color:red;">'.$row->NAMA_ASS_1.'</font>
											</br><b>Therapist 2 : </b></br><font style="color:red;">'.$row->NAMA_ASS_2.'</font>
											
											
											<!-- <a href="'.base_url().'gl-admin-images/penjualan/'.$row->id_h_penjualan.'" class="btn btn-warning btn-flat btn-xs" >Tampilkan Foto Pemeriksaan</a> -->
											
											</br>
											<a href="javascript:void(0)" id="'.$row->id_h_penjualan.'" class="btn btn-warning btn-flat btn-block" onclick="tampilkan_gambar(this);" title = "Tampilkan Gambar/Foto Pasien '.$row->no_faktur.'" alt = "Tampilkan Gambar/Foto Pasien '.$row->no_faktur.'" data-toggle="modal" data-target="#myModal_foto_pasien" >LIHAT FOTO</a>
											
											
										</td>
										<td width="80%" style="margin-left:10%;">
					';
						/*MASUKAN detail_penjualan*/
						echo'<table style="margin-left:2%;">';
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
						
						$order_by = " ORDER BY A.tgl_ins DESC ";
						
						$list_d_penjualan = $this->M_gl_h_penjualan->list_d_penjualan($cari_d_penjualan,$order_by,1000,0);
						//($cari,$_POST['limit'],$_POST['offset']);
						
						if(!empty($list_d_penjualan))
						{
								$list_result = $list_d_penjualan->result();
								//$no =$this->uri->segment(2,0)+1;
								$no = 1;
								
								echo'
								<thead>
									<tr style="background-color:green;text-align:center;">
										<!-- <th>Select</th> -->
										<th width="20%" style="text-align:center;">KODE</th>
										<th width="30%" style="text-align:center;">NAMA PRODUK/TINDAKAN</th>
										<th width="15%" style="text-align:center;">BANYAK</th>
										<th width="35%" style="text-align:center;">ATURAN PAKAI</th>
									</tr>
								</thead>
								';
								
								foreach($list_result as $row_d_penjualan)
								{
									//echo'<tr id="tr_'.$no.'">';
									
									echo'<tr id="tr_list_transaksi-'.$row_d_penjualan->id_produk.'-'.$row_d_penjualan->id_h_diskon.'" >';
										echo'<td style="text-align:left;border:black 0.5px solid;padding:0.5%;">'.$row_d_penjualan->kode_produk.'</td>';
										echo'<td style="text-align:left;border:black 0.5px solid;padding:0.5%;">'.$row_d_penjualan->nama_produk.'</td>';
										echo'<td style="text-align:center;border:black 0.5px solid;padding:0.5%;">'.$row_d_penjualan->jumlah.' '.$row_d_penjualan->satuan_jual.'</td>';
										echo'<td style="text-align:left;border:black 0.5px solid;padding:0.5%;">'.$row_d_penjualan->aturan_minum.' </td>';
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

	function list_d_penjualan()
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
		
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = 
			"
				WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
				AND A.id_produk = '".$_POST['id_produk']."'
				AND COALESCE(B.sts_penjualan,'') = 'SELESAI'
				AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
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
				AND COALESCE(B.sts_penjualan,'') = 'SELESAI'
				AND COALESCE(DATE(B.tgl_h_penjualan),'0000-00-00') BETWEEN '".$dari."' AND '".$sampai."'
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
													echo '<th style="text-align:center;" width="15%">PASIEN LAMA</th>';
													echo '<th style="text-align:center;" width="15%">PASIEN BARU </th>';
													echo '<th style="text-align:center;" width="15%">BUAT RESEP</th>';
													echo '<th style="text-align:center;"  width="20%">SUBTOTAL</th>';
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
													<br/> '.$row->ISDOKTER.'
												</td> ';
												echo'<td style="text-align:right;">'.number_format($row->CNT,1,'.',',').'</td>  ';
												echo'<td style="text-align:right;">'.number_format($row->PASIEN_LAMA,1,'.',',').'</td>  ';
												echo'<td style="text-align:right;">'.number_format($row->PASIEN_BARU,1,'.',',').'</td>  ';
												echo'<td style="text-align:right;">'.number_format($row->RESEP,1,'.',',').'</td>  ';
												echo'<td style="text-align:right;">'.number_format($row->TOTAL,1,'.',',').'</td>  ';
														
											echo'</tr>';
											
											//SUBTOTAL
												//$isJasa_old = $row->ISPRODUK;
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
}

/* End of file welcome.php */
/* Location: ./application/controllers/c_admin_jabatan.php */

