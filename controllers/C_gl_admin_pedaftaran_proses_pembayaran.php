<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class C_gl_admin_pedaftaran_proses_pembayaran extends CI_Controller {

	public function __construct()
	{
		parent::__construct();
		// Your own constructor code
		//$this->load->model(array('M_berita','M_kat_berita','M_images'));
		$this->load->model(array('M_gl_h_penjualan','M_gl_costumer','M_gl_produk','M_gl_h_diskon','M_gl_d_diskon','M_gl_images','M_gl_bank','M_gl_h_penjualan_catat_untuk_fee','M_gl_gedung'));
		
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
							AND A.sts_penjualan IN ('PEMERIKSAAN','PESAN')
							AND DATE(A.tgl_h_penjualan) = DATE(NOW())
							AND (A.no_faktur LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.nama_lengkap,A.nama_costumer) LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.sts_penjualan IN ('PEMERIKSAAN','PESAN')
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
				
				$config['first_url'] = site_url('gl-admin-pembayaran?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-pembayaran/');
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
					$msgbox_title = " Pembayaran Pasien";
					$data = array('page_content'=>'gl_admin_h_penjualan_proses_pembayaran_pst','halaman'=>$halaman,'msgbox_title' => $msgbox_title,'sum_pesan'=>$sum_pesan);
					$this->load->view('admin/container',$data);
				}
				else
				{
					
					
					if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
					{
						$msgbox_title = " List Transaksi";
					}
					else
					{
						$msgbox_title = " Pembayaran Pasien";
					}
					
					$data = array('page_content'=>'gl_admin_h_penjualan_proses_pembayaran','halaman'=>$halaman,'msgbox_title' => $msgbox_title,'sum_pesan'=>$sum_pesan);
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
							AND A.sts_penjualan IN ('PEMERIKSAAN','PESAN')
							AND DATE(A.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."'
							AND (A.no_faktur LIKE '%".str_replace("'","",$_POST['cari'])."%' OR COALESCE(B.nama_lengkap,A.nama_costumer) LIKE '%".str_replace("'","",$_POST['cari'])."%')";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.sts_penjualan IN ('PEMERIKSAAN','PESAN')
					AND DATE(A.tgl_h_penjualan) BETWEEN '".$dari."' AND '".$sampai."'
					";
				}
				
				$order_by = "ORDER BY A.tgl_ins ASC";
				
				//UNTUK AKUMULASI INFO
					$jum_row = $this->M_gl_h_penjualan->count_list_pendaftaran_periksa_dokter($cari)->JUMLAH;
				//UNTUK AKUMULASI INFO
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-pembayaran?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-pembayaran/');
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
									<br/> <b>CUTOMER : </b>'.$row->nama_costumer.'
									<br/> <b>INPUT OLEH : </b>'.$row->nama_karyawan.'
									</td>';
								}
								
								elseif($this->session->userdata('ses_kode_kantor') == "SLM")
								{
									echo'<td>
									<b>NO : </b><font style="font-weight:bold;color:red;">'.$row->no_antrian.'</font> ('.$row->tgl_ins.' / <b style="color:red;">'.$row->MENIT_TUNGGU.' Menit Menunggu </b>)
									<br/> <b>FAKTUR : </b>'.$row->no_faktur.'
									<br/> <b>AGENT : </b>'.$row->nama_costumer.'
									<br/> <b>ALAMAT KIRIM : </b>'.$row->ALAMAT_KIRIM.'
									<br/> <b>BIAYA KIRIM : </b>
									<br/>
									<input type="number" class="form-control" name="text_biaya_kirim-'.$row->id_h_penjualan.'" id="text_biaya_kirim-'.$row->id_h_penjualan.'" placeholder="*Biaya Kirim" onkeypress="return isNumberKey(event)" maxlength="10" value="'.$row->biaya.'"/>
									
									<a class="confirm-btn btn btn-warning btn-sm btn-flat" href="#" title = "Proses Biaya Kirim '.$row->no_faktur.' atas nama '.$row->nama_costumer.'" alt = "Proses Biaya Kirim '.$row->no_faktur.' atas nama '.$row->nama_costumer.'" name="btn_biaya_kirim-'.$row->id_h_penjualan.'" id="btn_biaya_kirim-'.$row->id_h_penjualan.'"  onclick="update_biaya_kirim(this)">SIMPAN BIAYA KIRIM</a>
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
								elseif($this->session->userdata('ses_kode_kantor') == "SLM")
								{
									echo'<td>
										<b>NO : </b><font style="font-weight:bold;color:red;">'.$row->no_antrian.'</font> ('.$row->tgl_ins.' / <b style="color:red;">'.$row->MENIT_TUNGGU.' Menit Menunggu </b>)
										<br/> <b>FAKTUR : </b>'.$row->no_faktur.'
										<br/> <b>AGENT : </b>'.$row->nama_costumer.'
										<br/> <b>PAKET : </b><font style="color:red;">'.$row->nama_diskon.'</font>
										<br/> <b>BIAYA KIRIM : </b>
										<br/>
										<input type="number" class="form-control" name="text_biaya_kirim-'.$row->id_h_penjualan.'" id="text_biaya_kirim-'.$row->id_h_penjualan.'" placeholder="*Biaya Kirim" onkeypress="return isNumberKey(event)" maxlength="10" value="'.$row->biaya.'"/>
										
										<a class="confirm-btn btn btn-warning btn-sm btn-flat" href="#" title = "Proses Biaya Kirim '.$row->no_faktur.' atas nama '.$row->nama_costumer.'" alt = "Proses Biaya Kirim '.$row->no_faktur.' atas nama '.$row->nama_costumer.'" name="btn_biaya_kirim-'.$row->id_h_penjualan.'" id="btn_biaya_kirim-'.$row->id_h_penjualan.'" onclick="update_biaya_kirim(this)">SIMPAN BIAYA KIRIM</a>
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
								
							if( ($row->id_dokter == $this->session->userdata('ses_id_karyawan')) or ($this->session->userdata('ses_akses_lvl2_54') > 0) or ($this->session->userdata('ses_nama_jabatan') == 'Admin Aplikasi') )
							{
								if($this->session->userdata('ses_kode_kantor') == "SLM")
								{
									
									if($row->ba_file == "")
									{
										$src = base_url().'assets/global/no-image.jpg';
									}
									else
									{
										//$src = base_url().'assets/global/karyawan/'.$row->avatar;
										//$src = base_url().''.$row->ba_url.''.$row->ba_file;
										$src = base_url().'api/upload/bukti/'.$row->ba_file;
									}
									
									
									echo'<td>
									<a  href="'.$src.'" target="_blank">
									<img id="IMG_'.$no.'"  width="100%" height="200px" style="border:1px solid #C8C8C8; padding:5px; float:left; margin-right:20px;" src="'.$src.'" /> </td>
									</a>
									';
									
									/*
									if($row->ba_file == "")
									{
										echo'<td>Pesanan Bisa Diproses Setelah ada bukti pembayaran</td>';
									}
									else
									{
										*/
										echo'<td>
		<a class="confirm-btn btn btn-danger btn-block btn-flat" href="'.base_url().'gl-admin-pemeriksaan-dokter-proses/'.md5($row->id_h_penjualan).'?from=bayar" title = "Proses Pendaftaran Data '.$row->no_antrian.' atas nama '.$row->nama_costumer.'" alt = "Proses Pendaftaran Data '.$row->no_antrian.' atas nama '.$row->nama_costumer.'">PROSES PESANAN</a>
										</td>';
									//}

								}
								else
								{
									echo'<td>
	<a class="confirm-btn btn btn-danger btn-block btn-flat" href="'.base_url().'gl-admin-pemeriksaan-dokter-proses/'.md5($row->id_h_penjualan).'?from=bayar" title = "Proses Pendaftaran Data '.$row->no_antrian.' atas nama '.$row->nama_costumer.'" alt = "Proses Pendaftaran Data '.$row->no_antrian.' atas nama '.$row->nama_costumer.'">PEMBAYARAN</a>
									</td>';
								}
								
							}
							else
							{
								echo'<td></td>';
							}
							
							echo'<input type="hidden" id="id_h_penjualan_'.$no.'" name="id_h_penjualan_'.$no.'" value="'.$row->id_h_penjualan.'" />';
							
						echo'</tr>';
						$no++;
					}
					//echo '</tbody>';
				}
				else
				{
					if($this->session->userdata('ses_isModePst') == "YA")
					{
						echo'<tr>';
						echo'<td colspan="4" style="text-align:center;color:red;font-weight:bold;">Tidak ada transaksi !</td>';
						echo'</tr>';
					}
					elseif($this->session->userdata('ses_kode_kantor') == "SLM")
					{
						echo'<tr>';
						echo'<td colspan="4" style="text-align:center;color:red;font-weight:bold;">Tidak ada transaksi !</td>';
						echo'</tr>';
					}
					else
					{
						echo'<tr>';
						echo'<td colspan="4" style="text-align:center;color:red;font-weight:bold;">Tidak Ada Pasien Yang Menunggu !</td>';
						echo'</tr>';
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

	function perawatan_lanjut()
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
							AND A.sts_penjualan = 'PEMBAYARAN'
							AND DATE(A.tgl_h_penjualan) = DATE(NOW())
							AND (A.no_faktur LIKE '%".str_replace("'","",$_GET['cari'])."%' OR COALESCE(B.nama_lengkap,A.nama_costumer) LIKE '%".str_replace("'","",$_GET['cari'])."%')";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.sts_penjualan = 'PEMBAYARAN'
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
				
				$config['first_url'] = site_url('gl-admin-perawatan-lanjut?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-perawatan-lanjut/');
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
				
				
				if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
				{
					$msgbox_title = " List Transaksi";
				}
				else
				{
					$msgbox_title = " Perawatan/Tindakan Pasien";
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
				
				//$data = array('page_content'=>'gl_admin_h_penjualan_proses_dokter','halaman'=>$halaman,'list_h_pendaftaran_proses_dokter'=>$list_h_pendaftaran_proses_dokter,'msgbox_title' => $msgbox_title,'sum_pesan'=>$sum_pesan);
				
				
				$data = array('page_content'=>'gl_admin_perawatan_lanjut','halaman'=>$halaman,'msgbox_title' => $msgbox_title,'sum_pesan'=>$sum_pesan);
				$this->load->view('admin/container',$data);
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}
	
	function get_ajax_update_perawatan_lanjut()
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
							AND A.sts_penjualan IN ('PEMBAYARAN','SELESAI')
							AND DATE(A.tgl_h_penjualan) = DATE(NOW())
							AND (
									A.no_faktur LIKE '%".str_replace("'","",$_POST['cari'])."%' 
									OR COALESCE(B.nama_lengkap,A.nama_costumer) LIKE '%".str_replace("'","",$_POST['cari'])."%'
								)";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.sts_penjualan IN ('PEMBAYARAN','SELESAI')
					AND DATE(A.tgl_h_penjualan) = DATE(NOW())
					";
				}
				
				$order_by = "ORDER BY A.tgl_updt DESC";
				
				//UNTUK AKUMULASI INFO
					$jum_row = $this->M_gl_h_penjualan->count_pendaftaran_perawatan_lanjutan($cari)->JUMLAH;
				//UNTUK AKUMULASI INFO
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-pembayaran?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-pembayaran/');
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
				
				$list_h_pendaftaran_proses_dokter = $this->M_gl_h_penjualan->list_pendaftaran_perawatan_lanjutan($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				$cari_gedung = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."'";
				$list_gedung = $this->M_gl_gedung->list_gedung_limit($cari_gedung,100,0);
				
				
				
				//ISI TABLE
				if(!empty($list_h_pendaftaran_proses_dokter))
				{
					$list_result = $list_h_pendaftaran_proses_dokter->result();
					$no =$this->uri->segment(2,0)+1;
					
					//echo '<tbody>';
					foreach($list_result as $row)
					{
						//GEDUNG/RUANGAN YANG SUDAH DIPILIH
						$cari_geung_terpilih = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_gedung = '".$row->id_gedung."'";
						$gedung_terpilih = $this->M_gl_gedung->list_gedung_limit($cari_geung_terpilih,100,0);
						//GEDUNG/RUANGAN YANG SUDAH DIPILIH
						
						echo'<tr>';
							
							//echo'<td>'.$no.'</td>';
							
							echo'<td>'.$row->no_antrian;
							echo'<input type="hidden" id="id_h_penjualan_'.$no.'" name="id_h_penjualan_'.$no.'" value="'.$row->id_h_penjualan.'" />';
							
							echo'<input type="hidden" id="type_h_penjualan-'.$row->id_h_penjualan.'" name="type_h_penjualan-'.$row->id_h_penjualan.'" value="'.$row->type_h_penjualan.'" />';
							
							//echo'<input type="text" id="lama_kerja_dokter-'.$row->id_h_penjualan.'" name="lama_kerja_dokter-'.$row->id_h_penjualan.'" value="'.$row->lama_kerja_dokter.'" />';
							
							echo'<input type="hidden" id="lama_kerja_dokter2-'.$row->id_h_penjualan.'" name="lama_kerja_dokter2-'.$row->id_h_penjualan.'" value="'.$row->lama_kerja_dokter2.'" />';
							echo'<input type="hidden" id="lama_kerja_ass-'.$row->id_h_penjualan.'" name="lama_kerja_ass-'.$row->id_h_penjualan.'" value="'.$row->lama_kerja_ass.'" />';
							echo'<input type="hidden" id="lama_kerja_ass2-'.$row->id_h_penjualan.'" name="lama_kerja_ass2-'.$row->id_h_penjualan.'" value="'.$row->lama_kerja_ass2.'" />';
							echo'</td>';
							
							
							/*
							if($row->avatar == "")
							{
								$src = base_url().'assets/global/costumer/loading.gif';
							}
							else
							{
								if(!(file_exists("assets/global/costumer/".$row->avatar)))
								{
									$src = base_url().'assets/global/costumer/loading.gif';
								}
								else
								{
									$src = base_url().''.$row->avatar_url.''.$row->avatar;
								}
							}
							*/
							
							//$src = base_url().'assets/global/images/qrcode/'.$row->no_faktur.'.png';
							/*
							echo '<td>
								<img id="IMG_barcode_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
								<br/>
								<center>
									<font style="font-weight:bold;color:red;font-size:18px;">'.$row->no_antrian.'
									</font>
								</center>
							</td>';
							*/
							if($row->sts_penjualan =="SELESAI")
							{
								$status_selesai = "disabled";
							}
							else
							{
								$status_selesai = "";
							}
							
							if($row->id_h_diskon == "")
							{
								echo'<td>
									<b>NO : </b><font style="font-weight:bold;color:red;">'.$row->no_antrian.'</font> ('.$row->tgl_ins.' / <b style="color:red;">'.$row->MENIT_TUNGGU.' Menit Menunggu </b>)
									
									';
									
									if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
									{
										echo '
											<br/> <b>LAMA TRANSAKSI : </b><font style="font-weight:bold;color:red;">'.$row->LAMA_KONSUL.' Menit</font>
											<br/> <b>NO FAKTUR : </b>'.$row->no_faktur.'
											<br/> <b>PELANGGAN : </b>'.$row->nama_costumer.'
										';
									}
									else
									{
										echo '
											<br/> <b>LAMA KONSULTASI : </b><font style="font-weight:bold;color:red;">'.$row->LAMA_KONSUL.' Menit</font>
											<br/> <b>NO FAKTUR : </b>'.$row->no_faktur.'
											<br/> <b>PASIEN : </b>'.$row->nama_costumer.'
											<br/> <b>DOKTER : </b>'.$row->nama_dokter.'
											<br/> <b>KUNJUNGAN : </b>'.$row->type_h_penjualan.'
										';
									}
								
								
								if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
								{
									echo'
									<br/>
									<div style="background-color:#c2f6c6;padding:1%;">
										<b>KETERANGAN : </b></br>'.$row->ket_penjualan.' '.$row->ket_penjualan2.'
									</div>
									
									<div class="form-group" style="background-color:grey;padding:1%;Visibility:hidden;">
									  <label for="id_gedung-'.$row->id_h_penjualan.'">RUANGAN</label>
									  <select name="id_gedung-'.$row->id_h_penjualan.'" id="id_gedung-'.$row->id_h_penjualan.'" class="form-control select2" title="Pilih ruangan ?" onchange="ubah_h_penjualan_gedung(this)" '.$status_selesai.' >
											
										';
											if(!empty($gedung_terpilih))
											{
												$gedung_terpilih = $gedung_terpilih->row();
												echo'<option value="'.$gedung_terpilih->id_gedung.'">'.$gedung_terpilih->nama_gedung.' ('.$gedung_terpilih->kode_gedung.')</option>';
											}
											else
											{
												echo'<option value="">== Pilih Ruangan ==</option>';
											}
											
											if(!empty($list_gedung))
											{
												$list_result = $list_gedung->result();
												foreach($list_result as $row_gedung)
												{
													echo'<option value="'.$row_gedung->id_gedung.'">'.$row_gedung->nama_gedung.' ('.$row_gedung->kode_gedung.')</option>';
												}
											}
										echo'
											<option value=""></option>
									   </select>
									</div>
									
									<hr/> 
									
									';
								}
								else
								{
									echo'
									<br/>
									<br/>
									<div class="form-group" style="background-color:grey;padding:1%;">
									  <label for="id_gedung-'.$row->id_h_penjualan.'">RUANGAN</label>
									  <select name="id_gedung-'.$row->id_h_penjualan.'" id="id_gedung-'.$row->id_h_penjualan.'" class="form-control select2" title="Pilih ruangan ?" onchange="ubah_h_penjualan_gedung(this)" '.$status_selesai.' >
											
										';
											if(!empty($gedung_terpilih))
											{
												$gedung_terpilih = $gedung_terpilih->row();
												echo'<option value="'.$gedung_terpilih->id_gedung.'">'.$gedung_terpilih->nama_gedung.' ('.$gedung_terpilih->kode_gedung.')</option>';
											}
											else
											{
												echo'<option value="">== Pilih Ruangan ==</option>';
											}
											
											if(!empty($list_gedung))
											{
												$list_result = $list_gedung->result();
												foreach($list_result as $row_gedung)
												{
													echo'<option value="'.$row_gedung->id_gedung.'">'.$row_gedung->nama_gedung.' ('.$row_gedung->kode_gedung.')</option>';
												}
											}
										echo'
											<option value=""></option>
									   </select>
									</div>
									
									<hr/> 
									<div style="background-color:#c2f6c6;padding:1%;">
										<b>KELUHAN DAN PEMERIKSAAN AWAL : </b></br>'.$row->ket_penjualan.' 
									</div>
									<div style="background-color:#c2f6c6;padding:1%;">
										<b>SARAN DOKTER/DIAGNOSA : </b></br>'.$row->ket_penjualan2.' 
									</div>
									';
								}
								
								
								//CLOSING TRANSAKSI - DITUTUP KARENA YANG TUTUP TRANSAKSI OLEH RECEPTIONIS
									/*
									if($row->sts_penjualan =="SELESAI")
									{
										echo'
											<a style="background-color:red;color:black; font-weight:bold;" href="#" id="selesai-'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan menyelesaikan transaksi ini ?" title="Apakah anda yakin akan menyelesaikan transaksi ini ?" class="confirm-btn btn btn-success btn-flat btn-block" onclick="selesai_tindakan(this)"> TRANSAKSI SUDAH DI TUTUP </a>
										</td>';
									}
									else
									{
										echo'
											<a style="color:black; font-weight:bold;" href="#" id="selesai-'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan menyelesaikan transaksi ini ?" title="Apakah anda yakin akan menyelesaikan transaksi ini ?" class="confirm-btn btn btn-success btn-flat btn-block" onclick="selesai_tindakan(this)"> SELESAI TINDAKAN </a>
										</td>';
									}
									*/
								//CLOSING TRANSAKSI - DITUTUP KARENA YANG TUTUP TRANSAKSI OLEH RECEPTIONIS
							}
							else
							{
								echo'<td>
									<b>NO : </b><font style="font-weight:bold;color:red;">'.$row->no_antrian.'</font> ('.$row->tgl_ins.' / <b style="color:red;">'.$row->MENIT_TUNGGU.' Menit Menunggu </b>)
									<br/> <b>LAMA KONSULTASI : </b><font style="font-weight:bold;color:red;">'.$row->LAMA_KONSUL.' Menit</font>
									<br/> <b>NO FAKTUR : </b>'.$row->no_faktur.'
									';
									
									if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
									{
										echo '
											<br/> <b>PELANGGAN : </b>'.$row->nama_costumer.'
										';
									}
									else
									{
										echo '
											<br/> <b>PASIEN : </b>'.$row->nama_costumer.'
											<br/> <b>DOKTER : </b>'.$row->nama_dokter.'
											<br/> <b>KUNJUNGAN : </b>'.$row->type_h_penjualan.'
										';
									}
									
							if($this->session->userdata('ses_gnl_isToko') == 'Y')  //MEMASTIKAN IS TOKO
							{
								echo'
									<br/>
									<div style="background-color:#c2f6c6;padding:1%;">
										<b>KETERANGAN : </b></br>'.$row->ket_penjualan.' '.$row->ket_penjualan2.'
									</div>
									<br/> <b>PAKET : </b><font style="color:red;">'.$row->nama_diskon.'</font>
									<hr/> 
									
									<div class="form-group" style="background-color:grey;padding:1%;Visibility:hidden;">
									  <label for="id_gedung-'.$row->id_h_penjualan.'">RUANGAN</label>
									  <select name="id_gedung-'.$row->id_h_penjualan.'" id="id_gedung-'.$row->id_h_penjualan.'" class="form-control select2" title="Pilih ruangan ?" onchange="ubah_h_penjualan_gedung(this)" '.$status_selesai.' >
											
										';
											if(!empty($gedung_terpilih))
											{
												$gedung_terpilih = $gedung_terpilih->row();
												echo'<option value="'.$gedung_terpilih->id_gedung.'">'.$gedung_terpilih->nama_gedung.' ('.$gedung_terpilih->kode_gedung.')</option>';
											}
											else
											{
												echo'<option value="">== Pilih Ruangan ==</option>';
											}
											
											if(!empty($list_gedung))
											{
												$list_result = $list_gedung->result();
												foreach($list_result as $row_gedung)
												{
													echo'<option value="'.$row_gedung->id_gedung.'">'.$row_gedung->nama_gedung.' ('.$row_gedung->kode_gedung.')</option>';
												}
											}
										echo'
											<option value=""></option>
									   </select>
									</div>
									
									<hr/> 
									
									';
							}
							else
							{
								echo'
									<br/>
									<br/>
									<div class="form-group" style="background-color:grey;padding:1%;">
										<label for="id_gedung-'.$row->id_h_penjualan.'">RUANGAN</label>
										<select name="id_gedung-'.$row->id_h_penjualan.'" id="id_gedung-'.$row->id_h_penjualan.'" class="form-control select2" title="Pilih ruangan ?" onchange="ubah_h_penjualan_gedung(this)" '.$status_selesai.'>
											
										';
											if(!empty($gedung_terpilih))
											{
												$gedung_terpilih = $gedung_terpilih->row();
												echo'<option value="'.$gedung_terpilih->id_gedung.'">'.$gedung_terpilih->nama_gedung.' ('.$gedung_terpilih->kode_gedung.')</option>';
											}
											else
											{
												echo'<option value="">== Pilih Ruangan ==</option>';
											}
											
											if(!empty($list_gedung))
											{
												$list_result = $list_gedung->result();
												foreach($list_result as $row_gedung)
												{
													echo'<option value="'.$row_gedung->id_gedung.'">'.$row_gedung->nama_gedung.' ('.$row_gedung->kode_gedung.')</option>';
												}
											}
										echo'
											<option value=""></option>
									   </select>
									</div>
									
									<br/> <b>PAKET : </b><font style="color:red;">'.$row->nama_diskon.'</font>
									<hr/> 
									<div style="background-color:#c2f6c6;padding:1%;">
										<b>KELUHAN DAN PEMERIKSAAN AWAL : </b></br>'.$row->ket_penjualan.' 
									</div>
									<div style="background-color:#c2f6c6;padding:1%;">
										<b>SARAN DOKTER : </b></br>'.$row->ket_penjualan2.' 
									</div>
									';
							}
							
								
								
								//CLOSING TRANSAKSI - DITUTUP KARENA YANG TUTUP TRANSAKSI OLEH RECEPTIONIS
									/*
										if($row->sts_penjualan =="SELESAI")
										{
											echo'
												<a style="background-color:red;color:black; font-weight:bold;" href="#" id="selesai-'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan menyelesaikan transaksi ini ?" title="Apakah anda yakin akan menyelesaikan transaksi ini ?" class="confirm-btn btn btn-success btn-flat btn-block" onclick="selesai_tindakan(this)"> TRANSAKSI SUDAH DI TUTUP </a>
											</td>';
										}
										else
										{
											echo'
												<a style="color:black; font-weight:bold;" href="#" id="selesai-'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan menyelesaikan transaksi ini ?" title="Apakah anda yakin akan menyelesaikan transaksi ini ?" class="confirm-btn btn btn-success btn-flat btn-block" onclick="selesai_tindakan(this)"> SELESAI TINDAKAN </a>
											</td>';
										}
									*/
								//CLOSING TRANSAKSI - DITUTUP KARENA YANG TUTUP TRANSAKSI OLEH RECEPTIONIS
								
							}
							
							
							//if( ($row->id_dokter == $this->session->userdata('ses_id_karyawan'))
							
							if($row->avatar_dokter2 == "")
							{
								$src_dokter2 = base_url().'assets/global/costumer/loading.gif';
							}
							else
							{
								$src_dokter2 = base_url().''.$row->avatar_url_dokter2.''.$row->avatar_dokter2;
							}
							
					
							//TIDAK DI AKTIFKAN, KARENA DOKTER DIPILIH PER TINDAKAN 20210324
							/*
							echo'
							<td>
								<!-- Profile Image -->
								  <div class="box box-primary">
									<div class="box-body box-profile">
									
											';
											
											if($row->sts_penjualan !="SELESAI")
											{
												echo'
													<button onclick="hapus(this)" id="dokter-'.$row->id_h_penjualan.'" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
												';
											}
											
											echo'
											<span id="img_edit_dokter-'.$row->id_h_penjualan.'">
												<img class="profile-user-img img-responsive img-circle" style="width:50%;height:110px;" src="'.$src_dokter2.'" alt="User profile picture">
											</span>
											
											<input type="hidden" name="id_dokter2-'.$row->id_h_penjualan.'" id="id_dokter2-'.$row->id_h_penjualan.'" value="'.$row->id_dokter2.'" />
											
											
											
											<h3 class="profile-username text-center" ><span id="nama_dokter2-'.$row->id_h_penjualan.'">'.$row->nama_dokter.'</span></h3>

											';
											
											if($row->sts_penjualan !="SELESAI")
											{
												
												echo'
												<p class="text-muted text-center"><span id="no_dokter2-'.$row->id_h_penjualan.'">'.$row->no_dokter.'</span></p>
												<a href="#" id="'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan melanjutkan transaksi ini ?" title="Apakah anda yakin akan melanjutkan transaksi ini ?" class="confirm-btn btn btn-primary btn-flat btn-block"    data-toggle="modal" data-target="#myModal_dokter" onclick="list_dokter(this);"> PILIH DOKTER </a>
												';
											}
										echo'
										</form>
									</div>
									<!-- /.box-body -->
								  </div>
							</td>';
							*/
							
							//PILIH THERAPIST
							if($this->session->userdata('ses_gnl_isToko') == 'N')  //MEMASTIKAN IS TOKO
							{
								echo'<td>';
									echo'<table width="100%" id="table_transaksi" class="table table-hover" style="border:1px solid black;">';
										echo'<thead>';
											echo'<tr style="background-color:grey;color:white;text-align:center;font-size:12px;">';
												echo'<th width="30%" style="text-align:center;border:1px solid black;">TINDAKAN</th>';
												echo'<th width="35%" style="text-align:center;border:1px solid black;">DOKTER</th>';
												echo'<th width="35%" style="text-align:center;border:1px solid black;">THERAPIST</th>';
											echo'</tr>';
											
											
											$cari = "
														WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
														AND A.id_h_penjualan = '".$row->id_h_penjualan."'
														AND COALESCE(B.isProduk,'') = 'JASA'
														";
											//}
											
											$order_by = " ORDER BY A.tgl_ins DESC ";
											
											$list_d_penjualan = $this->M_gl_h_penjualan->list_d_penjualan_faktur($cari,$order_by,1000,0);
											
											if(!empty($list_d_penjualan))
											{
												$list_result = $list_d_penjualan->result();
												$total = 0;
												$no = 1;
												foreach($list_result as $row_d_penjualan)
												{
													echo'<tr id="tr_list_transaksi-'.$row_d_penjualan->id_produk.'-'.$row_d_penjualan->id_h_diskon.'" >';
													echo'<td  style="text-align:left;width:40%;font-size:12px;border:1px solid black;">'.$row_d_penjualan->nama_produk.'</td>';
													
													if($row_d_penjualan->id_ass == "")
													{
														$nama_ass = "PILIH THERAPIST";
													}
													else
													{
														$nama_ass = $row_d_penjualan->nama_ass;
													}
													
													if($row_d_penjualan->id_dok == "")
													{
														$nama_dok = "PILIH DOKTER";
													}
													else
													{
														$nama_dok = $row_d_penjualan->nama_dok;
													}
													
													
													
													if($row->sts_penjualan != "SELESAI")
													{
														echo'<td style="text-align:left;width:30%;font-size:12px;border:1px solid black;">
															
															
															
															<a href="#" id="dok2-'.$row_d_penjualan->id_h_penjualan.'-'.$row_d_penjualan->id_d_penjualan.'" alt="Apakah anda yakin akan melanjutkan transaksi ini ?" title="Apakah anda yakin akan melanjutkan transaksi ini ?" class="confirm-btn btn btn-warning btn-flat btn-block"    data-toggle="modal" data-target="#myModal_dokter" onclick="list_dokter(this);"> '.$nama_dok.' </a>
															
														</td>';
														
														echo'<td style="text-align:left;width:30%;font-size:12px;border:1px solid black;">
															
															
															<a href="#" id="ass-'.$row_d_penjualan->id_h_penjualan.'-'.$row_d_penjualan->id_d_penjualan.'" alt="Apakah anda yakin akan melanjutkan transaksi ini ?" title="Apakah anda yakin akan melanjutkan transaksi ini ?" class="confirm-btn btn btn-primary btn-flat btn-block"    data-toggle="modal" data-target="#myModal_therapist" onclick="list_therapist(this);"> '.$nama_ass.' </a>
															
														</td>';
													}
													else
													{
														echo'<td style="text-align:left;width:30%;font-size:12px;border:1px solid black;">
															'.$nama_dok.'
														</td>';
														
														echo'<td style="text-align:left;width:30%;font-size:12px;border:1px solid black;">
															'.$nama_ass.'
														</td>';
													}
												echo'</tr>';
												$no++;
												}
											}
							
											
										echo'</thead>';
									echo'</table>';
								//echo'</div>';
								
								echo'</td>';
							}
							
							//PILIH THERAPIST
							
							
							
						echo'</tr>';
						$no++;
					}
					//echo '</tbody>';
				}
				else
				{
					echo'<tr>';
					echo'<td colspan="4" style="text-align:center;color:red;font-weight:bold;">Tidak Ada Pasien Yang Menunggu !</td>';
					echo'</tr>';
				}
				//ISI TABLE
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function get_ajax_update_perawatan_lanjut_old_tidak_detail()
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
							AND A.sts_penjualan IN ('PEMBAYARAN','SELESAI')
							AND DATE(A.tgl_h_penjualan) = DATE(NOW())
							AND (A.no_faktur LIKE '%".str_replace("'","",$_POST['cari'])."%' OR COALESCE(B.nama_lengkap,A.nama_costumer) LIKE '%".str_replace("'","",$_POST['cari'])."%')";
				}
				else
				{
					$cari = "WHERE A.kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' 
					AND A.sts_penjualan IN ('PEMBAYARAN','SELESAI')
					AND DATE(A.tgl_h_penjualan) = DATE(NOW())
					";
				}
				
				$order_by = "ORDER BY A.tgl_updt DESC";
				
				//UNTUK AKUMULASI INFO
					$jum_row = $this->M_gl_h_penjualan->count_pendaftaran_perawatan_lanjutan($cari)->JUMLAH;
				//UNTUK AKUMULASI INFO
				
				$this->load->library('pagination');
				//$config['first_url'] = base_url().'admin/jabatan?'.http_build_query($_GET);
				//$config['base_url'] = base_url().'admin/jabatan/';
				
				$config['first_url'] = site_url('gl-admin-pembayaran?'.http_build_query($_GET));
				$config['base_url'] = site_url('gl-admin-pembayaran/');
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
				
				$list_h_pendaftaran_proses_dokter = $this->M_gl_h_penjualan->list_pendaftaran_perawatan_lanjutan($cari,$order_by,$config['per_page'],$this->uri->segment(2,0));
				
				//ISI TABLE
				if(!empty($list_h_pendaftaran_proses_dokter))
				{
					$list_result = $list_h_pendaftaran_proses_dokter->result();
					$no =$this->uri->segment(2,0)+1;
					//echo '<tbody>';
					foreach($list_result as $row)
					{
						
						echo'<tr>';
							
							//echo'<td>'.$no.'</td>';
							
							echo'<td>';
							echo'<input type="hidden" id="id_h_penjualan_'.$no.'" name="id_h_penjualan_'.$no.'" value="'.$row->id_h_penjualan.'" />';
							
							echo'<input type="hidden" id="type_h_penjualan-'.$row->id_h_penjualan.'" name="type_h_penjualan-'.$row->id_h_penjualan.'" value="'.$row->type_h_penjualan.'" />';
							
							//echo'<input type="text" id="lama_kerja_dokter-'.$row->id_h_penjualan.'" name="lama_kerja_dokter-'.$row->id_h_penjualan.'" value="'.$row->lama_kerja_dokter.'" />';
							
							echo'<input type="hidden" id="lama_kerja_dokter2-'.$row->id_h_penjualan.'" name="lama_kerja_dokter2-'.$row->id_h_penjualan.'" value="'.$row->lama_kerja_dokter2.'" />';
							echo'<input type="hidden" id="lama_kerja_ass-'.$row->id_h_penjualan.'" name="lama_kerja_ass-'.$row->id_h_penjualan.'" value="'.$row->lama_kerja_ass.'" />';
							echo'<input type="hidden" id="lama_kerja_ass2-'.$row->id_h_penjualan.'" name="lama_kerja_ass2-'.$row->id_h_penjualan.'" value="'.$row->lama_kerja_ass2.'" />';
							echo'</td>';
							
							
							/*
							if($row->avatar == "")
							{
								$src = base_url().'assets/global/costumer/loading.gif';
							}
							else
							{
								if(!(file_exists("assets/global/costumer/".$row->avatar)))
								{
									$src = base_url().'assets/global/costumer/loading.gif';
								}
								else
								{
									$src = base_url().''.$row->avatar_url.''.$row->avatar;
								}
							}
							*/
							
							//$src = base_url().'assets/global/images/qrcode/'.$row->no_faktur.'.png';
							/*
							echo '<td>
								<img id="IMG_barcode_'.$no.'"  width="100%" style="border:1px solid #C8C8C8; padding:5px; float:left;" src="'.$src.'" />
								<br/>
								<center>
									<font style="font-weight:bold;color:red;font-size:18px;">'.$row->no_antrian.'
									</font>
								</center>
							</td>';
							*/
							
							if($row->id_h_diskon == "")
							{
								echo'<td>
									<b>NO : </b><font style="font-weight:bold;color:red;">'.$row->no_antrian.'</font> ('.$row->tgl_ins.' / <b style="color:red;">'.$row->MENIT_TUNGGU.' Menit Menunggu </b>)
									<br/> <b>LAMA KONSULTASI : </b><font style="font-weight:bold;color:red;">'.$row->LAMA_KONSUL.' Menit</font>
									<br/> <b>NO FAKTUR : </b>'.$row->no_faktur.'
									<br/> <b>PASIEN : </b>'.$row->nama_costumer.'
									<br/> <b>DOKTER : </b>'.$row->nama_dokter.'
									<br/> <b>KUNJUNGAN : </b>'.$row->type_h_penjualan.'
									<hr/> 
									<div style="background-color:#c2f6c6;padding:1%;">
										<b>KELUHAN DAN PEMERIKSAAN AWAL : </b></br>'.$row->ket_penjualan.' 
									</div>
									<div style="background-color:#c2f6c6;padding:1%;">
										<b>SARAN DOKTER/DIAGNOSA : </b></br>'.$row->ket_penjualan2.' 
									</div>
									';
								
								//CLOSING TRANSAKSI - DITUTUP KARENA YANG TUTUP TRANSAKSI OLEH RECEPTIONIS
									/*
									if($row->sts_penjualan =="SELESAI")
									{
										echo'
											<a style="background-color:red;color:black; font-weight:bold;" href="#" id="selesai-'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan menyelesaikan transaksi ini ?" title="Apakah anda yakin akan menyelesaikan transaksi ini ?" class="confirm-btn btn btn-success btn-flat btn-block" onclick="selesai_tindakan(this)"> TRANSAKSI SUDAH DI TUTUP </a>
										</td>';
									}
									else
									{
										echo'
											<a style="color:black; font-weight:bold;" href="#" id="selesai-'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan menyelesaikan transaksi ini ?" title="Apakah anda yakin akan menyelesaikan transaksi ini ?" class="confirm-btn btn btn-success btn-flat btn-block" onclick="selesai_tindakan(this)"> SELESAI TINDAKAN </a>
										</td>';
									}
									*/
								//CLOSING TRANSAKSI - DITUTUP KARENA YANG TUTUP TRANSAKSI OLEH RECEPTIONIS
							}
							else
							{
								echo'<td>
									<b>NO : </b><font style="font-weight:bold;color:red;">'.$row->no_antrian.'</font> ('.$row->tgl_ins.' / <b style="color:red;">'.$row->MENIT_TUNGGU.' Menit Menunggu </b>)
									<br/> <b>PASIEN : </b>'.$row->nama_costumer.'
									<br/> <b>DOKTER : </b>'.$row->nama_dokter.'
									<br/> <b>KUNJUNGAN : </b>'.$row->type_h_penjualan.'
									<br/> <b>PAKET : </b><font style="color:red;">'.$row->nama_diskon.'</font>
									<hr/> 
									<div style="background-color:#c2f6c6;padding:1%;">
										<b>KELUHAN DAN PEMERIKSAAN AWAL : </b></br>'.$row->ket_penjualan.' 
									</div>
									<div style="background-color:#c2f6c6;padding:1%;">
										<b>SARAN DOKTER : </b></br>'.$row->ket_penjualan2.' 
									</div>
									';
								
								//CLOSING TRANSAKSI - DITUTUP KARENA YANG TUTUP TRANSAKSI OLEH RECEPTIONIS
									/*
										if($row->sts_penjualan =="SELESAI")
										{
											echo'
												<a style="background-color:red;color:black; font-weight:bold;" href="#" id="selesai-'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan menyelesaikan transaksi ini ?" title="Apakah anda yakin akan menyelesaikan transaksi ini ?" class="confirm-btn btn btn-success btn-flat btn-block" onclick="selesai_tindakan(this)"> TRANSAKSI SUDAH DI TUTUP </a>
											</td>';
										}
										else
										{
											echo'
												<a style="color:black; font-weight:bold;" href="#" id="selesai-'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan menyelesaikan transaksi ini ?" title="Apakah anda yakin akan menyelesaikan transaksi ini ?" class="confirm-btn btn btn-success btn-flat btn-block" onclick="selesai_tindakan(this)"> SELESAI TINDAKAN </a>
											</td>';
										}
									*/
								//CLOSING TRANSAKSI - DITUTUP KARENA YANG TUTUP TRANSAKSI OLEH RECEPTIONIS
								
							}
							
							
							//if( ($row->id_dokter == $this->session->userdata('ses_id_karyawan'))
							
							if($row->avatar_dokter2 == "")
							{
								$src_dokter2 = base_url().'assets/global/costumer/loading.gif';
							}
							else
							{
								$src_dokter2 = base_url().''.$row->avatar_url_dokter2.''.$row->avatar_dokter2;
							}
							
					
							echo'
							<td>
								<!-- Profile Image -->
								  <div class="box box-primary">
									<div class="box-body box-profile">
									
											';
											
											if($row->sts_penjualan !="SELESAI")
											{
												echo'
													<button onclick="hapus(this)" id="dokter-'.$row->id_h_penjualan.'" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
												';
											}
											
											echo'
											<span id="img_edit_dokter-'.$row->id_h_penjualan.'">
												<img class="profile-user-img img-responsive img-circle" style="width:50%;height:110px;" src="'.$src_dokter2.'" alt="User profile picture">
											</span>
											
											<input type="hidden" name="id_dokter2-'.$row->id_h_penjualan.'" id="id_dokter2-'.$row->id_h_penjualan.'" value="'.$row->id_dokter2.'" />
											
											
											
											<h3 class="profile-username text-center" ><span id="nama_dokter2-'.$row->id_h_penjualan.'">'.$row->nama_dokter.'</span></h3>

											';
											
											if($row->sts_penjualan !="SELESAI")
											{
												echo'
												<p class="text-muted text-center"><span id="no_dokter2-'.$row->id_h_penjualan.'">'.$row->no_dokter.'</span></p>
												<a href="#" id="'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan melanjutkan transaksi ini ?" title="Apakah anda yakin akan melanjutkan transaksi ini ?" class="confirm-btn btn btn-primary btn-flat btn-block"    data-toggle="modal" data-target="#myModal_dokter" onclick="list_dokter(this);"> PILIH DOKTER </a>
												';
											}
										echo'
										</form>
									</div>
									<!-- /.box-body -->
								  </div>
							</td>';
							
							if($row->avatar_ass == "")
							{
								$src_ass = base_url().'assets/global/costumer/loading.gif';
							}
							else
							{
								$src_ass = base_url().''.$row->avatar_url_ass.''.$row->avatar_ass;
							}
							echo'
							<td>
							
								<!-- Profile Image -->
								  <div class="box box-primary">
									<div class="box-body box-profile">
											';
											
											if($row->sts_penjualan !="SELESAI")
											{
												echo'
													<button onclick="hapus(this)"  id="assdok-'.$row->id_h_penjualan.'" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
												';
											}
											
											echo'
											<span id="img_edit_ass-'.$row->id_h_penjualan.'">
												<img class="profile-user-img img-responsive img-circle" style="width:50%;height:110px;"  src="'.$src_ass.'" alt="User profile picture">
											</span>
											
											<input type="hidden" name="id_ass-'.$row->id_h_penjualan.'" id="id_ass-'.$row->id_h_penjualan.'" value="'.$row->id_ass_dok.'" />
											
											
											
											<h3 class="profile-username text-center" ><span id="nama_ass-'.$row->id_h_penjualan.'">'.$row->nama_ass.'</span></h3>
											
											';
											
											if($row->sts_penjualan !="SELESAI")
											{
												echo'
												<p class="text-muted text-center"><span id="no_ass-'.$row->id_h_penjualan.'">'.$row->no_ass.'</span></p>
												<a href="#" id="ass-'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan melanjutkan transaksi ini ?" title="Apakah anda yakin akan melanjutkan transaksi ini ?" class="confirm-btn btn btn-primary btn-flat btn-block"    data-toggle="modal" data-target="#myModal_therapist" onclick="list_therapist(this);"> PILIH THERAPIST/ASS 1 </a>
												';
											}
										echo'
										</form>
									</div>
									<!-- /.box-body -->
								  </div>
							</td>';
							
							if($row->avatar_ass2 == "")
							{
								$src_ass2 = base_url().'assets/global/costumer/loading.gif';
							}
							else
							{
								$src_ass2 = base_url().''.$row->avatar_url_ass2.''.$row->avatar_ass2;
							}
							echo'
							<td>
							
								<!-- Profile Image -->
								  <div class="box box-primary">
									<div class="box-body box-profile">
											
											';
											
											if($row->sts_penjualan !="SELESAI")
											{
												echo'
													<button onclick="hapus(this)" id="assdok2-'.$row->id_h_penjualan.'" type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
												';
											}
											
											echo'
											<span id="img_edit_ass2-'.$row->id_h_penjualan.'">
												<img class="profile-user-img img-responsive img-circle" style="width:50%;height:110px;"  src="'.$src_ass2.'" alt="User profile picture">
											</span>
											
											<input type="hidden" name="id_ass2-'.$row->id_h_penjualan.'" id="id_ass2-'.$row->id_h_penjualan.'" value="'.$row->id_ass_dok2.'" />
											
											
											
											<h3 class="profile-username text-center" ><span id="nama_ass2-'.$row->id_h_penjualan.'">'.$row->nama_ass2.'</span></h3>

											';
											
											if($row->sts_penjualan !="SELESAI")
											{
												echo'
													<p class="text-muted text-center"><span id="no_ass2-'.$row->id_h_penjualan.'">'.$row->no_ass2.'</span></p>
													<a href="#" id="ass2-'.$row->id_h_penjualan.'" alt="Apakah anda yakin akan melanjutkan transaksi ini ?" title="Apakah anda yakin akan melanjutkan transaksi ini ?" class="confirm-btn btn btn-primary btn-flat btn-block"    data-toggle="modal" data-target="#myModal_therapist2" onclick="list_therapist2(this);"> PILIH THERAPIST/ASS 2 </a>
												';
											}
										echo'
										</form>
									</div>
									<!-- /.box-body -->
								  </div>
							</td>';
							
							
						echo'</tr>';
						$no++;
					}
					//echo '</tbody>';
				}
				else
				{
					echo'<tr>';
					echo'<td colspan="4" style="text-align:center;color:red;font-weight:bold;">Tidak Ada Pasien Yang Menunggu !</td>';
					echo'</tr>';
				}
				//ISI TABLE
				
			}
			else
			{
				header('Location: '.base_url().'gl-admin-login');
			}
		}
	}

	function ubah_id_dokter_therapist_tindakan()
	{
		$this->M_gl_h_penjualan->ubah_h_penjualan_dkokter_therapist_tindakan($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$_POST['id_dokter'],$_POST['id_therapist'],$_POST['id_therapist2'],$_POST['id_gedung']);
		
		$this->M_gl_h_penjualan->ubah_status_therapist_pada_h_penjualan_masal($_POST['id_h_penjualan'],$this->session->userdata('ses_kode_kantor'));
	}

	function ubah_d_penjualan_therapist_tindakan()
	{
		//$this->M_gl_h_penjualan->ubah_h_penjualan_dkokter_therapist_tindakan($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$_POST['id_dokter'],$_POST['id_therapist'],$_POST['id_therapist2']);
		
		$this->M_gl_h_penjualan->ubah_d_penjualan_therapist($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$_POST['id_d_penjualan'],$_POST['id_therapist'],$_POST['nama_therapist']);
		
		echo"SUKSES-".$_POST['nama_therapist'];
	}
	
	function ubah_d_penjualan_dokter_tindakan()
	{
		//$this->M_gl_h_penjualan->ubah_h_penjualan_dkokter_therapist_tindakan($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$_POST['id_dokter'],$_POST['id_therapist'],$_POST['id_therapist2']);
		
		$this->M_gl_h_penjualan->ubah_d_penjualan_dokter($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],$_POST['id_d_penjualan'],$_POST['id_dok'],$_POST['nama_dok']);
		
		echo"SUKSES-".$_POST['nama_dok'];
	}
	
	function selesai_transaksi()
	{
		$id_h_penjualan = $_POST['id_h_penjualan'];
		$poli = $_POST['poli'];
		$var_lama_kerja_dokter2 = $_POST['var_lama_kerja_dokter2'];
		$var_lama_kerja_ass = $_POST['var_lama_kerja_ass'];
		$var_lama_kerja_ass2 = $_POST['var_lama_kerja_ass2'];
		
		
		$cari_catatan_fee = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' ";
			
		$data_catatan_fee = $this->M_gl_h_penjualan_catat_untuk_fee->get_h_penjualan_catat_untuk_fee($cari_catatan_fee);
		if(!empty($data_catatan_fee))
		{
			$data_catatan_fee = $data_catatan_fee->row();
			
			if($_POST['id_dokter2'] != "")
			{
				$this->hitung_fee
				(
					$_POST['id_h_penjualan'],
					$_POST['id_dokter2'],
					"DOKTER", //$_POST['isDokter'], //DOKTER, THERAPIST, KARYAWAN UMUM
					$poli, //$_POST['poli'],
					$var_lama_kerja_dokter2, //$_POST['var_lama_kerja'],
					$data_catatan_fee->isPasienBaru, //$_POST['var_isPasienBaru'],
					'TIDAK', //$_POST['var_isMembuatResep'],
					$data_catatan_fee->jasa, //$_POST['var_min_jasa'],
					$data_catatan_fee->produk, //$_POST['var_min_produk'],
					$data_catatan_fee->nominal_transaksi, //$_POST['var_nominal_tr_per_pasien']
					"DOKTER TINDAKAN"
				);
			}
			else
			{
				//JIKA ADA FEE, MAKA HAPUS FEE SEBELUMNYA UNTUK DOKTER TINDAKAN
					//($id_h_penjualan,$kode_kantor,$group_by,$id_karyawan)
					$this->M_gl_h_penjualan->hapus_d_penjualan_fee($_POST['id_h_penjualan'],$this->session->userdata('ses_kode_kantor'),"DOKTER TINDAKAN",$_POST['id_dokter2']);
				//JIKA ADA FEE, MAKA HAPUS FEE SEBELUMNYA UNTUK DOKTER TINDAKAN
			}
			
			if($_POST['id_ass'] != "")
			{
				$this->hitung_fee
				(
					$_POST['id_h_penjualan'],
					$_POST['id_ass'],
					"THERAPIST", //$_POST['isDokter'], //DOKTER, THERAPIST, KARYAWAN UMUM
					$poli, //$_POST['poli'],
					$var_lama_kerja_ass, //$_POST['var_lama_kerja'],
					$data_catatan_fee->isPasienBaru, //$_POST['var_isPasienBaru'],
					'TIDAK', //$_POST['var_isMembuatResep'],
					$data_catatan_fee->jasa, //$_POST['var_min_jasa'],
					$data_catatan_fee->produk, //$_POST['var_min_produk'],
					$data_catatan_fee->nominal_transaksi, //$_POST['var_nominal_tr_per_pasien']
					"ASISTEN 1"
				);
			}
			else
			{
				//JIKA ADA FEE, MAKA HAPUS FEE SEBELUMNYA UNTUK ASSISTEN 1
					//($id_h_penjualan,$kode_kantor,$group_by,$id_karyawan)
					$this->M_gl_h_penjualan->hapus_d_penjualan_fee($_POST['id_h_penjualan'],$this->session->userdata('ses_kode_kantor'),"ASISTEN 1",$_POST['id_ass']);
				//JIKA ADA FEE, MAKA HAPUS FEE SEBELUMNYA UNTUK ASSISTEN 1
			}
			
			if($_POST['id_ass2'] != "")
			{
				$this->hitung_fee
				(
					$_POST['id_h_penjualan'],
					$_POST['id_ass2'],
					"THERAPIST", //$_POST['isDokter'], //DOKTER, THERAPIST, KARYAWAN UMUM
					$poli, //$_POST['poli'],
					$var_lama_kerja_ass2, //$_POST['var_lama_kerja'],
					$data_catatan_fee->isPasienBaru, //$_POST['var_isPasienBaru'],
					'TIDAK', //$_POST['var_isMembuatResep'],
					$data_catatan_fee->jasa, //$_POST['var_min_jasa'],
					$data_catatan_fee->produk, //$_POST['var_min_produk'],
					$data_catatan_fee->nominal_transaksi, //$_POST['var_nominal_tr_per_pasien']
					"ASISTEN 2"
				);
			}
			else
			{
				//JIKA ADA FEE, MAKA HAPUS FEE SEBELUMNYA UNTUK ASSISTEN 2
					//($id_h_penjualan,$kode_kantor,$group_by,$id_karyawan)
					$this->M_gl_h_penjualan->hapus_d_penjualan_fee($_POST['id_h_penjualan'],$this->session->userdata('ses_kode_kantor'),"ASISTEN 2",$_POST['id_ass2']);
				//JIKA ADA FEE, MAKA HAPUS FEE SEBELUMNYA UNTUK ASSISTEN 2
			}
			
			
		}
		
		
		//PROSES UBAH STATUS H PENJUALAN MENJADI PEMERIKSAAN
			//JIKA PENJUALAN LANGSUNG UBAH STATUS JADI SELESAI//
				
				$data_h_penjualan = $this->M_gl_h_penjualan->get_h_penjualan('id_h_penjualan',$_POST['id_h_penjualan']);
				if(!empty($data_h_penjualan))
				{
					$data_h_penjualan = $data_h_penjualan->row();
					if($data_h_penjualan->sts_penjualan == "SELESAI")
					{
						$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"PEMBAYARAN",$data_h_penjualan->pajak,$data_h_penjualan->diskon,$data_h_penjualan->ket_penjualan2);
						
						$balikan = "PEMBAYARAN";
					}
					else
					{
						$this->M_gl_h_penjualan->ubah_h_penjualan_sts_saja($this->session->userdata('ses_kode_kantor'),$_POST['id_h_penjualan'],"SELESAI",$data_h_penjualan->pajak,$data_h_penjualan->diskon,$data_h_penjualan->ket_penjualan2);
						
						$this->M_gl_h_penjualan->simpan_outbox("085710867033","TERIMA KASIH ATAS KUNJUNGAN ANDA | KLINIK GLAFIDSYA MEDIKA");
						
						$balikan = "SELESAI";
					}
					
				}
			//JIKA PENJUALAN LANGSUNG UBAH STATUS JADI SELESAI//
		//PROSES UBAH STATUS H PENJUALAN MENJADI PEMERIKSAAN
		echo $balikan;
	}
	

	function hitung_fee($id_h_penjualan,$id_karyawan,$isDokter,$poli,$var_lama_kerja,$var_isPasienBaru,$var_isMembuatResep,$var_min_jasa,$var_min_produk,$var_nominal_tr_per_pasien,$var_jenis_tindakan)
	
	//function selesai_transaksi()
	{
		//$poli = $_POST['poli'];
		//$var_lama_kerja_dokter2 = $_POST['var_lama_kerja_dokter2'];
		
		//$cari_catatan_fee = " WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."' ";
			
		//$data_catatan_fee = $this->M_gl_h_penjualan_catat_untuk_fee->get_h_penjualan_catat_untuk_fee($cari_catatan_fee);
		//if(!empty($data_catatan_fee))
		//$data_catatan_fee = $data_catatan_fee->row();
		
		//$id_h_penjualan = $_POST['id_h_penjualan'];
		//$id_karyawan = $_POST['id_dokter2']; //$_POST['id_karyawan'];
		//$isDokter = "DOKTER"; //$_POST['isDokter'];
		//$poli = $_POST['poli'];
		//$var_lama_kerja = $_POST['var_lama_kerja_dokter2']; //$_POST['var_lama_kerja'];
		//$var_isPasienBaru = $data_catatan_fee->isPasienBaru; //$_POST['var_isPasienBaru'];
		//$var_isMembuatResep = "TIDAK"; //$_POST['var_isMembuatResep'];
		//$var_min_jasa = $data_catatan_fee->jasa; //$_POST['var_min_jasa'];
		//$var_min_produk = $data_catatan_fee->produk; //$_POST['var_min_produk'];
		//$var_nominal_tr_per_pasien = $data_catatan_fee->nominal_transaksi; //$_POST['var_nominal_tr_per_pasien'];
		
		
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
		
		
		
		$list_fee = $this->M_gl_h_penjualan->list_fee($this->session->userdata('ses_kode_kantor'),$isDokter,$poli," AND isMembuatResep = '".$var_isMembuatResep."' ");
		if(!empty($list_fee))
		{
			/*JIKA ADA FEE, MAKA HAPUS FEE SEBELUMNYA*/
				//($id_h_penjualan,$kode_kantor,$group_by,$id_karyawan)
				$this->M_gl_h_penjualan->hapus_d_penjualan_fee($id_h_penjualan,$this->session->userdata('ses_kode_kantor'),$var_jenis_tindakan,$id_karyawan);
			/*JIKA ADA FEE, MAKA HAPUS FEE SEBELUMNYA*/
			
			//echo"ADA FEE LOOPING</br>";
			$list_result = $list_fee->result();
			$no = 1;
			foreach($list_result as $row)
			{
				//echo"MASUK LOOPING</br>";
				
				$cari = "";
				
				if($row->masa_kerja_hari <> 0)
				{
					$cari = $cari ." AND ".$var_lama_kerja." ".$row->optr_masa_kerja." ".$row->masa_kerja_hari."";
				}
				
				//echo"MASUK LOOPING PASIEN BARU : ".$var_isPasienBaru." | BUAT RESEP : ".$var_isMembuatResep." | NOMINAL TRANSAKSI : ".$var_nominal_tr_per_pasien."</br>";
				
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
					$cari = $cari ." AND ".$var_min_jasa." ".$row->optr_min_jasa." ".$row->min_jasa."";
				}
				
				if($row->min_produk <> 0)
				{
					$cari = $cari ." AND ".$var_min_produk." ".$row->optr_min_produk." ".$row->min_produk."";
				}
				
				
				$cari = $cari ." AND ".$var_nominal_tr_per_pasien." >= nominal_tr_per_pasien";
				
				//echo '</br>'.$cari;
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
						
						//echo"SIMPAN </br>";
						$this->M_gl_h_penjualan->simpan_d_fee_penjualan
						(
							$id_h_penjualan,
							$row_fee->id_h_fee,
							$id_karyawan,
							$var_jenis_tindakan,
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
			//echo"TIDAK ADA FEE LOOPING</br>";
		}
	}
	
	function update_biaya()
	{
		$strQuery = "UPDATE tb_h_penjualan SET biaya = '".str_replace(",","",$_POST['biaya'])."' WHERE kode_kantor = '".$this->session->userdata('ses_kode_kantor')."' AND id_h_penjualan = '".$_POST['id_h_penjualan']."'";
		$this->M_gl_h_penjualan->ubah_h_penjualan_by_kriteria_query($strQuery);
		
		echo'SUKSES';
	}

}

/* End of file welcome.php */
/* Location: ./application/controllers/C_gl_admin_pedaftaran_proses_dokter.php */