<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_pedaftaran_proses_dokter extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_h_penjualan','M_gl_costumer','M_gl_produk','M_gl_h_diskon','M_gl_d_diskon','M_gl_images','M_gl_bank','M_gl_h_penjualan_catat_untuk_fee','M_gl_media_transaksi','M_gl_hpp_jasa','M_gl_d_pembelian'));
		
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
				if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND A.sts_penjualan = 'PENDING'
							AND DATE(A.tgl_h_penjualan) = DATE(NOW())
							AND (A.no_faktur LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.nama_lengkap,A.nama_costumer) LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.sts_penjualan = 'PENDING'
					AND DATE(A.tgl_h_penjualan) = DATE(NOW())
					";
				}
				
				$order_by = "ORDER BY A.tgl_ins DESC";
				
				//UNTUK AKUMULASI INFO
					$jum_row = $this->M_gl_h_penjualan->count_list_pendaftaran_periksa_dokter($cari)->JUMLAH;
				//UNTUK AKUMULASI INFO
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-pemeriksaan-dokter?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-pemeriksaan-dokter/');
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
				
				//$list_h_pendaftaran_proses_dokter = $this->M_gl_h_penjualan->list_pendaftaran_periksa_dokter($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				$msgbox_title = " Pemeriksaan Dokter";
				
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
				
				//$data = array('page_content'=>'gl_admin_h_penjualan_proses_dokter','halaman'=>$halaman,'list_h_pendaftaran_proses_dokter'=>$list_h_pendaftaran_proses_dokter,'msgbox_title' => $msgbox_title,'sum_pesan'=>$sum_pesan);
				
				if($this->session->userdata('ses_isModePst') == "YA")
				{
					$data = array('page_content'=>'gl_admin_h_penjualan_proses_dokter_pst','halaman'=>$halaman,'msgbox_title' => $msgbox_title,'sum_pesan'=>$sum_pesan);
					$this->load->view('admin/container',$data);
				}
				else
				{
					$data = array('page_content'=>'gl_admin_h_penjualan_proses_dokter','halaman'=>$halaman,'msgbox_title' => $msgbox_title,'sum_pesan'=>$sum_pesan);
					$this->load->view('admin/container',$data);
				}
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function get_ajax_update_pendaftaran()
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
				if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
							AND A.sts_penjualan = 'PENDING'
							AND DATE(A.tgl_h_penjualan) = DATE(NOW())
							AND (A.no_faktur LIKE '%".str_replace("'","",$_POST['cari'])."%' OR COALESCE(B.nama_lengkap,A.nama_costumer) LIKE '%".str_replace("'","",$_POST['cari'])."%')";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.sts_penjualan = 'PENDING'
					AND DATE(A.tgl_h_penjualan) = DATE(NOW())
					";
				}
				
				$order_by = "ORDER BY A.tgl_ins ASC";
				
				//UNTUK AKUMULASI INFO
					$jum_row = $this->M_gl_h_penjualan->count_list_pendaftaran_periksa_dokter($cari)->JUMLAH;
				//UNTUK AKUMULASI INFO
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-pemeriksaan-dokter?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-pemeriksaan-dokter/');
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
				
				$list_h_pendaftaran_proses_dokter = $this->M_gl_h_penjualan->list_pendaftaran_periksa_dokter($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				//ISI TABLE
				if(!empty($list_h_pendaftaran_proses_dokter))
				{
					$list_result = $list_h_pendaftaran_proses_dokter->result();
					$no =$this->uri->segment(2,0)+1;
					//echo '<tbody>';
					foreach($list_result as $row)
					{
						
						echo'<tr>';
							echo'<td>'.$no.'</td>';
							
							if($row->avatar == "")
							{
								$src = base_url().'assets/global/costumer/loading.gif';
							}
							else
							{
								//$src = base_url().'assets/global/costumer/'.$row->avatar;
								//$src = base_url().''.$row->avatar_url.''.$row->avatar;
								if(!(file_exists("assets/global/costumer/".$row->avatar)))
								{
									$src = base_url().'assets/global/costumer/loading.gif';
								}
								else
								{
									$src = base_url().''.$row->avatar_url.''.$row->avatar;
								}
							}
							
							//$src = base_url().'assets/global/images/qrcode/'.$row->no_faktur.'.png';
							echo '<td>
								<img id="IMG_barcode_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
								<br/>
								<center>
									<font style="font-weight:bold;color:red;font-size:18px;">'.$row->no_antrian.'
									</font>
								</center>
							</td>';
							
							if($row->id_h_diskon == "")
							{
								if($this->session->userdata('ses_isModePst') == "YA")
								{
									echo'<td>
										<b>NO : </b><font style="font-weight:bold;color:red;">'.$row->no_antrian.'</font> ('.$row->tgl_ins.' / <b style="color:red;">'.$row->MENIT_TUNGGU.' Menit Menunggu </b>)
										<br/> <b>CUSTOMER : </b>'.$row->nama_costumer.'
										<br/> <b>INPUT OLEH : </b>'.$row->nama_karyawan.'
									</td>';
								}
								else
								{
									if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
									{
										echo'<td>
											<b>NO : </b><font style="font-weight:bold;color:red;">'.$row->no_antrian.'</font> ('.$row->tgl_ins.' / <b style="color:red;">'.$row->MENIT_TUNGGU.' Menit Menunggu </b>)
											<br/> <b>PELANGGAN : </b>'.$row->nama_costumer.'
											<br/> <b>INPUT OLEH : </b>'.$row->nama_karyawan.'
											<br/> <b>KUNJUNGAN : </b>'.$row->type_h_penjualan.'
											<hr/> 
											<div style="background-color:#c2f6c6;padding:1%;">
												<b>KETERANGAN : </b></br>'.$row->ket_penjualan.' 
											</div>
										</td>';
									}
									else
									{
										echo'<td>
											<b>NO : </b><font style="font-weight:bold;color:red;">'.$row->no_antrian.'</font> ('.$row->tgl_ins.' / <b style="color:red;">'.$row->MENIT_TUNGGU.' Menit Menunggu </b>)
											<br/> <b>PASIEN : </b>'.$row->nama_costumer.'
											<br/> <b>DOKTER : </b>'.$row->nama_dokter.'
											<br/> <b>INPUT OLEH : </b>'.$row->nama_karyawan.'
											<br/> <b>KUNJUNGAN : </b>'.$row->type_h_penjualan.'
											<hr/> 
											<div style="background-color:#c2f6c6;padding:1%;">
												<b>KELUHAN DAN PEMERIKSAAN AWAL : </b></br>'.$row->ket_penjualan.' 
											</div>
										</td>';
									}
								}
							}
							else
							{
								if($this->session->userdata('ses_isModePst') == "YA")
								{
									echo'<td>
										<b>NO : </b><font style="font-weight:bold;color:red;">'.$row->no_antrian.'</font> ('.$row->tgl_ins.' / <b style="color:red;">'.$row->MENIT_TUNGGU.' Menit Menunggu </b>)
										<br/> <b>CUSTOMER : </b>'.$row->nama_costumer.'
										<br/> <b>INPUT OLEH : </b>'.$row->nama_karyawan.'
										<br/> <b>PAKET : </b><font style="color:red;">'.$row->nama_diskon.'</font>
									</td>';
								}
								else
								{
									echo'<td>
										<b>NO : </b><font style="font-weight:bold;color:red;">'.$row->no_antrian.'</font> ('.$row->tgl_ins.' / <b style="color:red;">'.$row->MENIT_TUNGGU.' Menit Menunggu </b>)
										<br/> <b>PASIEN : </b>'.$row->nama_costumer.'
										<br/> <b>DOKTER : </b>'.$row->nama_dokter.'
										<br/> <b>INPUT OLEH : </b>'.$row->nama_karyawan.'
										<br/> <b>KUNJUNGAN : </b>'.$row->type_h_penjualan.'
										<br/> <b>PAKET : </b><font style="color:red;">'.$row->nama_diskon.'</font>
										<hr/> 
										<div style="background-color:#c2f6c6;padding:1%;">
											<b>KELUHAN DAN PEMERIKSAAN AWAL : </b></br>'.$row->ket_penjualan.' 
										</div>
									</td>';
								}
							}
							
							
							//if( ($row->id_dokter == $this->session->userdata('ses_id_karyawan'))
							
							
							//if( ($row->id_dokter == $this->session->userdata('ses_id_karyawan')) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							
							//if( ($row->id_dokter == $this->session->userdata('ses_id_karyawan')) or ( ($this->session->userdata('ses_akses_lvl2_53') > 0) and ($this->session->userdata('ses_isDokter') != "DOKTER") ) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							//{
								if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
								{
									if( ($row->id_karyawan == $this->session->userdata('ses_id_karyawan')) OR ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
									{
										echo'<td>
<a class="confirm-btn btn btn-warning btn-block btn-flat" href="'.base_url().'gl-admin-pemeriksaan-dokter-proses/'.md5($row->id_h_penjualan).'" title = "Proses Pendaftaran Data '.$row->no_antrian.' atas nama '.$row->nama_costumer.'" alt = "Proses Pendaftaran Data '.$row->no_antrian.' atas nama '.$row->nama_costumer.'">PROSES</a>

<!--
'.$row->id_karyawan.'<br/>
'.$this->session->userdata('ses_id_karyawan').'
-->
										</td>';
									}
									else
									{
										echo'<td></td>';
									}
								}
								else
								{
									echo'<td>
<a class="confirm-btn btn btn-warning btn-block btn-flat" href="'.base_url().'gl-admin-pemeriksaan-dokter-proses/'.md5($row->id_h_penjualan).'" title = "Proses Pendaftaran Data '.$row->no_antrian.' atas nama '.$row->nama_costumer.'" alt = "Proses Pendaftaran Data '.$row->no_antrian.' atas nama '.$row->nama_costumer.'">PROSES</a>
									</td>';
								}
								
							//}
							//else
							//{
								//echo'<td></td>';
							//}
							
							echo'<input type="hidden" id="id_h_penjualan_'.$no.'" name="id_h_penjualan_'.$no.'" value="'.$row->id_h_penjualan.'" />';
							
						echo'</tr>';
						$no++;
					}
					//echo '</tbody>';
				}
				else
				{
					if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
					{
						echo'<tr>';
						echo'<td colspan="4" style="text-align:center;color:red;font-weight:bold;">Tidak Ada Transaksi !</td>';
						echo'</tr>';
					}
					else
					{
						if($this->session->userdata('ses_isModePst') == "YA")
						{
							echo'<tr>';
							echo'<td colspan="4" style="text-align:center;color:red;font-weight:bold;">Tidak Ada Transaksi !</td>';
							echo'</tr>';
						}
						else
						{
							echo'<tr>';
							echo'<td colspan="4" style="text-align:center;color:red;font-weight:bold;">Tidak Ada Pasien Yang Menunggu !</td>';
							echo'</tr>';
						}
					}
					
				}
				//ISI TABLE
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function proses_pendaftaran_by_dokter()
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
				if(!empty($this->uri->segment(2,0)))
				{
					$id_h_penjualan = $this->uri->segment(2,0);
					
					if((!empty($_GET['from'])) && ($_GET['from']!= "") && ($_GET['from']== "bayar")  )
					{
						$sts_penjualan = "PEMERIKSAAN";
					}
					else
					{
						$sts_penjualan = 'PENDING';
					}
					
					
					if((!empty($_GET['cari'])) && ($_GET['cari']!= "")  )
					{
						$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
								AND MD5(A.id_h_penjualan) = '".$id_h_penjualan."'
								-- AND A.sts_penjualan IN ('".$sts_penjualan."','SELESAI')
								AND (A.no_faktur LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.nama_lengkap,A.nama_costumer) LIKE '%".str_replace("'","",$_GET['cari'])."%')";
					}
					else
					{
						$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND MD5(A.id_h_penjualan) = '".$id_h_penjualan."' 
						-- AND A.sts_penjualan IN ('".$sts_penjualan."','SELESAI') ";
					}
					
					$order_by = "ORDER BY A.tgl_ins DESC";
					$data_h_penjualan = $this->M_gl_h_penjualan->list_pendaftaran($cari,$order_by,1,0);
					if(!empty($data_h_penjualan))
					{
						$data_h_penjualan = $data_h_penjualan->row();
						/*UPDATE WAKTU MULAI PERIKSA*/
							$this->M_gl_h_penjualan->ubah_h_penjualan_ubah_waktu_mulai_pemeriksaan($this->session->userdata('ses_kode_kantor'),$data_h_penjualan->id_h_penjualan);
						/*UPDATE WAKTU MULAI PERIKSA*/
						
						/*AMBIL DATA PASIEN*/
						//$cari_costumer = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_costumer = '".$data_h_penjualan->id_costumer."'";
						
						$cari_costumer = " WHERE A.no_costumer = '".$data_h_penjualan->no_costmer."'";
						$data_costumer = $this->M_gl_costumer->list_costumer_biasa($cari_costumer,1,0);
						if(!empty($data_costumer))
						{
							$data_costumer = $data_costumer->row();
						}
						else
						{
							$data_costumer = false;
						}
						/*AMBIL DATA PASIEN*/
						
						/*AMBIL DATA DOKTER*/
						$id_dokter = $data_h_penjualan->id_dokter;
						//$data_dokter = $this->M_gl_karyawan->get_karyawan('id_karyawan',$id_dokter);
						$cari_dokter = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_karyawan = '".$data_h_penjualan->id_dokter."'";
						$data_dokter = $this->M_gl_karyawan->get_karyawan_jabatan_row_when_login($cari_dokter);
						if(!empty($data_dokter))
						{
							//$data_dokter = $data_dokter->row(); // KARENA UDAH JADI ROW
						}
						else
						{
							$data_dokter = false;
						}
						/*AMBIL DATA DOKTER*/
						
						/*AMBIL DATA GAMBAR*/
							$cari_gambar = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.group_by = 'penjualan' AND id = '".$data_h_penjualan->id_h_penjualan."'";
							$list_images = $this->M_gl_images->list_images_limit($cari_gambar,10,0);
							if(!empty($list_images))
							{
								
							}
							else
							{
								$list_images = false;
							}
						/*AMBIL DATA GAMBAR*/
						
						/*AMBIL DATA PEMBAYARAN*/
							/*CASH*/
								$cari_pembayaran_cash = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$data_h_penjualan->id_h_penjualan."' AND id_bank = '' AND DATE(tgl_ins) = DATE('".$data_h_penjualan->tgl_ins."') AND nominal > 0";
								
								$order_by_pembayaran_cash = " ORDER BY tgl_ins ASC";
								$data_pembayaran_cash = $this->M_gl_h_penjualan->list_d_penjualan_bayar
										(
											$cari_pembayaran_cash,
											$order_by_pembayaran_cash,
											1,	
											0
										);
								if(!empty($data_pembayaran_cash))
								{
									$data_pembayaran_cash = $data_pembayaran_cash->row();
								}
								else
								{
									$data_pembayaran_cash = false;
								}
							/*CASH*/
							
							/*VIA_BANK*/
								$cari_pembayaran_bank = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$data_h_penjualan->id_h_penjualan."' AND id_bank <> '' AND cara <> 'CASH' AND DATE(tgl_ins) = DATE('".$data_h_penjualan->tgl_ins."')  AND nominal > 0";
								
								$order_by_pembayaran_bank = " ORDER BY tgl_ins ASC";
								$data_pembayaran_bank = $this->M_gl_h_penjualan->list_d_penjualan_bayar
										(
											$cari_pembayaran_bank,
											$order_by_pembayaran_bank,
											1,	
											0
										);
								if(!empty($data_pembayaran_bank))
								{
									$data_pembayaran_bank = $data_pembayaran_bank->row();
								}
								else
								{
									$data_pembayaran_bank = false;
								}
							/*VIA_BANK*/
						/*AMBIL DATA PEMBAYARAN*/
						
						/*PROSES JIKA MENGAMBIL PAKET*/
							if($data_h_penjualan->id_h_diskon != '')
							{
								/*CEK H DISKON APAKAH PAKET*/
								$cari_h_diskon = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_diskon = '".$data_h_penjualan->id_h_diskon."' AND optr_diskon = 'Paket' ";
								$cek_apakah_diskon_adalah_paket = $this->M_gl_h_diskon->get_h_diskon_cari($cari_h_diskon);
								if(!empty($cek_apakah_diskon_adalah_paket))
								{
									$cek_apakah_diskon_adalah_paket = $cek_apakah_diskon_adalah_paket->row();
									/*CEK tb_d_penjualan APAKAH SUDAH ADA*/
										$cari_d_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$data_h_penjualan->id_h_penjualan."' AND id_h_diskon = '".$cek_apakah_diskon_adalah_paket->id_h_diskon."'";
										$cek_d_penjualan_is_sudah_ada_paket = $this->M_gl_h_penjualan->get_d_penjualan_cari($cari_d_penjualan);
										
										/*CEK APAKAH MASIH KOSONG, JIKA IYA MASUKAN DENGAN LOOPING tb_d_diskon*/
										if(empty($cek_d_penjualan_is_sudah_ada_paket))
										{
											$cari_d_diskon = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_h_diskon = '".$cek_apakah_diskon_adalah_paket->id_h_diskon."'";
											$get_tb_d_diskon = $this->M_gl_h_diskon->get_d_diskon_cari($cari_d_diskon);
											
											/*MULAI LOOPING DAN INSERT KE tb_d_penjualan*/
												if(!empty($get_tb_d_diskon))
												{
													$list_result = $get_tb_d_diskon->result();
													foreach($list_result as $row)
													{
														if($row->status_konversi == '*')
														{
															$jumlah_konversi = $row->konversi * $row->banyaknya;
															$harga_konversi = $row->nominal / $row->konversi;
															$harga_dasar_konversi = $row->modal / $row->konversi;
														}
														else
														{
															$jumlah_konversi = $row->banyaknya;
															$harga_konversi = $row->nominal;
															$harga_dasar_konversi = $row->modal;
														}
														
														$this->M_gl_h_penjualan->simpan_d_penjualan
														(
														
															$data_h_penjualan->id_h_penjualan,
															'',
															$row->id_produk,
															$row->id_h_diskon,
															$row->id_d_diskon,
															$row->banyaknya, //jumlah
															$row->status_konversi,
															$row->konversi,
															$row->kode_satuan, //satuan_jual,
															$jumlah_konversi,
															'0', //$diskon,
															'%', //$optr_diskon,
															'0', //$besar_diskon_ori,
															$row->nominal, //harga,
															$harga_konversi, //$harga_konversi,
															$row->modal, //$harga_ori,
															$harga_dasar_konversi,
															'0', //$stock,
															$row->ket_d_diskon,
															$row->isProduk,
															$this->session->userdata('ses_id_karyawan'),
															$this->session->userdata('ses_kode_kantor')
														);
														
														//CEK UNTUK CONSUMBALE JIKA JASA, JADI LANGSUNG MASUKAN CONSUMBALE NYA
														if($row->isProduk == "JASA")
														{
															$cek_di_hpp_jasa = $this->M_gl_hpp_jasa->get_hpp_jasa_cari(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$row->id_produk."' AND id_assets = '' AND id_produk_hpp <> ''");
															if(!empty($cek_di_hpp_jasa))
															{
																//ID_D_PENERIMAAN DI TB_D_PENJUALAN BISA DI PAKAI ID_PRODUK UNTUK CONSUMABLE
																//HAPUS DL DATA SEBELUMNYA
																	$cari_delete_di_d_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$data_h_penjualan->id_h_penjualan."' AND id_d_penerimaan = '".$row->id_produk."' AND isProduk = 'CONSUMABLE'";
																	$this->M_gl_h_penjualan->hapus_d_penjualan_with_cari($cari_delete_di_d_penjualan);
																//HAPUS DL DATA SEBELUMNYA
																//ID_D_PENERIMAAN DI TB_D_PENJUALAN BISA DI PAKAI ID_PRODUK UNTUK CONSUMABLE
															
																$list_result = $cek_di_hpp_jasa->result();
																foreach($list_result as $row_consumable)
																{
																	$this->M_gl_h_penjualan->simpan_d_penjualan
																	(
																		
																		$data_h_penjualan->id_h_penjualan,
																		$row->id_produk, //$_POST['id_d_penerimaan'],
																		$row_consumable->id_produk_hpp,
																		'',
																		'',
																		$row_consumable->banyaknya, //['jumlah'],
																		'*', //$_POST['status_konversi'],
																		'1', //$_POST['konversi'],
																		$row_consumable->satuan, //$_POST['satuan_jual'],
																		$row_consumable->banyaknya, //$_POST['jumlah_konversi'],
																		0, //$_POST['diskon'],
																		'%', //'', //$optr_diskon,
																		0, //0, //$besar_diskon_ori,
																		$row_consumable->harga, //$_POST['harga'],
																		$row_consumable->harga, //$_POST['harga_konversi'],
																		$row_consumable->harga, //$_POST['harga_ori'],
																		$row_consumable->harga, //$_POST['harga_dasar_ori'],
																		0, //$_POST['stock'],
																		$row_consumable->ket_hpp, //$_POST['ket_d_penjualan'],
																		'CONSUMABLE', //$_POST['isProduk'],
																		$this->session->userdata('ses_id_karyawan'),
																		$this->session->userdata('ses_kode_kantor')
																	
																	);
																}
															}
														}
														
													}
												}
											/*MULAI LOOPING DAN INSERT KE tb_d_penjualan*/
										}
										/*CEK APAKAH MASIH KOSONG, JIKA IYA MASUKAN DENGAN LOOPING tb_d_diskon*/
										
									/*CEK tb_d_penjualan APAKAH SUDAH ADA*/
								}
								/*CEK H DISKON APAKAH PAKET*/
							}
						/*PROSES JIKA MENGAMBIL PAKET*/
						
						/*DAPATKAN DATA BANK*/
							$cari_bank = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
							$list_bank = $this->M_gl_bank->list_bank_limit($cari_bank,1000,0);
						/*DAPATKAN DATA BANK*/
						
						/*LIST PAKET YANG SESUAI*/
							if(!empty($data_costumer))
							{
								$cari_h_diskon_yang_sesuai_dengan_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (id_kat_costumer = '".$data_costumer->id_kat_costumer."' OR id_kat_costumer = '') AND optr_diskon = 'Paket' AND DATE(NOW()) >= DATE(dari) AND DATE(NOW()) <= DATE(sampai)";
							}
							else
							{
								$cari_h_diskon_yang_sesuai_dengan_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_kat_costumer = '' AND optr_diskon = 'Paket' AND DATE(NOW()) >= DATE(dari) AND DATE(NOW()) <= DATE(sampai)";
							}
							
							$cek_diskon_yang_sesuai_dengan_kat_diskon = $this->M_gl_h_diskon->get_h_diskon_cari($cari_h_diskon_yang_sesuai_dengan_kat_costumer);
						/*LIST PAKET YANG SESUAI*/
						
						/*MEDIA TRANSAKSI*/
							$cari_media = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' ";
							$order_by_media = " ORDER BY nama_media ASC";
							$media_transaksi = $this->M_gl_media_transaksi->list_media_transaksi_limit($cari_media,$order_by_media,1000,0);
						/*MEDIA TRANSAKSI*/
						
						//DATA KARYAWAN
							//$cari_karyawan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND isAktif NOT IN ('RESIGN','PHK') AND isDokter NOT IN ('DOKTER','THERAPIST') ";
							//$order_karyawan = " ORDER BY nama_karyawan ASC";
							//$list_karyawan = $this->M_gl_costumer->list_karyawan_cari($cari_karyawan,$order_karyawan);
							
							if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
							{
								$cari_karyawan = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.isAktif NOT IN ('RESIGN','PHK') AND A.isSales = 'YA' ";
							}
							else
							{
								$cari_karyawan = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.isAktif NOT IN ('RESIGN','PHK') AND A.isDokter NOT IN ('DOKTER','THERAPIST') AND COALESCE(B.nama_jabatan,'') IN ('APOTEKER','ASST. APOTEKER')";
							}
							
							$order_karyawan = " ORDER BY A.nama_karyawan ASC";
							$list_karyawan = $this->M_gl_karyawan->list_karyawan_with_jabatan_terbaru_for_transaksi($cari_karyawan,$order_karyawan);
						//DATA KARYAWAN
						
						/*TAMPILKAN*/
						
						if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
						{
							$msgbox_title = " Transaksi Penjualan";
						}
						else
						{
							if(!empty($data_dokter))
							{
								$msgbox_title = " Proses Pemeriksaan Pasien Oleh Dokter ".$data_dokter->nama_karyawan;
							}
							else
							{
								$msgbox_title = " Penjualan Langsung";
							}
						}
							
						
						
						if($this->session->userdata('ses_isModePst') == "YA")
						{
							$data = array('page_content'=>'gl_admin_h_penjualan_proses_dokter_proses_pst','msgbox_title' => $msgbox_title,'data_h_penjualan' => $data_h_penjualan,'data_costumer' => $data_costumer,'data_dokter' => $data_dokter,'list_images' => $list_images,'list_bank' => $list_bank,'data_pembayaran_cash' => $data_pembayaran_cash,'data_pembayaran_bank' => $data_pembayaran_bank,'cek_diskon_yang_sesuai_dengan_kat_diskon' => $cek_diskon_yang_sesuai_dengan_kat_diskon,'media_transaksi' => $media_transaksi,'list_karyawan' => $list_karyawan);
							$this->load->view('admin/container',$data);
						}
						else
						{
							$data = array('page_content'=>'gl_admin_h_penjualan_proses_dokter_proses','msgbox_title' => $msgbox_title,'data_h_penjualan' => $data_h_penjualan,'data_costumer' => $data_costumer,'data_dokter' => $data_dokter,'list_images' => $list_images,'list_bank' => $list_bank,'data_pembayaran_cash' => $data_pembayaran_cash,'data_pembayaran_bank' => $data_pembayaran_bank,'cek_diskon_yang_sesuai_dengan_kat_diskon' => $cek_diskon_yang_sesuai_dengan_kat_diskon,'media_transaksi' => $media_transaksi,'list_karyawan' => $list_karyawan);
							$this->load->view('admin/container',$data);
						}
						
						/*TAMPILKAN*/

					}
					else
					{
						header('Location: '.base_url().'gl-admin-pemeriksaan-dokter');
					}
				}
				else
				{
					$data_h_penjualan = false;
					$data_costumer = false;
					
					/*TAMPILKAN*/
					$msgbox_title = " Pendaftaran Pasien ";
				
					$data = array('page_content'=>'gl_admin_h_penjualan_daftar','msgbox_title' => $msgbox_title,'data_h_penjualan' => $data_h_penjualan,'data_costumer' => $data_costumer);
					$this->load->view('admin/container',$data);
					/*TAMPILKAN*/
				}
				
				
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	public function batal_transaksi()
	{
		$id_h_penjualan = $_POST['id_h_penjualan'];
		$data_h_penjualan = $this->M_gl_h_penjualan->get_h_penjualan('id_h_penjualan',$_POST['id_h_penjualan']);
		if(!empty($data_h_penjualan))
		{
			$data_h_penjualan = $data_h_penjualan->row();
			
			if($data_h_penjualan->sts_penjualan == 'PENDING')
			{
				$cari_d_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."'";
				
				$this->M_gl_h_penjualan->hapus_d_penjualan_with_cari($cari_d_penjualan); //D PENJUALAN
				$this->M_gl_h_penjualan->hapus($cari_d_penjualan); //H PENJUALAN
			}
			
		}
	}
	
	function kosongkan_d_penjualan()
	{
		$id_h_penjualan = $_POST['id_h_penjualan'];
		$cari_d_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."'";
				
		$this->M_gl_h_penjualan->hapus_d_penjualan_with_cari($cari_d_penjualan); //D PENJUALAN
		
		echo"BERHASIL";
	}
	
	function cek_no_retur()
	{
		$query = "
				SELECT
					A.id_h_penjualan
					,ROUND(COALESCE(B.NOMINAL,0),0) AS NOMINAL
				FROM tb_h_retur AS A
				LEFT JOIN
				(
					SELECT kode_kantor,id_h_penjualan,SUM(jumlah*harga) AS NOMINAL 
					FROM tb_d_retur 
					GROUP BY kode_kantor,id_h_penjualan
				) AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
				WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
				AND A.no_faktur = '".$_POST['no_retur']."'
				GROUP BY A.id_h_penjualan
				";
		
		$cek_nominal = $this->M_gl_h_penjualan->view_query_general($query);
		if(!empty($cek_nominal))
		{
			$cek_nominal = $cek_nominal->row();
			
			$exec_query = "
							UPDATE tb_h_penjualan 
							SET 
								id_h_retur = '".$cek_nominal->id_h_penjualan."'
								, diskon = '".$cek_nominal->NOMINAL."'
								, ket_penjualan2 = '".$_POST['no_retur']."' 
							WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."'
						";
			$this->M_gl_h_penjualan->exec_query_general($exec_query);
			
			echo $cek_nominal->NOMINAL;
		}
		else
		{
			echo 0;
		}
	}
	
	function cek_dan_masukan_paket()
	{
		/*PROSES JIKA MENGAMBIL PAKET*/
			//if($data_h_penjualan->id_h_diskon != '')
			//if($_POST['id_h_diskon'] != '')
			//{
				/*CEK H DISKON APAKAH PAKET*/
				/*
				$cari_h_diskon = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_diskon = '".$data_h_penjualan->id_h_diskon."' AND optr_diskon = 'Paket' ";
				$cek_apakah_diskon_adalah_paket = $this->M_gl_h_diskon->get_h_diskon_cari($cari_h_diskon);
				if(!empty($cek_apakah_diskon_adalah_paket))
				{
				*/
					//$cek_apakah_diskon_adalah_paket = $cek_apakah_diskon_adalah_paket->row();
					/*CEK tb_d_penjualan APAKAH SUDAH ADA*/
						//$cari_d_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_h_diskon = '".$cek_apakah_diskon_adalah_paket->id_h_diskon."'";
						
						if($_POST['id_h_diskon'] == "")
						{
							$this->M_gl_h_penjualan->ubah_h_penjualan_d_diskon_paket($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$_POST['id_h_diskon']);
							
							/*HAPUS D PENJUALAN*/
								$cari_hapus = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_h_diskon = '".$_POST['id_h_diskon_bfr']."'";
								
								$this->M_gl_h_penjualan->hapus_d_penjualan_with_cari($cari_hapus);
							echo $cari_hapus ;
							/*HAPUS D PENJUALAN*/
						}
						else
						{
							$this->M_gl_h_penjualan->ubah_h_penjualan_d_diskon_paket($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$_POST['id_h_diskon']);
							
							$cari_d_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_h_diskon = '".$_POST['id_h_diskon']."'";
							$cek_d_penjualan_is_sudah_ada_paket = $this->M_gl_h_penjualan->get_d_penjualan_cari($cari_d_penjualan);
							
							/*CEK APAKAH MASIH KOSONG, JIKA IYA MASUKAN DENGAN LOOPING tb_d_diskon*/
							if(empty($cek_d_penjualan_is_sudah_ada_paket))
							{
								//$cari_d_diskon = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_h_diskon = '".$cek_apakah_diskon_adalah_paket->id_h_diskon."'";
								
								$cari_d_diskon = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_h_diskon = '".$_POST['id_h_diskon']."'";
								$get_tb_d_diskon = $this->M_gl_h_diskon->get_d_diskon_cari($cari_d_diskon);
								/*MULAI LOOPING DAN INSERT KE tb_d_penjualan*/
									if(!empty($get_tb_d_diskon))
									{
										$list_result = $get_tb_d_diskon->result();
										foreach($list_result as $row)
										{
											if($row->status_konversi == '*')
											{
												$jumlah_konversi = $row->konversi * $row->banyaknya;
												$harga_konversi = $row->nominal / $row->konversi;
												$harga_dasar_konversi = $row->modal / $row->konversi;
											}
											else
											{
												$jumlah_konversi = $row->banyaknya;
												$harga_konversi = $row->nominal;
												$harga_dasar_konversi = $row->modal;
											}
											
											$this->M_gl_h_penjualan->simpan_d_penjualan_with_kategori
											(
											
												$_POST['id_h_penjualan'],
												'',
												$row->id_produk,
												$row->id_h_diskon,
												$row->id_d_diskon,
												$row->id_kat_produk, //id_kat_produk
												$row->banyaknya, //jumlah
												$row->status_konversi,
												$row->konversi,
												$row->kode_satuan, //satuan_jual,
												$jumlah_konversi,
												'0', //$diskon,
												'%', //$optr_diskon,
												'0', //$besar_diskon_ori,
												$row->nominal, //harga,
												$harga_konversi, //$harga_konversi,
												$row->modal, //$harga_ori,
												$harga_dasar_konversi,
												'0', //$stock,
												$row->ket_d_diskon,// $row->nama_diskon, //$row->ket_d_diskon,
												$row->isProduk,
												$this->session->userdata('ses_id_karyawan'),
												$this->session->userdata('ses_kode_kantor')
											);
											
											//CEK UNTUK CONSUMBALE JIKA JASA, JADI LANGSUNG MASUKAN CONSUMBALE NYA
											if($row->isProduk == "JASA")
											{
												$cek_di_hpp_jasa = $this->M_gl_hpp_jasa->get_hpp_jasa_cari(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$row->id_produk."' AND id_assets = '' AND id_produk_hpp <> ''");
												if(!empty($cek_di_hpp_jasa))
												{
													//ID_D_PENERIMAAN DI TB_D_PENJUALAN BISA DI PAKAI ID_PRODUK UNTUK CONSUMABLE
													//HAPUS DL DATA SEBELUMNYA
														$cari_delete_di_d_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_d_penerimaan = '".$row->id_produk."' AND isProduk = 'CONSUMABLE'";
														$this->M_gl_h_penjualan->hapus_d_penjualan_with_cari($cari_delete_di_d_penjualan);
													//HAPUS DL DATA SEBELUMNYA
													//ID_D_PENERIMAAN DI TB_D_PENJUALAN BISA DI PAKAI ID_PRODUK UNTUK CONSUMABLE
												
													$list_result = $cek_di_hpp_jasa->result();
													foreach($list_result as $row_consumable)
													{
														$this->M_gl_h_penjualan->simpan_d_penjualan
														(
															
															$_POST['id_h_penjualan'],
															$row->id_produk, //$_POST['id_d_penerimaan'],
															$row_consumable->id_produk_hpp,
															'',
															'',
															$row_consumable->banyaknya, //['jumlah'],
															'*', //$_POST['status_konversi'],
															'1', //$_POST['konversi'],
															$row_consumable->satuan, //$_POST['satuan_jual'],
															$row_consumable->banyaknya, //$_POST['jumlah_konversi'],
															0, //$_POST['diskon'],
															'%', //'', //$optr_diskon,
															0, //0, //$besar_diskon_ori,
															$row_consumable->harga, //$_POST['harga'],
															$row_consumable->harga, //$_POST['harga_konversi'],
															$row_consumable->harga, //$_POST['harga_ori'],
															$row_consumable->harga, //$_POST['harga_dasar_ori'],
															0, //$_POST['stock'],
															$row_consumable->ket_hpp, //$_POST['ket_d_penjualan'],
															'CONSUMABLE', //$_POST['isProduk'],
															$this->session->userdata('ses_id_karyawan'),
															$this->session->userdata('ses_kode_kantor')
														
														);
													}
												}
											}
										}
									}
									echo "BERHASIL LOOPING";
								/*MULAI LOOPING DAN INSERT KE tb_d_penjualan*/
							}
							/*CEK APAKAH MASIH KOSONG, JIKA IYA MASUKAN DENGAN LOOPING tb_d_diskon*/
						}
					/*CEK tb_d_penjualan APAKAH SUDAH ADA*/
				//}
				/*CEK H DISKON APAKAH PAKET*/
			//}
		/*PROSES JIKA MENGAMBIL PAKET*/
	}

	function list_produk_with_kategori()
	{
		
		//$list_produk = $this->M_gl_produk->list_produk_limit_harga_dasar_untuk_PENJUALAN($_POST['id_h_penjualan'],$_POST['id_kat_costumer'],$_POST['media_transaksi'],$cari,$order_by,$_POST['limit'],$_POST['offset']);
		
		$list_produk = $this->M_gl_produk->list_produk_dan_kategori($this->session->userdata('ses_kode_kantor'),$_POST['id_kat_produk'],str_replace("'","",$_POST['cari'])," ORDER BY AAA.nama_produk ASC ",30,0);
		
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_produk))
		{
			
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">ANO</th>';
							//echo '<th width="5%">PILIH</th>';
							//echo '<th width="10%">FOTO</th>';
							echo '<th width="60%">DATA PRODUK</th>';
							echo '<th width="15%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_produk->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					echo'<tr id="tr_list_produk-'.$no.'">';
					
						echo'<td>'.$no.'</td>';
						//echo'<td><input type="checkbox" name="record"></td>';
						
						
						//<br/> <b>PRODUKSI : </b>'.$row->produksi_oleh.'
						echo'<td>
								<b>KODE : </b>'.$row->kode_produk.'
								<br/> <b>JENIS : </b>'.$row->isProduk.'
								<br/> <b>NAMA : </b><b style="color:red;">'.$row->nama_produk.'</b> 
								
								
							</td>';
						echo'<td>
								<!-- <button type="button" onclick="insert_produk('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button> -->
								<button id="btn_pilih_'.$no.'" type="button" onclick="insert_produk_by_kategori('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal" >Pilih</button>
							</td>';
							
						echo'<input type="hidden" id="get_tr_5_'.$no.'" name="get_tr_5_'.$no.'" value="tr_list_produk-'.$no.'" />';
						echo'<input type="hidden" id="id_produk_5_'.$no.'" name="id_produk_5_'.$no.'" value="'.$row->id_produk.'" />';
						echo'<input type="hidden" id="kode_produk_5_'.$no.'" name="kode_produk_5_'.$no.'" value="'.$row->kode_produk.'" />';
						echo'<input type="hidden" id="nama_produk_5_'.$no.'" name="nama_produk_5_'.$no.'" value="'.$row->nama_produk.'" />';
						
						
						echo'<input type="hidden" id="id_satuan_5_'.$no.'" name="id_satuan_5_'.$no.'" value="'.$row->id_satuan.'" />';
						echo'<input type="hidden" id="kode_satuan_5_'.$no.'" name="kode_satuan_5_'.$no.'" value="'.$row->kode_satuan.'" />';
						echo'<input type="hidden" id="status_konversi_5_'.$no.'" name="status_konversi_5_'.$no.'" value="'.$row->status_konversi.'" />';
						echo'<input type="hidden" id="besar_konversi_5_'.$no.'" name="besar_konversi_5_'.$no.'" value="'.$row->besar_konversi.'" />';
						
						echo'<input type="hidden" id="harga_jual_5_'.$no.'" name="harga_jual_5_'.$no.'" value="'.$row->harga_jual.'" />';
						
						echo'<input type="hidden" id="isProduk_5_'.$no.'" name="isProduk_5_'.$no.'" value="'.$row->isProduk.'" />';
						
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

	function list_produk()
	{	
		if((!empty($_POST['isProduk'])) && ($_POST['isProduk']!= "")  )
		{
			if($_POST['isProduk'] == "JASA")
			{
				$isProduk =  " AND A.isProduk = 'JASA'";
			}
			else
			{
				//$isProduk =  " AND A.isProduk IN ('PRODUK','CONSUMABLE')";
				$isProduk =  " AND A.isProduk <> 'JASA'";
			}
		}
		else
		{
			$isProduk =  " AND A.isProduk <> 'JASA'";
		}
		
		
		if($this->session->userdata('ses_gnl_isPajakAktif')  == "Y") 
		{
			$isPajakaON = "AND A.isNPKP <> 'TIDAK'";
		}
		else
		{
			$isPajakaON = "";
		}
		
			
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "
				WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					".$isProduk."
					".$isPajakaON."
					AND A.isNotActive = 0
					AND B.besar_konversi = 1
					AND E.id_produk IS NULL
					AND 
						( 
							TRIM(A.kode_produk) LIKE '%".str_replace("'","",$_POST['cari'])."%' 
							OR REPLACE(A.nama_produk,'  ',' ') LIKE '%".str_replace("'","",$_POST['cari'])."%'
						)";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					".$isProduk."
					".$isPajakaON."
					AND A.isNotActive = 0
					AND B.besar_konversi = 1
					AND E.id_produk IS NULL
					";
		}
		
		$order_by = " ORDER BY A.nama_produk ASC ";
		
		//$list_produk = $this->M_gl_produk->list_produk_limit_harga_dasar_untuk_PENJUALAN($_POST['id_h_penjualan'],$_POST['id_kat_costumer'],$_POST['media_transaksi'],$cari,$order_by,$_POST['limit'],$_POST['offset']);
		
		$list_produk = $this->M_gl_produk->list_produk_limit_harga_dasar_untuk_PENJUALAN($_POST['id_h_penjualan'],$_POST['id_kat_costumer'],$_POST['media_transaksi'],$cari,$order_by,30,0);
		
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_produk))
		{
			echo '<div class="box-body table-responsive no-padding">';
			echo'<table width="100%" id="table_list_produk" class="table table-hover">';
				echo '<thead>
<tr>';
							echo '<th width="5%">ANO</th>';
							//echo '<th width="5%">PILIH</th>';
							//echo '<th width="10%">FOTO</th>';
							echo '<th width="60%">DATA PRODUK</th>';
							echo '<th width="15%">STOCK</th>';
							echo '<th width="10%">PILIH</th>';
				echo '</tr>
</thead>';
				$list_result = $list_produk->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				echo '<tbody>';
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					
					if( ($row->SISA_STOCK <=0) && ($row->isProduk != 'JASA'))
					{
						echo'<tr id="tr_list_produk-'.$no.'" style="background-color:yellow;">';
					}
					else
					{
						echo'<tr id="tr_list_produk-'.$no.'">';
					}
					
						echo'<td>'.$no.'</td>';
						//echo'<td><input type="checkbox" name="record"></td>';
						
						/*
						if($row->img_file == "")
						{
							$src = base_url().'assets/global/no-image.jpg';
							echo '<td><img id="IMG_'.$no.'"  width="100px" height="100px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" />
							</td>';
						}
						else
						{
							$src = base_url().''.$row->img_url.''.$row->img_file;
							echo '<td><img id="IMG_'.$no.'"  width="100px" height="100px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" />
							</td>';
						}
						*/
						
						
						//<br/> <b>PRODUKSI : </b>'.$row->produksi_oleh.'
						echo'<td>
								<b>KODE : </b>'.$row->kode_produk.'
								<br/> <b>JENIS : </b>'.$row->isProduk.'
								<br/> <b>NAMA : </b><b style="color:red;">'.$row->nama_produk.'</b> 
								<br/> <b>HARGA : </b><b style="color:red;">'.number_format($row->harga_jual,0,'.',',').' /'.$row->kode_satuan.'</b>
								<br/> <b>SUPPLIER : </b><b style="color:red;">'.$row->supplier.'</b>
							</td>';
							
						if( ($row->isProduk != 'JASA'))
						{
							echo'<td>
								'.number_format($row->SISA_STOCK,0,'.',',').' /'.$row->kode_satuan.'</b>
							</td>';
						}
						else
						{
							echo'<td>
							</td>';
						}
						
						echo'<td>
								<!-- <button type="button" onclick="insert_produk('.$no.')" class="btn btn-primary btn-sm" data-dismiss="modal">Pilih</button> -->
								<button id="btn_pilih_'.$no.'" type="button" onclick="insert_produk('.$no.')" class="btn btn-primary btn-sm" >Pilih</button>
							</td>';
							
						echo'<input type="hidden" id="get_tr_3_'.$no.'" name="get_tr_3_'.$no.'" value="tr_list_produk-'.$no.'" />';
						echo'<input type="hidden" id="id_produk_3_'.$no.'" name="id_produk_3_'.$no.'" value="'.$row->id_produk.'" />';
						echo'<input type="hidden" id="kode_produk_3_'.$no.'" name="kode_produk_3_'.$no.'" value="'.$row->kode_produk.'" />';
						echo'<input type="hidden" id="nama_produk_3_'.$no.'" name="nama_produk_3_'.$no.'" value="'.$row->nama_produk.'" />';
						echo'<input type="hidden" id="harga_jual_3_'.$no.'" name="harga_jual_3_'.$no.'" value="'.$row->harga_jual.'" />';
						echo'<input type="hidden" id="id_satuan_3_'.$no.'" name="id_satuan_3_'.$no.'" value="'.$row->id_satuan.'" />';
						echo'<input type="hidden" id="kode_satuan_3_'.$no.'" name="kode_satuan_3_'.$no.'" value="'.$row->kode_satuan.'" />';
						echo'<input type="hidden" id="status_3_'.$no.'" name="status_3_'.$no.'" value="'.$row->status.'" />';
						echo'<input type="hidden" id="besar_konversi_3_'.$no.'" name="besar_konversi_3_'.$no.'" value="'.$row->besar_konversi.'" />';
						
						echo'<input type="hidden" id="isProduk_3_'.$no.'" name="isProduk_3_'.$no.'" value="'.$row->isProduk.'" />';
						
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

	function scan_produk()
	{	
		//if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		//{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.isProduk IN ('PRODUK','JASA')
					AND B.besar_konversi = 1
					AND E.id_produk IS NULL
					AND A.kode_produk = '".str_replace("'","",$_POST['kode_produk'])."' ";
		//}
		
		$order_by = " ORDER BY A.nama_produk ASC ";
		
		$row_produk = $this->M_gl_produk->list_produk_limit_harga_dasar_untuk_PENJUALAN($_POST['id_h_penjualan'],$_POST['id_kat_costumer'],$_POST['media_transaksi'],$cari,$order_by,1,0);
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($row_produk))
		{
			$row_produk = $row_produk->row();
			$isProduk = $row_produk->isProduk;
			//SIMPAN D PENJUALAN
				$cek_diskon_produk = $this->M_gl_h_diskon->cek_diskon_produk($this->session->userdata('ses_kode_kantor'), $row_produk->id_produk, $_POST['id_kat_costumer'],1, $row_produk->kode_satuan, $row_produk->harga_jual);
		
				if(!empty($cek_diskon_produk))
				{
					$cek_diskon_produk = $cek_diskon_produk->row();
					
					$nama_diskon = $cek_diskon_produk->nama_diskon;
					$optr_diskon = $cek_diskon_produk->optr_diskon;
					$besar_diskon_ori = $cek_diskon_produk->besar_diskon;
					$diskon = $cek_diskon_produk->BESAR_DISKON_NOMINAL;
					$harga = $cek_diskon_produk->BESAR_DISKON_FIX;
					$harga_konversi = $cek_diskon_produk->BESAR_DISKON_FIX / $row_produk->besar_konversi;
					
					$cari_d_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_produk = '".$row_produk->id_produk."' AND id_h_diskon = '".$cek_diskon_produk->id_h_diskon."'";
					
					$cek_apakah_sudah_ada_diskon_di_d_penjualan = $this->M_gl_h_penjualan->get_d_penjualan_cari($cari_d_penjualan);
					if(!empty($cek_apakah_sudah_ada_diskon_di_d_penjualan))
					{
						$this->M_gl_h_penjualan->simpan_d_penjualan
						(
							$_POST['id_h_penjualan'],
							'',//$_POST['id_d_penerimaan'],
							$row_produk->id_produk,
							'',
							'',
							1, //$_POST['jumlah'],
							$row_produk->status, //$_POST['status_konversi'],
							$row_produk->besar_konversi, //$_POST['konversi'],
							$row_produk->kode_satuan, //$_POST['satuan_jual'],
							$row_produk->besar_konversi, //$_POST['jumlah_konversi'],
							0, //$_POST['diskon'],
							'', //$optr_diskon,
							0, //$besar_diskon_ori,
							$row_produk->harga_jual, //$_POST['harga'],
							$row_produk->harga_jual, //$_POST['harga_konversi'],
							$row_produk->harga_modal, //$_POST['harga_ori'],
							$row_produk->harga_modal, //$_POST['harga_dasar_ori'],
							0, //$_POST['stock'],
							'', //$_POST['ket_d_penjualan'],
							$row_produk->isProduk,
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						
						);
						//echo '|'.$row_produk->harga_jual.'|0||||||||||';
						echo 'get_tr_3_1|'.$row_produk->id_produk.'|'.$row_produk->kode_produk.'|'.$row_produk->nama_produk.'|'.$row_produk->id_satuan.'|'.$row_produk->kode_satuan.'|'.$row_produk->status.'|'.$row_produk->besar_konversi.'|'.$row_produk->harga_jual.'|'."".'|'."".'||'.$row_produk->harga_jual.'||'.$row_produk->isProduk;
					}
					else
					{
						/*DISKON BELUM ADA DI TRANSAKSI*/
						
							$this->M_gl_h_penjualan->simpan_d_penjualan
							(
								
								$_POST['id_h_penjualan'],
								'',//$_POST['id_d_penerimaan'],
								$row_produk->id_produk,
								$cek_diskon_produk->id_h_diskon,
								'',
								1, //$_POST['jumlah'],
								$row_produk->status, //$_POST['status_konversi'],
								$row_produk->besar_konversi, //$_POST['konversi'],
								$row_produk->kode_satuan, //$_POST['satuan_jual'],
								$row_produk->besar_konversi, //$_POST['jumlah_konversi'],
								
								$diskon, //$_POST['diskon'],
								$optr_diskon,
								$besar_diskon_ori,
								$harga, //$_POST['harga'],
								$harga_konversi, //$_POST['harga_konversi'],
								
								$row_produk->harga_modal, //$_POST['harga_ori'],
								$row_produk->harga_modal, //$_POST['harga_dasar_ori'],
								0, //$_POST['stock'],
								$nama_diskon, //$_POST['ket_d_penjualan'],
								$row_produk->isProduk,
								$this->session->userdata('ses_id_karyawan'),
								$this->session->userdata('ses_kode_kantor')
							
							
							);
							$harga_all = $harga + $diskon;
							echo 'get_tr_3_1|'.$row_produk->id_produk.'|'.$row_produk->kode_produk.'|'.$row_produk->nama_produk.'|'.$row_produk->id_satuan.'|'.$row_produk->kode_satuan.'|'.$row_produk->status.'|'.$row_produk->besar_konversi.'|'.$harga.'|'.$nama_diskon.'|'.$cek_diskon_produk->id_h_diskon.'||'.$harga_all.'|'.$cek_diskon_produk->optr_diskon.'|'.$row_produk->isProduk;
							
							//echo $cek_diskon_produk->optr_diskon;
							
							/*PROSES JIKA DISKON NYA ADA PRODUK*/
							if($cek_diskon_produk->optr_diskon == 'Produk')
							{
								$cari_d_diskon = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_h_diskon = '".$cek_diskon_produk->id_h_diskon."'";
						
								$order_by_d_diskon = " ORDER BY A.tgl_ins DESC ";
								$list_d_diskon = $this->M_gl_d_diskon->get_d_diskon_cari($cari_d_diskon,$order_by_d_diskon);
								
								if(!empty($list_d_diskon))
								{
									/*SIMPAN D DISKON*/
										$list_result = $list_d_diskon->result();
										//$no =$this->uri->segment(2,0)+1;
										$no = 1;
										foreach($list_result as $row_d_diskon)
										{
											if($row_d_diskon->status_konversi == '*')
											{
												$jumlah_konversi = $row_d_diskon->banyaknya * $row_d_diskon->konversi;
												$harga_konversi = $row_d_diskon->nominal / $row_d_diskon->konversi;
											}
											else
											{
												$jumlah_konversi = $row_d_diskon->banyaknya / $row_d_diskon->konversi;
												$harga_konversi = $row_d_diskon->nominal * $row_d_diskon->konversi;
											}
											
											$this->M_gl_h_penjualan->simpan_d_penjualan
											(
												
												$_POST['id_h_penjualan'], //$id_h_penjualan,
												'',//$id_d_penerimaan,
												$row_d_diskon->id_produk, //$id_produk,
												$row_d_diskon->id_h_diskon.''.$no, //$id_h_diskon,
												$row_d_diskon->id_d_diskon, //$id_d_diskon,
												$row_d_diskon->banyaknya, //$jumlah,
												$row_d_diskon->status_konversi, //$status_konversi,
												$row_d_diskon->konversi, //$konversi,
												$row_d_diskon->kode_satuan, //$satuan_jual,
												$jumlah_konversi, //$jumlah_konversi,
												0, //$diskon,
												'*', //$optr_diskon,
												'0', //$besar_diskon_ori,
												$row_d_diskon->nominal, //$harga,
												$harga_konversi, //$harga_konversi,
												$row_d_diskon->harga_modal, //$harga_ori,
												$row_d_diskon->harga_modal, //$harga_dasar_ori,
												0 , //$stock,
												$cek_diskon_produk->nama_diskon.'-'.$no, //$ket_d_penjualan,
												$row_d_diskon->isProduk,
												$this->session->userdata('ses_id_karyawan'),
												$this->session->userdata('ses_kode_kantor')
											
											
											);
											$no++;
										}
										
									/*SIMPAN D DISKON*/
								}
							}
							/*PROSES JIKA DISKON NYA ADA PRODUK*/
							
							
						/*DISKON BELUM ADA DI TRANSAKSI*/
					}
				}
				else
				{
					$this->M_gl_h_penjualan->simpan_d_penjualan
					(
					
						$_POST['id_h_penjualan'],
						'', //$_POST['id_d_penerimaan'],
						$row_produk->id_produk,
						'',
						'',
						1, //$_POST['jumlah'],
						$row_produk->status, //$_POST['status_konversi'],
						$row_produk->besar_konversi, //$_POST['konversi'],
						$row_produk->kode_satuan, //$_POST['satuan_jual'],
						$row_produk->besar_konversi, //$_POST['jumlah_konversi'],
						0, //$_POST['diskon'],
						'', //$optr_diskon,
						0, //$besar_diskon_ori,
						$row_produk->harga_jual, //$_POST['harga'],
						$row_produk->harga_jual, //$_POST['harga_konversi'],
						$row_produk->harga_modal, //$_POST['harga_ori'],
						$row_produk->harga_modal, //$_POST['harga_dasar_ori'],
						0, //$_POST['stock'],
						'', //$_POST['ket_d_penjualan'],
						$row_produk->isProduk,
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
						
						/*
						$_POST['id_h_penjualan'],
						$_POST['id_d_penerimaan'],
						$_POST['id_produk'],
						'',
						'',
						$_POST['jumlah'],
						$_POST['status_konversi'],
						$_POST['konversi'],
						$_POST['satuan_jual'],
						$_POST['jumlah_konversi'],
						$_POST['diskon'],
						'', //$optr_diskon,
						0, //$besar_diskon_ori,
						$_POST['harga'],
						$_POST['harga_konversi'],
						$_POST['harga_ori'],
						$_POST['harga_dasar_ori'],
						$_POST['stock'],
						$_POST['ket_d_penjualan'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
						*/
					
					);
					//echo '|'.$_POST['harga'].'|0';
					echo 'get_tr_3_1|'.$row_produk->id_produk.'|'.$row_produk->kode_produk.'|'.$row_produk->nama_produk.'|'.$row_produk->id_satuan.'|'.$row_produk->kode_satuan.'|'.$row_produk->status.'|'.$row_produk->besar_konversi.'|'.$row_produk->harga_jual.'|'."".'|'."".'||'.$row_produk->harga_jual.'||'.$row_produk->isProduk;
				}
			//SIMPAN D PENJUALAN
			
			//echo 'get_tr_3_1|'.$row_produk->id_produk.'|'.$row_produk->kode_produk.'|'.$row_produk->nama_produk.'|'.$row_produk->id_satuan.'|'.$row_produk->kode_satuan.'|'.$row_produk->status.'|'.$row_produk->besar_konversi.'|'.$row_produk->harga_jual.'|';
		}
		else
		{
			echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}
	
	function simpan_d_penjualan_test()
	{
		$hasil_cek = $this->M_gl_h_diskon->cek_kategori_produk($this->session->userdata('ses_kode_kantor'), $_POST['id_produk']);
		
		$strIdKatProduk = '';
		if(!empty($hasil_cek))
		{
			$list_result = $hasil_cek->result();
			foreach($list_result as $row)
			{
				$strIdKatProduk = $strIdKatProduk."'".$row->id_kat_produk."',";
			}
			//echo $strIdKatProduk;
		}
		else
		{
			$strIdKatProduk = "";
			//echo $strIdKatProduk;
		}
		
		$lenStrIdKatProduk = strlen($strIdKatProduk);
		
		//substr_replace("Hello","world",0); //UBAH HELO JADI WORLD
		$strIdKatProduk = substr_replace($strIdKatProduk,"",$lenStrIdKatProduk - 1,1);
		echo $strIdKatProduk;
		
	}
	
	function simpan_d_penjualan()
	{
		//CEK APAKAH ADA DISKON
		$cek_diskon_produk = $this->M_gl_h_diskon->cek_diskon_produk($this->session->userdata('ses_kode_kantor'), $_POST['id_produk'], $_POST['id_kat_costumer'], $_POST['jumlah'], $_POST['satuan_jual'], $_POST['harga']);
		
		if(!empty($cek_diskon_produk))
		{
			$cek_diskon_produk = $cek_diskon_produk;
		}
		else
		{
			$hasil_cek = $this->M_gl_h_diskon->cek_kategori_produk($this->session->userdata('ses_kode_kantor'), $_POST['id_produk']);
		
			$strIdKatProduk = '';
			if(!empty($hasil_cek))
			{
				$list_result = $hasil_cek->result();
				foreach($list_result as $row)
				{
					$strIdKatProduk = $strIdKatProduk."'".$row->id_kat_produk."',";
				}
				//echo $strIdKatProduk;
			}
			else
			{
				$strIdKatProduk = "'''";
				//echo $strIdKatProduk;
			}
			
			$lenStrIdKatProduk = strlen($strIdKatProduk);
			
			//substr_replace("Hello","world",0); //UBAH HELO JADI WORLD
			$strIdKatProduk = substr_replace($strIdKatProduk,"",$lenStrIdKatProduk - 1,1);
		
			$cek_diskon_produk = $this->M_gl_h_diskon->cek_diskon_kategori($this->session->userdata('ses_kode_kantor'), $strIdKatProduk, $_POST['id_kat_costumer'], $_POST['jumlah'], $_POST['satuan_jual'], $_POST['harga']);
		}
		
		if(!empty($cek_diskon_produk))
		{
			$cek_diskon_produk = $cek_diskon_produk->row();
			
			
			$optr_diskon = $cek_diskon_produk->optr_diskon;
			$besar_diskon_ori = $cek_diskon_produk->besar_diskon;
			$diskon = $cek_diskon_produk->besar_diskon; //$cek_diskon_produk->BESAR_DISKON_NOMINAL;
			$harga = $cek_diskon_produk->harga_jual; //$cek_diskon_produk->BESAR_DISKON_FIX;
			$harga_konversi = $cek_diskon_produk->BESAR_DISKON_FIX / $_POST['konversi'];
			
			$cari_d_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_produk = '".$_POST['id_produk']."' AND id_h_diskon = '".$cek_diskon_produk->id_h_diskon."'";
			
			$cek_apakah_sudah_ada_diskon_di_d_penjualan = $this->M_gl_h_penjualan->get_d_penjualan_cari($cari_d_penjualan);
			if(!empty($cek_apakah_sudah_ada_diskon_di_d_penjualan))
			{
				$this->M_gl_h_penjualan->simpan_d_penjualan
				(
					
					$_POST['id_h_penjualan'],
					$_POST['id_d_penerimaan'],
					$_POST['id_produk'],
					'',
					'',
					$_POST['jumlah'],
					$_POST['status_konversi'],
					$_POST['konversi'],
					$_POST['satuan_jual'],
					$_POST['jumlah_konversi'],
					$_POST['diskon'],
					'', //$optr_diskon,
					0, //$besar_diskon_ori,
					$_POST['harga'],
					$_POST['harga_konversi'],
					$_POST['harga_ori'],
					$_POST['harga_dasar_ori'],
					$_POST['stock'],
					$_POST['ket_d_penjualan'],
					$_POST['isProduk'],
					$this->session->userdata('ses_id_karyawan'),
					$this->session->userdata('ses_kode_kantor')
				
				);
				echo '|'.$_POST['harga'].'|0|||'.$_POST['harga'].'||';
			}
			else
			{
				$this->M_gl_h_penjualan->simpan_d_penjualan
				(
					
					$_POST['id_h_penjualan'],
					$_POST['id_d_penerimaan'],
					$_POST['id_produk'],
					$cek_diskon_produk->id_h_diskon,
					'',
					$_POST['jumlah'],
					$_POST['status_konversi'],
					$_POST['konversi'],
					$_POST['satuan_jual'],
					$_POST['jumlah_konversi'],
					$diskon, //$_POST['diskon'],
					$optr_diskon,
					$besar_diskon_ori,
					$harga, //$_POST['harga'],
					$harga_konversi, //$_POST['harga_konversi'],
					$_POST['harga_ori'],
					$_POST['harga_dasar_ori'],
					$_POST['stock'],
					$cek_diskon_produk->nama_diskon, //$_POST['ket_d_penjualan'],
					$_POST['isProduk'],
					$this->session->userdata('ses_id_karyawan'),
					$this->session->userdata('ses_kode_kantor')
				
				);
				$harga_all = $harga + $diskon;
				//echo $cek_diskon_produk->nama_diskon.'|'.$harga.'|'.$besar_diskon_ori.'|'.$cek_diskon_produk->id_h_diskon.'||'.$harga_all.'||Produk';
				echo $cek_diskon_produk->nama_diskon.'|'.$harga.'|'.$diskon.'|'.$cek_diskon_produk->id_h_diskon.'||'.$harga_all.'||Produk';
				//echo $cek_diskon_produk->optr_diskon.'</br>';
				
				/*PROSES JIKA DISKON NYA ADA PRODUK*/
					if($cek_diskon_produk->optr_diskon == 'Produk')
					{
						$cari_d_diskon = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_h_diskon = '".$cek_diskon_produk->id_h_diskon."'";
						
						$order_by_d_diskon = " ORDER BY A.tgl_ins DESC ";
						$list_d_diskon = $this->M_gl_d_diskon->get_d_diskon_cari($cari_d_diskon,$order_by_d_diskon);
						if(!empty($list_d_diskon))
						{
							/*SIMPAN D DISKON*/
								$list_result = $list_d_diskon->result();
								//$no =$this->uri->segment(2,0)+1;
								$no = 1;
								foreach($list_result as $row_d_diskon)
								{
									if($row_d_diskon->status_konversi == '*')
									{
										$jumlah_konversi = $row_d_diskon->banyaknya * $row_d_diskon->konversi;
										$harga_konversi = $row_d_diskon->nominal / $row_d_diskon->konversi;
									}
									else
									{
										$jumlah_konversi = $row_d_diskon->banyaknya / $row_d_diskon->konversi;
										$harga_konversi = $row_d_diskon->nominal * $row_d_diskon->konversi;
									}
									
									$this->M_gl_h_penjualan->simpan_d_penjualan
									(
										$_POST['id_h_penjualan'], //$id_h_penjualan,
										'',//$id_d_penerimaan,
										$row_d_diskon->id_produk, //$id_produk,
										$row_d_diskon->id_h_diskon.''.$no, //$id_h_diskon,
										$row_d_diskon->id_d_diskon, //$id_d_diskon,
										$row_d_diskon->banyaknya, //$jumlah,
										$row_d_diskon->status_konversi, //$status_konversi,
										$row_d_diskon->konversi, //$konversi,
										$row_d_diskon->kode_satuan, //$satuan_jual,
										$jumlah_konversi, //$jumlah_konversi,
										0, //$diskon,
										'*', //$optr_diskon,
										'0', //$besar_diskon_ori,
										$row_d_diskon->nominal, //$harga,
										$harga_konversi, //$harga_konversi,
										$row_d_diskon->harga_modal, //$harga_ori,
										$row_d_diskon->harga_modal, //$harga_dasar_ori,
										0 , //$stock,
										$cek_diskon_produk->nama_diskon.'-'.$no, //$ket_d_penjualan,
										$row_d_diskon->isProduk,
										$this->session->userdata('ses_id_karyawan'),
										$this->session->userdata('ses_kode_kantor')
											
											
									);
									$no++;
									//echo "TERIMAPN D DISKON";
								}
								
							/*SIMPAN D DISKON*/
						}
					}
				/*PROSES JIKA DISKON NYA ADA PRODUK*/
			}
		}
		else
		{
			$this->M_gl_h_penjualan->simpan_d_penjualan
			(
				
				$_POST['id_h_penjualan'],
				$_POST['id_d_penerimaan'],
				$_POST['id_produk'],
				'',
				'',
				$_POST['jumlah'],
				$_POST['status_konversi'],
				$_POST['konversi'],
				$_POST['satuan_jual'],
				$_POST['jumlah_konversi'],
				$_POST['diskon'],
				'', //$optr_diskon,
				0, //$besar_diskon_ori,
				$_POST['harga'],
				$_POST['harga_konversi'],
				$_POST['harga_ori'],
				$_POST['harga_dasar_ori'],
				$_POST['stock'],
				$_POST['ket_d_penjualan'],
				$_POST['isProduk'],
				$this->session->userdata('ses_id_karyawan'),
				$this->session->userdata('ses_kode_kantor')
			
			);
			//echo '|'.$_POST['harga'].'|0';
			//echo '|'.$_POST['harga'].'|0||||';
			echo '|'.$_POST['harga'].'|0|||'.$_POST['harga'].'||';
		}
		
		//CEK UNTUK CONSUMBALE JIKA JASA, JADI LANGSUNG MASUKAN CONSUMBALE NYA
		if($_POST['isProduk'] == "JASA")
		{
			$cek_di_hpp_jasa = $this->M_gl_hpp_jasa->get_hpp_jasa_cari(" WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_produk = '".$_POST['id_produk']."' AND id_assets = '' AND id_produk_hpp <> ''");
			if(!empty($cek_di_hpp_jasa))
			{
				$get_row_hpp_jasa = $cek_di_hpp_jasa->row();
				
				//ID_D_PENERIMAAN DI TB_D_PENJUALAN BISA DI PAKAI ID_PRODUK UNTUK CONSUMABLE
				//HAPUS DL DATA SEBELUMNYA
					$cari_delete_di_d_penjualan = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_d_penerimaan = '".$get_row_hpp_jasa->id_produk."' AND isProduk = 'CONSUMABLE'";
					$this->M_gl_h_penjualan->hapus_d_penjualan_with_cari($cari_delete_di_d_penjualan);
				//HAPUS DL DATA SEBELUMNYA
				//ID_D_PENERIMAAN DI TB_D_PENJUALAN BISA DI PAKAI ID_PRODUK UNTUK CONSUMABLE
			
				$list_result = $cek_di_hpp_jasa->result();
				foreach($list_result as $row)
				{
					$this->M_gl_h_penjualan->simpan_d_penjualan
					(
						
						$_POST['id_h_penjualan'],
						$_POST['id_produk'], //$_POST['id_d_penerimaan'],
						$row->id_produk_hpp,
						'',
						'',
						$row->banyaknya, //['jumlah'],
						'*', //$_POST['status_konversi'],
						'1', //$_POST['konversi'],
						$row->satuan, //$_POST['satuan_jual'],
						$row->banyaknya, //$_POST['jumlah_konversi'],
						0, //$_POST['diskon'],
						'%', //'', //$optr_diskon,
						0, //0, //$besar_diskon_ori,
						$row->harga, //$_POST['harga'],
						$row->harga, //$_POST['harga_konversi'],
						$row->harga, //$_POST['harga_ori'],
						$row->harga, //$_POST['harga_dasar_ori'],
						0, //$_POST['stock'],
						$row->ket_hpp, //$_POST['ket_d_penjualan'],
						'CONSUMABLE', //$_POST['isProduk'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					
					);
				}
				
			}
		}
		
		//GET DATA PEMBELIAN JIKA ADA
			$get_data_h_pembelian = $this->M_gl_d_pembelian->get_data_by_query("SELECT * FROM tb_h_pembelian WHERE no_h_pembelian = (SELECT DISTINCT ket_penjualan FROM tb_h_penjualan WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' GROUP BY ket_penjualan)");
			if(!empty($get_data_h_pembelian))
			{
				$get_data_h_pembelian = $get_data_h_pembelian->row();
				
				$this->M_gl_d_pembelian->simpan
				(
					$get_data_h_pembelian->id_h_pembelian, //$_POST['id_h_pembelian'],
					$_POST['id_produk'], //$_POST['id_produk'],
					$_POST['jumlah'],
					$_POST['harga'],
					$_POST['harga_ori'],
					$_POST['diskon'],
					'',//$_POST['optr_diskon'],
					$_POST['satuan_jual'], //$_POST['kode_satuan'],
					$_POST['satuan_jual'], //$_POST['nama_satuan'],
					$_POST['status_konversi'], //$_POST['status_konversi'],
					$_POST['konversi'], //$_POST['konversi'],
					'1', //$acc,
					$get_data_h_pembelian->user_ins,
					$get_data_h_pembelian->kode_kantor
				);
				
			}
		//GET DATA PEMEBLIAN JIKA ADA
		
	}
	
	function list_tampilkan_d_penjualan()
	{
		
		if((!empty($_POST['id_kat_costumer'])) && ($_POST['id_kat_costumer']!= "")  )
		{
			$id_kat_costumer = str_replace("'","",$_POST['id_kat_costumer']);
		}
		else
		{
			$id_kat_costumer = "";
		}
		
		/*LIST PAKET YANG SESUAI*/
			//if(!empty($data_costumer))
			if($id_kat_costumer <> "")
			{
				$cari_h_diskon_yang_sesuai_dengan_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (id_kat_costumer = '".$id_kat_costumer."' OR id_kat_costumer = '') AND optr_diskon = 'Paket' AND DATE(NOW()) >= DATE(dari) AND DATE(NOW()) <= DATE(sampai)";
			}
			else
			{
				$cari_h_diskon_yang_sesuai_dengan_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_kat_costumer = '' AND optr_diskon = 'Paket' AND DATE(NOW()) >= DATE(dari) AND DATE(NOW()) <= DATE(sampai)";
			}
			
			$cek_diskon_yang_sesuai_dengan_kat_diskon = $this->M_gl_h_diskon->get_h_diskon_cari($cari_h_diskon_yang_sesuai_dengan_kat_costumer);
		/*LIST PAKET YANG SESUAI*/
		
		if((!empty($_POST['cari'])) && ($_POST['cari']!= "")  )
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.id_d_penerimaan = ''
					AND A.id_h_penjualan = '".$_POST['id_h_penjualan']."'
					AND (B.kode_produk LIKE '%".str_replace("'","",$_POST['cari'])."%' OR B.nama_produk LIKE '%".str_replace("'","",$_POST['cari'])."%')";
		}
		else
		{
			$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.id_d_penerimaan = ''
					AND A.id_h_penjualan = '".$_POST['id_h_penjualan']."'";
		}
		
		$order_by = " ORDER BY A.tgl_ins DESC ";
		
		$list_d_penjualan = $this->M_gl_h_penjualan->list_d_penjualan($cari,$order_by,1000,0);
		//($cari,$_POST['limit'],$_POST['offset']);
		
		if(!empty($list_d_penjualan))
		{
				$list_result = $list_d_penjualan->result();
				//$no =$this->uri->segment(2,0)+1;
				$no = 1;
				foreach($list_result as $row)
				{
					//echo'<tr id="tr_'.$no.'">';
					
					//echo'<tr id="tr_list_transaksi-'.$row->id_produk.'-'.$row->id_h_diskon.'" >';
					
					
					
					echo'<tr id="'.$row->id_produk.''.$row->id_h_diskon.'" >';
						echo'<td><input type="hidden" id="id_produk-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="id_produk-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->id_produk.'" /> '.$row->kode_produk.'</td>';
						
						echo'<td>'.$row->nama_produk.'</td>';
						
						echo'<td><input type="text" name="jumlah-'.$row->id_produk.'-'.$row->id_h_diskon.'" id="jumlah-'.$row->id_produk.'-'.$row->id_h_diskon.'" style="text-align:right;" maxlength="4" size="4" onkeypress="return isNumberKey(event)" onchange="edit_d_penjualan(this.id)" onfocusout="getRupiah(this.id)" class="required select2" value="'.$row->jumlah.'" /></td>';
						
						echo'<td style="text-align:center;"><select width="100%" name="satuan_jual-'.$row->id_produk.'-'.$row->id_h_diskon.'" id="satuan_jual-'.$row->id_produk.'-'.$row->id_h_diskon.'" class="required" onfocus="tampilkan_list_satuan(this.id)"  onchange="harga_satuan(this.id)" ><option value="'.$row->satuan_jual.'">'.$row->satuan_jual.'</option></select></td>';
						
						if( ($this->session->userdata('ses_akses_lvl3_531') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
						{
							echo'<td style="text-align:right;"><input type="text" name="harga-'.$row->id_produk.'-'.$row->id_h_diskon.'" id="harga-'.$row->id_produk.'-'.$row->id_h_diskon.'" style="text-align:right;" maxlength="15" size="7" onkeypress="return isNumberKey(event)" onchange="edit_d_penjualan(this.id)" onfocusout="getRupiah(this.id)" class="required select2" value="'.number_format($row->harga,0,'.',',').'" /></td>';
							
							echo'<td style="text-align:right;"><input type="text" name="diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" id="diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" style="text-align:right;" maxlength="15" size="7" onkeypress="return isNumberKey(event)" onchange="edit_d_penjualan(this.id)" onfocusout="getRupiah(this.id)" class="required select2" value="'.number_format($row->diskon,0,'.',',').'" /></td>';
							
							echo'<td style="text-align:center;"><select width="100%" name="optr_diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" id="optr_diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" class="required" onfocus="tampilkan_list_satuan(this.id)"  onchange="edit_d_penjualan(this.id)" ><option value="'.$row->optr_diskon.'">'.$row->optr_diskon.'</option><option value="%">%</option><option value="C">C</option></select></td>';
							
						}
						else
						{
							echo'<td style="text-align:right;"><input type="text" name="harga-'.$row->id_produk.'-'.$row->id_h_diskon.'" id="harga-'.$row->id_produk.'-'.$row->id_h_diskon.'" style="text-align:right;" maxlength="15" size="7" onkeypress="return isNumberKey(event)" onchange="edit_d_penjualan(this.id)" onfocusout="getRupiah(this.id)" class="required select2" value="'.number_format($row->harga,0,'.',',').'" readonly/></td>';
							
							echo'<td style="text-align:right;"><input type="text" name="diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" id="diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" style="text-align:right;" maxlength="15" size="7" onkeypress="return isNumberKey(event)" onchange="edit_d_penjualan(this.id)" onfocusout="getRupiah(this.id)" class="required select2" value="'.number_format($row->diskon,0,'.',',').'" /></td>';
							
							echo'<td style="text-align:center;"><select width="100%" name="optr_diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" id="optr_diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" class="required" onfocus="tampilkan_list_satuan(this.id)"  onchange="edit_d_penjualan(this.id)" ><option value="'.$row->optr_diskon.'">'.$row->optr_diskon.'</option><option value="%">%</option><option value="C">C</option></select></td>';
						}
						
						echo'<td style="text-align:right;"><input style="text-align:right;background-color:#d7d7d6;" type="text" name="ed_subtotal" id="ed_subtotal-'.$row->id_produk.'-'.$row->id_h_diskon.'"  maxlength="15" size="7" onkeypress="return isNumberKey(event)" onfocusout="getRupiah(this.id)" class="required select2" value="'.number_format( ($row->harga - $row->besar_diskon_ori) * $row->jumlah,0,'.',',').'" disabled="true" /></td>';
						
						echo'<td>';
						
						if($this->session->userdata('ses_gnl_isToko') == 'Y') //MEMASTIKAN IS TOKO
						{
							echo '<input class="toggle" type="checkbox" />';
							//echo '<input class="toggle" type="checkbox" style="visibility:hidden;" />';
							echo'<input type="hidden" name="aturan_minum-'.$row->id_produk.'-'.$row->id_h_diskon.'" id="aturan_minum-'.$row->id_produk.'-'.$row->id_h_diskon.'" class="aturan_minum" style="text-align:right;" maxlength="35" size="18" class="required select2" onchange="edit_d_penjualan(this.id)" value="'.$row->aturan_minum.'" /> ';
						}
						else
						{
							echo '<input class="toggle" type="checkbox" style="display:none;" />';
							echo'<input type="text" name="aturan_minum-'.$row->id_produk.'-'.$row->id_h_diskon.'" id="aturan_minum-'.$row->id_produk.'-'.$row->id_h_diskon.'" class="aturan_minum" style="text-align:right;" maxlength="35" size="18" class="required select2" onchange="edit_d_penjualan(this.id)" value="'.$row->aturan_minum.'" /> ';
						}
						
						
						
						echo'</td>';
						
						if(($row->id_produk == "") AND ($row->id_d_diskon != ""))
						{
							echo'<td>
								<input type="hidden" id="id_d_penjualan-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="id_d_penjualan-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->id_d_penjualan.'" />
								
								<input type="hidden" id="id_kat_produk-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="id_kat_produk-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->id_kat_produk.'" />
								';
								
								
								echo'
								<font style="color:red;">
									<span id="ket_d_penjualan-'.$row->id_produk.'-'.$row->id_h_diskon.'">
										'.$row->ket_d_penjualan.'
										<button id="id_kat_produk-'.$row->id_produk.'-'.$row->id_h_diskon.'-btn-'.$no.'" class="btn btn-info btn-sm btn-flat" style="float:left;" type="button" title="Cari Produk" data-toggle="modal" data-target="#myModal_produk" onclick="list_data_produk_kategori(this)" >PRODUK/TINDAKAN</button>
									</span>
								</font>
								
								<input type="hidden" id="id_d_penjualan_untuk_kategori-'.$no.'"  value="'.$row->id_d_penjualan.'" />
						
								<input type="hidden" id="id_kat_produk_untuk_kategori-'.$no.'" value="'.$row->id_kat_produk.'" />
							</td>';
						}
						else
						{
							echo'<td>
								<input type="hidden" id="id_d_penjualan-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="id_d_penjualan-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->id_d_penjualan.'" />
								
								<input type="hidden" id="id_kat_produk-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="id_kat_produk-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->id_kat_produk.'" />
								';
								
								
								echo'
								
								<font style="color:red;"><span id="ket_d_penjualan-'.$row->id_produk.'-'.$row->id_h_diskon.'">'.$row->ket_d_penjualan.'</span></font>
								
								<input type="hidden" id="id_d_penjualan_untuk_kategori-'.$no.'"  value="'.$row->id_d_penjualan.'" />
						
								<input type="hidden" id="id_kat_produk_untuk_kategori-'.$no.'" value="'.$row->id_kat_produk.'" />
								
							</td>';
								
								
						}
						
						//PROMO PADA DET PENJUALAN
						
							echo'<td style="text-align:center;">
									<select width="100%" id="id_d_diskon2-'.$row->id_produk.'-'.$row->id_h_diskon.'" class="required" onfocus="tampilkan_promo(this)"  onchange="edit_promo(this)" >
										
										<option value="'.$row->id_d_diskon2.'">'.$row->nama_diskon.'</option>';
										if(!empty($cek_diskon_yang_sesuai_dengan_kat_diskon))
										{
											$list_result_promo = $cek_diskon_yang_sesuai_dengan_kat_diskon->result();
											foreach($list_result_promo as $row_promo)
											{
												echo'<option value="'.$row_promo->id_h_diskon.'">'.$row_promo->nama_diskon.'</option>';
											}
										}
							echo'
									</select>
								</td>';
								
						echo'<td><input type="hidden" id="satuan_jual_old-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="satuan_jual_old-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->satuan_jual.'" readonly /></td>';
						//PROMO PADA DET PENJUALAN
						
						
						echo'<input type="hidden" id="status_konversi-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="status_konversi-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->status_konversi.'" />';
						
						echo'<input type="hidden" id="konversi-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="konversi-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->konversi.'" />';
						
						echo'<input type="hidden" id="harga_ori-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="harga_ori-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->harga_ori.'" />';
						
						
						echo'<input type="hidden" id="id_h_diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="id_h_diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->id_h_diskon.'" />';
						
						echo'<input type="hidden" id="id_d_diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="id_d_diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->id_d_diskon.'" />';
						
						//echo'<input type="text" id="satuan_jual_old-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="satuan_jual_old-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->satuan_jual.'" />';
						
						echo'<input type="hidden" id="harga_tambah_diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="harga_tambah_diskon-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.($row->harga + $row->diskon).'" /> <input type="hidden" id="isProduk-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="isProduk-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->isProduk.'" />';
						
						//echo'<input type="text" id="id_kat_produk-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="id_kat_produk-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->id_kat_produk.'" />';
						
						//echo'<input type="hidden" id="id_d_penjualan-'.$row->id_produk.'-'.$row->id_h_diskon.'" name="id_d_penjualan-'.$row->id_produk.'-'.$row->id_h_diskon.'" value="'.$row->id_d_penjualan.'" />';
						
						
						
					echo'</tr>';
					
					$no++;
				}
				
		}
		else
		{
			//echo "TIDAK ADA DATA YANG DITAMPILKAN";
		}
	}
	
	function combo_list_h_diskon()
	{
		if((!empty($_POST['id_kat_costumer'])) && ($_POST['id_kat_costumer']!= "")  )
		{
			$id_kat_costumer = str_replace("'","",$_POST['id_kat_costumer']);
		}
		else
		{
			$id_kat_costumer = "";
		}
		
		/*LIST PAKET YANG SESUAI*/
			//if(!empty($data_costumer))
			if($id_kat_costumer <> "")
			{
				$cari_h_diskon_yang_sesuai_dengan_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND (id_kat_costumer = '".$id_kat_costumer."' OR id_kat_costumer = '') AND optr_diskon = 'Paket' AND DATE(NOW()) >= DATE(dari) AND DATE(NOW()) <= DATE(sampai)";
			}
			else
			{
				$cari_h_diskon_yang_sesuai_dengan_kat_costumer = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_kat_costumer = '' AND optr_diskon = 'Paket' AND DATE(NOW()) >= DATE(dari) AND DATE(NOW()) <= DATE(sampai)";
			}
			
			$cek_diskon_yang_sesuai_dengan_kat_diskon = $this->M_gl_h_diskon->get_h_diskon_cari($cari_h_diskon_yang_sesuai_dengan_kat_costumer);
		/*LIST PAKET YANG SESUAI*/
		
		if(!empty($cek_diskon_yang_sesuai_dengan_kat_diskon))
		{
			$cek_diskon_yang_sesuai_dengan_kat_diskon = $cek_diskon_yang_sesuai_dengan_kat_diskon->result();
			
			echo '<option value=""></option>';
			
			foreach($cek_diskon_yang_sesuai_dengan_kat_diskon as $row)
			{
				echo '<option value="'.$row->id_h_diskon.'">'.$row->nama_diskon.'</option>';
			}
		}
		
		/*
		
		$cari = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
				AND A.id_produk = '".$_POST['id_produk']."'";
		$order_by = " ORDER BY COALESCE(B.kode_satuan,'') ASC";
		
		$list_satuan = $this->M_gl_satuan_konversi->list_data_satuan_konversi($cari,$order_by,0,30);
		if(!empty($list_satuan))
		{
			$list_result = $list_satuan->result();
			foreach($list_result as $row)
			{
				echo '<option value="'.$row->kode_satuan.'">'.$row->kode_satuan.'</option>';
			}
		}
		*/
	}
	
	function combo_satuan_harga()
	{
		
		$list_satuan = $this->M_gl_h_penjualan->harga_costumer_satuanharga_costumer_satuan($this->session->userdata('ses_kode_kantor'),$_POST['id_produk'],$_POST['id_kat_costumer'],$_POST['kode_satuan']);
		if(!empty($list_satuan))
		{
			$list_satuan = $list_satuan->row();
			echo $list_satuan->harga.'|'.$list_satuan->status.'|'.$list_satuan->besar_konversi.'|'.$list_satuan->harga_modal;
		}
		else
		{
			$cari_harga_dasar_saja = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_produk = '".$_POST['id_produk']."' AND COALESCE(B.kode_satuan,'') = '".$_POST['kode_satuan']."'";
			
			$cek_harga_dasar = $this->M_gl_h_penjualan->get_harga_dasar_satuan_konversi($cari_harga_dasar_saja);
			if(!empty($cek_harga_dasar))
			{
				$cek_harga_dasar = $cek_harga_dasar->row();
				echo $cek_harga_dasar->harga_jual.'|'.$cek_harga_dasar->status.'|'.$cek_harga_dasar->besar_konversi.'|'.$cek_harga_dasar->harga_jual;
			}
			else
			{
				echo "0";
			}
		}
		
	}
	
	
	function ubah_d_penjualan()
	{
		
		$id_h_penjualan = $_POST['id_h_penjualan'];
		$id_produk = $_POST['id_produk'];
		$kode_satuan = $_POST['satuan_jual'];
		$ket_d_penjualan = $_POST['ket_d_penjualan'];
		
		
		if(str_replace(",","",$_POST['jumlah']) > 0)
		{
			$kode_satuan_old = $_POST['satuan_jual_old'];
			$harga_tambah_diskon = $_POST['harga_tambah_diskon'];
			//echo"MASUK IF 1";
			
			if($_POST['id_h_diskon'] == "KOSONG")
			{
				$id_h_diskon = "";
			}
			else
			{
				$id_h_diskon = $_POST['id_h_diskon'];
			}
			$cari_d_penjualan = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_h_penjualan = '".$id_h_penjualan."' AND A.id_produk = '".$id_produk."' AND COALESCE(B.kode_satuan,'') = '".$kode_satuan_old."' AND A.id_h_diskon = '".$id_h_diskon."'";
			
			$cek_d_penjualan = $this->M_gl_h_penjualan->get_d_penjualan_with_satuan_cari($cari_d_penjualan);
			if(!empty($cek_d_penjualan))
			{
				$cek_d_penjualan = $cek_d_penjualan->row();
				
				/*CEK DISKON*/
					//$cek_diskon_produk = $this->M_gl_h_diskon->cek_diskon_produk($this->session->userdata('ses_kode_kantor'), $_POST['id_produk'], $_POST['id_kat_costumer'], str_replace(",","",$_POST['jumlah']), $_POST['satuan_jual'], str_replace(",","",$_POST['harga_tambah_diskon']));
					$cek_diskon_produk = $this->M_gl_h_diskon->cek_diskon_produk($this->session->userdata('ses_kode_kantor'), $_POST['id_produk'], $_POST['id_kat_costumer'], str_replace(",","",$_POST['jumlah']), $_POST['satuan_jual'], str_replace(",","",$_POST['harga']));
			
					if(!empty($cek_diskon_produk))
					{
						$cek_diskon_produk = $cek_diskon_produk->row();
						
						$optr_diskon = $cek_diskon_produk->optr_diskon;
						$besar_diskon_ori = $cek_diskon_produk->besar_diskon;
						$diskon = $besar_diskon_ori; //$cek_diskon_produk->BESAR_DISKON_NOMINAL;
						$harga = $cek_diskon_produk->harga_jual; //$cek_diskon_produk->BESAR_DISKON_FIX;
						$harga_konversi = $cek_diskon_produk->BESAR_DISKON_FIX / $_POST['konversi'];
						$nama_diskon = $cek_diskon_produk->nama_diskon;
						
						if($_POST['status_konversi'] == '*')
						{
							$jumlah_konversi = str_replace(",","",$_POST['jumlah']) * $_POST['konversi'];
						}
						else
						{
							$jumlah_konversi = str_replace(",","",$_POST['jumlah']) / $_POST['konversi'];
						}
						
						$cari_untuk_ubah = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$id_h_penjualan."' AND id_produk = '".$id_produk."' AND satuan_jual = '".$kode_satuan_old."' AND ket_d_penjualan = '".$ket_d_penjualan."'";
						$this->M_gl_h_penjualan->ubah_d_penjualan
						(
							$cari_untuk_ubah, //$cari,
							$id_h_penjualan,
							$cek_d_penjualan->id_d_penerimaan,
							$cek_d_penjualan->id_produk,
							$cek_d_penjualan->id_h_diskon,
							$cek_d_penjualan->id_d_diskon,
							str_replace(",","",$_POST['jumlah']),
							$_POST['status_konversi'],
							$_POST['konversi'],
							$_POST['satuan_jual'],
							$jumlah_konversi,
							$diskon,
							$optr_diskon,
							$besar_diskon_ori,
							$harga,
							$harga, //$harga_konversi,
							$cek_d_penjualan->harga_ori, //str_replace(",","",$_POST['harga_ori']), //harga_ori
							$cek_d_penjualan->harga_dasar_ori, //str_replace(",","",$_POST['harga_ori']), //$harga_dasar_ori,
							'0', //$stock,
							$nama_diskon, //$ket_d_penjualan,
							$_POST['aturan_minum'], //$aturan_minum,
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')

						);
						
						
						//echo $harga.'|'.str_replace(",","",$_POST['harga_ori']).'|'.$nama_diskon.'|BERHASIl DENGAN DISKON|'.$cek_diskon_produk->optr_diskon;
						echo $harga.'|'.$cek_d_penjualan->harga_ori.'|'.$nama_diskon.'|BERHASIl DENGAN DISKON|'.$cek_diskon_produk->optr_diskon.'|'.$diskon;
						
						/*PROSES JIKA DISKON NYA ADA PRODUK*/
							if($cek_diskon_produk->optr_diskon == 'Produk')
							{
								$cari_d_diskon = " WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND A.id_h_diskon = '".$cek_diskon_produk->id_h_diskon."'";
								
								$order_by_d_diskon = " ORDER BY A.tgl_ins DESC ";
								$list_d_diskon = $this->M_gl_d_diskon->get_d_diskon_cari($cari_d_diskon,$order_by_d_diskon);
								if(!empty($list_d_diskon))
								{
									/*SIMPAN D DISKON*/
										$list_result = $list_d_diskon->result();
										//$no =$this->uri->segment(2,0)+1;
										$no = 1;
										foreach($list_result as $row_d_diskon)
										{
											if($row_d_diskon->status_konversi == '*')
											{
												$jumlah_konversi = $row_d_diskon->banyaknya * $row_d_diskon->konversi;
												$harga_konversi = $row_d_diskon->nominal / $row_d_diskon->konversi;
											}
											else
											{
												$jumlah_konversi = $row_d_diskon->banyaknya / $row_d_diskon->konversi;
												$harga_konversi = $row_d_diskon->nominal * $row_d_diskon->konversi;
											}
											
											$this->M_gl_h_penjualan->simpan_d_penjualan
											(
												$_POST['id_h_penjualan'], //$id_h_penjualan,
												'',//$id_d_penerimaan,
												$row_d_diskon->id_produk, //$id_produk,
												$row_d_diskon->id_h_diskon.''.$no, //$id_h_diskon,
												$row_d_diskon->id_d_diskon, //$id_d_diskon,
												$row_d_diskon->banyaknya, //$jumlah,
												$row_d_diskon->status_konversi, //$status_konversi,
												$row_d_diskon->konversi, //$konversi,
												$row_d_diskon->kode_satuan, //$satuan_jual,
												$jumlah_konversi, //$jumlah_konversi,
												0, //$diskon,
												'*', //$optr_diskon,
												'0', //$besar_diskon_ori,
												$row_d_diskon->nominal, //$harga,
												$harga_konversi, //$harga_konversi,
												$row_d_diskon->harga_modal, //$harga_ori,
												$row_d_diskon->harga_modal, //$harga_dasar_ori,
												0 , //$stock,
												$cek_diskon_produk->nama_diskon.'-'.$no, //$ket_d_penjualan,
												$row_d_diskon->isProduk,
												
												$this->session->userdata('ses_id_karyawan'),
												$this->session->userdata('ses_kode_kantor')
													
													
											);
											$no++;
											//echo "TERIMAPN D DISKON";
										}
										
									/*SIMPAN D DISKON*/
								}
							}
						/*PROSES JIKA DISKON NYA ADA PRODUK*/
						
					}
					else
					{
						$harga_konversi = str_replace(",","",$_POST['harga']) / $_POST['konversi'];
						
						if($_POST['status_konversi'] == '*')
						{
							$jumlah_konversi = str_replace(",","",$_POST['jumlah']) * $_POST['konversi'];
						}
						else
						{
							$jumlah_konversi = str_replace(",","",$_POST['jumlah']) / $_POST['konversi'];
						}
						
						$cari_untuk_ubah = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$id_h_penjualan."' AND id_produk = '".$id_produk."' AND satuan_jual = '".$kode_satuan_old."' AND ket_d_penjualan = '".$ket_d_penjualan."'";
						
						if(($_POST['diskon'] > 0))
						{
							if($_POST['optr_diskon'] == "%")
							{
								$besar_diskon_ori = ($harga_konversi/100) * $_POST['diskon'];
							}
							else
							{
								$besar_diskon_ori = $_POST['diskon'];
							}
						}
						else
						{
							$besar_diskon_ori = 0;
						}
						
						$this->M_gl_h_penjualan->ubah_d_penjualan
						(
							$cari_untuk_ubah, //$cari,
							$id_h_penjualan,
							$cek_d_penjualan->id_d_penerimaan,
							$cek_d_penjualan->id_produk,
							$cek_d_penjualan->id_h_diskon,
							$cek_d_penjualan->id_d_diskon,
							str_replace(",","",$_POST['jumlah']),
							$_POST['status_konversi'],
							$_POST['konversi'],
							$_POST['satuan_jual'],
							$jumlah_konversi,
							$_POST['diskon'], //$diskon,
							$_POST['optr_diskon'], //$optr_diskon,
							$besar_diskon_ori, //$besar_diskon_ori,
							str_replace(",","",$_POST['harga']),
							$harga_konversi,
							str_replace(",","",$_POST['harga_ori']),
							str_replace(",","",$_POST['harga_ori']), //$harga_dasar_ori,
							'0', //$stock,
							'', //$ket_d_penjualan,
							$_POST['aturan_minum'], //$aturan_minum,
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						);
						///echo"BERHASIl TANPA DISKON";
						echo str_replace(",","",$_POST['harga']).'|'.str_replace(",","",$_POST['harga_ori']).'||BERHASIl TANPA DISKON';
						
						//echo str_replace(",","",number_format(($harga_konversi -  $besar_diskon_ori),0,'.',',')).'|'.str_replace(",","",$_POST['harga_ori']).'||BERHASIl TANPA DISKON';
					}
				/*CEK DISKON*/
				
			}
		}
		else
		{
			
			//JIKA PEMESNA CABANG hapus_d_pembelian_with_cari
			$query_delete_order_cb = "
										DELETE tb_d_pembelian
										FROM tb_d_pembelian
										INNER JOIN 
										(
											SELECT
												A.*
												,COALESCE(B.ket_penjualan,'') AS ket_penjualan
												,COALESCE(C.id_h_pembelian,'') AS id_h_pembelian
												,COALESCE(C.kode_kantor,'') AS kode_kantor_beli
											FROM tb_d_penjualan AS A 
											INNER JOIN tb_h_penjualan AS B ON A.kode_kantor = B.kode_kantor AND A.id_h_penjualan = B.id_h_penjualan
											INNER JOIN tb_h_pembelian AS C ON B.ket_penjualan = C.no_h_pembelian
											WHERE A.id_h_penjualan = '".$id_h_penjualan."'
											AND A.id_produk = '".$id_produk."'
											AND A.satuan_jual = '".$kode_satuan."'
											AND A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'
										) AS B ON tb_d_pembelian.kode_kantor = B.kode_kantor_beli 
										AND tb_d_pembelian.id_h_pembelian = B.id_h_pembelian
										AND tb_d_pembelian.id_produk = B.id_produk
										AND tb_d_pembelian.kode_satuan = B.satuan_jual
										WHERE B.id_produk = '".$id_produk."';
			";
			$this->M_gl_h_penjualan->hapus_d_pembelian_with_cari($query_delete_order_cb);
			//JIKA PEMESNA CABANG hapus_d_pembelian_with_cari
			
			$cari_delete = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$id_h_penjualan."' AND id_produk = '".$id_produk."' AND satuan_jual = '".$kode_satuan."' AND ket_d_penjualan = '".$ket_d_penjualan."'";
			$this->M_gl_h_penjualan->hapus_d_penjualan_with_cari($cari_delete);
			
			
			//ID_D_PENERIMAAN DI TB_D_PENJUALAN BISA DI PAKAI ID_PRODUK UNTUK CONSUMABLE
			$cari_delete_2_consumable = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$id_h_penjualan."' AND id_d_penerimaan = '".$id_produk."' AND isProduk = 'CONSUMABLE'";
			$this->M_gl_h_penjualan->hapus_d_penjualan_with_cari($cari_delete_2_consumable);
			//ID_D_PENERIMAAN DI TB_D_PENJUALAN BISA DI PAKAI ID_PRODUK UNTUK CONSUMABLE
			
			
			
			
			echo $id_produk;
		}
		
	}

	function selesai_transaksi()
	{
		//HITUNG HPP DARI tb_satuan_konversi dulu
			/*
			$this->M_gl_h_penjualan->update_hpp_dari_tb_satuan_konversi($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan']);
			*/
		//HITUNG HPP DARI tb_satuan_konversi dulu
		
		/*PROSES PEMBAYARAN*/
			//if($_POST['is_allow_bayar'] == 1)
			if(($_POST['is_allow_bayar'] == 1) || ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi'))
			{
			//CASH
				//if(id_d_bayar_cash)
				if((empty($_POST['id_d_bayar_cash'])) && ($_POST['id_d_bayar_cash'] == "")  )
				{
					$this->M_gl_h_penjualan->simpan_d_penjualan_bayar
					(
						$_POST['id_h_penjualan'],
						'', //$_POST['id_bank'],
						$_POST['id_costumer'],
						'', //$_POST['no_pembayaran'],
						0, //$_POST['isTabungan'],
						'CASH', //$_POST['cara'],
						'', //$_POST['jenis'],
						'', //$_POST['norek'],
						'', //$_POST['nama_bank'],
						'', //$_POST['atas_nama'],
						date("Y-m-d"), //$_POST['tgl_pencairan'],
						$_POST['bayar_cash'],
						'', //$_POST['ket'],
						date("Y-m-d"), //$_POST['tgl_bayar'],
						'', //$_POST['dari'],
						'', //$_POST['diterima_oleh'],
						'1', //$_POST['isCair'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				else
				{
					//AND cara = 'CASH'  -> KARENA BISA JADI METODE NTA TF,EDC dll, tp tidak ada bank nya
					$cari_bayar_cash = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_d_bayar = '".$_POST['id_d_bayar_cash']."' ";
					$this->M_gl_h_penjualan->edit_d_penjualan_bayar
					(
						$_POST['id_d_bayar_cash'],
						$_POST['id_h_penjualan'],
						'', //$_POST['id_bank'],
						$_POST['id_costumer'],
						'', //$_POST['no_pembayaran'],
						0, //$_POST['isTabungan'],
						'CASH', //$_POST['cara'],
						'', //$_POST['jenis'],
						'', //$_POST['norek'],
						'', //$_POST['nama_bank'],
						'', //$_POST['atas_nama'],
						date("Y-m-d"), //$_POST['tgl_pencairan'],
						$_POST['bayar_cash'],
						'', //$_POST['ket'],
						date("Y-m-d"), //$_POST['tgl_bayar'],
						'', //$_POST['dari'],
						'', //$_POST['diterima_oleh'],
						'1', //$_POST['isCair'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor'),
						$cari_bayar_cash
					);
				}
			//CASH
			
			//BANK
				if($_POST['nominal_bank'] > 0)
				{
					if((empty($_POST['id_d_bayar_bank'])) && ($_POST['id_d_bayar_bank'] == "")  )
					{
						
						$this->M_gl_h_penjualan->simpan_d_penjualan_bayar
						(
							$_POST['id_h_penjualan'],
							$_POST['id_bank'],
							$_POST['id_costumer'],
							'', //$_POST['no_pembayaran'],
							0, //$_POST['isTabungan'],
							$_POST['cara'],
							'', //$_POST['jenis'],
							$_POST['norek'],
							$_POST['nama_bank'],
							$_POST['atas_nama'],
							$_POST['tgl_pencairan'],
							$_POST['nominal_bank'],
							$_POST['ket_bank'],
							$_POST['tgl_bayar'],
							'', //$_POST['dari'],
							'', //$_POST['diterima_oleh'],
							1, //$_POST['isCair'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					else
					{
						$cari_d_bayar_bank_for_edit = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_d_bayar = '".$_POST['id_d_bayar_bank']."' AND cara <> 'CASH' ";
						$this->M_gl_h_penjualan->edit_d_penjualan_bayar
						(
							$_POST['id_d_bayar_bank'],
							$_POST['id_h_penjualan'],
							$_POST['id_bank'],
							$_POST['id_costumer'],
							'', //$_POST['no_pembayaran'],
							0, //$_POST['isTabungan'],
							$_POST['cara'],
							'', //$_POST['jenis'],
							$_POST['norek'],
							$_POST['nama_bank'],
							$_POST['atas_nama'],
							$_POST['tgl_pencairan'],
							$_POST['nominal_bank'],
							$_POST['ket_bank'],
							$_POST['tgl_bayar'],
							'', //$_POST['dari'],
							'', //$_POST['diterima_oleh'],
							1, //$_POST['isCair'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor'),
							$cari_d_bayar_bank_for_edit
						);
					}
				}
				else
				{
					//HAPUS BAYAR BANK
						$cari_d_bayar_bank_for_delete = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_d_bayar = '".$_POST['id_d_bayar_bank']."' AND cara <> 'CASH' ";
						$this->M_gl_h_penjualan->hapus_d_penjualan_bayar($cari_d_bayar_bank_for_delete);
				}
			//BANK
			}
		/*PROSES PEMBAYARAN*/
		
		
		/*PROSES UBAH STATUS H PENJUALAN MENJADI PEMERIKSAAN*/
			//JIKA PENJUALAN LANGSUNG UBAH STATUS JADI SELESAI//
				
				$data_h_penjualan = $this->M_gl_h_penjualan->get_h_penjualan('id_h_penjualan',$_POST['id_h_penjualan']);
				if(!empty($data_h_penjualan))
				{
					$data_h_penjualan = $data_h_penjualan->row();
					
					//APAKAH UBAH, KARENA USDAH SELESAI
					if($data_h_penjualan->sts_penjualan == 'SELESAI')
					{
						$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"SELESAI",$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
						
						/* CATAT AKTIFITAS EDIT*/
						if($this->session->userdata('catat_log') == 'Y')
						{
							$this->M_gl_log->simpan_log
							(
								$this->session->userdata('ses_id_karyawan'),
								'UPDATE',
								'Melakukan Perubahan Transaksi Pasien '.$data_h_penjualan->nama_costumer.' Kunjungan Tanggal '.$data_h_penjualan->tgl_h_penjualan.' No Faktur : '.$data_h_penjualan->no_faktur,
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS EDIT*/
					}
					else
					{
					
						if($data_h_penjualan->id_dokter == '')
						{
							//MEMERIKSA APAKAH ADA JASA, AGAR TIDAK LANGSUNG SELESAI
							$cek_apakah_ada_jasa = $this->M_gl_h_penjualan->cek_apakah_ada_jasa($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan']);
							if(!empty($cek_apakah_ada_jasa))
							{
								$cek_apakah_ada_jasa = $cek_apakah_ada_jasa->row();
								if($cek_apakah_ada_jasa->JUM_JASA > 0)
								{
									$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"PEMBAYARAN",$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
								}
								else
								{
									if($this->session->userdata('ses_kode_kantor') == "SLM")
									{
										$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja_for_slim($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"PEMBAYARAN",$_POST['ket_penjualan2']);
									}
									else
									{
										$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"SELESAI",$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
									}
									
								}
							}
							else
							{
								if($this->session->userdata('ses_kode_kantor') == "SLM")
								{
									$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja_for_slim($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"PEMBAYARAN",$_POST['ket_penjualan2']);
								}
								else
								{
									$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"SELESAI",$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
								}
								
							}
							//MEMERIKSA APAKAH ADA JASA, AGAR TIDAK LANGSUNG SELESAI
							
							
							/*UPDATE WAKTU MULAI PERIKSA*/
								$this->M_gl_h_penjualan->ubah_h_penjualan_ubah_waktu_selesai_pemeriksaan($this->session->userdata('ses_kode_kantor'),$data_h_penjualan->id_h_penjualan);
							/*UPDATE WAKTU MULAI PERIKSA*/
						}
						else
						{
							if($_POST['from_halaman_pembayaran'] == 1)
							{
								/*CEK APAKAH ADA JASA/TINDAKAN SEHINGGA HARUS PILIH DOK & THERAPIS ATAU LANGSUNG SELESAI*/
								$cek_apakah_ada_jasa = $this->M_gl_h_penjualan->cek_apakah_ada_jasa($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan']);
								if(!empty($cek_apakah_ada_jasa))
								{
									$cek_apakah_ada_jasa = $cek_apakah_ada_jasa->row();
									if($cek_apakah_ada_jasa->JUM_JASA > 0)
									{
										$hasil_status = "PEMBAYARAN";
									}
									else
									{
										if($this->session->userdata('ses_kode_kantor') == "SLM")
										{
											$hasil_status = "PEMBAYARAN";
										}
										else
										{
											$hasil_status = "SELESAI";
										}
									}
								}
								else
								{
									$hasil_status = "PEMBAYARAN";
								}
								/*CEK APAKAH ADA JASA/TINDAKAN SEHINGGA HARUS PILIH DOK & THERAPIS ATAU LANGSUNG SELESAI*/
								
								//UPDATE STATUS H PENJUALAN
								if($this->session->userdata('ses_kode_kantor') == "SLM")
								{
									$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja_for_slim($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$hasil_status,$_POST['ket_penjualan2']);
								}
								else
								{
									$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$hasil_status,$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
								}
								//UPDATE STATUS H PENJUALAN
								
								/*UPDATE WAKTU MULAI PERIKSA*/
									$this->M_gl_h_penjualan->ubah_h_penjualan_ubah_waktu_selesai_pemeriksaan($this->session->userdata('ses_kode_kantor'),$data_h_penjualan->id_h_penjualan);
								/*UPDATE WAKTU MULAI PERIKSA*/
							}
							else
							{
								if( ($_POST['bayar_cash'] + $_POST['nominal_bank']) > 0)
								{
									/*CEK APAKAH ADA JASA/TINDAKAN SEHINGGA HARUS PILIH DOK & THERAPIS ATAU LANGSUNG SELESAI*/
									$cek_apakah_ada_jasa = $this->M_gl_h_penjualan->cek_apakah_ada_jasa($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan']);
									if(!empty($cek_apakah_ada_jasa))
									{
										$cek_apakah_ada_jasa = $cek_apakah_ada_jasa->row();
										if($cek_apakah_ada_jasa->JUM_JASA > 0)
										{
											$hasil_status = "PEMBAYARAN";
										}
										else
										{
											if($this->session->userdata('ses_kode_kantor') == "SLM")
											{
												$hasil_status = "PEMBAYARAN";
											}
											else
											{
												$hasil_status = "SELESAI";
											}
										}
									}
									else
									{
										$hasil_status = "PEMBAYARAN";
									}
									/*CEK APAKAH ADA JASA/TINDAKAN SEHINGGA HARUS PILIH DOK & THERAPIS ATAU LANGSUNG SELESAI*/
									
									//UPDATE STATUS H PENJUALAN
									//$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$hasil_status,$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
									
									
									if($this->session->userdata('ses_kode_kantor') == "SLM")
									{
										$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja_for_slim($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$hasil_status,$_POST['ket_penjualan2']);
									}
									else
									{
										$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$hasil_status,$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
									}
									
									//UPDATE STATUS H PENJUALAN
									
									/*UPDATE WAKTU MULAI PERIKSA*/
										$this->M_gl_h_penjualan->ubah_h_penjualan_ubah_waktu_selesai_pemeriksaan($this->session->userdata('ses_kode_kantor'),$data_h_penjualan->id_h_penjualan);
									/*UPDATE WAKTU MULAI PERIKSA*/
								}
								else
								{
									$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"PEMERIKSAAN",$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
								}
							}
						}
						
						/* CATAT AKTIFITAS EDIT*/
						if($this->session->userdata('catat_log') == 'Y')
						{
							$this->M_gl_log->simpan_log
							(
								$this->session->userdata('ses_id_karyawan'),
								'INSERT',
								'Melakukan Penyelesaian Transaksi Pelanggan '.$data_h_penjualan->nama_costumer.' Tanggal '.$data_h_penjualan->tgl_h_penjualan.' No : '.$data_h_penjualan->no_faktur,
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS EDIT*/
					}
				}
			//JIKA PENJUALAN LANGSUNG UBAH STATUS JADI SELESAI//
		/*PROSES UBAH STATUS H PENJUALAN MENJADI PEMERIKSAAN*/
		
		echo"TERSIMPAN";
	}
	
	function selesai_transaksi_ori()
	{
		//HITUNG HPP DARI tb_satuan_konversi dulu
			$this->M_gl_h_penjualan->update_hpp_dari_tb_satuan_konversi($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan']);
		//HITUNG HPP DARI tb_satuan_konversi dulu
		
		/*
		$this->hitung_fee
		(
			$_POST['id_h_penjualan'],
			$_POST['id_karyawan'],
			$_POST['isDokter'], //DOKTER, THERAPIST, KARYAWAN UMUM
			$_POST['poli'],
			$_POST['var_lama_kerja'],
			$_POST['var_isPasienBaru'],
			$_POST['var_isMembuatResep'],
			$_POST['var_min_jasa'],
			$_POST['var_min_produk'],
			$_POST['var_nominal_tr_per_pasien']
		);
		*/
		
		//SIMPAN CATATAN SYARAT FEE
			/*
			$cari_catatan_fee = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' ";
			
			$data_catatan_fee = $this->M_gl_h_penjualan_catat_untuk_fee->get_h_penjualan_catat_untuk_fee($cari_catatan_fee);
			
			if(empty($data_catatan_fee))
			{
				$this->M_gl_h_penjualan_catat_untuk_fee->simpan
				(
					$_POST['id_h_penjualan'], //$id_h_penjualan,
					$_POST['poli'], //$jenis_poli,
					$_POST['var_isPasienBaru'], //$isPasienBaru,
					$_POST['var_min_jasa'], //$jasa,
					$_POST['var_min_produk'], //$produk,
					$_POST['var_nominal_tr_per_pasien'], //$nominal_transaksi,
					$this->session->userdata('ses_kode_kantor') //$kode_kantor
				);
			}
			else
			{
				$data_catatan_fee = $data_catatan_fee->row();
				
				$this->M_gl_h_penjualan_catat_untuk_fee->edit
				(
					$data_catatan_fee->id_catatan, //$id_catatan,
					$_POST['id_h_penjualan'], //$id_h_penjualan,
					$_POST['poli'], //$jenis_poli,
					$_POST['var_isPasienBaru'], //$isPasienBaru,
					$_POST['var_min_jasa'], //$jasa,
					$_POST['var_min_produk'], //$produk,
					$_POST['var_nominal_tr_per_pasien'], //$nominal_transaksi,
					$this->session->userdata('ses_kode_kantor') //$kode_kantor
				
				);
			}
			*/
		//SIMPAN CATATAN SYARAT FEE
		
		
		/*PROSES PEMBAYARAN*/
			//if($_POST['is_allow_bayar'] == 1)
			if(($_POST['is_allow_bayar'] == 1) || ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi'))
			{
			//CASH
				//if(id_d_bayar_cash)
				if((empty($_POST['id_d_bayar_cash'])) && ($_POST['id_d_bayar_cash'] == "")  )
				{
					$this->M_gl_h_penjualan->simpan_d_penjualan_bayar
					(
						$_POST['id_h_penjualan'],
						'', //$_POST['id_bank'],
						$_POST['id_costumer'],
						'', //$_POST['no_pembayaran'],
						0, //$_POST['isTabungan'],
						'CASH', //$_POST['cara'],
						'', //$_POST['jenis'],
						'', //$_POST['norek'],
						'', //$_POST['nama_bank'],
						'', //$_POST['atas_nama'],
						date("Y-m-d"), //$_POST['tgl_pencairan'],
						$_POST['bayar_cash'],
						'', //$_POST['ket'],
						date("Y-m-d"), //$_POST['tgl_bayar'],
						'', //$_POST['dari'],
						'', //$_POST['diterima_oleh'],
						'1', //$_POST['isCair'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor')
					);
				}
				else
				{
					//AND cara = 'CASH'  -> KARENA BISA JADI METODE NTA TF,EDC dll, tp tidak ada bank nya
					$cari_bayar_cash = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_d_bayar = '".$_POST['id_d_bayar_cash']."' ";
					$this->M_gl_h_penjualan->edit_d_penjualan_bayar
					(
						$_POST['id_d_bayar_cash'],
						$_POST['id_h_penjualan'],
						'', //$_POST['id_bank'],
						$_POST['id_costumer'],
						'', //$_POST['no_pembayaran'],
						0, //$_POST['isTabungan'],
						'CASH', //$_POST['cara'],
						'', //$_POST['jenis'],
						'', //$_POST['norek'],
						'', //$_POST['nama_bank'],
						'', //$_POST['atas_nama'],
						date("Y-m-d"), //$_POST['tgl_pencairan'],
						$_POST['bayar_cash'],
						'', //$_POST['ket'],
						date("Y-m-d"), //$_POST['tgl_bayar'],
						'', //$_POST['dari'],
						'', //$_POST['diterima_oleh'],
						'1', //$_POST['isCair'],
						$this->session->userdata('ses_id_karyawan'),
						$this->session->userdata('ses_kode_kantor'),
						$cari_bayar_cash
					);
				}
			//CASH
			
			//BANK
				if($_POST['nominal_bank'] > 0)
				{
					if((empty($_POST['id_d_bayar_bank'])) && ($_POST['id_d_bayar_bank'] == "")  )
					{
						
						$this->M_gl_h_penjualan->simpan_d_penjualan_bayar
						(
							$_POST['id_h_penjualan'],
							$_POST['id_bank'],
							$_POST['id_costumer'],
							'', //$_POST['no_pembayaran'],
							0, //$_POST['isTabungan'],
							$_POST['cara'],
							'', //$_POST['jenis'],
							$_POST['norek'],
							$_POST['nama_bank'],
							$_POST['atas_nama'],
							$_POST['tgl_pencairan'],
							$_POST['nominal_bank'],
							$_POST['ket_bank'],
							$_POST['tgl_bayar'],
							'', //$_POST['dari'],
							'', //$_POST['diterima_oleh'],
							1, //$_POST['isCair'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor')
						);
					}
					else
					{
						$cari_d_bayar_bank_for_edit = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_d_bayar = '".$_POST['id_d_bayar_bank']."' AND cara <> 'CASH' ";
						$this->M_gl_h_penjualan->edit_d_penjualan_bayar
						(
							$_POST['id_d_bayar_bank'],
							$_POST['id_h_penjualan'],
							$_POST['id_bank'],
							$_POST['id_costumer'],
							'', //$_POST['no_pembayaran'],
							0, //$_POST['isTabungan'],
							$_POST['cara'],
							'', //$_POST['jenis'],
							$_POST['norek'],
							$_POST['nama_bank'],
							$_POST['atas_nama'],
							$_POST['tgl_pencairan'],
							$_POST['nominal_bank'],
							$_POST['ket_bank'],
							$_POST['tgl_bayar'],
							'', //$_POST['dari'],
							'', //$_POST['diterima_oleh'],
							1, //$_POST['isCair'],
							$this->session->userdata('ses_id_karyawan'),
							$this->session->userdata('ses_kode_kantor'),
							$cari_d_bayar_bank_for_edit
						);
					}
				}
				else
				{
					//HAPUS BAYAR BANK
						$cari_d_bayar_bank_for_delete = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_d_bayar = '".$_POST['id_d_bayar_bank']."' AND cara <> 'CASH' ";
						$this->M_gl_h_penjualan->hapus_d_penjualan_bayar($cari_d_bayar_bank_for_delete);
				}
			//BANK
			}
		/*PROSES PEMBAYARAN*/
		
		/*PROSES UBAH STATUS H PENJUALAN MENJADI PEMERIKSAAN*/
			//JIKA PENJUALAN LANGSUNG UBAH STATUS JADI SELESAI//
				
				$data_h_penjualan = $this->M_gl_h_penjualan->get_h_penjualan('id_h_penjualan',$_POST['id_h_penjualan']);
				if(!empty($data_h_penjualan))
				{
					$data_h_penjualan = $data_h_penjualan->row();
					
					//APAKAH UBAH, KARENA USDAH SELESAI
					if($data_h_penjualan->sts_penjualan == 'SELESAI')
					{
						$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"SELESAI",$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
						
						/* CATAT AKTIFITAS EDIT*/
						if($this->session->userdata('catat_log') == 'Y')
						{
							$this->M_gl_log->simpan_log
							(
								$this->session->userdata('ses_id_karyawan'),
								'UPDATE',
								'Melakukan Perubahan Transaksi Pasien '.$data_h_penjualan->nama_costumer.' Kunjungan Tanggal '.$data_h_penjualan->tgl_h_penjualan.' No Faktur : '.$data_h_penjualan->no_faktur,
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS EDIT*/
					}
					else
					{
					
						if($data_h_penjualan->id_dokter == '')
						{
							//MEMERIKSA APAKAH ADA JASA, AGAR TIDAK LANGSUNG SELESAI
							$cek_apakah_ada_jasa = $this->M_gl_h_penjualan->cek_apakah_ada_jasa($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan']);
							if(!empty($cek_apakah_ada_jasa))
							{
								$cek_apakah_ada_jasa = $cek_apakah_ada_jasa->row();
								if($cek_apakah_ada_jasa->JUM_JASA > 0)
								{
									$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"PEMBAYARAN",$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
								}
								else
								{
									$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"SELESAI",$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
								}
							}
							else
							{
								$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"SELESAI",$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
							}
							//MEMERIKSA APAKAH ADA JASA, AGAR TIDAK LANGSUNG SELESAI
							
							
							/*UPDATE WAKTU MULAI PERIKSA*/
								$this->M_gl_h_penjualan->ubah_h_penjualan_ubah_waktu_selesai_pemeriksaan($this->session->userdata('ses_kode_kantor'),$data_h_penjualan->id_h_penjualan);
							/*UPDATE WAKTU MULAI PERIKSA*/
						}
						else
						{
							if($_POST['from_halaman_pembayaran'] == 1)
							{
								/*CEK APAKAH ADA JASA/TINDAKAN SEHINGGA HARUS PILIH DOK & THERAPIS ATAU LANGSUNG SELESAI*/
								$cek_apakah_ada_jasa = $this->M_gl_h_penjualan->cek_apakah_ada_jasa($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan']);
								if(!empty($cek_apakah_ada_jasa))
								{
									$cek_apakah_ada_jasa = $cek_apakah_ada_jasa->row();
									if($cek_apakah_ada_jasa->JUM_JASA > 0)
									{
										$hasil_status = "PEMBAYARAN";
									}
									else
									{
										$hasil_status = "SELESAI";
									}
								}
								else
								{
									$hasil_status = "PEMBAYARAN";
								}
								/*CEK APAKAH ADA JASA/TINDAKAN SEHINGGA HARUS PILIH DOK & THERAPIS ATAU LANGSUNG SELESAI*/
								
								//UPDATE STATUS H PENJUALAN
								$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$hasil_status,$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
								//UPDATE STATUS H PENJUALAN
								
								/*UPDATE WAKTU MULAI PERIKSA*/
									$this->M_gl_h_penjualan->ubah_h_penjualan_ubah_waktu_selesai_pemeriksaan($this->session->userdata('ses_kode_kantor'),$data_h_penjualan->id_h_penjualan);
								/*UPDATE WAKTU MULAI PERIKSA*/
							}
							else
							{
								if( ($_POST['bayar_cash'] + $_POST['nominal_bank']) > 0)
								{
									/*CEK APAKAH ADA JASA/TINDAKAN SEHINGGA HARUS PILIH DOK & THERAPIS ATAU LANGSUNG SELESAI*/
									$cek_apakah_ada_jasa = $this->M_gl_h_penjualan->cek_apakah_ada_jasa($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan']);
									if(!empty($cek_apakah_ada_jasa))
									{
										$cek_apakah_ada_jasa = $cek_apakah_ada_jasa->row();
										if($cek_apakah_ada_jasa->JUM_JASA > 0)
										{
											$hasil_status = "PEMBAYARAN";
										}
										else
										{
											$hasil_status = "SELESAI";
										}
									}
									else
									{
										$hasil_status = "PEMBAYARAN";
									}
									/*CEK APAKAH ADA JASA/TINDAKAN SEHINGGA HARUS PILIH DOK & THERAPIS ATAU LANGSUNG SELESAI*/
									
									//UPDATE STATUS H PENJUALAN
									$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$hasil_status,$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
									//UPDATE STATUS H PENJUALAN
									
									/*UPDATE WAKTU MULAI PERIKSA*/
										$this->M_gl_h_penjualan->ubah_h_penjualan_ubah_waktu_selesai_pemeriksaan($this->session->userdata('ses_kode_kantor'),$data_h_penjualan->id_h_penjualan);
									/*UPDATE WAKTU MULAI PERIKSA*/
								}
								else
								{
									$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"PEMERIKSAAN",$_POST['var_pajak'],$_POST['pengurang_diskon'],$_POST['ket_penjualan2']);
								}
							}
						}
						
						/* CATAT AKTIFITAS EDIT*/
						if($this->session->userdata('catat_log') == 'Y')
						{
							$this->M_gl_log->simpan_log
							(
								$this->session->userdata('ses_id_karyawan'),
								'INSERT',
								'Melakukan Penyelesaian Transaksi Pasien '.$data_h_penjualan->nama_costumer.' Tanggal '.$data_h_penjualan->tgl_h_penjualan.' No : '.$data_h_penjualan->no_faktur,
								$this->M_gl_pengaturan->getUserIpAddr(),
								gethostname(),
								$this->session->userdata('ses_kode_kantor')
							);
						}
						/* CATAT AKTIFITAS EDIT*/
					}
				}
			//JIKA PENJUALAN LANGSUNG UBAH STATUS JADI SELESAI//
		/*PROSES UBAH STATUS H PENJUALAN MENJADI PEMERIKSAAN*/
		echo"TERSIMPAN";
	}
	
	
	function hitung_fee($id_h_penjualan,$id_karyawan,$isDokter,$poli,$var_lama_kerja,$var_isPasienBaru,$var_isMembuatResep,$var_min_jasa,$var_min_produk,$var_nominal_tr_per_pasien)
	//function selesai_transaksi()
	{
		$id_h_penjualan = $_POST['id_h_penjualan'];
		$id_karyawan = $_POST['id_karyawan'];
		$isDokter = $_POST['isDokter'];
		$poli = $_POST['poli'];
		$var_lama_kerja = $_POST['var_lama_kerja'];
		$var_isPasienBaru = $_POST['var_isPasienBaru'];
		$var_isMembuatResep = $_POST['var_isMembuatResep'];
		$var_min_jasa = $_POST['var_min_jasa'];
		$var_min_produk = $_POST['var_min_produk'];
		$var_nominal_tr_per_pasien = $_POST['var_nominal_tr_per_pasien'];
		
		//FORMAT APAKAH PASIEN BARU
		if($var_isPasienBaru <= 1)
		{
			$var_isPasienBaru = "YA";
		}
		else
		{
			$var_isPasienBaru = "TIDAK";
		}
		//FORMAT APAKAH PASIEN BARU
		
		
		$list_fee = $this->M_gl_h_penjualan->list_fee($this->session->userdata('ses_kode_kantor'),$isDokter,$poli," AND isMembuatResep = '".$var_isMembuatResep."'");
		if(!empty($list_fee))
		{
			/*JIKA ADA FEE, MAKA HAPUS FEE SEBELUMNYA*/
				//($id_h_penjualan,$kode_kantor,$group_by,$id_karyawan)
				$this->M_gl_h_penjualan->hapus_d_penjualan_fee($id_h_penjualan,$this->session->userdata('ses_kode_kantor'),"DOKTER KONSULTASI",$id_karyawan);
			/*JIKA ADA FEE, MAKA HAPUS FEE SEBELUMNYA*/
			
			echo"ADA FEE LOOPING</br>";
			$list_result = $list_fee->result();
			$no = 1;
			foreach($list_result as $row)
			{
				echo"MASUK LOOPING</br>";
				
				$cari = "";
				
				if($row->masa_kerja_hari <> 0)
				{
					$cari = $cari ." AND ".$var_lama_kerja." ".$row->optr_masa_kerja." ".$row->masa_kerja_hari."";
				}
				
				echo"MASUK LOOPING PASIEN BARU : ".$var_isPasienBaru." | BUAT RESEP : ".$var_isMembuatResep." | NOMINAL TRANSAKSI : ".$var_nominal_tr_per_pasien."</br>";
				
				if($var_isPasienBaru == "YA")
				{
					$cari = $cari ." AND isPasienBaru = 'YA'";
				}
				else
				{
					$cari = $cari ." AND isPasienBaru = 'TIDAK'";
				}
				
				if($var_isMembuatResep == "YA")
				{
					$cari = $cari ." AND isMembuatResep = 'YA'";
				}
				else
				{
					$cari = $cari ." AND isMembuatResep = 'TIDAK'";
				}
				
				if($row->min_jasa <> 0)
				{
					//$cari = $cari ." AND ".$var_min_jasa." ".$row->optr_min_jasa." ".$row->min_jasa."";
					$cari = $cari ." AND ".$var_min_jasa." >= ".$row->min_jasa."";
				}
				
				if($row->min_produk <> 0)
				{
					//$cari = $cari ." AND ".$var_min_produk." ".$row->optr_min_produk." ".$row->min_produk."";
					$cari = $cari ." AND ".$var_min_produk." >= ".$row->min_produk."";
				}
				
				
				$cari = $cari ." AND ".$var_nominal_tr_per_pasien." >= nominal_tr_per_pasien";
				
				echo '</br>'.$cari;
				/*LAKUKAN PENCARIAN FEE YANG MASUK KRITERIA*/
					$row_fee = $this->M_gl_h_penjualan->list_fee($this->session->userdata('ses_kode_kantor'),$isDokter,$poli,$cari);
					if(!empty($row_fee))
					{
						$row_fee = $row_fee->row();
						
						if($row_fee->optr_fee == 'C')
						{
							$nominal_fee = $row_fee->besar_fee;
						}
						else
						{
							$nominal_fee = ($var_nominal_tr_per_pasien/100) * $row_fee->besar_fee;
						}
						
						echo"SIMPAN </br>";
						$this->M_gl_h_penjualan->simpan_d_fee_penjualan
						(
							$id_h_penjualan,
							$row_fee->id_h_fee,
							$id_karyawan,
							"DOKTER KONSULTASI",
							$row_fee->nama_h_fee,
							$nominal_fee,
							$this->session->userdata('ses_kode_kantor')

						);
					}
				/*LAKUKAN PENCARIAN FEE YANG MASUK KRITERIA*/
			}
			
		}
		else
		{
			echo"TIDAK ADA FEE LOOPING</br>";
		}
	}

	function cek_diskon_akumulasi()
	{
		//cek_diskon_akumulasi($kode_kantor, $id_kat_costumer, $besar_pembelian_satuan)
		$cek_diskon_akumulasi = $this->M_gl_h_diskon->cek_diskon_akumulasi($this->session->userdata('ses_kode_kantor'), $_POST['id_kat_costumer'], $_POST['besar_pembelian_satuan'], $_POST['besar_pembelian_nominal']);
		//$besar_pembelian_satuan, $besar_pembelian_nomina
		
		if(!empty($cek_diskon_akumulasi))
		{
			$cek_diskon_akumulasi = $cek_diskon_akumulasi->row();
			
			//PERIKSA JENIS DISKON
			if($cek_diskon_akumulasi->optr_diskon == 'Produk')
			{
				//CEK APAKAH SUDAH ADA DI tb_d_penjualan
					$cari_diskon_di_tb_d_penjualan = "WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND  id_h_penjualan = '".$_POST['id_h_penjualan']."' AND id_h_diskon = '".$cek_diskon_akumulasi->id_h_diskon."'";
					$cek_diskon_di_tb_d_penjualan = $this->M_gl_h_penjualan->get_d_penjualan_cari($cari_diskon_di_tb_d_penjualan);
					
					if(empty($cek_diskon_di_tb_d_penjualan)) //PASTIKAN MASIH KOSONG
					{
						//SIMPAN d_diskon produk
						$this->M_gl_h_diskon->simpan_d_penjualan_dari_d_diskon_by_produk($_POST['id_h_penjualan'], $cek_diskon_akumulasi->id_h_diskon,$this->session->userdata('ses_kode_kantor'),$this->session->userdata('ses_id_karyawan'));
						
						echo 'DISKON PRODUK';
					}
				//CEK APAKAH SUDAH ADA DI tb_d_penjualan
				
				
			}
			else
			{
				$optr_diskon = $cek_diskon_akumulasi->optr_diskon;
				$besar_diskon_ori = $cek_diskon_akumulasi->besar_diskon;
				$diskon = $cek_diskon_akumulasi->BESAR_DISKON_NOMINAL;
				$harga = $cek_diskon_akumulasi->BESAR_DISKON_FIX;
				//$harga_konversi = $cek_diskon_produk->BESAR_DISKON_FIX / $_POST['konversi'];
				
				echo $diskon;
			}
		}
		else
		{
			echo 0;
		}
	}

	function ubah_h_penjualan_media_transaksi()
	{
		//$this->M_gl_h_penjualan->ubah_h_penjualan_media_transaksi($_POST['id_h_penjualan'],$this->session->userdata('ses_kode_kantor'),$_POST['media_transaksi'],$_POST['diagnosa'],$_POST['id_ass_dok2']);
		$this->M_gl_h_penjualan->ubah_h_penjualan_media_transaksi($_POST['id_h_penjualan'],$this->session->userdata('ses_kode_kantor'),$_POST['media_transaksi'],$_POST['diagnosa'],$_POST['id_sales'],$_POST['isKirim'],$_POST['tgl_tempo'],$_POST['tgl_h_penjualan']);
		
		echo"BERHASIL";
	}
	
	function ubah_d_penjualan_produk_karena_kategori()
	{
		/*
			$satuan_jual;
			$harga_ori;
		*/
		//ubah_d_penjualan_tambahkan_produk_saja($kode_kantor,$id_d_penjualan,$id_produk)
		$this->M_gl_h_penjualan->ubah_d_penjualan_tambahkan_produk_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_d_penjualan'],$_POST['id_produk'],$_POST['satuan_jual'],$_POST['harga_ori']);
		
		echo"BERHASIL";
	}
	
	function ubah_d_penjualan_id_h_diskon()
	{
		//YANG DI UBAH FILED id_d_diskon2
		
		$this->M_gl_h_penjualan->ubah_d_penjualan_id_h_diskon($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$_POST['id_produk'],$_POST['id_h_diskon'],$_POST['satuan_jual']);
		
		echo"BERHASIL";
	}
	
	
	/*
	function simpan_d_penjualan_bayar()
	{
		
	}
	*/
}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_pedaftaran_proses_dokter.php */